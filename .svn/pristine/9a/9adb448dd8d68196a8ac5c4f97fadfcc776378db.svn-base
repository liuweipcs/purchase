<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItems;
$this->title = '收快递列表';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="panel panel-default">
    <div class="panel-body">
        <?php echo $this->render('_search', ['model'=>$searchModel]); ?>
    </div>
</div>

<?= GridView::widget([
        'dataProvider' => $dataProvider,

        'options'=>[
            'id'=>'grid_purchase_order',
        ],


        'pager'=>[
            'options'=>['class' => 'pagination','style'=> "display:block;"],
            'class'=>\liyunfang\pager\LinkPager::className(),
            'pageSizeList' => [20, 50, 100, 200],
//                'options'=>['class'=>'hidden'],//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    //return //['value' => $model->pur_number];
                }

            ],

            [
                'label'=>'id',
                'attribute' => 'ids',
                'value'=>
                    function($model){
                        return  $model->id;   //主要通过此种方式实现
                    },

            ],
            [
                'label' => '快递商ID',
                'format' => 'raw',
                'value' => function($model) {
                    return $model->express_merchant_id;
                }
            ],
            [
                'label'=>'快递单号',
                "format" => "raw",
                'value'=> function($model) {
                    return $model->express_single;
                },

            ],
            [
                'label' => '采购单',
                'value' => function($model) {
                    return $model->relation_order_no;
                }
            ],
            [
                'label'=>'箱数',
                "format" => "raw",
                'value'=> function($model) {
                    return $model->box_number;
                },

            ],
            [
                'label'=>'到付费用',
                "format" => "raw",
                'value'=> function($model) {
                    return $model->pat_fee;
                },
            ],
            [
                'label'=>'状态',
                "format" => "raw",
                'value'=> function($model) {
                    $span = [
                        '1' => '<span class="label label-primary">正常</span>',
                        '2' => '<span class="label label-default">删除</span>',
                    ];
                    if(isset($span[$model->status])) {
                        return $span[$model->status];
                    }
                },

            ],
            [
                'label' => '是否加急',
                "format" => "raw",
                'value'=> function($model) {
                    $span = [
                        '1' => '<span class="label label-primary">是</span>',
                        '2' => '<span class="label label-default">否</span>',
                    ];
                    if(isset($span[$model->is_urgent])) {
                        return $span[$model->is_urgent];
                    }
                },

            ],
            [
                'label'=>'是否质检',
                "format" => "raw",
                'value'=> function($model, $url, $key) {
                    $span = [
                        '1' => '<span class="label label-primary">是</span>',
                        '2' => '<span class="label label-default">否</span>',
                    ];
//                    if(isset($span[$model->is_quality])) {
//                        return $span[$model->is_quality];
//                    }
                    $exist = \app\models\ArrivalRecord::find()->where(['like','express_no',$model->express_single])->exists();
                    return $exist ? $span['1']: $span['2'];
                },
            ],
            [
                'label' => '是否异常',
                "format" => "raw",
                'value'=> function($model, $url, $key) {
                    $span = [
                        '1' => '<span class="label label-primary">是</span>',
                        '2' => '<span class="label label-default">否</span>',
                    ];
                    if(isset($span[$model->is_abnormal])) {
                        return $span[$model->is_abnormal];
                    }
                },
            ],
            [
                'label' => '签收包裹重量',
                "format" => "raw",
                'value'=> function($model, $url, $key) {
                    return $model->weight;
                },
            ],
            [
                'label' => '签收时间',
                "format" => "raw",
                'value'=> function($model, $url, $key) {
                    return $model->add_time;
                },
            ]

        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            '{export}',
        ],


        'pjax' => false,
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
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
