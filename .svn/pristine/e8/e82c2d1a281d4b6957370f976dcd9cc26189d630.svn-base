<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\PurchaseOrderItemsV2;
use app\config\Vhelper;
use yii\helpers\Url;
use yii\bootstrap\Modal;

?>
    <style type="text/css">
        .img-rounded{width: 60px; height: 60px; !important;}
        .floors{max-height: 750px; overflow-y: scroll}
    </style>

<?php if(!empty($model)){ ?>
<div class="stockin-view floors">
    <h4 class="modal-title">审核采购单</h4>
    <?php $form = ActiveForm::begin(); ?>
    <input type="hidden" id="page"  name="page" value="<?=$page?>">

    <input type="hidden" id="purchaseorder-purchas_status" class="form-control" name="PurchaseOrders[purchas_status]">
    <?php foreach ($model as $item) { ?>
<?php
$m_style1='';
$m_style2='';
if(!empty($item['e_supplier_name']) && $item['review_status']<3){
    $m_style1="style='color:red'";
}

if(!empty($item['e_account_type']) && $item['review_status']<3){
    $m_style2="style='color:red'";
}
?>

    <div class="col-md-12" style="border: 1px solid red; margin: 10px 0; padding: 10px 0;">
        <input type="hidden" id="purchaseorder-id" class="form-control" name="PurchaseOrdersV2[id][]" value="<?= $item['id'] ?>" />
        <div class="col-md-4">
            <label>采购单号:</label>
            <input type="text" id="purchaseorder-warehouse_code"  value="<?php ECHO $item['pur_number']?>" disabled="disabled" />
        </div>

        <div class="col-md-4">
            <label>供应商:</label>
            <input type="text" <?=$m_style1?> id="purchaseorder-warehouse_code" value="<?php ECHO $item['supplier_name']?>" disabled="disabled" />
        </div>

        <?php
        $qsum=PurchaseOrderItemsV2::find()
            ->select(['sum(qty) as qty, sum(ctq) as ctq'])
            ->where(['pur_number'=>$item['pur_number']])->asArray()->all();
        ?>

        <div class="col-md-4">
            <label>SKU数量:</label>
            <input type="text" id="purchaseorder-pur_number" name="PurchaseOrdersV2[pur_number]" value="<?=!empty($qsum[0]['qty']) ? $qsum[0]['qty'] : '' ?>" disabled="disabled" />
        </div>

        <div class="col-md-4">
            <label>采购数量:</label>
            <input type="text" id="purchaseorder-pur_number" name="PurchaseOrdersV2[pur_number]" value="<?=!empty($qsum[0]['ctq']) ? $qsum[0]['ctq'] : '' ?>" disabled="disabled" />
        </div>

        <div class="col-md-4">
            <label>总金额:</label>
            <input type="text" id="purchaseorder-warehouse_code"  value="<?=round(PurchaseOrderItemsV2::getCountPrice($item['pur_number']),2)?>" disabled="disabled" />
        </div>

        <div class="col-md-4">
            <label>运&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;费:</label>
            <input type="text" id="purchaseorder-warehouse_code" value="<?=round(\app\models\PurchaseOrderShip::find()->where(['pur_number'=>$item['pur_number']])->sum('freight'),2)?>" disabled="disabled" />
        </div>

        <div class="col-md-4">
            <label>结款方式:</label>
            <input type="text" <?=$m_style2?> id="purchaseorder-warehouse_code" value="<?=$item['account_type'] ? \app\services\SupplierServices::getSettlementMethod($item['account_type']) : ''?>" disabled="disabled" />
        </div>

        <div class="col-md-4">
            <label>订单号:</label>
            <input type="text" id="purchaseorder-warehouse_code"  value="<?php
            $findone=\app\models\PurchaseOrderOrders::findOne(['pur_number'=>$item['pur_number']]);
            echo !empty($findone) ? $findone->order_number : '';
            ?>" disabled="disabled" />
        </div>

        <div style="padding: 50px"></div>

        <table class="table table-bordered">
            <tr>
                <th>图片</th>
                <th>SKU</th>
                <th>产品名称</th>
                <th>产品链接</th>
                <th>采购数量</th>
                <th>单价( RMB )</th>
                <th>金额</th>
                <th>预计到货时间</th>
            </tr>
            <?php foreach ($item['purchaseOrderItems'] as $v) {
                $results = \app\models\WarehouseResults::getResults($v['pur_number'],$v['sku'],'instock_user,instock_date');
                $img=Vhelper::toSkuImg($v['sku'],$v['product_img']);

                $style1='';
                $style2='';
                $style3='';
                $style4='';

                if(!empty($v['e_date_eta']) && $item['review_status']<3){
                    $style1="style='color:red'";
                }
                if(!empty($v['e_ctq']) && $v->ctq != $v->qty && $item['review_status']<3){
                    $style2="style='color:red'";
                }
                if(!empty($v['e_price']) && $v['e_price']!=0 && $item['review_status']<3){
                    $style3="style='color:red'";
                }
                if(!empty($v['product_link']) && $item['review_status']<3){
                    $style4="style='color:red'";
                }

                ?>

                <tr class="pay_list" style="width: 10%">
                    <td><?=Html::a($img,['#'], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal', 'data-skus' => $v['sku'],'data-imgs' => $v['product_img']])?></td>
                    <td>
                        <?=Html::a($v['sku'],['#'], ['class' => "sales", 'data-sku' =>$v['sku'], 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal',]) ?>
                    </td>
                    <td><a href="<?=Yii::$app->params['SKU_ERP_Product_Detail'].$v['sku']?>" target="_blank"><?=$v['name']?></a></td>
                    <td <?=$style4?>>
                        <?php
                        $plink=\app\models\PurchaseHistory::getPurchaseLink($v['sku']);
                        if($v['product_link']){
                            $prolink=$v['product_link'];
                        }else{
                            $prolink=$plink;
                        }
                        ?>
                        <a href="<?=$prolink?>" title=" <?=$prolink?>" target="_blank"><?=Vhelper::toSubStr($prolink,1,10)?></a>
                    </td>
                    <td <?=$style2?>><?=$v['ctq']?></td>
                    <td <?=$style3?>><?=round($v['price'],2)?></td>
                    <td><?=round($v['price']*$v['ctq'],2)?></td>
                    <td <?=$style1?>><?=$item['date_eta']?></td>
                </tr>
            <?php } ?>
        </table>
        <div class="form-group field-purchaseorder-pur_number required col-md-12">
            <label class="control-label" for="purchaseorder-pur_number">采购确认备注:</label>
            <?=$item['orderNote']['note']?>
        </div>

        <div class="form-group field-purchaseorder-pur_number required col-md-4">
            <label class="control-label" for="purchaseorder-pur_number">备注</label>
            <textarea name="PurchaseOrdersV2[audit_note][]" style="margin: 0px; width: 530px; height: 36px;"  placeholder="请写点什么吧"></textarea>
            <div class="help-block"></div>
        </div>

    </div>
    <?php } ?>

    <div class="form-group">
        <?= Html::submitButton('审批通过(Ok)',['class' => 'btn btn-success']) ?>
        <?= Html::submitButton('审批不通过(Rollback)', ['class' => 'btn btn-warning']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php }else{ ?>
    <h4 class="modal-title">没有需要审核的采购单</h4>
<?php } ?>

<?php
Modal::begin([
'id' => 'created-modal',
'header' => '<h4 class="modal-title">系统信息</h4>',
'size'=>'modal-lg',
'options'=>[
'z-index' =>'-1',
],
]);

$url=Url::toRoute(['product/viewskusales']);
$imgurl=Url::toRoute(['purchase-suggest/img']);

$js = <<<JS
$(function(){
    $(document).on('click', '.btn-success', function () {
        $('#purchaseorder-purchas_status').attr('value','3');
    });
     $(document).on('click', '.btn-warning', function () {
        $('#purchaseorder-purchas_status').attr('value','4');
    });
     $(document).on('click', '.btn-info', function () {
        $('#purchaseorder-purchas_status').attr('value','5');
    });

});

    $(document).on('click', '.sales', function () {
        $.get('{$url}', {sku:$(this).attr('data-sku')},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    
    $(document).on('click', '.img', function () {
        $.get('{$imgurl}', {img:$(this).attr('data-imgs'),sku:$(this).attr('data-skus')},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });


JS;
$this->registerJs($js);
?>