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
        <input type="hidden" id="purchaseorder-purchas_status" class="form-control" name="PurchaseOrderPay[pay_status]" value="" style="display:none">

        <h5><span class="glyphicon glyphicon-duplicate"></span><?=Yii::t('app','基本资料')?></h5>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <th scope="row"><?=Yii::t('app','结算对象:')?></th>
                <td><?=BaseServices::getSupplierName($model->supplier_code)?></td>
                <th scope="row"><?=Yii::t('app','公司名:')?></th>
                <td></td>
                <th scope="row"><?=Yii::t('app','结算对象类型 :')?></th>
                <td><?=$model->billing_object_type==1?'供应商':'服务商'?></td>
            </tr>
            <tr>
                <th scope="row"><?=Yii::t('app','结算方式:')?></th>
                <td></td>
                <th scope="row"><?=Yii::t('app','支付方式:')?></th>
                <td><?=SupplierServices::getDefaultPaymentMethod($model->beneficiary_payment_method)?></td>
                <th scope="row"><?=Yii::t('app','交易号:')?></th>
                <td><?=$model->transaction_number?></td>
            </tr>
            <tr>
                <th scope="row"><?=Yii::t('app','创建人:')?></th>
                <td><?=BaseServices::getEveryOne($model->create_id)?></td>
                <th scope="row"><?=Yii::t('app','创建时间:')?></th>
                <td><?=$model->create_time?></td>
            </tr>
            <tr>
                <th scope="row"><?=Yii::t('app','收款人:')?></th>
                <td>系统</td>
                <th scope="row"><?=Yii::t('app','交易日期:')?></th>
                <td><?=$model->pay_time?></td>
            </tr>
            </tbody>
        </table>
        <h5><span class="glyphicon glyphicon-yen"></span><?=Yii::t('app','账户信息')?></h5>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <th scope="row" style="color: red"><?=Yii::t('app','付款方信息')?></th>
                <th scope="row"><?= $form->field($model, 'create_id')->dropDownList(SupplierServices::getDefaultPaymentMethod(),['class'=>'form-control beneficiary','disabled'=>'disabled']) ?></th>
                <th scope="row"><?=Yii::t('app','支行:')?></th>
                <td><?=$model->beneficiary_branch?></td>
                <th scope="row"><?=Yii::t('app','帐号 :')?></th>
                <td><?=$model->beneficiary_account?></td>
                <th scope="row"><?=Yii::t('app','开户名 :')?></th>
                <td><?=$model->beneficiary_account_name?></td>
            </tr>
            <tr>
                <th scope="row" style="color: red"><?=Yii::t('app','我方收款帐户信息')?></th>
                <th scope="row"><?= $form->field($model, 'create_id')->dropDownList(BaseServices::getBankCard(),['class'=>'form-control bank','disabled'=>'disabled'])->label('银行卡') ?></th>
                <th scope="row"><?=Yii::t('app','支行:')?></th>
                <td><?=$model->our_branch?></td>

                <th scope="row"><?=Yii::t('app','账号简称:')?></th>
                <td><?=$model->our_account_abbreviation?></td>
                <th scope="row"><?=Yii::t('app','开户人:')?></th>
                <td><?=$model->our_account_holder?></td>
            </tr>
            </tbody>
        </table>
        <h5><span class="glyphicon glyphicon-eur"></span><?=Yii::t('app','付款金额')?></h5>
        <ul class="list-group">
            <li class="list-group-item"><?=Yii::t('app','金额：')?><span style="color: red"><?=$model->price?></span></li>
            <li class="list-group-item"><?=Yii::t('app','已核销金额：')?><span style="color: red"><?=!empty($model->write_off_price)?$model->write_off_price:'0.000'?></span></li>
            <li class="list-group-item"><?=Yii::t('app','币种：')?><span style="color: red"><?=$model->original_currency?></span></li>

        </ul>



        <?php ActiveForm::end(); ?>

    </div>
