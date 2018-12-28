<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\web\JsExpression;
use Yii\helpers\Url;
use app\models\SupplierUpdateLog;
use app\services\SupplierServices;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use app\models\SupplierBuyer;
use app\controllers\SupplierController;
use app\models\SupplierLog;
use yii\bootstrap\Modal;
?>
<div class="stockin-update">
    <table class="table table-bordered">
        <thead>
            <tr>
                <td>时间</td>
                <td>操作人</td>
                <td>明细</td>
            </tr>
        </thead>
        <tbody>
        <?php
        //更新供应商
        $supplier_update_info = SupplierUpdateLog::getSupplierUpdateInfo($supplier_code);
        if (!empty($supplier_update_info)) {
            foreach ($supplier_update_info as $value) {
                echo "<tr>";
                echo "<td>{$value['time']}</td>";
                echo "<td>{$value['buyer']}</td>";
                echo "<td>{$value['detail']}</td>";
                echo "</tr>";
            }
        }
        //审核
        $supplier_audit_info = SupplierUpdateLog::getSupplierAuditInfo($supplier_code);
        if (!empty($supplier_audit_info)) {
            foreach ($supplier_audit_info as $value) {
                echo "<tr>";
                echo "<td>{$value['time']}</td>";
                echo "<td>{$value['buyer']}</td>";
                echo "<td>{$value['detail']}</td>";
                echo "</tr>";
            }
        }
        //是否禁用供应商和搜索
        $supplier_log_info = SupplierLog::getSupplierLogInfo($supplier_code);
        if (!empty($supplier_log_info)) {
            foreach ($supplier_log_info as $value) {
                echo "<tr>";
                echo "<td>{$value['time']}</td>";
                echo "<td>{$value['buyer']}</td>";
                echo "<td>{$value['detail']}</td>";
                echo "</tr>";
            }
        }
        ?>
        </tbody>
    </table>
    <div id="outerdiv" style="position:fixed;top:100px;left:30%;background:rgba(0,0,0,0.7);z-index:2000;display:none;">
        <div id="innerdiv" style="position:absolute;"><img id="bigimg" style="border:5px solid #fff;" src="" /></div>
    </div>
</div>
<?php
$js = <<<JS

//图片放大  
      $(function(){  
        $(".viewImg2").click(function(){  
            var _this = $(this);//将当前的pimg元素作为_this传入函数    
            imgShow("#outerdiv", "#innerdiv", "#bigimg", _this);    
          });
        });    
      
        function imgShow(outerdiv, innerdiv, bigimg, _this){  
            var src = _this.attr("src");//获取当前点击的pimg元素中的src属性    
            $('#outerdiv').attr('display','block');  
            $(bigimg).attr("src", src);//设置#bigimg元素的src属性    
             $(outerdiv).fadeIn("fast");  
            
        $(outerdiv).click(function(){//再次点击淡出消失弹出层    
            $(this).fadeOut("fast");    
        });    
    }  
    
   
JS;
$this->registerJs($js);
?>