<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\models\PurchaseTacticsSearch;
?>

<?php $url = \yii\helpers\Url::to(['/supplier/search-supplier']);
$form = ActiveForm::begin(['action' =>['index'] , 'method' => 'get']); ?>

<div class="col-md-1"><?= $form->field($model, 'warehouse_type')->dropDownList(Yii::$app->params['warehouse'],['prompt' => '请选择'])->label('用户类型') ?></div>
<div class="col-md-1">
    <?= $form->field($model, 'sku')->label('SKU') ?>
</div>

<div class="col-md-2">
    <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入供应商 ...','value'=>!empty($model->supplier_code) ? $model->supplier_code :''],
        'pluginOptions' => [
            'placeholder' => 'search ...',
            'allowClear' => true,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'ajax' => [
                'url' => $url,
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term,status:null}; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ])->label('供应商');
    ?>
</div>
<div class="col-md-1">
    <?= $form->field($model, 'warehouse_code')->dropDownList(PurchaseTacticsSearch::getWarehouseList(), ['prompt' => '请选择'])->label('仓库') ?>
</div>
<div class="form-group col-md-2" style="margin-top: 24px;float:left">
    <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>


