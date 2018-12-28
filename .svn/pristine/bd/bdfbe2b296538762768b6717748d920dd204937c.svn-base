<?php

$this->title = '海外仓-下单数';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .order{
        float: left;
        border:solid 1px black;
        padding:10px;
        border-radius: 5px;
        color:black;
        cursor: pointer;
    }
    .payment{
        float: left;
        border:solid 1px black;
        padding:10px;
        margin-left:10px;
        border-radius: 5px;
        color:black;
        background: white;
        cursor: pointer;
    }
</style>
<div class="logistics-carrier-index">

    <?php  echo $this->render('_search3', ['model' => $searchModel]); ?>
    <div class="clearfix"></div>

    <div class="btn-group" style="margin-bottom: 10px;">
        <a href="/overseas-purchase-order-statistics/index" class="btn btn-default">采购下单完成率</a>
        <a href="?source=2" class="btn btn-default">付款完成率</a>
        <a href="?source=3" class="btn btn-danger" style="background:#DF8A89" disabled="disabled">下单数</a>
    </div>

    <div role="tabpanel" class="tab-pane active" id="compact">
        <div class="panel panel-success">
            <div class="panel-body">
                <table class="table table-bordered">
                <table class="table table-bordered">
                    <thead>
                    <th style="text-align: center">采购员</th>
                    <th style="text-align: center">PO单数</th>
                    <th style="text-align: center">SKU数</th>
                    </thead>
                    <tbody>
                        <?php
                        $total_po = 0;
                        $total_sku = 0;
                        foreach($list as $lk => $lv):
                            $total_po += $lv['po_num'];
                            $total_sku += $lv['sku_num'];
                            ?>
                            <tr>
                                <td style="vertical-align: middle;text-align: center;"><?= $lk ?></td>
                                <td style="vertical-align: middle;text-align: center;"><?= $lv['po_num'] ?></td>
                                <td style="vertical-align: middle;text-align: center;"><?= $lv['sku_num'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="text-align: center">
                            <td style="font-weight: bold">总计</td>
                            <td><?= $total_po ?></td>
                            <td><?= $total_sku ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="panel-footer">
                <?= \yii\widgets\LinkPager::widget([
                    'pagination' => $pager,
                    'firstPageLabel' => "首页",
                    'prevPageLabel' => '上一页',
                    'nextPageLabel' => '下一页',
                    'lastPageLabel' => '末页',
                    'options' => ['class' => 'pagination no-margin']
                ]);
                ?>
            </div>

        </div>
    </div>
</div>
