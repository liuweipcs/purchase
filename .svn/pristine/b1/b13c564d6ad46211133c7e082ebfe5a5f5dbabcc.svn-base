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

$this->title = '海外仓-采购单';
$this->params['breadcrumbs'][] = $this->title;

$platform = \app\models\PlatformSummarySearch::overseasPlatformList(null,true);

$bool = true;
$userRoleName = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
$not_allow_role = ['海外销售组','海外销售经理组', '销售组', 'FBA销售经理组','FBA销售组']; //不可以查看供应商信息的角色
$not_allow_user = ['龚海燕', '伍如丽'];
if (!empty($userRoleName)) {
    foreach ($not_allow_role as $key => $value) {
        if (array_key_exists($value,$userRoleName)) {
            $bool=false;
            break;
        }
    }
} elseif (in_array(Yii::$app->user->identity->username, $not_allow_user) ) {
    $bool=false;
}
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
    <?php if (!isset($_GET['requisition_number'])) : ?>
    .kv-panel-before{ display:none}
    <?php endif; ?>
    #grid_purchase_order-container{}
    #grid_purchase_order-container thead th{background:#fff; }
    
</style>
<?php if (!isset($_GET['requisition_number'])) : ?>
<div class="panel panel-default" style="position:relative;z-index:10">
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
        ]); ?>
        <div class="col-md-2" style="padding-left:0px">
            <?= $form->field($model, 'order_status')->dropDownList(PurchaseOrderServices::getOverseasOrderStatus(),['prompt'=>'全部','multiple'=>true,'style'=>"padding:3px;height:108px"])->label('订单状态') ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'audit_level')->dropDownList([1=>'主管/经理审核',2=>'经理审核'],['prompt'=>'全部'])->label('审核状态') ?>
        </div>
        <div class="col-md-1"><?= $form->field($model, 'demand_number')->label('需求单号') ?></div>
        <div class="col-md-1"><?= $form->field($model, 'pur_number')->label('PO号') ?></div>
        <div class="col-md-1"><?= $form->field($model, 'compact_number')->label('合同号') ?></div>
        <div class="col-md-1"><?= $form->field($model, 'sku', ['inputOptions' => [
            'placeholder' => '支持模糊查询',
            'class' => 'form-control',
        ],])->label('SKU') ?></div>
        <div class="col-md-1"><?= $form->field($model, 'product_name')->label('产品名称') ?></div>
        <div class="col-md-1">
            <?= $form->field($model, 'buyer')->widget(Select2::classname(), [
                'options' => ['placeholder' => '采购员'],
                'data' =>BaseServices::getEveryOne('','name'),
                'pluginOptions' => [
                    'allowClear' => true,
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(res) { return res.text; }'),
                    'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                ],
            ])->label('采购员 <a class="get-all-buyer" title="查到所有等待采购询价状态的需求关联的采购员" href="javascript:;">All</a>');
            ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'platform_number')->dropDownList($platform, ['prompt' => '全部'])->label('平台') ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'bh_type')->dropDownList(PurchaseOrderServices::getBhTypes(),['prompt' => '全部'])->label('补货类型') ?>
        </div>
        <div class="col-md-1">
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
            <?= $form->field($model, 'arrival_status')->dropDownList(PurchaseOrderServices::getArrivalStatus(),['prompt'=>'请选择'])->label('到货状态') ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'is_drawback')->dropDownList([2=>'退税',1=>'不退税'],['prompt'=>'不限'])->label('是否退税') ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model,'pay_status')->dropDownList(PurchaseOrderServices::getPayStatus(), ['prompt'=>'请选择'])->label('付款状态'); ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'transport_style')->dropDownList(PurchaseOrderServices::getTransport(),['prompt'=>'全部'])->label('物流类型') ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'order_source')->dropDownList([1=>'合同订单',2=>'网采订单'],['prompt'=>'不限'])->label('采购来源') ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'is_sku_destroy')->dropDownList([1=>'是',2=>'否'],['prompt'=>'不限'])->label('是否核销') ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'product_is_new')->dropDownList([1=>'是',2=>'否'],['prompt'=>'不限'])->label('是否新品') ?>
        </div>
        <div class="col-md-1">
            <label class="control-label" for="overseaspurchaseordersearch-applicant">创建时间：</label>
            <?php 
            $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'time',
            'useWithAddon'=>true,
            'convertFormat'=>true,
            'startAttribute' => 'OverseasPurchaseOrderSearch[start_time]',
            'endAttribute' => 'OverseasPurchaseOrderSearch[end_time]',
            'startInputOptions' => ['value' => $model->start_time],
            'endInputOptions' => ['value' => $model->end_time],
            'pluginOptions'=>[
                'locale'=>['format' => 'Y-m-d H:i:s'],
            ]
        ]).$addon ;
        echo '</div>';
        ?>
        </div>

        <div class="col-md-1">
            <?= $form->field($model, 'supplier_special_flag')->dropDownList(\app\services\SupplierServices::supplierSpecialFlag(),['prompt'=>'请选择'])->label('跨境宝供应商') ?>
        </div>

        <div class="col-md-10" style="text-align: right">
            <label class="control-label">&nbsp;</label>
            <div>
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::submitButton('导出', ['class' => 'btn btn-primary', 'name'=>'export']) ?>
            <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    
    <div class="box-footer">
        <?php if (Helper::checkRoute('print-order')) : ?>
        <?= Html::button('打印采购单',['class' => 'btn btn-sm btn-success btn-print-data','data-url'=>'print-data','target'=>'_blank']) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('confirm-info')) : ?>
        <?= Html::button('确认提交信息',['class'=>'btn btn-sm btn-success btn-info-submit','data-url'=>'confirm-info']) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('info-audit')) : ?>
        <?= Html::button('信息变更审核',['class'=>'btn btn-sm btn-info btn-audit','data-url'=>'info-audit','data-toggle'=>'modal','data-target'=>"#create-modal"]) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('purchase-audit')) : ?>
        <?= Html::button('采购审核',['class'=>'btn btn-sm btn-info btn-audit','data-url'=>'purchase-audit','data-toggle'=>'modal','data-target'=>"#create-modal"]) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('sale-audit')) : ?>
        <?php Html::button('销售审核',['class'=>'btn btn-sm btn-info btn-audit','data-url'=>'sale-audit','data-toggle'=>'modal','data-target'=>"#create-modal"]) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('purchase-disagree')) : ?>
        <?= Html::button('驳回',['class'=>'btn btn-sm btn-warning btn-note-submit','data-action'=>'purchase-disagree']) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('confirm-order')) : ?>
        <?= Html::button('生成进货单',['class'=>'btn btn-sm btn-success btn-submit','data-action'=>'confirm-order']) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('change-buyer')) : ?>
        <?= Html::button('变更采购员',['class'=>'btn btn-sm btn-warning btn-audit','data-url'=>'change-buyer','data-toggle'=>'modal','data-target'=>"#create-modal"]) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('add-note')) : ?>
        <?= Html::button('添加备注',['class'=>'btn btn-sm btn-warning btn-note-submit','data-action'=>'add-note']) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('pay-apply')) : ?>
        <?= Html::button('申请请款',['class'=>'btn btn-sm btn-success btn-submit','data-action'=>'pay-apply']) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('add-express-no')) : ?>
        <?= Html::button('录入物流单号',['class'=>'btn btn-sm btn-success btn-audit','data-url'=>'add-express-no','data-toggle'=>'modal','data-target'=>"#create-modal"]) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('cancel-order')) : ?>
        <?= Html::button('作废订单',['class'=>'btn btn-sm btn-danger btn-audit','data-url'=>'cancel-order','data-toggle'=>'modal','data-target'=>"#create-modal"]) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('cancel-audit')) : ?>
        <?= Html::button('审核作废订单',['class'=>'btn btn-sm btn-success btn-audit','data-url'=>'cancel-audit','data-toggle'=>'modal','data-target'=>"#create-modal"]) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('purchase-abnormal-answer')) : ?>
        <?= Html::button('采购异常回复',['class'=>'btn btn-sm btn-warning btn-note-submit','data-action'=>'purchase-abnormal-answer']) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('sale-feedback')) : ?>
        <?= Html::button('销售反馈',['class'=>'btn btn-sm btn-warning btn-note-submit','data-action'=>'sale-feedback']) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('invoice')) : ?>
        <?= Html::button('开票',['class'=>'btn btn-sm btn-warning btn-audit','data-url'=>'invoice','data-toggle'=>'modal','data-target'=>"#create-modal"]) ?>
        <?php endif; ?>
        <?php if (Helper::checkRoute('split-purchase')) : ?>
        <?= Html::button('拆分采购单',['class'=>'btn btn-sm btn-success btn-audit','data-url'=>'split-purchase','data-toggle'=>'modal','data-target'=>"#create-modal"]) ?>
        <?php endif; ?>
        <a href="/overseas-purchase-order/compact-list" class="btn btn-sm btn-info" target="_blank">合同列表</a>
        
        <?= Html::button('编辑显示内容', ['type'=>'button','class'=>'btn btn-sm btn-success btn-edit-fields','data-toggle'=>"modal",'data-target'=>"#create-modal",'style'=>"float:right"]) ?>
        
    </div><!-- box-footer -->
