<?php
$this->title = '系统错误提示';
$this->params['breadcrumbs'][] = $this->title;
?>

<table class="my-table">
    <colgroup>
        <col class="col-md-1">
        <col class="col-md-11">
    </colgroup>
    <thead>
    <tr>
        <th>单号</th>
        <th>错误信息</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach($errors as $key => $val): ?>

        <?php foreach($val as $k => $v): ?>

        <tr>

        <?php if($k == 0): ?>

        <td rowspan="<?= count($val) ?>" style="vertical-align: middle;text-align: center;"><?= $key ?></td>

        <?php endif; ?>

        <td><?= $v ?></td>

        </tr>

        <?php endforeach; ?>

    <?php endforeach; ?>
    </tbody>

</table>
