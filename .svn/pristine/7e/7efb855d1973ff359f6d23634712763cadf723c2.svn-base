<?php
use yii\widgets\LinkPager;
use app\models\AlibabaZzh;
$this->title = '1688子账户';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="box box-success">
    <div class="box-body">
        <?= $this->render('_search', ['model' => $model]); ?>
    </div>
</div>


<div class="box box-info">

    <div class="box-header with-border">
        <a class="btn btn-success" href="add">添加子账号</a>

        <div class="box-tools pull-right">

            <?php
                $offset1 = $pagination->offset;
                $offset2 = $pagination->offset+$pagination->limit;
            ?>

            <div class="summary" style="padding: 10px 0px;">第<b><?= $offset1.'-'.$offset2 ?></b>条，共<b><?= $pagination->totalCount ?></b>条数据.</div>

        </div>

    </div>

    <div class="box-body">


<table class="table table-bordered table-hover" style="margin-top: 10px;">
    <thead>
        <tr>
            <th>ID</th>
            <th>账号</th>
            <th>使用者</th>
            <th>付款人</th>
            <th>状态</th>
            <th>级别</th>
            <th>操作</th>
        </tr>
    </thead>

    <tbody>

    <?php foreach($data as $k => $v): ?>

    <tr>

        <td><?= $v['id'] ?></td>
        <td><?= $v['account'] ?></td>
        <td><?= $v['username'] ?></td>

        <td>
            <?php

            if(!empty(AlibabaZzh::$payer)) {
                echo $v['pid'] == 0 ? '我是付款人' : AlibabaZzh::$payer[$v['pid']];
            } else {
                $payer = AlibabaZzh::getPayer();
                echo $v['pid'] == 0 ? '我是付款人' : $payer[$v['pid']];
            }

            ?>
        </td>

        <td>
            <?= \app\models\AlibabaZzh::$status_to_txt[$v['status']] ?>
        </td>

        <td>
            <?= \app\models\AlibabaZzh::$level_to_txt[$v['level']] ?>
        </td>

        <td><a href="update?id=<?= $v['id'] ?>">修改</a> | <a href="javascript:void(0)" onclick="del(<?= $v['id'] ?>)">删除</a></td>

    </tr>

    <?php endforeach; ?>

    </tbody>

</table>

    </div>

    <div class="box-footer">
        <?php echo LinkPager::widget([
            'pagination' => $pagination,
            'firstPageLabel' => '首页',
            'prevPageLabel' => '上一页',
            'nextPageLabel' => '下一页',
            'lastPageLabel' => '末页',
        ]);?>
    </div>

</div>

    <script>
        function del(id)
        {
            layer.confirm('确定要删除吗？', function(r) {
               if(r) {
                   location.href = '/alibaba-zzh/delete?id='+id;
               }
            });
        }
    </script>
