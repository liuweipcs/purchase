<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/31
 * Time: 16:52
 */
?>
<table class="table">
    <h3>sku各平台销量</h3>
    <thead>
        <th>sku</th>
        <th>平台</th>
        <th>仓库名称</th>
        <th>3天销量</th>
        <th>7天销量</th>
        <th>15天销量</th>
        <th>30天销量</th>
        <th>统计时间</th>
        <th>创建时间</th>
        <th>更新时间</th>
    </thead>
    <tbody>
    <?php
        if(isset($datas['salesDatas'])&&!empty($datas['salesDatas'])){
            foreach ($datas['salesDatas'] as $sale){
    ?>
        <tr>
            <td><?= isset($sale['sku']) ? $sale['sku'] : '' ?></td>
            <td><?= isset($sale['platform_code']) ? $sale['platform_code'] : '' ?></td>
            <td><?php
                $warehouse = \app\models\Warehouse::find()
                    ->select('warehouse_name')
                    ->where(['warehouse_code'=>isset($sale['warehouse_code']) ? $sale['warehouse_code'] : ''])
                    ->scalar();
                echo $warehouse?$warehouse:'' ?>
            </td>
            <td><?= isset($sale['days_sales_3']) ? $sale['days_sales_3'] : '' ?></td>
            <td><?= isset($sale['days_sales_7']) ? $sale['days_sales_7'] : '' ?></td>
            <td><?= isset($sale['days_sales_15']) ? $sale['days_sales_15'] : '' ?></td>
            <td><?= isset($sale['days_sales_30']) ? $sale['days_sales_30'] : '' ?></td>
            <td><?= isset($sale['statistics_date']) ? $sale['statistics_date'] : '' ?></td>
            <td><?= isset($sale['create_time']) ? $sale['create_time'] : '' ?></td>
            <td><?= isset($sale['update_time']) ? $sale['update_time'] : '' ?></td>
        </tr>
    <?php }}else {?>
            <tr>
                <td colspan="12" style="text-align: center">暂无销量信息</td>
            </tr>
    <?php
        }
    ?>
    </tbody>
</table>
<table class="table">
    <h3>sku实时库存信息</h3>
    <thead>
        <th>sku</th>
        <th>仓库</th>
        <th>平台</th>
        <th>在途库存</th>
        <th>可用库存</th>
    </thead>
    <tbody>
    <?php
    $html='';
        if(isset($datas['stock']['status'])&&$datas['stock']['status']=='success'){
            if(empty($datas['stock']['data'])){
                $html =  '<tr><td colspan="5" style="text-align: center">暂无实时库存</td></tr>';
            }else{
                foreach ($datas['stock']['data'] as $stock){
                    $html .= "<tr>
                    <td>".$stock->sku."</td>
                    <td>".$stock->warehouse_code."</td>
                    <td>".$stock->platform_code."</td>
                    <td>".$stock->on_way_stock."</td>
                    <td>".$stock->available_stock."</td>
                    </tr>";
                }
            }
        }else{
            $html = '<tr><td colspan="5" style="text-align: center">'.$datas['stock']['message'].'</td></tr>';
        }
        echo $html;
    ?>
    </tbody>
</table>
