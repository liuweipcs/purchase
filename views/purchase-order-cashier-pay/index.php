<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use yii\bootstrap\Modal;
use app\models\PurchaseOrder;
$this->title = '出纳付款';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="panel panel-default" style="position: fixed;margin-right: 15px;z-index: 10;">

    <div class="panel-body">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="panel-footer">

    <?php

    if($source == 1) {

        /*echo Html::button('时间段导出', ['class' => 'btn btn-success print c-bulk-execl', 'data-type' => '1']);*/
        echo Html::button('勾选导出', ['class' => 'btn btn-success print c-bulk-execl', 'data-type' => '2']);

        echo Html::button('批量驳回', ['class' => 'btn btn-success print bulk-reject','source' => 1]);



        
    }
    ?>

        <?php
        if($source == 2) {
            echo Html::a('批量付款', ['bulk-payment'], [
                'class' => 'btn btn-success',
                'id' => 'bulk-payment',
                'data-toggle' => 'modal',
                'data-target' => '#create-modal'
            ]);

            echo Html::button('时间段导出', ['class' => 'btn btn-success print bulk-execl', 'data-type' => '1']);
            echo Html::button('勾选导出', ['class' => 'btn btn-success print bulk-execl', 'data-type' => '2']);



            echo Html::button('<span class="glyphicon glyphicon-cloud-upload"></span> 1688在线付款', [
                'type' => 'button',
                'id' => 'online-payment',
                'class' => 'btn btn-warning'
            ]);


            echo Html::button('<span class="glyphicon glyphicon-cloud-upload"></span> 1688超级买家在线付款', [
                'type' => 'button',
                'id' => 'super-online-payment',
                'class' => 'btn btn-warning'
            ]);

            echo Html::button('批量驳回', ['class' => 'btn btn-success print bulk-reject','source' => 2]);



        }
        ?>
        <?php
            if(\mdm\admin\components\Helper::checkRoute('ufxfuiou-pay')){
                echo Html::a('<span class="glyphicon glyphicon-cloud-upload"></span> 富友在线付款',['ufxfuiou-pay'],[
                    'data-toggle' => 'modal',
                    'data-target' => '#create-modal',
                    'type' => 'button',
                    'id' => 'fuiou-pay',
                    'class' => 'btn btn-warning'
                ]);
            }
        ?>

    </div>

</div>

<div style="margin-top: 235px;padding-left: 8px;">
    <h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span>  温馨小提示：</h4>
    <p style="color: red">1：请注意单号PO->国内，ABD->海外，FBA->FBA。</p>
    <p style="color: red">2：帐期->银行卡转帐,款到发货->支付宝。不同类型请分开付款。</p>
    <p style="color: red">3： 1688批量在线付款每次只针对同一个申请人的请款数据，且没有拍单号的将会过滤掉，建议每次10个单。</p>
</div>

<?php

if($source == 1):  ?>

<div class="btn-group" style="margin-bottom: 10px;">
    <span class="btn btn-danger" disabled="disabled">合同单</span>
    <a href="?source=2" class="btn btn-default">其它采购单</a>
</div>

