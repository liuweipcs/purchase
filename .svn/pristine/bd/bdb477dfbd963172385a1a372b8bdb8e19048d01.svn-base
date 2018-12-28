<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/9
 * Time: 11:04
 */
use yii\widgets\ActiveForm;
?>
<style>
    .pay_span {padding:0px 1% 0px 1%;color:black;float:left;font-weight:bold;}
</style>
<?php
if(!empty($model)) {
?>
    <?php $form = ActiveForm::begin(['id'=>'fuiou_pay_form']); ?>
<?=\yii\helpers\Html::hiddenInput('ids',implode(',',$ids))?>
    <p class="glyphicon glyphicon-folder-open"></p>&nbsp;&nbsp;&nbsp;基本资料
    <table class="table">
        <tr>
            <td>供应商名称</td>
            <td><?php
                    $supplier_code = isset($model[0]) ? $model[0]->supplier_code :'';
                    $supplier_name = \app\models\Supplier::find()->select('supplier_name')->where(['supplier_code'=>$supplier_code])->scalar();
                    $ufxiouCharge  = \app\models\DataControlConfig::find()->select('values')
                        ->where(['type'=>'ufxiouCharge'])->scalar();
                    $ufxiouCharge = $ufxiouCharge ? $ufxiouCharge : 1 ;
                    echo $supplier_name ? $supplier_name :'';
                ?>
            </td>
        </tr>
    </table>
    <p class="glyphicon glyphicon-bell">付款信息<p/>
    <table class="table">
        <thead>
            <th>NO</th>
            <th>采购单号</th>
            <th>申请金额（单位：元）</th>
            <th>运费</th>
            <th>优惠额</th>
            <th>结算方式</th>
            <th>支付方式</th>
            <th>支付名称</th>
            <th>请款时间</th>
            <th>请款人</th>
            <th>请款备注</th>
            <th>付款备注</th>
        </thead>
        <tbody>
        <?php $totalPrice = 0;$totalApplyPrice=0;?>
        <?php foreach ($model as $key=>$value){?>
        <tr>
            <?php
            $totalPrice += 1000*$value->pay_price;
            $totalApplyPrice  += 1000*$value->pay_price;
            ?>
            <td><?= $key+1?></td>
            <td><?= $value->pur_number?></td>
            <td><?= $value->pay_price?></td>
            <?php
                $is_check_freight = false;
                $is_check_discount = false;
                $payInfo = \app\models\PurchaseOrderPayDetail::find()->where(['requisition_number'=>$value->requisition_number,'pur_number'=>$value->pur_number])->one();
                $freight = empty($payInfo)||empty($payInfo->freight) ? 0 : $payInfo->freight;

                $discount = empty($payInfo)||empty($payInfo->discount) ? 0 : $payInfo->discount;

                $purchaseOrderInfo = \app\models\PurchaseOrderPayType::find()->where(['pur_number'=>$value->pur_number])->one();
                $orderFreight = empty($purchaseOrderInfo)||empty($purchaseOrderInfo->freight) ? 0 : $purchaseOrderInfo->freight;
                $orderDiscount = empty($purchaseOrderInfo)||empty($purchaseOrderInfo->discount) ? 0 : $purchaseOrderInfo->discount;
                $freightExcep = false;
                $discountExcep = false;
                if(!empty($freight)&&$freight==$orderFreight){
                    $is_check_freight = true;
                    $totalPrice+=1000*$freight;
                }
                if(!empty($discount)&&$discount==$orderDiscount){
                    $is_check_discount = true;
                    $totalPrice-=1000*$discount;
                }
                if(!in_array($freight,[0,$orderFreight])){
                    $freightExcep = true;
                }
                if(!in_array($discount,[0,$orderDiscount])){
                    $discountExcep = true;
                }
            ?>
            <td><input type="checkbox" class="pay_freight pay_ext" value="<?=round($orderFreight*1000)?>" <?= $is_check_freight ? 'checked="checked"' : '' ?>" ><?=$freightExcep ? "<p style='color: red'>".$orderFreight."</p>" :  "<p style='color: green'>".$orderFreight."</p>" ?></td>
            <td><input type="checkbox" class="pay_discount pay_ext" value="<?=round($orderDiscount*1000)?>" <?= $is_check_discount ? 'checked="checked"' : '' ?>" ><?=$discountExcep ? "<p style='color: red'>".$orderDiscount."</p>" :  "<p style='color: green'>".$orderDiscount."</p>" ?></td>
            <td><?= \app\services\SupplierServices::getSettlementMethod($value->settlement_method)?></td>
            <td><?= \app\services\SupplierServices::getDefaultPaymentMethod($value->pay_type)?></td>
            <td><?= $value->pay_name?></td>
            <td><?= $value->application_time?></td>
            <td><?php
                $username=\app\models\User::find()->select('username')->where(['id'=>$value->applicant])->scalar();
                echo $username?$username :'';
                ?>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
    <span class="pay_detail" totalprice="<?=round($totalApplyPrice)?>" charge =<?=$ufxiouCharge*1000?> >
    <div style="border: solid red 1px ;height: 80px;text-align:center;margin-bottom: 20px">
        <div style="line-height: 40px;float:right;width: 100%;">
        <span class="pay_span" style="padding-left: 15%">请款金额(元)：</span>
        <span class="pay_span apply_price" style="color: red"><?=round($totalPrice/1000,2)?></span>
        <span class="pay_span" style="padding-left: 10%">到账金额(元)：</span>
        <span class="pay_span arrival_price" style="color: red"><?= round(($totalPrice-$ufxiouCharge*1000)/1000,2)?></span>
        <span class="pay_span" style="padding-left: 10%">手续费(元):</span>
        <span class="pay_span" style="color: red"><?= $ufxiouCharge?></span>
        </div>
        <div style="line-height: 40px;float:left;width: 100%;" >
            <span class="pay_span " style="padding-left: 15%" >我方承担手续费(元):</span>
            <span class="pay_span">
                <?= \yii\helpers\Html::radioList('Fuiou[charge]','01',['01'=>'否','02'=>'是'],['class'=>'pay_ext'])?>
            </span>
            <span class="pay_span " style="padding-left: 20%">实际扣除金额(元):</span>
            <span class="pay_span tran_price" style="color: red"><?=round(($totalPrice)/1000,2)?></span>
        </div>
    </div>
    <div style="clear: both"></div>
    <?= $this->render('_payee_info',['totalPrice'=>$totalPrice-$ufxiouCharge*1000,'supplier_code'=>$model[0]->supplier_code,'is_drawback'=>$is_drawback,'bank'=>$bank,'model'=>$model])?>
    <?= \yii\helpers\Html::submitButton('确认付款', ['class' => 'pay_submit btn btn-warning']); ?>
    <?php ActiveForm::end();?>
<?php
}
?>
<?php
$js = <<<JS

    //手续费变更获取最新转账金额
    $(document).on('change','.pay_ext',function() {
        //请款金额
        var totalPrice = Number($('.pay_detail').attr('totalprice'));
        //手续费
        var charge  = Number($('.pay_detail').attr('charge'));
        //总运费
        var payFreight = 0;
        //总优惠金额
        var payDiscount = 0;
        //我方支付手续费
        var ourPayCharge  = 0;
        //供应商支付付手续费
        var payeePayCharge  = 0;
        $('.pay_freight').each(function() {
            var freight = Number($(this).val());
            if($(this).is(':checked')){
                payFreight+=freight;
            }
        });
        $('.pay_discount').each(function() {
            var discount = Number($(this).val());
            if($(this).is(':checked')){
                payDiscount+=discount;
            }
        });
        $('[name="Fuiou[charge]"]').each(function() {
            //供应商承担手续费
          if($(this).is(':checked')&&$(this).val()=='01'){
              payeePayCharge+=charge;
          }
          //我方承担手续费
          if($(this).is(':checked')&&$(this).val()=='02'){
              ourPayCharge+=charge;
          }
        });
        //供应商到账金额
        $('.arrival_price').text(Math.round((totalPrice+payFreight-payDiscount-payeePayCharge)/10)/100);
        //我方实际扣除金额
        $('.tran_price').text(Math.round((totalPrice+payFreight-payDiscount+ourPayCharge)/10)/100);
        //接口传递金额（不含手续费）
        $('[name="Fuiou[amt]"]').val(Math.round((totalPrice+payFreight-payDiscount+ourPayCharge-charge)/10)/100);
        //请款金额
        $('.apply_price').text(Math.round((totalPrice+payFreight-payDiscount)/10)/100);
    });
    var click_index = 0;
    $(document).on('submit','#fuiou_pay_form',function() {
        click_index++;
        $(".pay_submit").attr('disabled','disabled');
        if(click_index>1){
            layer.msg('不可多次提交');
            return false;
        }
      var loading = layer.load(6 , {shade : [0.5 , '#BFE0FA']});
    });
JS;
$this->registerJs($js);
?>
