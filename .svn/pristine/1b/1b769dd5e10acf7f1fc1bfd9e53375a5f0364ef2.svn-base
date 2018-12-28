<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use app\models\PurchaseSuggest;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
$this->title = '付款通知';
$this->params['breadcrumbs'][] = '费用管理';
$this->params['breadcrumbs'][] = '应付';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box box-success">
    <div class="box-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="box-footer">
        <?= Html::a('批量审批', ['all-approval'], ['class'=>'btn btn-success','id'=>'all-approval','data-toggle'=>'modal', 'data-target'=>'#create-modal']) ?>
        <?= Html::a('导出Excel', ['export-csv'], ['class'=>'btn btn-success print','id'=>'bulk-execl']) ?>
        <?php
            echo Html::button('批量驳回', ['class' => 'btn btn-success print bulk-reject','source' => 1]);
        ?>
    </div>
</div>
<input type="hidden" id="source" value="<?=$source?>">
<?php

if($source == 1):
    $gridColumns = [
        [
            'class' => 'kartik\grid\CheckboxColumn',
            'name' => "id" ,
            'checkboxOptions' => function ($model) {
                return ['value' => $model->pur_number];
            }
        ],
        [
            'label' => 'id',
            'value' => function($model) {
                return $model->id;
            }
        ],
        [
            'label' => '状态',
            "format" => "raw",
            'value' => function($model) {
                return PurchaseOrderServices::getPayStatusType($model->pay_status);
            }
        ],
        [
            'label' => '单号',
            "format" => "raw",
            'value'=> function($model) {
                $data = Yii::t('app','申请单：').$model->requisition_number."<br/>";
                $data.= Yii::t('app','合同号：').$model->pur_number."<br/>";
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
            'label' => '付款信息',
            'format' => 'raw',
            'value'=> function($model) {
                $supplierName = !empty($model->purchaseCompact[0]->purchaseOrder->supplier_name)?$model->purchaseCompact[0]->purchaseOrder->supplier_name:null;
                if (empty($supplierName)) $supplierName = empty($model->supplier['supplier_name'])?PurchaseSuggest::GetCode($model->supplier_code):$model->supplier['supplier_name'];

                $sub_html = \app\models\SupplierSearch::flagCrossBorder(true,$model->supplier_code);
                $data =  Yii::t('app','供应商').'：'.$supplierName.$sub_html."<br/>";
                $data .= !empty($model->pay_type) ? Yii::t('app','支付方式').'：'.SupplierServices::getDefaultPaymentMethod($model->pay_type)."<br/>":'';
                $data .= !empty($model->settlement_method) ? Yii::t('app','结算方式').'：'.SupplierServices::getSettlementMethod($model->settlement_method)."<br/>":'';

                return $data;
            }
        ],
        [
            'label' => '补货方式',
            'attribute' => 'ids',
            "format" => "raw",
            'value' =>
                function($model) {
                    $data = !empty($model->purchaseOrder['pur_type'])?PurchaseOrderServices::getPurType($model->purchaseOrder['pur_type']):''.'<br/>';
                    return $data;
                },

        ],
        [
            'label' => '操作人',
            "format" => "raw",
            'value' => function($model) {
                $data = '';
                if(!empty($model->applicant)) {
                    $data .= '<p>申请人：'.BaseServices::getEveryOne($model->applicant).'</p>';
                }
                if(!empty($model->approver)) {
                    $data .= '<p>审批人：'.BaseServices::getEveryOne($model->approver).'</p>';
                } elseif (!empty($model->auditor)) {
                    $data .= '<p>审批人：'.BaseServices::getEveryOne($model->auditor).'</p>';
                }
                return $data;
            },
        ],
        [
            'label' => '操作时间',
            'format' => 'raw',
            'value' => function($model) {
                $data = '<p>申请时间：'.$model->application_time.'</p>';
                if(!empty($model->processing_time)) {
                    $data .= '<p>审批时间：'.$model->processing_time.'</p>';
                } elseif (!empty($model->review_time)) {
                    $data .= '<p>审批时间：'.BaseServices::getEveryOne($model->review_time).'</p>';
                }
                return $data;
            },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => false,
            'width' => '180px',
            'template' => "<p>{finance-audit}</p><p>{note}</p>",
            'buttons' => [
                'finance-audit' => function($url, $model, $key) {
                    if($model->pay_status == 2) {
                        return Html::a('<i class="glyphicon glyphicon-wrench"></i> 财务审批',['compact-finance-audit', 'id' => $model->id], [
                            'title' => '财务审批',
                            'class' => 'btn btn-xs red compact-finance-audit',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal'
                        ]);
                    }
                },
                'note' => function ($url, $model, $key) {
                    return Html::a('<i class="fa fa-fw fa-comment"></i> 添加合同备注', ['purchase-compact/add-compact-note', 'cpn' => $model->pur_number], [
                        'title' => Yii::t('app', '添加采购单备注'),
                        'class' => 'btn btn-xs c-note',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal'
                    ]);
                }
            ]
        ]
    ];
?>

<div class="btn-group" style="margin-bottom: 10px;">
    <span class="btn btn-danger" disabled="disabled">合同单</span>
    <a href="?source=2" class="btn btn-default">其它采购单</a>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
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
    'columns' => $gridColumns,
    'toolbar' => [],
    'condensed' => true,
    'hover' => true,
    'panel' => [
        'type' => 'success',
    ]
]);
?>

