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
use mdm\admin\components\Helper;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use kartik\daterange\DateRangePicker;
use dosamigos\datepicker\DatePicker;
use yii\widgets\LinkPager;
use app\models\OverseasPurchaseOrderSearch;
use app\api\v1\models\Supplier;
use kartik\datetime\DateTimePicker;
use yii\base\Widget;
use app\models\PurchaseNote;
use app\models\PurchaseOrderPaySearch;
use app\models\OverseasPaymentSearch;
use app\models\OverseasCheckPriv;

$this->title = '海外仓-请款单';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    #grid_purchase_order p {
        margin: 6px;
    }
    .label-box {
        position: absolute;
        top: 40px;
        left: 0;
        border: 1px solid #8BC34A;
        padding: 2px;
        display: none;
        z-index: 500;
        background-color: #fff;
    }
    .label-box span:hover {
        background-color: red !important;
        cursor: pointer;
    }
    .kv-panel-before{ display:none}
    #grid_purchase_order-container thead th{background:#fff}
</style>

<div class="panel panel-default" style="position:relative;z-index:10">
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'action' => ['payment'],
            'method' => 'get',
        ]); ?>
        <div class="col-md-2">
            <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
                'options' => ['placeholder' => '请输入供应商 ...','value' =>!empty($model->supplier_code)?BaseServices::getSupplierName($model->supplier_code):''],
                'pluginOptions' => [
                    'placeholder' => 'search ...',
                    'allowClear' => true,
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                    ],
                    'ajax' => [
                        'url' => \yii\helpers\Url::to(['/supplier/search-supplier']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(res) { return res.text; }'),
                    'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                ],
            ])->label('供应商');
            ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model,'pay_status')->dropDownList(PurchaseOrderServices::getPayStatus(),['prompt'=>'请选择'])->label('请款单状态') ?>
        </div>
        <div class="col-md-1"><?= $form->field($model, 'requisition_number')->label('请款单号') ?></div>
        <div class="col-md-1">
            <?= $form->field($model, 'settlement_method')->dropDownList(SupplierServices::getSettlementMethod(), ['prompt'=>'请选择'])->label('结算方式') ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'pay_type')->dropDownList(SupplierServices::getDefaultPaymentMethod(), ['prompt'=>'请选择'])->label('支付方式') ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'applicant')->widget(Select2::classname(), [
                'options' => ['placeholder' => '申请人'],
                'data' =>BaseServices::getEveryOne(),
                'pluginOptions' => [
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(res) { return res.text; }'),
                    'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                ],
            ])->label('申请人');
            ?>
        </div>
        <div class="col-md-3">
    <label class="control-label">申请时间</label>
    <?php
    $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
            echo '<div class="input-group drp-container">';
            echo DateRangePicker::widget([
                    'name' => 'OverseasPaymentSearch[application_time]',
                    'value' => $model->application_time,
                    'useWithAddon'   => true,
                    'convertFormat'  => true,
                    'initRangeExpr'  => true,
                    'startAttribute' => 'OverseasPaymentSearch[start_time]',
                    'endAttribute'   => 'OverseasPaymentSearch[end_time]',
                    'startInputOptions' => ['value' => $model->start_time],
                    'endInputOptions' => ['value' => $model->end_time],
                    'pluginOptions' => [
                        'locale' => ['format' => 'Y-m-d H:i:s'],
                        'ranges' => [
                            Yii::t('app', "今天") => ["moment().startOf('day')", "moment()"],
                            Yii::t('app', "昨天") => ["moment().startOf('day').subtract(1,'days')", "moment().endOf('day').subtract(1,'days')"],
                            Yii::t('app', "最近7天") => ["moment().startOf('day').subtract(6, 'days')", "moment()"],
                            Yii::t('app', "最近30天") => ["moment().startOf('day').subtract(29, 'days')", "moment()"],
                            Yii::t('app', "本月") => ["moment().startOf('month')", "moment().endOf('month')"],
                            Yii::t('app', "上月") => ["moment().subtract(1, 'month').startOf('month')", "moment().subtract(1, 'month').endOf('month')"],
                        ]
                    ],
                ]).$addon;
            echo '</div>';
            ?>
        </div>
        <div class="col-md-2" style="padding-left:0px">
        	<label class="control-label">&nbsp;</label>
        	<div>
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    
    <div class="box-footer">
		<?= Html::button('审核',['class'=>'btn btn-success btn-audit','data-url'=>'payment-audit','data-toggle'=>'modal','data-target'=>"#create-modal"]) ?>
    	<?= Html::button('编辑显示内容', ['type'=>'button','class'=>'btn btn-success btn-edit-fields','data-toggle'=>"modal",'data-target'=>"#create-modal",'style'=>"float:right"]) ?>
    </div><!-- box-footer -->
</div><!-- /.box -->
<form id="order-form" action="" method="post">
<input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
<input type="hidden" name="remark" value="" id="form-remark" />
<?php
$columns =  [
    'requisition_number' => [
        'label'=>'请款单号',
        "format" => "raw",
        'value'=> function($model){
            $status = '<a href="index?requisition_number='.$model->requisition_number.'" target="payment_detail">'.$model->requisition_number.'</a>';
            return getDivContent($status,90);
        },
    ],
    'buyer_company' => [
        'label' => '采购主体',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(BaseServices::getBuyerCompany(OverseasPaymentSearch::getOrderInfo($model->pur_number,'is_drawback'),'name'),100,'font-size:12px');
        }
    ],
    'source' => [
        'label' => '采购来源',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->source == 1 ? '合同' : '网采',60);
        }
    ],
    'applicant' => [
        'label' => '申请人',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->applicant ? BaseServices::getEveryOne($model->applicant) : '',80);
        }
    ],
    'application_time' => [
        'label' => '申请时间',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->application_time,120);
        }
    ],
    'auditor' => [
        'label' => '审核人',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->auditor ? BaseServices::getEveryOne($model->auditor) : '',80);
        }
    ],
    'review_time' => [
        'label' => '审核时间',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->review_time,120);
        }
    ],
    'pay_status' => [
        'label' => '状态',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(PurchaseOrderServices::getPayStatus($model->pay_status),80);
        }
    ],
    'supplier_code' => [
        'label' => '供应商',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(BaseServices::getSupplierName($model->supplier_code),100);
        }
    ],
    'contact_person' => [
        'label' => '联系人',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->contact_person,60);
        }
    ],
    'contact_number' => [
        'label' => '联系电话',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->contact_number,80);
        }
    ],
    'pay_price' => [
        'label' => '申请金额',
        'format' => 'raw',
        'value' => function($model) {
            $check_priv = '';
            if ($model->pay_status == 10) {
                if ($model->pay_price < OverseasCheckPriv::getOverseasCheckPirce(3)) {
                    $check_priv = '<br>[经理/主管审核]';
                } else {
                    $check_priv = '<br>[经理审核]';
                }
            }
            if ($model->source == 1) {
                return getDivContent($model->pay_price.$check_priv,100);
            } else {
                return getDivContent(PurchaseOrderPay::getPrice($model,true).$check_priv,100);
            }
        }
    ],
    'real_pay_price' => [
        'label' => '已付金额',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->real_pay_price,60);
        }
    ],
    'pay_type' => [
        'label' => '支付方式',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(SupplierServices::getDefaultPaymentMethod($model->pay_type),60);
        }
    ],
    'freight_payer' => [
        'label' => '运费支付',
        'format' => 'raw',
        'value' => function($model) {
            $freight_payer = OverseasPaymentSearch::getOrderPayTypeInfo($model->pur_number,'freight_payer');
            return getDivContent($freight_payer == 1 ? '甲方支付' : '乙方支付',60);
        }
    ],
    'settlement_method' => [
        'label' => '结算方式',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(SupplierServices::getSettlementMethod($model->settlement_method),80);
        }
    ],
    'create_notice' => [
        'label' => '请款备注',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->create_notice, 100);
        }
    ],
    'review_notice' => [
        'label' => '审核备注',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->review_notice, 100);
        }
    ],
    
];

