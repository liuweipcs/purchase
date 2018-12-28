<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
?>
<?php if(isset($print)): ?>

<table class="my-table2" style="width: 756px;">
    <tr>
        <th colspan="8" style="text-align: center;font-weight: bold;">付款申请书</th>
    </tr>
    <tr>
        <th colspan="2"><?= $model->fk_name ?></th>
        <th colspan="4"><?= $model->create_time ?></th>
        <th colspan="2">合同号: <?= $model->compact_number ?></th>
    </tr>
    <tr>
        <td width="60px" >收款单位</td>
        <td><?= $model->supplier_name ?></td>
        <td colspan="6" style="width: 40%;">付款原因</td>
    </tr>
    <tr>
        <td>账号</td>
        <td><?= $model->account ?></td>
        <td rowspan="5" colspan="6" style="vertical-align: top;">
            <?= $model->payment_reason ?>
        </td>
    </tr>
    <tr>
        <td>开户行</td>
        <td><?= $model->payment_platform_branch ?></td>
    </tr>
    <tr>
        <td>金额</td>
        <td><?= Vhelper::num_to_rmb($model->pay_price); ?></td>
    </tr>
    <tr>
        <td>附件</td>
        <td><?= $model->pay_price ?></td>
    </tr>
    <tr>
        <td>审批</td>
        <td>总经办</td>
    </tr>
</table>
<table class="my-table2" style="width: 756px;">
    <tr>
        <td>财务总监</td>
        <td style="width:15%;"></td>
        <td >记账</td>
        <td style="width:15%;"></td>
        <td >采购经理</td>
        <td style="width:15%;"></td>
        <td >制单</td>
        <td style="width:15%;"></td>
    </tr>
</table>

<?php

else:
$this->title = '请款申请书';
$this->params['breadcrumbs'][] = '采购单';
$this->params['breadcrumbs'][] = $this->title;

if(!empty($account)) {
    $payment_platform_branch = $account->payment_platform_branch ? $account->payment_platform_branch : '';
} else {
    $payment_platform_branch = '';
}

?>
<?php ActiveForm::begin(['id' => 'createCompace-form']); ?>

<div style="width: 756px;background-color: #fff;">

    <table class="table table-bordered" style="margin-bottom: 0;">
        <tr>
            <th colspan="8" style="text-align: center;font-weight: bold;">付款申请书</th>
        </tr>
        <tr>
            <th colspan="2"><?= $is_drawback == 2 ? '深圳市易佰网络科技有限公司' : 'YIBAI TECHNOLOGY LTD' ?></th>
            <th colspan="4"><?= date('Y年m月d日', time()) ?></th>
            <th colspan="2">合同号：<input type="text" name="Payment[compact_number]" value="<?= $model->pur_number ?>"></th>
        </tr>
        <tr>
            <td>收款单位</td>
            <td><input type="text" class="form-control" name="Payment[supplier_name]" value="<?= BaseServices::getSupplierName($model->supplier_code) ?>"></td>
            <td colspan="6">付款原因</td>
        </tr>
        <tr>
            <td>账号</td>
            <td><input type="text" class="form-control" name="Payment[account]" value="<?= !empty($account) ? $account->account : '' ?>"></td>
            <td rowspan="5" colspan="6" style="width: 50%;vertical-align: top;"><textarea name="Payment[payment_reason]" class="form-control" rows="6"><?= $pos ?></textarea></td>
        </tr>
        <tr>
            <td>开户行</td>
            <td><input type="text" class="form-control" name="Payment[payment_platform_branch]" value="<?= $payment_platform_branch ?>"></td>
        </tr>
        <tr>
            <td>金额</td>
            <td><?= Vhelper::num_to_rmb($model->pay_price); ?></td>
        </tr>
        <tr>
            <td>附件</td>
            <td><?= $model->pay_price ?><input type="hidden" name="Payment[pay_price]" value="<?= $model->pay_price ?>"></td>
        </tr>
        <tr>
            <td>审批</td>
            <td>总经办</td>
        </tr>
        <input type="hidden" name="Payment[tpl_id]" value="<?= $tpl_id ?>">
        <input type="hidden" name="Payment[pay_id]" value="<?= $model->id ?>">
        <input type="hidden" name="Payment[fk_name]" value="<?= $is_drawback == 2 ? '深圳市易佰网络科技有限公司' : 'YIBAI TECHNOLOGY LTD' ?>">
        <input type="hidden" name="Payment[create_time]" value="<?= date('Y-m-d H:i:s', time()) ?>">
    </table>
    <table class="table table-bordered">
        <tr>
            <td>财务总监</td>
            <td style="width: 113px;"></td>
            <td>记账</td>
            <td style="width: 113px;"></td>
            <td>采购经理</td>
            <td style="width: 113px;"></td>
            <td>制单</td>
            <td style="width: 113px;"></td>
        </tr>
    </table>

</div>

<div class="my-box">
    <button type="submit" class="btn btn-success">提交审核</button>
    <a href="javascript:void(0)" style="display: inline-block; margin-left: 25px;" onclick="javascript :history.back(-1)">返回上一步</a>
</div>

<?php ActiveForm::end(); ?>
<?php endif; ?>
