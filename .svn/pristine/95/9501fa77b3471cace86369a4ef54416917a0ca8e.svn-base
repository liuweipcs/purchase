<?php

use app\services\PurchaseOrderServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
?>

    <div class="panel panel-success">
        <h4 align="center" style="font-weight:bold;color: red;">执行sql</h4>
        <div class="panel panel-body">
            <h4>温馨小提示:</h4>
            <ol style="color:red;font-weight: bold;">
                <li>要求：写入sql</li>
            </ol>
            <div class="form-group">
                <label class="control-label" for="sql_data">SQL</label>
                <textarea id="sql_data" class="form-control" rows="6" placeholder="请填写sql"></textarea>
                <!-- <input type="text" id="sql_data" class="form-control"> -->
            </div>
            <div class="form-group">
                <span id="sql_data_btn" class="btn btn-success">确认执行</span>
                <a href="https://www.bejson.com/" target="_blank">json格式化</a>
            </div>
        </div>
        <div class="panel-footer">
            <span id="sql_data_span"></span>
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
    //================ 确认执行数据  ================================
    $('#sql_data_btn').click(function() {
        var sql_data = $.trim($('#sql_data').val()); //sql语句
        if(sql_data == '') {
            alert('sql语句不能为空');
            return false;
        }

        $.ajax({
            url: 'sql',
            data: {sql_data: sql_data},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               
               $('#sql_data_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#sql_data_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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