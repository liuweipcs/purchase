<?php

use app\services\PurchaseOrderServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
?>

    <div class="panel panel-success">
        <h4 align="center" style="font-weight:bold;color: red;">删除数据</h4>
        <div class="panel panel-body">
            <h4>温馨小提示:</h4>
            <ol style="color:red;font-weight: bold;">
                <li>要求：知道-表名称</li>
                <li>要求：知道-id</li>
                <li>还原时：要知道：被还原的表名和pur_operat_log的ID</li>
                <li>采购系统合同主表：pur_purchase_compact</li>
                <li>请款单表：pur_purchase_order_pay</li>
                <li>采购单收款表：pur_purchase_order_receipt</li>
                <li>采购到货记录：pur_arrival_record</li>
                <li>入库结果表：pur_warehouse_results</li>
            </ol>
            <div class="form-group">
                <label class="control-label" for="delete_data_data_table">数据表名称</label>
                <input type="text" id="delete_data_data_table" class="form-control">
            </div>
            <div class="form-group">
                <label class="control-label" for="delete_data_id">ID</label>
                <input type="text" id="delete_data_id" class="form-control">
            </div>
            <div class="form-group">
                <span id="delete_data_btn" class="btn btn-success">确认删除</span>
                <span id="restore_data_btn" class="btn btn-success">数据还原</span>
            </div>
        </div>
        <div class="panel-footer">
            <span id="delete_data_span"></span>
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
    //================ 删除数据  ================================
    $('#delete_data_btn').click(function() {
        var data_table = $.trim($('#delete_data_data_table').val()); //数据表名
        var id = $.trim($('#delete_data_id').val()); //id
        if(data_table == '' || id == '') {
            alert('数据表 或 id不能为空');
            return false;
        }

        $.ajax({
            url: 'delete-data',
            data: {data_table: data_table, id: id},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               
               $('#delete_data_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#delete_data_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
               });
            }
        });   
    });
    //===================   还原数据 ============================================
    $('#restore_data_btn').click(function() {
        var data_table = $.trim($('#delete_data_data_table').val()); //数据表名
        var id = $.trim($('#delete_data_id').val()); //id
        if(data_table == '' || id == '') {
            alert('数据表 或 id不能为空');
            return false;
        }

        $.ajax({
            url: 'restore-data',
            data: {data_table: data_table, id: id},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               
               $('#delete_data_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#delete_data_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
               });
            }
        });   
    });
    //===============================================================
});
JS;
$this->registerJs($js);
$this->beginContent('@app/views/layouts/waste.php');
$this->endContent();
?>