<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\services\SupplierServices;
use yii\bootstrap\Modal;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseOrderPaySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '应收管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-pay-index">


    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>


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


                        $data =Yii::t('app','供应商').'：'.BaseServices::getSupplierName($model->supplier_code)."<br/>";
                        $data .=Yii::t('app','采购单号').'：'.$model->pur_number."<br/>";
                        $data .= Yii::t('app','结算对象类型').'：'.SupplierServices::getSupplierType($model->billing_object_type);
                        return $data;
                    },

            ],
            [
                'label'=>'交易信息',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data = Yii::t('app','交易号').'：'.$model->transaction_number."<br/>";
                        $data .= Yii::t('app','支付平台').'：'.SupplierServices::getDefaultPaymentMethod($model->beneficiary_payment_method)."<br/>";
                        $data .= Yii::t('app','是否指定帐单').'：'.PurchaseOrderServices::getAuditReturn($model->is_bill)."<br/>";
                        return $data;
                    },

            ],
            [
                'label'=>'金额/币种',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data = Yii::t('app','金额').'：'.$model->price."<br/>";
                        $data .= Yii::t('app','已核销金额').'：'.$model->write_off_price."<br/>";
                        $data .= Yii::t('app','原金额').'：'.$model->original_price."<br/>";
                        $data .= Yii::t('app','原币种').'：'.$model->original_currency."<br/>";
                        return $data;
                    },

            ],
//            [
//                'label'=>'标志信息',
//                'attribute' => 'ids',
//                "format" => "raw",
//                'value'=>
//                    function($model){
//                        $data = Yii::t('app','核销完成标志').'：'.PurchaseOrderServices::getAuditReturn($model->write_off_sign)."<br/>";
//                        $data .= Yii::t('app','参与月结账').'：'.PurchaseOrderServices::getAuditReturn($model->monthly_checkout)."<br/>";
//                        $data .= Yii::t('app','内部抵销标志').'：'.PurchaseOrderServices::getAuditReturn($model->internal_offset_sign)."<br/>";
//                        return $data;
//                    },
//
//            ],
            [
                'label'=>'备注',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        return $model->remarks;
                    },

            ],
            [
                'label'=>'创建人',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data = BaseServices::getEveryOne($model->create_id).'<br/>';
                        return $data;
                    },

            ],

            [
                'label'=>'操作时间',
                'attribute' => 'ids',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data  = Yii::t('app','创建:').$model->create_time.'<br/>';
                        $data .= Yii::t('app','交易:').$model->pay_time.'<br/>';
                        return $data;
                    },

            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => '{views}',
                'buttons'=>[
                    'views' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 查看', ['views','id'=>$key], [
                            'title' => Yii::t('app', '查看'),
                            'class' => 'btn btn-xs red',
                            'id' =>'views',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
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
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();
$js = <<<JS
    $(function(){
            $("a#submit-audit").click(function(){
                var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
                if(ids==''){
                    alert('请先选择!');
                    return false;
                }else{
                return false;
//                    var url = $(this).attr("href");
//                    if($(this).hasClass("print"))
//                    {
//                        url = '/purchase-order/print-data';
//                    }
//                    url     = url+'?ids='+ids;
//                    $(this).attr('href',url);
                }
            });
        });
    $(document).on('click', '#views', function () {


        $.get($(this).attr("href"), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });



JS;
$this->registerJs($js);
?>
