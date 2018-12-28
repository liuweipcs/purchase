<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = '修改备注';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin(); ?>
<div class="row">
    <div class="col-md-3">
        <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>
    </div>
    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>