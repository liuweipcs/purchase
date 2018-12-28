<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use mdm\admin\components\Helper;
use yii\bootstrap\Modal;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SkuSingleTacticMainSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'SKU补货策略');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sku-single-tactic-main-index">

    <h1><?php //echo Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '创建补货策略'), ['create'], ['class' => 'btn btn-success']) ?>
        <?php
        if(Helper::checkRoute('replenishment-strategy-import')) {
            echo Html::a('批量导入补货策略', ['#'], ['class' => 'btn btn-success replenishment-strategy-import', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
        }
        if(Helper::checkRoute('delete-batch'))
        {
            echo Html::a('批量删除', ['#'], ['class' => 'btn btn-primary delete-batch']);
        }
        ?>
    </p>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'options'=>[
            'id'=>'grid_sku_single',
        ],
        'filterModel' => $searchModel,
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->id];
                }
            ],
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
            'scontent.minimum_safe_stock_days',
            'scontent.days_safe_transfer',
            [
                'attribute' => 'status',
                'value'=> function($model){ return Yii::$app->params['boolean'][$model->status];},
                'filter'=> Yii::$app->params['boolean'],
            ],


            [
                'header'=>'操作',
                'class' => 'yii\grid\ActionColumn',
                'template'=>Helper::filterActionColumn('{update}{delete}'),
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
<?php
Modal::begin([
    'id' => 'create-modal',
    //'header' => '<h4 class="modal-title">系统信息</h4>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',
    ],
]);
Modal::end();
$arrival='请选择';

$urls = Url::toRoute('replenishment-strategy-import');
$delete_batch = Url::toRoute('delete-batch'); //批量删除

$js = <<<JS
$(function () {
        //批量导入补货策略
        $(".replenishment-strategy-import").click(function(){
            $.get("$urls", {},function (data) {
                $('.modal-body').html(data);
            });
        });
        //批量删除
        $(".delete-batch").click(function(){
            var ids = $('#grid_sku_single').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择数据!');
                return false;
            }else{
                layer.confirm('确定删除吗?', {icon: 3, title:'提示'}, function(index){
                      $.post("$delete_batch", {ids:ids},function (data) {
                            $('.modal-body').html(data);
                        });
                       layer.close(index);
    
                });
                return false;
            }
        });
});
JS;
$this->registerJs($js);
?>