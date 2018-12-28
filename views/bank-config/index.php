<?php
use kartik\grid\GridView;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:49
 */
 echo  GridView::widget([
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
        [
            'label'=>'id',
            'attribute' => 'id',
            'value'=>
                function($model){
                    return $model->id;
                },

        ],
        [
            'label'=>'银行名称',
            'attribute' => 'supplier_codes',
            "format" => "raw",
            'value'=>
                function($model){
                    return $model->bank_name;
                },

        ],
        [
            'label'=>'状态',
            'attribute' => 'status',
            'format'=>'html',
            'value'=>
                function($model){
                    return \yii\helpers\Html::a($model->status==1 ? '启用' :'禁用' ,['change-status','id'=>$model->id],['class'=> $model->status==1 ? 'btn btn-success  btn-xs': 'btn btn-danger  btn-xs']);
                },

        ]
    ],
    'pjax' => true,
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'responsive' => true,
    'hover' => false,
    'floatHeader' => false,
    'showPageSummary' => false,
    'toggleDataOptions' =>[

    ],
    'exportConfig' => [
    ],
    'panel' => [
    ],
]);

 ?>