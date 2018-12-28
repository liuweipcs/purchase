
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

        <?php foreach($arrive as $v){ ?>
            <tr>
            <td><?= $v['delivery_time']?></td>
            <td><?= $v['delivery_qty']?></td>
            <td><?= $v['delivery_user']?></td>
            <td><?= $v['check_type']?>
            </td>
            <td><?= $v['check_time']?></td>
            <td><?= $v['check_user']?></td>
            <td><?= $v['note']?></td>
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
        <?php foreach($stock as $w){ ?>
            <tr>
                <td><?= $w['instock_qty_count']?></td>
                <td><?= $w['instock_date']?></td>
                <td><?= $w['instock_user']?></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>