<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use app\models\User;
use kartik\tabs\TabsX;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItems;

/* @var $this yii\web\View */
/* @var $model app\models\Stockin */

?>
<div class="stockin-view">
    <?php $form = ActiveForm::begin(); ?>
    <input type="hidden" id="page"  name="page" value="<?=$page?>">
    <input type="hidden" id="purchaseorder-purchas_status" class="form-control" name="PurchaseOrder[purchas_status]" value="" style="display:none">
    <input type="hidden" id="purchaseorder-id" class="form-control" name="PurchaseOrder[id]" value="<?=$model->id?>" style="display:none">
    <h4 class="modal-title">审核采购单</h4>
    <div class="row">
        <div class="col-md-4">
            <label>采购单号</label>
            <input type="text" disabled value="<?=$model->pur_number?>">
        </div>

        <div class="col-md-4">
            <label>采&nbsp;&nbsp;购&nbsp;&nbsp;&nbsp;员</label>
            <input type="text" disabled value="<?=$model->buyer?>">
        </div>

        <div class="col-md-4">
            <label>供应商</label>
            <?php
                $m_style1='';
                $m_style2='';
                if(!empty($model->e_supplier_name) && !empty($grade) && $grade->grade<3){
                    $m_style1="style='color:red'";
                }

               /* if(!empty($model->e_account_type) && !empty($grade) && $grade->grade<3){
                    $m_style2="style='color:red'";
                }*/
            ?>
            <input type="text" <?=$m_style1?> disabled value="<?=$model->supplier_name?>">
        </div>

        <div class="col-md-4">
            <?php
                    $qsum=PurchaseOrderItems::find()
                        ->select(['count(sku) as sku_count,sum(qty) as qty, sum(ctq) as ctq'])
                        ->where(['pur_number'=>$model->pur_number])->asArray()->all();
                    ?>

            <label>SKU数量</label>
            <input type="text" disabled value="<?=!empty($qsum[0]['sku_count']) ? $qsum[0]['sku_count'] : ''?>">
        </div>

        <div class="col-md-4">
            <label>采购数量</label>
            <input type="text" disabled value="<?=!empty($qsum[0]['ctq']) ? $qsum[0]['ctq'] : ''?>">
        </div>

        <div class="col-md-4">
            <label>总金额</label>
            <input type="text" disabled value="<?=round(PurchaseOrderItems::getCountPrice($model->pur_number),2)?>">
        </div>

        <?php

        $freight1 = \app\models\PurchaseOrderShip::find()->where(['pur_number'=>$model->pur_number])->select('freight')->scalar();
        $freight2 = 0;
        if(!empty($model->purchaseOrderPayType)) {
            $freight2 = $model->purchaseOrderPayType->freight ? $model->purchaseOrderPayType->freight : 0;
        }
        if($freight2) {
            $freight = $freight2;
        } else {
            $freight = $freight1;
        }

        ?>




        <div class="col-md-4">
            <label>运&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;费</label>
            <input type="text" disabled value="<?= $freight ?>">
        </div>

        <div class="col-md-4">
            <label>优&nbsp;&nbsp;惠&nbsp;&nbsp;&nbsp;额</label>
            <input type="text" disabled value="<?= $model->purchaseOrderPayType->discount ? $model->purchaseOrderPayType->discount : 0 ?>">
        </div>

        <div class="col-md-4">
            <label>结款方式</label>
            <input type="text" <?=$m_style2?> disabled value="<?=$model->account_type ? \app\services\SupplierServices::getSettlementMethod($model->account_type) : ''?>">
        </div>

        <div class="col-md-4">
            <label>支付方式</label>
            <input type="text" <?=$m_style2?> disabled value="<?=$model->pay_type ? \app\services\SupplierServices::getDefaultPaymentMethod($model->pay_type) : ''?>">
        </div>

        <div class="col-md-4">
            <label>供应商运输</label>
            <input type="text" disabled value="<?= $model->shipping_method ? \app\models\PurchaseOrder::getShippingMethod($model->pay_type) : '' ?>">
        </div>

        <div class="col-md-4">
            <label>账号</label>
            <input type="text" disabled value="<?=\app\models\PurchaseOrderPayType::getPurchaseAccount($model->pur_number)?>">
        </div>

        <div class="col-md-4">
            <label>拍&nbsp;&nbsp;单&nbsp;&nbsp;&nbsp;号</label>
            <input type="text" disabled value="<?=\app\models\PurchaseOrderPayType::getOrderNumber($model->pur_number)?>">
        </div>

        <div class="col-md-4">
            <label>是否加急</label>
            <input type="text" disabled value="<?=!empty($model->is_expedited)?$model->is_expedited==1?'否':'是':'' ?>">
        </div>

        <div class="col-md-4">
            <?php $findone=\app\models\PurchaseOrderOrders::findOne(['pur_number'=>$model->pur_number]);?>
            <label>订单号</label>
            <input type="text" disabled value="<?=!empty($findone) ? $findone->order_number : ''?>">
        </div>


    </div>

    <?php
    $items = [
        [
            'label'=>'<span class="glyphicon glyphicon-star" aria-hidden="true"></span>采购产品',
            'content'=>$this->render('_product',['purchaseOrderItems'=>$model->purchaseOrderItems,'code'=>$model->warehouse_code,'codes'=>$model->supplier_code,'date_eta'=>$model->date_eta,'e_date_eta'=>$model->e_date_eta,'grade'=>$grade,'supplier_code'=>$model->supplier_code]),

        ],
    ];

    echo TabsX::widget([
        'items'=>$items,
        'position'=>TabsX::POS_ABOVE,
        'encodeLabels'=>false
    ]);?>
    <div  class="col-md-4">采购确认备注:<?=!empty($model->orderNote->note) ? $model->orderNote->note : ''?></div>
    <div class="col-md-12"><?= $form->field($model, 'audit_note')->textarea(['cols'=>'5','rows'=>3,'maxlength' => true,'placeholder'=>'审批不通过请填写原因'])->label('备注')?></div>

   <!-- --><?php /*if(!empty($grade)){ */?>
    <div class="form-group " style="clear: both">
        <?= Html::submitButton('审批通过(Ok)',['class' => 'btn btn-success']) ?>
        <?= Html::submitButton('审批不通过(Rollback)', ['class' => 'btn btn-warning']) ?>
       <!-- <?php /*if($name =='audit'){*/?>
        <?/*= Html::submitButton('提交复审(Re-examine)', ['class' => 'btn btn-info']) */?>
        --><?php /*}*/?>
    </div>
   <!-- --><?php /*} */?>
    <?php ActiveForm::end(); ?>

<?php

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


JS;
$this->registerJs($js);
?>
