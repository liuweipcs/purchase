<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;

?>
<h2><?=$sku?></h2>
<?php if(!empty($model) || !empty($model2)){ ?>
<div class="purchase-suggest-view" style="height: 650px; overflow-y: scroll; margin: 10px 0">
<?php if(!empty($model)){ ?>
<h4 class="modal-title">新系统采购信息</h4>
    <table class="table table-bordered">
        <thead>
        <tr>
            <?php if($role == 'purchase' && $is_visible == 1) {?>
            <th>供应商</th>
            <?php }?>
            <th>采购单号</th>
            <th>单价</th>
            <th>采购数量</th>
            <th>入库数量</th>
            <th>采购日期</th>
            <th>采购员</th>
        </tr>
        </thead>
    <?php foreach($model as $value){ ?>
        <tr class="table-module-b1">
        <?php if($role == 'purchase' && $is_visible == 1) {?>
            <td><?=$value['supplier_name']?></td>
        <?php }?>

            <td><?=$value['pur_number']?></td>
            <td><?=$value['price']?></td>
            <td><?=$value['ctq']?></td>
            <td><?=$value['cty']?></td>
            <td><?=$value['audit_time']?></td>
            <td><?=$value['buyer']?></td>
        </tr>
    <?php } ?>
    </table>
<?php } ?>

<?php if(!empty($model2)){ ?>
<h4 class="modal-title">通途采购信息</h4>
    <table class="table table-bordered">
        <thead>
        <tr>
    <?php if($role == 'purchase') {?>
            <th>供应商</th>
    <?php }?>

            <th>采购单号</th>
            <th>单价</th>
            <th>采购数量</th>
            <th>入库数量</th>
            <th>采购日期</th>
            <th>采购员</th>
        </tr>
        </thead>
        <?php foreach($model2 as $v){ ?>
            <tr class="table-module-b1">
                <?php if($role == 'purchase') {?>
                <td><?=$v['supplier_name']?></td>
                <?php }?>

                <td><?=$v['pur_number']?></td>
                <td><?=$v['purchase_price']?></td>
                <td><?=$v['purchase_quantity']?></td>
                <td><?=$v['actual_storage_quantity']?></td>
                <td><?=$v['purchase_time']!='0000-00-00 00:00:00' ? $v['purchase_time'] : ''?></td>
                <td><?=$v['buyer']?></td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>
<?php }else{ ?>
    <div style="height: 200px; text-align: center">没有数据</div>
<?php } ?>
