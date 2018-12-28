<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;
use yii\helpers\Url;

?>
<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">修改到货记录</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>要求：知道【采购单单号和sku】</li>
            <li>表名：pur_arrival_record</li>
        </ol>
        <div class="form-group">
            <label class="control-label" for="update_arrival_record_id">
                ID
                <input type="text" id="update_arrival_record_id" class="form-control update_order_items_price_class">
            </label>
            <label class="control-label" for="update_arrival_record_pur_number">
                采购单单号
                <input type="text" id="update_arrival_record_pur_number" class="form-control update_order_items_price_class">
            </label>
            <label class="control-label" for="update_arrival_record_sku">
                SKU
                <input type="text" id="update_arrival_record_sku" class="form-control update_order_items_price_class">
            </label>
            <label class="control-label" for="update_arrival_record_qc_id">
                唯一表示
                <input type="text" id="update_arrival_record_qc_id" class="form-control update_order_items_price_class">
            </label>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>入库结果表</caption>
                    <tbody id="select_arrival_record"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="update_arrival_record_info_sku">
                SKU
                <input type="text" id="update_arrival_record_info_sku" class="form-control">
            </label>
            <label class="control-label" for="update_arrival_record_info_name">
                产品名称
                <input type="text" id="update_arrival_record_info_name" class="form-control">
            </label>
            <label class="control-label" for="update_arrival_record_info_delivery_qty">
                到货数量
                <input type="number" id="update_arrival_record_info_delivery_qty" class="form-control">
            </label>
            <label class="control-label" for="update_arrival_record_info_delivery_time">
                收货时间
                <input type="date" id="update_arrival_record_info_delivery_time" class="form-control">
            </label>
            <label class="control-label" for="update_arrival_record_info_delivery_user">
                收货人
                <input type="text" id="update_arrival_record_info_delivery_user" class="form-control">
            </label>
            <label class="control-label" for="update_arrival_record_info_check_type">
                品检类型
                <input type="text" id="update_arrival_record_info_check_type" class="form-control">
            </label>
            <label class="control-label" for="update_arrival_record_info_bad_products_qty">
                次品数量
                <input type="number" id="update_arrival_record_info_bad_products_qty" class="form-control">
            </label>
            <label class="control-label" for="update_arrival_record_info_check_time">
                品检时间
                <input type="date" id="update_arrival_record_info_check_time" class="form-control">
            </label>
            <label class="control-label" for="update_arrival_record_info_check_user">
                品检人
                <input type="text" id="update_arrival_record_info_check_user" class="form-control">
            </label>
        </div>
        <div class="form-group">
            <?php
            if(Helper::checkRoute('update-arrival-record')) {
                echo '<span id="update_arrival_record_btn" class="btn btn-success">确认修改</span>';
            }
            ?>
        </div>
    </div>
    <div class="panel-footer">
        <span id="update_arrival_record_span"></span>
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
        var id = $.trim($('#update_arrival_record_id').val()); 
        var pur_number = $.trim($('#update_arrival_record_pur_number').val()); 
        var sku = $.trim($('#update_arrival_record_sku').val()); 
        var qc_id = $.trim($('#update_arrival_record_qc_id').val()); 
        if(pur_number == '') {
            if (sku == '') {
                $('#select_arrival_record').html('');
                return false;
            }
        }
        
        $.ajax({
            url: 'select-arrival-record',
            data: {id:id,pur_number: pur_number,sku:sku,qc_id:qc_id},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                //采购单物流信息表
               var arrival_record_info =  data.arrival_record_info;
               $('#select_arrival_record').html('');
               if (arrival_record_info=='') {
                   $('#select_arrival_record').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(arrival_record_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_arrival_record').append('<tr class="size-row success">' +
                              '<td>' + v['id'] +'</td>' +
                              '<td>' + v['purchase_order_no'] +'</td>' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['name'] +'</td>' +
                              '<td>' + v['delivery_qty'] +'</td>' +
                              '<td>' + v['delivery_time'] +'</td>' +
                              '<td>' + v['delivery_user'] +'</td>' +
                              '<td>' + v['check_type'] +'</td>' +
                              '<td>' + v['bad_products_qty'] +'</td>' +
                              '<td>' + v['check_time'] +'</td>' +
                              '<td>' + v['check_user'] +'</td>' +
                              '<td>' + v['qc_id'] +'</td>' +
                              '<td>' + v['note'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //===============  修改到货记录   ======================
    $('#update_arrival_record_btn').click(function() {
        var id = $.trim($('#update_arrival_record_id').val()); 
        var pur_number = $.trim($('#update_arrival_record_pur_number').val()); 
        var sku = $.trim($('#update_arrival_record_sku').val()); 
        var qc_id = $.trim($('#update_arrival_record_qc_id').val()); 
        var new_sku = $.trim($('#update_arrival_record_info_sku').val()); 
        var name = $.trim($('#update_arrival_record_info_name').val());
        var delivery_qty = $.trim($('#update_arrival_record_info_delivery_qty').val());
        var delivery_time = $.trim($('#update_arrival_record_info_delivery_time').val());
        var delivery_user = $.trim($('#update_arrival_record_info_delivery_user').val());
        var check_type = $.trim($('#update_arrival_record_info_check_type').val());
        var bad_products_qty = $.trim($('#update_arrival_record_info_bad_products_qty').val());
        var check_time = $.trim($('#update_arrival_record_info_check_time').val());
        var check_user = $.trim($('#update_arrival_record_info_check_user').val());
        if(pur_number == '') {
            alert('请输入采购单单号');
            return false;
        }
                
        $.ajax({
            url: 'update-arrival-record',
            data: {id:id,purchase_order_no: pur_number, sku: sku,qc_id:qc_id,new_sku:new_sku,name:name, delivery_qty:delivery_qty, delivery_time:delivery_time, delivery_user:delivery_user, check_type:check_type,bad_products_qty:bad_products_qty,check_time:check_time,check_user:check_user},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_arrival_record_span').html('');
               $('#update_arrival_record_info_sku').val('');
               $('#update_arrival_record_info_name').val('');
               $('#update_arrival_record_info_delivery_qty').val('');
               $('#update_arrival_record_info_delivery_time').val('');
               $('#update_arrival_record_info_delivery_user').val('');
               $('#update_arrival_record_info_check_type').val('');
               $('#update_arrival_record_info_bad_products_qty').val('');
               $('#update_arrival_record_info_check_time').val('');
               $('#update_arrival_record_info_check_user').val('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     console.log(color,v['msg']);
                     $('#update_arrival_record_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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

