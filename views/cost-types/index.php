<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LogisticsCarrierSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '费用类型');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="logistics-carrier-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <p>
        <?= Html::a(Yii::t('app', '创建'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
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
            [
                'label'=>'费用代码',
                'attribute' => 'supplier_codes',
                "format" => "raw",
                'value'=>
                    function($model){

                        $data = $model->cost_code;
                        return $data;
                    },

            ],
            [
                'label'=>'英文名称',
                'attribute' => 'supplier_codes',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data  = $model->cost_en;
                        return $data;
                    },

            ],
            [
                'label'=>'中文名称',
                'attribute' => 'supplier_codes',
                'value'=>
                    function($model){
                        return  $model->cost_cn;   //主要通过此种方式实现
                    },

            ],
            [
                'label'=>'描述',
                'attribute' => 'supplier_codes',
                'value'=>
                    function($model){
                        return $model->notice;   //主要通过此种方式实现
                    },

            ],


            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => '{update} {delete}',
                'buttons'=>[
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 编辑', ['update','id'=>$key], [
                            'title' => Yii::t('app', '编辑'),
                            'class' => 'btn btn-xs red'
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {

                            return Html::a('<i class="glyphicon glyphicon-trash"></i> 删除', ['delete', 'id' => $key], [
                                'title' => Yii::t('app', '删除'),
                                'class' => 'btn btn-xs purple',
                                'data' => [
                                    'confirm' => '确定要删除么?',
                                ],
                            ]);


                    },],

            ],
        ],
        'pjax' => true,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => true,
        'showPageSummary' => false,
        'toggleDataOptions' =>[
            'maxCount' => 10000,
            'minCount' => 1000,
            'confirmMsg' => Yii::t(
                'app',
                'There are {totalCount} records. Are you sure you want to display them all?',
                ['totalCount' => number_format($dataProvider->getTotalCount())]
            ),
            'all' => [
                'icon' => 'resize-full',
                'label' => Yii::t('app', '所有'),
                'class' => 'btn btn-default',

            ],
            'page' => [
                'icon' => 'resize-small',
                'label' => Yii::t('app', '单页'),
                'class' => 'btn btn-default',

            ],
        ],
        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
</div>
