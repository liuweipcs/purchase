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
    <h4><?=Yii::t('app','合并付款')?></h4>
    <table class="table table-bordered">
        <tbody>
        <tr>
            <th scope="row"><?=Yii::t('app','NO')?></th>
            <th scope="row"><?=Yii::t('app','采购单号')?></th>
            <th scope="row"><?=Yii::t('app','供应商')?></th>
            <th scope="row"><?=Yii::t('app','支付方式')?></th>
            <th scope="row"><?=Yii::t('app','金额')?></th>
            <th scope="row"><?=Yii::t('app','备注')?></th>

        </tr>
        <?php
                 foreach($model as $k=>$v){
        ?>
        <tr>
            <td scope="row"><?=$k+1?></td>
            <td scope="row"><input type="text" id="supplier-supplier_name" class="form-control" name="PurchaseOrderPays[pur_number][]" value="<?=$v['pur_number']?>" readonly  maxlength="30"  aria-required="true"></td>
            <td scope="row"><input type="hidden" id="supplier-supplier_name" class="form-control" name="PurchaseOrderPays[supplier_code][]" value="<?=$v['supplier_code']?>" readonly maxlength="30"  aria-required="true"><?=BaseServices::getSupplierName($v['supplier_code'])?></td>
            <td scope="row"><?=!empty($v['pay_type'])?SupplierServices::getDefaultPaymentMethod($v['pay_type']):''?></td>
            <td scope="row"><input type="text" id="supplier-supplier_name" class="form-control" name="PurchaseOrderPays[pay_price][]" value="<?=$v['pay_price']?>" readonly maxlength="30"  aria-required="true"></td>
            <td scope="row"><input type="text" id="supplier-supplier_name" class="form-control" name="PurchaseOrderPays[create_notice][]" value="<?=$v['create_notice']?>"  aria-required="true"></td>

        </tr>
                     <input type="hidden" name="PurchaseOrderPays[id][]" value="<?= $v['id'] ?>">
        <?php }?>
        </tbody>
    </table>
    <h5><span class="glyphicon glyphicon-yen"></span><?=Yii::t('app','账户信息')?></h5>
    <table class="table table-bordered">
        <tbody>
        <tr>
            <th scope="row" style="color: red"><?=Yii::t('app','收款方信息')?></th>
            <th scope="row"><?= $form->field($models, 'pay_type')->dropDownList(SupplierServices::getDefaultPaymentMethod(),['class'=>'form-control beneficiary','options' => [2 => ['selected' => 'selected']]]) ?></th>
            <th scope="row"><?=Yii::t('app','支行:')?></th>
            <td><input type="text" class="form-control e1" name="PurchaseOrderPay[e1]" value=""  readonly></td>
            <th scope="row"><?=Yii::t('app','帐号 :')?></th>
            <td><input type="text" class="form-control e2" name="PurchaseOrderPay[e2]" value=""  readonly></td>
            <th scope="row"><?=Yii::t('app','开户名 :')?></th>
            <td><input type="text" class="form-control e3" name="PurchaseOrderPay[e3]" value=""  readonly></td>
        </tr>
        <tr>
            <th scope="row" style="color: red"><?=Yii::t('app','我方付款帐户信息')?></th>
            <th scope="row"><?= $form->field($models, 'pay_types')->dropDownList(BaseServices::getBankCard(null,'account_abbreviation'),['class'=>'form-control bank','value'=>$bank->id])->label('账号简称') ?></th>
            <th scope="row"><?=Yii::t('app','支行:')?></th>
            <td><input type="text" class="form-control banks" name="PurchaseOrderPay[branch]" value="<?=$bank->branch?>"  readonly></td>

            <th scope="row"><?=Yii::t('app','开户账号:')?></th>
            <td><input type="text" class="form-control account" name="PurchaseOrderPay[account_number]" value="<?=$bank->account_number?>"  readonly></td>
            <th scope="row"><?=Yii::t('app','开户人:')?></th>
            <td><input type="text" class="form-control holder" name="PurchaseOrderPay[account_holder]" value="<?=$bank->account_holder?>"  readonly></td>
        </tr>
        </tbody>
    </table>
    <ul class="list-group">
        <li class="list-group-item"><?=Yii::t('app','付款时间：')?><?php
            echo DateTimePicker::widget([
                'name' => 'pay_time_all',
                'options' => ['placeholder' => ''],
                //注意，该方法更新的时候你需要指定value值
                'value' => date('Y-m-d H:i:s',time()),
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd HH:ii:ss',
                    'todayHighlight' => true
                ]
            ]);?></li>
    </ul>
    <p style="color: red;">要么就全额付款,要么就部分付款,不能这个全额那个部分,暂时无法支持这种操作,可以分别付款</p>
    <div class="form-group">
        <?= Html::submitButton('全额付款',['class' => 'btn btn-success','value'=>'5','name'=>'PurchaseOrderPay[pay_status]']) ?>
        <?php //Html::submitButton('部分付款', ['class' => 'btn btn-warning','value'=>'6','name'=>'PurchaseOrderPay[pay_status]']); ?>
    </div>
<?php ActiveForm::end(); ?>
    <?php
    $url = Url::to(['get-bank']);
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
                $('.account').val(data.account_number);
                $('.holder').val(data.account_holder);
            },
        });
    });





});


JS;
    $this->registerJs($js);
    ?>
