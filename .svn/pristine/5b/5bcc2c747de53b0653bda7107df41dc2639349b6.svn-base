<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItems;
use mdm\admin\components\Helper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '海外仓-采购计划单';
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="purchase-order-index">

        <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
        <p class="clearfix"></p>
        <p>

            <?php
            if(Helper::checkRoute('submit-audit'))
            {
                echo  Html::a('采购确认', ['#'], ['class' => 'btn btn-success', 'id' => 'submit-audits'/*, 'data-toggle' => 'modal', 'data-target' => '#create-modal',*/]);
            }
            ?>
            <?php
            if(Helper::checkRoute('purchase-merge'))
            {
                echo  Html::a('合并采购单', ['#'], ['class' => 'btn btn-success', 'id' => 'purchase-merge', 'data-toggle' => 'modal', 'data-target' => '#create-modal',]);
            }
            ?>
            <?php
            if(Helper::checkRoute('revoke-confirmation'))
            {
                echo Html::a('撤销确认', ['revoke-confirmation'], ['class' => 'btn btn-warning','id'=>'submit-audit',]);
            }
            ?>
            <?php
            if(Helper::checkRoute('revoke-purchase-order'))
            {
                echo Html::a('撤销采购单', ['revoke-purchase-order'], ['class' => 'btn btn-danger', 'id' => 'submit-audit']);
            }
            ?>
            <?php

            echo Html::a('打印采购单', ['print-data'], ['class' => 'btn btn-primary print', 'id' => 'submit-audit', 'target' => '_blank']);
