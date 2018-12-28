<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\OverseasWarehouseGoodsTaxRebate */

$this->title = Yii::t('app', '添加');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="overseas-warehouse-goods-tax-rebate-create">

    <h1><?php // Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
