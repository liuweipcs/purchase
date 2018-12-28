<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\SupplierCheck;
use app\models\SupplierCheckNote;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商验货验厂列表');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="stockin-index">
    <?= $this->render('_search', ['model' => $searchModel,'view'=>'index']); ?>
    <p class="clearfix"></p>
    <div>
        <?= Html::a(Yii::t('app', '添加供应商'), 'create',[
            'id' => 'add-supplier',
            'class' => 'btn btn-success',
//            'data-toggle' => 'modal',
//            'data-target' => '#supplier-check',
        ]);?>

        <?= Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv']) ?>

        <?php Html::a(Yii::t('app', '导出验厂报告'), '#', [
            'id' => 'checkno',
            'class' => 'btn btn-warning',
        ]);?>
    </div>
    <div >
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
            'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
            'pager'=>[
                'options'=>['class' => 'pagination','style'=> "display:block;"],
                'class'=>\liyunfang\pager\LinkPager::className(),
                'pageSizeList' => [20, 50, 100, 200],
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
                        return ['value' => $model->id];
                    }

                ],
                [
                    'label'=>'申请信息',
                    'format'=>'raw',
                    'value'=>function($model){
                            $html  = '申请人：'.$model->apply_user_name.'</br>';
                            $create_time = !empty($model->create_time) ? date('Y-m-d',strtotime($model->create_time)) : '';
                            $html .= '申请时间：'.$create_time.'</br>';
                            $expect_time = !empty($model->expect_time) ? date('Y-m-d',strtotime($model->expect_time)) : '';
                            $html .= '期望时间：'.$expect_time.'</br>';
                            $confirm_time = !empty($model->confirm_time) ? date('Y-m-d',strtotime($model->confirm_time)) : '';
                            $html .= '确认时间：'.$confirm_time.'</br>';
                            $report_time = !empty($model->report_time) ? date('Y-m-d',strtotime($model->report_time)) : '';
                            $html .= '报告时间：'.$report_time;
                        return $html;
                    }
                ],
                [
                    'label'=>'类别',
                    'format'=>'raw',
                    'width'=>'60px',
                    'value'=>function($model){
                        $sup_html = $model->is_urgent==1 ? '<sup style="color: red">急</sup>':'';
                        return $model->getCheckType().$sup_html;
                    }
                ],
                [
                    'label'=>'次数',
                    'format'=>'raw',
                    'value'=>function($model){
                        return '第'.$model->check_times.'次';
                    }
                ],
                [
                    'label'=>'编号',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->check_code;
                    }
                ],
                [
                    'label'=>'供应商信息',
                    'format'=>'raw',
                    'value'=>function($model){
                        $supplierName =  $model->supplier ? "<span style='color: green'>".$model->supplier->supplier_name.'</span></br>' : "<span style='color: red'>".$model->supplier_name."</span></br>";
                        $supplierInfo ='供应商名称：'.$supplierName;
                        $supplierInfo .= '联系人：'.$model->contact_person.'</br>';
                        $supplierInfo .= '电话：'.$model->phone_number.'</br>';
                        $supplierInfo .= '地址：'.$model->contact_address;
                        return $supplierInfo;
                    }
                ],
                [
                    'label'=>'PO号',
                    'format'=>'raw',
                    'value'=>function($model){
                        $top = $model->order_type==2 ? '<sup style="color: red">非</sup>':'';
                        $html = Html::a(str_replace(',','<br/>',$model->pur_number.$top), ['sku-detail','id'=>$model->id], [
                            'class' => 'btn btn-xs sku-detail',
                            'data-toggle' => 'modal',
                            'data-target' => '#supplier-check',
                            'id'=>$model->id
                        ]);
                        return $html;
                    }
                ],
                [
                    'label'=>'申请备注',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->check_reason;
                    }
                ],
                [
                    'label'=>'验货次数',
                    'format'=>'raw',
                    'value'=>function($model){
                        $html = '检验次数：'.$model->times.'</br>';
                        if($model->times>1){
                            $html.='验货原因:'.\app\services\SupplierServices::getSupplierReviewReason($model->review_reason,'string').'</br>';
                            $html.='检验费用:'.$model->check_price;
                        }
                        return $html;
                    }
                ],
                [
                    'label'=>'检验人员',
                    'format'=>'raw',
                    'value'=>function($model){
                        $data = '';
                        if($model->checkUser){
                            $data = implode(',',array_column($model->checkUser,'check_user_name'));
                        }
                        return $data;
                    }
                ],
                [
                    'label'=>'检验状态',
                    'format'=>'raw',
                    'value'=>function($model){
                        return \app\services\SupplierServices::getSupplierCheckStatus($model->status);
                    }

                ],
                [
                    'label'=>'检验结果',
                    'value'=>function($model){
                        $arr = [
                            0=>'待确认',
                            1=>'合格',
                            2=>'不合格'
                        ];
                        return isset($arr[$model->judgment_results]) ? $arr[$model->judgment_results] : '';
                    }
                ],
                [
                    'label'=>'检验结论',
                    'format' => 'raw',
                    'value'=>function($model){
                        $html = '';
                        return $model->evaluate;
                        $res = SupplierCheckNote::getAuditNote(['check_id'=>$model->id]);
                        if (!empty($res)) {
                            foreach ($res as $key => $value) {
                                $html .= $value['role'] . '评价：' . $value['check_note'] . '<br />';
                            }
                        }
                        return $html;
                        // return $model->evaluate;
                    }
                ],



                [
                    'label'=>'改善措施',
                    'value'=>function($model){
                        return $model->improvement_measure;
                    }
                ],
                [
                    'label'=>'检验资料',
                    'format'=>'raw',
                    'value'=>function($model){
                        $downloadUrl = $model->check_type==1 ?  Html::a('<span class="glyphicon glyphicon-cloud-download" style="font-size:20px;color:coral" title="下载验厂模板"></span>',['download-export','checkId'=>$model->id], ['class' => "download",'type'=>'2','title' => '下载验厂模板']): Html::a('<span class="glyphicon glyphicon-cloud-download" style="font-size:20px;color:coral" title="下载验货模板"></span>',['downloadsku-export','checkId'=>$model->id]) ;
                        return $downloadUrl.'&nbsp'.Html::a('<span class="glyphicon glyphicon-search" style="font-size:20px;color:coral" title="查看资料"></span>','#', ['class' => "view",'checkId'=>$model->id,'type'=>'2','data-toggle' => 'modal', 'data-target' => '#supplier-check']);
                    }
                ],

                [
                    'class' => 'kartik\grid\ActionColumn',
                    'dropdown' => false,
                    'width'=>'180px',
                    'template' => '{end}{update}{delete}{confirm}{upload}',
                    'buttons'=>[
                        'confirm' => function ($url, $model, $key) {
                            return in_array($model->status,[1,2]) ? Html::a('<i class="glyphicon glyphicon-refresh"></i> 确认时间人员', ['confirm-date-user','id'=>$key], [
                                'class' => 'btn btn-xs confirm',
                                'data-toggle' => 'modal',
                                'data-target' => '#supplier-check-confirm',
                                'id' =>$key,
                            ]) : '';
                        },
                        'end' => function ($url, $model, $key) {

                            return in_array($model->status,[2,5]) ? Html::a('<i class="glyphicon glyphicon-refresh"></i> 提交结果', ['check-result','id'=>$key,'status'=>3], [
                                'class' => 'btn btn-xs check-result',
                                'data-toggle' => 'modal',
                                'data-target' => '#supplier-check-confirm',
                                'id' =>$key,
                            ]) :'';
                        },
                        'delete' => function ($url, $model, $key) {
                            //所有状态显示删除按钮
                            return  Html::a('<i class="glyphicon glyphicon-remove"></i>删除', ['delete','id'=>$key], [
                                'class' => 'btn btn-xs red delete',
                            ]);
                        },
                        'update' => function ($url, $model, $key) {
                            //待采购确认显示编辑按钮
                            return $model->status==6  ?  Html::a('<i class="glyphicon glyphicon-scissors"></i> 编辑', ['update','id'=>$key], [
                                'title' => Yii::t('app', '编辑 '),
                                'class' => 'btn btn-xs',
                                'id' =>$key,
                            ]) : '';
                        },
                        'upload' => function ($url, $model, $key) {
                            //无资料显示提交资料按钮
                            return $model->status==7  ?  Html::a('<i class="glyphicon glyphicon-scissors"></i> 提交资料', ['upload','checkId'=>$key], [
                                'title' => Yii::t('app', '提交资料 '),
                                'data-toggle' => 'modal',
                                'data-target' => '#supplier-check-confirm',
                                'class' => 'btn btn-xs upload',
                                'id' =>$key,
                            ]) : '';
                        },
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
            'hover' => false,
            'floatHeader' => false,
            'showPageSummary' => false,

            'exportConfig' => [
                GridView::EXCEL => [],
            ],
            'panel' => [
                //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
                'type'=>'success',
                //'before'=>false,
                //'after'=>false,
                //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
                //'footer'=>true
            ],
        ]);?>
    </div>
