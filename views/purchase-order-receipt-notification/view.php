<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use kartik\datetime\DateTimePicker;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-form">

    <?php $form = ActiveForm::begin(); ?>

    <h4><?=Yii::t('app','收款详细')?></h4>
    <input type="hidden" id="purchaseorder-purchas_status" class="form-control" name="PurchaseOrderReceipt[pay_status]" value="" style="display:none">
    <input type="hidden" class="form-control" name="PurchaseOrderReceipt[supplier_code]" value="<?=$model->supplier_code?>"  readonly>
    <input type="hidden" class="form-control" name="PurchaseOrderReceipt[pur_number]" value="<?=$model->pur_number?>"  readonly>
    <input type="hidden" class="form-control" name="PurchaseOrderReceipt[requisition_number]" value="<?=$requisition_number?>"  readonly>

    <h5><span class="glyphicon glyphicon-duplicate"></span><?=Yii::t('app','基本资料')?></h5>
    <table class="table table-bordered">
        <tbody>
        <tr>
            <th scope="row"><?=Yii::t('app','采购单:')?></th>
            <td><?=$model->pur_number?></td>
            <th scope="row"><?=Yii::t('app','支付方式:')?></th>
            <td><?=SupplierServices::getDefaultPaymentMethod($model->pay_type)?></td>
            <th scope="row"><?=Yii::t('app','币种:')?></th>
            <td><input type="text" class="form-control" name="PurchaseOrderReceipt[currency]" value="<?=$model->currency?>"  readonly></td>

        </tr>
        <tr>
            <th scope="row"><?=Yii::t('app','供应商:')?></th>
            <td><?=$model->supplier['supplier_name']?></td>
            <th scope="row"><?=Yii::t('app','创建人:')?></th>
            <td><?=!empty($model->applicant)?BaseServices::getEveryOne($model->applicant):''?></td>
            <th scope="row"><?=Yii::t('app','创建时间:')?></th>
            <td><?=$model->application_time?></td>

        </tr>
        <tr>

            <th scope="row"><?=Yii::t('app','收款人:')?></th>
            <td><?=!empty($model->payer)?BaseServices::getEveryOne($model->payer):''?></td>
            <th scope="row"><?=Yii::t('app','收款日期:')?></th>
            <td><?=$model->payer_time?></td>
            <th scope="row"><?=Yii::t('app','状态:')?></th>
            <td><?=PurchaseOrderServices::getReceiptStatus($model->pay_status)?></td>

        </tr>
        </tbody>
    </table>
    <h5><span class="glyphicon glyphicon-yen"></span><?=Yii::t('app','账户信息')?></h5>
    <table class="table table-bordered">
        <tbody>
        <tr>
            <th scope="row" style="color: red"><?=Yii::t('app','付款方信息')?></th>
            <th scope="row"><?= $form->field($model, 'pay_types')->dropDownList(SupplierServices::getDefaultPaymentMethod(),['class'=>'form-control beneficiary'])->label('支付方式') ?></th>
            <th scope="row"><?=Yii::t('app','支行:')?></th>
            <td><input type="text" class="form-control e1" name="PurchaseOrderReceipt[e1]" value=""  readonly></td>
            <th scope="row"><?=Yii::t('app','帐号 :')?></th>
            <td><input type="text" class="form-control e2" name="PurchaseOrderReceipt[e2]" value=""  readonly></td>
            <th scope="row"><?=Yii::t('app','开户名 :')?></th>
            <td><input type="text" class="form-control e3" name="PurchaseOrderReceipt[e3]" value=""  readonly></td>
        </tr>
        <tr>
            <th scope="row" style="color: red"><?=Yii::t('app','我方收款帐户信息')?></th>
            <th scope="row">
                账号简称
                <?= Html::dropDownList('bank',$bank->id,BaseServices::getBankCard(null,'account_abbreviation'),['class' => 'form-control bank']) ?>
            </th>
            <th scope="row"><?=Yii::t('app','支行:')?></th>
            <td><input type="text" class="form-control banks" name="PurchaseOrderReceipt[branch]" value="<?=$bank->branch?>"  readonly></td>
            <th scope="row"><?=Yii::t('app','银行卡号:')?></th>
            <td><input type="text" class="form-control account_number" name="PurchaseOrderReceipt[account_number]" value="<?=$bank->account_number?>"  readonly>
                <input type="hidden" class="form-control account" name="PurchaseOrderReceipt[account_abbreviation]" value="<?=$bank->account_abbreviation?>"  readonly></td>
            <th scope="row"><?=Yii::t('app','开户人:')?></th>
            <td><input type="text" class="form-control holder" name="PurchaseOrderReceipt[account_holder]" value="<?=$bank->account_holder?>"  readonly></td>
        </tr>
        </tbody>
    </table>
    <h5><span class="glyphicon glyphicon-eur"></span><?=Yii::t('app','收款金额')?></h5>
    <ul class="list-group">
        <li class="list-group-item"><?=Yii::t('app','金额：')?><input type="text" class="form-control" style="color: red" name="PurchaseOrderReceipt[pay_price]" value="<?=$model->pay_price?>"  readonly></li>
        <li class="list-group-item"><?=Yii::t('app','收款时间：')?><?php
            echo DateTimePicker::widget([
                'name' => 'PurchaseOrderReceipt[payer_time]',
                'options' => ['placeholder' => ''],
                //注意，该方法更新的时候你需要指定value值
                'value' => date('Y-m-d H:i:s',time()),
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd HH:ii:ss',
                    'todayHighlight' => true
                ]
            ]);?></li>
        <li class="list-group-item"><?=Yii::t('app','说明：')?><?= $model->review_notice?></li>
        <li class="list-group-item"><?= $form->field($model, 'payment_notice')->textarea(['cols'=>'1','rows'=>'2','placeholder'=>'操作作废时,必须填写备注','required'=>true, 'id' => 'notice'])->label('收款备注')?></li>
    </ul>
