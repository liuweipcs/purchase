<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
?>
<div class="jumbotron">
    <h3>采购建议数：<?= $total ?>条（供应商维度）</h3>
    <p><a class="btn btn-primary lead-suggest-submit" href="/purchase-suggest/lead-suggest?ids=<?= $ids ?>" role="button">一键生成采购单</a></p>
</div>

<?php
$js = <<<JS
$(function() {
    var in_go = 0;
    $(".lead-suggest-submit").click(function(){
        $(this).css("color","#CCC");
        if (in_go == 1) return false;
        in_go = 1;
    })
});
JS;
$this->registerJs($js);
?>
