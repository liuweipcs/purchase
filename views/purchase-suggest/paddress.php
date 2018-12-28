<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

?>
<div class="purchase-suggest-view" style="height: 650px; overflow-y: scroll">
    <h4>半年内下单链接</h4>
    <table class="table table-bordered">
    <?php if(!empty($model) || !empty($model2)){ ?>
    <?php if(!empty($model)){ ?>
        <?php
            foreach ($model as $v){
                if(empty($v['supplier_product_address'])) continue;
        ?>
        <tr>
            <td>
                <a href="<?=$v['supplier_product_address']?>" target="_blank">
                    <?=$v['supplier_product_address']?>
                </a>
            </td>
        </tr>
    <?php }} ?>

    <?php if(!empty($model2)){ ?>
        <?php
            foreach ($model2 as $v2){
                if(empty($v2['features'])) continue;
        ?>
             <tr>
                <td>
                    <a href="<?=$v2['features']?>" target="_blank"><?=$v2['features']?></a>
                </td>
            </tr>
    <?php }} ?>
    <?php }else{ ?>
            <div style="height: 100px; text-align: center; margin-top: 20px">没有数据</div>
    <?php } ?>
    </table>

</div>
