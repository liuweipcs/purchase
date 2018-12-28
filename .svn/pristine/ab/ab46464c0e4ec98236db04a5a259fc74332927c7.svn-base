<?php
$this->title = '模板列表';
$this->params['breadcrumbs'][] = '合同模板管理';
$this->params['breadcrumbs'][] = $this->title;

$state = $model->statusCss;
$platform = [
    '1' => '国内',
    '2' => '海外',
    '3' => 'FBA'
];
?>

<div class="my-box">

    <table class="my-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>名称</th>
            <th>类型</th>
            <th>状态</th>
            <th>平台</th>
            <th>样式码</th>
        </tr>
        </thead>


        <tbody>

        <?php foreach($tpls as $m): ?>
        <tr>
            <td><?= $m->id ?></td>
            <td><?= $m->name ?></td>
            <td><?= $model::getTplTypeName($m->type) ?></td>
            <td><?= $state[$m->status] ?></td>
            <td><?= $platform[$m->platform] ?></td>
            <td><?= $m->style_code ?></td>
        </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

</div>


