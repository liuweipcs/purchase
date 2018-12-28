<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\services\BaseServices;
?>
<?php $form = ActiveForm::begin(); ?>
<div class="purchase-order-form">
    <div class="container-fluid">
    	<p style="font-size:14px">可审核需求：</p>
    	<?php foreach ($data as $v) : ?>
    	<div style="line-height:30px">
    		[需求单号:<?php echo $v['demand_number']?>] &nbsp;
    		[SKU:<?php echo $v['sku']?>]
    		[采购仓:<?php echo BaseServices::getWarehouseCode($v['purchase_warehouse'])?>]
        </div>
        <input type="hidden" name="demand_numbers[]" value="<?php echo $v['demand_number']?>" />
        <?php endforeach; ?>
    </div>
    
    <div class="col-md-12" style="margin-top:20px"><?= $form->field($model, 'cargo_company_id')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入快递公司 ...','required'=>true],
        'data' => BaseServices::getLogisticsCarrier(null,'name'),
        'pluginOptions' => [
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ])->label('快递公司');
    ?></div>
    
    <div class="col-md-12">
    	<?= $form->field($model, 'express_no')->input('text',['required'=>true])->label('快递号');?>
    </div>
    
    <div class="form-group" style="margin-top:20px;padding-left:10px">
        <?= Html::submitButton('提交', ['class'=>'btn btn-success']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>