<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPayDetail;
use app\models\SupervisorGroupBind;
use app\models\UebExpressReceipt;
use mdm\admin\components\Helper;
use app\models\PurchaseOrderPayType;
use yii\widgets\LinkPager;
use app\models\PurchaseWarehouseAbnormalSearch;

$this->title = '采购订单';
$this->params['breadcrumbs'][] = $this->title;

$bool = SupervisorGroupBind::getGroupPermissions(38);
?>
<style type="text/css">
    #grid_purchase_order p {
        margin: 6px;
    }
</style>
<div class="panel panel-default">
    <div class="panel-body">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="panel-footer">
        <?= Html::a('打印采购单', ['print-data'], ['class' => 'btn btn-success print','id'=>'submit-audit','target'=>'_blank']) ?>
        <?php
        if($source == 2) {
            echo Html::a('申请批量付款', ['allpayment'], [
                'class' => 'btn btn-success all-payment',
                'id' => 'all-payment',
                'data-toggle' => 'modal',
                'data-target' => '#create-modal'
            ]);
        }
        ?>

        <?= Html::a('标记到货日期', ['arrival-date'], ['class' => 'btn btn-success','id'=>'arrival','data-toggle' => 'modal',
            'data-target' => '#create-modal',])?>

        <?= Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv']) ?>

        <?php
        if(Helper::checkRoute('audit-ship')) {
            echo '<a href="audit-ship" class="btn btn-warning" target="_blank">订单信息修改-审核</a>';
        }
        if (Helper::checkRoute('payment-contract')) {
            echo Html::button('采购合同导出', ['class' => 'btn btn-success payment-contract']);
        }
        ?>

    <!--    --><?php
/*        if($source == 2) {
            echo Html::a('创建合同', ['#'], [
                'class' => 'btn btn-success all-info',
                'id' => 'create-compact',
            ]);
        }
        */?>

        <a href="/purchase-order/compact-list" class="btn btn-info" target="_blank">合同列表</a>

    </div>
</div>

<h4><span class="glyphicon glyphicon-heart" style="color: red"></span> 温馨小提示：</h4>
<p style="color: red">1：全额退款需要上级审核,部分退款直接进入财务收款模块。</p>
<p style="color: red">2：默认30分钟请求物流信息。</p>
<p style="color: red">3：想增加物流信息--请点击右边（动作 =》编辑跟踪记录）；第二条物流记录，请点击（动作 =》添加跟踪记录）。</p>
<p style="color: red">4：想申请退款或则作废采购单--请点击右边（动作 =》编辑采购单）。</p>
<p style="color: red">5：被驳回的退款单，可以点击退款状态，进行编辑，保存后会重新回到待财务收款。</p>
<p style="color:red;">6. 合同下的任何一个单，修改运费或优惠额，合同都会暂时冻结，待运费优惠审核通过后，刷新合同即可更新合同相关金额信息。</p>
<p style="color:red;">7. 账期订单（周结、半月结、月结、两月结）全到货或部分到货不等待剩余的情况下，才能去请款</p>

<?php if($source == 1): ?>

<div class="btn-group" style="margin-bottom: 10px;">
    <span class="btn btn-danger" disabled="disabled">合同单</span>
    <a href="?source=2" class="btn btn-default">网采单</a>
</div>
<?php else: ?>
<div class="btn-group" style="margin-bottom: 10px;">
    <a href="?source=1" class="btn btn-default">合同单</a>
    <span class="btn btn-danger" disabled="disabled">网采单</span>
