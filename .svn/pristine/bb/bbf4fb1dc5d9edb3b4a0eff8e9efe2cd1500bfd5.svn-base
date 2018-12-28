<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\models\PurchaseOrderPay;
use app\services\BaseServices;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderItems;
use app\models\PurchaseReply;
use app\models\PurchaseOrderReceipt;
use mdm\admin\components\Helper;
$this->title = '海外仓-采购单';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    #grid_purchase_order p {
        margin: 6px;
    }
</style>
    <div class="box box-success">
        <div class="box-body">
            <?= $this->render('_search', ['model' => $searchModel]); ?>
        </div><!-- /.box-body -->
        <div class="box-footer">

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

            <?= Html::a('标记到货日期', ['arrival-date'], [
                'class' => 'btn btn-success',
                'id' => 'arrival',
                'data-toggle' => 'modal',
                'data-target' => '#create-modal'
            ]);
            ?>

            <?= Html::button('导出Excel', ['class' => 'btn btn-success','id' => 'export-csv']); ?>



            <?php
            if(Helper::checkRoute('audit-ship')) {
                echo '<a href="audit-ship" class="btn btn-warning">订单信息修改-审核</a>';
            }
            ?>



            <?php
            if($source == 2) {
                echo Html::button('创建合同', [
                    'class' => 'btn btn-success all-info',
                    'id' => 'create-compact',
                ]);
            }
            ?>

            <a href="/overseas-purchase-order/compact-list" class="btn btn-info" target="_blank">合同列表</a>






        </div><!-- box-footer -->
    </div><!-- /.box -->


    <ol style="color:red;">
        <li> 全额退款需要上级审核,部分退款直接进入财务收款模块。默认30分钟请求物流信息。</li>
        <li> 想增加物流信息，请点击右边（动作 =》编辑跟踪记录）；第二条物流记录，请点击（动作 =》添加跟踪记录）。</li>
        <li> 被驳回的退款单，可以点击退款状态，进行编辑，保存后会重新回到待财务收款。</li>
        <li> 合同下的任何一个单，修改运费或优惠额，合同都会暂时冻结，待运费优惠审核通过后，刷新合同即可更新合同相关金额信息。</li>
    </ol>







<?php if($source == 1): ?>

