<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SkuStatisticsLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '补货日志';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sku-statistics-log-index">
    <? Pjax::begin()?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'po_number',
            'sku',
            'warehouse_code',
            'note',
            'created_at',
            'creator',
            'status',
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [
            // '{export}',
        ],


        'pjax' => true,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => false,

        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['view-log'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
    <? Pjax::end()?>
</div>