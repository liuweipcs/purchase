<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\bootstrap\Modal;
use app\services\BaseServices;
use app\models\ProductTaxRate;
?>

<div class="my-box">
    <a href="/purchase-compact/view?cpn=<?= $model->pur_number ?>" target="_blank" class="btn btn-info">查看采购订单合同</a>
    <a href="/purchase-compact/show-form?id=<?= $model->id ?>" id="sf" data-toggle = 'modal' data-target = '#created-modal' class="btn btn-info">查看付款申请书</a>
</div>

<?= $this->render('_compact_public', ['data' => $data, 'model' => $model, 'compact' => $compact]); ?>


<?php

Modal::begin([
    'id' => 'created-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗
        'z-index' =>'-1',

    ],
]);
Modal::end();
$js = <<<JS
$(function() {
    
    $('#sf').click(function () {
        $.get($(this).attr('href'),
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    
    
});


JS;
$this->registerJs($js);
?>




