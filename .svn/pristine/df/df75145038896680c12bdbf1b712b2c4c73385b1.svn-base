<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
use app\models\PurchaseDemand;
use app\models\PurchaseEstimatedTime;
use app\models\PurchaseOrderReceipt;
use app\models\WarehouseResults;
use app\models\PurchaseOrderPay;
use app\models\ArrivalRecord;
use app\models\PurchaseOrderCancelSub;
use app\models\UebExpressReceipt;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TodayListSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '需求跟踪';
$this->params['breadcrumbs'][] = $this->title;
$url_export  = Url::toRoute('summary-export');// 导出URL

// 格式化输出时间
function format_date($date){
    $date = trim($date);
    if(is_string($date)){
        if(strlen($date) == 10) $date = $date.' 00:00:00';
        return $date;
        return substr($date,0,10);
    }elseif(is_numeric($date)){
        return date('Y-m-d H:i:s',$date);
    }
    return '';
}
?>

<div class="purchase-order-index">

    <?= $this->render('_summary-search', ['model' => $searchModel]); ?>

    <p class="clearfix"></p>
    <?php
    if(\mdm\admin\components\Helper::checkRoute('summary-export')) {
        echo Html::button(Yii::t('app', '导出'), ["class" => "btn btn-info button-success btn-export"]);
    }
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,

        // Start:禁用 options 关闭拖动列表效果
        /* 'options'=>[
            'id'=>'grid_purchase_order',
        ], */
        // End:禁用 options 关闭拖动列表效果

        'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
        'options'=>[
            'id'=>'grid_purchase_order',
        ],
        'pager' => [
            'class' => \liyunfang\pager\LinkPager::className(),
            'options' => ['class' => 'pagination', 'style' => "display:block;"],
            'template' => '{pageButtons} {pageSize}', //分页栏布局
            'pageSizeList' => [10, 20, 50, 100,], //页大小下拉框值
        ],
        'columns' => [
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model) {
                    return ['value' => $model->id];
                }
            ],
            [
                'label'=>'sku',
                'format'=>'raw',
                'value' => function ($model) {
                    $html    = "<br/>".( Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku,['target'=>'blank']) );
                    $subHtml = \app\models\ProductRepackageSearch::getPlusWeightInfo($model->sku,true);// 加重SKU搜索
                    $html   .= $subHtml;
                    $html .= '<br>'.Html::a('<span class="glyphicon glyphicon-stats" style="font-size:10px;color:cornflowerblue;" title="销量库存"></span>', ['product/get-stock-sales','sku'=>$model->sku],
                            [
                                'class' => 'btn btn-xs stock-sales-purchase',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                    $html .= Html::a('<span class="glyphicon glyphicon-eye-open" style="font-size:10px;color:cornflowerblue;" title="历史采购记录"></span>', ['purchase-suggest/histor-purchase-info','sku'=>$model->sku,'role'=>'sales'],[
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                        'class'=>'btn btn-xs stock-sales-purchase',
                    ]);
                    return $html;

                }
            ],
            [
                'label'=>'图片',
                "format" => "raw",
                'value'=> function($model){
                    //return Html::img(Vhelper::downloadImg($model->sku,$model->uploadimgs,2),['width'=>'60px']);
                    //return Vhelper::toSkuImg($model->sku,$model->uploadimgs,'60px');
                    return "<br/>".( \toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($model->sku),'width'=>'60px']) );
                }
            ],
            [
                'label' => '产品信息',
                "format" => "raw",
                'width' => '100px;',
                'value'=>
                    function($model){
                        $html = "<br/>品名：".(!empty($model->desc) ? $model->desc->title : '');
                        $html .= "<br/><br/>供应商：".(!empty($model->purOrder->supplier_code) ? SupplierServices::getSupplierName($model->purOrder->supplier_code) : '');
                        return  $html;
                    },

            ],
            [
                'label'=>'需求信息',
                'format'=>'raw',
                'value' => function($model){
                    $html = "<br/>需求单号：<br/>".($model->demand_number).'<br/>';
                    $html .= "<br/>销售组别：<br/>".(  ($group_id = BaseServices::getAmazonGroupName($model->group_id))?$group_id.'<br/>':'' );
                    $html .= "<br/>销售：<br/>".$model->sales;
                    $html .= "<br/>销售：<br/>".$model->xiaoshou_zhanghao;

                    return  $html;
                }
            ],
            [
                'label'=>'采购信息',
                'format'=>'raw',
                'value' => function($model){
                    $html = "<br/>采购单号：".(!empty($model->purOrder) ? $model->purOrder->pur_number : '');
                    $html .= "<br/>仓库：". (!empty($model->purchase_warehouse) ? BaseServices::getWarehouseCode($model->purchase_warehouse) : '');
                    $html .= "<br/>采购员：".(\app\models\PurchaseCategoryBind::getBuyerBySku($model->sku));
                    $html .= "<br/>采购单价：".(!empty($model->purOrderItem) ? !empty($model->purOrderItem->price) ?  $model->purOrderItem->price : '' : '');
                    $html .= "<br/>结算方式：".(!empty($model->purOrder)?SupplierServices::getSettlementMethod($model->purOrder->account_type):'');   //主要通过此种方式实现);

                    return  $html;
                }
            ],
            [
                'label'=>'含税信息',
                'format'=>'raw',
                'value' => function($model){
                    $drawArray = ['1'=>'不含税','2'=>'含税'];
                    $html = "<br/>是否含税：<br/>".( (!empty($model->purOrder) AND isset($drawArray[$model->purOrder->is_drawback]))?$drawArray[$model->purOrder->is_drawback].'<br/>':'' );
                    $html .= "<br/>可做退税：<br/>".($model->is_back_tax == 1 ? '可退税' : '');

                    return  $html;
                }
            ],
            [
                'label'=>'数量',
                'format'=>'raw',
                'width' => '100px;',
                'value' => function($model){
                    $pur_number = isset($model->purOrder->pur_number)?$model->purOrder->pur_number:'';
                    $html  = "<br/>需求：".( $model->purchase_quantity );

                    $ctq = !empty($model->purOrderItem) ? !empty($model->purOrderItem->ctq) ?  $model->purOrderItem->ctq : 0 : 0;
                    if($ctq) $subHtml = Html::a($ctq, ['show-detail'], ['id'=>'show-detail','data-show-type' => 'cgsl','data-show-name' => '采购数量详情','data-sku-number' => $model->sku .'&'.$model->demand_number.'&'.$pur_number,'data-toggle' => 'modal','data-target' => '#show-detail-modal']);
                    else $subHtml = $ctq;
                    $html .= "<br/>采购：".( $subHtml );
                    $cancel_num = PurchaseOrderCancelSub::getCancelCtq($pur_number,$model->sku); // 取消数量

                    $html .= "<br/>取消：". ( $cancel_num );
                    $html .= "<br/>收货：". ( intval(isset($model->purOrderItem->rqy)?$model->purOrderItem->rqy:0) );
                    $html .= "<br/>上架：". ( intval(isset($model->purOrderItem->cty)?$model->purOrderItem->cty:0) );


                    // 统计SKU的请款数量
                    $skuNum = [];
                    $payInfoList = PurchaseOrderPay::findAll(['pur_number' => $pur_number ] );// 找到此 采购单号下面所有请款单

                    if($payInfoList) {
                        foreach($payInfoList as $payInfo){
                            $pay_model  = new PurchaseOrderPay();
                            $pay_info   = $pay_model->getPayDetailById($payInfo->id);
                            if(!$pay_info) return null;
                            $skuNum = [];
                            if($pay_info['sku_list']) {
                                $sku_list = json_decode($pay_info['sku_list'],1);
                                foreach($sku_list as $k => $v) {
                                    $skuNum[$v['sku']] = $v['num'];
                                }
                            }

                            $order_model    = new \app\models\PurchaseOrder();
                            $order_info     = $order_model->getOrderDetail($pay_info['pur_number'], $skuNum);

                            foreach($order_info['purchaseOrderItems'] as $k => $v){
                                $sku = strtoupper($v['sku']);
                                if(isset($skuNum[$sku])){
                                    $skuNum[$sku] = + ($v['yizhifu_num']?:$v['ctq']);
                                }else{
                                    $skuNum[$sku] = $v['yizhifu_num']?:$v['ctq'];
                                }
                            }
                        }
                    }

                    $html .= "<br/>请款：". ( isset($skuNum[strtoupper($model->sku)])?$skuNum[strtoupper($model->sku)]:0 );

                    //FBA
                    $html .= "<br/>退款：". ( intval($cancel_num) );

                    return  $html;
                }
            ],
            [
                'label'=>'状态',
                'width' => '150px;',
                'format'=>'raw',
                'value'=>function($model){
                    $demand_status_list = \app\services\PlatformSummaryServices::getLevelAuditStatus();
                    $demand_status      = isset($demand_status_list[$model->level_audit_status])?$demand_status_list[$model->level_audit_status]:'状态异常';
                    $html  = "<br/>需求状态：".( $demand_status );
                    $html .= "<br/>订单状态：".( !empty($model->purOrder) ? PurchaseOrderServices::getPurchaseStatus($model->purOrder->purchas_status) : '' );
                    $html .= "<br/>付款状态：".( !empty($model->purOrder)&&!empty($model->purOrder->pay_status) ? PurchaseOrderServices::getPayStatus($model->purOrder->pay_status) : '' );


                    // 退款状态
                    if(isset($model->purOrder->refund_status) AND in_array($model->purOrder->refund_status, [1, 10])) {
                        $return_status = Html::a(PurchaseOrderServices::getReceiptStatusCss($model->purOrder->refund_status), ['refund-handler', 'pur_number' => $model->purOrder->pur_number],
                            ['class' => 'refund-handler', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
                    } else {
                        $return_status = '';
                        if(isset($model->purOrder->pur_number)){
                            $receipt = PurchaseOrderReceipt::find()->where(['pur_number' => $model->purOrder->pur_number])->all();
                            if (!empty($receipt)) {
                                foreach ($receipt as $rk => $rv) {
                                    $pay_status = PurchaseOrderServices::getReceiptStatusCss($rv->pay_status);
                                    $return_status .= (!empty($pay_status) ? $pay_status : '') . '<br />';
                                }
                            }
                        }
                    }

                    $html .= "<br/>退款状态：". ( $return_status );

                    return $html;
                }
            ],
            [
                'label'=>'物流信息',
                'format'=>'raw',
                'value'=>function($model){
                    if(!empty($model->purOrder)){
                        $html = "<br/>".PurchaseOrderServices::getShippingMethod($model->purOrder->shipping_method) . '<br/>';   //主要通过此种方式实现
                        foreach($model->purOrder->fbaOrderShip as $value) {
                            $s = !empty($value['cargo_company_id']) ? $value['cargo_company_id'] : '';
                            $url = 'https://www.kuaidi100.com/chaxun?com=' . $s . '&nu=' . $value['express_no'];

                            $html .= !preg_match("/^[a-z]/i", $value['cargo_company_id']) ? "<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>" : "<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>";   //主要通过此种方式实现

                            $html .= preg_match("/^[a-z]/i", $value['cargo_company_id']) ? $value['cargo_company_id'] . '<br/>' : $value['cargo_company_id'] . '<br/>';   //主要通过此种方式实现

                        }
                        return $html;
                    }
                    return '';
                }
            ],
            [
                'label'=>'内部流程时间',
                'format'=>'raw',
                'value' => function($model){
                    $pur_number = isset($model->purOrder->pur_number)?$model->purOrder->pur_number:'';
                    $html  = "<br/>需求审核：".( (empty($model->agree_time)?'':format_date($model->agree_time)) );
                    $html .= "<br/>订单审核：".( empty($model->purOrder->audit_time)?"":format_date($model->purOrder->audit_time) );

                    // 请款时间
                    $application_time = !empty($model->pay) ? format_date(date('Y-m-d H:i:s',strtotime($model->pay->application_time))) : '';
                    $subHtml    = Html::a($application_time, ['show-detail'], ['id'=>'show-detail','data-show-type' => 'qksj','data-show-name' => '请款详情','data-sku-number' => $model->sku .'&'.$model->demand_number.'&'.$pur_number,'data-toggle' => 'modal','data-target' => '#show-detail-modal']);
                    $html       .= "<br/>请款：".( $subHtml );
                    // 付款时间
                    $payer_time = !empty($model->pay) ? format_date($model->pay->payer_time) : '';
                    $subHtml    = Html::a($payer_time, ['show-detail'], ['id'=>'show-detail','data-show-type' => 'fksj','data-show-name' => '付款详情','data-sku-number' => $model->sku .'&'.$model->demand_number.'&'.$pur_number,'data-toggle' => 'modal','data-target' => '#show-detail-modal']);
                    $html       .= "<br/>付款：".( $subHtml );

                    if($pur_number){
                        $model_arrival_record = ArrivalRecord::find()->where(['purchase_order_no' => $pur_number])->orderBy('id desc')->one();
                        $delivery_time       = isset($model_arrival_record->delivery_time)?$model_arrival_record->delivery_time:'';
                        $receipt    = UebExpressReceipt::findOne(['relation_order_no' => $pur_number]);
                        $results    = WarehouseResults::getResults($pur_number,$model->sku,'create_time,instock_user,instock_date');
                    }
                    $html .= "<br/>签收：". ( format_date(isset($receipt->add_time)?$receipt->add_time:'') );

                    // 收货时间
                    $delivery_time = format_date(isset($delivery_time)?$delivery_time:'');
                    $subHtml    = Html::a($delivery_time, ['show-detail'], ['id'=>'show-detail','data-show-type' => 'shsj','data-show-name' => '收货详情','data-sku-number' => $model->sku .'&'.$model->demand_number.'&'.$pur_number,'data-toggle' => 'modal','data-target' => '#show-detail-modal']);
                    $html       .= "<br/>收货：". ( $subHtml );
                    // 入库时间
                    $instock_date = format_date(isset($results->instock_date)?$results->instock_date:'');
                    $subHtml    = Html::a($instock_date, ['show-detail'], ['id'=>'show-detail','data-show-type' => 'rksj','data-show-name' => '入库详情','data-sku-number' => $model->sku .'&'.$model->demand_number.'&'.$pur_number,'data-toggle' => 'modal','data-target' => '#show-detail-modal']);
                    $html       .= "<br/>入库：". ( $subHtml );


                    return  $html;
                }
            ],
            [
                'label'=>'交期信息',
                'format'=>'raw',
                'value' => function($model){
                    $pur_number = !empty($model->purOrder) ? $model->purOrder->pur_number : '';
                    $estimated_time = PurchaseEstimatedTime::getEstimatedTime($model->sku,$pur_number);// 首次预计到货时间
                    //如果有标记到货时间就取标记到货时间，否则取预计到货时间
                    $date_eta = (!empty($model->purOrder) AND !empty($model->purOrder->date_eta))? $model->purOrder->date_eta:$estimated_time;

                    $html  = "<br/>首次预计到货时间：".( format_date(!empty($estimated_time)?$estimated_time:'') );
                    $html .= "<br/>预计到货时间：". ( format_date(!empty($date_eta)?$date_eta:'') );

                    $avg = isset($model->fbaAvgArrival->avg_delivery_time) ? $model->fbaAvgArrival->avg_delivery_time :null;
                    $audit_time = !empty($model->purOrder) ? $model->purOrder->audit_time : '';
                    $audit_time = empty($audit_time) ? '' : date('Y-m-d H:i:s',strtotime($audit_time)+round($avg,2));

                    $html .= "<br/>权均交期时间：".( format_date($audit_time) );
                    $html .= "<br/>权均交期天数：". ( ($avg === null)?"":(($avg>0)?sprintf('%.2f',$avg/86400):0) );

                    return  $html;
                }
            ],
            [
                'label'=>'交期准确性',
                'format'=>'raw',
                'value' => function($model){
                    $pur_number         = isset($model->purOrder)?$model->purOrder->pur_number:'';
                    $purchase_status    = isset($model->purOrder)?$model->purOrder->purchas_status:'';

                    // 权均交期
                    $avg                = isset($model->fbaAvgArrival)?$model->fbaAvgArrival->avg_delivery_time :0;
                    $audit_time         = isset($model->purOrder)?$model->purOrder->audit_time :'';
                    $int_3_days         = 3 * 24 * 60 * 60;

                    // 预计到货时间
                    $estimated_time     = PurchaseEstimatedTime::getEstimatedTime($model->sku,$pur_number);// 首次预计到货时间
                    $int_estimated_time = strtotime($estimated_time);
                    // 最后一次入库时间
                    $last_instock_date  = !empty($pur_number)?WarehouseResults::find()->select('instock_date')->where(['pur_number' => $pur_number,'sku'=>$model->sku])->orderBy('id desc')->scalar() : '';
                    $int_last_instock_date = $last_instock_date?strtotime($last_instock_date):time();// 入库时间为空 说明还没入过库
                    // 当前时间
                    $int_now_time       = time();


                    if(in_array($purchase_status,[4,6,9,10])){// 已完结(预计到货时间与最后一次入库时间做对比)
                        $is_timeout_diff = $int_estimated_time - $int_last_instock_date;//预计到货时间 - 最后一次入库时间
                        $is_timeout_diff = abs(sprintf("%.1f",$is_timeout_diff/86400));

                        if($int_estimated_time <= $int_last_instock_date){
                            $is_timeout = '<span style="color: red">是</span>';
                            $is_timeout_diff = '<span style="color: red">'.$is_timeout_diff.'</span>';
                        }elseif( $int_estimated_time > $int_last_instock_date + $int_3_days){
                            $is_timeout = '<span style="color: red">是</span>';
                            $is_timeout_diff = '<span style="color: red">'.$is_timeout_diff.'</span>';
                        }else{
                            $is_timeout = '<span style="color: green">否</span>';
                            $is_timeout_diff = '<span style="color: green">'.$is_timeout_diff.'</span>';
                        }
                    }else{// 未完结(预计到货时间与当前时间作对比)
                        $is_timeout_diff = $int_estimated_time - $int_now_time;//预计到货时间 - 当前时间
                        $is_timeout_diff = abs(sprintf("%.1f",$is_timeout_diff/86400));

                        if($int_estimated_time <= $int_now_time){
                            $is_timeout = '<span style="color: red">是</span>';
                            $is_timeout_diff = '<span style="color: red">'.$is_timeout_diff.'</span>';
                        }elseif( $int_estimated_time > $int_now_time AND $int_estimated_time < $int_now_time + $int_3_days){
                            $is_timeout = '<span style="color: red">是</span>';
                            $is_timeout_diff = '<span style="color: red">'.$is_timeout_diff.'</span>';
                        }else{
                            $is_timeout = '<span style="color: green">否</span>';
                            $is_timeout_diff = '<span style="color: green">'.$is_timeout_diff.'</span>';
                        }
                    }
                    if(empty($audit_time)){// 采购单未审核不计算超时
                        $html  = "<br/>预计到货是否超时：".( "<span title='采购单未审核' style='color:blue;'>!!!</span>");
                        $html .= "<br/>差额天数：". ( "<span title='采购单未审核' style='color:blue;'>!!!</span>" );
                    }else{
                        $html  = "<br/>预计到货是否超时：".( $is_timeout );
                        $html .= "<br/><span title='首次预计到货时间 - 最后一次入库时间'>差额天数</span>：". ( $is_timeout_diff );
                    }


                    // 权均交期是否超时
                    $fou = '<span style="color: green">否</span>';
                    $shi = '<span style="color: red">是</span>';
                    $int_avg_delivery_date = (strtotime($audit_time)+$avg);// 权均交期
                    $int_avg_delivery_diff = $int_avg_delivery_date - $int_last_instock_date;// 权均交期 - 最后一次入库时间
                    $int_avg_delivery_diff_days = abs(sprintf("%.1f",$int_avg_delivery_diff/86400));

                    if(empty($audit_time)){// 采购单未审核不计算超时
                        $instock = $int_avg_delivery_diff_days = "<span title='采购单未审核' style='color:blue;'>!!!</span>";
                    }elseif($int_avg_delivery_diff > 0){
                        $instock = $fou;
                        $int_avg_delivery_diff_days = '<span style="color: green">'.$int_avg_delivery_diff_days.'</span>';
                    }else{
                        $instock = $shi;
                        $int_avg_delivery_diff_days = '<span style="color: red">'.$int_avg_delivery_diff_days.'</span>';
                    }

                    $html .= "<br/>权均交期是否超时：".( $instock );
                    $html .= "<br/><span title='权均交期时间 - 最后一次入库时间'>差额天数</span>：".( $int_avg_delivery_diff_days );
                    
                    if($pur_number){
                        // 签收时间
                        $receipt            = UebExpressReceipt::findOne(['relation_order_no' => $pur_number]);
                        $receipt_time       = isset($receipt->add_time)?$receipt->add_time:'';
                        $int_receipt_time   = strtotime($receipt_time);

                        // 上架时间(品检时间)
                        $model_arrival_record   = ArrivalRecord::find()->where(['purchase_order_no' => $pur_number])->orderBy('id desc')->one();
                        $check_time             = isset($model_arrival_record->check_time)?$model_arrival_record->check_time:'';
                        $check_time             = strtotime($check_time);

                        $receipt_time_diff      = $int_receipt_time - $check_time;
                        $receipt_time_diff      = sprintf("%.2f",$receipt_time_diff/86400).' (天)';
                    }else{
                        $receipt_time_diff  = "<span title='签收时间和上架时间缺失' style='color:blue;'>!!!</span>";
                    }

                    $html .= "<br/>签收时间和上架时间<br/>对比差：".$receipt_time_diff;

                    return  $html;
                }
            ],

            [
                'label' => '销售备注',
                "format" => "raw",
                'width' => '80px;',
                'value' => function($model){
                        $html = !empty($model->sales_note) ? $model->sales_note: '';
                        return $html;
                    },

            ],

        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [],


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
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
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


Modal::begin([
    'id' => 'show-detail-modal',
    'header' => '<h4 class="modal-title">展示详情</h4>',
//    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    //'closeButton' =>false,
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();

$js = <<<JS

$(function(){
    
    $(".btn-export").click(function(){
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        var url = '{$url_export}';
        urls = url+'?ids='+ids;
        window.location.href = urls;

    })

    $(document).on('click', '.detail', function () {
        var id   = $(this).attr('value');
        $.get($(this).attr('href'), {id:id},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.stock-sales-purchase', function () {
        $('.modal-body').html('正在请求数据....');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    
    
    /**
     * 展示详情
     */
    $(document).on('click', '#show-detail', function () {
        var show_type   = $(this).attr('data-show-type');
        var show_name   = $(this).attr('data-show-name');
        var sku_number     = $(this).attr('data-sku-number');
        
        $(".modal-title").html(show_name);//改变表头
        
        $.get($(this).attr('href'), {sku_number:sku_number,show_type:show_type},function (data) {
            $('.modal-body').html(data);
        });
    });
	
});
JS;
$this->registerJs($js);
?>
