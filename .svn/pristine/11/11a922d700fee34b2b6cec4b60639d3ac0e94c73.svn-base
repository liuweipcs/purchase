<?php
use yii\widgets\ActiveForm;
$this->title = '合同模板配置';
$this->params['breadcrumbs'][] = $this->title;
?>

<style type="text/css">

</style>

<?php ActiveForm::begin(['id' => 'setting-form']); ?>

<div class="mt-box">
    <form action="/" method="post">

        <div class="fg">
            <label>模板名称</label>
            <input type="text" name="name" value="" style="width: 350px;">
        </div>

        <div class="fg">
            <label>合同状态</label>
            <input type="radio" name="status" value="0" checked> 禁用
            <input type="radio" name="status" value="1"> 启用
        </div>

        <div class="fg">
            <label>适用平台</label>
            <input type="radio" name="platform" value="1" checked> 国内
            <input type="radio" name="platform" value="2"> 海外
            <input type="radio" name="platform" value="3"> FBA
        </div>

        <div class="fg">
            <label>合同类型</label>
            <select name="type" style="width: 350px;">
                <option value="">请选择...</option>
                <option value="DDHT">采购订单合同</option>
                <option value="DZHT">采购对账合同</option>
                <option value="FKSQS">付款申请书</option>
                <option value="GXHT">购销合同</option>
            </select>
        </div>

        <div class="fg">
            <label style="float: left;">模板样式</label>
            <ul class="img-list">

                <?php foreach($files as $k => $f): ?>
                <li data-code="<?= $f ?>">
                    <img class="thum" src="/images/tpl.jpg">
                    <p><?= $f ?></p>
                    <span class="icon"></span>
                </li>
                <?php endforeach; ?>

            </ul>
            <input type="hidden" name="style_code" value="">
        </div>

        <p class="text_line"></p>

        <!--<div class="fg">
            <label style="float: left;">模板内容</label>
            <div id="content" style="float:left;"></div>
        </div>

        <p class="text_line"></p>-->

        <div class="fg">
            <label></label>
            <input type="submit" value="保存">
            <input type="reset" value="重置">
        </div>

    </form>
</div>

<?php ActiveForm::end(); ?>

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


