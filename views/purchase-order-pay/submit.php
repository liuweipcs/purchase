<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;

?>
<?php ActiveForm::begin(['id'=>'payment-form']);?>
<h5>提交请款单</h5>
<div class="container-fluid">
    <?php echo $this->render('_public',['orderInfo' => $orderInfo, 'payInfo' => $payInfo]); ?>
    <div class="row">
        <div class="col-md-12">
            <span id="submit" class="btn btn-primary" data-payid="<?= $payInfo['id'] ?>">提交</span>
            <ins>请款单提交后，将变为待经理审核。</ins>
        </div>
    </div>
</div>
<?php
ActiveForm::end();
?>
<?php
$js=<<<JS
$(function() {
    $('#submit').click(function() {
        var index = layer.load(0,{shade:false});
        var id = $(this).attr('data-payid');
        $.ajax({
            url: '/purchase-order-pay/submit',
            data: {id:id},
            type: 'post',
            dataType: 'json',
            success:function(data) {
                if(data.error == 0) {
                    location.reload();
                } else {
                    layer.close(index);
                    layer.alert('对不起，出错啦');
                }
            }
        });
    });
});
JS;
$this->registerJs($js);
?>
