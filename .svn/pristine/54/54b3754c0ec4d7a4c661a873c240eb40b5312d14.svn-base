<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use yii\bootstrap\Modal;
$this->title = '收款通知';
$this->params['breadcrumbs'][] = '费用管理';
$this->params['breadcrumbs'][] = '应收';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-default">
    <div class="panel-body">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="panel-footer">
        <?= Html::a('导出execl', ['export-cvs'], ['class' => 'btn btn-success print','id'=>'bulk-execl','target'=>'_blank']) ?>
    </div>
</div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options'=>[
            'id'=>'grid_purchase_order',
        ],
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
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
                'label'=>'基本信息',
                'attribute' => 'pur_numbers',
                "format" => "raw",
                'value'=>
                    function($model, $key, $index, $column){
                        $sub_html = \app\models\SupplierSearch::flagCrossBorder(true,$model->supplier_code);
                        $data =Yii::t('app','供应商').'：'.$model->supplier['supplier_name'].$sub_html."<br/>";
                       // $data .=Yii::t('app','交易号').'：'.$model->payment_cycle."<br/>";
                        $data.=Yii::t('app','申请单号').'：'.$model->requisition_number."<br/>";
                        $data.=Yii::t('app','采购单号').'：'.$model->pur_number."<br/>";
                        $data.=Yii::t('app','支付方式').'：'.SupplierServices::getDefaultPaymentMethod($model->pay_type)."<br/>";
                        $data.=Html::a('<span class="fa fa-fw fa-comment" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单备注"></span>', ['purchase-order/get-purchase-note'],['id' => 'note',
                                                                                                                                                                                                     'data-toggle' => 'modal',
                                                                                                                                                                                                     'data-target' => '#create-modal','value' =>$model->pur_number,
                        ]);

                        return $data;
                    },

            ],
            [
                'label'=>'名称',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        return $model->pay_name;
                    },

            ],
            [
                'label'=>'金额/币种',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data = '<span style=color:#E06B26;font-weight:bold>'.$model->pay_price.'</span>&nbsp;&nbsp;';
                        $data.= $model->currency;
                        return $data;
                    },

            ],
//            [
//                'label'=>'银行信息',
//                'attribute' => 'ids',
//                "format" => "raw",
//                'value'=>
//                    function($model){
//                        if ($model->pay_type ==3)
//                        {
//                            $data =Yii::t('app','支行').'：'.$model->pur_number."<br/>";
//                            $data.=Yii::t('app','开户').'：'.$model->requisition_number."<br/>";
//                        } else {
//                            $data =Yii::t('app','支行').'：'.''."<br/>";
//                            $data.=Yii::t('app','开户').'：'.''."<br/>";
//                        }
//
//                        return $data;
//                    },
//
//            ],
            [
                'label'=>'状态',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model) {
                        return PurchaseOrderServices::getReceiptStatusCss($model->pay_status);
                    },
            ],
//            [
//                'label'=>'补货方式',
//                'attribute' => 'ids',
//                "format" => "raw",
//                'value'=>
//                    function($model){
//                       // $data = PurchaseOrderServices::getPurType($model->purchaseOrder['pur_type']);
//                        //return $data;
//                    },
//
//            ],
            [
                'label'=>'备注',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        return $model->review_notice;
                    },

            ],
            [
                'label'=>'操作人',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data  = !empty($model->applicant)?Yii::t('app','申请人:').BaseServices::getEveryOne($model->applicant).'<br/>':Yii::t('app','申请人:').''.'<br/>';
                        $data .= !empty($model->payer)?Yii::t('app','收款人:').BaseServices::getEveryOne($model->payer).'<br/>':Yii::t('app','收款人:').''.'<br/>';
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
                        $data .= Yii::t('app','收款:').$model->payer_time;
                        return $data;
                    },

            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => '{complete}{remarks}',
                'buttons'=>[
                    'complete' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 详细', ['view','id'=>$key], [
                            'title' => Yii::t('app', '采购明细'),
                            'class' => 'btn btn-xs red',
                            'id' =>'views',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    },
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 收款明细', ['purchase-order/view','id'=>$model->pur_number], [
                            'title' => Yii::t('app', '采购明细'),
                            'class' => 'btn btn-xs red',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'id'=>'views',
                        ]);
                    },
                    'remarks' => function ($url, $model, $key) {

                        return Html::a('<i class="fa fa-fw fa-comment"></i> 添加采购单备注', ['purchase-order/add-purchase-note', 'pur_number' => $model->pur_number,'flag'=>4], [
                            'title' => Yii::t('app', '添加采购单备注'),
                            'class' => 'btn btn-xs note',
                            'target'=>'_blank',
                            //'data-toggle' => 'modal',
                            //'data-target' => '#create-modal',
                            'id' => 'return',
                        ]);

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

$js = <<<JS

    $(document).on('click', '#submit-audit', function () {


        $.get($(this).attr('href'), {id:$(this).attr('value'),status:$(this).attr('status'),currency_code:$(this).attr('currency_code')},
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
    $(document).on('click', '#views', function () {


        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

 //批量导出
    $(document).on('click', '#bulk-execl', function () {
      var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids==''){
                    alert('请先选择!');
                    return false;
                }else{
                    var url = $(this).attr("href");
                    if($(this).hasClass("print"))
                    {
                        url = '/purchase-order-receipt-notification/export-cvs';
                    }
                    url     = url+'?ids='+ids;
                    $(this).attr('href',url);
                }
    });

JS;
$this->registerJs($js);
?>
