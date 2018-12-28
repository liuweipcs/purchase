<style>
    .middle{vertical-align:middle;text-align: center;}
</style>


<table class="table table-bordered table-condensed">
    <tr>
        <th style="vertical-align:middle;text-align: center;">SKU</th>
        <th style="vertical-align:middle;text-align: center;">历史供应商</th>
        <th style="vertical-align:middle;text-align: center;">采购次数</th>
        <th style="vertical-align:middle;text-align: center;">历史采购单价</th>
        <th style="vertical-align:middle;text-align: center;">采购数量</th>
    </tr>
    <?php
        $row = count($history);
        $sku_num = 0;
        foreach ($history as $k => $v1):
            $num = 0;
    ?>
        <tr>
            <?php if($sku_num == 0):?>
                <td style="vertical-align:middle;text-align: center;" rowspan="<?=$total+3?>"><?=$sku?></td>
            <?php endif;?>
            <?php foreach ($v1 as $v):?>
                <?php if($num == 0):?>
                <td style="vertical-align:middle;text-align: center;" rowspan="<?=count($v1)+1?>"><?=$k?></td>
                <?php endif;?>
                <?php if($num == 0 && $total>1):?>
                    <tr>
                        <td style="vertical-align:middle;text-align: center;"><?=$v['count']?></td>
                        <td style="vertical-align:middle;text-align: center;"><?=$v['price']?></td>
                        <td style="vertical-align:middle;text-align: center;"><?=$v['ctq']?></td>
                    </tr>
                <?php else:?>
                    <tr>
                        <td style="vertical-align:middle;text-align: center;"><?=$v['count']?></td>
                        <td style="vertical-align:middle;text-align: center;"><?=$v['price']?></td>
                        <td style="vertical-align:middle;text-align: center;"><?=$v['ctq']?></td>
                    </tr>
                <?php endif;?>
            <?php
            $num++;
            endforeach;
        ?>
        </tr>
    <?php
        $sku_num++;
        endforeach;
    ?>
</table>