</div><!-- /.box -->

<form id="order-form" action="" method="post">
<input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
<input type="hidden" name="remark" value="" id="form-remark" />
<?php endif; ?>

<?php
$columns =  [
    'demand_status' => [
        'label'=>'订单状态',
        'contentOptions'=>['style'=>'background:pink'],
        "format" => "raw",
        'value'=> function($model){
            $status = PurchaseOrderServices::getOverseasOrderStatus($model->demand_status);
            if ($model->audit_level > 0) {
                $status .= $model->audit_level == 1 ? '[主管/经理审核]' : '[经理审核]';
            }
            return getDivContent(Html::a($status, 'javascript:;', ['class'=>'order-status-button','data-demand-number'=>$model->demand_number,'data-toggle'=>'modal','data-target'=>"#create-modal"]),90);
            $status = '<a href="javascript:;" onclick="showlog(\''.$model->demand_number.'\')">'.$status.'</a>';
            return getDivContent($status,90);
        },
    ],
    'demand_number' => [
        'label' => '需求单号',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->demand_number,70);
        }
    ],
    'sku' => [
        'label' => 'sku',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->sku, 80);
        }
    ],
    'product_name' => [
        'label'=>'产品名称',
        "format" => "raw",
        'attribute' => 'product_name',
        'value'=> function($model){
            return '<a style="display:block;width:150px;font-size:12px" class="product-title" data-toggle="modal" data-target="#create-modal" data-imgs="'.$model->product_img.'" data-skus="'.$model->sku.'">'.$model->product_name.'</a>';
        },
    ],
    'pur_number' => [
        'label'=>'PO号',
        "format" => "raw",
        'attribute' => 'pur_number',
        'value'=> function($model){
            $checkExist = \app\models\SupplierCheck::find()->where(['like','pur_number',$model->pur_number])->andWhere(['check_type'=>2])->andWhere('status<>4')->exists();
            $check_html='';
            if($checkExist){
                $check_html='<sup style="color: red">验</sup>';
            }
            return getDivContent(!empty($model->pur_number) ? $model->pur_number.$check_html : '',80);
        },
    ],
    'compact_number' => [
        'label'=>'合同号',
        "format" => "raw",
        'attribute' => 'compact_number',
        'value'=> function($model){
            return getDivContent(!empty($model->compact_number) ? $model->compact_number : '',90);
        },
    ],
    'buyer' => [
        'label' => '采购员',
        'contentOptions'=>['style'=>'background:pink'],
        'format' => 'raw',
        'attribute' => 'buyer',
        'value' => function($model) {
            return getDivContent($model->buyer ? $model->buyer : '', 80);
            if ($model->demand_status > 6) {
                return getDivContent($model->buyer ? $model->buyer : '', 80);
            }
            return Select2::widget(['name'=>"buyer[{$model->demand_number}]",
                'options' => ['placeholder'=>'采购员'],
                'value' => $model->buyer,
                'data' => BaseServices::getEveryOne('','name'),
                'pluginOptions' => [
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(res) { return res.text; }'),
                    'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                ],
            ]);
        }
    ],
    'buyer_company' => [
        'label' => '采购主体',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(BaseServices::getBuyerCompany($model->is_drawback,'name'),100,'font-size:12px');
        }
    ],
    'supplier_code' => [
        'label' => '供应商',
        'contentOptions'=>['style'=>'background:pink'],
        'format' => 'raw',
        'visible'=>$bool,
        'attribute' => 'supplier_code',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                $supplierName = empty($model->supplier2) ? '' : $model->supplier2->supplier_name;
            } else {
                $supplierName = !empty($model->demand->purchaseOrder->supplier_name)?$model->demand->purchaseOrder->supplier_name:null;
                if (empty($supplierName)) $supplierName = empty($model->supplier2) ? '' : $model->supplier2->supplier_name;
            }
            $sub_html = \app\models\SupplierSearch::flagCrossBorder(true,$model->supplier_code);
            return getDivContent($supplierName,140,'font-size:12px').$sub_html;
        }
    ],
    'purchase_quantity' => [
        'label' => '数量',
        'contentOptions'=>['style'=>'background:pink'],
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                $string = Html::input('number', "purchase_quantity[{$model->demand_number}]", $model->purchase_quantity, ['style'=>'width:60px','class'=>'field-note']);
                $string .= '<div style="margin-top:2px">';
                $string .= Html::input('text', "purchase_quantity_note[{$model->demand_number}]", '', ['style'=>'width:100px;display:none;font-size:12px','class'=>'field-input-note','placeholder'=>'请填写变更备注']);
                $string .= '</div>';
                return $string;
            } else {
                return getDivContent($model->purchase_quantity,50);
            }
        }
    ],
    'product_is_new' => [
        'label' => '是否新品',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->product_is_new == 1 ? '是' : '否', 58);
        }
    ],
    'demand_price' => [
        'label' => '含税单价',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->is_drawback == 1) {
                return getDivContent('',60);
            }
            if ($model->demand_status == 1) {
                return getDivContent(OverseasPurchaseOrderSearch::getSkuQuoteValue($model->sku, 'price'),60);
            }
            return getDivContent(round($model->price,4),60);
        }
    ],
    'demand_base_price' => [
        'label' => '未税单价',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                return getDivContent(OverseasPurchaseOrderSearch::getSkuQuoteValue($model->sku, 'base_price'),60);
            }
            return getDivContent(round($model->base_price,4),60);
        }
    ],
    'pur_ticketed_point' => [
        'label' => '开票点',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                return getDivContent(OverseasPurchaseOrderSearch::getSkuQuoteValue($model->sku, 'pur_ticketed_point') . '%',50);
            } else {
                return getDivContent(!empty($model->pur_ticketed_point) ? $model->pur_ticketed_point.'%' : '',50);
            }
        }
    ],
    'tax_rate' => [
        'label' => '出口退税税率',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(!empty($model->tax_rate) ? $model->tax_rate.'%' : '',85);
        }
    ],
    'export_cname' => [
        'label' => '开票品名',
        'format' => 'raw',
        'filterWidgetOptions' => '',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                $string = Html::input('text', "demand_export_cname[{$model->demand_number}]", $model->demand_export_cname ? $model->demand_export_cname : $model->export_cname, ['class'=>'','style'=>'width:80px']);
                $string .= Html::input('hidden', "default_demand_export_cname[{$model->demand_number}]", $model->demand_export_cname ? $model->demand_export_cname : $model->export_cname);
                return $string;
            } else {
                return getDivContent(!empty($model->demand_export_cname) ? $model->demand_export_cname : '',80);
            }
        }
    ],
    'declare_unit' => [
        'label' => '开票单位',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                $string = Html::input('text', "demand_declare_unit[{$model->demand_number}]", $model->demand_declare_unit ? $model->demand_declare_unit : $model->declare_unit, ['class'=>'','style'=>'width:60px']);
                $string .= Html::input('hidden', "default_demand_declare_unit[{$model->demand_number}]", $model->demand_declare_unit ? $model->demand_declare_unit : $model->declare_unit);
                return $string;
            } else {
                return getDivContent(!empty($model->demand_declare_unit) ? $model->demand_declare_unit : '',60);
            }
        }
    ],
    'history_price' => [
        'label' => '历史采购单价',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(PurchaseOrderServices::getlastPirce2($model->sku, $model->create_time),85);
        }
    ],
    'item_totalprice' => [
        'label' => '采购金额',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                $price_type = $model->is_drawback == 2 ? 'price' : 'base_price';
                $price = OverseasPurchaseOrderSearch::getSkuQuoteValue($model->sku, $price_type);
            } else {
                $price = $model->price;
            }
            $pre = ($model->source == 1) ? 3 : 2;
            return getDivContent(round($price * $model->purchase_quantity,$pre),60);
        }
    ],
    'transit_number' => [
        'label' => '中转数',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->transit_number,50);
        }
    ],
    'transport_style' => [
        'label' => '海外物流类型',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(!empty($model->transport_style) ? PurchaseOrderServices::getTransport($model->transport_style) : '',85);
        }
    ],
    'platform_number' => [
        'label' => '平台',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->platform_number,50);
        }
    ],
    'purchase_warehouse' => [
        'label' => '采购仓库',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(BaseServices::getWarehouseCode($model->purchase_warehouse),120);
        }
    ],
    'is_expedited' => [
        'label' => '加急采购单',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->demand_is_expedited == 1 ? '是' : '否',70);
        }
    ],
    'express_no' => [
        'label' => '物流单号',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->demand_status > 6 ? OverseasPurchaseOrderSearch::getShipExpressNo($model->pur_number, $model->demand_number) : '',120);
        }
    ],
    'bh_type' => [
        'label' => '补货类型',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->bh_type ? PurchaseOrderServices::getBhTypes($model->bh_type) : '',60);
        }
    ],
    'currency_code' => [
        'label' => '币种',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(!empty($model->currency_code) ? $model->currency_code : '',50);
        }
    ],
    'create_type' => [
        'label' => '创建类型',
        'format' => 'raw',
        'value' => function($model) {
            if (empty($model->create_type)) return getDivContent('');
            return getDivContent($model->create_type == 1 ? '系统' : ( $model->create_type == 2 ? '手工' : '' ),60);
        }
    ],
    'account_type' => [
        'label' => '结算方式',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                $account_type = empty($model->supplier2) ? '' : $model->supplier2->supplier_settlement;
            } else {
                $account_type = $model->account_type;
            }
            return getDivContent(!empty($account_type) ? SupplierServices::getSettlementMethod($account_type) : '',60);
        }
    ],
    'pay_type' => [
        'label' => '支付方式',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                $pay_type = empty($model->supplier2) ? '' : $model->supplier2->payment_method;
            } else {
                $pay_type = $model->pay_type;
            }
            return getDivContent($pay_type ? SupplierServices::getDefaultPaymentMethod($pay_type) : '',60);
        }
    ],
    'pay_percent' => [
        'label' => '结算比例',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                $string = '<div class="input-group">';
                $string .= '<input style="width:170px" readonly type="text" name="settlement_ratio['.$model->demand_number.']" class="form-control settlement_ratio same-pur-number same-supplier-name" data-pur-number="'.$model->pur_number.'" data-field="settlement_ratio" data-supplier-name="'.(empty($model->supplier2) ? '' : $model->supplier2->supplier_name).'" value="'.$model->settlement_ratio.'">';
                $string .= '<div class="input-group-btn">
                    <button class="btn btn-default settlement_ratio_clear" type="button"><span class="glyphicon glyphicon-remove"></span></button>
                    <button class="btn btn-default settlement_ratio_define" type="button">定义</button>
                </div>
                <div class="label-box">
                <p>
                    <span class="label label-info">5%</span>
                    <span class="label label-info">10%</span>
                    <span class="label label-info">20%</span>
                    <span class="label label-info">30%</span>
                    <span class="label label-info">40%</span>
                    <span class="label label-info">50%</span>
                </p>
                <p>
                    <span class="label label-info">60%</span>
                    <span class="label label-info">70%</span>
                    <span class="label label-info">80%</span>
                    <span class="label label-info">90%</span>
                    <span class="label label-info">95%</span>
                    <span class="label label-info">100%</span>
                    <span class="label label-danger">关闭</span>
                </p>
                </div></div>';
                return $string;
            } else {
                return getDivContent($model->settlement_ratio,80);
            }
        }
    ],
    'shipping_method' => [
        'label' => '供应商运输',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                return Html::dropDownList("shipping_method[{$model->demand_number}]", $model->shipping_method, array_filter(PurchaseOrderServices::getShippingMethod()), ['style'=>'width:80px','data-pur-number'=>$model->pur_number,'class'=>'same-pur-number','data-field'=>'shipping_method']);
            } else {
                return getDivContent($model->shipping_method ? PurchaseOrderServices::getShippingMethod($model->shipping_method) : '',80);
            }
        }
    ],
    'is_transit' => [
        'label' => '是否中转',
        'format' => 'raw',
        'value' => function($model) {
            //if ($model->demand_status == 1) {
            //   return Html::dropDownList("is_transit[{$model->demand_number}]", $model->is_transit, [1=>'直发',2=>'需要中转'], ['style'=>'width:80px','data-pur-number'=>$model->pur_number,'class'=>'same-pur-number','data-field'=>'is_transit']);
            //} else {
                return getDivContent($model->is_transit == 1 ? '直发' : '是',60);
            //}
        }
    ],
    'transit_warehouse' => [
        'label' => '中转仓库',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                return Html::dropDownList("transit_warehouse[{$model->demand_number}]", $model->transit_warehouse, PurchaseOrderServices::getTransitWarehouse(), ['data-pur-number'=>$model->pur_number,'class'=>'same-pur-number','data-field'=>'transit_warehouse']);
            } else {
                if (is_array(PurchaseOrderServices::getTransitWarehouse($model->transit_warehouse))) {
                } else {
                    return getDivContent(PurchaseOrderServices::getTransitWarehouse($model->transit_warehouse),80);
                }
            }
        }
    ],
    'is_drawback' => [
        'label' => '是否退税',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                $string = Html::dropDownList("is_drawback[{$model->demand_number}]", $model->is_drawback, [1=>'否',2=>'是'], ['style'=>'width:80px','data-pur-number'=>$model->pur_number,'class'=>'same-pur-number field-note-change','data-field'=>'is_drawback']);
                $string .= '<div style="margin-top:2px">';
                $string .= Html::input('text', "is_drawback_note[{$model->demand_number}]", '', ['style'=>'width:100px;display:none;font-size:12px','class'=>'field-input-note','placeholder'=>'请填写变更备注']);
                $string .= '</div>';
                return $string;
            } else {
                return getDivContent($model->is_drawback == 2 ? '是' : '否',60);
            }
        }
    ],
    'demand_agree_time' => [
        'label' => '创建日期',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->agree_time,70);
        }
    ],
    'demand_audit_time' => [
        'label' => '审核日期',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->audit_time != '0000-00-00 00:00:00' ? $model->audit_time : '',70);
        }
    ],
    'date_eta' =>  [
        'label' => '预计到货日期',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                return DateTimePicker::widget([
                    'name' => "date_eta[{$model->demand_number}]",
                    'value' => $model->date_eta,
                    'options' => ['placeholder'=>'',
                        'style'=>'width:150px',
                        'data-pur-number'=>$model->pur_number,
                        'class'=> 'same-pur-number same-supplier-name',
                        'data-field'=>'date_eta',
                        'data-supplier-name' => (empty($model->supplier2) ? '' : $model->supplier2->supplier_name)
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd HH:ii:ss',
                    ]
                ]);
            } else {
                return getDivContent($model->date_eta,150);
            }
        }
    ],
    'source' => [
        'label' => '采购来源',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                return Html::dropDownList("source[{$model->demand_number}]", $model->source, [1=>'合同',2=>'网采'], ['style'=>'width:60px','data-pur-number'=>$model->pur_number,'class'=>'same-pur-number','data-field'=>'source']);
            } else {
                return getDivContent(!empty($model->source) ? ( $model->source == 1 ? '合同' : '网采' ) : '',60);
            }
        }
    ],
    'demand_freight' => [
        'label' => '运费',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(OverseasPurchaseOrderSearch::getDemandPayInfo($model->demand_number, 'freight',true),40);
        }
    ],
    'freight_type' => [
        'label' => '运费计算方式',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                return Html::dropDownList("freight_formula_mode[{$model->demand_number}]", $model->freight_formula_mode, ['weight'=>'重量','volume'=>'体积'], ['style'=>'width:85px','data-pur-number'=>$model->pur_number,'class'=>'same-pur-number','data-field'=>'freight_formula_mode']);
            } else {
                return getDivContent(!empty($model->freight_formula_mode) ? ( $model->freight_formula_mode == 'weight' ? '重量' : '体积' ) : '',85);
            }
        }
    ],
    'freight_payer' => [
        'label' => '运费支付',
        'format' => 'raw',
        'filterWidgetOptions' => '',
        'width' => '60px',
        'value' => function($model) {
            if ($model->demand_status == 1) {
                return Html::dropDownList("freight_payer[{$model->demand_number}]", $model->freight_payer, [1=>'甲方支付',2=>'乙方支付'], ['data-pur-number'=>$model->pur_number,'class'=>'same-pur-number','data-field'=>'freight_payer']);
            } else {
                return getDivContent(!empty($model->freight_payer) ? ( $model->freight_payer == 1 ? '甲方支付' : '乙方支付' ) : '',60);
            }
        }
    ],
    'demand_discount' => [
        'label' => '优惠额',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(OverseasPurchaseOrderSearch::getDemandPayInfo($model->demand_number, 'discount',true),50);
        }
    ],
    'purchase_acccount' => [
        'label' => '账号',
        'format' => 'raw',
        'attribute' => 'purchase_acccount',
        'value' => function($model) {
            return getDivContent(!empty($model->purchase_acccount) ? $model->purchase_acccount : '',100);
            /*
            if ($model->demand_status == 1) {
                return Html::dropDownList("purchase_acccount[{$model->demand_number}]", $model->purchase_acccount, BaseServices::getAlibaba(), ['data-pur-number'=>$model->pur_number,'class'=>'same-pur-number','data-field'=>'purchase_acccount']);
            } else {
                return getDivContent(!empty($model->purchase_acccount) ? $model->purchase_acccount : '',100);
            }
            */
        }
    ],
    'platform_order_number' => [
        'label' => '拍单号',
        'format' => 'raw',
        'attribute' => 'platform_order_number',
        'value' => function($model) {
            return getDivContent(!empty($model->platform_order_number) ? $model->platform_order_number : '',125);
        }
    ],
    'purchase_arrival_date' => [
        'label' => '采购到货日期',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->demand_status < 7) return getDivContent('',90);
            return DateTimePicker::widget([
                'name' => "purchase_arrival_date[{$model->demand_number}]",
                'value' => $model->purchase_arrival_date == '0000-00-00 00:00:00' ? '' : $model->purchase_arrival_date,
                'options' => ['placeholder'=>'','style'=>'width:150px','class'=>'purchase_arrival_date','data-demand-number'=>$model->demand_number],
                'pluginOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'format' => 'yyyy-mm-dd HH:ii:ss',
                ]
            ]);
        }
    ],
    'warehouse_arrival_date' => [
        'label' => '仓库到货日期',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent('',85);
        }
    ],
    'rqy' => [
        'label' => '到货数量',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->rqy,60);
        }
    ],
    'instock_date' => [
        'label' => '入库日期',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->instock_date != '0000-00-00 00:00:00' ? substr($model->instock_date,0,10) : '',70);
        }
    ],
    'cty' => [
        'label' => '入库数量',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->cty,60);
        }
    ],
    'cty_amount' => [
        'label' => '入库金额',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->cty*$model->price ,60);
        }
    ],
    'pay_amount' => [
        'label' => '已付金额',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(OverseasPurchaseOrderSearch::getDemandPayInfo($model->demand_number, 'pay_amount'),60);
        }
    ],
    'is_overdue' => [
        'label' => '是否逾期',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent('' ,60);
        }
    ],
    'is_hexiao' => [
        'label' => '是否核销',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent('' ,60);
        }
    ],
    'pay_status' => [
        'label' => '付款状态',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(PurchaseOrderServices::getPayStatus($model->pay_status),80);
        }
    ],
    'pay_time' => [
        'label' => '付款时间',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(implode('<br>',OverseasPurchaseOrderSearch::getDemandPayInfo($model->demand_number, 'payer_time')),120);
        }
    ],
    'requisition_number' => [
        'label' => '请款单号',
        'format' => 'raw',
        'value' => function($model) {
            if ($model->source == 1) {
                return getDivContent(implode('<br>',OverseasPurchaseOrderSearch::getCompactPayInfo($model->compact_number, 'requisition_number')),80);
            } else {
                return getDivContent(implode('<br>',OverseasPurchaseOrderSearch::getDemandPayInfo($model->demand_number, 'requisition_number')),80);
            }

        }
    ],
    'invoice_has_qty' => [
        'label' => '已开票数量',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(OverseasPurchaseOrderSearch::getInvoiceQty($model->demand_number, 'qty'),80);
        }
    ],
    'invoice_no_qty' => [
        'label' => '未开票数量',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent($model->purchase_quantity - OverseasPurchaseOrderSearch::getInvoiceQty($model->demand_number, 'qty'),80);
        }
    ],
    'invoice_code' => [
        'label' => '发票编号',
        'format' => 'raw',
        'value' => function($model) {
            return getDivContent(implode('<br>',OverseasPurchaseOrderSearch::getInvoiceQty($model->demand_number, 'invoice_code')),120);
        }
    ],
    'purchase_abnormal_answer' => [
        'label' => '采购异常回复',
        'format' => 'raw',
        'value' => function($model) {
            $string = $title = '';
            $data = PurchaseReply::find()->where(['demand_number'=>$model->demand_number,'replay_type'=>1])->orderBy('id asc')->asArray()->all();
            if ($data) {
                $string = mb_substr($data[0]['note'],0,30).'...';
                foreach ($data as $v) {
                    $title .= $v['create_time'].'(by '.$v['create_user'].")\r\n".$v['note']."\r\n";
                }
            }
            return "<div style='width:100px' class='note-show' title='{$title}' show-title='".str_replace("\r\n", "<br>", $title)."'>{$string}</div>";
        }
    ],
    'sale_feedback' => [
        'label' => '销售反馈',
        'format' => 'raw',
        'value' => function($model) {
            $string = $title = '';
            $data = PurchaseReply::find()->where(['demand_number'=>$model->demand_number,'replay_type'=>2])->orderBy('id asc')->asArray()->all();
            if ($data) {
                $string = mb_substr($data[0]['note'],0,30).'...';
                foreach ($data as $v) {
                    $title .= $v['create_time'].'(by '.$v['create_user'].")\r\n".$v['note']."\r\n";
                }
            }
            return "<div style='width:100px' class='note-show' title='{$title}' show-title='".str_replace("\r\n", "<br>", $title)."'>{$string}</div>";
        }
    ],
    'product_link' => [
        'label' => '产品链接',
        'format' => 'raw',
        'value' => function($model) {
            $plink = !empty($model->product_link) ? $model->product_link : \app\models\SupplierQuotes::getUrl($model->sku);
            $string = Html::input('text', 'product_link', $plink, ['readonly'=>true,'style'=>'width:100px']);
            $string .= "<a href='{$plink}' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>";
            return getDivContent($string ,120);
        }
    ],
    'note' => [
        'label' => '备注',
        'format' => 'raw',
        'value' => function($model) {
            $string = $title = '';
            $data = PurchaseNote::find()->where(['demand_number'=>$model->demand_number])->orderBy('id asc')->asArray()->all();
            if ($data) {
                $string = mb_substr($data[0]['note'],0,30).'...';
                foreach ($data as $v) {
                    $title .= $v['create_time'].'(by '.$v['create_user'].")\r\n".$v['note']."\r\n";
                }
            }
            return "<div style='width:60px' class='note-show' title='{$title}' show-title='".str_replace("\r\n", "<br>", $title)."'>{$string}</div>";
        }
    ],
];