<!--    <input type="hidden"  class="form-control" name="PurchaseOrderPay[pur_number]" value="--><?//=$pur_number?><!--" />-->

    <?php if($model->pay_status == 1) { ?>
    <div class="form-group">
        <?= Html::submitButton('确认到帐',['class' => 'btn btn-success','value'=>'3','name'=>'PurchaseOrderReceipt[pay_status]']) ?>
        <?= Html::submitButton('作废', ['class' => 'btn btn-warning','value'=>'4','name'=>'PurchaseOrderReceipt[pay_status]']) ?>


        <span class="btn btn-danger" id="reject" data-id="<?= $model->id ?>">驳回</span>

    </div>
    <?php } ?>



    <?php ActiveForm::end(); ?>

</div>
<?php
$url = Url::to(['purchase-order-cashier-pay/get-bank']);
$urls = Url::to(['purchase-order-cashier-pay/get-supplier-pay']);
$code = $model->supplier_code;
$js = <<<JS
$(function(){

    //切换我方银行账户信息
    $(".bank").change(function(){

       var id = $(this).val();
       $.ajax({
               url:'{$url}',
            type:'GET', //GET
            async:true,    //或false,是否异步
            data:{
                id:id,
            },
            timeout:5000,    //超时时间
            dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
            success:function(data,textStatus,jqXHR){

                $('.banks').val(data.branch);
                $('.account').val(data.account_abbreviation);
                $('.holder').val(data.account_holder);
                $('.account_number').val(data.account_number);
            },
        });
    });
    
    
    
    //切换收款方账户信息
    $(".beneficiary").change(function(){
        

       var id = $(this).val();
       var code ='{$code}';
       $.ajax({
               url:'{$urls}',
            type:'GET', //GET
            async:true,    //或false,是否异步
            data:{
                id:id,
                code:code,
            },
            timeout:5000,    //超时时间
            dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
            success:function(data,textStatus,jqXHR){
                if(data)
                {
                  $('.e1').val(data.payment_platform_branch);
                  $('.e2').val(data.account);
                  $('.e3').val(data.account_name);

                } else{
                     $('.e1').val('');
                     $('.e2').val('');
                     $('.e3').val('');

                }

            },
    });
    });
    
    $('#reject').click(function() {
        var id = $(this).attr('data-id');
        notice = $('#notice').val();
        if(notice == '') {
            layer.alert('驳回操作，请填写备注。');
            return false;
        }
        $.ajax({
            url: '/purchase-order-receipt-notification/reject',
            data: {id: id, payer_notice: notice},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                if(data.error == 0) {
                    location.reload();
                } else {
                    layer.alert(data.message);
                }
            }
        });
    });
});


JS;
$this->registerJs($js);
?>