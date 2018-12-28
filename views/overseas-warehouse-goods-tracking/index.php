<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\OverseasWarehouseGoodsTrackingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '海外仓货物跟踪');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="overseas-warehouse-goods-tracking-index">

    <h1><?php // Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php // Html::a(Yii::t('app', 'Create Overseas Warehouse Goods Tracking'), ['create'], ['class' => 'btn btn-success']) ?>
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

            'owarehouse_name',
            'sku',
            'purchase_order_no',
            'buyer',

            [
                'attribute'=>'countdown_days',
                'format'=>'raw',
                'value'=>function($model){
                    $tohtml="<span style='color: red' ><b>$model->countdown_days</b></span>";
                    $tovalue=$model->countdown_days;
                    return $tovalue <= 7 && $tovalue > 0 ? $tohtml : $tovalue;
                }
            ],

            [
                'attribute' => 'state',
                'value'=> function($model){ return Yii::$app->params['owhouse_state'][$model->state];},
                'filter'=>Yii::$app->params['owhouse_state'],
            ],

            // 'financial_payment_time:datetime',
            // 'product_arrival_time:datetime',

            //['class' => 'yii\grid\ActionColumn'],
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