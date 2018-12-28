<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title =Yii::t('app','深圳市易佰采购后台管理系统');

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-envelope form-control-feedback'></span>"
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
];
?>
<style>
    .login-page, .register-page{
    background: url("/images/back.jpg");
        background-repeat:no-repeat;
        background-size:100%;-moz-background-size:100% 100%;
};    
</style>
<div class="login-box">
    <div class="login-logo">
        <a href="#" style="color:#fff"><b><?=Yii::t('app','易佰采购后台管理系统')?></b></a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg"><?=Yii::t('app','请输入用户名与帐号')?>,推荐使用<b style="color: red">google</b>浏览器</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

        <?= $form
            ->field($model, 'username', $fieldOptions1)
            ->label(false)
            ->textInput(['placeholder' => $model->getAttributeLabel('username')]) ?>

        <?= $form
            ->field($model, 'password', $fieldOptions2)
            ->label(false)
            ->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>

        <div class="row">
           <!-- <div class="col-xs-8">
                <?/*= $form->field($model, 'rememberMe')->checkbox() */?>
            </div>-->
            <!-- /.col -->
            <div class="col-xs-4">
                <?= Html::submitButton(Yii::t('app','登录'), ['class' => 'btn btn-primary btn-block btn-flat sumbit123','name' => 'login-button']) ?>
            </div>
            <!-- /.col -->
        </div>


        <?php ActiveForm::end(); ?>



    </div>
    <!-- /.login-box-body -->
</div><!-- /.login-box -->
<?php
$js = <<<JS
$('.sumbit123').click(function(){
        var user_name = $('#loginform-username').val();
        $.post("/site/check",{'user_name':user_name},function(result){
            var obj = eval('('+result+')');
            if(obj.status == 1){
                /* alertMsg.info(obj.msg); */
                alert(obj.msg);
                return false;
            }else{
                $('#login-form').submit();
            }
        });
    });
    $(window).load(function(){
        var url_list = new Array;
      // url_list[0] = 'http://192.168.10.17/caigou.php'; //深圳
      // url_list[1] = 'http://192.168.1.15:8089/caigou.php'; //东莞
      // url_list[2] = 'http://192.168.5.212/caigou.php'; //武汉
      // url_list[3] = 'http://192.168.0.80/caigou.php'; //成都

        for(i=0;i<url_list.length;i++){
            if(url_list[i] != ''){
                $.ajax(  {
                        type:'get',
                        url : url_list[i]+'?check=check&key='+i,
                        dataType : 'jsonp',
                        jsonp:"jsoncallback",
                        success  : function(data) {
                            if(data.check=='check'){
                                window.location.href = url_list[data.key];
                            }
                        },
                        error : function() { }
                    }
                );
            }
        }
    });

JS;
$this->registerJs($js);
?>