<?php
else:
    $gridColumns = [
        [
            'class' => 'kartik\grid\CheckboxColumn',
            'name' => 'id',
            'checkboxOptions' => function ($model) {
                return ['value' => $model->pur_number];
            }
        ],
        [
            'label' => 'id',
            'attribute' => 'ids',
            'value' => function($model) {
                    return  $model->id;
            }
        ],
        [
            'label' => '状态',
            'format' => 'raw',
            'value' => function($model) {
                return PurchaseOrderServices::getPayStatusType($model->pay_status);
            }
        ],
            [
                'label' => '单号',
                'attribute' => 'pur_numbers',
                "format" => "raw",
                'value'=> function($model, $key, $index, $column) {
                    $order_number = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->platform_order_number : '';
                    if(!$order_number) {
                        $order_number = !empty($model->orderOrders) ? $model->orderOrders->order_number : '';
                    }
                    $data = Yii::t('app','申请单：').$model->requisition_number."<br/>";
                    $data.= Yii::t('app','采购单：').$model->pur_number."<br/>";
                    $data.= Yii::t('app','拍单号：').$order_number."<br/>";
                    $data.= Html::a('<span class="fa fa-fw fa-comment" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单备注"></span>', ['purchase-order/get-purchase-note'],['id' => 'note',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                        'value' => $model->pur_number,
                    ]);
                    return $data;
                }
            ],

            [
                'label'=>'付款信息',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $sub_html = \app\models\SupplierSearch::flagCrossBorder(true,$model->supplier_code);
                        $data = empty($model->supplier['supplier_name'])?Yii::t('app','供应商').'：'.PurchaseSuggest::GetCode($model->supplier_code):Yii::t('app','供应商').'：'.$model->supplier['supplier_name'];
                        $data .= $sub_html.'<br/>';
                        $data .=!empty($model->pay_type) ?Yii::t('app','支付方式').'：'.SupplierServices::getDefaultPaymentMethod($model->pay_type)."<br/>":'';
                        $data .= !empty($model->settlement_method) ?Yii::t('app','结算方式').'：'.SupplierServices::getSettlementMethod($model->settlement_method)."<br/>":'';
                        //$data .= Yii::t('app','请款方式：').PurchaseOrderServices::getRequestPayoutType($model->request_payout_type)."<br/>";
                        //$data .= !empty($model->supplier['payment_cycle'])?Yii::t('app','支付周期类型').'：'.SupplierServices::getPaymentCycle($model->supplier['payment_cycle'])."<br/>":Yii::t('app','支付周期类型').'：'."<Br/>";
                        $data .= Yii::t('app','状态:').PurchaseOrderServices::getPayStatus($model->pay_status);
                        return $data;
                    },

            ],
            [
                'label'=>'费用名称',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        return $model->pay_name;
                    },

            ],
            [
                'label'=>'申请金额/运费',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model) {
                        return \app\models\PurchaseOrderPay::getPrice($model);
                    }
            ],

            [
                'label'=>'补货方式',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data = !empty($model->purchaseOrder['pur_type'])?PurchaseOrderServices::getPurType($model->purchaseOrder['pur_type']):''.'<br/>';
                        return $data;
                    },

            ],
            [
                'label'=>'备注',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){

                        $data =  Yii::t('app','创建').'：'.$model->orderNote['note'].'<br/>';
                        $data .= Yii::t('app','审核').'：'.$model->review_notice . '<br />';
                        $account = !empty($model->purchaseOrderAccount['account']) ? $model->purchaseOrderAccount['account'] : '';
                        $data .= '账号：' . $account;
                        return $data;
                    },

            ],
            [
                'label'=>'操作人',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data  = Yii::t('app','申请人:').BaseServices::getEveryOne($model->applicant).'<br/>';
                        //$data .= Yii::t('app','审核人:').BaseServices::getEveryOne($model->auditor).'<br/>';
                        if(!empty($model->approver))
                        {
                            $data .= Yii::t('app','审批人:').BaseServices::getEveryOne($model->approver).'<br/>';
                        } else{
                            $data .= Yii::t('app','审批人:').''.'<br/>';
                        }

                        $data .= Yii::t('app','采购员:').$model->purchaseOrder['buyer'].'<br/>';
                        $data .= Yii::t('app','跟单员:').$model->purchaseOrder['merchandiser'];
                        return $data;
                    },

            ],
            [
                'label'=>'操作时间',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data  = Yii::t('app','申请时间:').$model->application_time.'<br/>';
                        // $data .= Yii::t('app','审核时间:').$model->review_time.'<br/>';
                        $data .= Yii::t('app','审批时间:').$model->processing_time;
                        return $data;
                    },

            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width' => '180px',
                'template' => "{finance-audit}<br/>{remarks}",
                'buttons' => [
                    'finance-audit' => function($url, $model, $key) {

                        if($model->pay_status == 2) {
                            return Html::a('<i class="glyphicon glyphicon-wrench"></i> 财务审批',['finance-audit','id' => $model->id], [
                                'title' => '财务审批',
                                'class' => 'btn btn-xs red finance-audit',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal'
                            ]);
                        }
                    },

                    'remarks' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-fw fa-comment"></i> 添加采购单备注', ['purchase-order/add-purchase-note', 'pur_number' => $model->pur_number,'flag'=>2], [
                            'title' => Yii::t('app', '添加采购单备注'),
                            'class' => 'btn btn-xs note',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'id' => 'return',
                        ]);

                    }
                ]
            ],
    ];

