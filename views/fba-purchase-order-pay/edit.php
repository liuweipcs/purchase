<?php
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;

?>
<?php ActiveForm::begin(['id' => 'edit-form', 'options' => ['class' => 'form-horizontal']]);?>
<div class="container-fluid">
    <h5>编辑请款单</h5>
    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                <tr>
                    <th class="col-md-3">订单号</th>
                    <td><?= $payInfo['pur_number'] ?></td>
                </tr>
                <tr>
                    <th class="col-md-3">申请号</th>
                    <td><?= $payInfo['requisition_number'] ?></td>
                </tr>
                <tr>
                    <th class="col-md-3">状态</th>
                    <td><?= PurchaseOrderServices::getPayStatusType($payInfo['pay_status']) ?></td>
                </tr>
                <tr>
                    <th class="col-md-3">币种</th>
                    <td><?= $payInfo['currency'] ?></td>
                </tr>
                <tr>
                    <th class="col-md-3">请款类型</th>
                    <td><?= PurchaseOrderServices::getRequestPayoutType($orderInfo['rpt']) ?></td>
                </tr>
                <tr>
                    <th class="col-md-3">申请时间</th>
                    <td><?= $payInfo['application_time'] ?></td>
                </tr>
                <tr>
                    <th class="col-md-3">驳回人</th>
                    <td>
                        <?php
                        if(in_array($payInfo['pay_status'], [3])) {
                            echo !empty($payInfo['approver']) ? BaseServices::getEveryOne($payInfo['approver']) : '';
                        } elseif(in_array($payInfo['pay_status'], [12])) {
                            echo !empty($payInfo['payer']) ? BaseServices::getEveryOne($payInfo['payer']) : '';
                        } else {
                            echo !empty($payInfo['auditor']) ? BaseServices::getEveryOne($payInfo['auditor']) : '';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-3">驳回时间</th>
                    <td>
                        <?php
                        if(in_array($payInfo['pay_status'], [3])) {
                            echo $payInfo['processing_time'];
                        } elseif(in_array($payInfo['pay_status'], [12])) {
                            echo $payInfo['payer_time'];
                        } else {
                            echo $payInfo['review_time'];
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-2">驳回原因</th>
                    <td>
                        <?php
                            if(in_array($payInfo['pay_status'], [3, 12])) {
                                echo $payInfo['payment_notice'];
                            } else {
                                echo $payInfo['review_notice'];
                            }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="pay_price">请款金额</label>
                <input type="text" class="form-control" name="pay_price" id="pay_price" value="<?= $payInfo['pay_price'] ?>" disabled>
            </div>
            <div class="form-group">
                <label for="freight">运费</label>
                <input type="text" class="form-control" name="freight" id="freight" value="<?= $payInfo['freight'] ?>">
            </div>
            <div class="form-group">
                <label for="discount">优惠额</label>
                <input type="text" class="form-control" name="discount" id="discount" value="<?= $payInfo['discount'] ?>">
            </div>
            <div class="form-group">
                <label for="order_number">拍单号</label>
                <input type="text" class="form-control" name="order_number" id="order_number" value="<?= $payInfo['order_number'] ?>">
            </div>
            <div class="form-group">
                <label for="create_notice">申请备注</label>
                <textarea class="form-control" rows="3" name="create_notice" id="create_notice" value="<?= $payInfo['create_notice'] ?>"></textarea>
            </div>
            <div class="form-group">
                <a href="javascript: void(0)" id="btn-submit" class="btn btn-default">提交</a>
            </div>
        </div>
    </div>

</div>

<input type="hidden" name="id" value="<?= $payInfo['id'] ?>">
<input type="hidden" name="pur_number" value="<?= $payInfo['pur_number'] ?>">
<input type="hidden" name="requisition_number" value="<?= $payInfo['requisition_number'] ?>">

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$(function() {
    
    var flag1 = flag2 = true;
    
    function checkMoney(v) {
        if(v == '') {
            return true;
        }
        var temp = /^\d+\.?\d{0,3}$/;
        if(temp.test(v)) {
            return true;
        } else {
            return false;
        }
    }

    $('#freight').change(function() {
        var res = checkMoney($(this).val());
        if(res == false) {
            layer.tips('请输入正确金额，只能包含数字字符，且保留小数点后三位', $(this), {
                tips: [1, '#3595CC'],
                time: 4000
            });
            flag1 = false;
        } else {
            flag1 = true;
        }
    });
    
    $('#discount').change(function() {
        var res = checkMoney($(this).val());
        if(res == false) {
            layer.tips('请输入正确金额，只能包含数字字符，且保留小数点后两位', $(this), {
                tips: [1, '#3595CC'],
                time: 4000
            });
            flag2 = false;
        } else {
            flag2 = true;
        }
    });
    
    // 提交验证
    $('#btn-submit').click(function() {
        if(flag1 && flag2) {
            $('#edit-form').submit();
        } else {
            layer.alert('你有金额填写错误');
        }
    });
    
});
JS;
$this->registerJs($js);
?>
