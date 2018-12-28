<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;
use yii\helpers\Url;

?>
<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">修改采购单中的运费和优惠额</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>要求：知道【采购单单号】</li>
            <li>要求：知道采购单要改成的【运费】或 【优惠额】</li>
            <li>优惠后总金额：采购总金额+运费-优惠金额</li>
        </ol>
        <div class="form-group">
            <label class="control-label" for="update_order_items_price_pur_number">
                采购单单号
                <input type="text" id="update_order_items_price_pur_number" class="form-control update_order_items_price_class">
            </label>
            <label class="control-label" for="update_order_items_price_sku">
                SKU
                <input type="text" id="update_order_items_price_sku" class="form-control update_order_items_price_class">
            </label>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购订单详表</caption>
                    <tbody id="select_order_items_price"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="update_order_items_price_order_price">
                产品名称
                <input type="text" id="update_order_items_price_order_name" class="form-control">
            </label>
            <label class="control-label" for="update_order_items_price_order_price">
                单价
                <input type="number" id="update_order_items_price_order_price" class="form-control">
            </label>
            <label class="control-label" for="update_order_items_price_order_price">
                采购数量
                <input type="number" id="update_order_items_price_order_ctq" class="form-control">
            </label>
        </div>
        <div class="form-group">
            <?php
            if(Helper::checkRoute('update-order-items-price')) {
                echo '<span id="update_order_items_price_order_btn" class="btn btn-success">确认修改</span>';
            }
            ?>
        </div>
    </div>
    <div class="panel-footer">
        <span id="update_order_items_price_order_span"></span>
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
    $(".update_order_items_price_class").blur(function(){
        var pur_number = $.trim($('#update_order_items_price_pur_number').val()); 
        var sku = $.trim($('#update_order_items_price_sku').val()); 
        if(pur_number == '') {
            if (sku == '') {
                $('#select_order_items_price').html('');
                return false;
            }
        }
        
        $.ajax({
            url: 'select-order-items-price',
            data: {pur_number: pur_number,sku:sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                //采购单物流信息表
               var purchase_order_items_info =  data.purchase_order_items_info;
               $('#select_order_items_price').html('');
               if (purchase_order_items_info=='') {
                   $('#select_order_items_price').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order_items_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_order_items_price').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['name'] +'</td>' +
                              '<td>' + v['price'] +'</td>' +
                              '<td>' + v['ctq'] +'</td>' +
                              '<td>' + v['items_totalprice'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //===============  处理 作废单被入错库   ======================
    $('#update_order_items_price_order_btn').click(function() {
        var pur_number = $.trim($('#update_order_items_price_pur_number').val()); 
        var sku = $.trim($('#update_order_items_price_sku').val()); 
        var price = $.trim($('#update_order_items_price_order_price').val());
        var ctq = $.trim($('#update_order_items_price_order_ctq').val());
        var name = $.trim($('#update_order_items_price_order_name').val());
        if(pur_number == '' || sku=='') {
            alert('请输入采购单单号和sku');
            return false;
        }
                
        $.ajax({
            url: 'update-order-items-price',
            data: {pur_number: pur_number, sku: sku,price:price,ctq:ctq,name:name},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_order_items_price_order_span').html('');
               $('#update_order_items_price_order_price').val('');
               $('#update_order_items_price_order_ctq').val('');
               $('#update_order_items_price_order_name').val('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     console.log(color,v['msg']);
                     $('#update_order_items_price_order_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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

