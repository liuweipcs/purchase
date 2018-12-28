<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\config\Vhelper;
use app\models\PurchaseOrder;


/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '跟踪到货';
$this->params['breadcrumbs'][] = $this->title;
?>
    <!--<p>
    You may change the content of this page by modifying
    the file <code><?/*= __FILE__; */?></code>.
</p>-->
    <div class="panel panel-default">
    <div class="panel-body">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
        <div class="panel-footer">
            <?= Html::a('导出', 'export',['class' => 'btn btn-info export','id'=>'export'])?>
        </div>
    </div>


    <div>

        <!--<p>
            <?/*= Html::a('打印采购单', ['print-data'], ['class' => 'btn btn-success print','id'=>'submit-audit','target'=>'_blank']) */?>
            <?/*= Html::a('申请批量付款', ['allpayment'], ['class' => 'btn btn-success all-payment','id'=>'all-payment','data-toggle' => 'modal', 'data-target' => '#create-modal',]) */?>
            <?/*= Html::a('标记到货日期', ['arrival-date'], ['class' => 'btn btn-success','id'=>'arrival','data-toggle' => 'modal',
                'data-target' => '#create-modal',])*/?>
            <?/*= Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv']) */?>
        </p>
        <h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span>温馨小提示:<span style="color: red">1:全额退款需要上级审核,部分退款直接进入财务收款模块。2：默认30分钟请求物流信息。3：想增加物流信息--请点击右边（动作 =》编辑跟踪记录）；第二条物流记录，请点击（动作 =》添加跟踪记录）<i class="fa fa-fw fa-smile-o"></i></h4>-->

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'options'=>[
                'id'=>'grid_purchase_order_items',
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
                /*[
                    'class' => 'kartik\grid\CheckboxColumn',
                    'name'=>"id" ,
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return ['value' => $model->pur_number];
                    }
                ],*/
                ['class' => 'kartik\grid\CheckboxColumn'],
                [
                    'label'=>'PO生成日期',
                    'value'=> function($model){
                        $create_time = PurchaseOrder::getAuditTime($model->pur_number);
                        return date('Y-m-d', strtotime($create_time));

                    },
                ],
                [
                    'label'=>'sku',
                    'format'=>'raw',
                    'value'=>function($model){
                        $subHtml = \app\models\ProductRepackageSearch::getPlusWeightInfo($model->sku,true,1);// 加重SKU标记
                        return $model->sku . $subHtml;
                    }
                ],
                [
                    'label'=>'缩略图',
                    "format" => "raw",
                    'value'=> function($model){
                        return \toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($model->sku)]);
                    },
                ],
                [
                    'label'=>'中文名称',
                    'headerOptions' => ['width' => '100','word-break'=>'break-all'],
//                    'style' => "word-break:break-all;",
                    'value'=> function($model){
//                        width="200" style="word-break:break-all;"
                        return $model->name;
                    },
                ],
                [
                    'label'=>'PO号',
                    'format'=>'raw',
                    'value'=> function($model){
                        $applyTime = \app\models\PurchaseOrderPay::find()
                            ->select('payer_time')
                            ->where(['pur_number'=>$model->pur_number])
                            ->andWhere(['<>','pay_status',0])
                            ->orderBy('id ASC')
                            ->scalar();
                        $warn1 = \app\models\PurchaseWarningStatus::find()->select('id')->where(['pur_number'=>$model->pur_number,'sku'=>$model->sku,'warn_status'=>3])->scalar();
                        $time = $applyTime ? (time()-strtotime($applyTime))/(60*60*24) : 0;
                        return $time>=10&&$warn1 ? '<span style="color: red">'.$model->pur_number.'</span>' : $model->pur_number;

                    },
                ],
                [
                    'label'=>'结算方式',
                    'value'=>function($model){
                        return \app\services\SupplierServices::getSettlementMethod($model->purNumber->account_type);
                    }
                ],
                [
                    'label'=>'采购员',
                    'value'=> function($model){
                        $buyer = PurchaseOrder::getBuyer($model->pur_number);
                        return $buyer;

                    },
                ],
                [
                    'label'=>'供应商名称',
                    'value'=> function($model){
                        $supplier_name = PurchaseOrder::getSupplierName($model->pur_number);
                        return $supplier_name;
                    },
                ],
                [
                    'label'=>'采购数量',
                    'pageSummary'=>true,
                    'value'=> function($model){
                        return $model->ctq;
                    },
                ],
                [
                    'label'=>'待入库数量',
                    'pageSummary'=>true,
                    'value'=> function($model){
                        return !empty($model->cty) ? $model->ctq-$model->cty : $model->ctq;
                    },
                ],
                [
                    'label'=>'已入库数量',
                    'value'=> function($model){
                        return empty($model->cty) ? 0 : $model->cty;
                    },
                ],
