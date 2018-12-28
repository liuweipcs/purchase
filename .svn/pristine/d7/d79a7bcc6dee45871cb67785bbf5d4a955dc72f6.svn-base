<?php
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use yii\helpers\Html;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSub;
use app\models\WarehouseResults;
use app\models\PurchaseOrderItems;

$noPayMoney = $orderInfo['order_real_money']-$payInfo['hasPaidMoney']; // 订单未付款
$pur_number = $orderInfo['pur_number'];
?>
<?= $this->render('_overseas-public', ['orderInfo' => $orderInfo, 'payInfo' => $payInfo]); ?>

    <div class="row">
        <div class="col-md-12">
            <h5>订单取消明细</h5>
            <table class="table table-bordered table-condensed">
                <tr>
                    <th class="col-md-2">取消类型</th>
                    <td colspan="6">
                        <?php
                        $cancel_type = PurchaseOrderCancel::getCancelType($cancel_id);
                        echo PurchaseOrderServices::getCancelTypeCss($cancel_type);
                        ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-2">运费</th>
                    <td colspan="6"><?=PurchaseOrderCancel::getFreight($cancel_id) ?></td>
                </tr>
                <tr>
                    <th class="col-md-2">优惠额</th>
                    <td colspan="6"><?=PurchaseOrderCancel::getDiscount($cancel_id) ?></td>
                </tr>
                <tr>
                    <th class="col-md-2">SKU</th>
                    <th class="col-md-2">单价</th>
                    <th class="col-md-2">名称</th>
                    <th class="col-md-1">订单数量</th>
                    <th class="col-md-1">已取消数量</th>
                    <th class="col-md-1">入库数量</th>
                    <th class="col-md-2">取消数量</th>
                </tr>
                <?php 
                $itemsPriceInfo = PurchaseOrderItems::getItemsPrice($pur_number);
                foreach($orderInfo['purchaseOrderItems'] as $k => $v):  
                $cancel_ctq = PurchaseOrderCancelSub::getCancelCtqSku($cancel_id,$v['pur_number'],$v['sku']);
                if ($cancel_ctq<=0) {
                    continue;
                }

                foreach ($itemsPriceInfo as $iv) {
                    if ($v['pur_number']==$iv['pur_number'] && $v['sku']==$iv['sku'] ) {
                       $v['price'] = $iv['price'];
                    }
                }
                ?>
                    <tr>
                        <td><?= $v['sku'] ?></td>
                        <td><?= $v['price'] ?></td>
                        <td><?= $v['name'] ?></td>
                        <td><?= $v['ctq'] ?></td><!--订单数量-->
                        <td><?= $v['quxiao_num'] ?></td><!--已取消数量-->
                        <?php $v['ruku_num'] = WarehouseResults::getInstockInfo($v['pur_number'],$v['sku'])['instock_qty_count'] ?>
                        <td><?= $v['ruku_num'] ?></td><!--入库数量-->
                        <td>
                            <input type="number" class="form-control" value="<?=$cancel_ctq?>" readonly>
                        </td><!--取消数量-->
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th class="col-md-2" colspan="7" style="color: red">
                        <?php
                        $info = PurchaseOrderCancelSub::getCancelDetail($cancel_id);
                        echo "取消件数：{$info['cancel_ctq_total']}件<br />取消金额：{$info['cancel_price_total']}元";
                        ?>
                    </th>
                </tr>
                <tr>
                    <th class="col-md-2">备注</th>
                    <?php $buyer_note = PurchaseOrderCancel::getBuyerNote($cancel_id,$v['pur_number'],$v['sku']); ?>
                    <td colspan="6"><textarea class="form-control" readonly placeholder="<?=$buyer_note?>"></textarea></td>
                </tr>
                <tr>
                    <?php
                    $audit_status = PurchaseOrderCancel::getAuditStatus($cancel_id);
                    $audit_status = PurchaseOrderServices::getCancelAuditStatusCss($audit_status);
                    $audit_note = PurchaseOrderCancel::getAuditNote($cancel_id,$v['pur_number'],$v['sku']);
                    ?>
                    <th class="col-md-2" rowspan="2">审核结果</th>
                    <td><?=$audit_status ?></td>
                </tr>
                <tr>
                    <td colspan="6"><textarea class="form-control" readonly placeholder="<?=$audit_note?>"></textarea></td>
                </tr>
            </table>
        </div>
    </div>