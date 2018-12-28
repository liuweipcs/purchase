<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\Url;

$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
$this->title = '修改供应商';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-md-3">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...'],
            'pluginOptions' => [

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
        ])->label('供应商');
        ?>
    </div>
    <a title="添加供应商" href="<?=Url::toRoute(['supplier/create'])?>" target="_blank" style="margin-top: 33px;" class="glyphicon glyphicon-plus add-supp"></a>
</div>
<div class="row">
    <input type="hidden" name="PurchaseOrder[id]" value="<?=$id?>">
    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
