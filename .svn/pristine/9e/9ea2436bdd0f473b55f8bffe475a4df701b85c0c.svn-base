<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseSuggest */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Purchase Suggests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-suggest-view">

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
            'warehouse_code',
            'warehouse_name',
            'sku',
            'name',
            'supplier_code',
            'supplier_name',
            'buyer',
            'replenish_type',
            'qty',
            'price',
            'ship_method',
            'is_purchase',
            'created_at',
            'creator',
            'product_category_id',
            'category_cn_name',
            'on_way_stock',
            'available_stock',
            'stock',
            'left_stock',
            'days_sales_3',
            'days_sales_7',
            'days_sales_15',
            'days_sales_30',
            'sales_avg',
            'type',
        ],
    ]) ?>

</div>
