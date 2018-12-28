
<style>
    .detail-table {
        border: 1px solid #ccc;
        border-collapse: collapse;
        background-color: white;
    }
    .detail-table th,.detail-table td {
        text-align: left;
        border: 1px solid #ccc;
    }
    .detail-table .form {
        padding: 0 1% 0 1%
    }
</style>
<table class="detail-table table">
    <thead>
        <th>sku</th>
        <th>采购数量</th>
<!--        <th>执行标准</th>-->
<!--        <th>抽检数量</th>-->
        <th>不良品数量</th>
<!--        <th>合格质量标准</th>-->
        <th>客诉率</th>
        <th>客诉问题点</th>
    </thead>
    <tbody>
        <?php foreach ($data as $v ){?>
            <tr>
                <td><?= "<a target='_blank' href=".Yii::$app->params['SKU_ERP_Product_Detail'].$v['sku'].">".$v['sku']."</a>"?></td>
                <td><?= $v['purchase_num']?></td>
<!--                <td>--><?//= $v['check_standard']?><!--</td>-->
<!--                <td>--><?//= $v['check_num']?><!--</td>-->
                <td><?= $v['bad_goods']?></td>
<!--                <td>--><?//= $v['check_rate']?><!--</td>-->
                <td><?= $v['complaint_rate']?></td>
                <td><?= $v['complaint_point']?></td>
            </tr>
        <?php }?>
    </tbody>
</table>