<?php

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => [
            'id' => 'grid_purchase_order'
    ],
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
    'pager' => [
        'options' => ['class' => 'pagination', 'style' => 'display:block;'],
        'class' => \liyunfang\pager\LinkPager::className(),
        'pageSizeList' => [10, 20, 30, 50, 100],
        'firstPageLabel' => '首页',
        'prevPageLabel' => '上一页',
        'nextPageLabel' => '下一页',
        'lastPageLabel' => '末页',
    ],
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'name'=>"id" ,

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
            'label' => '申请人',
            "format" => "raw",
            'value' =>
                function($model) {
                    if($model->applicant) {
                        $name = PurchaseOrderServices::getEveryOne($model->applicant);
                        return "<a href='?applicant={$model->applicant}' title='点击搜索'>".$name."</a>";
                    }
                },
        ],
        [
            'label' => '单号',
            'format' => 'raw',
            'value' => function($model) {
                $data = "<p style='margin-bottom: 8px;'>".PurchaseOrderServices::getPayStatusType($model->pay_status)."</p>";
                $data .= "<p>合同号：{$model->pur_number}</p>";
                $data .= "<p>请款单号：{$model->requisition_number}</p>";
                return $data;
            }
        ],
        [
            'label' => '请款信息',
            'format' => 'raw',
            'value' => function($model) {
                $data = "<p>申请金额：<strong style='color:red;'>{$model->pay_price}</strong></p>";
                $data .= "<p>请款名称：{$model->pay_name}</p>";

                if(!empty($model->purchase_account)) {
                    $data .= "<p>付款账号：{$model->purchase_account}</p>";
                }
                if(!empty($model->pai_number)) {
                    $data .= "<p>拍单号：{$model->pai_number}</p>";
                }
                return $data;
            }
        ],
        [
            'label'=>'基本信息',
            'attribute' => 'pur_numbers',
            "format" => "raw",
            'value'=>
                function($model, $key, $index, $column){
                    $supplierName = !empty($model->purchaseCompact[0]->purchaseOrder->supplier_name)?$model->purchaseCompact[0]->purchaseOrder->supplier_name:null;
                    $supplierCode = !empty($model->purchaseCompact[0]->purchaseOrder->supplier_code)?$model->purchaseCompact[0]->purchaseOrder->supplier_code:null;
                    if (empty($supplierName)) $supplierName = BaseServices::getSupplierName($model->supplier_code);
                    $sub_html = \app\models\SupplierSearch::flagCrossBorder(true,$model->supplier_code);
                    $data = '<p>供应商：'. $supplierName .$sub_html.'</p>';
                    $data .=!empty($model->pay_type)?Yii::t('app','支付方式').'：'.SupplierServices::getDefaultPaymentMethod($model->pay_type)."<br/>":'';
                    $data .=!empty($model->settlement_method)? Yii::t('app','结算方式').'：'.SupplierServices::getSettlementMethod($model->settlement_method)."<br/>":'';
                    $data.=Html::a('<span class="fa fa-fw fa-comment" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单备注"></span>', ['purchase-order/get-purchase-note'],['id' => 'note',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal','value' =>$model->pur_number,
                    ]);
                    return $data;
                },

        ],
        [
            'label' => '备注',
            'headerOptions' => ['width' => '300px'],
            "format" => "raw",
            'value' =>
                function($model) {
                    if($data = $model->orderNote['note']) {
                        return $data;
                    }
                },
        ],
        [
            'label' => '操作人/时间',
            "format" => "raw",
            'value' =>
                function($model) {
                    $data = !empty($model->approver)?Yii::t('app','审批:').BaseServices::getEveryOne($model->approver).'<br/>':Yii::t('app','审批人:').''.'<br/>';
                    $data .= !empty($model->payer)?Yii::t('app','付款人:').BaseServices::getEveryOne($model->payer).'<br/>':Yii::t('app','付款人:').''.'<br/>';
                    return $data;
                },

        ],
        [
            'label'=>'操作时间',
            'attribute' => 'ids',
            "format" => "raw",
            'value'=>
                function($model){
                    $data  = Yii::t('app','申请:').$model->application_time.'<br/>';
                    $data .= Yii::t('app','审批:').$model->processing_time.'<br/>';
                    $data .= Yii::t('app','付款:').$model->payer_time;
                    return $data;
                },

        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => false,
            'width'=>'180px',
            'template' => '<p>{complete}</p><p>{view}</p><p>{note}</p>{info}',
            'buttons' => [
                'complete' => function ($url, $model, $key) {
                    $arr = ['0', '5', '2'];
                    if(!in_array($model->pay_status, $arr)) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 付款', ['view', 'id' => $key], [
                            'title' => Yii::t('app', '付款'),
                            'class' => 'btn btn-xs red',
                        ]);
                    }
                },
                'view' => function ($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 合同明细', ['purchase-compact/view', 'cpn' => $model->pur_number,'requisition_number'=>$model->requisition_number], [
                        'title' => Yii::t('app', '合同明细'),
                        'class' => 'btn btn-xs red',
                        'target' => '_blank'
                    ]);
                },
                'note' => function ($url, $model, $key) {
                    return Html::a('<i class="fa fa-fw fa-comment"></i> 添加合同备注', ['purchase-compact/add-compact-note', 'cpn' => $model->pur_number], [
                        'title' => Yii::t('app', '添加采购单备注'),
                        'class' => 'btn btn-xs c-note',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal'
                    ]);
                },
                'info'=>function($url, $model, $key){
                    if(in_array($model->pay_status,[5,13])){
                        return Html::a('<i class="fa fa-fw fa-comment"></i> 查询富友付款状态', ['get-fuiou-pay-info', 'requisition_number' => $model->requisition_number], [
                            'title' => Yii::t('app', '查询富友付款状态'),
                            'class' => 'btn btn-xs u-info',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal'
                        ]);
                    }
                }
            ],

        ],
    ],
    'containerOptions' => ["style"=>"overflow:auto"],
    'toolbar' =>  [],
    'bordered' => true,
    'condensed' => true,
    'responsive' => true,
    'hover' => true,
    'floatHeader' => true,
    'showPageSummary' => false,
    'panel' => [
        'type' => 'success'
    ],
]); ?>


