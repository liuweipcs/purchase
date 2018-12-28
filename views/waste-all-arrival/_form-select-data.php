<?php

use app\services\PurchaseOrderServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;

use kartik\select2\Select2;
?>
    <div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">查看数据</h4>
    <div class="panel panel-body">
    <h4>温馨小提示:</h4>
    <ol style="color:red;font-weight: bold;">
        <li>注意：修改的是【采购单状态】</li>
    </ol>
    <div class="form-group" style="width: 2500px;">
        <label class="control-label" style="width: 15%;">数据表名
            <?php
            echo Select2::widget([ 'name' => 'title',
                'data' => $tables,
                'options' => ['placeholder' => !empty($table_name) ? $table_name : '请选择...','class'=>'demo_01']
            ]);
            ?>
        </label>
        <!--<label class="control-label" style="width: 50%">字段名
                <?php
        /*                echo Select2::widget([
                            'name' => 'title',
        //                    'value' => 2,
                            'data' => $fields,
                            'options' => ['multiple' => true,'placeholder' => '请选择...'],
                            'id'=>'demo_02'
                        ]);
                        */?>
                </label>-->
        <div class="table-responsive">
            <label class="control-label">字段名
                <select id="select_data" class="form-control selectpicker"  multiple='multiple'>
                    <?php
                    foreach ($fields as $k => $v) {
                        echo '<option value="' . $k . '">' . $k . '【' . $v  . '】</option>';
                    }
                    ?>
                </select>
            </label>
        </div>
    </div>
    <div>
    <label class="control-label">要修改的字段名
        <select id="select_data" class="form-control selectpicker select_data_01">
            <?php
            foreach ($fields as $k => $v) {
                echo '<option value="' . $k . '">' . $k . '【' . $v  . '】</option>';
            }
            ?>
        </select>
    </label>
    <label class="control-label">条件
        <input class="form-control" id="selected_01" name="u_exam_idnumber" placeholder="请输入条件1"/>
    </label>
    <div>
    <div>
        <label class="control-label">要修改的字段名
            <select id="select_data" class="form-control selectpicker select_data_02">
                <?php
                foreach ($fields as $k => $v) {
                    echo '<option value="' . $k . '">' . $k . '【' . $v  . '】</option>';
                }
                ?>
            </select>
        </label>
        <label class="control-label">条件
            <input class="form-control" id="selected_02" name="u_exam_idnumber" placeholder="请输入条件2"/>
        </label>
        <div>
            <div class="form-group">
                <?php
                if(Helper::checkRoute('select-data')) {
                    echo '<span id="select_data_btn" class="btn btn-success">查看数据</span>';
                }
                ?>
            </div>
        </div>
        <div class="panel-footer">
            <span id="select_data_span"></span>
        </div>
    </div>
<?php
$this->registerCssFile('@web/js/multi-select-master/css/multi-select.css', ['depends' => ['app\assets\AppAsset']]);
$this->registerJsFile('@web/js/multi-select-master/js/jquery.multi-select.js', ['depends' => ['app\assets\AppAsset']]);

//$viewUrl = Url::toRoute('/waste-all-arrival/waste-all-arrival');
$js = <<<JS
$('#select_data').multiSelect();

// $.fn.modal.Constructor.prototype.enforceFocus = function () {};
   
$(function() {
    var kkk = {
        red: 'red',
        green: 'green',
        dark: 'dark',
    };
    $("#select2-w0-container").bind("DOMNodeInserted",function(){
        //需要调用的方法；
         var table_name = $.trim($('#select2-w0-container').text());
         window.location.href='select-data?table_name='+table_name;
         return false;
         $.ajax({
            url: 'select-data',
            data: {table_name: table_name},
            type: 'get',
            dataType: 'json',
            success: function(data) {
                $('.selectpicker').html('');        
                //对象遍历
                for(var pk in data){
                    $('.selectpicker').append('<option value="'+ pk +'">'+ pk + '【' + data[pk] + '】</option>');
                }
               //数组遍历
               /*$(data).each(function(pk,pv) {
                    $('#select_data').append('<option value="'+ pk +'">'+ pv +'</option>');
               });*/
            }
        });   
    });
    //===============  展示产品详情 ===================
    $("#update_order_purchas_status_pur_number").blur(function(){
        var pur_number = $.trim($('#update_order_purchas_status_pur_number').val()); 
        if(pur_number == '') {
            $('#select_order_purchas_status_pur_number').html('');
            return false;
        }
        $.ajax({
            url: 'select-purchas-status',
            data: {pur_number: pur_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var purchase_order =  data.purchase_order;
               $('#select_order_purchas_status_pur_number').html('');
               if (purchase_order=='') {
                   $('#select_order_purchas_status_pur_number').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_order_purchas_status_pur_number').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['purchas_status'] +'</td>' +
                              '<td>' + v['is_push'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                              '<td>' + v['created_at'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //================ 修改采购单状态  ================================
    $('#select_data_btn').click(function() {
        var select_data_01 = $.trim($('.select_data_01').val());
        var selected_01 = $.trim($('#selected_01').val());
        
        var select_data_02 = $.trim($('.select_data_02').val()); 
        var selected_02 = $.trim($('#selected_02').val());
        $.ajax({
            url: 'select-data',
            data: {select_data_01: select_data_01, selected_01: selected_01,select_data_02:select_data_02,selected_02:selected_02},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var data =  data.message;
               $('#select_data_span').html('');
               $(data).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#select_data_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
               });
               
               /*for(var pk in data){
                    $('#select_data_span').html('');
                    var color = data['color'];
                    $('#select_data_span').append('<p style="font-weight:bold;color:'+color+'">'+data['msg'][0]+'</p>');
                    console.log(color,data['msg'][0]);
                }*/
                
               /*$(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#select_data_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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