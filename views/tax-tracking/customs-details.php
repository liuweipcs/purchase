<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
?>
<h4>报关详情</h4>
<div class="my-box">
    <table class="my-table">
        <thead>
            <tr>
                <th>报关数量</th>
                <th>报关单号</th>
                <th>发货单号</th>
                <th>报关时间</th>
                <th>报关品名</th>
                <th>报关单位</th>
                <th>是否报关</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($customs_info as $k=>$v):?>
            <tr>
                <td><?=$v['amounts']?></td> <!-- 报关数量 -->
                <td><?=$v['custom_number']?></td> <!-- 报关单号 -->
                <td><?=$v['order_id']?></td> <!-- 发货单号 -->
                <td><?=$v['clear_time']?></td> <!-- 报关时间 -->
                <td><?=$v['declare_name']?></td> <!-- 报关品名 -->
                <td><?=$v['declare_unit']?></td> <!-- 报关单位 -->
                <td><?=!empty($v['is_clear']) ? (($v['is_clear']==1) ? '否':'是') : '未知'; ?></td> <!-- 是否报关 -->
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>