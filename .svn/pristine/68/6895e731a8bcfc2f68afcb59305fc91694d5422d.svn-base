<?php
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\AlibabaZzh;
$this->title = '添加子账号';
$this->params['breadcrumbs'][] = $this->title;
?>

<style type="text/css">

</style>


<div class="my-box" style="background-color: #fff;">

    <?php $form = ActiveForm::begin(['id' => 'fm']); ?>


    <div class="form-group">
        <label>账号</label>
        <input type="text" id="account" class="form-control" name="account" value="" style="width: 350px;">
    </div>

    <div class="form-group">
        <label>付款人</label>

        <div style="width: 355px;">
            <?php
            echo Select2::widget([
                'name' => 'pid',
                'value' => '',
                'data' => AlibabaZzh::getPayer(),
                'options' => ['multiple' => false, 'placeholder' => 'Select states ...', 'id' => 'pid']
            ]);
            ?>

        </div>

    </div>

    <div class="form-group">
        <label>级别</label>

        <div style="width: 355px;">

            <?php
            echo Select2::widget([
                'name' => 'level',
                'value' => '',
                'data' => [0 => '出纳', 1 => '非出纳'],
                'options' => ['multiple' => false, 'placeholder' => 'Select states ...', 'id' => 'level']
            ]);
            ?>

        </div>


    </div>

    <div class="form-group">

        <label>使用者</label>

        <div style="width: 355px;">

            <?php
            echo Select2::widget([
                'name' => 'user',
                'value' => '',
                'data' => \app\services\BaseServices::getEveryOne(),
                'options' => ['multiple' => false, 'placeholder' => 'Select states ...', 'id' => 'user']
            ]);
            ?>
        </div>

    </div>

    <div class="form-group">
        <label></label>
        <input type="button" class="btn btn-primary" id="btn-submit" value="保存">
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php


$js = <<<JS
$(function() {
    
    $('#btn-submit').click(function() {
        var a = $('#account').val(),
            b = $('#pid').val(),
            c = $('#level').val(),
            d = $('#user').val();
        
        if(!a || !c || !d) {
            layer.alert('数据输入有误');
            return false;
        }
        
        if(c == 1) {
            if(!b) {
                layer.alert('非出纳账户，必须指定付款人');
                return false;
            }
        }
        $('#fm').submit();
    });
    
});
JS;
$this->registerJs($js);
?>


