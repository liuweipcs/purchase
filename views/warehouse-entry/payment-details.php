<?php
/** 
 * 入库明细表
 */

use yii\widgets\LinkPager;
// $this->title = '1688子账户';
// $this->params['breadcrumbs'][] = $this->title;
// vd($data);
?>





<div class="box box-info">

    <div class="box-header with-border">

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
            <th>合同号</th>
            <th>采购单号</th>
            <th>sku</th>
            <th>商品名称</th>
            <th>采购类型</th>
            <th>供应商名称</th>
            <th>采购员</th>
            <th>sku采购数量</th>
            <th>sku采购单价</th>
            <th>sku采购金额</th>
            <th>到货数量</th>
            <th>采购日期</th>
            <th>付款日期</th>
            <th>采购状态</th>
            <th>备注</th>
        </tr>
    </thead>
    <?php foreach($data as $k => $v):?>
        <tr>
            <td><?=$v['compact_number'] ?></td> 
            <td><?=$v['pur_number'] ?></td> 
            <td><?=$v['sku'] ?></td>                <!-- sku -->
            <td><?=$v['name'] ?></td>               <!-- 商品名称 -->
            <td><?=$v['purchase_type'] ?></td>      <!-- 采购类型 -->
            <td><?=$v['supplier_name'] ?></td>      <!-- 供应商名称 -->
            <td><?=$v['buyer'] ?></td>      <!-- 采购员 -->
            <td><?=$v['ctq'] ?></td>       <!-- sku采购数量 -->
            <td><?=$v['price'] ?></td>     <!-- sku采购单价 -->
            <td><?=$v['total_price'] ?></td>     <!-- sku采购金额 -->
            <td><?=$v['arrival_quantity'] ?></td>  <!-- 到货数量 -->
            <td><?=$v['created_at'] ?></td>  <!-- 采购日期 -->
            <td><?=$v['payer_time'] ?></td>  <!-- 付款日期 -->
            <td><?=$v['purchas_status'] ?></td>            <!-- 采购状态 -->
            <td><?=$v['note'] ?></td>              <!-- 备注 -->
        </tr>
    <?php endforeach; ?>
    <tbody>
        
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