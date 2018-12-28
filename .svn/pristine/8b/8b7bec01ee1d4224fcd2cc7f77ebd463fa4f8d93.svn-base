<?php

use app\services\PurchaseOrderServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;

use kartik\select2\Select2;
?>
    <div class="panel panel-success">
        <h4 align="center" style="font-weight:bold;color: red;">修改数据</h4>
        <div class="panel panel-body">
            <h4>温馨小提示:</h4>
            <ol style="color:red;font-weight: bold;">
                <li></li>
            </ol>
            <div class="form-group">
                <label class="control-label" style="width: 15%;">数据表名
                <?php
                    echo Select2::widget([ 'name' => 'title',
                        'data' => $tables,
                        'options' => ['placeholder' => !empty($table_name) ? $table_name : '请选择...','class'=>'demo_01']
                    ]);
                ?>
                </label>
                <!--<label class="control-label" for="update_order_purchas_status_new" style="width: 50%">字段名
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
                    <label class="control-label">查询的条件
                        <select id="update_data" class="form-control selectpicker select_data"  multiple='multiple'>
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
                <label class="control-label">修改的条件01
                    <select class="form-control selectpicker where_selected_01 select_data">
                        <?php
                        foreach ($fields as $k => $v) {
                            echo '<option value="' . $k . '">' . $k . '【' . $v  . '】</option>';
                        }
                        ?>
                    </select>
                </label>
                <label class="control-label">条件
                    <input class="form-control" id="where_01" name="u_exam_idnumber" placeholder="请输入中条件" />
                </label>
            </div>
            <div>
                <label class="control-label">修改的条件02
                    <select class="form-control selectpicker where_selected_02 select_data">
                        <?php
                        foreach ($fields as $k => $v) {
                            echo '<option value="' . $k . '">' . $k . '【' . $v  . '】</option>';
                        }
                        ?>
                    </select>
                </label>
                <label class="control-label">条件
                    <input class="form-control" id="where_02" name="u_exam_idnumber" placeholder="请输入中考准考证号" data-vaild="^\d{5,20}$" data-errmsg="准考证号码不正确，仅能包含数字" />
                </label>
            </div>
            <hr>
            <div>
                <label class="control-label">修改的数据01
                    <select class="form-control selectpicker field_selected_01 select_data">
                        <?php
                        foreach ($fields as $k => $v) {
                            echo '<option value="' . $k . '">' . $k . '【' . $v  . '】</option>';
                        }
                        ?>
                    </select>
                </label>
                <label class="control-label">修改结果
                    <input class="form-control" id="field_01" name="u_exam_idnumber" placeholder="请输入中考准考证号" data-vaild="^\d{5,20}$" data-errmsg="准考证号码不正确，仅能包含数字" />
                </label>
            </div>
            <div class="form-group">
                <?php
                if(Helper::checkRoute('update-data')) {
                    echo '<span id="update_data_btn" class="btn btn-success">修改数据</span>';
                }
                ?>
            </div>
        </div>
        <div class="panel-footer">
            <span id="update_data_span"></span>
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
         window.location.href='update-data?table_name='+table_name;
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
    $('#update_data_btn').click(function() {
        var where_selected_01 = $.trim($('.where_selected_01').val());
        var where_01 = $.trim($('#where_01').val());
        
        var where_selected_02 = $.trim($('.where_selected_02').val()); 
        var where_02 = $.trim($('#where_02').val());
        
        var field_selected_01 = $.trim($('.field_selected_01').val()); 
        var field_01 = $.trim($('#field_01').val());
        
        
        $.ajax({
            url: 'update-data',
            data: {where_selected_01: where_selected_01, where_01: where_01,where_selected_02:where_selected_02,where_02:where_02,field_selected_01:field_selected_01,field_01:field_01},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var data =  data.message;
               $('#update_data_span').html('');
               $(data).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#update_data_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
               });
               
               /*for(var pk in data){
                    $('#update_data_span').html('');
                    var color = data['color'];
                    $('#update_data_span').append('<p style="font-weight:bold;color:'+color+'">'+data['msg'][0]+'</p>');
                    console.log(color,data['msg'][0]);
                }*/
                
               /*$(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#update_data_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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