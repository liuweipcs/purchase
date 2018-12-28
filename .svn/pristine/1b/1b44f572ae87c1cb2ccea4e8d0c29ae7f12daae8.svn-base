<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;
use yii\helpers\Url;

?>
<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">修改采购单中的运费和优惠额和采购数量</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>要求：知道【采购单单号】</li>
            <li>要求：知道采购单要改成的【运费】或 【优惠额】</li>
            <li>优惠后总金额：采购总金额+运费-优惠金额</li>
        </ol>
        <div class="form-group">
            <label class="control-label" for="update_ship_discount_price_pur_number">采购单单号</label>
            <input type="text" id="update_ship_discount_price_pur_number" class="form-control">
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购单物流信息表</caption>
                    <tbody id="select_pur_number_ship_price"></tbody>
                </table>
            </div>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购单-优惠表</caption>
                    <tbody id="select_pur_number_discount_price"></tbody>
                </table>
            </div>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购单-费用详情表</caption>
                    <tbody id="select_pur_number_order_pay_type"></tbody>
                </table>
            </div>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购单-子表</caption>
                    <tbody id="select_pur_number_order_pay_detail"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="update_ship_discount_price_freight">
                运费
                <input type="number" id="update_ship_discount_price_freight" class="form-control">
            </label>
            <label class="control-label" for="update_ship_discount_price_discount">
                优惠金额
                <input type="number" id="update_ship_discount_price_discount" class="form-control">
            </label>
        </div>
        <div class="form-group">
            <?php
            if(Helper::checkRoute('update-ship-discount-price')) {
                echo '<span id="update_ship_discount_price_btn" class="btn btn-success">确认修改</span>';
            }
            ?>
        </div>
    </div>
    <div class="panel-footer">
        <span id="update_ship_discount_price_span"></span>
    </div>
</div>

<?php
//$viewUrl = Url::toRoute('/waste-all-arrival/waste-all-arrival');
$js = <<<JS
$(function() {
    var kkk = {
        red: 'red',
        green: 'green',
        dark: 'dark',
    };
    //===============  展示产品详情 ===================
    $("#update_ship_discount_price_pur_number").blur(function(){
        var pur_number = $.trim($('#update_ship_discount_price_pur_number').val()); 
        if(pur_number == '') {
            $('#select_pur_number_ship_price').html('');
            $('#select_pur_number_discount_price').html('');
            $('#select_pur_number_order_pay_type').html('');
            $('#select_pur_number_order_pay_detail').html('');
            return false;
        }
        
        $.ajax({
            url: 'select-ship-discount-price',
            data: {pur_number: pur_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                //采购单物流信息表
               var purchase_order_ship_info =  data.purchase_order_ship_info;
               $('#select_pur_number_ship_price').html('');
               if (purchase_order_ship_info=='') {
                   $('#select_pur_number_ship_price').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order_ship_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_pur_number_ship_price').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['express_no'] +'</td>' +
                              '<td>' + v['freight'] +'</td>' +
                              // '<td>' + v['pay_number'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               //采购单-优惠表
               var purchase_discount_info =  data.purchase_discount_info;
               $('#select_pur_number_discount_price').html('');
               if (purchase_discount_info=='') {
                   $('#select_pur_number_discount_price').append('<span style="font-weight: bold;color:red;">采购单支付表无此单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_discount_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_pur_number_discount_price').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                              '<td>' + v['discount_price'] +'</td>' +
                              '<td>' + v['total_price'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               //采购单-费用详情表
               var purchase_order_pay_type_info =  data.purchase_order_pay_type_info;
               $('#select_pur_number_order_pay_type').html('');
               if (purchase_order_pay_type_info=='') {
                   $('#select_pur_number_order_pay_type').append('<span style="font-weight: bold;color:red;">采购单支付表无此单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order_pay_type_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_pur_number_order_pay_type').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['request_type'] +'</td>' +
                              '<td>' + v['freight'] +'</td>' +
                              '<td>' + v['discount'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               //采购单-子表
               var purchase_order_pay_detail_info =  data.purchase_order_pay_detail_info;
               $('#select_pur_number_order_pay_detail').html('');
               if (purchase_order_pay_detail_info=='') {
                   $('#select_pur_number_order_pay_detail').append('<span style="font-weight: bold;color:red;">采购单支付表无此单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order_pay_detail_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_pur_number_order_pay_detail').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['requisition_number'] +'</td>' +
                              '<td>' + v['freight'] +'</td>' +
                              '<td>' + v['discount'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //===============  处理 作废单被入错库   ======================
    $('#update_ship_discount_price_btn').click(function() {
        var pur_number = $.trim($('#update_ship_discount_price_pur_number').val());
        var freight = $.trim($('#update_ship_discount_price_freight').val());
        var discount_price = $.trim($('#update_ship_discount_price_discount').val());
        if(pur_number == '') {
            alert('采购单单号');
            return false;
        }
                
        $.ajax({
            url: 'update-ship-discount-price',
            data: {pur_number: pur_number, freight: freight,discount_price:discount_price},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_ship_discount_price_span').html('');
               $('#update_ship_discount_price_freight').val('');
               $('#update_ship_discount_price_discount').val('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#update_ship_discount_price_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
               });
            }
        });   
    });
});
JS;
$this->registerJs($js);
$this->beginContent('@app/views/layouts/waste.php');
$this->endContent();
?>