</div>
<?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'options'=>[
                'id'=>'grid_purchase_order',
            ],
            //'showFooter'=>true,
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
                    'class' => 'kartik\grid\CheckboxColumn',
                    'name'=>"id" ,
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return ['value' => $model->pur_number];
                    }

                ],
                [
                    'label'=>'PO号',
                    'attribute' => 'pur_numbers',
                    "format" => "raw",
                    'value'=> function($model, $url, $key){
                        //异常单标红
                        $is_exp = PurchaseWarehouseAbnormalSearch::checkIsExp($model->pur_number);
                        if(!empty($is_exp) && $is_exp>0) {
                            $data = Html::a($model->pur_number, ['purchase-order/view'],['id' => 'views',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                                'value' =>$model->pur_number,
                                'style' => 'color:red',
                            ]);
                        }else{
                            $data = Html::a($model->pur_number, ['purchase-order/view'],['id' => 'views',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                                'value' =>$model->pur_number,
                            ]);
                        }

                        $data .= \app\models\ProductRepackageSearch::getPlusWeightInfoByPurNumber($model->pur_number,$model->purchaseOrderItems,1);// 根据采购单SKU展示 加重标记

                        $data .= Html::a('<span class="fa fa-fw fa-comment" style="font-size:20px;color:#f2adb1;" title="单击，查看采购单备注"></span>', ['#'],
                            [
                                'id' => $key,
                                'class' => 'btn btn-xs note',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                                'value' => $model->pur_number,
                                'pur_number' => $model->pur_number
                            ]);

                        $flag = true;
                        if(!empty($model->fbaOrderShip)){
                            foreach($model->fbaOrderShip as $k => $value) {
                                if(!empty($value->cargo_company_id) && !empty($value->express_no) && $flag==true) {
                                    $flag=false;
                                    $data.=Html::a('<span class="fa fa-fw fa-truck" style="font-size:20px;color:coral" title="物流信息"></span>', ['get-tracking','id'=>$model->pur_number],
                                        ['id' => 'logistics',
                                            'data-toggle' => 'modal',
                                            'data-target' => '#create-modal'
                                        ]);
                                }
                            }
                        }

                        /*$data.=Html::a('<span class="fa fa-fw fa-truck" style="font-size:20px;color:coral" title="物流信息"></span>', ['get-tracking','id'=>$model->pur_number],
                                ['id' => 'logistics',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal'
                                ]);*/
                        return $data;
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
                        return $data = PurchaseOrderServices::getPurchaseStatus($model->purchas_status).'&nbsp;&nbsp;';

                    },

                ],
                [
                    'label'=>'付款状态',
                    'attribute' => 'pur_numbers',
                    "format" => "raw",
                    'value'=> function($model){


                        /*$state = PurchaseOrderPay::getOrderPayStatus($model->pur_number);
                        if(!$state) {
                            return PurchaseOrderServices::getPayStatusType(1);
                        } else {
                            $data = '';
                            foreach($state as $v) {
                                $data .= "<a href='/purchase-order-pay/index?id={$v['id']}' target='_blank' title='点击查看请款单'>".PurchaseOrderServices::getPayStatusType($v['pay_status'])."</a><br/>";
                            }
                            return $data;
                        }*/
                        if(!empty($model->pay_status)) {
                            return PurchaseOrderServices::getPayStatusType($model->pay_status);
                        }

                    },

                ],

                [
                    'label' => '退款状态',
                    'attribute' => 'refund_status',
                    "format" => "raw",
                    'value' => function($model) {
                        $html='';
                        if($model->purchaseOrderRefund){
                            foreach ($model->purchaseOrderRefund as $data){
                                if(in_array($data->pay_status, [10])) {
                                    $html.= Html::a(PurchaseOrderServices::getReceiptStatusCss($data->pay_status), ['refund-handler', 'pur_number' => $data->pur_number,'requisition_number'=>$data->requisition_number],
                                        ['class' => 'refund-handler','title'=>$data->payer_notice, 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
                                } else {
                                    $html.= $data = !empty($data->pay_status) ? PurchaseOrderServices::getReceiptStatusCss($data->pay_status) : '';
                                }
                            }
                        }
                        return $html;
                    },
                ],
                [
                    'label'=>'运输方式',
                    'attribute' => 'ids',
                    "format" => "raw",
                    'value'=>
                        function($model){

                            $data =PurchaseOrderServices::getShippingMethod($model->shipping_method).'<br/>';   //主要通过此种方式实现

                            if(!empty($model->fbaOrderShip)){
                                foreach($model->fbaOrderShip as $k => $value) {
                                    if(!empty($value['cargo_company_id'])) {
                                        $s =!empty($value['cargo_company_id']) ? $value['cargo_company_id'] : '';
                                        if($s=='韵达快递'){
                                            $s='韵达快运';
                                        }
                                        $url ='https://www.kuaidi100.com/chaxun?com='.$s.'&nu='.$value['express_no'];

                                        $data .= !preg_match ("/^[a-z]/i",$value['cargo_company_id'])?"<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>":"<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>";   //主要通过此种方式实现

//                                    $data .= preg_match("/^[a-z]/i",$value['cargo_company_id']) ? BaseServices::getLogisticsCarrier($value['cargo_company_id'])->name.'<br/>':$value['cargo_company_id'].'<br/>';   //主要通过此种方式实现
                                        $data .= preg_match("/^[a-z]/i",$value['cargo_company_id']) ? $value['cargo_company_id'].'<br/>':$value['cargo_company_id'].'<br/>';   //主要通过此种方式实现
                                    }
                                }
                            }

                            /*$s =!empty($model->orderShip['cargo_company_id'])?$model->orderShip['cargo_company_id']:'';
                            $url ='https://www.kuaidi100.com/chaxun?com='.$s.'&nu='.$model->orderShip['express_no'];

                            $data .= !preg_match ("/^[a-z]/i",$model->orderShip['cargo_company_id'])?"<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>":"<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>";   //主要通过此种方式实现

                            $data .= preg_match ("/^[a-z]/i",$model->orderShip['cargo_company_id'])?BaseServices::getLogisticsCarrier($model->orderShip['cargo_company_id'])->name.'<br/>':$model->orderShip['cargo_company_id'].'<br/>';   //主要通过此种方式实现*/



                            //$data .=$model->supplier['transport_party']==1?Yii::t('app','运输承担方').':'.Yii::t('app','供应商'):Yii::t('app','运输承担方').':'.Yii::t('app','采购方');
                            return $data;
                        },

                ],
                [
                    'label'=>'签收时间',
                    "format" => "raw",
                    'value'=>
                        function($model){
                            $data =PurchaseOrderServices::getShippingMethod($model->shipping_method).'<br/>';   //主要通过此种方式实现
                            if(!empty($model->fbaOrderShip)){
                                foreach($model->fbaOrderShip as $k => $value) {
                                    if(!empty($value['cargo_company_id'])) {
                                        $res = UebExpressReceipt::getUebExpressReceipt($value['express_no']);
                                        return $res;
                                    }
                                }
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
                    'visible'=>$bool,
                    "format" => "raw",
                    'value'=>function($model){
                        $sub_html = \app\models\SupplierSearch::flagCrossBorder(true,$model->supplier_code);
                        return BaseServices::getSupplierName($model->supplier_code).$sub_html;
                    }
                ],

                [
                    'label'=>'SKU数量',
                    'value'=> function($model){
                        return PurchaseOrderItems::find()->where(['pur_number'=>$model->pur_number])->count('id');
                    },
                    'pageSummary' => true
                ],

                [
                    'label'=>'采购数量',
                    'value'=> function($model){
                        return PurchaseOrderItems::find()->where(['pur_number'=>$model->pur_number])->sum('ctq');
                    },
                    'pageSummary' => true
                ],

                [
                    'label'=>'总金额( RMB )',
                    "format" => "raw",
                    'visible'=>$bool,
                    'value'=>function($model){
                        return PurchaseOrderItems::getCountPrice($model->pur_number);
                    },
                    'pageSummary' => true
                ],

                [
                    'label'=>'运费',
                    'value'=>function($model) {
                        $freight = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->freight : 0;
                        return floatval($freight);
                    },
                ],

                [
                    'label' => '结算方式',
                    'value'=>function($model){
                        return $model->account_type ? \app\services\SupplierServices::getSettlementMethod($model->account_type) : '';
                    },
                ],
                'created_at',
                'audit_time',
                [
                    'label' =>'付款时间',
                    'value'=> function($model){
                        $pay_time = PurchaseOrderPay::findOne(['pur_number' => $model->pur_number]);
                        return $pay_time['payer_time'];
                    }
                ],
                [
                    'label'=>'拍单号',
                    "format" => "raw",
                    'value'=>function($model) {

                        $order_number = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->platform_order_number : '';

                        if(!$order_number) {
                            $order_number = !empty($model->orderOrders) ? $model->orderOrders->order_number : '';
                        }

                        return $order_number;
                    },

                ],
                [
                    'label'=>'传到仓库',
                    'attribute' => 'created_ats',
                    "format" => "raw",
                    'value'=>
                        function($model, $key, $index, $column){
                            return PurchaseOrderServices::getPush($model->is_push);
                        },
                ],

                [
                    'label'=>'信息修改状态',
                    'attribute' => 'created_ats',
                    "format" => "raw",
                    'value'=>
                        function($model) {
                            return PurchaseOrderServices::getShipfeesAuditStatus($model->shipfees_audit_status);
                        },
                ],













                [
                    'class' => 'kartik\grid\ActionColumn',
                    'dropdown' => true,
                    'width'=>'180px',
                    'template' => '<div>{edit}</div>
                               <div>{payment}</div>
                               <div>{print}</div> 
                               <div>{download}</div>
                               <div>{export}</div>
                               <div>{estimate}</div>
                               <div>{add-tracking}</div>
                               <div>{audit-backwards}</div>
                               <div>{add-note}</div>
                               <div>{edit-tracking}</div>
                               <div>{disagree}</div>
                               <p>{update-order}</p>
                               <p>{apply-breakage}</p>
                               <p>{exp-deal}</p>',

                    'buttons' => [

                        // 	已审批 3  等待到货 8  部分到货等待剩余 7
                        'edit' => function ($url, $model, $key) {
                           // if(in_array($model->purchas_status, [3, 7, 8]) && $model->refund_status !== 4 && $model->buyer == Yii::$app->user->identity->username) {
                                return Html::a('<i class="glyphicon glyphicon-ok"></i> 编辑采购单', ['edit', 'pur_number' => $model->pur_number], [
                                    'title'       => Yii::t('app', '编辑采购单'),
                                    'class'       => 'btn btn-xs edit',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal',
                                ]);
                           // }
                        },

                        'payment' => function ($url, $model, $key) {
                            // 全付款了就不能再付款了
                            //账期订单（周结、半月结、月结、两月结）全到货或部分到货不等待剩余的情况下，才能去请款
                            $account_period = yii::$app->params['account_period'];
                            $is_zhangqi = array_key_exists($model->account_type, $account_period);//是否是账期单
                            $zhangqi_status_array = [6=>'全到货',9=>'部分到货不等待剩余']; //全到货，部分到货不等待剩余
                            $is_purchas_status = array_key_exists($model->purchas_status, $zhangqi_status_array);//是否是全到货，部分到货不等待剩余

                            //非账期
                            $status_array = [5=>'部分到货',7=>'等待到货',8=>'部分到货等待剩余',6=>'全到货',9=>'部分到货不等待剩余'];
                            $no_zhangqi_status = array_key_exists($model->purchas_status, $status_array);//否账期单

                            //允许操作人
                            $is_user = ( ($model->buyer == Yii::$app->user->identity->username) || BaseServices::getIsAdmin(1) );

                            if(   ($is_zhangqi && $is_purchas_status && $is_user) || ( !$is_zhangqi && $is_user) ) {

                                return Html::a('<i class="glyphicon glyphicon-yen"></i> 申请付款', ['payment', 'pur_number' => $model->pur_number], [
                                    'title' => Yii::t('app', '申请付款'),
                                    'class' => 'btn btn-xs payment',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal',
                                ]);
                           }
                        },



                        /*  'print' => function ($url, $model, $key) {
                              return Html::a('<i class="glyphicon glyphicon-print"></i> 打印采购合同', ['print','id'=>$key,'pur_number'=>$model->pur_number], [
                                  'title' => Yii::t('app', '打印采购合同'),
                                  'class' => 'btn btn-xs red',
                                  'target'=>'_blank'
                              ]);
                          },*/
//                    'download' => function ($url, $model, $key) {
//                        return Html::a('<i class="glyphicon glyphicon-arrow-down"></i> 下载采购合同', ['download-zip','id'=>$key], [
//                            'title' => Yii::t('app', '下载采购合同 '),
//                            'class' => 'btn btn-xs purple'
//                        ]);
//                    },
//                    'export' => function ($url, $model, $key) {
//                        return Html::a('<i class="glyphicon glyphicon-export"></i> 导出PDF合同', ['export-pdf','id'=>$key], [
//                            'title' => Yii::t('app', '导出PDF合同 '),
//                            'class' => 'btn btn-xs purple'
//                        ]);
//                    },
//                    'estimate' => function ($url, $model, $key) {
//                        return Html::a('<i class="glyphicon glyphicon-magnet"></i> 预估采购合同', ['estimate','id'=>$key], [
//                            'title' => Yii::t('app', '预估采购合同'),
//                            'class' => 'btn btn-xs purple'
//                        ]);
//                    },


                        'add-tracking' => function ($url, $model, $key) {
                            return Html::a('<i class=" fa fa-fw fa-plus-square"></i> 添加跟踪记录', ['add-tracking','pur_number'=>$model->pur_number], [
                                'title' => Yii::t('app', '添加跟踪记录'),
                                'class' => 'btn btn-xs tracking',
                                //'data-toggle' => 'modal',
                                //'data-target' => '#create-modal',
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
                        'add-note' => function ($url, $model, $key) {
                            return Html::a('<i class=" fa fa-fw fa-comment"></i> 添加备注', ['add-purchase-note','id'=>$key,'pur_number'=>$model->pur_number,'flag'=>2], [
                                'title' => Yii::t('app', '添加备注'),
                                'class' => 'btn btn-xs note',
                                'target'=>'_blank',
                                //'data-toggle' => 'modal',
                                //'data-target' => '#create-modal',
                            ]);
                        },
                        'audit-backwardss' => function ($url, $model, $key) {
                            return Html::a('<i class=" glyphicon glyphicon-retweet"></i> 添加到货时间', ['arrival-date','id'=>$key], [
                                'title' => Yii::t('app', '添加到货时间'),
                                'class' => 'btn btn-xs arrival',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                        },
                        'disagree' => function ($url, $model, $key) {
                            // 如果是自己下的单 且 状态为部分到货等待剩余的
                            $pay_status = !empty($model->pay_status)?$model->pay_status:'';
                            if ($model->purchas_status=='8' && $model->buyer==Yii::$app->user->identity->username && ($pay_status==1 || $pay_status==2 || $pay_status==4)) {
                                return Html::a('<i class="fa fa-fw fa-close"></i> 取消部分到货等待剩余', ['cancel','id'=>$key], [
                                    'title' => Yii::t('app', '取消部分到货等待付款 '),
                                    'class' => 'btn btn-xs disagree purple',
                                    'data' => [
                                        'confirm' => '确定要取消么?',
                                    ],
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


                        'update-order' => function ($url, $model, $key) {
                            if ($model->buyer==Yii::$app->user->identity->username || BaseServices::getIsAdmin()) {
                                return Html::a('<i class="fa fa-fw fa-exclamation-triangle"></i> 修改订单', ['update-order', 'pur_number' => $model->pur_number], [
                                    'title' => Yii::t('app', '修改订单'),
                                    'class' => 'btn btn-xs disagree update-order',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal',
                                ]);
                            }
                        },


                        'exp-deal' => function ($url, $model, $key) {
                            $is_exp = PurchaseWarehouseAbnormalSearch::checkIsExp($model->pur_number);

                            if(!empty($is_exp) && $is_exp>0) {
                                $url = '';
                                if($is_exp == 1){
                                    $url = '/exp-ruku/index';
                                }else if($is_exp == 2){
                                    $url = '/exp-cipin/index';
                                }else if($is_exp == 3){
                                    $url = '/exp-zhijian/index';
                                }
                                return Html::a('<i class="fa fa-fw fa-exclamation-triangle"></i> 异常单处理', [$url, 'purchase_order_no' => $model->pur_number], [
                                    'title' => Yii::t('app', '异常单处理'),
                                    'target' => '_blank',
                                ]);
                            }
                        },





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
            'showPageSummary' => true,

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

$requestUrl = Url::toRoute('view');
$noteUrl = Url::toRoute('add-purchase-note');
$arrival='请选择需要标记到货日期的采购单';
$js = <<<JS

$(function(){
            $("a#submit-audit").click(function(){
                var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
                if(ids==''){
                    alert('请先选择!');
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
            
            $('#all').click(function() {
        var coll = $('input[name="all[]"]');
        if($(this).is(':checked')) {
            for(var i=0;i<coll.length;i++) {
                coll[i].checked=true;
            }
        } else {
            for(var i=0;i<coll.length;i++) {
                coll[i].checked = false;
            }
        }
    });

    function changeUrl(search, value)
    {
    return search.replace(/tab=([^&]*)/, value);
    }
    
    
    
    
    // print order
    $("#submit-audit").click(function() {
        
        
        var q = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        
        if(q == '') {
            layer.alert('请选择数据');
            return false;
        } else {
            var url = '/purchase-order/print-data';
            url = url + '?ids=' + q;
            window.open(url);
        }
    });
    
    // remark arrival date
    $('#arrival').click(function () {
        var s = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(s == '') {
            layer.alert('请选择订单');
            return false;
        } else {
            $.get($(this).attr('href'), {id: s}, function(data) {
                $('.modal-body').html(data);
            });
        }
    });
    
    

    
    // batch export
    $('#export-csv').click(function() {
        var ids = getSelected();
        window.location.href='export-csv?ids=' + ids;
    });
 
    
    
    // update-freight-discount
    $('.update-freight-discount').click(function() {
        $('.modal-body').html('');
        $('.modal-body').load($(this).attr('href'));
    });

            
            //批量申请付款
            $("a#all-payment").click(function(){
                var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
                if(ids==''){
                    alert('请先选择!');
                    return false;
                }else{
                    var url = $(this).attr("href");
                    url     = url+'?ids='+ids;
                    
                    $.get(url,function (data){
                        $('.modal-body').html(data);
                    });
                }
            });
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
    
    
    // 编辑跟踪记录
    $(document).on('click', '.edit-tracking', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    
    
     $(document).on('click', '.note', function () {
         $.get('{$noteUrl}', {pur_number:$(this).attr('value'),flag:1},
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
     
    // 编辑采购单
    $(document).on('click', '.edit', function () {
        $('.modal-body').html('');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    
     $(document).on('click', '#arrival', function () {
            var str='';
            //获取所有的值
            $("input[name='id[]']:checked").each(function(){
                str+=','+$(this).val();
                //alert(str);

            })
            str=str.substr(1);

         if (str == ''){

            $('.modal-body').html('$arrival');
         }else{

            $.get($(this).attr('href'), {id:str},
                function (data) {
                    $('.modal-body').html(data);
                }
            );

         }

    });


     //批量导出
     $('#export-csv').click(function() {
            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            /*if(ids==''){
                alert('请先选择!');
                return false;
            }else{*/
                 
                window.location.href='/purchase-order/export-csv?ids='+ids;
            /*}*/
     });
     
         //合同导出
    $('.payment-contract').click(function () {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if (ids == '') {
            alert('请勾选订单');
        } else {
            window.location.href='payment-contract?ids=' + ids;
        }
    });   
    
     $(document).on('click', '.apply-breakage', function() {
         $('.modal-body').html('');
         $('.modal-body').load($(this).attr('href'));
     });
     
     $(document).on('click', '.refund-handler', function() {
         $('.modal-body').html('');
         $('.modal-body').load($(this).attr('href'));
     });
     
     $('.update-order').click(function() {
         $('.modal-body').html('');
         $('.modal-body').load($(this).attr('href'));
     });

JS;
$this->registerJs($js);
?>