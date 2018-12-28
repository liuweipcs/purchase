<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
?>
<h4>发货详情</h4>
<div class="my-box">
    <table class="my-table">
        <thead>
            <tr>
                <th>发货数量</th>
                <th>发货单号</th>
                <th>发货时间</th>
                <th>发货人</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($delivery_info as $k=>$v):if (abs($v['change_qty'])==0) continue;?>
            <tr>
                <td><?=abs($v['change_qty'])?></td> <!-- 发货数量 -->
                <td><?=$v['key_id']?></td> <!-- 发货单号 -->
                <td><?=$v['operate_time']?></td> <!-- 发货时间 -->
                <td><?=$v['operator']?></td> <!-- 发货人 -->
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>