<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\config\Vhelper;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
    body {
        font: 12px/150% Arial, Helvetica, sans-serif, '宋体';
    }
    #print_content {
        background: none repeat scroll 0 0 white;
        margin-left: auto;
        margin-right: auto;
        position: relative;
        text-align: center;
        width: 21cm;
        padding: 0px;
        font-size: 13px;
    }

    #print_content table {
        border-collapse: collapse;
        border: none;
        width: 100%;
    }

    .list td, .list th {
        border: solid #000000 1px;
        height: 27px;
    }

    .print-header{
        border:0;
    }

</style>
<!--  -->

<div id="print_content">
    <?php foreach($ordersitmes as $v){

        ?>
    <div style="page-break-after:always;clear:both;padding-top: 20px;">
        <div style="text-align:left;margin-top: 30px;">
            <table border="0" class="print-header">
                <tbody>
                <tr>
                    <td style="text-align:left;width:20%"><img src="<?=Url::toRoute(['code','codes'=>$v['supplier_code']])?>"></td>
                    <td style="text-align:center;width:48%"><h1>采购单</h1><br>日期：<?=$v['created_at']?><br>采购员：<?=$v['buyer']?>
                    </td>
                    <td style="text-align:right;vertical-align:bottom; ">供应商：<?= $v['supplier']['supplier_name']?><br>地址：<?= \app\models\SupplierContactInformation::getAddrss($v['supplier']['id'])?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div style="clear:both;">
            <table class="list">
                <tr>
                    <th width="13%">产品图片</th>
                    <th width="10%">SKU</th>
                    <th width="20%">产品名称</th>
                    <th width="10%">数量</th>
                    <th width="5%">单价</th>
                    <th width="5%">是否缺货</th>
                    <th width="5%">缺货天数</th>
                    <th width="5%">备注</th>
                </tr>
                <?php
                $purchase_price = 0;
                foreach($v['purchaseOrderItems'] as $k=>$c){

                    $purchase_price += $c['qty'] * $c['price'];

                    ?>
                <tr>
                    <td>
                        <?= Vhelper::toSkuImg($c['sku'],$c['product_img']);?>
                    </td>
                    <td><?=$c['sku']?></td>
                    <td><?=$c['name']?></td>
                    <td><?=$c['qty']?></td>
                    <td><?=$c['price'].'&nbsp;&nbsp;'.$v->currency_code?></td>
                    <td><!-- 否 --></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php }?>
                <tr>
                    <td>产品种数</td>
                    <td colspan='1'><?=count($v['purchaseOrderItems'])?></td>
                    <td>总采购数量</td>
                    <td colspan='1'><?=count($v['purchaseOrderItems'])?></td>
                    <td>总应付金额</td>
                    <td colspan='1'><?=number_format($purchase_price,3).'&nbsp;&nbsp;'.$v->currency_code?></td>
                    <td><strong>备注</strong></td>
                    <td colspan='3'><b></b></td>
                </tr>
            </table>
        </div>
    </div>
    <?php }?>
</div>

