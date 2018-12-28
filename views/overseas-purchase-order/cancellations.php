<?php
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use yii\helpers\Html;
use app\models\WarehouseResults;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;

$noPayMoney = $orderInfo['order_real_money']-$payInfo['hasPaidMoney']; // 订单未付款

$pay_price = PurchaseOrderPay::getOrderPaidMoney($orderInfo['pur_number']); //订单总额
$item_price = PurchaseOrder::getOrderTotalPrice($orderInfo['pur_number']); //已付款的
?>
<?= $this->render('_cancellations-public', ['orderInfo' => $orderInfo, 'payInfo' => $payInfo]); ?>
<?php ActiveForm::begin([
    'action'=>['cancellations'],
    'id'=>'submit-form']);
?>
    <h5>取消未到货</h5>
    <!--<div class="container-fluid">-->

    <div class="row">
        <div class="col-md-12">
            <h5>取消明细</h5>
            <table class="table table-bordered table-condensed">
                <tr>
                    <th class="col-md-2">取消类型</th>
                    <td colspan="3"><input type="radio" name="is_all_cancel" value="1" id="part_cancel" checked>部分取消</td>
                    <td colspan="3"><input type="radio" name="is_all_cancel" value="2" id="all_cancel" pay_price="0" item_price="<?=$item_price ?>">全部取消</td>
                </tr>
                <tr>
                    <th class="col-md-2">运费</th>
                    <td><input type="text" name="freight" class="form-control freight" placeholder="请输入要退的运费" value="0"></td>
                </tr>
                <tr>
                    <th class="col-md-2">优惠额</th>
                    <td><input type="text" name="discount" class="form-control discount" placeholder="请输入要退的优惠额" value="0"></td>
                </tr>
                <tr>
                    <th class="col-md-2">SKU</th>
                    <th class="col-md-2">单价</th>
                    <th class="col-md-2">名称</th>
                    <th class="col-md-1">订单数量</th>
                    <th class="col-md-1">已取消数量</th>
                    <th class="col-md-1">入库数量</th>
                    <th class="col-md-2">取消数量</th>
                </tr>
                <tbody>
                <?php
                $cancel_price = 0;
                $cancel_total = 0;
                $pur_number = $orderInfo['pur_number'];
                $itemsPriceInfo = PurchaseOrderItems::getItemsPrice($pur_number);
                ?>
                <?php foreach($orderInfo['purchaseOrderItems'] as $k => $v): 

                foreach ($itemsPriceInfo as $iv) {
                    if ($v['pur_number']==$iv['pur_number'] && $v['sku']==$iv['sku'] ) {
                       $v['price'] = $iv['price'];
                    }
                }

                ?>
                    <tr>
                        <td><?= $v['sku'] ?></td>
                        <td class="cancel_price"><?=$v['price']?></td>
                        <td><?= $v['name'] ?></td>
                        <td class="ctq"><?= $v['ctq'] ?></td><!--订单数量-->
                        <td class="quxiao_num"><?= $v['quxiao_num'] ?></td><!--已取消数量-->
                        <?php $v['ruku_num'] = WarehouseResults::getInstockInfo($v['pur_number'],$v['sku'])['instock_qty_count'] ?>
                        <td class="ruku_num"><?=$v['ruku_num'] ?></td>
                        <td class="ctqs">
                            <?php $res_ctq = $v['ctq']-$v['quxiao_num']-$v['ruku_num'];?>
                            <input type="number" name="cancel_ctq[cancel_ctq][<?=$k?>]" min="1" max="<?=$res_ctq?>" class="form-control cancel_ctq" value="<?=$res_ctq?>">
                        </td><!--取消数量-->
                        <input type="hidden" name="cancel_ctq[pur_number][<?= $k ?>]" value="<?= $v['pur_number'] ?>">
                        <input type="hidden" name="cancel_ctq[sku][<?= $k ?>]" value="<?= $v['sku'] ?>">
                        <input type="hidden" name="cancel_ctq[price][<?= $k ?>]" value="<?= $v['price'] ?>">
                        <input type="hidden" name="cancel_ctq[ctq][<?= $k ?>]" value="<?= $v['ctq'] ?>">
                        <!--<input type="hidden" name="cancel_ctq[quxiao_num][<?/*= $k */?>]" value="<?/*= $v['quxiao_num'] */?>">-->
                    </tr>
                    <?php $cancel_price += $v['price']*$res_ctq; $cancel_total+=$res_ctq;?>
                <?php endforeach; ?>
                <tr>
                    <th class="col-md-2 total" colspan="7" style="color: red;">
                        取消件数：<?=$cancel_total?>件<br />取消金额：<?=$cancel_price ?>RMB
                        <input type="hidden" name="cancel_total_price" value="<?=$cancel_price?>">
                    </th>
                </tr>
                <tr>
                    <th class="col-md-2">备注</th>
                    <td colspan="6"><textarea name="buyer_note" class="form-control" rows="3" id="confirm_note" placeholder="请填写备注"></textarea></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <input type="hidden" name="pur_number" value="<?= $orderInfo['pur_number'] ?>">

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::button(Yii::t('app', '提交'), ['class' => 'btn btn-primary submit']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>
    <!--</div>-->
<?php
ActiveForm::end();
?>

<?php
$js = <<<JS
$(function() {
    var bool=true;
   //提交
   $('.submit').click(function() {
      if($.trim($('#confirm_note').val()) == '') {
           layer.alert('备注不能为空');
           return false;
      }else {
          $('#submit-form').submit();
          $(this).prop('disabled',true);
        
      }
   });
   //部分取消 启用编辑
   $('#part_cancel').click(function() {
       $('.cancel_ctq').attr('readonly', false);
       setTotal(bool=false);

   });
   //全部取消 禁用编辑
   $('#all_cancel').click(function() {
       $(".table tbody tr .ctqs").each(function(){
           var ctq = $(this).parent().find('.ctq').html();
           var quxiao_num = $(this).parent().find('.quxiao_num').html();
           var ruku_num = $(this).parent().find('.ruku_num').html();
           var ctq2 = ctq-quxiao_num-ruku_num;
           $(this).find('input[class*=cancel_ctq]').val(ctq2);
        });
       $('.cancel_ctq').attr('readonly', false);
       var pay_price=$(this).attr('pay_price');
       var item_price=$(this).attr('item_price');
       total_pay_item_price = item_price-pay_price;
        setTotal(bool=true);
   });
   //统计取消的数量和金额
   $('.cancel_ctq').change(function() {
        var obj = $(this);
    var objTr = $(this).parent().parent();
    //获取单价
    var unitPrice = objTr.find(".cancel_price").html();
        
    var sum = accMul(unitPrice,obj.val());
    //objTr.find(".payable_amount").val(sum);
     setTotal(bool=false);
   });
   $('.freight').change(function() {
        setTotal(bool=false);
   });
   $('.discount').change(function() {
        setTotal(bool=false);
   });
    
  function   accMul(arg1,arg2){
      //乘法
      var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
      try { m += s1.split(".")[1].length;}
      catch (e) {}
      try {m += s2.split(".")[1].length;}
      catch (e) {}
      return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
    }
    //数量修改时统计
    function setTotal(bool){
        var s=0;
        var cancel_ctq = 0;
        var cancel_price=0;
        $(".table tbody tr .ctqs").each(function(){
            cancel_ctq += parseInt(($(this).find('input[class*=cancel_ctq]').val())); //取消数量
            cancel_price = parseFloat($(this).parent().find('.cancel_price').html()); //取消单价
           s += parseInt(($(this).find('input[class*=cancel_ctq]').val())) * parseFloat($(this).parent().find('.cancel_price').html());
        });
        var freight = parseFloat($('.freight').val());
        var discount = parseFloat($('.discount').val());
        var total_price = s + freight - discount;
        $(".total").html('取消件数：'+ cancel_ctq + '件<br />取消金额：'+ total_price.toFixed(2)+'RMB<input type="hidden" name="cancel_total_price" value="'+ total_price +'">');
       
    }
});
JS;
$this->registerJs($js);
?>