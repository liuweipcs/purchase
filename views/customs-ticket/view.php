<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderItems */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Purchase Order Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-items-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'pur_number',
            'sku',
            'name',
            'qty',
            'price',
            'ctq',
            'rqy',
            'cty',
            'sales_status',
            'product_img',
            'order_id',
            'is_exemption',
            'items_totalprice',
            'product_link',
            'e_ctq',
            'e_price',
            'avg_num',
            'is_end',
            'check_date',
            'is_check',
            'wcq',
            'pur_ticketed_point',
            'base_price',
        ],
    ]) ?>

</div>
