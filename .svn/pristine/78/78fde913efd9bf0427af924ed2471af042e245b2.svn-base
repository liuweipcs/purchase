<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \mdm\admin\models\form\ChangePassword */

$this->title = Yii::t('app', '修改密码');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <h4 style="color:red;">新密码必须包含字母,数字,特殊字符,长度至少为7</h4>
    <h3 style="color: red">特殊符号最好为标点符号（不要包含#*%&^@+）</h3>

    <p><?=Yii::t('app','请填写以下字段以更改密码')?></p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-change']); ?>
            <?= $form->field($model, 'oldPassword')->passwordInput()->label('原密码') ?>
            <?= $form->field($model, 'newPassword')->passwordInput()->label('新密码') ?>
            <?= $form->field($model, 'retypePassword')->passwordInput()->label('确认新密码') ?>
            <div class="form-group">
                <?= Html::button(Yii::t('app', '修改'), ['class' => 'btn btn-primary submitchange', 'name' => 'change-button']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$js = <<<JS
    $('.submitchange').click(function() {
        var newPassword = $('#user-newpassword').val();
        var retypePassword = $('#user-retypepassword').val();
        if(newPassword!=retypePassword){
            alert('新密码与确认密码不一致！');
            return false;
        }
        var ret = /(?=.*\d)(?=.*[a-zA-Z])(?=.*[^a-zA-Z0-9]).{8,30}/;
        if(ret.test(newPassword)){
            $('#form-change').submit();
        }else {
            alert('密码太简单');
        }
    });
JS;
$this->registerJs($js);
?>
