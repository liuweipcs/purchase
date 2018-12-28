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

    <h4><?=Yii::t('app','付款详细')?></h4>

    <input type="hidden" class="form-control" name="PurchaseOrderPay[supplier_code]" value="<?=$model['supplier_code']?>"  readonly>
    <input type="hidden" class="form-control" name="PurchaseOrderPay[pur_number]" value="<?=$model['pur_number']?>"  readonly>
    <input type="hidden" class="form-control" name="PurchaseOrderPay[created_at]" value="<?=$model->purchaseOrder['created_at']?>"  readonly>
    <h5><span class="glyphicon glyphicon-duplicate"></span><?=Yii::t('app','基本资料')?></h5>
    <table class="table table-bordered">
        <tbody>
        <tr>
            <th scope="row"><?=Yii::t('app','结算对象:')?></th>
            <td><?=$model->purchaseOrder['supplier_name']?></td>
            <th scope="row"><?=Yii::t('app','补货方式:')?></th>
            <td><?=!empty($model->purchaseOrder['pur_type'])?PurchaseOrderServices::getPurType($model->purchaseOrder['pur_type']):''?></td>
            <th scope="row"><?=Yii::t('app','结算对象类型 :')?></th>
            <td>供应商</td>
        </tr>
        <tr>
            <th scope="row"><?=Yii::t('app','支付方式:')?></th>
            <td><?=SupplierServices::getDefaultPaymentMethod($model->pay_type)?></td>
            <th scope="row"><?=Yii::t('app','交易号:')?></th>
            <td></td>
            <th scope="row"><?=Yii::t('app','状态:')?></th>
            <td><?=PurchaseOrderServices::getPayStatus($model->pay_status)?></td>
        </tr>
        <tr>
            <th scope="row"><?=Yii::t('app','创建人:')?></th>
            <td><?=$model->purchaseOrder['creator']?></td>
            <th scope="row"><?=Yii::t('app','创建时间:')?></th>
            <td><?=$model->purchaseOrder['created_at']?></td>
            <th scope="row"><?=Yii::t('app','币种:')?></th>
            <td><input type="text" class="form-control" name="PurchaseOrderPay[currency]" value="<?=$model->currency?>"  readonly></td>
        </tr>
        <tr>
            <th scope="row"><?=Yii::t('app','付款人:')?></th>
            <td><?=!empty($model->payer)?BaseServices::getEveryOne($model->payer):''?></td>
            <th scope="row"><?=Yii::t('app','付款日期:')?></th>
            <td><?=$model->payer_time?></td>
        </tr>
        </tbody>
    </table>
    <h5><span class="glyphicon glyphicon-yen"></span><?=Yii::t('app','账户信息')?></h5>
    <table class="table table-bordered">
        <tbody>
        <tr>
        <?php
            if ($model->supplier->payment_method == 3) {
                $selfPayName = \app\models\DataControlConfig::find()->select('values')->where(['type'=>'self_pay_name'])->scalar();
                $self_pay_name_array = $selfPayName ? explode(',',$selfPayName) : ['合同运费','合同运费走私账'];
                $accountType = in_array($model->pay_name,$self_pay_name_array) ? 2 : (!empty($model->purchaseOrder->is_drawback)&&$model->purchaseOrder->is_drawback==1 ? 2 : 1);//通过是否退税判断银行卡类型
                $bankCardInfo = \app\models\SupplierPaymentAccount::find()
                    ->where(['supplier_code'=>$model->supplier_code])
                    ->andWhere(['account_type'=>$accountType])
                    ->andWhere(['status'=>1])->asArray()->one();
            }else{
                $bankCardInfo=['payment_platform_branch'=>'','account'=>'','account_name'=>''];
            }
         ?>
            <th scope="row" style="color: red"><?=Yii::t('app','收款方信息')?></th>
            <th scope="row"><?= $form->field($model, 'pay_types')->dropDownList(SupplierServices::getDefaultPaymentMethod(),['class'=>'form-control beneficiary', 'value'=>!empty($model->pay_type)?$model->pay_type:2]) ?></th>
            <th scope="row"><?=Yii::t('app','支行:')?></th>
            <td><input type="text" class="form-control e1" name="PurchaseOrderPay[e1]" value="<?=!empty($bankCardInfo['payment_platform_branch']) ? $bankCardInfo['payment_platform_branch'] : ''?>"  readonly></td>
            <th scope="row"><?=Yii::t('app','帐号 :')?></th>
            <td><input type="text" class="form-control e2" name="PurchaseOrderPay[e2]" value="<?=!empty($bankCardInfo['account']) ? $bankCardInfo['account'] :''?>"  readonly></td>
            <th scope="row"><?=Yii::t('app','开户名 :')?></th>
            <td><input type="text" class="form-control e3" name="PurchaseOrderPay[e3]" value="<?=!empty($bankCardInfo['account_name']) ? $bankCardInfo['account_name'] :''?>"  readonly></td>
        </tr>
        <tr>
            <th scope="row" style="color: red"><?=Yii::t('app','我方付款帐户信息')?></th>
            <th scope="row"><?= $form->field($model, 'pay_type')->dropDownList(BaseServices::getBankCard(null,'account_abbreviation'),['class'=>'form-control bank','value'=>!empty($bank) ? $bank->id : ''])->label('账号简称') ?></th>
            <th scope="row"><?=Yii::t('app','支行:')?></th>
            <td><input type="text" class="form-control banks" name="PurchaseOrderPay[branch]" value="<?= !empty($bank->branch)?$bank->branch:''?>"  readonly></td>

            <th scope="row"><?=Yii::t('app','开户账号:')?></th>
            <td><input type="text" class="form-control account" name="PurchaseOrderPay[account_number]" value="<?= !empty($bank->account_number)?$bank->account_number:''?>"  readonly></td>
            <th scope="row"><?=Yii::t('app','开户人:')?></th>
            <td><input type="text" class="form-control holder" name="PurchaseOrderPay[account_holder]" value="<?= !empty($bank->account_holder)?$bank->account_holder:''?>"  readonly></td>
        </tr>
        </tbody>
    </table>
    <h5><span class="glyphicon glyphicon-eur"></span><?=Yii::t('app','付款金额')?></h5>
    <ul class="list-group">
        <li class="list-group-item"><?=Yii::t('app','金额：')?><input type="text" class="form-control" style="color: red" name="PurchaseOrderPay[pay_price]" value="<?=$model->pay_price?>"  readonly></li>
        <li class="list-group-item"><?=Yii::t('app','付款时间：')?><?php
            echo DateTimePicker::widget([
                'name' => 'PurchaseOrderPay[payer_time]',
                'options' => ['placeholder' => ''],
                //注意，该方法更新的时候你需要指定value值
                'value' => date('Y-m-d H:i:s',time()),
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd hh:ii:ss',
                    'todayHighlight' => true
                ]
            ]);?></li>
        <li class="list-group-item"><?=Yii::t('app','说明：')?><?= $model->orderNote['note']?></li>
        <li class="list-group-item"><?= $form->field($model, 'payment_notice')->textarea(['cols'=>'1','rows'=>'2','id'=>'payment_notice', 'placeholder'=>'写点什么吧','required'=>true])->label('付款备注')?></li>
    </ul>
