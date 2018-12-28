<?php

use yii\helpers\Html;
use app\config\Vhelper;

?>
<div class="basic-form">
        <?php if($resultList): ?>
            <table class='table table-hover table-bordered table-striped' >
                <tr>
                    <th>采购单号</th>
                    <th>sku</th>
                    <th>图片</th>
                    <th>产品名称</th>
                    <th>未到货数量</th>
                    <th>未到货金额</th>
                </tr>
                <?php foreach ($resultList as $value):
                    $pur_number = $value['pur_number'];
                    $rowSpan = isset($resultListCount[$pur_number])?$resultListCount[$pur_number]:'';
                    unset($resultListCount[$pur_number]);
                    ?>
                    <tr>
                        <?php if($rowSpan){?>
                            <td <?php echo "rowspan='$rowSpan'";?> style="padding-top: <?php echo $rowSpan*20 ?>px; " ><?php echo $value['pur_number'];?></td>
                        <?php }?>
                        <td rowspan=""><?php echo $value['sku'];?></td>
                        <td><?php
                            // 加载图片：优先加载缓存的图片
                            echo Html::img(Vhelper::getSkuImage($value['sku']),['width'=>'80px','height'=>'40px']);
                            ?></td>
                        <td style="max-width: 250px;"><?php echo $value['name'];?></td>
                        <td><?php echo $value['totalCount'];?></td>
                        <td><?php echo $value['totalAmount'];?></td>
                    </tr>
                <?php endforeach; ?>
                
            </table>
        <?php endif;?>
</div>
<style>
    /* 设置居中   */
    table tr th{
        text-align: center;
    }
    table tr td{
        text-align: center;
    }
</style>