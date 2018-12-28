<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;

?>
<?php $form = ActiveForm::begin(); ?>
<div class="purchase-order-form">
    <div class="container-fluid">
    	<p style="font-size:14px">变更的PO：</p>
    	<?php foreach ($data as $v) : ?>
    	<div style="line-height:30px">
    		[PO:<?php echo $v['pur_number']?>] &nbsp;
    		[供应商:<?php echo $v['supplier_name']?>] &nbsp;
    		[采购员:<?php echo $v['buyer']?>]
        </div>
        <input type="hidden" name="pur_numbers[]" value="<?php echo $v['pur_number']?>" />
        <?php endforeach; ?>
    </div>

    <div class="col-md-12" style="margin-top:20px"><?= $form->field($model, 'buyer')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入采购员 ...'],
        'data' =>\app\services\BaseServices::getBuyer('name'),
        'pluginOptions' => [
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ])->label('采购员');
    ?></div>
    <div class="form-group" style="margin-top:20px;padding-left:10px">
        <?= Html::submitButton('提交', ['class'=>'btn btn-success']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>