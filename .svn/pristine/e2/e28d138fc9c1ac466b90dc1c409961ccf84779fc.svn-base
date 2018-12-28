<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="basic-form">

    <?php $form = ActiveForm::begin(); ?>
    <table class="table table-hover ">
        <h3>1.加权系数</h3>
        <h5>使用"加权"，获取更精确的日均销量，用于"波动上升"，"波动下降"的销售走势</h5>
        <thead>
            <tr>
                <th>销量走势</th>
                <th>3天</th>
                <th>7天</th>
                <th>15天</th>
                <th>30天</th>
            </tr>
        </thead>
        <tbody class="pay">
            <tr class="pay_list ">
                <td><?=$form->field($model, "type[]")->dropDownList(['wave_up'=>'波动上升'],['class' => 'form-control','readonly'=>true,'prompt'=>'请选择','style'=>'width:110px','value'=>'wave_up','required'=>true])->label(false)?></td>
                <td><?=$form->field($model, "days_3[]")->textInput(['value'=>isset($data['wave_up']['days_3'])?$data['wave_up']['days_3']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_7[]")->textInput(['value'=>isset($data['wave_up']['days_7'])?$data['wave_up']['days_7']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_15[]")->textInput(['value'=>isset($data['wave_up']['days_15'])?$data['wave_up']['days_15']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_30[]")->textInput(['value'=>isset($data['wave_up']['days_30'])?$data['wave_up']['days_30']:''])->label(false)?></td>
            </tr>
            <tr class="pay_list ">
                <td><?=$form->field($model, "type[]")->dropDownList(['wave_down'=>'波动下降'],['class' => 'form-control','readonly'=>true,'prompt'=>'请选择','style'=>'width:110px','value'=>'wave_down','required'=>true])->label(false)?></td>
                <td><?=$form->field($model, "days_3[]")->textInput(['value'=>isset($data['wave_down']['days_3'])?$data['wave_down']['days_3']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_7[]")->textInput(['value'=>isset($data['wave_down']['days_7'])?$data['wave_down']['days_7']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_15[]")->textInput(['value'=>isset($data['wave_down']['days_15'])?$data['wave_down']['days_15']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_30[]")->textInput(['value'=> isset($data['wave_down']['days_30'])?$data['wave_down']['days_30']:''])->label(false)?></td>
            </tr>
            <tr class="pay_list ">
                <td><?=$form->field($model, "type[]")->dropDownList(['last_up'=>'持续上升'],['class' => 'form-control','readonly'=>true,'style'=>'width:110px','value'=>'wave_down','required'=>true])->label(false)?></td>
                <td><?=$form->field($model, "days_3[]")->textInput(['value'=>isset($data['last_up']['days_3'])?$data['last_up']['days_3']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_7[]")->textInput(['value'=>isset($data['last_up']['days_7'])?$data['last_up']['days_7']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_15[]")->textInput(['value'=>isset($data['last_up']['days_15'])?$data['last_up']['days_15']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_30[]")->textInput(['value'=> isset($data['last_up']['days_30'])?$data['last_up']['days_30']:''])->label(false)?></td>
            </tr>
            <tr class="pay_list ">
                <td><?=$form->field($model, "type[]")->dropDownList(['last_down'=>'持续下降'],['class' => 'form-control','readonly'=>true,'style'=>'width:110px','value'=>'wave_down','required'=>true])->label(false)?></td>
                <td><?=$form->field($model, "days_3[]")->textInput(['value'=>isset($data['last_down']['days_3'])?$data['last_down']['days_3']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_7[]")->textInput(['value'=>isset($data['last_down']['days_7'])?$data['last_down']['days_7']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_15[]")->textInput(['value'=>isset($data['last_down']['days_15'])?$data['last_down']['days_15']:''])->label(false)?></td>
                <td><?=$form->field($model, "days_30[]")->textInput(['value'=> isset($data['last_down']['days_30'])?$data['last_down']['days_30']:''])->label(false)?></td>
            </tr>
        </tbody>
    </table>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
