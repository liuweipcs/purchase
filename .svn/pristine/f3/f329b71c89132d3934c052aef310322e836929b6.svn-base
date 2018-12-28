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
                        ->select(['sum(qty) as qty, sum(ctq) as ctq'])
                        ->where(['pur_number'=>$model->pur_number])->asArray()->all();
                    ?>

            <label>SKU数量</label>
            <input type="text" disabled value="<?=!empty($qsum[0]['qty']) ? $qsum[0]['qty'] : ''?>">
        </div>

        <div class="col-md-4">
            <label>采购数量</label>
            <input type="text" disabled value="<?=!empty($qsum[0]['ctq']) ? $qsum[0]['ctq'] : ''?>">
        </div>

        <div class="col-md-4">
            <label>总金额</label>
            <input type="text" disabled value="<?=round(PurchaseOrderItems::getCountPrice($model->pur_number),2)?>">
        </div>






        <div class="col-md-4">
            <label>运&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;费</label>
            <?php

                $freight = 0;
                $freight1 = \app\models\PurchaseOrderShip::find()->where(['pur_number'=>$model->pur_number])->sum('freight');
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


            <input type="text" disabled value="<?= $freight ?>">
        </div>

        <div class="col-md-4">
            <label>结款方式</label>
            <input type="text" <?=$m_style2?> disabled value="<?=$model->account_type ? \app\services\SupplierServices::getSettlementMethod($model->account_type) : ''?>">
        </div>

        <div class="col-md-4">
            <?php $findone=\app\models\PurchaseOrderOrders::findOne(['pur_number'=>$model->pur_number]);?>
            <label>订单号</label>
            <input type="text" disabled value="<?=!empty($findone) ? $findone->order_number : ''?>">
        </div>
        <div class="col-md-4">
            <?php $findone= !empty($model->is_drawback) ? ($model->is_drawback==2?'是' : '否') : '否';?>
            <label>是否退税</label>
            <input type="text" disabled value="<?=$findone;?>">
        </div>


        <?php
        $txt = '';
        $settlement_ratio = '';
        if(!empty($model->purchaseOrderPayType)) {
            $settlement_ratio = $model->purchaseOrderPayType->settlement_ratio;
            $real_price = $model->purchaseOrderPayType->real_price;
            $ratio_list = explode('+', $settlement_ratio);
            foreach($ratio_list as $percent) {
                $p = (float)$percent/100;
                $txt .= $real_price*$p.' / ';
            }
            if(count($ratio_list)>=3){
                $settlement_ratio = '结算方式(月结)+10%定金+发货前30%尾款+到货后60%尾款月结';
            }
        }
        ?>
        <div class="col-md-4">
            <label>结算比例</label>
            <input type="text" value="<?= $settlement_ratio ?>" disabled>
        </div>

        <div class="col-md-4">
            <label>结算比例结果</label>
            <input type="text" value="<?= $txt ?>" disabled>
        </div>


    </div>

    <?php
    $items = [
        [
            'label'=>'<span class="glyphicon glyphicon-star" aria-hidden="true"></span>采购产品',//税金税金税金
            'content'=>$this->render('_product',['model'=>$model,'purchaseOrderItems'=>$model->purchaseOrderItems,'code'=>$model->warehouse_code,'codes'=>$model->supplier_code,'date_eta'=>$model->date_eta,'e_date_eta'=>$model->e_date_eta,'grade'=>$grade,'supplier_code'=>$model->supplier_code]),

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
