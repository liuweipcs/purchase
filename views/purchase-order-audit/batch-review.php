<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\PurchaseOrderItems;
use app\config\Vhelper;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\SupplierQuotes;
use app\services\SupplierGoodsServices;
?>
<style type="text/css">
    .img-rounded{width: 30px; height: 30px; !important;}
    .col-md-1{width: auto; !important;}
    .floors{max-height: 750px; overflow-y: scroll}
</style>
<div class="stockin-view floors">
    <?php $form = ActiveForm::begin(); ?>
    <input type="hidden" id="page"  name="page" value="<?=$page?>">

    <input type="hidden" id="purchaseorder-purchas_status" class="form-control" name="PurchaseOrders[purchas_status]">
    <?php foreach ($model as $ak => $item) { ?>

        <?php
            $m_style1='';
            $m_style2='';
            if(!empty($item['e_supplier_name']) && !empty($grade) && $grade->grade<3){
                $m_style1="style='color:red'";
            }

           /* if(!empty($item['e_account_type']) && !empty($grade) && $grade->grade<3){
                $m_style2="style='color:red'";
            }*/
        ?>

        <div class="col-md-12" style="border: 1px solid red;">
            <input type="hidden" id="purchaseorder-id" class="form-control" name="PurchaseOrder[id][]" value="<?= $item['id'] ?>">
            <div class="form-group field-purchaseorder-pur_number required col-md-1">
                <label class="control-label" for="purchaseorder-pur_number">采购单号:</label>
                <input type="text" id="purchaseorder-warehouse_code" class="form-control"  value="<?=$item['pur_number']?>" disabled="disabled" aria-required="true">
            </div>

            <div class="form-group field-purchaseorder-pur_number required col-md-1">
                <label class="control-label" for="purchaseorder-pur_number">采购员:</label>
                <input type="text" id="purchaseorder-warehouse_code" class="form-control"  value="<?=$item['buyer']?>" disabled="disabled" aria-required="true">
            </div>

            <div class="form-group field-purchaseorder-pur_number required col-md-1">
                <label class="control-label" for="purchaseorder-pur_number">供应商:</label>
                <input type="text" <?=$m_style1?> id="purchaseorder-warehouse_code" class="form-control"  value="<?php ECHO $item['supplier_name']?>" disabled="disabled" aria-required="true">
            </div>

            <?php
            $qsum=PurchaseOrderItems::find()
                ->select(['sum(qty) as qty, sum(ctq) as ctq'])
                ->where(['pur_number'=>$item['pur_number']])->asArray()->all();
            ?>

            <div class="form-group field-purchaseorder-pur_number required col-md-1">
                <label class="control-label" for="purchaseorder-pur_number">SKU数量</label>
                <input type="text" id="purchaseorder-pur_number" class="form-control" name="PurchaseOrder[pur_number]"
                       value="<?=!empty($qsum[0]['qty']) ? $qsum[0]['qty'] : '' ?>" disabled="disabled" maxlength="20" aria-required="true">
            </div>

            <div class="form-group field-purchaseorder-pur_number required col-md-1">
                <label class="control-label" for="purchaseorder-pur_number">采购数量</label>
                <input type="text" id="purchaseorder-pur_number" class="form-control" name="PurchaseOrder[pur_number]"
                       value="<?=!empty($qsum[0]['ctq']) ? $qsum[0]['ctq'] : '' ?>" disabled="disabled" maxlength="20" aria-required="true">
            </div>


            <div class="form-group field-purchaseorder-pur_number required col-md-1">
                <label class="control-label" for="purchaseorder-pur_number">总金额:</label>
                <input type="text" id="purchaseorder-warehouse_code" class="form-control"  value="<?=round(PurchaseOrderItems::getCountPrice($item['pur_number']),2)?>" disabled="disabled" aria-required="true">
            </div>



            <?php

            $freight1 = \app\models\PurchaseOrderShip::find()->where(['pur_number'=>$item->pur_number])->select('freight')->scalar();
            $freight2 = 0;
            if(!empty($item->purchaseOrderPayType)) {
                $freight2 = $item->purchaseOrderPayType->freight ? $item->purchaseOrderPayType->freight : 0;
            }
            if($freight2) {
                $freight = $freight2;
            } else {
                $freight = $freight1;
            }

            ?>










            <div class="form-group field-purchaseorder-pur_number required col-md-1">
                <label class="control-label" for="purchaseorder-pur_number">运费:</label>
                <input type="text" id="purchaseorder-warehouse_code" class="form-control"  value="<?= $freight ?>" disabled="disabled" aria-required="true">
            </div>

            <div class="form-group field-purchaseorder-pur_number required col-md-1">
                <label class="control-label" for="purchaseorder-pur_number">结款方式:</label>
                <input type="text" <?=$m_style2?> id="purchaseorder-warehouse_code" class="form-control" value="<?=$item['account_type'] ? \app\services\SupplierServices::getSettlementMethod($item['account_type']) : ''?>" disabled="disabled" aria-required="true">
            </div>

            <div class="form-group field-purchaseorder-pur_number required col-md-1">
                <label class="control-label" for="purchaseorder-pur_number">订单号:</label>
                <input type="text" id="purchaseorder-warehouse_code" class="form-control"  value="<?php
                $findone=\app\models\PurchaseOrderOrders::findOne(['pur_number'=>$item['pur_number']]);
                ECHO !empty($findone) ? $findone->order_number : '';
                ?>" disabled="disabled" aria-required="true">
            </div>

            <table class="table table-bordered">
                <tr>
                    <th>图片</th>
                    <th>SKU</th>
                    <th>产品名称</th>
                    <th>产品链接</th>
                    <th>采购数量</th>
                    <th>单价( RMB )</th>
                    <th>上次采购单价( RMB )</th>
                    <th>金额</th>
                    <th>预计到货时间</th>
                </tr>
                <?php
                foreach ($item['purchaseOrderItems'] as $v) {
                    $results = \app\models\WarehouseResults::getResults($v['pur_number'],$v['sku'],'instock_user,instock_date');
                    $supplierprice = SupplierQuotes::getQuotes($v['sku'],$item['supplier_code'])['supplierprice'];
                    //$img=Vhelper::toSkuImg($v['sku'],$v['product_img']);
                    $img=Html::img(Vhelper::downloadImg($v['sku'],$v['product_img'],2));
                    $style1='';
                    $style2='';
                    $style3='';
                    $style4='';

                    if(!empty($v['e_date_eta']) && !empty($grade) && $grade->grade<=3){
                        $style1="style='color:red'";
                    }
                    $date_start = date('Y-m-d 00:00:00');
                    $date_end   = date('Y-m-d 23:23:59');
                    $model2=\app\models\PurchaseSuggest::find()->select('qty')->where(['sku'=>$v['sku'],'purchase_type'=>1])->andWhere(['>','qty',0])->andWhere(['between','created_at',$date_start,$date_end])->scalar();
                    if(!empty($model2)&& $v['ctq'] != $model2){
                        $style2="style='color:red'";
                    }
                    /*if(!empty($v['e_price']) && $v['e_price']!=0 && !empty($grade) && $grade->grade<3){
                        $style3="style='color:red'";
                    }*/
                    if (($v['price'] - $supplierprice) > 0) {
                        $style3="style='color:red;font-weight: bold;'";
                    } elseif(($v['price'] - $supplierprice) < 0) {
                        $style3="style='color: #04f751;font-weight: bold;'";
                    }
                    if(!empty($v['product_link']) && !empty($grade) && $grade->grade<=3){
                        $style4="style='color:red'";
                    }

                    ?>

                    <tr class="pay_list" style="width: 10%">
                        <td><?=Html::a($img,['#'], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal', 'data-skus' => $v['sku'],'data-imgs' => $v['product_img']])?></td>
                        <td>
                            <?=Html::a($v['sku'],['#'], ['class' => "sales", 'data-sku' =>$v['sku'], 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal',]).SupplierGoodsServices::getSkuStatus($v['sku']) ?>
                        </td>
                        <td><a href="<?=Yii::$app->params['SKU_ERP_Product_Detail'].$v['sku']?>" target="_blank"><?=Vhelper::toSubStr($v['name'],1,5)?></a></td>
                        <td <?=$style4?>>
                            <?php
                            $plink=\app\models\PurchaseHistory::getPurchaseLink($v['sku']);
                            if($plink){
                                $prolink=$plink;
                            }else{
                                $prolink=$v['product_link'];
                            }
                            ?>
                            <a href="<?=$prolink?>" target="_blank"><?=Vhelper::toSubStr($prolink,1,5)?></a>
                        </td>
                        <td <?=$style2?>><?=$v['ctq'].Html::a('', ['#'],[
                                'data-toggle' => 'modal',
                                'data-target' => '#created-modal',
                                'class'=>'data-updatesd glyphicon glyphicon-zoom-in',
                                'title'=>'建议',
                                'sku'  => $v['sku'],
                                'pur'  => $v['pur_number'],
                            ]);?></td>
 
                        <td <?=$style3?>><?php echo round($v['price'],2).Html::a('', ['#'],[
                                    'data-toggle' => 'modal',
                                    'data-target' => '#created-modal',
                                    'class'=>'data-updatess glyphicon glyphicon-zoom-in',
                                    'title'=>'历史采购记录',
                                    'sku'  => $v['sku'],
                                ]);?></td>
                        <td><?php 
                            $last_order = \app\models\PurchaseOrderItems::find()
                                                        ->select('t.price')
                                                        ->from(\app\models\PurchaseOrderItems::tableName().' as t')
                                                        ->leftJoin('pur_purchase_order as t1','t1.pur_number = t.pur_number')
                                                        ->where(['t.sku'=>$v->sku,'t1.purchas_status'=>[3,5,6,7,8,9]])
                                                        ->orderBy('t1.id DESC')
                                                        ->one();
                            echo round((!empty($last_order->price)?$last_order->price:'首次采购'),2)
                        ?></td><!--上次采购单价-->  
                        <td><?=round($v['price']*$v['ctq'],2)?></td>
                        <td <?=$style1?>><?=$item['date_eta']?></td>
                    </tr>
                <?php } ?>
            </table>
            <div class="form-group field-purchaseorder-pur_number required col-md-12">
                <label class="control-label" for="purchaseorder-pur_number">采购确认备注:</label>
                <?php

                ECHO $item['orderNote']['note']?>
            </div>

            <div class="col-md-1">备注</div>
            <div class="form-group field-purchaseorder-pur_number required col-md-10">
                <textarea name="PurchaseOrder[audit_note][]" style="margin: 0px; width: 530px; height: 36px;"  placeholder="请写点什么吧"></textarea>
                <div class="help-block"></div>
            </div>
            <div class="col-md-2">提交操作:</div>
            <div class="col-md-8">
                <label class="btn btn-info"><input name="PurchaseOrders[purchas_status][<?=$ak?>]" type="radio" value="3" checked />审批通过(Ok)</label>
                <label class="btn btn-warning"><input name="PurchaseOrders[purchas_status][<?=$ak?>]" type="radio" value="4"/>审批不通过(Rollback) </label>
            </div>
        </div>
    <?php } ?>
    <div class="form-group">
        <?= Html::submitButton('确定审核',['class' => 'btn btn-success']) ?>
    </div>

    <!--<div class="form-group">
            <?/*= Html::submitButton('审批通过(Ok)',['class' => 'btn btn-success']) */?>
            <?/*= Html::submitButton('审批不通过(Rollback)', ['class' => 'btn btn-warning']) */?>
             <?php /*if($name =='audit'){*/?>
        <?/*= Html::submitButton('提交复审(Re-examine)', ['class' => 'btn btn-info']) */?>
        <?php /*}*/?>

    </div>-->
    <?php ActiveForm::end(); ?>
</div>
<?php
Modal::begin([
    'id' => 'created-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    //'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗
        'z-index' =>'-1',

    ],
]);

$url=Url::toRoute(['product/viewskusales']);
$imgurl=Url::toRoute(['purchase-suggest/img']);
$historys = Url::toRoute(['purchase-suggest/histor-purchase-info']);
$historyb = Url::toRoute(['purchase-suggest/suggest-quantity']);
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
    
    $(document).on('click','.data-updatess', function () {
        $.get('{$historys}', {sku:$(this).attr('sku')},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);

            }
        );
    });
    $(document).on('click','.data-updatesd', function () {
        $.get('{$historyb}', {sku:$(this).attr('sku'),'pur':$(this).attr('pur')},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);

            }
        );
    });
    
    $("#created-modal").on("hidden", function() {
        $(this).removeData("modal");
    });
    $("#created-modal").on("hidden.bs.modal",function(){
        $(document.body).addClass("modal-open");
    });

    $("[z-index='-1'] .close").on("click",function(){
        $("#created-modal").modal('hide');  
        return false;
    })
JS;
$this->registerJs($js);
?>