<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use app\models\User;
use kartik\tabs\TabsX;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\models\PurchaseRefunds;
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
?>
<div class="stockin-view">
    <?php $form = ActiveForm::begin(); ?>
    <input type="hidden" id="purchaseorder-purchas_status" class="form-control" name="PurchaseOrders[purchas_status]">
    <?php foreach ($model as $item) { ?>
        <div class="col-md-12" style="border: 1px solid red">
            <input type="hidden" id="purchaseorder-id" class="form-control" name="PurchaseOrder[id][]" value="<?= $item['id'] ?>">
            <div class="form-group field-purchaseorder-pur_number required col-md-4">
                <label class="control-label" for="purchaseorder-pur_number">采购单号</label>
                <input type="text" id="purchaseorder-pur_number" class="form-control" name="PurchaseOrder[pur_number]"
                       value="<?= $item['pur_number'] ?>" disabled="disabled" maxlength="20" aria-required="true">
                <div class="help-block"></div>
            </div>
            <div class="form-group field-purchaseorder-pur_number required col-md-4">
                <label class="control-label" for="purchaseorder-pur_number">备注</label>
                <textarea readonly>
                <?php if ($item['refund_status']==7) {
                    echo $item['confirm_note'];
                } else {
                    echo $item['arrival_note'];
                } ?>
                </textarea>

                <div class="help-block"></div>
            </div>

            <table class="table table-bordered">
                <?php foreach ($item['purchaseOrderItems'] as $sku) { ?>
                    <tr >
                        <td>产品代码：<?= $sku['sku'] ?></div>
                        <td >产品名称：<?= $sku['name'] ?></td>
                        <td >产品数量：<?= $sku['ctq'] ?></td>
                        <?php if (!empty($sku['cly'])) {
                            echo "<td style='color: red;'>取消数量：" . $sku['cly']  . "</td>";
                        }?>
                        <td >产品单价：<?= $sku['price'] ?></td>
                    </tr>
                <?php } ?>
            </table>
            <td >
                <label>退款金额</label>
                <label><?=PurchaseRefunds::getRefundsAmount($sku['pur_number']); ?></label>
            </td>
        </div>

    <?php } ?>
    <div class="form-group">
        <?= Html::submitButton('审核通过(Ok)',['class' => 'btn btn-success']) ?>
        <?= Html::submitButton('审核不通过(Rollback)', ['class' => 'btn btn-warning']) ?>

    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php


$js = <<<JS
$(function(){
    $(document).on('click', '.btn-success', function () {
        $('#purchaseorder-purchas_status').attr('value','3');
    });
     $(document).on('click', '.btn-warning', function () {
        $('#purchaseorder-purchas_status').attr('value','4');
    });
     $(document).on('click', '.btn-info', function () {
        $('#purchaseorder-purchas_status').attr('value','5');
    });

});


JS;
$this->registerJs($js);
?>
