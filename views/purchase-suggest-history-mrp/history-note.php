<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/26
 * Time: 18:54
 */
?>
<table class="table">
    <thead>
        <th>sku</th>
        <th>仓库</th>
        <th>备注</th>
        <th>备注时间</th>
        <th>备注更新时间</th>
        <th>备注更新人</th>
    </thead>
    <tbody>
        <?php foreach ($noteDatas as $note){?>
            <tr>
                <td><?= $note['sku']?></td>
                <td><?php
                    $warehouseName= \app\models\Warehouse::find()
                        ->select('warehouse_name')
                        ->where(['warehouse_code'=>$note['warehouse_code']])
                        ->scalar();
                    echo  $warehouseName ? $warehouseName :'';
                    ?></td>
                <td><?= $note['suggest_note'];?></td>
                <td><?=$note['create_time'];?></td>
                <td><?=$note['update_time'];?></td>
                <td><?=$note['update_user_name'];?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