</div>

<?php
Modal::begin([
    'id' => 'supplier-check',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        'tabindex' => false
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

Modal::begin([
    'id' => 'supplier-check-confirm',
    'header' => '<h4 class="modal-title"></h4>',
    'footer' => false,
    'size'=>'modal-lg',
    'options'=>[
        'tabindex' => false
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();
$uploadUrl = Url::toRoute('upload');
$viewUrl = Url::toRoute('view');
$gradeUrl = Url::toRoute('change-grade');
$resultUrl = Url::toRoute('check-result');
$auditNoteUrl = Url::toRoute('audit-note');
$detail = Url::toRoute('sku-detail');
$measure = Url::toRoute('improvement-measure');
$noteUrl = Url::toRoute('create-note');
$confirmTimeUrl = Url::toRoute('update-confirm-time');
$js = <<<JS
$(function() {
    //双击编辑到货时间
    $("input[name='confirm-time']").dblclick(function(){
        $(this).removeAttr("readonly");
    });

    //失焦添加readonly
    $("input[name='confirm-time']").blur(function(){
        $(this).attr("readonly","true");
        var confirm_time = $(this).val();
        var id   = $(this).attr('supplier_id');
        var status   = $(this).attr('status');
        $.ajax({
                url:'{$confirmTimeUrl}',
                data:{confirm_time:confirm_time,id:id,status:status},
                type: 'get',
                dataType:'json',
                success:function (data) {
                    /*if(data==1) {
                        console.log('成功');
                    } else {
                        console.log('失败');
                    }*/
                }
            });

        $(this).attr("readonly","readonly")
    });
        $('.create-note').click(function () {
            var check_id = $(this).attr('check_id');
            var supplier_code = $(this).attr('supplier_code');

            $.get('{$noteUrl}',{check_id:check_id,supplier_code:supplier_code}, function (data) {
                $('#supplier-check').find('.modal-body').html(data);
            });
        });

});
$(document).on('click','.confirm',function() {
    $('#supplier-check-confirm').find('.modal-title').text('确认时间人员');
    $('#supplier-check-confirm').find('.modal-body').html('正在请求数据。。。。。。。');
    $.get($(this).attr('href'),{}, function (data) {
       $('#supplier-check-confirm').find('.modal-body').html(data);
    });
});

$(document).on('click','.upload','',function() {
    $('#supplier-check-confirm').find('.modal-title').text('提交资料');
    $('#supplier-check-confirm').find('.modal-body').html('正在请求数据。。。。。。。');
    $.get($(this).attr('href'),{},function(data) {
     $('#supplier-check-confirm').find('.modal-body').html(data);
    });
});
$(document).on('click','.view','',function() {
    var checkId = $(this).attr('checkId');
    var type = $(this).attr('type');
    $.get('{$viewUrl}',{checkId:checkId},function(data) {
       $('#supplier-check').find('.modal-body').html(data);
    });
});

$(document).on('dblclick','.check-grade',function() {
  $(this).attr('readonly',false);
});

$(document).on('click','.check-result','',function() {
    var checkId = $(this).attr('id');
    $('#supplier-check-confirm').find('.modal-title').text('确认验货结果');
    $('#supplier-check-confirm').find('.modal-body').html('正在请求数据。。。。。。。');
    $.get('{$resultUrl}',{checkId:checkId},function(data) {
        $('#supplier-check-confirm').find('.modal-body').html(data);
    });
});
$(document).on('click','.audit-note','',function() {
    var checkId = $(this).attr('id');
    $.get('{$auditNoteUrl}',{checkId:checkId},function(data) {
       $('#supplier-check').find('.modal-body').html(data);
    });
});
$(document).on('click','.improvement-measure','',function() {
    var checkId = $(this).attr('id');
    $.get('{$measure}',{checkId:checkId},function(data) {
        $('#supplier-check').find('.modal-body').html(data);
    });
});
$(document).on('click','.sku-detail','',function() {
    var checkId = $(this).attr('id');
    $.get('{$detail}',{check_id:checkId},function(data) {
        $('#supplier-check').find('.modal-body').html(data);
    });
});

$("#supplier-check").on("hidden.bs.modal", function() {
    $(this).removeData("modal");
});

$('#export-csv').click(function() {
    var ids = new Array();
    $("input[name='id[]']:checked").each(function(){
       ids.push($(this).val());
    });     
    window.location.href='/supplier-check/export-csv?ids='+ids.join(',');
});

JS;
$this->registerJs($js);
?>
