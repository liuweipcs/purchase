<?php

$this->title = '海外仓-付款完成率';
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

    <?php  echo $this->render('_search1', ['model' => $searchModel]); ?>
    <div class="clearfix"></div>

    <div class="btn-group" style="margin-bottom: 10px;">
        <a href="/overseas-purchase-order-statistics/index" class="btn btn-default">采购下单完成率</a>
        <a href="?source=2" class="btn btn-danger" style="background:#DF8A89" disabled="disabled">付款完成率</a>
        <a href="?source=3" class="btn btn-default" >下单数</a>
    </div>

    <div role="tabpanel" class="tab-pane active" id="compact">
        <div class="panel panel-success">
            <div class="panel-body">
                <table class="table table-bordered">
                <table class="table table-bordered">
                    <thead>
                    <th style="text-align: center">采购员</th>
                    <th style="text-align: center">18小时完成率</th>
                    <th style="text-align: center">24小时完成率</th>
                    </thead>
                    <tbody>
                        <?php
                            $total_18 = 0;
                            $total_24 = 0;
                            $total_po = 0;
                            $total_sku = 0;
                            foreach($list as $lk => $lv):
                                if($lv['total'] == 0):
                                    $per_18 = 0;
                                    $per_24 = 0;
                                else:
                                    //数据处理
                                    $per_18 = round(($lv['number_18']/$lv['total']) * 100,2);
                                    $per_24 = round(($lv['number_24']/$lv['total']) * 100,2);
                                endif;
                                $total_18 += $per_18;
                                $total_24 += $per_24;
                            ?>
                            <tr>
                                <td style="vertical-align: middle;text-align: center;"><?= $lk ?></td>
                                <td style="vertical-align: middle;text-align: center;"><?= $per_18 . "%" ?></td>
                                <td style="vertical-align: middle;text-align: center;"><?= $per_24 . "%" ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td style="vertical-align: middle;text-align: center;font-weight: bold">总计</td>
                            <td style="vertical-align: middle;text-align: center;"><?= round($total_18/$total,2) . "%"?></td>
                            <td style="vertical-align: middle;text-align: center;"><?= round($total_24/$total,2) . "%"?></td>
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