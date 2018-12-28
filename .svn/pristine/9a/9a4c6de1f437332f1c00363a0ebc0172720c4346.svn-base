<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/30
 * Time: 11:46
 */
?>

<table class="table table-bordered">


    <tr>
        <th>编号</th>
        <th>异常单号</th>
        <th>采购单号</th>
        <th>是否处理</th>
        <th>异常类型</th>
        <th>处理类型</th>
        <th>处理人</th>
        <th>是否推送至仓库</th>
        <th>推送结果</th>
        <th>处理</th>
    </tr>

    <?php foreach($data as $k => $v): ?>
    <tr>
        <td><?= $k+1 ?></td>
        <td><?= $v['defective_id'] ?></td>
        <td><?= $v['purchase_order_no'] ?></td>
        <td><?= $v['is_handler'] ?></td>
        <td><?= $v['abnormal_type'] ?></td>
        <td><?= $v['handler_type'] ?></td>
        <td><?= $v['handler_person'] ?></td>
        <td><?= $v['is_push_to_warehouse'] ?></td>
        <td><?= $v['warehouse_handler_result'] ?></td>
        <td><a href="complete?id=<?= $v['id'] ?>">标记完成</a></td>
    </tr>
    <?php endforeach; ?>

</table>
