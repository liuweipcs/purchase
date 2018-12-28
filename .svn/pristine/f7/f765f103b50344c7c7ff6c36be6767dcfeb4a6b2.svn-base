<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SkuStatisticsLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sku Statistics Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sku-statistics-log-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Sku Statistics Log', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'po_number',
            'sku',
            'warehouse_code',
            'note',
            // 'created_at',
            // 'creator',
            // 'status',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
