<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use app\services\PurchaseOrderServices;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseAbnomalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'PO异常处理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-abnomal-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <p>
        <?php Html::a('批量处理', ['batch-handle'], ['class' => 'btn btn-success','id'=>'a-batch-handle','data-toggle'=>'modal','data-target'=>'#batch-handle-modal']) ?>
    </p>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'options'=>[
            'id'=>'grid_abnomal',
        ],
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'name'=>'id',
            ],

            'id',
            [
                'attribute'=>'express_no',
                "format" => "raw",
                'value'=>function($data){
                    $s = !empty($value['cargo_company_id']) ? $value['cargo_company_id'] : '';
                    $url = 'https://www.kuaidi100.com/chaxun?com=' . $s . '&nu=' . $data->express_no;
                    return "<a target='_blank' href='$url'>$data->express_no</a>";
                }
            ],

            [
                'attribute'=>'img',
                "format" => "raw",
                'value'=>function($data){
                    if($data->img)
                    {
                        $img =json_decode($data['img']);
                        $str ='';
                        foreach($img as $v)
                        {
                            $str .="<a href='{$v}' target='_blank'><img src='$v' width='100px'></a>";

                        }
                        return $str;
                    } else{

                    }

                }
            ],
            'buyer',
            'package_qty',
            'send_addr',
            'send_name',
            [
                'attribute'=>'status',
                'value'=>function($data){
                    return Yii::$app->params['status'][$data->status];
                }
            ],
            // 'is_del',
            'note',
            'create_user',
            'create_time',
            'handle_note',
            'handle_time',
            'handle_id',
            [
                'label'=>'推送状态',
                'attribute' => 'created_ats',
                "format" => "raw",
                'value'=>
                    function($model, $key, $index, $column){
                        return PurchaseOrderServices::getPush($model->is_push);
                    },
            ],
            [
                'header'=>'处理异常',
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update}',
                'buttons'=>[
                    'update'=> function ($url,$model){
                        if($model->status==3){
                            $url = "/purchase-abnomal/handle?id={$model->id}" ;
                            return Html::a("<span class='label label-success'>处理异常</span>", $url, ['title' => '处理异常', 'class' => 'handle','data-toggle'=>'modal','data-target'=>'#batch-handle-modal']);
                        }elseif($model->status==4){
                            return "<span class='label label-info'>已处理</span>";
                        }
                    }
                ],
            ],
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            //'{export}',
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
    ]);
    ?>
</div>
<?php
Modal::begin([
    'id' => 'batch-handle-modal',
    'header' => '<h4 class="modal-title">标识已处理</h4>',
    //'footer' => '<a href="#" class="btn btn-primary a-batch-handle" data-dismiss="modal">确认</a>',
]);
Modal::end();

$js = <<<JS
$(function(){
    //处理异常
    $("a.handle").click(function(){
        var url=$(this).attr("href");
        $.get(url, {},
            function (tmp) {
                $('#batch-handle-modal').find('.modal-body').html(tmp);
            }
        );
    });
});
JS;
$this->registerJs($js);
?>