<div class="btn-group" style="margin-bottom: 10px;">
    <span class="btn btn-danger" disabled="disabled">合同单</span>
    <a href="?source=2" class="btn btn-default">网采单</a>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => [
        'id' => 'grid_purchase_order',
    ],
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'], input[name='".$dataProvider->getPagination()->pageParam."']",
    'pager' => [
        'options' => ['class' => 'pagination', 'style' => 'display:block;'],
        'class' => \liyunfang\pager\LinkPager::className(),
        'pageSizeList' => [10, 20, 50, 100,500,1000],
        'firstPageLabel' => '首页',
        'prevPageLabel' => '上一页',
        'nextPageLabel' => '下一页',
        'lastPageLabel' => '末页',
    ],
    'columns' => [
        [
            'class' => 'kartik\grid\CheckboxColumn',
            'name' => 'id',
            'checkboxOptions' => function ($model, $key, $index, $column) {
                return ['value' => $model->pur_number];
            }

        ],
            [
                'label' => '单号',
                'width' => '200px',
                'format' => 'raw',
                'value' => function($model, $url, $key) {
                    $data = '<p>'.PurchaseOrderServices::getPurchaseStatus($model->purchas_status).'</p>';

                    if($model->is_drawback == 2) {
                        $data .= '<p>采购单号：'.Html::a($model->pur_number, ['view', 'id' => $model->pur_number],[
                                //'target' => '_blank',
                                'class' => 'views-hetong',
                                        'data-toggle' => 'modal',
                                        'data-target' => '#create-modal',
                                'value' =>$model->pur_number,
                            ]).'<strong style="margin-left:10px;color: green;">T</strong></p>';

                    } else {
                        $data .= '<p>采购单号：'.Html::a($model->pur_number, ['view', 'id' => $model->pur_number],[
                            //'target' => '_blank',
                                        'class' => 'views-hetong',
                                        'data-toggle' => 'modal',
                                        'data-target' => '#create-modal',
                                'value' =>$model->pur_number,
                            ]).'</p>';
                    }


                    $cpn = !empty($model->purchaseCompact) ? $model->purchaseCompact->compact_number : '';
                    $data .= "<p>合同号：<a href='?source=1&compact_number={$cpn}' title='搜索合同'>".$cpn.'</a></p>';
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
                    $data .= Html::a('<span class="fa fa-fw fa-comment" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单备注"></span>', ['#'],
                        [
                            'class' => 'btn btn-xs note',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'value' =>$model->pur_number,
                            'id'=>$key,'pur_number'=>$model->pur_number
                        ]);


                    $data .= "<a href='/purchase-compact/view?cpn={$cpn}' title='查看合同详情' target='_blank'><span class='fa fa-fw fa-file-text-o' style='color: #f2adb1;margin-right: 10px;'></span></a>";

                    $data .= "<a href='/overseas-purchase-order/compact-list/?compact_number={$cpn}' title='去合同列表查看' target='_blank'><span class='glyphicon glyphicon-tags' style='color: #f2adb1;margin-right: 10px;'></span></a>";






                    return $data;
                }
            ],
            [
                'label'=>'采购到货状态',
                'attribute' => 'arrival_status',
                "format" => "raw",
                'value'=> function($model){
                    return PurchaseOrderServices::getArrivalStatusCss($model->arrival_status);
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
                'label'=>'付款状态',
                "format"=>"raw",
                'value'=>function($model){
                    if(!empty($model->pay_status)) {
                       return PurchaseOrderServices::getPayStatusType($model->pay_status);
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
                        // return $data = !empty($model->refund_status) ? PurchaseOrderServices::getReceiptStatusCss($model->refund_status) : '';
                    }
                }
            ],
            [
                'label'=>'运输方式',
                "format" => "raw",
                'value'=>
                    function($model){

                        $data =PurchaseOrderServices::getShippingMethod($model->shipping_method).'<br/>';   //主要通过此种方式实现

                        if(!empty($model->fbaOrderShip)){
                            foreach($model->fbaOrderShip as $k => $value) {
                                if(!empty($value['cargo_company_id'])) {
                                    $s =!empty($value['cargo_company_id']) ? $value['cargo_company_id'] : '';
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
                'label' => '信息',
                'format' => 'raw',
                'value' => function($model) {
                    $data = '<p>供应商：'.$model->supplier_name.'</p>';
                    if(!empty($model->is_transit) && $model->is_transit == 1 && $model->transit_warehouse) {
                        $text = BaseServices::getWarehouseCode($model->transit_warehouse);
                        $text .= !empty($model->warehouse_code) ? '-'.BaseServices::getWarehouseCode($model->warehouse_code) : '';
                        $data .= '<p>仓库：'.$text.'</p>';
                    } else {
                        $text = !empty($model->warehouse_code) ? BaseServices::getWarehouseCode($model->warehouse_code) : '';
                        $data .= '<p>仓库：'.$text.'</p>';
                    }
                    $data .= '<p>采购员：'.$model->buyer.'</p>';
                    return $data;
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
                'value'=>function($model){
                    return PurchaseOrderItems::getCountPrice($model->pur_number);
                },
                'pageSummary' => true
            ],

            [
                'label'=>'运费',
                "format" => "raw",
                'value'=>function($model) {


                    $freight = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->freight : 0;



                    return $freight;


                },
            ],

            [
                'label' => '结算方式',
                'value'=>function($model){
                    return $model->account_type ? \app\services\SupplierServices::getSettlementMethod($model->account_type) : '';
                },
            ],
            [
                'label'=>'时间',
                "format"=>"raw",
                'value'=>function($model){
                    $data=Yii::t('app','创建时间：').$model->created_at."<br/>";
                    $data.=Yii::t('app','审核时间：').$model->audit_time."<br/>";
                    $instock_date = app\models\WarehouseResults::find()->select('instock_date')->where(['pur_number'=>$model->pur_number])->orderBy('id desc')->scalar();
                    $data .= '<span style="color:#00a65a">入库时间:'.$instock_date.'</span><br/>';
                    return $data;
                }
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
                'label'=>'采购异常回复',
                "format" => "raw",
                'value'=>
                    function($model) {
                        $data = '';
                        $reply_info = PurchaseReply::getReplyInfo($model->pur_number,1);

                        if (!empty($reply_info)) {
                            foreach ($reply_info as $k => $v) {
                                $data = "回复时间：{$v['create_time']}<br />回复内容：{$v['note']}";
                                break;
                            }
                        }
                        return $data;
                    },
            ],
            [
                'label'=>'销售反馈',
                "format" => "raw",
                'value'=>
                    function($model) {
                        $data = '';
                        $reply_info = PurchaseReply::getReplyInfo($model->pur_number,2);

                        if (!empty($reply_info)) {
                            foreach ($reply_info as $k => $v) {
                                $data = "提交时间：{$v['create_time']}<br />反馈内容：{$v['note']}";
                                break;
                            }
                        }
                        return $data;
                    },
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => true,
                'width' => '180px',
                'template' => '<p>{edit}</p>
                               <p>{cancellations}</p>
                               <p>{remove}</p>
                               <p>{payment}</p>
                               <p>{print}</p> 
                               <p>{download}</p>
                               <p>{export}</p>
                               <p>{estimate}</p>
                               <p>{add-tracking}</p>
                               <p>{audit-backwards}</p>
                               <p>{add-note}</p>
                               <p>{edit-tracking}</p>
                               <p>{disagree}</p>
                               <p>{update-order}</p>
                               <p>{apply-breakage}</p>
                               <p>{add-sale-reply}</p>
                               <p>{add-abnormal-reply}</p>',
                'buttons' => [
                    'cancellations' => function ($url, $model, $key) {
                        //如果自己下的单
                        //已审批、等待到货、部分到货等待剩余
                        $username = Yii::$app->user->identity->username;
                        $arr = [3, 7, 8];
                        if ( ($model->buyer==$username || BaseServices::getIsAdmin()) && in_array($model->purchas_status, $arr) ) {
                                return Html::a('<i class="glyphicon glyphicon-magnet"></i> 订单作废', ['cancellations', 'pur_number' => $model->pur_number, 'id' => $model->id], [
                                    'title' => Yii::t('app', '取消部分数量'),
                                    'class' => 'btn btn-xs cancellations',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal',
                                ]);
                            }
                    },
                    'edit' => function ($url, $model, $key) {
                        $arr = ['3','4'];
                        $arrs = ['3','8','7'];
                        if(in_array($model->purchas_status, $arrs) && !in_array($model->refund_status, $arr) && $model->buyer == Yii::$app->user->identity->username)
                        {
                            return Html::a('<i class="glyphicon glyphicon-ok"></i> 编辑采购单', ['edit', 'pur_number' => $model->pur_number], [
                                'title'       => Yii::t('app', '编辑采购单'),
                                'class'       => 'btn btn-xs edit',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                        }
                    },
                    'remove' => function ($url, $model, $key) {
                        if($model->buyer == Yii::$app->user->identity->username)
                        {
                            return Html::a('<i class="glyphicon glyphicon-trash"></i> 作废采购单', ['remove-compact-order', 'opn' => $model->pur_number], [
                                'title'       => Yii::t('app', '作废采购单'),
                                'class'       => 'btn btn-xs remove',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                        }
                    },



                    'payment' => function ($url, $model, $key) {
                        //if (!in_array($model->purchas_status,[4,10])  && ($model->buyer == Yii::$app->user->identity->username)) {


                        if($model->purchaseCompact) {
                            $cpn = $model->purchaseCompact->compact_number;

                            return Html::a('<i class="glyphicon glyphicon-yen"></i> 申请付款', ['payment', 'compact_number' => $cpn], [
                                'title' => '申请付款',
                                'class' => 'btn btn-xs payment',
                            ]);
                        }


                        //}
                    },


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
                    'add-abnormal-reply' => function ($url, $model, $key) {
                        if (Helper::checkRoute('add-abnormal-reply')) {
                            return Html::a('<i class=" fa fa-fw fa-comment"></i> 采购异常回复', ['add-abnormal-reply','id'=>$key,'pur_number'=>$model->pur_number,'flag'=>2], [
                                'title' => Yii::t('app', '采购异常回复'),
                                'class' => 'btn btn-xs add-abnormal-reply',
                            ]);
                        }
                    },
                    'add-sale-reply' => function ($url, $model, $key) {
                        if (Helper::checkRoute('add-sale-reply')) {
                            return Html::a('<i class=" fa fa-fw fa-comment"></i> 销售反馈', ['add-sale-reply','id'=>$key,'pur_number'=>$model->pur_number,'flag'=>2], [
                                'title' => Yii::t('app', '销售反馈'),
                                'class' => 'btn btn-xs add-sale-reply',
                            ]);
                        }
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
                        if($model->buyer == Yii::$app->user->identity->username || Yii::$app->user->identity->username == '王维') {
                            if($model->purchaseCompact) {
                                $cpn = $model->purchaseCompact->compact_number;
                                return Html::a('<i class="fa fa-fw fa-plus-square"></i> 修改运费', ['update-freight-discount', 'opn' => $model->pur_number, 'cpn' => $cpn], [
                                    'title' => Yii::t('app', '修改运费'),
                                    'class' => 'btn btn-xs update-freight-discount',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal',
                                ]);

                            }
                        }
                    },

                ],

            ],
        ],
        'containerOptions' => ["style" => "overflow: auto"],
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
        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            'type' => 'success'
        ],
    ]);
    ?>
<?php

$requestUrl = Url::toRoute('view');
$noteUrl = Url::toRoute('add-purchase-note');
$editShipUrl = Url::toRoute('edit-ship');
$arrival='请选择需要标记到货日期的采购单';

$js = <<<JS

$(function() {

    $(document).on('click', '.cancellations', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });


    function changeUrl(search, value)
    {
    return search.replace(/tab=([^&]*)/, value);
    }

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








    $(document).on('click', '.refund-handler', function() {
    $('.modal-body').html('');
    $('.modal-body').load($(this).attr('href'));
    });

    
    

    
    
    

    $(document).on('click', '.views-hetong', function () {
    $('.modal-body').html('');

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
        $.get('{$noteUrl}', {pur_number:$(this).attr('value'),flag:1},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.add-abnormal-reply', function () {
        $.get('add-abnormal-reply', {pur_number:$(this).attr('value'),flag:1},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.add-sale-reply', function () {
        $.get('add-sale-reply', {pur_number:$(this).attr('value'),flag:1},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.edit-ship', function () {
    $.get($(this).attr("href"),
    function (data) {
    $('.modal-body').html(data);
    }
    );
    });
    $(document).on('click', '.audit-ship', function () {
    $.get($(this).attr("href"),
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
        $.get($(this).attr('href'), {id:$(this).attr('value')},function (data) {
            $('.modal-body').html(data);
        });
    });
    
    $(document).on('click', '#note', function () {
        $.get($(this).attr('href'), {id:$(this).attr('value')},function (data) {
            $('.modal-body').html(data);
        });
    });
    
    $(document).on('click', '.edit', function () {
        $.get($(this).attr('href'), {},function (data) {
            $('.modal-body').html(data);
        });
    });
    
    $(document).on('click', '#arrival', function () {
        var str='';
        // 获取所有的值
        $("input[name='id[]']:checked").each(function(){
            str+=','+$(this).val();
        });
        str = str.substr(1);
        if (str == ''){
            $('.modal-body').html('$arrival');
        } else {
            $.get($(this).attr('href'), {id:str},function (data) {
                $('.modal-body').html(data);
            });
        }
    });
    
    // 编辑跟踪记录
    $(document).on('click', '.edit-tracking', function () {
        $.get($(this).attr('href'), {}, function (data) {
            $('.modal-body').html(data);
        });
    });
    
    // 批量导出
    $('#export-csv').click(function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        window.location.href='export-csv?source='+ $source +'&ids='+ids;
    });

    $('a.payment').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });

    $(document).on('click', '.refund-handler', function() {
        $('.modal-body').html('');
        $('.modal-body').load($(this).attr('href'));
    });

    $(document).on('click', '.apply-breakage', function() {
        $('.modal-body').html('');
        $('.modal-body').load($(this).attr('href'));
    });

    $('.update-order').click(function() {
        $('.modal-body').html('');
        $('.modal-body').load($(this).attr('href'));
    });
    
    $('.update-freight-discount').click(function() {
        $('.modal-body').html('');
        $('.modal-body').load($(this).attr('href'));
    });
    
    
    
    $('#create-compact').click(function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids.length == 0) {
        layer.alert('请选择要生成合同的订单。');
        return false;
        }
        window.open('/overseas-purchase-order/create-compact?ids='+ids+'&platform=2');
    });
    
    
    // 合同单作废
    $('a.remove').click(function() {
        $('.modal-body').html('');
        $('.modal-body').load($(this).attr('href'));
    });
    
    
    
    
    
});
JS;
$this->registerJs($js);
?>

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
        'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
        'pager' => [
            'options' => ['class' => 'pagination', 'style' => 'display:block;'],
            'class' => \liyunfang\pager\LinkPager::className(),
            'pageSizeList' => [10, 20, 50, 100,500,1000],
            'firstPageLabel' => '首页',
            'prevPageLabel' => '上一页',
            'nextPageLabel' => '下一页',
            'lastPageLabel' => '末页',
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
            'label' => '单号',
            'attribute' => 'pur_numbers',
            'width' => '200px',
            "format" => "raw",
            'value' => function($model, $url, $key) {

                $data = '<p>'.PurchaseOrderServices::getPurchaseStatus($model->purchas_status).'</p>';

                $data .= '采购单号：'.Html::a($model->pur_number, ['view'],['id' => 'views',
                    'data-toggle' => 'modal',
                    'data-target' => '#create-modal',
                    'value' =>$model->pur_number,
                ]);

                if($model->is_drawback == 2) {
                    $data .= '<strong style="margin-left:20px;color: green;">T</strong>';
                }



                $order_number = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->platform_order_number : '';

                $data .= '<p>拍单号：'.$order_number.'</p>';

                $cpn = !empty($model->purchaseCompact) ? $model->purchaseCompact->compact_number : '';//合同号 合同列表新增删除功能
                $data .= "<p>合同号：<a href='?source=1&compact_number={$cpn}' title='搜索合同'>".$cpn.'</a></p>';

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


                $data .= Html::a('<span class="fa fa-fw fa-comment" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单备注"></span>', ['#'],
                    [
                        'class' => 'btn btn-xs note',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                        'value' =>$model->pur_number,
                        'id'=>$key,'pur_number'=>$model->pur_number
                    ]);




                return $data;
            },

        ],
        [
            'label'=>'采购到货状态',
            'attribute' => 'arrival_status',
            "format" => "raw",
            'value'=> function($model){
                return PurchaseOrderServices::getArrivalStatusCss($model->arrival_status);
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
            'label'=>'付款状态',
            "format"=>"raw",
            'value'=>function($model){
                if(!empty($model->pay_status)) {
                    return PurchaseOrderServices::getPayStatusType($model->pay_status);
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
                    // return $data = !empty($model->refund_status) ? PurchaseOrderServices::getReceiptStatusCss($model->refund_status) : '';
                }
            }
        ],
        [
            'label'=>'运输方式',
            "format" => "raw",
            'value'=>
                function($model){

                    $data =PurchaseOrderServices::getShippingMethod($model->shipping_method).'<br/>';   //主要通过此种方式实现

                    if(!empty($model->fbaOrderShip)){
                        foreach($model->fbaOrderShip as $k => $value) {
                            if(!empty($value['cargo_company_id'])) {
                                $s =!empty($value['cargo_company_id']) ? $value['cargo_company_id'] : '';
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
            'label' => '信息',
            'format' => 'raw',
            'value' => function($model) {
                $data = '<p>供应商：'.$model->supplier_name.'</p>';
                if(!empty($model->is_transit) && $model->is_transit == 1 && $model->transit_warehouse) {
                    $text = BaseServices::getWarehouseCode($model->transit_warehouse);
                    $text .= !empty($model->warehouse_code) ? '-'.BaseServices::getWarehouseCode($model->warehouse_code) : '';
                    $data .= '<p>仓库：'.$text.'</p>';
                } else {
                    $text = !empty($model->warehouse_code) ? BaseServices::getWarehouseCode($model->warehouse_code) : '';
                    $data .= '<p>仓库：'.$text.'</p>';
                }
                $data .= '<p>采购员：'.$model->buyer.'</p>';
                return $data;
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
            'value'=>function($model){
                return PurchaseOrderItems::getCountPrice($model->pur_number);
            },
            'pageSummary' => true
        ],

        [
            'label'=>'运费',
            "format" => "raw",
            'value'=>function($model) {


                $freight = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->freight : 0;



                return $freight;


            },
        ],

        [
            'label' => '结算方式',
            'value'=>function($model){
                return $model->account_type ? \app\services\SupplierServices::getSettlementMethod($model->account_type) : '';
            },
        ],
        [
            'label'=>'时间',
            "format"=>"raw",
            'value'=>function($model){
                $data=Yii::t('app','创建时间：').$model->created_at."<br/>";
                $data.=Yii::t('app','审核时间：').$model->audit_time."<br/>";
                $instock_date = app\models\WarehouseResults::find()->select('instock_date')->where(['pur_number'=>$model->pur_number])->orderBy('id desc')->scalar();
                    $data .= '<span style="color:#00a65a">入库时间:'.$instock_date.'</span><br/>';
                return $data;
            }
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
            'label'=>'采购异常回复',
            "format" => "raw",
            'value'=>
                function($model) {
                    $data = '';
                    $reply_info = PurchaseReply::getReplyInfo($model->pur_number,1);

                    if (!empty($reply_info)) {
                        foreach ($reply_info as $k => $v) {
                            $data = "回复时间：{$v['create_time']}<br />回复内容：{$v['note']}";
                            break;
                        }
                    }
                    return $data;
                },
        ],
        [
            'label'=>'销售反馈',
            "format" => "raw",
            'value'=>
                function($model) {
                    $data = '';
                    $reply_info = PurchaseReply::getReplyInfo($model->pur_number,2);

                    if (!empty($reply_info)) {
                        foreach ($reply_info as $k => $v) {
                            $data = "提交时间：{$v['create_time']}<br />反馈内容：{$v['note']}";
                            break;
                        }
                    }
                    return $data;
                },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => true,
            'width' => '180px',
            'template' => '<div>{edit}</div>
               <div>{cancellations}</div>
               <div>{payment}</div>
               <div>{print}</div> 
               <div>{download}</div>
               <div>{export}</div>
               <div>{estimate}</div>
               <div>{add-tracking}</div>
               <div>{audit-backwards}</div>
               <div>{add-note}</div>
               <div>{add-abnormal-reply}</div>
               <div>{add-sale-reply}</div>
               <div>{edit-tracking}</div>
               <div>{disagree}</div>
               <p>{update-order}</p>
               <p>{apply-breakage}</p>',
            'buttons'=>[
                'cancellations' => function ($url, $model, $key) {
                        //如果自己下的单
                        //已审批、等待到货、部分到货等待剩余
                        $username = Yii::$app->user->identity->username;
                        $arr = [3, 7, 8];
                        if ( ($model->buyer==$username || BaseServices::getIsAdmin()) && in_array($model->purchas_status, $arr) ) {
                                return Html::a('<i class="glyphicon glyphicon-magnet"></i> 订单作废', ['cancellations', 'pur_number' => $model->pur_number, 'id' => $model->id], [
                                    'title' => Yii::t('app', '取消部分数量'),
                                    'class' => 'btn btn-xs cancellations',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal',
                                ]);
                            }
                    },
                'edit' => function ($url, $model, $key) {
                    $arr= ['3','4'];
                    $arrs = ['3','8','7'];
                    if (in_array($model->purchas_status,$arrs) && !in_array($model->refund_status,$arr) && $model->buyer==Yii::$app->user->identity->username)
                    {
                        return Html::a('<i class="glyphicon glyphicon-ok"></i> 编辑采购单', ['edit', 'pur_number' => $model->pur_number], [
                            'title'       => Yii::t('app', '编辑采购单'),
                            'class'       => 'btn btn-xs edit',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    }
                },



                    'payment' => function ($url, $model, $key) {
                        if (!in_array($model->purchas_status,[4,10])  && ($model->buyer == Yii::$app->user->identity->username||(in_array(Yii::$app->user->identity->username,['史家松','张建蓉','王维'])))) {
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
                'add-abnormal-reply' => function ($url, $model, $key) {
                    if (Helper::checkRoute('add-abnormal-reply')) {
                        return Html::a('<i class=" fa fa-fw fa-comment"></i> 采购异常回复', ['add-abnormal-reply','id'=>$key,'pur_number'=>$model->pur_number,'flag'=>2], [
                            'title' => Yii::t('app', '采购异常回复'),
                            'class' => 'btn btn-xs add-abnormal-reply',
                        ]);
                    }
                },
                'add-sale-reply' => function ($url, $model, $key) {
                    if (Helper::checkRoute('add-sale-reply')) {
                        return Html::a('<i class=" fa fa-fw fa-comment"></i> 销售反馈', ['add-sale-reply','id'=>$key,'pur_number'=>$model->pur_number,'flag'=>2], [
                            'title' => Yii::t('app', '销售反馈'),
                            'class' => 'btn btn-xs add-sale-reply',
                        ]);
                    }
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
                    if($model->buyer == Yii::$app->user->identity->username) {
                        return Html::a('<i class="fa fa-fw fa-exclamation-triangle"></i> 修改订单', ['update-order', 'pur_number' => $model->pur_number], [
                            'title' => Yii::t('app', '修改订单'),
                            'class' => 'btn btn-xs disagree update-order',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    }
                },



            ],

        ],
    ],
    'containerOptions' => ["style" => "overflow: auto"],
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
    'exportConfig' => [
        GridView::EXCEL => [],
    ],
    'panel' => [
        'type' => 'success'
    ],
]);
?>






<?php

$requestUrl = Url::toRoute('view');
$noteUrl = Url::toRoute('add-purchase-note');
$editShipUrl = Url::toRoute('edit-ship');
$arrival='请选择需要标记到货日期的采购单';

$js = <<<JS

$(function() {


    $(document).on('click', '.cancellations', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

    function changeUrl(search, value)
    {
    return search.replace(/tab=([^&]*)/, value);
    }

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








    $(document).on('click', '.refund-handler', function() {
    $('.modal-body').html('');
    $('.modal-body').load($(this).attr('href'));
    });

    
    

    
    
    

    $(document).on('click', '#views', function () {
    $('.modal-body').html('');

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
    $.get('{$noteUrl}', {pur_number:$(this).attr('value'),flag:1},
    function (data) {
    $('.modal-body').html(data);
    }
    );
    });
    $(document).on('click', '.add-abnormal-reply', function () {
        $.get('add-abnormal-reply', {pur_number:$(this).attr('value'),flag:1},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.add-sale-reply', function () {
        $.get('add-sale-reply', {pur_number:$(this).attr('value'),flag:1},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.edit-ship', function () {
    $.get($(this).attr("href"),
    function (data) {
    $('.modal-body').html(data);
    }
    );
    });
    $(document).on('click', '.audit-ship', function () {
    $.get($(this).attr("href"),
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
        $.get($(this).attr('href'), {id:$(this).attr('value')},function (data) {
            $('.modal-body').html(data);
        });
    });
    
    $(document).on('click', '#note', function () {
        $.get($(this).attr('href'), {id:$(this).attr('value')},function (data) {
            $('.modal-body').html(data);
        });
    });
    
    $(document).on('click', '.edit', function () {
        $.get($(this).attr('href'), {},function (data) {
            $('.modal-body').html(data);
        });
    });
    
    $(document).on('click', '#arrival', function () {
        var str='';
        // 获取所有的值
        $("input[name='id[]']:checked").each(function(){
            str+=','+$(this).val();
        });
        str = str.substr(1);
        if (str == ''){
            $('.modal-body').html('$arrival');
        } else {
            $.get($(this).attr('href'), {id:str},function (data) {
                $('.modal-body').html(data);
            });
        }
    });
    
    // 编辑跟踪记录
    $(document).on('click', '.edit-tracking', function () {
        $.get($(this).attr('href'), {}, function (data) {
            $('.modal-body').html(data);
        });
    });
    
    // 批量导出
    $('#export-csv').click(function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        window.location.href='export-csv?source='+ $source +'&ids='+ids;

    });

    $('a.payment').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });

    $(document).on('click', '.refund-handler', function() {
        $('.modal-body').html('');
        $('.modal-body').load($(this).attr('href'));
    });

    $(document).on('click', '.apply-breakage', function() {
        $('.modal-body').html('');
        $('.modal-body').load($(this).attr('href'));
    });

    $('.update-order').click(function() {
        $('.modal-body').html('');
        $('.modal-body').load($(this).attr('href'));
    });
    
    $('#create-compact').click(function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids.length == 0) {
        layer.alert('请选择要生成合同的订单。');
        return false;
        }
        window.open('/overseas-purchase-order/create-compact?ids='+ids);
    });
    
});

JS;
$this->registerJs($js);
?>
<?php endif; ?>

<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',

    ],
]);
Modal::end();
?>