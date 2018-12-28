<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItems;
use mdm\admin\components\Helper;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderReceipt;
use app\models\WarehouseResults;

?>
<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options'=>[
            'id'=>'grid_purchase_order',
        ],
        'pager'=>[
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
                    return ['value' => $model->pur_number];
                }
            ],
            [
                'label'=>'id',
                'attribute' => 'ids',
                'value'=> function($model){
                	return  $model->id;   //主要通过此种方式实现
                },
            ],
            [
                'label'=>'订单',
                'attribute' => 'pur_numbers',
                "format" => "raw",
                'value'=> function($model, $key, $index, $column){
                    $data  = PurchaseOrderServices::getPurchaseStatus($model->purchas_status).'&nbsp;&nbsp;';
                    $data .= $model->is_expedited==2 ? '<span class="label label-danger">加急采购单</span>&nbsp;&nbsp;':'';
                    $data .= $model->audit_return==1 ? '<span class="label label-primary">审核退回</span><br/>':'';
                    $data .= $model->audit_return==1 ? '<span style="color: red">'.Yii::t("app","审核备注").":".$model->audit_note .'</span>': '';
                    $data.='<br/>'.Yii::t('app','采购单').':'.$model->pur_number;
                    if ($model->is_drawback == 2) { $data .= "<font style='color:red;font-size:12px'> [含税]</font>";}
                    $data.="<br/>".Yii::t('app','供应商').':'.$model->supplier_name."<br/>";
                    $data.=Yii::t('app','采购员').':'.$model->buyer."<br/>";
                    $data.=Html::a('<span class="glyphicon glyphicon-zoom-in"  style="font-size:20px;color:#00a65a;margin-right:10px;" title="单击，查看采购产品明细"></span>', ['#'],['id' => 'views',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                        'value' =>$model->pur_number,
                    ]);
                    $data.=Html::a('<span class="glyphicon glyphicon-list-alt" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单日志"></span>', ['get-purchase-log'],['id' => 'logs',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal','value' =>$model->pur_number,
                    ]);

                    $data.=Html::a('<span class="fa fa-fw fa-truck" style="font-size:20px;color:coral" title="物流信息"></span>', ['get-tracking','id'=>$model->pur_number],['id' => 'logistics',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',]);
                    $data.=Html::a('<span class="fa fa-fw fa-comment" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单备注"></span>', ['get-purchase-note'],['id' => 'note',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal','value' =>$model->pur_number,
                    ]);
                    $data.=$model->receiving_exception_status?'<span class="glyphicon glyphicon-question-sign" style="font-size:14px;color:red" title="收货异常">'.PurchaseOrderServices::getPurchaseEx($model->receiving_exception_status).'</span>&nbsp;&nbsp;':'';
                    $data.=$model->qc_abnormal_status?'<span class="glyphicon glyphicon-exclamation-sign" style="font-size:14px;color:coral" title="QC异常">'.PurchaseOrderServices::getPurchaseExs($model->qc_abnormal_status).'</span>':'';
                    return $data;
                },
            ],

            [
                'label'=>'是否验货',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=> function($model, $key, $index, $column){
                    return PurchaseOrderServices::getIsCheckGoods($model->is_check_goods, true);
                },
            ],
            [
                'label' => '支付状态',
                "format" => "raw",
                'value' => function($model) {
                    $state = PurchaseOrderPay::getOrderPayStatus($model->pur_number);
                    if(!$state) {
                        return PurchaseOrderServices::getPayStatusType(1);
                    } else {
                        $data = '';
                        foreach($state as $v) {
                            $data .= "<a href='/fba-purchase-order-pay/index?id={$v['id']}' target='_blank' title='点击查看请款单'>".PurchaseOrderServices::getPayStatusType($v['pay_status'])."</a><br/>";
                        }
                        return $data;
                    }
                }
            ],
            [
                'label'=>'退款状态',
                "format" => "raw",
                'value'=> function($model){
                    if(in_array($model->refund_status, [1, 10])) {
                        return Html::a(PurchaseOrderServices::getReceiptStatusCss($model->refund_status), ['refund-handler', 'pur_number' => $model->pur_number],
                            ['class' => 'refund-handler', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
                    } else {
                        $receipt = PurchaseOrderReceipt::find()->where(['pur_number' => $model->pur_number])->all();
                        $res = '';
                        if (!empty($receipt)) {
                            foreach ($receipt as $rk => $rv) {
                                $pay_status = $rv->pay_status;
                                $res .= (!empty(PurchaseOrderServices::getReceiptStatusCss($pay_status)) ? PurchaseOrderServices::getReceiptStatusCss($pay_status) : '') . '<br />';
                            }
                        }
                        return $res;
                    }
                }
            ],
            [
                'label'=>'报损状态',
                "format" => "raw",
                'value'=> function($model){
                    $arr_status = \app\models\PurchaseOrderBreakage::getStatus($model->pur_number);
                    $html='';
                    if(!empty($arr_status)){
                        foreach ($arr_status as $key=>$status){
                            $html .= $status!=='' ? Html::a(PurchaseOrderServices::getBreakageStatus($status), ['exp-breakage/index','sku'=>$key],['target'=>'_black']):'';
                        }
                    }
                    return $html;
                }
            ],
            [
                'label'=>'仓库',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=> function($model){
                    $data  =!empty($model->warehouse_code)?BaseServices::getWarehouseCode($model->warehouse_code):'<br/>';
                    return  $data;   //主要通过此种方式实现
                },
            ],
            [
                'label'=>'运输方式',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=> function($model){
                    $data = PurchaseOrderServices::getShippingMethod($model->shipping_method) . '<br/>';   //主要通过此种方式实现
                    if(!empty($model->fbaOrderShip)){
                        foreach($model->fbaOrderShip as $value) {
                            if(!empty($value['cargo_company_id'])) {
                                $s = !empty($value['cargo_company_id']) ? $value['cargo_company_id'] : '';
                                if ($s == '韵达快递') {
                                    $url = 'https://www.kuaidi100.com/all/yd.shtml?mscomnu=' . $value['express_no'];
                                } else {
                                    $url = 'https://www.kuaidi100.com/chaxun?com=' . $s . '&nu=' . $value['express_no'];
                                }

                                $data .= !preg_match("/^[a-z]/i", $value['cargo_company_id']) ? "<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>" : "<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>";   //主要通过此种方式实现
                                $data .= preg_match("/^[a-z]/i", $value['cargo_company_id']) ? $value['cargo_company_id'] . '<br/>' : $value['cargo_company_id'] . '<br/>';   //主要通过此种方式实现
                            }
                        }
                    }
                    return $data;
                },
            ],
            [
                'label'=>'金额',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=> function($model, $key, $index, $column){
                    return PurchaseOrderItems::getCountPrice($model->pur_number).$model->currency_code."<br/>";
                },
            ],
            [
                'label'=>'运费',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=> function($model){
                    $freight = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->freight : 0;
                    return $freight;
                },
            ],
            [
                'label'=>'结算方式',
                'attribute' => 'ids',
                'value'=> function($model){
                    if (!empty($model->account_type)) {
                        return SupplierServices::getSettlementMethod($model->account_type);   //主要通过此种方式实现
                    }
                },
            ],
            [
                'label'=>'时间',
                'attribute' => 'created_ats',
                "format" => "raw",
                'headerOptions' => ['width' => '200'],
                'value'=> function($model, $key, $index, $column){
                    $data = '创建时间:'.$model->created_at.'<br/>';
                    $data .= '提交时间:'.$model->submit_time.'<br/>';
                    $data.= '审核时间:'.$model->audit_time.'<br/>';

                    $detime=round((strtotime($model->date_eta)-time())/86400);
                    if($detime>0 && $detime<=2){
                        $data.='<span style="color:red">到货时间:'.$model->date_eta.'</span><br/>';
                    }else{
                        $data.='<span style="color:#00a65a">到货时间:'.$model->date_eta.'</span><br/>';
                    }
                    $instock_date = WarehouseResults::find()->select('instock_date')->where(['pur_number'=>$model->pur_number])->orderBy('id desc')->scalar();
                    $data .= '<span style="color:#00a65a">入库时间:'.$instock_date.'</span><br/>';
                    return $data;
                },
            ],
            [
                'label'=>'备注',
                'attribute' => 'created_ats',
                "format" => "raw",
                'value'=> function($model, $key, $index, $column) {
                    $data = '确认备注:'.$model->orderNote['note'].'<br/>';
                    $data.= '采购单备注:'.$model->confirm_note.'<br/>';
                    
                    $order_number = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->platform_order_number : '';
                    if(!$order_number) {
                        $order_number = !empty($model->orderOrders) ? $model->orderOrders->order_number : '';
                    }

                    $data.= '拍单号：'.$order_number . '<br />';
                    $account = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->purchase_acccount : '';
                    $data .= '账号：' . $account;
                    
                    return $data;
                },
            ],
            [
                'label'=>'推送状态',
                'attribute' => 'created_ats',
                "format" => "raw",
                'value'=>function($model, $key, $index, $column){
                    return PurchaseOrderServices::getPush($model->is_push);
                },
            ],
            [
                'label'=>'信息修改状态',
                'attribute' => 'created_ats',
                "format" => "raw",
                'value'=> function($model) {
                    return PurchaseOrderServices::getShipfeesAuditStatus($model->shipfees_audit_status);
                },
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => true,
                'width'=>'180px',
                'template' => '{payment}{add-tracking}{add-note}{edit-tracking}<p>{update-order}</p>{apply-breakage}{cancellations}{apply-inspection}',
                'buttons'=>[
                    'payment' => function ($url, $model, $key) {
						$username = Yii::$app->user->identity->username;
                        if (!in_array($model->purchas_status,[1,2,4,10])  && ($model->buyer == $username || PurchaseOrderServices::getIsAdmin())) {
                            return Html::a('<i class="glyphicon glyphicon-yen"></i> 申请付款', ['payment', 'pur_number' => $model->pur_number], [
                                'title' => Yii::t('app', '申请付款'),
                                'class' => 'btn btn-xs payment',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                        }
                    },
                    'add-tracking' => function ($url, $model, $key) {
                        return Html::a('<i class=" fa fa-fw fa-plus-square"></i> 添加跟踪记录', ['add-tracking','pur_number'=>$model->pur_number], [
                            'title' => Yii::t('app', '添加跟踪记录'),
                            'class' => 'btn btn-xs tracking',
                        ]);
                    },
                    'add-note' => function ($url, $model, $key) {
                        return Html::a('<i class=" fa fa-fw fa-comment"></i> 添加备注', ['add-purchase-note','id'=>$key,'pur_number'=>$model->pur_number,'flag'=>1], [
                            'title' => Yii::t('app', '添加备注'),
                            'class' => 'btn btn-xs note',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    },
                    'edit-tracking' => function ($url, $model, $key) {
                        return Html::a('<i class=" fa fa-fw fa-plus-square"></i> 编辑跟踪记录', ['edit-tracking','pur_number'=>$model->pur_number], [
                            'title' => Yii::t('app', '编辑跟踪记录'),
                            'class' => 'btn btn-xs edit-tracking',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    },
                    'update-order' => function ($url, $model, $key) {
                       if ($model->buyer==Yii::$app->user->identity->username) {
                            return Html::a('<i class="fa fa-fw fa-exclamation-triangle"></i> 修改订单', ['update-order', 'pur_number' => $model->pur_number], [
                                'title' => Yii::t('app', '修改订单'),
                                'class' => 'btn btn-xs disagree update-order',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                        }
                    },
                    'apply-breakage' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-fw fa-exclamation-triangle"></i> 申请报损', ['apply-breakage', 'pur_number' => $model->pur_number], [
                            'title' => Yii::t('app', '申请报损'),
                            'class' => 'btn btn-xs disagree apply-breakage',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    },
                    'cancellations' => function ($url, $model, $key) {
                        //如果自己下的单
                        //已审批、等待到货、部分到货等待剩余
                        $username = Yii::$app->user->identity->username;
                        $arr = [3, 7, 8];
                        if ( ($model->buyer==$username || BaseServices::getIsAdmin()) && in_array($model->purchas_status, $arr) ) {
                            return Html::a('<i class="glyphicon glyphicon-magnet"></i> 取消未到货', ['cancellations', 'pur_number' => $model->pur_number, 'id' => $model->id], [
                                'title' => Yii::t('app', '取消部分数量'),
                                'class' => 'btn btn-xs cancellations',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                        }
                    },
                    'apply-inspection' => function ($url, $model, $key) {//是否需要验货
                        $price =  PurchaseOrderItems::getCountPrice($model->pur_number);
                        $is_gd = 0;
                        if($price > 20000){
                            $is_gd = \app\models\PurchaseOrder::checkProvince($model->pur_number);
                        }
                        if ($is_gd && !in_array($model->purchas_status,[1,2])) {
                            return Html::a('<i class="fa fa-fw fa-plus-square"></i> 申请验货', ['/supplier-check/create'], [
                                'title' => Yii::t('app', '申请验货 '),
                                'class' => 'btn btn-xs disagree purple',
                            ]);
                        }
                    },
                ],
            ],
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  ['{export}',],
        'pjax' => false,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => false,
        'exportConfig' => [GridView::EXCEL => [],],
        'panel' => ['type'=>'success',],
    ]); ?>
</div>
<?php
$requestUrl = Url::toRoute('view');
$js = <<<JS
$(function() {
    $('.refund-handler').click(function() {
         $('.modal-body').html('');
         $('.modal-body').load($(this).attr('href'));
    });
    
	$('.apply-breakage').click(function() {
		$('.modal-body').html('');
		$('.modal-body').load($(this).attr('href'));
	});
     
	$('.update-order').click(function() {
		$('.modal-body').html('');
		$('.modal-body').load($(this).attr('href'));
	});

    $(document).on('click', '#views', function () {
        $.get('{$requestUrl}', {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.tracking', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.trackings', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.note', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
     
     // 编辑跟踪记录
    $(document).on('click', '.edit-tracking', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    
    $(document).on('click', '#logistics', function () {

        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '#logs', function () {

        $.get($(this).attr('href'), {id:$(this).attr('value')},
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
     $(document).on('click', '.payment', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.edit', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

    $(document).on('click', '.cancellations', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
});
JS;
$this->registerJs($js);
?>