$fields_params = [];
foreach ($columns as $k=>$v) {
    $fields_params[$k] = $v['label'];
}
$fields_params['table'] = 'overseas_order_list';
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

$toolbar = isset($_GET['requisition_number']) ? ['{export}'] : [];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => [
        'id' => 'grid_purchase_order',
    ],
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'], input[name='".$dataProvider->getPagination()->pageParam."']",
    'pager' => [
        'options' => ['class' => 'pagination', 'style' => 'display:block;'],
        'class' => \liyunfang\pager\LinkPager::className(),
        'pageSizeList' => [5,10,20,50,100,200,500],
        'firstPageLabel' => '首页',
        'prevPageLabel' => '上一页',
        'nextPageLabel' => '下一页',
        'lastPageLabel' => '末页',
    ],
    'columns' => isset($_GET['requisition_number']) ? $columns :
        array_merge([
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name' => 'id',
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->demand_number];
                }
            ], 
        ], $columns),
    'containerOptions' => ["style"=>"overflow:auto;"],
    'toolbar' =>  $toolbar,
    'exportConfig' => [
        GridView::EXCEL => [],
    ],
    'pjax' => false,
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'responsive' => true,
    'hover' => true,
    'floatHeader' => false,
    'panel' => [
        'type' => 'success'
    ],
]);

