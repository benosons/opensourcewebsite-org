<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User;
use yii\helpers\ArrayHelper;

/**
 * Class GroupRefreshController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupRefreshController extends Controller
{
    /**
     * @param string|int|null $chatId
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionIndex($chatId = null): array
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->run('group/index');
        }

        try {
            $botApiChat = $this->getBotApi()->getChat($chat->getChatId());
        } catch (\Exception $e) {
            Yii::warning($e);

            if (in_array($e->getCode(), [400, 403])) {
                // Chat has been removed in Telegram => remove chat from db
                $chat->delete();

                return $this->run('group/index');
            }

            throw $e;
        }

        if (!$this->getBotApi()->getChatMember($chat->getChatId(), explode(':', $this->getBot()->token)[0])->isActualChatMember()) {
            // Bot is not the chat member => remove chat from db
            $chat->delete();

            return $this->run('group/index');
        }
        // Update chat information
        $chat->setAttributes([
            'type' => $botApiChat->getType(),
            'title' => $botApiChat->getTitle(),
            'username' => $botApiChat->getUsername(),
            'description' => $botApiChat->getDescription(),
        ]);

        if (!$chat->save()) {
            Yii::warning($chat->getErrors());

            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $botApiAdministrators = $this->getBotApi()->getChatAdministrators($chat->getChatId());

        $botApiAdministratorsIds = array_map(
            fn ($a) => $a->getUser()->getId(),
            $botApiAdministrators
        );

        $outdatedAdministrators = $chat->getAdministrators()
            ->andWhere([
                'not',
                ['provider_user_id' => $botApiAdministratorsIds],
            ])
            ->all();

        foreach ($outdatedAdministrators as $outdatedAdministrator) {
            try {
                $botApiChatMember = $this->getBotApi()->getChatMember(
                    $chat->getChatId(),
                    $outdatedAdministrator->provider_user_id
                );

                if ($botApiChatMember && $botApiChatMember->isActualChatMember()) {
                    $chatMember = ChatMember::findOne([
                        'chat_id' => $chat->id,
                        'user_id' => $outdatedAdministrator->id,
                    ]);

                    $chatMember->setAttributes([
                        'status' => $botApiChatMember->getStatus(),
                    ]);

                    $chatMember->save(false);

                    continue;
                } else {
                    ChatMember::deleteAll([
                        'chat_id' => $chat->id,
                        'user_id' => $outdatedAdministrator->id,
                    ]);
                }
            } catch (\Exception $e) {
                Yii::warning($e);
            }

            $chat->unlink('users', $outdatedAdministrator, true);
        }

        $currentAdministratorsIds = array_map(
            fn ($a) => $a->provider_user_id,
            $chat->getAdministrators()->all()
        );

        foreach ($botApiAdministrators as $botApiAdministrator) {
            $user = User::find()
                ->andWhere([
                    'provider_user_id' => $botApiAdministrator->getUser()->getId(),
                ])
                ->one();

            if (!$user) {
                $user = User::createUser($botApiAdministrator->getUser());
                $user->updateInfo($botApiAdministrator->getUser());
                $user->save();
            }

            $chatMember = ChatMember::findOne([
                'chat_id' => $chat->id,
                'user_id' => $user->id,
            ]);

            if (!$chatMember) {
                $user->link('chats', $chat, ['status' => $botApiAdministrator->getStatus()]);
            } else {
                $chatMember->setAttributes([
                    'status' => $botApiAdministrator->getStatus(),
                ]);

                $chatMember->save(false);
            }
        }
        // user is not in Telegram admin list
        if (!in_array($this->getTelegramUser()->provider_user_id, $botApiAdministratorsIds)) {
            return $this->run('group/index');
        } else {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('index')
                )
                ->build();
        }
    }
}
