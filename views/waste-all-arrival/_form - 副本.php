<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;

/* @var $this yii\web\View */
/* @var $model app\models\OverseasWarehouseGoodsTaxRebate */
/* @var $form yii\widgets\ActiveForm */
?>
<?php
if(Helper::checkRoute('suggest-notes')) {

}
?>

<div class="container-fluid">
    <div class="row">
        <!--******************  处理 作废单被入错库【开始】  *************************-->
        <div class="col-md-6">
            <?= $this->render('_form-waste-all-arrival', []) ?>

            <!--<div class="panel panel-success">
                <h4 align="center" style="font-weight:bold;color: red;">处理 作废单被入错库</h4>
                <div class="panel panel-body">
                    <h4>温馨小提示:</h4>
                    <ol style="color:red;font-weight: bold;">
                        <li>作废的单里面的东西和数量移到后面的实际付款单号中</li>
                        <li>将作废单号：【作废】  ----  实际付款单号：【全到货】</li>
                    </ol>
                    <div class="form-group">
                        <label class="control-label" for="zuofei">作废订单号</label>
                        <input type="text" id="zuofei" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="quandao">实际付款单号</label>
                        <input type="text" id="quandao" class="form-control">
                    </div>
                    <div class="form-group">
                    <span id="zuofei_btn" class="btn btn-success">Create</span>
                    </div>
                </div>
                <div class="panel-footer">
                    <span id="zuofei_span"></span>
                </div>
            </div>-->
        </div>
        <!--******************  处理 作废单被入错库【结束】  *************************-->
        <!--******************  海外仓-采购建议-供应商：新建采购计划单报错问题 【开始】  *************************-->
        <div class="col-md-6">
            <div class="panel panel-success">
                <h4 align="center" style="font-weight:bold;color: red;">海外仓-采购建议-供应商：新建采购计划单报错问题</h4>
                <div class="panel panel-body">
                    <h4>温馨小提示:</h4>
                    <ol style="color:red;font-weight: bold;">
                        <li>【海外仓-采购建议-供应商】栏目--新建采购计划单报错</li>
                    </ol>
                    <div class="form-group">
                        <label class="control-label" for="supplier_name">供应商名</label>
                        <input type="text" id="supplier_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="sku">sku</label>
                        <input type="text" id="sku" class="form-control">
                    </div>
                    <div class="form-group">
                        <span id="hwc_create_order" class="btn btn-success">Create</span>
                    </div>
                </div>
                <div class="panel-footer">
                    <span id="hwc_create_order_span"></span>
                </div>
            </div>
        </div>
        <!--******************  海外仓-采购建议-供应商：新建采购计划单报错问题 【结束】  *************************-->
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-success">
                    <div class="panel panel-body">
                        <h4>温馨小提示:</h4>
                        <ol style="color:red;font-weight: bold;">
                            <li style="border-bottom: 1px solid #ccc;">

                                如果下拉框中搜索不到【供应商】名称：
                                <ol>
                                    <li>有空格</li>
                                    <li>或者是不是这家供应商禁用了【供货商管理】</li>
                                </ol>

                            </li>
                            <li style="border-bottom: 1px solid #ccc;">如果采购员下拉框中搜不到【采购员】：1.在采购用户里面加【新增采购用户】用户名（采购员）、采购小组（1组）、级别（组员）、用户类型（国内采购组）</li>
                            <li style="border-bottom: 1px solid #ccc;">PO381026 这个是通途的  暂时不处理【对接陈望】</li>
                            <li style="border-bottom: 1px solid #ccc;">PO026317  请问现在我作废的订单，仓库还可以入吗【对接陈望】</li>
                            <li style="border-bottom: 1px solid #ccc;">修改订单状态【pur_purchase_order  purchas_status】??</li>
                            <li style="border-bottom: 1px solid #ccc;">成金红  PO060482  恭喜你找到我  麻烦修改下供应商（未处理）-----  作废（未处理）</li>
                            <li style="border-bottom: 1px solid #ccc;">给用户开权限？？</li>
                            <li style="border-bottom: 1px solid #ccc;">李华英(李华英)  导数据（未处理）</li>
                            <li style="border-bottom: 1px solid #ccc;">徐梦梦  国内</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>






<?php
$viewUrl = \yii\helpers\Url::toRoute('/waste-all-arrival/waste-all-arrival');
$js = <<<JS
$(function() {
    var kkk = {
        red: 'red',
        green: 'green',
        dark: 'dark',
    };
    //===============  处理 作废单被入错库   ======================
    // $('#zuofei_btn').click(function() {
    //     var v1 = $.trim($('#zuofei').val());
    //     var v2 = $.trim($('#quandao').val());
    //     if(v1 == '' || v2 == '') {
    //         alert('采购单单号不能空');
    //         return false;
    //     }
    //    
    //     $.ajax({
    //         url: 'waste-all-arrival',
    //         data: {waste: v1, all_arrival: v2},
    //         type: 'post',
    //         dataType: 'json',
    //         success: function(data) {
    //            var d =  data.message;
    //            $('#zuofei_span').html('');
    //            $(d).each(function(k,v) {
    //                  var color = kkk[v['color']];
    //                  $('#zuofei_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
    //            });
    //         }
    //     });   
    // });
    //================  海外仓-采购建议-供应商：新建采购计划单报错问题  ================================
    $('#hwc_create_order').click(function() {
        var v1 = $.trim($('#supplier_name').val()); //供应商名称
        var v2 = $.trim($('#sku').val()); //sku
        if(v1 == '' || v2 == '') {
            alert('供应商名称 或 sku 不能为空');
            return false;
        }
        
        $.ajax({
            url: 'update-is-purchase',
            data: {supplier_name: v1, sku: v2},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#hwc_create_order_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#hwc_create_order_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
               });
            }
        });   
    });
    //=============================================================================
});
JS;
$this->registerJs($js);
?>





