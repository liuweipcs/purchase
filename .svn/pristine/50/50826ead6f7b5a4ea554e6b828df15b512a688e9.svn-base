<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;
use yii\helpers\Url;

?>
<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">修改派单号</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>要求：知道【采购单单号】</li>
        </ol>
        <div class="form-group">
            <label class="control-label" for="update_order_number_pur_number">采购单单号</label>
            <input type="text" id="update_order_number_pur_number" class="form-control">
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购单--派单号表</caption>
                    <tbody id="select_order_orders"></tbody>
                </table>
            </div>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购单-费用详情表</caption>
                    <tbody id="select_order_type"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="update_order_number_order_number">
                派单号
                <input type="text" id="update_order_number_order_number" class="form-control">
            </label>
        </div>
        <div class="form-group">
            <?php
            if(Helper::checkRoute('update-order-number')) {
                echo '<span id="update_order_number_btn" class="btn btn-success">确认修改</span>';
            }
            ?>
        </div>
    </div>
    <div class="panel-footer">
        <span id="update_order_number_span"></span>
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
    $("#update_order_number_pur_number").blur(function(){
        var pur_number = $.trim($('#update_order_number_pur_number').val()); 
        if(pur_number == '') {
            $('#select_order_orders').html('');
            $('#select_order_type').html('');
            return false;
        }
        
        $.ajax({
            url: 'select-order-number',
            data: {pur_number: pur_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                //采购单派单号表
               var purchase_order_orders_info =  data.purchase_order_orders_info;
               $('#select_order_orders').html('');
               if (purchase_order_orders_info=='') {
                   $('#select_order_orders').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order_orders_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_order_orders').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['order_number'] +'</td>' +
                              '<td>' + v['is_request'] +'</td>' +
                              '<td>' + v['create_id'] +'</td>' +
                              '<td>' + v['e_order_number'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               //采购单-费用详情表
               var purchase_order_pay_type_info =  data.purchase_order_pay_type_info;
               $('#select_order_type').html('');
               if (purchase_order_pay_type_info=='') {
                   $('#select_order_type').append('<span style="font-weight: bold;color:red;">采购单支付表无此单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order_pay_type_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_order_type').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['platform_order_number'] +'</td>' +
                              '<td>' + v['purchase_acccount'] +'</td>' +
                              '<td>' + v['is_request'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //===============  修改派单号   ======================
    $('#update_order_number_btn').click(function() {
        var pur_number = $.trim($('#update_order_number_pur_number').val());
        var order_number = $.trim($('#update_order_number_order_number').val());
        if(pur_number == '') {
            alert('采购单单号');
            return false;
        }
                
        $.ajax({
            url: 'update-order-number',
            data: {pur_number: pur_number, order_number: order_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_order_number_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#update_order_number_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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

