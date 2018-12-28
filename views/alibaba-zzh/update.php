<?php
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\AlibabaZzh;


$this->title = '修改子账号';
$this->params['breadcrumbs'][] = $this->title;





?>

<style type="text/css">

</style>


<div class="my-box" style="background-color: #fff;">

    <?php $form = ActiveForm::begin(); ?>


    <div class="form-group">
        <label>账号</label>
        <input type="text" class="form-control" name="account" value="<?= $model->account ?>" style="width: 350px;">
    </div>

    <div class="form-group">
        <label>付款人</label>

        <div style="width: 355px;">

            <?php
            echo Select2::widget([
                'name' => 'pid',
                'value' => $model->pid,
                'data' => AlibabaZzh::getPayer(),
                'options' => ['multiple' => false, 'placeholder' => 'Select states ...']
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
                'value' => $model->level,
                'data' => [0 => '出纳', 1 => '非出纳'],
                'options' => ['multiple' => false, 'placeholder' => 'Select states ...']
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
                'value' => $model->user,
                'data' => \app\services\BaseServices::getEveryOne(),
                'options' => ['multiple' => false, 'placeholder' => 'Select states ...']
            ]);
            ?>
        </div>


    </div>

    <input type="hidden" name="id" value="<?= $model->id ?>">

    <div class="form-group">
        <label></label>
        <input type="submit" class="btn btn-primary" value="保存">
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php


$js = <<<JS
$(function() {
    
    $('.img-list li').click(function() {
        if($(this).hasClass('selected')) {
            return false;
        }
        $(this).parent().find('li.selected').removeClass('selected');
        $(this).addClass('selected');
        $('input[name="style_code"]').val($(this).attr('data-code'));
        
        /*$.get('get-tpl', {code: $(this).attr('data-code')}, function(html) {
            $('#content').html(html.replace(/<\?=\s+\S*\s+\?>/igm, ''));
        });*/
        
    });
    
    
    
});

JS;
$this->registerJs($js);
?>


