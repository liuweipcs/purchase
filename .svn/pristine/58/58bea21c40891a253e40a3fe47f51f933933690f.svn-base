<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/14
 * Time: 14:22
 */


/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */

?>

<?php $form = ActiveForm::begin([
        'id' => 'create-quotes',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 2,'deviceSize' => ActiveForm::SIZE_MEDIUM],
        'fieldConfig' => [  //统一修改字段的模板
            'template' => "{label}\n<div class='col-md-8'>{input}</div>\n<div class='col-md-3'>{error}</div>",
            'labelOptions' => ['class' => 'control-label col-md-3'],
        ],
        'enableAjaxValidation' => false,
        //'validationUrl' => Url::toRoute(['validate-form']),
    ]
); ?>
<div class="raw">
    <div class="col-md-12 type">
        <?= $form->field($model,'module')->textInput(['required'=>true])->label('module')?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model,'controller')->textInput()->label('controller')?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model,'action')->textInput()->label('action') ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model,'permission_name')->textInput()->label('permission_name') ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model,'parent_id')->dropDownList(\app\modules\manage\models\SupplierPermission::getPermission())->label('父级id') ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model,'type')->dropDownList([1=>'一级菜单栏',2=>'二级菜单栏',3=>'操作按钮及字段'])->label('权限级别') ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model,'is_show')->dropDownList([0=>'否',1=>'是'])->label('是否左侧显示') ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model,'order_num')->input('number')->label('排序') ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model,'icon')->textInput() ?>
    </div>
</div>
<div class="form-group">
    <div class=" col-md-12 col-md-offset-8">
        <button class="btn btn-success" type="submit">立即提交</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">关闭</button>
    </div>
</div>
<div style="clear: both"></div>
<?php ActiveForm::end(); ?>

<?php
$formCheck = \yii\helpers\Url::toRoute('check-form');
$js = <<<JS

JS;
$this->registerJs($js);
?>





