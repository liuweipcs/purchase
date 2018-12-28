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

    <div class="panel panel-default">
        <div class="panel-body">
            <?= $this->render('_overseas-search', ['model' => $searchModel]); ?>
        </div>
        <div class="panel-footer">
            <?= Html::a('导出', 'oversea-export',['class' => 'btn btn-info export','id'=>'export'])?>
        </div>
    </div>
    <div>
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

                [
                    'label'=>'PO生成日期',
                    'value'=> function($model){
                        $create_time = PurchaseOrder::getAuditTime($model->pur_number);
                        return date('Y-m-d', strtotime($create_time));

                    },
                ],
                'sku',
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
                    'label'=>'中转仓库',
                    'value'=>function($model){
                        return $model->purNumber ? \app\services\PurchaseOrderServices::getWarehouseCode($model->purNumber->transit_warehouse) : '';
                    }
                ],
                [
                    'label'=>'采购仓库',
                    'value'=>function($model){
                        return $model->purNumber ? \app\services\PurchaseOrderServices::getWarehouseCode($model->purNumber->warehouse_code) : '';
                    }
                ],
                [
                    'label'=>'结算方式',
                    'value'=>function($model){
                        return \app\services\SupplierServices::getSettlementMethod($model->purNumber->account_type);
                    }
                ],
                [
                    'label'=>'付款状态',
                    'format'=>'raw',
                    'value'=>function($model){
                        $state = \app\models\PurchaseOrderPay::getOrderPayStatus($model->pur_number);
                        if(!$state) {
                            $html =  \app\services\PurchaseOrderServices::getPayStatusType(1).'<br/>';
                        } else {
                            $html = '';
                            foreach($state as $v) {
                                $html .= \app\services\PurchaseOrderServices::getPayStatusType($v['pay_status'])."<br/>";
                            }
                        }
                        $ratio = \app\models\PurchaseOrderPayType::find()->select('settlement_ratio')->where(['pur_number'=>$model->pur_number])->scalar();
                        $html.= '结算比例:'.$ratio;
                        return $html;
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
                    'value'=> function($model){
                        return $model->ctq;
                    },
                ],
                [
                    'label'=>'待入库数量',
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
                [
                    'label'=>'预计到货时间',
                    'value'=> function($model){
                        return !empty($model->purNumber) ? $model->purNumber->date_eta : '';
                    },
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
                [
                    'label'=>'权均交期（天）',
                    'value'=>function($model){
                        $arrivalAvg = \app\models\HwcAvgDeliveryTime::find()->select('delivery_total,purchase_time')->where(['sku'=>$model->sku])->asArray()->one();
                        return $arrivalAvg&&$arrivalAvg['purchase_time']!=0 ? sprintf('%.2f',$arrivalAvg['delivery_total']/($arrivalAvg['purchase_time']*60*60*24)) : 0;
                    }
                ],
                [
                    'label'=>'权均交期剩余时间（天）',
                    'value'=>function($model){
                        $audit = $model->purNumber->audit_time;
                        $arrivalAvg = \app\models\HwcAvgDeliveryTime::find()->select('delivery_total,purchase_time')->where(['sku'=>$model->sku])->asArray()->one();
                        $avgtime = $arrivalAvg&&$arrivalAvg['purchase_time']!=0 ? $arrivalAvg['delivery_total']/($arrivalAvg['purchase_time']) : 0;
                        return sprintf('%.2f',($avgtime-(time()-strtotime($audit)))/(60*60*24));
                    }
                ],
                [
                    'label'=>'备注',
                    "format" => "raw",
                    'value'=> function($model){
                        $create_time = !empty($model->purchaseEstimatedTime)?$model->purchaseEstimatedTime->create_time:'';
                        return Html::input('text','note',!empty($model->purchaseEstimatedTime)?$model->purchaseEstimatedTime->note:'',['readonly'=>'readonly','sku'=>$model->sku,'pur_number'=>$model->pur_number,'style'=>'width:200px']) . '<br />' . $create_time;
                    },
                ],
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