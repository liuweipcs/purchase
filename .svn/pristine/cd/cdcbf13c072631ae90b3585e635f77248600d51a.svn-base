<?php
use yii\widgets\ActiveForm;
?>
<?php ActiveForm::begin(['id'=>'payment-form']);?>
<h5>财务审批</h5>
<div class="container-fluid">
    <?php echo $this->render('_public',['orderInfo' => $orderInfo, 'payInfo' => $payInfo]); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="processing_notice">审批备注</label>
                <textarea class="form-control" id="processing_notice" rows="3" name="processing_notice" placeholder="如果不填，默认备注为财务审批"></textarea>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <span id="reject" class="btn btn-success" data-payid="<?= $payInfo['id'] ?>">驳回</span>
            <span id="pass" class="btn btn-primary" data-payid="<?= $payInfo['id'] ?>">通过</span>
        </div>
    </div>
</div>
<?php
ActiveForm::end();
?>
<?php
$js = <<<JS
$(function() {
    // 驳回
    $('#reject').click(function() {
        var notice = $('#processing_notice').val();
        if($.trim(notice)=='') {
            layer.alert('驳回的请款单，必须输入备注。');
            return false;
        }
        var id = $(this).attr('data-payid');
        layer.load(0, {shade: false});
        $.ajax({
            url: '/purchase-order-pay-notification/finance-audit',
            data: {id: id, pay_status: 3, processing_notice: notice},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                location.reload();
            }
        });
    });
    // 通过
    $('#pass').click(function() {
        var notice = $('#processing_notice').val();
        var id = $(this).attr('data-payid');
        layer.load(0, {shade: false});
        $.ajax({
            url: '/purchase-order-pay-notification/finance-audit',
            data: {id: id, pay_status: 4, processing_notice: notice},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                location.reload();
            }
        });
    });
});
JS;
$this->registerJs($js);
?>

