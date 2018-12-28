<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderCancelSub;

$item_price = PurchaseOrder::getOrderTotalPrice($items_details[0]['pur_number']); //订单总额
$pay_price = PurchaseOrderPay::getOrderPaidMoney($items_details[0]['pur_number']); //已付款的
$cancel_price = PurchaseOrderCancelSub::getCancelPriceOrder($items_details[0]['pur_number']); //已取消总金额
$demand_status = [1,2,3,4,5,6,7,8];
?>
<?php echo $this->render('_public', ['order_details' => $order_details]); ?>
<?php ActiveForm::begin([
    'action'=>['cancel-order'],
    // 'method' => 'get',
    'id'=>'submit-form']);
?>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-condensed">
                
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
                $freight = 0;
                $discount = 0;
                ?>
                <?php foreach($items_details as $k => $v):?>
                    <tr>
                        <td><?= $v['sku'] ?></td>
                        <td class="cancel_price"><?=$v['price']?></td>
                        <td><?= $v['name'] ?></td>
                        <td class="ctq"><?= $v['ctq'] ?></td><!--订单数量-->
                        <td class="quxiao_ctq"><?= $v['quxiao_ctq'] ?></td><!--已取消数量-->
                        <td class="instock_qty_count"><?=$v['instock_qty_count'] ?></td><!--入库数量-->
                        <td class="ctqs">
                            <?php $res_ctq = $v['ctq']-$v['quxiao_ctq']-$v['instock_qty_count'];?>

                            <?php if(in_array($v['demand_status'], $demand_status)):?>
                            <input type="number" name="cancel_ctq[cancel_ctq][<?=$k?>]" min="1" max="<?=$res_ctq?>" class="form-control cancel_ctq" value="<?=$res_ctq?>" readonly>
                            <?php else:?>
                              <input type="number" name="cancel_ctq[cancel_ctq][<?=$k?>]" min="1" max="<?=$res_ctq?>" class="form-control cancel_ctq" value="<?=$res_ctq?>">
                            <?php endif;?>

                        </td><!--取消数量-->
                        <input type="hidden" name="cancel_ctq[pur_number][<?= $k ?>]" value="<?=$items_details[0]['pur_number'] ?>">
                        <input type="hidden" name="cancel_ctq[sku][<?= $k ?>]" value="<?= $v['sku'] ?>">
                        <input type="hidden" name="cancel_ctq[price][<?= $k ?>]" value="<?= $v['price'] ?>">
                        <input type="hidden" name="cancel_ctq[ctq][<?= $k ?>]" value="<?= $v['ctq'] ?>">
                        <input type="hidden" name="cancel_ctq[demand_number][<?= $k ?>]" value="<?= $v['demand_number'] ?>">
                        <input type="hidden" name="cancel_ctq[old_demand_status][<?= $k ?>]" value="<?= $v['demand_status'] ?>">
                    </tr>
                    <?php 
                      $cancel_price += $v['price']*$res_ctq; 
                      $cancel_total +=$res_ctq;
                      $freight += $v['freight']; 
                      $discount +=$v['discount'];
                    ?>
                <?php endforeach; ?>

                <tr>
                    <th class="col-md-2">运费</th>
                    <td>
                      <input type="text" name="freight" class="form-control freight" value=""></td>
                      <td><span class="text-danger">*</span>取消全部订单退运费，取消部分订单不退运费</td>
                </tr>
                <tr>
                    <th class="col-md-2">优惠额</th>
                    <td><input type="text" name="discount" class="form-control discount" value=""></td>
                    <td><span class="text-danger">*</span>请注意优惠额填写</td>
                </tr>
                <tr>
                    <th class="col-md-2 total" colspan="7" style="color: red;">
                        取消件数：<?=$cancel_total?>件<br />取消金额：<?=$cancel_price+$freight-$discount ?>RMB
                        <input type="hidden" name="cancel_total_price" value="<?=$cancel_price+$freight-$discount?>">
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

    <input type="hidden" name="pur_number" value="<?= $items_details[0]['pur_number'] ?>">
    <input type="hidden" name="is_all_cancel" value="1" id="part_cancel" checked>
    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::button(Yii::t('app', '提交'), ['class' => 'btn btn-primary submit']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>
<?php
ActiveForm::end();
?>

<?php
$js = <<<JS
$(function() {
    var bool=true;
   //提交
   $('.submit').click(function() {
      var flag = false;
      var cancel_ctq=0;
      $(".table tbody tr .ctqs").each(function(){
          var new_cancel_ctq = parseInt(($(this).find('input[class*=cancel_ctq]').val())); //当前取消数量
          var ctq = parseFloat($(this).parent().find('.ctq').html()); //采购数量
          var quxiao_ctq = parseFloat($(this).parent().find('.quxiao_ctq').html()); //已取消数量
          var instock_qty_count = parseFloat($(this).parent().find('.instock_qty_count').html()); //入库数量
          var bcc_res = ctq-quxiao_ctq-instock_qty_count-new_cancel_ctq;
          cancel_ctq += new_cancel_ctq;

          if ( (cancel_ctq<=0) || (bcc_res<0)) {
            flag = true;
          }
      });
      if( ($.trim($('#confirm_note').val()) == '') || (flag==true) ) {
           layer.alert('取消数量大于零 且 取消数量大于（采购数量-已取消数量-入库数量） 且 备注不能为空');
           return false;
      }else {
          var freight = $('.freight').val();
          var discount = $('.discount').val();
          if (freight.replace(/(^s*)|(s*$)/g, "").length == 0) {
            layer.alert('请填写运费，无运费填0');
            return false;
          }
          if (discount.replace(/(^s*)|(s*$)/g, "").length == 0) {
            layer.alert('请填写优惠金额，无优惠填0');
            return false;
          }
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
            $(this).find('input[class*=cancel_ctq]').val(ctq);
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
        if (bool==true) {
            $(".total").html('取消件数：'+ cancel_ctq + '件<br />取消金额：'+ total_pay_item_price.toFixed(2)+'RMB<input type="hidden" name="cancel_total_price" value="'+ total_pay_item_price +'">');
        } 
        if (bool ==false) {
            var freight = parseFloat($('.freight').val());
            var discount = parseFloat($('.discount').val());
            var total_price = s + freight - discount;
            $(".total").html('取消件数：'+ cancel_ctq + '件<br />取消金额：'+ total_price.toFixed(2)+'RMB<input type="hidden" name="cancel_total_price" value="'+ total_price +'">');
        }
       
    }
});
JS;
$this->registerJs($js);
?>
