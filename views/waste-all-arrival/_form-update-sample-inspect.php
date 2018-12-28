<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;
use yii\helpers\Url;
use app\services\SupplierServices;

?>
<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">供应商整合</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>要求：知道【sku】</li>
            <li>表名：pur_sample_inspect</li>
        </ol>
        <div class="form-group">
            <label class="control-label">
                ID
                <input type="text" id="update_sample_inspect_id" class="form-control update_sample_inspect_class">
            </label>
            <label class="control-label">
                SKU
                <input type="text" id="update_sample_inspect_sku" class="form-control update_sample_inspect_class">
            </label>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>样品检验</caption>
                    <tbody id="select_sample_inspect"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label">
                质检结果
                <select id="update_sample_inspect_qc_result" class="form-control">
                    <option value="">请选择...</option>
                    <?php
                    foreach (SupplierServices::getSampleResultStatus() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v  . ' 【' . $k .'】</option>';
                    }
                    ?>
                </select>
            </label>
        </div>
        <div class="form-group">
            <?php
            if(Helper::checkRoute('update-sample-inspect')) {
                echo '<span id="update_sample_inspect_btn" class="btn btn-success">修改</span>';
            }
            ?>

        </div>
    </div>

    <div class="panel-footer">
        <span id="update_sample_inspect_span"></span>
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
    $(".update_sample_inspect_class").blur(function(){
        var id = $.trim($('#update_sample_inspect_id').val()); 
        var sku = $.trim($('#update_sample_inspect_sku').val()); 
        if(id == '') {
            if (sku == '') {
                $('#select_sample_inspect').html('');
                return false;
            }
        }
        
        $.ajax({
            url: 'select-sample-inspect',
            data: {id:id,sku:sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                //采购单物流信息表
               var sample_inspect_info =  data.sample_inspect_info;
               $('#select_sample_inspect').html('');
               if (sample_inspect_info=='') {
                   $('#select_sample_inspect').append('<span style="font-weight: bold;color:red;">没有该数据</span>');
               } else {
                   $(sample_inspect_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_sample_inspect').append('<tr class="size-row success">' +
                              '<td>' + v['id'] +'</td>' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['qc_result'] +'</td>' +
                              '<td>' + v['confirm_user_name'] +'</td>' +
                              '<td>' + v['pur_number'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //===============  修改到货记录   ======================
    $('#update_sample_inspect_btn').click(function() {
        var id = $.trim($('#update_sample_inspect_id').val()); 
        var sku = $.trim($('#update_sample_inspect_sku').val()); 
        var qc_result = $.trim($('#update_sample_inspect_qc_result').val());
        if(id == '') {
            alert('请输入ID');
            return false;
        }
                
        $.ajax({
            url: 'update-sample-inspect',
            data: {id:id,sku: sku,qc_result:qc_result},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_sample_inspect_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     console.log(color,v['msg']);
                     $('#update_sample_inspect_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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

