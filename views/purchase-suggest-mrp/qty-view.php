<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\config\Vhelper;
use app\services\BaseServices;
use app\services\PurchaseSuggestQuantityServices;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>
<!--<style type="text/css">
    .img-rounded{width: 60px; height: 60px; !important;}
    .floors{max-height: 750px; overflow-y: scroll}
    .modal-lg{width: 90%; !important;}
</style>-->
<h4 class="modal-title">采购单产品信息</h4>
<div class="row floors">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>SKU</th>
            <th>平台号</th>
            <th>采购数量</th>
            <th>导入数量</th>
            <th>活动备货</th>
            <th>常规备货</th>
            <th>采购仓</th>
            <th>创建人</th>
            <th>创建时间</th>
            <th>销售备注</th>
            <th>采购建议状态</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?=$model_suggest['sku'];?></td>
            <td></td><!--平台号-->
            <td><?=$model_suggest['qty'];?></td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td><?=$model_suggest['warehouse_name'];?></td>
            <td><?=$model_suggest['creator'];?></td>
            <td><?=$model_suggest['created_at'];?></td>
            <td></td><!--销售备注-->
            <td></td><!--采购建议状态-->
        </tr>
        <?php
            if (empty($model)) return;
            foreach ($model as $k=>$v) {
        ?>
        <tr>
            <td><?=$v['sku'];?></td>
            <td><?=$v['platform_number'];?></td>
            <td><?=$v['purchase_quantity']+$v['activity_stock']+$v['routine_stock'];?></td>
            <td><?=$v['purchase_quantity'];?></td>
            <td><?=$v['activity_stock'];?></td>
            <td><?=$v['routine_stock'];?></td>
            <td><?=BaseServices::getWarehouseCode($v['purchase_warehouse']);?></td>
            <td><?=$v['create_id'];?></td>
            <td><?=$v['create_time'];?></td>
            <td><?=$v['sales_note'];?></td>
            <td><?=PurchaseSuggestQuantityServices::getSuggestStatus($v['suggest_status']);?></td>
        </tr>
        <?php }?>
        </tbody>
    </table>
</div>