<?php

use yii\helpers\Html;



$this->title = '收货异常审核';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<table class='table table-hover table-bordered table-striped' >
    <tr>
        <th>No.</th>
        <th>产品</th>
        <th>数量</th>
        <th>单价</th>
        <th>处理</th>
        <th>操作人员</th>
        <th>时间</th>
        <th>留言</th>
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
            赠送: <?=$val['presented_qty']?>
        </td>
        <td><?=$val['price']?></td>
        <td>
            状态:<?=Yii::$app->params['receive_status'][$val['receive_status']]?></br>
            方式:<?=Yii::$app->params['handle_type'][$val['handle_type']]?>
        </td>
        <td>
            创建人: <?=$val['creator']?></br>
            处理人: <?=$val['handler']?></br>
            审核人: <?=$val['auditor']?>
        </td>
        <td>
            创建时间: <?=$val['created_at']?></br>
            处理时间: <?=$val['time_handle']?></br>
            审核时间: <?=$val['time_audit']?>
        </td>
        <td>
            处理留言:<?=$val['note_handle']?></br>
            审核留言:<?=$val['note_audit']?>
        </td>
    </tr>
    <?php endforeach;?>
</table>