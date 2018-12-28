<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;

?>
<style>
    .col-md-1, .col-md-2{padding-left: 0;}
</style>
<div class="stockin-search">
    <?php $form = ActiveForm::begin(['action' => ['index'], 'method' => 'get']); ?>
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_code')->textInput(['placeholder'=>'如A001']) ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_name')->textInput(['placeholder'=>'支持模糊查询']) ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'buyer')->widget(Select2::className(), [
            'options' => ['placeholder' => '请选择'],
            'data' =>BaseServices::getEveryOne(),
            'pluginOptions' => [
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
            ]
        ]);
        ?>
    </div>
    <div class="col-md-1" >
        <label class="control-label" for="purchaseorderpaysearch-applicant">创建时间：</label>
        <?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'SupplierSearch[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'SupplierSearch[start_time]',
                'endAttribute' => 'SupplierSearch[end_time]',
                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("last month"))],
                'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?>
    </div>
    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', '重置'), Url::toRoute('index'),['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>