$fields_params = [];
foreach ($columns as $k=>$v) {
    $fields_params[$k] = $v['label'];
}
$fields_params['table'] = 'overseas_payment';
$fields_params = json_encode($fields_params);

if ($fields) {
    $columns_help = [];
    foreach ($fields as $val) {
        if (isset($columns[$val])) {
            $columns_help[] = $columns[$val];
        }
    }
    $columns = $columns_help;
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => [
        'id' => 'grid_purchase_order',
    ],
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'], input[name='".$dataProvider->getPagination()->pageParam."']",
    'pager' => [
        'options' => ['class' => 'pagination', 'style' => 'display:block;'],
        'class' => \liyunfang\pager\LinkPager::className(),
        'pageSizeList' => [8,10, 20, 50, 100,500,1000],
        'firstPageLabel' => '首页',
        'prevPageLabel' => '上一页',
        'nextPageLabel' => '下一页',
        'lastPageLabel' => '末页',
    ],
    'columns' => 
        array_merge([
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name' => 'id',
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->id];
                }
            ],
        ], $columns),
    'containerOptions' => ["style" => "overflow: auto"],
    'toolbar' =>  [],
    'pjax' => false,
    'bordered' => true,
    'striped' => false,
    'condensed' => false,
    'responsive' => true,
    'hover' => true,
    'floatHeader' => false,
    'panel' => [
        'type' => 'success'
    ],
]);
?>
</form>

<iframe src="" name="payment_detail" id="payment_detail" width="100%" height="400px"></iframe>

<?php

$js = <<<JS

$(function() {
   
    $(".btn-edit-fields").click(function(){
        $("#create-modal .modal-title").text('编辑显示内容');
        $.post('/member/update-table-fields', {$fields_params}, function (data) {
            $('#create-modal .modal-body').html(data);
        });
    })
    
    $(".btn-audit").click(function(){
        var data = get_ids();
        if (data.length == 0)    {
            layer.msg('请至少选择一条数据');
            return false;
        }
        $("#create-modal .modal-title").text($(this).text());
        $.post($(this).attr('data-url'), {data:data}, function (data) {
            $('#create-modal .modal-body').html(data);
        });
    })
});


function get_ids() {
    var data = new Array();
    $('input[name="id[]"]').each(function(){
        if($(this).is(':checked')){
            data.push($(this).val());
        }
    })
    return data;
}

$(document).ready(function () {
    var btop = $(".panel-default").offset().top;
    $(window).scroll(function(){
		var wtop = $(window).scrollTop();
        if (wtop > btop - 50) {
            $(".panel-default").css('transform','translateY(' + (wtop-60) + 'px)');
        } else {
            $(".panel-default").css('transform','translateY(0px)');
        }
        if (wtop > btop + 10) {
            $(".panel-success thead").css('transform','translateY(' + (wtop-122) + 'px)');
        } else {
            $(".panel-success thead").css('transform','translateY(0px)');
        }
	});
});


JS;
$this->registerJs($js);

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