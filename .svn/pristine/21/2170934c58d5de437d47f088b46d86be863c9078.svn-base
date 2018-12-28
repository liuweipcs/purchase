<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;
use yii\helpers\Url;

?>

<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">处理 作废单被入错库</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>单子还是 等待审批状态  并不是作废状态  ，然后仓库那边也不知道这个单子是作废的   把货入进去了</li>
            <li>就是因为单子的状态没有及时变成作废状态</li>
            <li>所以仓库那边就把货入进去了</li>
            <li>要求：作废的单里面的东西和数量移到后面的实际付款单号中</li>
            <li>要求：将作废单号：【作废】  ----  实际付款单号：【全到货】</li>
        </ol>
        <div class="form-group">
            <label class="control-label" for="zuofei">作废订单号</label>
            <input type="text" id="zuofei" class="form-control">
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购订单主表</caption>
                    <tbody id="select_zuofei"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="quandao">实际付款单号</label>
            <input type="text" id="quandao" class="form-control">
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购订单主表</caption>
                    <tbody id="select_quandao"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <?php
            if(Helper::checkRoute('waste-all-arrival')) {
                echo '<span id="zuofei_btn" class="btn btn-success">处理入错库</span>';
            }
            ?>

        </div>
    </div>
    <div class="panel-footer">
        <span id="zuofei_span"></span>
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
    $("#zuofei").blur(function(){
        var pur_number = $.trim($('#zuofei').val());
        if(pur_number == '') {
            $('#select_zuofei').html('');
            return false;
        }

        $.ajax({
            url: 'select-waste-all-arrival',
            data: {pur_number: pur_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var purchase_order =  data.purchase_order;
               $('#select_zuofei').html('');
               if (purchase_order=='') {
                   $('#select_zuofei').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_zuofei').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['purchas_status'] +'</td>' +
                              '<td>' + v['pay_status'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    $("#quandao").blur(function(){
        var pur_number = $.trim($('#quandao').val());
        if(pur_number == '') {
            $('#select_quandao').html('');
            return false;
        }

        $.ajax({
            url: 'select-waste-all-arrival',
            data: {pur_number: pur_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var purchase_order =  data.purchase_order;
               $('#select_quandao').html('');
               if (purchase_order=='') {
                   $('#select_quandao').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_quandao').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['purchas_status'] +'</td>' +
                              '<td>' + v['pay_status'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //===============  处理 作废单被入错库   ======================
    $('#zuofei_btn').click(function() {
        var v1 = $.trim($('#zuofei').val());
        var v2 = $.trim($('#quandao').val());
        if(v1 == '' || v2 == '') {
            alert('采购单单号不能空');
            return false;
        }
        
        $.ajax({
            url: 'waste-all-arrival',
            data: {waste: v1, all_arrival: v2},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#zuofei_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#zuofei_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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