//            ?>
<!--            --><?php
//            if(Helper::checkRoute('addproduct'))
//            {
//                Html::a('创建采购单', ['addproduct'], ['class' => 'btn btn-info'/*,'id'=>'create' ,'data-toggle' => 'modal', 'data-target' => '#create-modal'*/]);
//            }?>
            <?= Html::a('导出Excel', '#',['class' => 'btn btn-success export','id'=>'export-csv']) ?>



            <button type="button" class="btn btn-success" id="compact-confirm">合同采购确认</button>





            <!--<p class="clearfix"></p>
    <div class="form-group">
        <div class="col-md-3" style="width: auto;">
            <label for="name">选择导出数量</label>
            <select class="form-control" style="width: auto; display:inline" name="demand_purchase[demand_purchase]" id="limit">
                <option value="">请选择导出多少数据</option>
                <option value="100">100</option>
                <option value="500">500</option>
                <option value="1000">1000</option>
                <option value="2000">2000</option>
                <option value="5000">5000</option>
                <option value="all">所有</option>
            </select>
        </div>
        <div class="col-md-1">
            <?/*= Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv1']) */?>
        </div>
    </div>
    <p class="clearfix"></p>-->
        <h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span>温馨小提示:<span style="color: red">谁执行了采购确认,采购员将是谁<i class="fa fa-fw fa-smile-o"></i></h4>
        </p>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'options'=>[
                'id'=>'grid_purchase_order',
            ],
            'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
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
                    'class' => 'yii\grid\CheckboxColumn',
                    'name'=>"id" ,
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return ['value' => $model->pur_number,'create_type'=>$model->create_type,'purchas_status'=>$model->purchas_status,'warehouse_code'=>$model->warehouse_code,'is_transit'=>$model->is_transit,'transit_warehouse'=>$model->transit_warehouse,'purchase_type'=>$model->purchase_type];
                    }

                ],

                /* [
                     'label'=>'id',
                     'format'=>'raw',
                     'attribute' => 'ids',
                     'value'=>
                         function($model){
                             return  $model->id;
                         },

                 ],*/

                [
                    'label'=>'PO号',
                    'attribute' => 'pur_numbers',
                    "format" => "raw",
                    'value'=> function($model){
                        return Html::a($model->pur_number, ['#'],['data-id' => $model->id,
                            //'data-toggle' => 'modal',
                            //'data-target' => '#create-modal',
                            'value' =>$model->pur_number,
                            'class'=>'submitaudits',
                            'target' => '_blank'
                        ]);
                    },

                ],
                [
                    'label'=>'创建类型',
                    'attribute' => 'create_type',
                    "format" => "raw",
                    'value'=> function($model){
                        if ($model->create_type == 1) {
                            $data = '<span class="label label-success">系统</span>&nbsp;&nbsp;';

                        } elseif ($model->create_type == 2) {
                            $data = '<span class="label label-info">手工</span>&nbsp;&nbsp;';
                        } else {
                            $data = '';
                        }
                        return $data;
                    },
                ],
                [
                    'label'=>'订单状态',
                    'attribute' => 'pur_numbers',
                    "format" => "raw",
                    'value'=> function($model){
                        if (isset($model->audit_note)) {
                            return $data = '<span class="label label-danger">'.PurchaseOrderServices::getPurchaseStatusText($model->purchas_status).'（驳回）</span>&nbsp;&nbsp;';
                        } else {
                            return $data = '<span class="label label-primary">'.PurchaseOrderServices::getPurchaseStatusText($model->purchas_status).'</span>&nbsp;&nbsp;';
                        }

                    },

                ],
                [
                    'label'=>'仓库',
                    'attribute' => 'ids',
                    "format" => "raw",
                    'value'=>
                        function($model){

                            if(!empty($model->is_transit) && $model->is_transit==1 && $model->transit_warehouse)
                            {
                                $data   = BaseServices::getWarehouseCode($model->transit_warehouse);
                                $data  .=!empty($model->warehouse_code)?'-'.BaseServices::getWarehouseCode($model->warehouse_code):'<br/>';

                            } else {
                                $data  =!empty($model->warehouse_code)?BaseServices::getWarehouseCode($model->warehouse_code):'<br/>';
                            }
                            return  $data;   //主要通过此种方式实现
                        },

                ],
                'buyer',

                [
                    'label'=>'供应商名称',
                    'value'=>'supplier_name',
                ],

                [
                    'label'=>'SKU数量',
                    'value'=> function($model){
                        return PurchaseOrderItems::find()->where(['pur_number'=>$model->pur_number])->count('id');
                    },
                ],

                [
                    'label'=>'采购数量',
                    'value'=> function($model){
                        $ctq=PurchaseOrderItems::find()->where(['pur_number'=>$model->pur_number])->sum('ctq');
                        if(!empty($ctq)){
                            return $ctq;
                        }else{
                            return PurchaseOrderItems::find()->where(['pur_number'=>$model->pur_number])->sum('qty');
                        }
                    },
                ],

                [
                    'label'=>'总金额( RMB )',
                    "format" => "raw",
                    'value'=>function($model){
                        return round(PurchaseOrderItems::getCountPrice($model->pur_number),2);
                    },
                ],

                [
                    'label'=>'运费',
                    'value'=>function($model){


                        $freight = 0;
                        $freight1 = \app\models\PurchaseOrderShip::find()->where(['pur_number'=>$model->pur_number])->sum('freight');
                        $freight2 = 0;
                        if(!empty($model->purchaseOrderPayType)) {
                            $freight2 = $model->purchaseOrderPayType->freight ? $model->purchaseOrderPayType->freight : 0;
                        }
                        if($freight2) {
                            $freight = $freight2;
                        } else {
                            $freight = $freight1;
                        }

                        return $freight;

                    },
                ],

                [
                    'label' => '结算方式',
                    'value'=>function($model){
                        $atype=\app\services\SupplierServices::getSettlementMethod($model->account_type);
                        return $model->account_type ? $atype  : '';
                    },
                ],

                [
                    'label'=>'订单号',
                    "format" => "raw",
                    'value'=>function($model){
                        $findone=\app\models\PurchaseOrderOrders::findOne(['pur_number'=>$model->pur_number]);
                        return !empty($findone) ? $findone->order_number : '';
                    },
                ],

                [
                    'label'=>'确认备注',
                    'attribute' => 'created_ats',
                    "format" => "raw",
                    'value'=>
                        function($model, $key, $index, $column){

                            return $model->orderNote['note'];
                        },
                ],
                [
                    'label'=>'审核备注',
                    'attribute' => 'created_ats',
                    "format" => "raw",
                    'value'=>
                        function($model, $key, $index, $column){

                            return $model->audit_note;
                        },
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'dropdown' => false,
                    'width'=>'180px',//{update}
                    'template' => '{print} {download}{export}{edit-note}{bind-compact}',
                    'buttons'=>[
                        'prints' => function ($url, $model, $key) {
                            return Html::a('<i class="glyphicon glyphicon-print"></i> 打印采购合同', ['purchase-order/print','id'=>$key,'pur_number'=>$model->pur_number], [
                                'title' => Yii::t('app', '打印采购合同'),
                                'class' => 'btn btn-xs red',
                                'target'=>'_blank'
                            ]);
                        },
                        'update' => function ($url, $model, $key) {
                            return Html::a('<i class="glyphicon glyphicon-pencil"></i> 修改供应商', ['update-supplier','id'=>$key,], [
                                'title' => Yii::t('app', '修改供应商'),
                                'class' => 'btn btn-xs red',
                                //'target'=>'_blank'
                            ]);
                        },
                        'edit-note' => function ($url, $model, $key) {
                            return Html::a('<i class="glyphicon glyphicon-pencil"></i> 修改备注', ['edit-note','id'=>$model->pur_number,], [
                                'title' => Yii::t('app', '修改备注'),
                                'class' => 'btn btn-xs red',
                                //'target'=>'_blank'
                            ]);
                        },



                         'bind-compact' => function($url, $model, $key) {
	 return Html::button('<i class="glyphicon glyphicon-paperclip"></i> 关联已有合同', [
		 'data-id' => $model->pur_number,
		 'title' => Yii::t('app', '关联已有合同'),
		 'class' => 'btn btn-link btn-xs bind-compact'
	 ]);
 },
















//                    'download' => function ($url, $model, $key) {
//                        return Html::a('<i class="glyphicon glyphicon-arrow-down"></i> 下载采购合同', ['purchase-order/download-zip','id'=>$key], [
//                            'title' => Yii::t('app', '下载采购合同 '),
//                            'class' => 'btn btn-xs purple'
//                        ]);
//                    },
//                    'export' => function ($url, $model, $key) {
//                        return Html::a('<i class="glyphicon glyphicon-export"></i> 导出PDF合同', ['purchase-order/export-pdf','id'=>$key], [
//                            'title' => Yii::t('app', '更新 '),
//                            'class' => 'btn btn-xs purple'
//                        ]);
//                    },
                    ],

                ],
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
    </div>
