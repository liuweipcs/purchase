<?php
$this->title = '海外仓-添加付款申请书';
$this->params['breadcrumbs'][] = '海外仓';
$this->params['breadcrumbs'][] = '请款单';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-success">
    <div class="box-header">付款申请书模板</div>
    <div class="box-body">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>名称</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach($tpls as $tpl): ?>
                <tr>
                    <td><a href="?compact_number=<?= $compact_number ?>&pid=<?= $pid ?>&tid=<?= $tpl->id ?>"><?= $tpl->name ?></a></td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>