//                [
//                    'label'=>'预警状态',
//                    'format'=>'raw',
//                    'value'=> function($model){
//                    $html = '';
//                        if(!empty($model->warnStatus)){
//                            foreach ($model->warnStatus as $v){
//                                $html.= '<span style="color: red">'.\app\services\PurchaseOrderServices::getEarlyWarningStatus($v->warn_status).'<span/><br/>';
//                            }
//                        }
//                    return $html;
//                    },
//                ],
                [
                    'label'=>'预计到货时间',
                    'value'=> function($model){
                        return !empty($model->purNumber) ? $model->purNumber->date_eta : '';
                    },
                ],
                [
                    'label'=>'sku权均交期（天）',
                    'value'=>function($model){
                        return !empty($model->inlandAvgDelivery) ? round($model->inlandAvgDelivery->avg_delivery_time/(60*60*24),2) :0;
                    }
                ],
                [
                    'label'=>'权均交期时间',
                    'value'=>function($model){
                       $avg = !empty($model->inlandAvgDelivery) ? $model->inlandAvgDelivery->avg_delivery_time :0;
                       $audit_time = !empty($model->purNumber) ? $model->purNumber->audit_time : '';
                       return empty($audit_time) ? '' : date('Y-m-d H:i:s',strtotime($audit_time)+round($avg,0));
                    }
                ],
                [
                     'label'=>'是否超时',
                     'format'=>'raw',
                     'value'=>function($model){
                         $data = \app\models\WarehouseResults::find()->select('instock_date')->where(['pur_number'=>$model->pur_number,'sku'=>$model->sku])->scalar();
                         $avg = !empty($model->inlandAvgDelivery) ? $model->inlandAvgDelivery->avg_delivery_time :0;
                         $audit_time = !empty($model->purNumber) ? $model->purNumber->audit_time : '';
                         $fou = '<span style="color: green">否</span>';
                         $shi = '<span style="color: red">是</span>';
                         $instock = $data ? strtotime($data):time();
                         return empty($audit_time) ? $fou: (((strtotime($audit_time)+$avg) <= $instock)? $shi:$fou );
                     }
                ],
                [
                    'label'=>'审核通过时间',
                    'value'=> function($model){
                        return !empty($model->purNumber) ? $model->purNumber->audit_time : '';
                    },
                ],
                [
                    'label'=>'申请付款时间',
                    'format'=>'raw',
                    'value'=> function($model){
                        $applyTime = \app\models\PurchaseOrderPay::find()
                            ->select('application_time')
                            ->where(['pur_number'=>$model->pur_number])
                            ->andWhere(['<>','pay_status',0])
                            ->orderBy('id ASC')
                            ->scalar();
                        $warn = \app\models\PurchaseWarningStatus::find()->select('id')->where(['pur_number'=>$model->pur_number,'sku'=>$model->sku,'warn_status'=>1])->scalar();
                        $warnstatus = $warn ? '<span style="color: red">申请付款超时</span>' : '';
                        return $applyTime ? $applyTime.'</br>'.$warnstatus : ''.$warnstatus;
                    },
                ],
                [
                    'label'=>'付款时间',
                    'format'=>'raw',
                    'value'=> function($model){
                        $applyTime = \app\models\PurchaseOrderPay::find()
                            ->select('payer_time')
                            ->where(['pur_number'=>$model->pur_number])
                            ->andWhere(['<>','pay_status',0])
                            ->orderBy('id ASC')
                            ->scalar();
                        $warn = \app\models\PurchaseWarningStatus::find()->select('id')->where(['pur_number'=>$model->pur_number,'sku'=>$model->sku,'warn_status'=>2])->scalar();
                        $warnstatus = $warn ? '<span style="color: red">付款超时</span>' : '';
                        return $applyTime ? $applyTime.'</br>'.$warnstatus : ''.$warnstatus;
                    },
                ],
                [
                    'label'=>'物流信息',
                    "format" => "raw",
                    'value'=> function($model){
                        $data='';
                        if(!empty($model->purNumber->fbaOrderShip)){
                            foreach($model->purNumber->fbaOrderShip as $k => $value) {
                                if(!empty($value['cargo_company_id'])) {
                                    $s =!empty($value['cargo_company_id']) ? $value['cargo_company_id'] : '';
                                    $url ='https://www.kuaidi100.com/chaxun?com='.$s.'&nu='.$value['express_no'];
                                    $data .= "<a target='_blank' href='$url'>".$value['express_no']."</a><br/>";   //主要通过此种方式实现
                                }
                            }
                        }
                        $warn1 = \app\models\PurchaseWarningStatus::find()->select('id')->where(['pur_number'=>$model->pur_number,'sku'=>$model->sku,'warn_status'=>3])->scalar();
                        $warnstatus1 = $warn1 ? '<span style="color: red">获取物流超时</span>' : '';
                        $warn = \app\models\PurchaseWarningStatus::find()->select('id')->where(['pur_number'=>$model->pur_number,'sku'=>$model->sku,'warn_status'=>4])->scalar();
                        $warnstatus = $warn ? '<span style="color: red">签收超时</span>' : '';
                        $html = '物流编码:'.$data.$warnstatus1.'<br/>';
                        $html .= '签收时间:'.\app\models\ArrivalRecord::getDeliveryTime($model->pur_number,$model->sku).$warnstatus;
                        return $html;
                    },
                ],
                [
                    'label'=>'上架时间',
                    "format" => "raw",
                    'value'=> function($model){
                        $data = \app\models\WarehouseResults::find()->select('instock_date')->where(['pur_number'=>$model->pur_number,'sku'=>$model->sku])->one();
                        $warn = \app\models\PurchaseWarningStatus::find()->select('id')->where(['pur_number'=>$model->pur_number,'sku'=>$model->sku,'warn_status'=>5])->scalar();
                        $warnstatus = $warn ? '<span style="color: red">上架超时</span>' : '';
                        return empty($data)?$warnstatus:$data->instock_date.'</br>'.$warnstatus;
                    },
                ],
                /*[
                    'label'=>'异常数',
                    'attribute' => 'sku',
                    "format" => "raw",
                    'value'=> function($model){
                        return $model->sku;

                    },
                ],*/
                /*[
                    'label'=>'取消未到货',
                    'attribute' => 'sku',
                    "format" => "raw",
                    'value'=> function($model){
                        return $model->sku;
                    },
                ],*/
                [
                    'label'=>'备注',
                    "format" => "raw",
                    'value'=> function($model){
                        $create_time = !empty($model->purchaseEstimatedTime)?$model->purchaseEstimatedTime->create_time:'';
                        return Html::input('text','note',!empty($model->purchaseEstimatedTime)?$model->purchaseEstimatedTime->note:'',['readonly'=>'readonly','sku'=>$model->sku,'pur_number'=>$model->pur_number,'style'=>'width:200px']) . '<br />' . $create_time;
                    },
                ],
                /*[
                    'class' => 'kartik\grid\ActionColumn',
                    'dropdown' => true,
                    'width'=>'180px',
                    'template' => '<div>{edit}</div>
                               <div>{disagree}</div>',
                    'buttons'=>[
                    ],
                ],*/
                [
                    'label' => '仓库信息',
                    "format" => "raw",
                    'value'=>
                        function($model){
                            $data =Html::a('<span class="glyphicon glyphicon-scale" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看到货信息"></span>',
                                ['get-platform-detail'],[
                                    'class' => 'detail',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal','sku' =>$model->sku,'pur_number'=>$model->pur_number
                                ]);
                            return $data;
                        },

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
            'floatHeader' => true,
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

$requestUrl = Url::toRoute('view');
$noteUrl = Url::toRoute('add-purchase-note');
$arrival='请选择需要标记到货日期的采购单';
$js = <<<JS
//双击编辑报价
    $("input[name='note']").dblclick(function(){
            $(this).removeAttr("readonly");
        });
    //失焦添加readonly
    $("input[name='note']").change(function(){
        $(this).attr("readonly","true");
        var note = $(this).val();
        var sku   = $(this).attr('sku');
        var pur_number   = $(this).attr('pur_number');
        $.ajax({
            url:'update-follow-goods-note',
            data:{note:note,sku:sku,pur_number:pur_number},
            type: 'get',
            dataType:'json',
        });
       });
    
    $(document).on('click', '.detail', function () {
        var sku   = $(this).attr('sku');
        var pur_number   = $(this).attr('pur_number');
        $.get($(this).attr('href'), {sku:sku,pur_number:pur_number},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
JS;
$this->registerJs($js);
?>