<?php else: ?>


<div class="btn-group" style="margin-bottom: 10px;">
    <a href="?source=1" class="btn btn-default">合同单</a>
    <span class="btn btn-danger" disabled="disabled">其它采购单</span>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'options'=>[
        'id'=>'grid_purchase_order',
    ],
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
    'pager' => [
        'options' => ['class' => 'pagination', 'style' => 'display:block;'],
        'class' => \liyunfang\pager\LinkPager::className(),
        'pageSizeList' => [10, 20, 30, 50, 100],
        'firstPageLabel' => '首页',
        'prevPageLabel' => '上一页',
        'nextPageLabel' => '下一页',
        'lastPageLabel' => '末页',
    ],
                'columns' => [
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        'name'=>"id"
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
                        'label' => '申请人',
                        "format" => "raw",
                        'value' =>
                            function($model) {
                                if($model->applicant) {
                                    $name = PurchaseOrderServices::getEveryOne($model->applicant);
                                    return "<a href='?applicant={$model->applicant}' title='点击搜索'>".$name."</a>";
                                }
                            },
                    ],
                    [
                        'label' => '单号',
                        "format" => "raw",
                        'value' =>
                            function($model) {

                                $order_number = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->platform_order_number : '';
                                $order_account = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->purchase_acccount : '';

                                $data = "<p>".PurchaseOrderServices::getPayStatusType($model->pay_status)."</p>";
                                $data .= "<p>采购单号：".$model->pur_number."</p>";
                                $data .= "<p>申请单号：".$model->requisition_number."</p>";
                                $data .= "<p>账号：".$order_account."</p>";
                                $data .= "<p>拍单号：".$order_number."</p>";
                                return $data;
                            },
                    ],
                    [
                        'label' => '请款金额/运费',
                        "format" => "raw",
                        'value'=>
                            function($model){
                                return \app\models\PurchaseOrderPay::getPrice($model);
                            },
                    ],
                    [
                        'label'=>'基本信息',
                        'attribute' => 'pur_numbers',
                        "format" => "raw",
                        'value'=>
                            function($model, $key, $index, $column){
                                $sub_html = \app\models\SupplierSearch::flagCrossBorder(true,$model->supplier_code);
                                $data = Yii::t('app','供应商'). '：' . PurchaseOrder::getSupplierName($model->pur_number) .$sub_html. "<br />";

                                $data .=!empty($model->pay_type)?Yii::t('app','支付方式').'：'.SupplierServices::getDefaultPaymentMethod($model->pay_type)."<br/>":'';
                                $data .=!empty($model->settlement_method)? Yii::t('app','结算方式').'：'.SupplierServices::getSettlementMethod($model->settlement_method)."<br/>":'';
                                $data.=Html::a('<span class="fa fa-fw fa-comment" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单备注"></span>', ['purchase-order/get-purchase-note'],['id' => 'note',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal','value' =>$model->pur_number,
                                ]);
                                return $data;
                            },

                    ],
                    [
                        'label' => '备注',
                        'attribute' => 'ids',
                        'headerOptions' => ['width'=>'300px'],
                        "format" => "raw",
                        'value' =>
                            function($model) {
                                $where=['requisition_number'=>$model->requisition_number,'pur_number'=>$model->pur_number];
                                $res = \app\models\PurchaseOrderPay::getCreateNotice($where,1);
                                if (!empty($res) && isset($model->purchaseOrder->purchase_type) && ($model->purchaseOrder->purchase_type==2)) return implode('；', $res);
                                return $model->orderNote['note'].'<br/>';
                            },
                    ],
                    [
                        'label'=>'付款回单',
                        'format'=>'raw',
                        'value'=>function($model){
                            if(empty($model->images)){
                                return '无回单';
                            }else{
                                $imagesArray = json_decode($model->images);
                                $html='';
                                foreach ($imagesArray as $key=>$value){
                                    $html .=Html::a('付款回单'.$key,$value,['target'=>'blank']).'<br/>';
                                }
                                return $html;
                            }
                        }
                    ],
                    [
                        'label'=>'操作人',
                        'attribute' => 'ids',
                        "format" => "raw",
                        'value'=>
                            function($model){
                                $data = !empty($model->approver)?Yii::t('app','审批人:').BaseServices::getEveryOne($model->approver).'<br/>':Yii::t('app','审批人:').''.'<br/>';
                                $data .= !empty($model->payer)?Yii::t('app','付款人:').BaseServices::getEveryOne($model->payer).'<br/>':Yii::t('app','付款人:').''.'<br/>';
                                return $data;
                            },

                    ],
                    [
                        'label'=>'操作时间',
                        'attribute' => 'ids',
                        "format" => "raw",
                        'value'=>
                            function($model){
                                $data  = Yii::t('app','申请:').$model->application_time.'<br/>';
                                $data .= Yii::t('app','审批:').$model->processing_time.'<br/>';
                                $data .= Yii::t('app','付款:').$model->payer_time;
                                return $data;
                            },

                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'dropdown' => false,
                        'width'=>'180px',
                        'template' => '{complete}{view}{remarks}{info}',
                        'buttons'=>[
                            'complete' => function ($url, $model, $key) {
                                $arr =['0','5','2'];
                               //if(!in_array($model->pay_status,$arr)) {
                                    return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 付款', ['view', 'id' => $key], [
                                        'title'       => Yii::t('app', '采购明细'),
                                        'class'       => 'btn btn-xs red',
                                        'id'          => 'views',
                                        'data-toggle' => 'modal',
                                        'data-target' => '#create-modal',
                                    ]);
                                //}
                            },
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 采购明细', ['purchase-order/view','id'=>$model->pur_number,'requisition_number'=>$model->requisition_number], [
                                    'title' => Yii::t('app', '采购明细'),
                                    'class' => 'btn btn-xs red',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal',
                                    'id'=>'views',
                                ]);
                            },
                            'remarks' => function ($url, $model, $key) {
                                return Html::a('<i class="fa fa-fw fa-comment"></i> 添加采购单备注', ['purchase-order/add-purchase-note', 'pur_number' => $model->pur_number,'flag'=>3], [
                                    'title' => Yii::t('app', '添加采购单备注'),
                                    'class' => 'btn btn-xs note',
                                    'target'=>'_blank',
                                    //'data-toggle' => 'modal',
                                    //'data-target' => '#create-modal',
                                    'id' => 'return',
                                ]);

                            },
                            'info'=>function($url, $model, $key){
                                if(in_array($model->pay_status,[5,13])){
                                    return Html::a('<i class="fa fa-fw fa-comment"></i> 查询富友付款状态', ['get-fuiou-pay-info', 'requisition_number' => $model->requisition_number], [
                                        'title' => Yii::t('app', '查询富友付款状态'),
                                        'class' => 'btn btn-xs u-info',
                                        'data-toggle' => 'modal',
                                        'data-target' => '#create-modal'
                                    ]);
                                }
                            }
                        ],

                    ],
                ],
                'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
                'toolbar' =>  [

                    // '{export}',
                ],


                'pjax' => false,
                'bordered' => true,
                'striped' => false,
                'condensed' => true,
                'responsive' => true,
                'hover' => true,
                'floatHeader' => true,
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


