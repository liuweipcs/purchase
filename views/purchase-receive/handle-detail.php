<?php

use yii\helpers\Html;



$this->title = 'QC异常处理';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= Html::beginForm(['handle-save'], 'post', ['enctype' => 'multipart/form-data']) ?>
<table class='table table-hover table-bordered table-striped'>
    <tr>
        <td>采购单 : <?=$data[0]['pur_number']?></td>
        <td>供应商 : <?=$data[0]['supplier_name']?></td>
        <td><span class="label label-danger"><?=Yii::$app->params['receive_type'][$data[0]['receive_type']]?></span></td>
    </tr>
</table>
<!--<p>温馨提示:选择了<span style="color: red">全额退款</span>或则是选择<span style="color: red">部分到货不等待剩余</span>请把退款选择为<span style="color: red">是</span>并填写上金额</p>-->
<table class='table table-hover table-bordered table-striped' >
    <tr>
        <th>No.</th>
        <th>产品</th>
        <th>数量</th>
        <th>单价</th>
        <th>是否收货</th>
        <th>采购处理结果</th>
        <th>仓库返回结果</th>
        <th>仓库返回图片</th>
      <!--  <?php /*if($data[0]['receive_type']=='2'):*/?>
        <th width="120">退款</th>
        <?php /*else:*/?>
        <th>承担方</th>
        --><?php /*endif;*/?>

    </tr>
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
            赠送: <?=$val['presented_qty']?>
        </td>
        <td><?=$val['price']?></td>
        <td>
            <?= Html::dropDownList("PurchaseReceive[{$val['id']}][is_receipt]",'',Yii::$app->params['is_receipt'],['class'=>'','prompt' => '请选择','required'=>true])?></td>
       <!-- <?php /*if($val['receive_type']=='2'):*/?>
        <td>
            <span><?/*= Html::radioList("PurchaseReceive[{$val['id']}][handle_type]", $val['handle_type']?$val['handle_type']:'3', ['1'=>'全额退款','2'=>'部分到货不等待剩余','3'=>'部分到货等待剩余'],['class'=>'radio handle_type','required'=>true]) */?></span>
        </td>

        <td>

            <span style="display: none">
                <?/*= Html::dropDownList("PurchaseReceive[{$val['id']}][is_return]",0, [0=>'否',1=>'是'],['class'=>'is_return','required'=>true])*/?>
                <br>
                <span class="pay_price" style="display: none">
                    金额<?/*= Html::input('text', "PurchaseReceive[{$val['id']}][refund_amount]", '', ['class' => 'input-small', 'size' => 10,'placeholder'=>'请算上运费']) */?>
                </span>
            </span>

        </td>

        <?php /*else:*/?>
        <td>
            <span><?/*= Html::radioList("PurchaseReceive[{$val['id']}][handle_type]", $val['handle_type']?$val['handle_type']:'4', ['4'=>'入库','5'=>'退货'],['class'=>'radio handle_type','required'=>true]) */?></span>
        </td>
        <td>
            <span ><?/*= Html::radioList("PurchaseReceive[{$val['id']}][bearer]", $val['bearer']?$val['bearer']:'1', ['1'=>'供应商','2'=>'我方'],['class'=>'radio bearer','required'=>true]) */?></span>
        </td>
        --><?php /*endif;*/?>
        <td>
            <?=Html::textarea("PurchaseReceive[{$val['id']}][note_handle]", $val['note_handle'],['placeholder'=>'此备注信息我们将告诉仓库','required'=>true])?></br>
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
            console.log(handle_type);
            //收货收多 处理方式为入库的时候，需要选择责任方
            if(handle_type=="5"){
                $(this).parents("tr").find("div.bearer").hide();
                $(this).parents("tr").find("div.bearer input").attr("disabled","disabled");
            }else{
                $(this).parents("tr").find("div.bearer").show();
                $(this).parents("tr").find("div.bearer input").removeAttr("disabled");
            }
            //来货不足 处理方式为 终止来货 时，需要确认是否退款

            if(handle_type=="3"){
                $(this).parents("tr").find("select.is_return").parent().hide();
                $(this).parents("tr").find("select.is_return").attr("disabled","disabled");
            } else {

                $(this).parents("tr").find("select.is_return").parent().show();
                $(this).parents("tr").find("select.is_return").removeAttr("disabled");
            }
        });
        
        $('.is_return').on('change', function() {
            var pay_price = $(this).siblings('.pay_price');
          if ($(this).val() == 1) {
              pay_price.show();
              pay_price.find('input').focus();
          } else {
              pay_price.hide();
          }
        });
   });
JS;
$this->registerJs($js);
?>