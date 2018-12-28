<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use app\models\PurchaseOrderItemsV2;
use app\models\PurchaseUser;

?>
<div class="stockin-view" style="max-height: 650px; overflow-y: scroll">
    <?php $form = ActiveForm::begin(); ?>
    <input type="hidden" id="page"  name="page" value="<?=$page?>">

    <input type="hidden" id="purchaseorder-purchas_status" class="form-control" name="PurchaseOrdersV2[purchas_status]" value="" style="display:none">
    <input type="hidden" id="purchaseorder-id" class="form-control" name="PurchaseOrdersV2[id]" value="<?=$model->id?>" style="display:none">
    <h4 class="modal-title">审核采购单</h4>
    <div class="row" style="margin: 10px 0">
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
                if(!empty($model->e_supplier_name) && $model->review_status < 3){
                    $m_style1="style='color:red'";
                }

                if(!empty($model->e_account_type) && $model->review_status < 3){
                    $m_style2="style='color:red'";
                }
            ?>
            <input type="text" <?=$m_style1?> disabled value="<?=$model->supplier_name?>">
        </div>

        <div class="col-md-4">
            <?php
                    $qsum=PurchaseOrderItemsV2::find()
                        ->select(['sum(qty) as qty, sum(ctq) as ctq'])
                        ->where(['pur_number'=>$model->pur_number])->asArray()->all();
                    ?>

            <label>SKU 数量</label>
            <input type="text" disabled value="<?=!empty($qsum[0]['qty']) ? $qsum[0]['qty'] : ''?>">
        </div>

        <div class="col-md-4">
            <label>采购数量</label>
            <input type="text" disabled value="<?=!empty($qsum[0]['ctq']) ? $qsum[0]['ctq'] : ''?>">
        </div>

        <div class="col-md-4">
            <label>总金额</label>
            <input type="text" disabled value="<?=round(PurchaseOrderItemsV2::getCountPrice($model->pur_number),2)?>">
        </div>

        <div class="col-md-4">
            <label>运&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;费</label>
            <input type="text" disabled value="<?=round(\app\models\PurchaseOrderShip::find()->where(['pur_number'=>$model->pur_number])->sum('freight'),2)?>">
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


    </div>

    <?php
    $items = [
        [
            'label'=>'<span class="glyphicon glyphicon-star" aria-hidden="true"></span>采购产品',
            'content'=>$this->render('_product',['purchaseOrderItems'=>$model->purchaseOrderItems,'code'=>$model->warehouse_code,'codes'=>$model->supplier_code,'date_eta'=>$model->date_eta,'e_date_eta'=>$model->e_date_eta,'grade'=>$grade,'review_status'=>$model->review_status]),

        ],
    ];

    echo TabsX::widget([
        'items'=>$items,
        'position'=>TabsX::POS_ABOVE,
        'encodeLabels'=>false
    ]);?>
    <div  class="col-md-12" style="margin-bottom:10px">采购确认备注:<?=!empty($model->orderNote->note) ? $model->orderNote->note : ''?></div>
    <div class="col-md-12"><?= $form->field($model, 'audit_note')->textarea(['cols'=>'5','rows'=>3,'maxlength' => true,'placeholder'=>'审批不通过请填写原因','style'=>'height:36px; width:530px'])->label('备注')?></div>

    <div class="form-group " style="clear: both">
        <?= Html::submitButton('审批通过(Ok)',['class' => 'btn btn-success']) ?>
        <?= Html::submitButton('审批不通过(Rollback)', ['class' => 'btn btn-warning']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <div class="stockin-update">
        <div class="col-md-2"><h4>操作日志</h4></div>
        <table class="table table-bordered" style="font-size: 10px">
            <thead>
            <tr>
                <th>操作人</th>
                <th width="70%">操作内容</th>
                <th>操作时间</th>
            </tr>
            </thead>
            <tbody class="pay">
            <?php if(!empty($log)){ ?>
                <?php foreach($log as $k=>$v){ ?>
                    <tr>
                        <td><?=$v['username'].PurchaseUser::getUserGrade($v['uid'])?></td>
                        <td><?=explode('：',$v['content'])[0]?></td>
                        <td><?=$v['create_date']?></td>
                    </tr>
                <?php }} ?>
            </tbody>

        </table>
    </div>

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
