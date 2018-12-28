<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SkuSalesStatisticsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sku Sales Statistics';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sku-sales-statistics-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Sku Sales Statistics', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'sku',
            'warehouse_code',
            'days_sales_3',
            'days_sales_7',
            // 'days_sales_15',
            // 'days_sales_30',
            // 'days_sales_60',
            // 'days_sales_90',
            // 'statistics_date',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
