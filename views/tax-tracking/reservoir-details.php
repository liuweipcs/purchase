<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
?>
<h4>库龄详情</h4>
<div class="my-box">
    <table class="my-table">
        <thead>
            <tr>
                <th>库存数量</th>
                <th>入库单号</th>
                <th>入库时间</th>
                <th>库龄</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reservoir_info as $k=>$v):?>
            <tr>
                <td><?=abs($v['change_qty'])?></td> <!-- 库存数量 -->
                <td><?=$v['key_id']?></td> <!-- 入库单号 -->
                <td><?=$v['operate_time']?></td> <!-- 入库时间 -->
                <td><?=$v['ku_age']?></td> <!-- 库龄 -->
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>