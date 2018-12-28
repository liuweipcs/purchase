<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use \app\services\BaseServices;
use yii\widgets\ActiveForm;

$this->title = '换货物流';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin(); ?>
<table class='table table-hover table-bordered table-striped' >
    <tr>
        <td><?= $form->field($model, 'pur_number')->textInput(['maxlength'=>true,'value'=>$pur_number,'readonly'=>true]); ?></td>
        <td><?= $form->field($model, 'express_no'); ?></td>

    </tr>
    <tr>
        <td> <?= $form->field($model,'freight')->textInput(['maxlength'=>true]);?></td>
        <td>
            <?= $form->field($model, 'cargo_company_id')->dropDownList(BaseServices::getLogisticsCarrier(),['prompt'=>'Choose','required'=>true]);?>
        </td>
    </tr>
</table>
<div><?=$form->field($model,'note')->textarea(['cols'=>10,'rows'=>2,'style'=>'width:100%','required'=>true,'placeholder'=>'如果是换货请把需要的信息告诉仓库吧'])?></div><br/>
<p style="text-align: right">
    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Close', '#', ['class' => 'btn btn-primary closes','data-dismiss'=>'modal']) ?>
</p>
<?php ActiveForm::end(); ?>
<script>
    $("[name='cargo_company_id']").on('change', function () {
        var cargo_company = $(this).children(':selected').html();
        $(this).prev().val(cargo_company);
    })
</script>