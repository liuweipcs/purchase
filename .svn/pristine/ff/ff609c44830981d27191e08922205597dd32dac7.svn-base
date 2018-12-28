<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
?>
<h4>开票详情</h4>
<div class="my-box">
    <table class="my-table">
        <thead>
            <tr>
                <th>开票数量</th>
                <th>开票单号</th>
                <th>开票时间</th>
                <th>开票品名</th>
                <th>开票单位</th>
                <th>票面总金额</th>
                <th>发票编码</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($poen_info as $k=>$v):?>
            <tr>
                <td><?=$v['tickets_number']?></td> <!-- 开票数量 -->
                <td><?php echo $v['open_number']?></td> <!-- 开票单号 -->
                <td><?=$v['open_time']?></td> <!-- 开票时间 -->
                <td><?=$v['ticket_name']?></td> <!-- 开票品名 -->
                <td><?=$v['issuing_office']?></td> <!-- 开票单位 -->
                <td><?=$v['total_par']?></td> <!-- 票面总金额 -->
                <td><?=$v['invoice_code']?></td> <!-- 发票编码 -->
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>