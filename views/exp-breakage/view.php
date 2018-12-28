<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
$img = Html::img(Vhelper::downloadImg($mod->sku, $img, 2), ['width' => '110px', 'class' => 'img-thumbnail']);
?>


<h5>报损信息-经理审核</h5>
<div class="my-box">
    <table class="my-table">
        <thead>
        <tr>
            <th>图片</th>
            <th>sku</th>
            <th>名称</th>
            <th>单价</th>
            <th>订单数量</th>
            <th>入库数量</th>
            <th>取消数量</th>
            <th>报损数量</th>
            <th>报损金额</th>
        </tr>
        </thead>
        <tbody>

        <tr>
            <td><?= $img ?></td>
            <td><?= $mod->sku ?></td>
            <td><?= $mod->name ?></td>
            <td><?= $mod->price ?></td>
            <td><?= $mod->ctq ?></td>
            <td><?= $mod->qty ?></td>
            <td><?= \app\models\PurchaseOrderCancelSub::getCancelCtq($mod->pur_number,$mod->sku); ?></td>
            <td><?= $mod->breakage_num ?></td>

            <td><?= $mod->items_totalprice ?></td>

        </tr>

        </tbody>
    </table>


</div>

<?php ActiveForm::begin(['id' => 'fm']); ?>

<input type="hidden" name="id" value="<?= $mod->id ?>">

<div class="my-box">

    <div class="fg">
        <label>采购员申请备注</label>
        <p><?= $mod->apply_notice ?></p>
    </div>

    <div class="fg">
        <label>审核备注</label>
        <textarea name="audit_notice" rows="3" cols="80"></textarea>
    </div>


    <div class="fg">
        <label></label>
        <input type="radio" name="status" value="1" checked> 通过
        <input type="radio" name="status" value="2"> 不通过
    </div>


    <div class="fg">
        <label></label>
        <input type="submit" value="提交">
    </div>

</div>

<?php
ActiveForm::end();
?>
