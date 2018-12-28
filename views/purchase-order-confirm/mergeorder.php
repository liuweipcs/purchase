<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use app\services\SupplierServices;
?>
<input type="hidden" class="merge_purnumber" value="<?= $purNumber ?>">
<?php if(is_array($orderData)&&count($orderData)){
    foreach($orderData as $ak=>$vb){
        ?>
        <div class="row">
            <div class="col-md-1">
                <div class="form-group field-purchaseorder-carrier">
                    <label class="control-label" for="purchaseorder-carrier">SKU数量</label>
                    <input type="text"  class="form-control" name="" value="<?= count($itemsData)?>"  disabled>
                </div>
            </div>

            <div class="col-md-1">
                <div class="form-group field-purchaseorder-carrier">
                    <label class="control-label" for="purchaseorder-carrier">采购数量</label>
                    <input type="text"  class="form-control" name="" value="<?= array_sum(array_column($itemsData,'qty')) ?>"  disabled>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-purchaseorder-carrier">
                    <label class="control-label" for="purchaseorder-carrier">结算方式</label>
                    <?= Html::dropDownList('',$vb['account_type'],SupplierServices::getSettlementMethod(),['class'=>'form-control','disabled'=>'disabled'])?>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-purchaseorder-carrier">
                    <label class="control-label" for="purchaseorder-carrier">支付方式</label>
                    <?= Html::dropDownList('',$vb['pay_type'],\app\services\SupplierServices::getDefaultPaymentMethod(),['class'=>'form-control','disabled'=>'disabled'])?>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-purchaseorder-carrier">
                    <label class="control-label" for="purchaseorder-carrier">运输方式</label>
                    <?= Html::dropDownList('',$vb['shipping_method'],\app\services\PurchaseOrderServices::getShippingMethod(),['class'=>'form-control','disabled'=>'disabled'])?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group field-purchaseorder-carrier">
                    <label class="control-label" for="purchaseorder-carrier">供应商名称</label>
                    <input type="text"  class="form-control" name="" value="<?= $vb['supplier_name']?>" disabled>
                </div>
            </div>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th><?=Yii::t('app','图片')?></th>
                    <th><?=Yii::t('app','SKU')?></th>
                    <th><?=Yii::t('app','产品名')?></th>
                    <th><?=Yii::t('app','采购数量')?></th>
                    <th><?=Yii::t('app','单价')?></th>
                    <th><?=Yii::t('app','金额')?></th>
                    <th><?=Yii::t('app','产品链接')?></th>
                </tr>
                </thead>
                <tbody class="bs">
                <?php
                $total =0;
                foreach($itemsData as $k=> $v){
                    $totalprice = $v['ctq']*$v['price'];
                    $totalprices = $v['qty']*$v['price'];
                    $total += $totalprice?$totalprice:$totalprices;
                    $img=Vhelper::downloadImg($v['sku'],$v['product_img'],2);
                    $img =Html::img($img,['width'=>100]);
                    ?>
                    <tr>
                        <td>
                            <?=Html::a($img,['#'], ['class' => "img", 'data-skus' => $v['sku'],'data-imgs' => $v['product_img'], 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal'])?>
                        </td>
                        <td title="<?=$v['sku']?>">
                            <?= $v['sku']?>
                        </td>

                        <td title="<?=$v['name']?>">
                            <a href="<?=Yii::$app->params['SKU_ERP_Product_Detail'].$v['sku']?>" target="_blank"><?=$v['name']?></a>
                        </td>

                        <td class="ctqs">
                            <?= $v['qty']?>
                        </td>

                        <td>
                            <?= round($v['price'],2)?>
                        </td>

                        <td><?= $totalprice?$totalprice:$totalprices?></td>
                        <td>
                            <?php
                            $plink=$v['product_link'] ? $v['product_link'] : \app\models\SupplierQuotes::getUrl($v['sku']);
                            echo Html::input('text', 'purchaseOrderItems[product_link][]',$plink, ['class' => '','readonly'=>'readonly']) ?>
                            <a href='<?=$plink?>' title='' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>
                        </td>
                    </tr>
                <?php }?>
                <tr class="table-module-b1">
                    <td class="total" colspan="8">总额：<b><?=round($total,2).'&nbsp;&nbsp;'.$vb['currency_code']?></b></td>
                </tr>
                </tbody>
            </table>
    <?php }?>
<?php }else{ ?>
    <h1>数据有误</h1>
<?php }?>
            <button class="btn btn-primary merge_submit">确认合并</button>

<?php
$mergePostUrl = Url::toRoute('purchase-merge');
$js = <<<JS
$('.merge_submit').on('click',function(){
    var purNumber = $('.merge_purnumber').val();
    $.ajax({
        url :'{$mergePostUrl}',
        data:{purNumber:purNumber},
        dataType:'json',
        type:'post',
        success:function (data) {
            $('.modal-body').html(data.message);
        }
        });
    });

JS;
$this->registerJs($js);
?>




