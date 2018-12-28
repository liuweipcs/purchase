<?php
$this->title = '采购合同-申请付款';
$this->params['breadcrumbs'][] = '海外仓';
$this->params['breadcrumbs'][] = '采购合同';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="my-box" style="margin-bottom: 45px;">
    <div class="bg-line no">
        <span>1</span>
        <p>确认采购单信息</p>
    </div>
    <div class="bg-line">
        <span>2</span>
        <p>确认合同信息</p>
    </div>
</div>
<div class="my-box">

    <table class="my-table">
        <thead>
        <tr>
            <th>名称</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach($tpls as $tpl): ?>
            <tr>
                <td><a href="?compact_number=<?= $compact_number ?>&pid=<?= $pid ?>&tid=<?= $tpl->id ?>"><?php $tpl->name ?>付款申请书-通用版</a></td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
</div>