<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$requestUrl = Url::toRoute(['purchase-order/view']);
$sumbitUrl  = Url::toRoute(['submit-audit']);
$exportUrl  = Url::toRoute('export');
$mergeUrl = Url::toRoute(['purchase-merge']);
$msg ='请选择采购单';
$js = <<<JS

            $(document).on('click', '#submit-audit', function () {
                    var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
                    if(ids==''){

                        alert('{$msg}');
                        return false;
                    }else{
                        var url = $(this).attr("href");
                        if($(this).hasClass("print"))
                        {
                            url = '/purchase-order/print-data';
                        }
                        url     = url+'?ids='+ids;
                        $(this).attr('href',url);
                    }
            });

    $(document).on('click', '#views', function () {


        $.get('{$requestUrl}', {id:$(this).attr('value'),status:$(this).attr('status'),currency_code:$(this).attr('currency_code')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    //修改供应商
       $(document).on('click',"#update-supplier",function(){
            var url=$(this).attr("href");
            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            if(ids==''){
              $('.modal-body').html('{$msg}');
               return false;
            }else{
               $.get(url, {id: ids},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                 );
            }
        });
    $(document).on('click', '#logs', function () {

        $.get($(this).attr('href'), {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    
    
        $(document).on('click', '.submitaudits', function () {


            var id=$(this).attr('data-id');
            var url = $(this).attr("href");
            url = '/overseas-purchase-order-confirm/submit-audit';
            url     = url+'?id='+id;
            $(this).attr('href',url);
        });

    
    
    
    $(document).on('click', '#submit-audits', function () {

            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            if (ids&& ids.length !=0)
            {
              
                     /*$.get('{$sumbitUrl}', {id: ids},
                                function (data) {
                                    $('.modal-body').html(data);
                                }
                     );*/
                     
                     var url='{$sumbitUrl}?id='+ids;
                     window.open(url);

            } else {
                    $('.modal-body').html('{$msg}');
                   return false;

            }

    });
    //解决模态框js只加载一次的问题
    $("#create-modal").on("hidden", function() {
        $(this).removeData("modal");
    });
    $(document).on('click', '#purchase-merge', function () {
            var purNumber = new Array();
            $('[name="id[]"]').each(function(){
                if($(this).is(':checked')){
                    purNumber.push($(this).val());
                }
            });
            if(checkData() == false){
                $('#create-modal').modal('show');
                $('.modal-body').html('采购单关键属性不一致不能合并');
            }else {
                if (purNumber&& purNumber.length >1)
                {
                    var purData = purNumber.join(',');
                    $.get('{$mergeUrl}', {purNumber:purData},
                        function (data) {
                            $('.modal-body').html(data);
                        }
                    );
                }else {
                    $('#create-modal').modal('show');
                    $('.modal-body').html('至少合并两个采购单');
                }
            }

    });

    var checkData = function(){
        var data = new Array();
        $('[name="id[]"]').each(function(){
            if($(this).is(':checked')){
                var createType = $(this).attr('create_type');
                var purchasStatus = $(this).attr('purchas_status');
                var warehouseCode = $(this).attr('warehouse_code');
                var isTransit = $(this).attr('is_transit');
                var transitWarehouse = $(this).attr('transit_warehouse');
                var purchaseType = $(this).attr('purchase_type');
                var rowData = createType+','+purchasStatus+','+warehouseCode+','+isTransit+','+transitWarehouse+','+purchaseType;
                data.push(rowData);
            }
        });
        if($.unique(data).length ==1){
            return true;
        }else{
            return false;
        }
    }
 $("#create-modal").on("hidden.bs.modal", function() {

  $(this).removeData("bs.modal");

});
 
     //PHPExcel 导出
      $(document).on('click','.export',function() {
          var id = $('#grid_purchase_order').yiiGridView('getSelectedRows');
          // var limit = $('#limit').val();
/*           if (id=='') {
               alert('请先选择!');
               return false;
          } */
          $(this).attr('href',"{$exportUrl}" + "?id=" + id);
    });
      
      
      
      
      
      
      
      
          // 合同采购确认（唯一入口） 
    $(document).on('click', '#compact-confirm', function () {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids.length > 0) {
            var url = 'compact-confirm?ids=' +ids;
            window.open(url);
        } else {
            layer.alert('你没有选择订单');
            return false;
        }
    }); 
    
    // bind compact
    $(document).on('click', '.bind-compact', function() {
        var pur_number = $(this).attr('data-id');
          var name=prompt("请输入一个合同号","");
          if (name!=null && name!="") {
              location.href = '/overseas-purchase-order-confirm/bind-compact?pur_number=' + pur_number + '&compact_number=' + name;
          }
    }); 
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      

JS;
$this->registerJs($js);
?>