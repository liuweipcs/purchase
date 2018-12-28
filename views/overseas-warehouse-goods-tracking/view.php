<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\OverseasWarehouseGoodsTracking */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Overseas Warehouse Goods Trackings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="overseas-warehouse-goods-tracking-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'owarehouse_name',
            'sku',
            'purchase_order_no',
            'buyer',
            'state',
            'countdown_days',
            'financial_payment_time:datetime',
            'product_arrival_time:datetime',
        ],
    ]) ?>

</div>
