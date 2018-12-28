
<div class="row">
    <h4 class="modal-title">到货记录</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>收货时间</th>
                <th>收货数量</th>
                <th>收货人员</th>
                <th>品检类型</th>
                <th>品检时间</th>
                <th>品检人员</th>
                <th>质检备注</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($model->arrival as $v){ ?>
            <tr>
            <td><?= $v->delivery_time?></td>
            <td><?= $v->delivery_qty?></td>
            <td><?= $v->delivery_user?></td>
            <td><?= $v->check_type?>
            </td>
            <td><?= $v->check_time?></td>
            <td><?= $v->check_user?></td>
            <td><?= $v->note?></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
    <h4 class="modal-title">入库记录</h4>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>上架数量</th>
            <th>入库时间</th>
            <th>入库人员</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($model->warehouseResults as $w){ ?>
            <tr>
                <td><?= $w->instock_qty_count?></td>
                <td><?= $w->instock_date?></td>
                <td><?= $w->instock_user?></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
    <h4 class="modal-title">质检异常记录</h4>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>质检数量</th>
            <th>异常创建时间</th>
            <th>处理备注</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($model->qc as $q){ ?>
            <tr>
                <td><?= $q->check_qty?></td>
                <td><?= $q->created_at?></td>
                <td><?= $q->note_handle?></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
    <h4 class="modal-title">收货异常记录</h4>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>异常类型</th>
            <th>是否收货</th>
            <th>处理备注</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($model->receive as $r){ ?>
            <tr>
                <td>
                    <?php
                        $arr = [1=>'收货收多',2=>'来货不足'];
                        echo isset($arr[$r->receive_type]) ? $arr[$r->receive_type] :'';
                    ?>
                </td>
                <td>
                    <?php
                        $arr2 = [1=>'收货',2=>'不收'];
                        echo isset($arr2[$r->is_receipt]) ? $arr2[$r->is_receipt] : '';
                    ?>
                </td>
                <td><?= $r->note_handle?></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>