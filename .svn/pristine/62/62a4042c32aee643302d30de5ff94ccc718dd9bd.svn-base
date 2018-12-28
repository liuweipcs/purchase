<?php
use yii\widgets\ActiveForm;
?>
<style>
    em {
        color: #e4393c;
        font-weight: 700;
        font-style: normal;
    }
</style>
<?php ActiveForm::begin(['id' => 'approval-form']);?>
<table class="table table-bordered table-striped">
    <thead>
    <th>#</th>
    <th>订单号</th>
    <th>申请号</th>
    <th>金额</th>
    <th>申请时间</th>
    </thead>
    <tbody>
    <?php foreach($models as $k=>$m): ?>
        <tr>
            <td><?= $k+1; ?></td>
            <td><?= $m->pur_number ?></td>
            <td><?= $m->requisition_number ?></td>
            <td><em><?= \app\models\PurchaseOrderPay::getPrice($m,true); ?></em></td>
            <td><?= $m->application_time ?></td>
        </tr>
        <input type="hidden" name="ids[]" value="<?= $m->id ?>">
        <input type="hidden" name="pur_number[]" value="<?= $m->pur_number ?>">
    <?php endforeach; ?>
    </tbody>

    <tfoot>
    <tr>
        <td colspan="5">
            <div class="form-group">
                <label>请输入备注</label>
                <textarea class="form-control" rows="3" name="review_notice"></textarea>
            </div>
            <input type="submit" value="通过" class="btn btn-info">
        </td>
    </tr>
    </tfoot>
</table>
<?php ActiveForm::end(); ?>