<!--    <input type="hidden"  class="form-control" name="PurchaseOrderPay[pur_number]" value="--><?//=$pur_number?><!--" />-->


    <input type="hidden" name="PurchaseOrderPay[id]" value="<?= $model->id ?>">


    <!-- 只有 6 已部分付款 和 4 待财务付款，这两种状态下的单才能操作 -->
    <?php if($model->pay_status == 4 || $model->pay_status == 6) { ?>

        <div class="form-group">

            <!-- pay_status状态值说明： 5 已付款 6 已部分付款 3 是财务驳回（这里不要再使用这个值了）-->
            <?= Html::submitButton('付款', ['class' => 'btn btn-success', 'value' => '5', 'name' => 'PurchaseOrderPay[pay_status]']); ?>
            <?php  Html::submitButton('部分付款', ['class' => 'btn btn-warning', 'value' => '6', 'name' => 'PurchaseOrderPay[pay_status]']); ?>

            <a id="btn-reject" href="javascript:void(0)" class="btn btn-link" data-payid="<?= $model->id ?>"> 驳回此请款单</a>

        </div>
    <?php } ?>


    <?php ActiveForm::end(); ?>

</div>
<?php
$url = Url::to(['get-bank']);
$urls = Url::to(['get-supplier-pay']);
$code = $model->purchaseOrder['supplier_code'];
$js = <<<JS
$(function() {
    
    $('#btn-reject').click(function() {
        var notice = $('#payment_notice').val();
        if(notice == '') {
            layer.alert('驳回请款单，务必填写备注');
            return false;
        }
        layer.load(0, {shade: false});
        var id = $(this).attr('data-payid');
        $.ajax({
            url: '/purchase-order-cashier-pay/cashier-reject',
            data: {id: id, payment_notice: notice},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                if(data.error==0) {
                    location.reload();
                } else {
                    layer.alert(data.message);
                }
            }
        });
    });
    
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
                $('.account').val(data.account_number);
                $('.holder').val(data.account_holder);
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



});


JS;
$this->registerJs($js);
?>