<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;
use yii\helpers\Url;

?>
<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">修改采购单中的供应商</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>采购单中的供应商错误</li>
            <li>绑定的错的  或者采购这边换供应商了  绑定的还是原来的   新供应商是备用的供应商</li>
            <li>采购这边下单的时候忘记改了</li>
            <li>直接提交原来绑定的</li>
            <li>要求：知道【采购单单号】</li>
            <li>要求：知道采购单要改成的【供应商】或 【供应商编码】</li>
        </ol>
        <div class="form-group">
            <label class="control-label" for="pur_number_supplier">采购单单号</label>
            <input type="text" id="pur_number_supplier" class="form-control">
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购订单主表</caption>
                    <tbody id="select_pur_number_supplier"></tbody>
                </table>
            </div>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购单支付表</caption>
                    <tbody id="select_pur_number_supplier_pay"></tbody>
                </table>
            </div>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>供应商表-根据订单的供应商编码查询</caption>
                    <tbody id="select_pur_number_supplier_code"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="supplier_name_supplier">替换后的供应商--供应商名称</label>
            <input type="text" id="supplier_name_supplier" class="form-control">
        </div>
        <div class="form-group">
            <label class="control-label" for="supplier_code_supplier">替换后的供应商--供应商编码</label>
            <input type="text" id="supplier_code_supplier" class="form-control">
        </div>
        <div class="form-group">
            <?php
            if(Helper::checkRoute('update-supplier')) {
                echo '<span id="update_supplier_btn" class="btn btn-success">修改供应商</span>';
            }
            ?>
        </div>
    </div>
    <div class="panel-footer">
        <span id="supplier_span"></span>
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
    $("#pur_number_supplier").blur(function(){
        var pur_number = $.trim($('#pur_number_supplier').val()); 
        if(pur_number == '') {
            $('#select_pur_number_supplier').html('');
            $('#select_pur_number_supplier_pay').html('');
            $('#select_pur_number_supplier_code').html('');
            return false;
        }
        $.ajax({
            url: 'select-supplier',
            data: {pur_number: pur_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var purchase_order =  data.purchase_order;
               $('#select_pur_number_supplier').html('');
               if (purchase_order=='') {
                   $('#select_pur_number_supplier').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_pur_number_supplier').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['purchas_status'] +'</td>' +
                              '<td>' + v['supplier_code'] +'</td>' +
                              '<td>' + v['supplier_name'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               //采购单支付表
               var purchase_order_pay =  data.purchase_order_pay;
               $('#select_pur_number_supplier_pay').html('');
               if (purchase_order_pay=='') {
                   $('#select_pur_number_supplier_pay').append('<span style="font-weight: bold;color:red;">采购单支付表无此单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order_pay).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_pur_number_supplier_pay').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['supplier_code'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               
               var purchase_order_code =  data.purchase_order_code;
               $('#select_pur_number_supplier_code').html('');
               if (purchase_order_code=='') {
                   $('#select_pur_number_supplier_code').append('<span style="font-weight: bold;color:red;">没有找到对应的供应商 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order_code).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_pur_number_supplier_code').append('<tr class="size-row success">' +
                              '<td>' + v['supplier_code'] +'</td>' +
                              '<td>' + v['supplier_name'] +'</td>' +
                              '<td>' + v['supplier_settlement'] +'</td>' +
                              '<td>' + v['payment_method'] +'</td>' +
                              '<td>' + v['status'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //===============  处理 作废单被入错库   ======================
    $('#update_supplier_btn').click(function() {
        var pur_number = $.trim($('#pur_number_supplier').val());
        var supplier_name = $.trim($('#supplier_name_supplier').val());
        var supplier_code = $.trim($('#supplier_code_supplier').val());
        if(pur_number == '') {
            alert('采购单单号');
            return false;
        }
        if (supplier_code=='') {
            if (supplier_name=='') {
                alert('供应商名和供应商名称 至少要填一个');
                return false;
            }
        }
        
        $.ajax({
            url: 'update-supplier',
            data: {pur_number: pur_number, supplier_name: supplier_name,supplier_code:supplier_code},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#supplier_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#supplier_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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

