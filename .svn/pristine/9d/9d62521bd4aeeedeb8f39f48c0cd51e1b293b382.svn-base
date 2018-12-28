<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\OverseasWarehouseGoodsTaxRebateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '海外仓货物退税');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="overseas-warehouse-goods-tax-rebate-index">

    <h1><?php // Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '创建'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
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
            'country',
            'tax_rate',

            [
                'attribute' => 'state',
                'value'=> function($model){ return Yii::$app->params['boolean'][$model->state];},
                'filter'=>Yii::$app->params['boolean'],
            ],

            'create_user',

            [
                'attribute' => 'create_time',
                'format'=>['date','php:Y-m-d H:i:s'],
                'filterType'=>GridView::FILTER_DATETIME ,
            ],

            [
                'header'=>'操作',
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update} {delete}',
                'buttons'=>[],
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
<?php Pjax::end(); ?>
</div>