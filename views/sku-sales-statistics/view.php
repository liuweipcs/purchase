<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SkuSalesStatistics */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Sku Sales Statistics', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sku-sales-statistics-view">

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
            'sku',
            'warehouse_code',
            'days_sales_3',
            'days_sales_7',
            'days_sales_15',
            'days_sales_30',
            'days_sales_60',
            'days_sales_90',
            'statistics_date',
        ],
    ]) ?>

</div>
