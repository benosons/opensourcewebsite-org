<?php

use app\components\helpers\TimeHelper;
use app\models\Country;
use app\models\Language;
use app\models\LanguageLevel;
use app\widgets\buttons\TrashButton;
use app\widgets\ModalAjax;
use yii\helpers\Url;
use app\widgets\buttons\EditButton;
use app\components\helpers\Html;
use app\components\helpers\ExternalLink;
use app\models\User;

/* @var $this yii\web\View */

$this->title = Yii::t('app', 'Dashboard');

$currencyExchangeOrderMatchesCount = $model->getCurrencyExchangeOrderMatchesCount();
?>
<div class="account-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                    <tr>
                                        <th class="align-middle"><?= Yii::t('user', 'Rank'); ?></th>
                                        <td class="align-middle"><b><?= $model->getRank() ?></b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= Yii::t('user', 'Voting Power'); ?></th>
                                        <td class="align-middle"><b><?= $model->getRatingPercent() ?> %</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= Yii::t('user', 'Real confirmations'); ?></th>
                                        <td class="align-middle"><?= $model->getRealConfirmations() ?></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($currencyExchangeOrderMatchesCount) : ?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'New matches'); ?></h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                    <?php if ($currencyExchangeOrderMatchesCount) : ?>
                                        <tr><td><?= Html::a(Yii::t('app', 'Currency Exchange') . ' - ' . Yii::t('app', 'Orders') . ': ' . $currencyExchangeOrderMatchesCount, Url::toRoute(['currency-exchange-order/index'])) ?></td></tr>
                                    <?php endif; ?>
                                    <?php //TODO show new matches?>
                                    <?php if (false) : ?>
                                        <tr><td><?= Html::a(Yii::t('app', 'Ads') . ' - ' . Yii::t('app', 'Offers') . ': ' . '10', Url::toRoute(['ad-offer/index'])) ?></td></tr>
                                    <?php endif; ?>
                                    <?php if (false) : ?>
                                        <tr><td><?= Html::a(Yii::t('app', 'Ads') . ' - ' . Yii::t('app', 'Searches') . ': ' . '10', Url::toRoute(['ad-search/index'])) ?></td></tr>
                                    <?php endif; ?>
                                    <?php if (false) : ?>
                                        <tr><td><?= Html::a(Yii::t('app', 'Jobs') . ' - ' . Yii::t('app', 'Vacancies') . ': ' . '10', Url::toRoute(['vacancy/index'])) ?></td></tr>
                                    <?php endif; ?>
                                    <?php if (false) : ?>
                                        <tr><td><?= Html::a(Yii::t('app', 'Jobs') . ' - ' . Yii::t('app', 'Resumes') . ': ' . '10', Url::toRoute(['resume/index'])) ?></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>