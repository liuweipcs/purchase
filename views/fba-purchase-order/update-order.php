<?php
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
$is_update = []; // 是否可以修改
$urlPath = Yii::$app->request->hostInfo;

if(empty($model)) {
    $freight = '';
    $discount = '';
    $account = '';
    $order_number = '';
} else {
    $freight = $model->freight;
    $discount = $model->discount;
    $account = $model->purchase_acccount;
    $order_number = $model->platform_order_number;
}

?>
<div class="my-box">

    <div class="fg" style="border: 1px solid red;">
        <h4>系统提示 > <small> 满足以下条件的订单才可以修改：</small></h4>
        <p><u>1. 没有请款记录的订单。</u></p>
        <p><u>2. 有请款记录，但是请款单被驳回的订单。</u></p>
        <h4>系统警告 > <small> 你的每一次修改将会被记录下来，请谨慎操作！！！</small></h4>
    </div>

    <h4>订单支付信息</h4>
    <table class="my-table">
        <thead>
        <tr>
            <th>订单号</th>
            <th>金额</th>
            <th>申请人</th>
            <th>申请时间</th>
            <th>状态</th>
            <th>#</th>
        </tr>
        </thead>
        <tbody>

        <?php if(empty($paylist)): ?>

            <tr>
                <td colspan="6" style="text-align: center;color: red;">订单目前还没有支付信息</td>
            </tr>

        <?php else: ?>

        <?php
            foreach($paylist as $mod):
                if(in_array($mod->pay_status, [3, 11, 12])) {
                    $is_update[] = 1;
                } else {
                    $is_update[] = 0;
                }

        ?>

        <tr>
            <td><?= $mod->pur_number ?></td>
            <td><?= $mod->pay_price ?></td>
            <td><?= BaseServices::getEveryOne($mod->applicant) ?></td>
            <td><?= $mod->application_time ?></td>
            <td><?= PurchaseOrderServices::getPayStatusType($mod->pay_status) ?></td>
            <td><a href="<?= $urlPath ?>/fba-purchase-order-pay/index?pur_number=<?= $mod->pur_number ?>" target="_blank">查看</a></td>
        </tr>
        <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>
</div>

<?php $form = ActiveForm::begin([]) ?>

<div class="my-box">
    <h4>修改订单信息 > <small style="color: red;"><?= $pur_number ?></small></h4>

    <div class="fg">
        <label>运费：</label>
        <input type="text" name="new[freight]" value="<?= $freight ?>">
    </div>
    <div class="fg">
        <label>优惠额：</label>
        <input type="text" name="new[discount]" value="<?= $discount ?>">
    </div>
    <div class="fg">
        <label>账号：</label>
        <select name="new[purchase_acccount]">
            <option value="0">请选择...</option>
            <?php
            $accountes = BaseServices::getAlibaba();
            foreach($accountes as $k => $v):
                if($account == $v):
                    ?>
                    <option value="<?= $k ?>" selected><?= $v ?></option>
                <?php else: ?>
                    <option value="<?= $k ?>"><?= $v ?></option>
                <?php endif; endforeach; ?>
        </select>
    </div>
    <div class="fg">
        <label>拍单号：</label>
        <input type="text" name="new[platform_order_number]" value="<?= $order_number ?>" style="width:200px">
    </div>
    <div class="fg">
        <label>备注：</label>
        <textarea name="new[note]" cols="100" rows="4"></textarea>
    </div>

    <input type="hidden" name="pur_number" value="<?= $pur_number ?>">


    <?php if(in_array(0, $is_update)): ?>

    <div class="fg">
        <p style="color: red;font-size: 16px;">（系统检测到，你的订单不满足可修改条件。）</p>
    </div>

    <?php else: ?>

    <div class="fg">
        <label></label>
        <input type="submit" value="提交">
    </div>

    <?php endif; ?>

</div>


<?php ActiveForm::end() ?>



