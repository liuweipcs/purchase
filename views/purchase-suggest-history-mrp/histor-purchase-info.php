<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;

?>

<?php if(!empty($model) || !empty($model2)){ ?>
<div class="purchase-suggest-view" style="height: 650px; overflow-y: scroll; margin: 10px 0">
<?php if(!empty($model)){ ?>
<h4 class="modal-title">新系统采购信息</h4>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>供应商</th>
            <th>单价</th>
            <th>数量</th>
            <th>采购日期</th>
            <th>采购员</th>
        </tr>
        </thead>
    <?php foreach($model as $value){ ?>
        <tr class="table-module-b1">
            <td><?=!empty($value->purNumber) ? $value->purNumber->supplier_name : ''?></td>
            <td><?=$value->price?></td>
            <td><?=$value->ctq?></td>
            <td><?=!empty($value->purNumber) ? $value->purNumber->submit_time != '00-00-00 00:00:00' ? $value->purNumber->created_at : '' : ''?></td>
            <td><?=!empty($value->purNumber) ? $value->purNumber->buyer : ''?></td>
        </tr>
    <?php } ?>
    </table>
<?php } ?>

<?php if(!empty($model2)){ ?>
<h4 class="modal-title">通途采购信息</h4>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>供应商</th>
            <th>单价</th>
            <th>数量</th>
            <th>采购日期</th>
            <th>采购员</th>
        </tr>
        </thead>
        <?php foreach($model2 as $v){ ?>
            <tr class="table-module-b1">
                <td><?=$v['supplier_name']?></td>
                <td><?=$v['purchase_price']?></td>
                <td><?=$v['purchase_quantity']?></td>
                <td><?=$v['purchase_time']!='0000-00-00 00:00:00' ? $v['purchase_time'] : ''?></td>
                <td><?=$v['buyer']?></td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>
<?php }else{ ?>
    <div style="height: 200px; text-align: center">没有数据</div>
<?php } ?>
