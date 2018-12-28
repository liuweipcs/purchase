<?php

use app\services\PurchaseOrderServices;

$type = [
    '0' => '<span class="label label-default">未推送</span>',
    '1' => '<span class="label label-success">处理结果已推送至仓库系统</span>',
    '2' => '<span class="label label-success">仓库已经处理</span>',
];

function getHandlerType($type = null)
{
    $types = [
        '1'=>'退货',
        '2'=>'次品退货',
        '3'=>'次品转正',
        '4'=>'正常入库',
        '5'=>'不做处理',
        '6'=>'优品入库',
        '7'=>'整批退货',
        '8'=>'二次包装',
        '9'=>'正常入库',
        '10'=>'不做处理',
    ];
    if(!$type) {
        return '未处理';
    } else {
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }
}

?>
<div class="container-fluid" style="border:1px solid #ccc;">
    <div class="row" style="margin:0;">
        <h5>异常信息</h5>
        <div class="col-md-12">
            <table class="table table-bordered">
                <tr>
                    <th class="col-md-2">异常单号</th>
                    <td><?= $model->defective_id ?></td>
                </tr>
                <tr>
                    <th>异常货位</th>
                    <td><?= $model->position ?></td>
                </tr>
                <tr>
                    <th>采购单号</th>
                    <td><?= $model->purchase_order_no ?></td>
                </tr>
                <tr>
                    <th>快递单号</th>
                    <td><?= $model->express_code ?></td>
                </tr>
                <tr>
                    <th>异常原因</th>
                    <td><?= $model->abnormal_depict ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row" style="margin:0;">
        <h5>异常处理信息</h5>
        <div class="col-md-12">
            <table class="table table-bordered">
                <tr>
                    <th class="col-md-2">处理人</th>
                    <td><?= $model->handler_person ?></td>
                </tr>
                <tr>
                    <th>处理类型</th>
                    <td><span class="label label-info"><?= getHandlerType($model->handler_type) ?></span></td>
                </tr>
                <tr>
                    <th>采购单号</th>
                    <td><?= $model->purchase_order_no ?></td>
                </tr>
                <tr>
                    <th>采购处理时间</th>
                    <td><?= $model->handler_time ?></td>
                </tr>
                <tr>
                    <th>采购处理描述</th>
                    <td><?= $model->handler_describe ?></td>
                </tr>
                <tr>
                    <th>是否推送至仓库</th>
                    <td><?= $type[$model->is_push_to_warehouse] ?></td>
                </tr>
            </table>
        </div>
    </div>

</div>
