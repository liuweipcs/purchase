<?php

use yii\helpers\Html;



$this->title = 'QC异常处理';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= Html::beginForm(['handle-save'], 'post', ['enctype' => 'multipart/form-data']) ?>
<table class='table table-hover table-bordered table-striped'>
    <tr>
        <td>快递单 : <?=$data[0]['express_no']?></td>
        <td>采购单 : <?=$data[0]['pur_number']?></td>
        <td>供应商 : <?=$data[0]['supplier_code']?></td>
    </tr>
</table>
<table class='table table-hover table-bordered table-striped' >
    <tr>
        <th>No.</th>
        <th>产品</th>
        <th>采购单数量</th>
        <th>品检数量</th>
        <th>单价</th>
        <th>是否收货</th>
        <th>采购处理结果</th>
        <th>仓库返回结果</th>
        <th>仓库返回图片</th>
    </tr>
    <?php 
        $handle_type=Yii::$app->params['is_receipt'];
        array_pop($handle_type);
    ?>
    <?php foreach ($data as $key=>$val):?>
    <tr>
        <td><?=$key+1?></td>
        <td>
            SKU:<?=$val['sku']?></br>
            名称:<?=$val['name']?>
        </td>
        <td>
            预期: <?=$val['qty']?></br>
            到货: <?=$val['delivery_qty']?></br>
            赠送: <?=$val['presented_qty']?></br>
        </td>
        <td>
            品检: <?=$val['check_qty']?></br>
            合格: <?=$val['good_products_qty']?></br>
            不合格: <?=$val['bad_products_qty']?></br>
        </td>
        <td><?=$val['price']?></td>
        <td>
            <?= Html::dropDownList("PurchaseQc[{$val['id']}][is_receipt]",'',Yii::$app->params['is_receipt'],['class'=>'','prompt' => '请选择','required'=>true])?></td>
       <!-- <td>
            <?/*= Html::radioList("PurchaseQc[{$val['id']}][handle_type]", $val['handle_type']?$val['handle_type']:'1', $handle_type,['class'=>'radio handle_type_qc','required'=>true]) */?>
            <span class="pay_price" style="display: none">
                退款金额&nbsp;<?/*= Html::input('text', "PurchaseQc[{$val['id']}][refund_amount]", '', ['class' => 'input-small', 'size' => 10]) */?>
            </span>
        </td>-->
        <td>
            <?=Html::textarea("PurchaseQc[{$val['id']}][note_handle]", $val['note_handle'],['class' => 'input-small', 'placeholder' => '此填写的信息将传送至仓库','required'=>true])?></br>
        </td>
        <td><?=$val['note']?></td>
        <td>
            <?php if($val['img']){
                $img =json_decode($val['img']);
                foreach($img as $v){

                    ?>
                    <a href="<?php echo $v?>" target="_blank"><img src="<?php echo $v?>" width="50px"></a>
                <?php }}?>
        </td>
    </tr>
    <?php endforeach;?>
</table>
<p style="text-align:right"><?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?></p>
<?= Html::endForm() ?>
<?php
$js=<<<JS
   $(function(){
        $("div.handle_type").find("input").change(function(){
            var handle_type=this.value;
            if(handle_type=="return"){
                $(this).parents("tr").find("div.bearer").hide();
                $(this).parents("tr").find("div.bearer input").attr("disabled","disabled");
            }else{
                $(this).parents("tr").find("div.bearer").show();
                $(this).parents("tr").find("div.bearer input").removeAttr("disabled");
            }
        });
        
        $('.handle_type_qc input:radio').on('change', function() {
            var pay_price = $(this).parent().parent().next();
          if ($(this).val() == 2 || $(this).val() == 3) {
              pay_price.show();
              pay_price.find('input').focus();
          } else {
              pay_price.hide();
          }
        });
   });
JS;
$this->registerJs($js);

$cssString = "div.handle_type_qc label{display:block;}";  
$this->registerCss($cssString); 
?>