?>
<?php if (!isset($_GET['requisition_number'])) : ?>
</form>
<?php endif; ?>
<?php
$imgurl = Url::toRoute(['purchase-suggest/img']);

$js = <<<JS

$(function() {
    $(".same-pur-number").change(function(){
        var data_field = $(this).attr('data-field');
        var data_pur_number = $(this).attr('data-pur-number');
        var value = $(this).val();
        if(data_pur_number && data_pur_number !='undefined'){
            $(".same-pur-number").each(function(){
                if ($(this).attr('data-field') == data_field && $(this).attr('data-pur-number') == data_pur_number) {
                    $(this).val(value);
                    if ($(this).attr('select2') == 1) {
                        var selectid = $(this).attr('id');
                        $('#select2-'+selectid+'-container').html($(this).find("option:selected").text());
                        $('#select2-'+selectid+'-container').attr('title',$(this).find("option:selected").text());
                    }
                }
            })
        }
    });
    $(".same-supplier-name").change(function(){
        var data_field = $(this).attr('data-field');
        var data_supplier_name = $(this).attr('data-supplier-name');
        var value = $(this).val();
        if(data_supplier_name && data_supplier_name !='undefined'){
            $(".same-supplier-name").each(function(){
                if ($(this).attr('data-field') == data_field && $(this).attr('data-supplier-name') == data_supplier_name) {
                    $(this).val(value);
                    if ($(this).attr('select2') == 1) {
                        var selectid = $(this).attr('id');
                        $('#select2-'+selectid+'-container').html($(this).find("option:selected").text());
                        $('#select2-'+selectid+'-container').attr('title',$(this).find("option:selected").text());
                    }
                }
            })
        }
    });
    
    $(".btn-print-data").click(function(){
        var data = get_ids();
        if (data.length == 0) {
            layer.msg('请至少选择一条数据');
            return false;
        }
        window.open($(this).attr('data-url')+'?ids='+data);
    });

    $(".btn-edit-fields").click(function(){
        $("#create-modal .modal-title").text('编辑显示内容');
        $.post('/member/update-table-fields', {$fields_params}, function (data) {
            $('#create-modal .modal-body').html(data);
        });
    })
    
    $(".btn-audit").click(function(){
        var data = get_ids();
        if (data.length == 0) {
            layer.msg('请至少选择一条数据');
            return false;
        }
        $("#create-modal .modal-title").text($(this).text());
        $('#create-modal .modal-body').html('加载中...');
        $.post($(this).attr('data-url'), {data:data}, function (data) {
            $('#create-modal .modal-body').html(data);
        });
    })

    $(".order-status-button").click(function(){
        var demand_number = $(this).attr('data-demand-number');
        $("#create-modal .modal-title").text('查看日志 '+demand_number);
        $('#create-modal .modal-body').html('加载中...');
        $.get('demand-log', {demand_number:demand_number}, function(data) {
            $('#create-modal .modal-body').html(data);
        });
    })

    $(".btn-submit").click(function(){
        var data = get_ids();
        if (data.length == 0) {
            layer.msg('请至少选择一条数据');
            return false;
        }
        $("#order-form").attr('action', $(this).attr('data-action'));
        $("#order-form").submit();
    })

    $(".btn-info-submit").click(function(){
        var data = get_ids();
        if (data.length == 0) {
            layer.msg('请至少选择一条数据');
            return false;
        }
        $.post($(this).attr('data-url'), $("#order-form").serialize(), function (data) {
            if (data.code == 1) {
                window.location.reload();
            } else {
                layer.alert(data.message);
            }
        },'json');
    })

    $(".btn-note-submit").click(function(){
        var data = get_ids();
        if (data.length == 0) {
            layer.msg('请至少选择一条数据');
            return false;
        }
        var form_action = $(this).attr('data-action');
        layer.prompt({title: '备注', value: '', formType: 2}, function (remark, index) {
            $("#form-remark").val(remark);
            layer.close(index);
            $("#order-form").attr('action', form_action);
            $("#order-form").submit();
        });
    })

    $(".note-show").click(function(){
        layer.open({
          type: 1,
          shade: false,
          title: false, 
          content: "<div style='padding:10px'>"+$(this).attr('show-title')+"</div>", 
        });
    })

    $(".field-note").focus(function(){
        $(this).parent().find(".field-input-note").show();
    })

    $(".field-note-change").change(function(){
        $(this).parent().find(".field-input-note").show();
        $(this).parent().find(".field-input-note").focus();
    })

    $(".panel-success th").dblclick(function(){
        var data_col = $(this).attr('data-col-seq');
        var string = '<table style="width:100%">';
        $(".panel-success td").each(function(){
            if ($(this).attr('data-col-seq') == data_col) {
                string += '<tr><td>'+$(this).html()+'</td></tr>';
            }
        })
        string += '</table>';
        layer.open({
          type: 1,
          skin: 'layui-layer-rim',
          area: ['400px', '240px'],
          content: "<div style='padding:10px 20px'>"+string+"</div>",
        });
    })

    $(".purchase_arrival_date").change(function(){
        var obj = $(this);
        var demand_number = obj.attr('data-demand-number');
        var arrival_date = obj.val();
        if (arrival_date == '') return false;
        $.post('update-purchase-arrival-date', {demand_number:demand_number,arrival_date:arrival_date}, function (data) {
            if (data.code == 1) {
                layer.msg('修改成功');
            } else {
                layer.msg(data.message);
                obj.val(data.data.arrival_date);
                obj.focus();
            }
        },'json');
    })

    $(".delete-express").click(function(){
        var obj = $(this);
        layer.confirm('确认要删除吗？', {btn: ['确定','取消']}, function(){
            var id = obj.attr('data-id');
            $.post('delete-express-no', {id:id}, function (data) {
                if (data.code == 1) {
                    layer.msg('删除成功');
                    obj.parent().remove();
                } else {
                    layer.msg(data.message);
                }
            },'json');
        });
    })

    //图片大图查看
    $(".product-title").click(function(){
        $("#create-modal .modal-title").text('查看图片');
        $('#create-modal .modal-body').html('加载中...');
        $.get('$imgurl', {img: $(this).attr('data-imgs'), sku: $(this).attr('data-skus')}, function(data) {
            $('#create-modal .modal-body').html(data);
        });
    })

    $(".get-all-buyer").click(function(){
        $.get('get-all-buyer', function(data) {
            if (data.code == 1) {
                var string = '';
                $.each(data.data, function(i,n){
                    if (string) string += ', '
                    string += n;
                })
                layer.open({
                  type: 1,
                  skin: 'layui-layer-rim',
                  area: ['400px', '240px'],
                  content: "<div style='padding:10px 20px'>"+string+"</div>",
                });
            }
        },'json');
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

$('.settlement_ratio_define').click(function() {
    var parent = $(this).parents('.input-group');
    parent.find('.label-box').toggle();
});

$('.settlement_ratio_clear').click(function() {
    var parent = $(this).parents('.input-group');
    parent.find('input').val('');
    parent.find('input').change();
});

$('.label-box span').click(function() {
    var parent = $(this).parents('.input-group');
        ratio  = parent.find('input');
        _ratio = ratio.val();
    if($(this).text() == '关闭') {
        parent.find('.label-box').toggle();
        return true;
    }
    if(_ratio == '') {
         _ratio += $(this).text();
    } else {
        var _ratioes = _ratio.split('+');
        var total = parseInt($(this).text());
        for(i = 0; i < _ratioes.length; i++) {
            total += parseInt(_ratioes[i]);
        }
        if(total > 100) {
            layer.tips('总百分比不能超过100', ratio, {tips: 1});
            return false;
        }
         _ratio += '+'+$(this).text();
        if (total == 100) {
            parent.find('.label-box').toggle();
        }
    }
    parent.find('input').val(_ratio);
    parent.find('input').change();
});

$(document).ready(function () {
    if (is_requisition_iframe == 1) return false;
    /*
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
    */
    var table_height = $(window).height() - 480;
    $("#grid_purchase_order-container").css('height',table_height+'px');
    $("#grid_purchase_order-container").scroll(function(){
        var ttop = $("#grid_purchase_order-container").scrollTop();
        //console.log(ttop);
        $(".panel-success thead").css('transform','translateY(' + ttop + 'px)');
    });

    $(".pull-right .summary").append(" 总金额:{$totalprice}");
    $("*[name=per-page]").change(function(){
        var pageSize=$(this).val();
        var url = '/overseas-purchase-order2/index?pageSize=' + pageSize;
        window.location.href=url;
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

<script type="text/javascript">
var is_requisition_iframe = <?php echo isset($_GET['requisition_number']) ? 1 : 0 ?>;
</script>
