<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use kartik\select2\Select2;
use yii\web\JsExpression;

$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\TodayListSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-search">
    <?php $form = ActiveForm::begin([
        'action' => ['summary-detail'],
        'method' => 'get',
    ]);?>
    <div class="col-md-1">
        <?= $form->field($model, 'sku')->textInput() ?>
    </div>
   <!-- <div class="col-md-1">
        <?/*= $form->field($model, 'demand_number')->textInput() */?>
    </div>-->
    <div class="col-md-1"><?= $form->field($model, 'purchase_warehouse')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选仓库 ...'],
            'data' =>BaseServices::getWarehouseCode(),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                /*'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],*/
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])?></div>
    <div class="col-md-1"><?= $form->field($model, 'order_buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...','value'=>!empty($model->order_buyer) ? $model->order_buyer :''],
            'data' =>BaseServices::getBuyer('name'),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                /*'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],*/
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('采购员');
        ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'sales')->textInput()->label('销售') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'pur_number')->textInput()->label('采购单号') ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'order_status')->dropDownList(['99'=>'未全部到货','3'=>'已审批','6'=>'全到货','7'=>'等待到货','8'=>'部分到货等待剩余','9'=>'部分到货不等待剩余','10'=>'已作废'],['prompt' => '请选择'])->label('采购单到货状态') ?></div>
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...'],
            'pluginOptions' => [
                'placeholder' => 'search ...',
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('供货商');
        ?>
    </div>
    <div class="col-md-1" ><label class="control-label" for="purchaseorderpaysearch-applicant">创建时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PlatformSummarySearch[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PlatformSummarySearch[start_time]',
                'endAttribute' => 'PlatformSummarySearch[end_time]',
                'startInputOptions' => ['value' => $model->start_time ? $model->start_time : date('Y-m-d H:i:s',strtotime("last year"))],
                'endInputOptions' => ['value' => $model->end_time ? $model->end_time :date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>
    <div class="col-md-1">
        <?= $form->field($model, 'is_purchase')->dropDownList(['all'=>'全部','1'=>'未生成','2'=>'已生成'],['value'=>$model->is_purchase])->label('是否生成采购单') ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'group_id')->dropDownList(BaseServices::getAmazonGroup(),['value'=>$model->group_id])?></div>

    <div class="col-md-1">
        <?= $form->field($model, 'is_drawback')->dropDownList(['all'=>'全部','1'=>'不含税','2'=>'含税'],['value'=>$model->is_drawback])->label('是否含税') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'is_back_tax')->dropDownList(['all'=>'全部','1'=>'可退税','2'=>'不可退税'],['value'=>$model->is_back_tax])->label('是否可退税') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'amount_1')->textInput(['placeholder' => '20'])->label('数量1(区间)') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'amount_2')->textInput(['placeholder' => '50'])->label('数量2(区间)') ?>
    </div>

    <div class="col-md-1"><?= $form->field($model, 'level_audit_status')->dropDownList(\app\services\PlatformSummaryServices::getLevelAuditStatus(),['prompt' => '请选择'])->label('需求状态') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'pay_status')->dropDownList(app\services\PurchaseOrderServices::getPayStatus(),['prompt' => '请选择'])->label('付款状态') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'refund_status')->dropDownList(['-100' => '作废','1' => '待收款', '2' => '已收款'],['prompt' => '请选择'])->label('退款状态') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'date_eta_is_timeout')->dropDownList(['1' => '是', '2' => '否'],['prompt' => '请选择'])->label('预计到货是否超时') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'avg_eta_is_timeout')->dropDownList(['1' => '是', '2' => '否'],['prompt' => '请选择'])->label('权均交期是否超时') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'weight_sku')->dropDownList(['1' => '是', '2' => '否'],['prompt' => '请选择'])->label('是否加重sku') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'xiaoshou_zhanghao')->dropDownList(BaseServices::getXiaoshouZhanghao(),['value'=>$model->xiaoshou_zhanghao])->label('销售账号')?></div>
    <div class="col-md-1"><?= $form->field($model, 'demand_number')->textInput() ?></div>
    
    <div class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <a class="btn btn-default" href="summary-detail">重置</a>
    </div>
    <?php ActiveForm::end(); ?>
</div>
