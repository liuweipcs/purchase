<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\SupplierServices;

/* @var $this yii\web\View */
/* @var $model app\models\BankCardManagement */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bank-card-management-form">

    <?php $form = ActiveForm::begin(['enableClientValidation'=>false]); ?>

    <div class="col-md-4"><?= $form->field($model, 'head_office')->textInput(['placeholder'=>'请输入主行','id'=>'head_office','required'=>true,'value'=>is_array(SupplierServices::getPayBank($model->head_office)) ? '' :SupplierServices::getPayBank($model->head_office)]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'branch')->textInput(['maxlength' => true,'placeholder'=>'如：农业银行深圳xxx支行','required'=>true]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'account_holder')->textInput(['maxlength' => true,'placeholder'=>'如：陈XX','required'=>true]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'account_number')->textInput(['maxlength' => true,'placeholder'=>'如：6225 0000 0000 0000 (新增后，不允许修改)必填','required'=>true]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'account_abbreviation')->textInput(['maxlength' => true,'placeholder'=>'用于快速录入,如后四位2541','required'=>true]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'payment_password')->textInput(['maxlength' => true,'placeholder'=>'此密码应用于采购业务,如果未使用该业务,请勿填写密码;']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'payment_types')->dropDownList(['1'=>'银行卡','2'=>'支付宝'],['prompt'=>'请选择','required'=>true]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'account_sign')->dropDownList(['1'=>'对公帐号','2'=>'对私帐号'],['prompt'=>'请选择','required'=>true]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'status')->dropDownList(['1'=>'可用','2'=>'不可用'],['prompt'=>'请选择','required'=>true]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'application_business')->dropDownList(['1'=>'采购']) ?></div>

    <div class="col-md-4"><?= $form->field($model, 'k3_bank_account')->textInput(['maxlength' => true,'placeholder'=>'PMS提供将采购付款账号绑定我方银行账号功能，以便财务维护我方银行资料','required'=>true]);?></div>

    <div class="col-md-8"><?= $form->field($model, 'remarks')->textInput(['maxlength' => true,'required'=>true]) ?></div>



    <div class="form-group col-md-4">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$js = <<<JS
    layui.config({
          base: '/js/layExtend/' 
        });
    var complete = function(elem,url) {
    layui.use(['autocomplete'], function(){
        var autocomplete = layui.autocomplete;
        autocomplete.render({
                elem: elem,
                url:url,
                cache: false,
                template_val: '{{d.text}}',
                template_txt: '{{d.text}}',
                onselect: function (resp) {
                    }
            });
            });
        }
    complete($('#head_office'),'/bank-config/get-bank-config');
        
JS;

$this->registerJs($js);
?>