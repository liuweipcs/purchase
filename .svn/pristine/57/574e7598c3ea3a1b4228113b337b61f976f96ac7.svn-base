<?php

use app\services\PurchaseOrderServices;
use app\config\Vhelper;
?>

<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">查看在途库存问题</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>要求：知道sku</li>
            <li>【采购建议数量】 = 日权均销量（sales_avg）* 安全备货天数15+缺货数量（left_stock）- 在途库存（on_way_stock）- 可用库存（available_stock）</li>
        </ol>
        <div class="form-group">
            <label class="control-label" for="select_stock_sku">sku</label>
            <input type="text" id="select_stock_sku" class="form-control">
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购建议表</caption>
                    <tbody id="select_suggest_span"></tbody>
                </table>
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>库存综合查询表</caption>
                    <tbody id="select_stock_sku_span"></tbody>
                </table>
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>库存记录</caption>
                    <tbody id="select_stock_owes_sku_span"></tbody>
                </table>
            </div>
        </div>
        <!--<div class="form-group">
            <span id="select_stock_sku_btn" class="btn btn-success">Create</span>
        </div>-->
    </div>
    <div class="panel-footer"></div>
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
    $("#select_stock_sku").blur(function(){
        var sku = $.trim($('#select_stock_sku').val()); 
        if(sku == '') {
            $('#select_suggest_span').html('');
            $('#select_stock_sku_span').html('');
            $('#select_stock_owes_sku_span').html('');
            return false;
        }
        $.ajax({
            url: 'select-stock',
            data: {sku: sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               var suggest =  data.suggest;
               var stock_owes =  data.stock_owes;

               $('#select_suggest_span').html('');
               if (suggest=='') {
                   $('#select_suggest_span').append('<span style="font-weight: bold;color:red;">没有该sku -- '+ sku + '</span>');
               } else {
                   $(suggest).each(function(sk,sv) {
                       $(sv).each(function(k,v) {
                             $('#select_suggest_span').append('<tr class="size-row success">' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['sales_avg'] +'</td>' +
                              '<td>' + v['left_stock'] +'</td>' +
                              '<td>' + v['on_way_stock'] +'</td>' +
                              '<td>' + v['available_stock'] +'</td>' +
                              '<td>' + v['qty'] +'</td>' +
                              '<td>' + v['created_at'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                              '<td>' + v['is_purchase'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               
               $('#select_stock_sku_span').html('');
               if (d=='') {
                   $('#select_stock_sku_span').append('<span style="font-weight: bold;color:red;">没有该sku -- '+ sku + '</span>');
               } else {
                   $(d).each(function(sk,sv) {
                       $(sv).each(function(k,v) {
                             $('#select_stock_sku_span').append('<tr class="size-row success">' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['stock'] +'</td>' +
                              '<td>' + v['on_way_stock'] +'</td>' +
                              '<td>' + v['available_stock'] +'</td>' +
                              '<td>' + v['warehouse_code'] +'</td>' +
                              '<td>' + v['created_at'] +'</td>' +
                              '<td>' + v['update_at'] +'</td>' +
                              '<td>' + v['left_stock'] +'</td>' +
                              '<td>' + v['is_suggest'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               
               $('#select_stock_owes_sku_span').html('');
               if (stock_owes=='') {
                   $('#select_stock_owes_sku_span').append('<span style="font-weight: bold;color:red;">没有该sku -- '+ sku + '</span>');
               } else {
                   $(stock_owes).each(function(sk,sv) {
                       $(sv).each(function(k,v) {
                             $('#select_stock_owes_sku_span').append('<tr class="size-row success">' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['warehouse_code'] +'</td>' +
                              '<td>' + v['left_stock'] +'</td>' +
                              '<td>' + v['status'] +'</td>' +
                              '<td>' + v['statistics_date'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });
    });
    //================ 修改采购单状态  ================================
    $('#select_stock_sku_btn').click(function() {
        var sku = $.trim($('#select_stock_sku').val()); //sku
        if(sku == '') {
            alert('sku 不能为空');
            return false;
        }
        
        $.ajax({
            url: 'select-stock',
            data: {sku: sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               console.log(d);
               $('#select_stock_sku_span').html('');
               $(d).each(function(sk,sv) {
                   $(sv).each(function(k,v) {
                         $('#select_stock_sku_span').append('<tr class="size-row">' +
                          '<td>' + v['sku'] +'</td>' +
                          '<td>' + v['stock'] +'</td>' +
                          '<td>' + v['on_way_stock'] +'</td>' +
                          '<td>' + v['available_stock'] +'</td>' +
                          '<td>' + v['warehouse_code'] +'</td>' +
                          '<td>' + v['created_at'] +'</td>' +
                          '<td>' + v['update_at'] +'</td>' +
                          '<td>' + v['left_stock'] +'</td>' +
                          '<td>' + v['is_suggest'] +'</td>' +
                           '</tr');
                   });
               });
               /*$('#select_stock_sku_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#select_stock_sku_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
               });*/
            }
        });   
    });
    //=============================================================================
});
JS;
$this->registerJs($js);
$this->beginContent('@app/views/layouts/waste.php');
$this->endContent();
?>