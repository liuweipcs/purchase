<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/11
 * Time: 19:20
 */
?>
 <?php if(empty($excepInfo)){ Yii::$app->end('<div style="text-align: center">暂无退货数据</div>');}else{?>
<table class="table">
    <thead>
        <th>状态</th>
        <th>物流单号</th>
        <th>退货时间</th>
        <th>退货人</th>
    </thead>
    <tbody>
        <?php foreach ($excepInfo as $value){?>
            <td><?= $value->return_status ==4 ? '已处理' : '未知状态'?></td>
            <td><?php
                $url = 'https://www.kuaidi100.com/chaxun?com=&nu=' . $value->express_no;
                echo "<a target='_blank' href='{$url}'>".$value->express_no."</a></p>";
                ?></td>
            <td><?= $value->return_time?></td>
            <td><?= $value->return_user?></td>
        <?php } ?>
    </tbody>

</table>
<?php }?>