?>

<div class="btn-group" style="margin-bottom: 10px;">
    <a href="?source=1" class="btn btn-default">合同单</a>
    <span class="btn btn-danger" disabled="disabled">其它采购单</span>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
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
    'columns' => $gridColumns,
    'toolbar' => [],
    'condensed' => true,
    'hover' => true,
    'exportConfig' => [
        GridView::EXCEL => [],
    ],
    'panel' => [
        'type' => 'success',
    ]
]);
?>

<?php endif; ?>

<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size' => 'modal-lg',
    'options' => [
        'data-backdrop'=>'static',
    ]
]);
Modal::end();
$arrival='请选择需要审批的订单';
$js = <<<JS
    $(document).on('click', '.finance-audit', function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));  
    });

    $(document).on('click', '.compact-finance-audit', function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));  
    });
    
    $(document).on('click', '#return', function () {

        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    
    $(document).on('click', '#note', function () {
        $('.modal-body').html('Waiting...');
        $.get($(this).attr('href'), {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    
    // add compact note 
    $(document).on('click', '.c-note', function() {
        $('.modal-body').html('Waiting...');
        $.get($(this).attr('href'), function(data) {
            $('.modal-body').html(data);
        });
    });
    
    
    $(document).on('click', '#all-approval', function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids.length == 0) {
            $('.modal-body').html('请选择要审批的数据');
        } else {
            $('.modal-body').load($(this).attr('href'), {ids: ids});
        }
    });
    
    
    //批量导出
    $(document).on('click', '#bulk-exec2', function () {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        var url = $(this).attr("href");
        if($(this).hasClass("print")) {
            url = '/purchase-order-pay-notification/pay-status';
        }
        url = url+'?pay_status=2&ids='+ids;
        $(this).attr('href',url);
    });
    
    $(document).on('click', '#bulk-execl', function () {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        var daterangepicker_start = $("input[name='daterangepicker_start']").val(); //时间段
        var daterangepicker_end = $("input[name='daterangepicker_end']").val();
        var pay_status = $('#purchaseorderpaysearch-pay_status').val();
        var url = $(this).attr("href");
        if($(this).hasClass("print"))
        {
            url = '/purchase-order-pay-notification/export-csv';
        }
        url=url + '?daterangepicker_start=' + daterangepicker_start + '&daterangepicker_end=' + daterangepicker_end + '&ids=' + ids + '&pay_status=' + pay_status;
        $(this).attr('href',url);
    });
     // 批量驳回
    $(document).on('click', '.bulk-reject', function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
       
        if(ids == '') {
            layer.alert('请先勾选要驳回的数据');
            return false;
        }
        var source = $("#source").val();
        layer.prompt({title: '驳回备注', value: '', formType: 2}, function (remark, index) {
            $.ajax({
                url:'/purchase-order-pay-notification/batch-reject',
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
