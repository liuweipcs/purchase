<?php

use yii\helpers\Html;



$this->title = '收货异常处理';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<table class='table table-hover table-bordered table-striped' >
    <tr>
        <th>No.</th>
        <th>产品</th>
        <th>数量</th>
        <th>单价</th>
        <th>状态</th>
        <th>操作人员</th>
        <th>时间</th>
        <th>备注</th>
    </tr>
    <?php foreach ($data as $key=>$val):?>
    <tr>
        <td><?=$key+1?></td>
        <td>
            SKU:<?=$val['sku']?></br>
            名称:<?=$val['name']?>
        </td>
        <td>
            预期: <?=$val['qty']?></br>
            到货: <?=$val['delivery_qty']?></br>
            赠送: <?=$val['presented_qty']?></br>
            送检: <?=$val['check_qty']?></br>
            合格: <?=$val['good_products_qty']?></br>
            不合格: <?=$val['bad_products_qty']?></br>
        </td>
        <td><?=$val['price']?></td>
        <td>
            <?=Yii::$app->params['qc_status'][$val['qc_status']]?></br>
            方式:<?=Yii::$app->params['handle_type_qc'][$val['handle_type']]?>
        </td>
        <td>
            创建人: <?=$val['creator']?></br>
            处理人: <?=$val['handler']?></br>
            审核人: <?=$val['auditor']?>
        </td>
        <td>
            创建:<?=$val['created_at']?></br>
            处理:<?=$val['time_handle']?></br>
            审核:<?=$val['time_audit']?>
        </td>
        <td>
            备注:<?=$val['note']?></br>
            审核:<?=$val['note_audit']?>
        </td>
    </tr>
    <?php endforeach;?>
</table>