<?php endif; ?>








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
$batchUrls  = Url::toRoute('export-cvs');
$js = <<<JS
    //查看明细
    $(document).on('click', '#submit-audit', function () {
        $.get($(this).attr('href'), {id:$(this).attr('value'),status:$(this).attr('status'),currency_code:$(this).attr('currency_code')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    //付款
    $(document).on('click', '#views', function () {
        $('#create-modal .modal-body').html('正在请求中。。。。');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '#note', function () {

        $.get($(this).attr('href'), {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
     $(document).on('click', '#return', function () {

        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
     $(document).on('click','.u-info',function() {
         $('.modal-body').html('正在请求数据》》》》》');
        $.get($(this).attr('href'),{},function(data) {
            $('.modal-body').html(data);
        });
     });
    //批量付款
    $(document).on('click', '#bulk-payment', function () {
        $('#create-modal .modal-body').html('正在请求中。。。。');
        var keys = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(keys.length==0)
        {
            $('.modal-body').html('请勾选要付款的订单');
            return false;
        } else{
            $.get($(this).attr('href'), {id:keys},
                function (data) {
                    $('.modal-body').html(data);
                }
            );

        }


    });
    
    //富友在线支付
    $(document).on('click','#fuiou-pay',function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids.length==0){
            $('.modal-body').html('请勾选要付款的订单');
            return false;
        }else {
            $('#create-modal .modal-body').html('正在请求中。。。。');
           $.get($(this).attr('href'), {ids:ids},
                function (data) {
                    $('#create-modal .modal-body').html(data);
                }
            ); 
        }
    })
    
    
    $(document).on('click', '#alipay', function() {
        
        var keys = $('#grid_purchase_order').yiiGridView('getSelectedRows');

        if(keys.length == 0) {
            $('.modal-body').html('请勾选要付款的订单');
            return false;
        } else {
            $.post($(this).attr('href'), {ids: keys}, function(data) {
                $('#create-modal .modal-body').html(data);
            });
        }
        
    });
    
    
    
    // batch export
    $(document).on('click', '.bulk-execl', function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
     
        var start = $("input[name='daterangepicker_start']").val(); 
        var end = $("input[name='daterangepicker_end']").val(); 
        var type = $(this).attr('data-type');
        if(type == 1) {
            if(end == '') {
                 layer.alert('请选择时间段');
                 return false;
            }
        } else {
            if(ids == '') {
                layer.alert('请先勾选要导出的数据');
                return false;
            }
        }
        var url = '/purchase-order-cashier-pay/export-cvs';
        url = url + '?start='+start + '&end=' + end + '&ids=' + ids+ '&type=' + type;
        location.href = url;
    });
    
    
    // 1688 payment online
    $(document).on('click', '#online-payment', function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids.length == 0) {
            layer.alert('请选择要付款的请款单');
            return false;
        }
        var str = ids.join(',');
        window.open("/purchase-order-cashier-pay/online-payment?ids=" + str);
    });
    
    // 1688 payment online
    $(document).on('click', '#super-online-payment', function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids.length == 0) {
            layer.alert('请选择要付款的请款单');
            return false;
        }
        var str = ids.join(',');
        window.open("/purchase-order-cashier-pay/super-online-payment?ids=" + str);
    });
    
    
    
    
    
    
    // add compact note 
    $(document).on('click', '.c-note', function() {
        $('.modal-body').html('Waiting...');
        $.get($(this).attr('href'), function(data) {
            $('.modal-body').html(data);
        });
    });
    
    // compact batch export
    $(document).on('click', '.c-bulk-execl', function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');

        var start = $("input[name='daterangepicker_start']").val(); 
        var end = $("input[name='daterangepicker_end']").val(); 
        var type = $(this).attr('data-type');
        if(type == 1) {
            if(end == '') {
                 layer.alert('请选择时间段');
                 return false;
            }
        } else {
            if(ids == '') {
                layer.alert('请先勾选要导出的数据');
                return false;
            }
        }
        var url = '/purchase-order-cashier-pay/export-cvs';
        url = url + '?start='+start + '&end=' + end + '&ids=' + ids+ '&type=' + type + '&s=1';
        location.href = url;
    });
    
    // 批量驳回
    $(document).on('click', '.bulk-reject', function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
       
        if(ids == '') {
            layer.alert('请先勾选要驳回的数据');
            return false;
        }
        var source = $(this).attr('source');
        layer.prompt({title: '驳回备注', value: '', formType: 2}, function (remark, index) {
            $.ajax({
                url:'/purchase-order-cashier-pay/batch-reject',
                data:{ids:ids,payment_notice:remark,source:source},
                type: 'post',
                dataType:'json',
                success: function (data) {
                    if(data.status==1){
                        layer.msg(data.msg);
                        window.location.reload();
                    }else{
                        layer.alert(data.msg);
                    }
                }
            });
            layer.close(index);
        });
    });

JS;
$this->registerJs($js);
?>
