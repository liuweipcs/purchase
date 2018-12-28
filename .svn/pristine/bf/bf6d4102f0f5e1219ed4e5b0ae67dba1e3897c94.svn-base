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
<h5>经理审核</h5>
<div class="container-fluid">
    <?= $this->render('_public',['orderInfo' => $orderInfo, 'payInfo' => $payInfo]); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="review_notice">请款备注</label>
                <textarea class="form-control" id="review_notice" rows="3" name="review_notice" placeholder="如果不填，默认备注为经理审核"></textarea>
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
$(function(){
    $('#reject').click(function() {
        var notice = $('textarea[name="review_notice"]').val();
        if($.trim(notice) == '') {
            layer.alert('驳回的请款单，必须输入备注。');
            return false;
        }
        var id = $(this).attr('data-payid');
        $.ajax({
            url: '/fba-purchase-order-pay/audit',
            data: {id:id,pay_status:11, notice: notice},
            type: 'post',
            dataType: 'json',
            success:function(data) {
                layer.load(0, {shade: false});
                location.reload();
            }
        });
    });
    $('#pass').click(function() {
        var notice = $('#review_notice').val();
        var id = $(this).attr('data-payid');
        $.ajax({
            url: '/fba-purchase-order-pay/audit',
            data: {id:id, pay_status:2, notice:notice},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                layer.load(0, {shade: false});
                location.reload();
            }
        });
    });
});
JS;
$this->registerJs($js);
?>
