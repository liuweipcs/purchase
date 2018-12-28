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
            <th>供应商编号</th>
            <th>入库日期</th>
            <th>入库单号</th>
            <th>入库仓</th>
            <th>应入库数</th>
            <th>已入库数</th>
            <th>良品库</th>
            <th>次品库/不入库</th>
            <th>入库单价</th>
            <th>是否开票</th>
            <th>结算方式</th>
            <th>支付方式</th>
            <th>采购员备注</th>
        </tr>
    </thead>
	<?php foreach($data as $k => $v):?>
		<tr>
			<td><?=$v['compact_number'] ?></td> 
			<td><?=$v['pur_number'] ?></td> 
			<td><?=$v['sku'] ?></td> 				<!-- sku -->
            <td><?=$v['name'] ?></td>        		<!-- 商品名称 -->
            <td><?=$v['purchase_type'] ?></td>      <!-- 采购类型 -->
            <td><?=$v['supplier_name'] ?></td>      <!-- 供应商名称 -->
            <td><?=$v['supplier_code'] ?></td>      <!-- 供应商编码 -->
            <td><?=$v['instock_date'] ?></td>       <!-- 入库日期 -->
            <td><?=$v['receipt_number'] ?></td>     <!-- 入库单号 -->
            <td><?=$v['warehouse_code'] ?></td>     <!-- 入库仓 -->
            <td><?=$v['purchase_quantity'] ?></td>  <!-- 应入库数 -->
            <td><?=$v['instock_qty_count'] ?></td>  <!-- 已入库数 -->
            <td><?=$v['instock_qty_count'] ?></td>  <!-- 良品库 -->
            <td><?=$v['nogoods'] ?></td>            <!-- 次品库/不入库 -->
            <td><?=$v['price'] ?></td>              <!-- 入库单价 -->
            <td><?=$v['is_drawback'] ?></td>        <!-- 是否开票 -->
            <td><?=$v['supplier_settlement_name'] ?></td>  <!-- 结算方式 -->
            <td><?=$v['pay_type'] ?></td>                  <!-- 支付方式 -->
            <td><?=$v['note'] ?></td>                      <!-- 采购员备注 -->
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