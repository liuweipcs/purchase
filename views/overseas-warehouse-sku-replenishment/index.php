<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SkuSingleTacticMainSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '海外仓SKU补货策略');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sku-single-tactic-main-index">

    <h1><?php //echo Html::encode($this->title) ?></h1>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <p>
        <?= Html::a(Yii::t('app', '创建海外仓补货策略'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'sku',
            [
                'attribute' => 'warehouse',
                'value'=> function($model){
                    return \app\services\BaseServices::getWarehouseCode($model->warehouse);
                },
                'filter'=> \app\services\BaseServices::getWarehouseCode(),

            ],

            [
                'attribute' => 'date_start',
                'value'=> function($model){ if($model->date_start){return $model->date_start;}else{ return '';} },
                'filterType'=>GridView::FILTER_DATETIME ,
            ],

            [
                'attribute' => 'date_end',
                'value'=> function($model){ if($model->date_end){return $model->date_end;}else{ return '';} },
                'filterType'=>GridView::FILTER_DATETIME ,
            ],

            'user',
            // 'create_date',

            'scontent.supply_days',

            [
                'attribute' => 'status',
                'value'=> function($model){ return Yii::$app->params['boolean'][$model->status];},
                'filter'=> Yii::$app->params['boolean'],
            ],


            [
                'header'=>'操作',
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update} ',
                'buttons' => [

                ],
            ],
        ],

        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            //'{export}',
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
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            // 'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
</div>
