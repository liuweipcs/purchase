<?php
use yii\helpers\Html;
$this->title = '下单率统计';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="logistics-carrier-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="clearfix"></div>

    <div role="tabpanel" class="tab-pane active" id="compact">
        <div class="panel panel-success">
            <div class="panel-heading">
                <div class="pull-right">
                    财务付款超时总数量：<b><?=$total?></b>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                    <th>创建日期</th>
                    <th>采购员</th>
                    <th>SKU下单率</th>
                    <th>采购数量下单率</th>
                    <th>到货率</th>
                    <th>PO数</th>
                    <th>SKU数</th>
                    <th>异常单数</th>
                    <th>处理的异常单数</th>
                    <th>缺货数</th>
                    <th>在途数</th>
                    <th>操作</th>
                    </thead>
                    <tbody>

                    <?php

                    if (empty($list)) {
                        echo "<tr><td colspan='12' style='text-align: center;'>";
                        echo '无数据';
                        echo "</tr></td>";
                        return false;
                    }

                    $all_count = count($list);
                    $sku_total_res = 0;
                    $ctq_total_res = 0;
                    $arrival_total_res = 0;
                    $total_po_number = 0;
                    $total_sku_number = 0;
                    $total_exp_number = 0;
                    $total_handler_exp_number = 0;
                    $total_left_stock = 0;
                    $total_on_way_stock = 0;

                    $po_number_res = 0;
                    $sku_number_res = 0;
                    $exp_number_res = 0;
                    $handler_exp_number_res = 0;
                    $left_stock_res = 0;
                    $on_way_stock_res = 0;
                    foreach($list as $lk => $lv):
                    $r = count($lv);
                    $sku_lower_rate_total = 0;
                    $ctq_lower_rate_total = 0;
                    $arrival_rate_total = 0;

                    ?>
                    <?php foreach($lv as $k => $v):
                    $sku_lower_rate_total += ($v['total_sku']==0 ? 0 : $v['success_sku']/$v['total_sku']);
                    $ctq_lower_rate_total += ($v['total_qty']==0 ? 0 : $v['success_qty']/$v['total_qty']);
                    $arrival_rate_total += ($v['success_qty']==0 ? 0 : $v['arrival_qty']/$v['success_qty']);
                    $total_po_number += $v['po_number'];
                    $total_sku_number += $v['sku_number'];
                    $total_exp_number += $v['exp_number'];
                    $total_handler_exp_number += $v['handler_exp_number'];
                    $total_left_stock += $v['left_stock'];
                    $total_on_way_stock += $v['on_way_stock'];
                    ?>

                        <tr>
                            <td style="vertical-align: middle;text-align: center;"><?= $lk ?></td>
                            <td><?= $v['buyer'] ?></td>
                            <!--已完成状态SKU数/今日总SKU数-->
                            <td><?= round(($v['total_sku'] ==0 ? 0 : $v['success_sku']/$v['total_sku'])* 100,2) . "%" ?></td>
                            <!--已完成状态采购数量／今日总采购数量-->
                            <td><?= round(( $v['total_qty']==0 ? 0 : $v['success_qty']/$v['total_qty']) * 100,2) . "%" ?></td>
                            <!--已到货采购数量／已完成状态采购数量-->
                            <td><?= round(($v['success_qty']==0 ? 0 : $v['arrival_qty']/$v['success_qty']) * 100,2) . "%" ?></td>
                            <!--PO数-->
                            <td><?=$v['po_number']?></td>
                            <!--SKU数-->
                            <td><?=$v['sku_number']?></td>
                            <!--异常单数量-->
                            <td><?=$v['exp_number']?></td>
                            <!--处理的异常单数-->
                            <td><?=$v['handler_exp_number']?></td>
                            <!--缺货数-->
                            <td><?=$v['left_stock']?></td>
                            <!--在途数-->
                            <td><?=$v['on_way_stock']?></td>
                            <td>
                                <?php
                                if (\mdm\admin\components\Helper::checkRoute('delete-one')) {
                                    echo Html::a('<i class="fa fa-fw fa-close"></i>删除', ['delete-one', 'id'=>$v['id']], [
                                        'title'       => Yii::t('app', '删除'),
                                        'class'       => 'btn btn-xs delete',
                                        'data' => [
                                            'confirm' => '确定删除吗?',
                                        ]
                                    ]);
                                }

                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?><!-- 循环2 -->
                    <?php
                    //数据处理
                    $sku_total = round(($sku_lower_rate_total/$r) * 100,2);
                    $ctq_total = round(($ctq_lower_rate_total/$r) * 100,2);
                    $arrival_total = round(($arrival_rate_total/$r) * 100,2);

                    $sku_total_res += $sku_total;
                    $ctq_total_res += $ctq_total;
                    $arrival_total_res += $arrival_total;

                    $po_number_res += $total_po_number;
                    $sku_number_res += $total_sku_number;
                    $exp_number_res += $total_exp_number;
                    $handler_exp_number_res += $total_handler_exp_number;
                    $left_stock_res += $total_left_stock;
                    $on_way_stock_res += $total_on_way_stock;
                    ?>
                    <tr style="border-bottom: #0c0c0c 2px solid;background-color: #8fdf82">
                        <td></td>
                        <td>总计</td>
                        <td style="vertical-align: middle;text-align: center;"><?= $sku_total . "%" ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $ctq_total . "%" ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $arrival_total . "%" ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $total_po_number  ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $total_sku_number  ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $total_exp_number  ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $total_handler_exp_number  ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $total_left_stock  ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $total_on_way_stock  ?></td>
                    </tr>


                    <?php endforeach; ?><!-- 循环1 -->
                    <tr style="background-color: #CCCCCC">
                        <td></td>
                        <td>总计</td>
                        <td style="vertical-align: middle;text-align: center;"><?= round(($sku_total_res/$all_count),2) . "%" ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= round(($ctq_total_res/$all_count),2) . "%" ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= round(($arrival_total_res/$all_count), 2) . "%" ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $po_number_res ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $sku_number_res ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $exp_number_res ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $handler_exp_number_res ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $left_stock_res ?></td>
                        <td style="vertical-align: middle;text-align: center;"><?= $on_way_stock_res ?></td>
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

<?php
$js = <<<JS
    $(function () {
            //批量导出
         $('#export-csv').click(function() {
              var create_time = $("input[name='LowerRateStatisticsSearch[create_time]']").val();
              var buyer_id = $("select[name='LowerRateStatisticsSearch[buyer_id]'] :selected").val();
              window.location.href='/lower-rate-statistics/export-csv?create_time='+create_time+'&buyer_id='+buyer_id;
         });
    });
JS;
$this->registerJs($js);
?>