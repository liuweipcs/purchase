<?php

$this->title = '海外仓-采购单数据统计';
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
        background: white;
    }
    .payment{
        float: left;
        border:solid 1px black;
        padding:10px;
        margin-left:10px;
        border-radius: 5px;
        color:black;
        cursor: pointer;
    }
</style>
<div class="logistics-carrier-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="clearfix"></div>

    <!--<div class="btn-group" style="margin-bottom: 10px;">
        <span class="btn btn-danger" disabled="disabled">采购下单完成率</span>
        <a href="/overseas-purchase-order-statistics/payment" class="btn btn-default">付款完成率</a>
    </div>-->

    <div class="btn-group" style="margin-bottom: 10px;">
        <a class="btn btn-danger" disabled="disabled">采购下单完成率</a>
        <a href="?source=2" class="btn btn-default">付款完成率</a>
        <a href="?source=3" class="btn btn-default">下单数</a>
    </div>

    <div role="tabpanel" class="tab-pane active" id="compact">
        <div class="panel panel-success">
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                    <th style="text-align: center">采购员</th>
                    <th style="text-align: center">18小时完成率</th>
                    <th style="text-align: center">24小时完成率</th>
                    <th style="text-align: center">36小时完成率</th>
                    <th style="text-align: center">48小时完成率</th>
                    <th style="text-align: center">72小时完成率</th>
                    </thead>
                    <tbody>
                    <?php
                        $total_18 = 0;
                        $total_24 = 0;
                        $total_36 = 0;
                        $total_48 = 0;
                        $total_72 = 0;
                        foreach($list as $lk => $lv):
                            if($lv['total'] == 0):
                                $per_18 = 0;
                                $per_24 = 0;
                                $per_36 = 0;
                                $per_48 = 0;
                                $per_72 = 0;
                            else:
                                //数据处理
                                $per_18 = round(($lv['number_18']/$lv['total']) * 100,2);
                                $per_24 = round(($lv['number_24']/$lv['total']) * 100,2);
                                $per_36 = round(($lv['number_36']/$lv['total']) * 100,2);
                                $per_48 = round(($lv['number_48']/$lv['total']) * 100,2);
                                $per_72 = round(($lv['number_72']/$lv['total']) * 100,2);
                            endif;
                            $total_18 += $per_18;
                            $total_24 += $per_24;
                            $total_36 += $per_36;
                            $total_48 += $per_48;
                            $total_72 += $per_72;
                    ?>
                        <tr>
                            <td style="vertical-align: middle;text-align: center;"><?= $lk ?></td>
                            <td style="vertical-align: middle;text-align: center;"><?= $per_18 . "%" ?></td>
                            <td style="vertical-align: middle;text-align: center;"><?= $per_24 . "%" ?></td>
                            <td style="vertical-align: middle;text-align: center;"><?= $per_36 . "%" ?></td>
                            <td style="vertical-align: middle;text-align: center;"><?= $per_48 . "%" ?></td>
                            <td style="vertical-align: middle;text-align: center;"><?= $per_72 . "%" ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="text-align: center">
                        <td style="font-weight: bold">总计</td>
                        <td><?= round($total_18/$total,2) . "%"?></td>
                        <td><?= round($total_24/$total,2) . "%"?></td>
                        <td><?= round($total_36/$total,2) . "%"?></td>
                        <td><?= round($total_48/$total,2) . "%"?></td>
                        <td><?= round($total_72/$total,2) . "%"?></td>
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
<script type="text/javascript">
    function changeButton(obj , type){
        if(type == 1){
            window.location.href="/overseas-purchase-order-statistics/index";
        }else if(type == 2){
            window.location.href="/overseas-purchase-order-statistics/payment";
        }
    }
</script>