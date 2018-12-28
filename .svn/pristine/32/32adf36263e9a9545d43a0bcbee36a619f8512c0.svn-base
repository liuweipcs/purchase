<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\web\JsExpression;
use Yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model app\models\Stockin */

$url = Url::to(['/supplier/search-supplier']);
?>
<div class="stockin-update">
<?php
if($view =='update'){
    echo Html::button('申请修改',['class'=>'btn btn-info apply']);
}
?>
<table class="table table-bordered">
    <thead>
        <tr>
            <td><input type="checkbox" class="checkAll"></td>
            <td>审核状态</td>
            <td>SKU</td>
            <td>供应编码</td>
            <td>供应商名称</td>
            <td>单价</td>
            <td>采购链接</td>
        </tr>
    </thead>
    <tbody>
    <?php foreach($model->product as $v){?>
        <tr>
            <td>
                <?php
                $quotesId =  !empty($v->supplierQuote) ? $v->supplierQuote->id : 0;
                $supplier =  !empty($v->defaultSupplierDetail) ? $v->defaultSupplierDetail->supplier_code : '';
                $price =  !empty($v->supplierQuote) ? $v->supplierQuote->supplierprice : '';
                $link  = !empty($v->supplierQuote) ? $v->supplierQuote->supplier_product_address : '';
                echo Html::input('checkbox','',$v->sku,['class'=>'check','quotesId'=>$quotesId,'supplierCode'=>$supplier,'price'=>$price,'link'=>$link])
                ?>
            </td>
            <td><?=!empty($v->updateApply) ? '有' : '无';?></td>
            <td><?= $v->sku?></td>
            <td width="180px"><?= Select2::widget([ 'name' => 'title',
                    'options' => ['placeholder' => '请输入供应商 ...','disabled'=>$view=='update'?false:true],
                    'value'    =>!empty($v->defaultSupplierDetail) ? $v->defaultSupplierDetail->supplier_code : '',
                    'pluginOptions' => [
                        'placeholder' => 'search ...',
                        'allowClear' => true,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                        ],
                        'ajax' => [
                            'url' => $url,
                            'dataType' => 'json',
                            'data' => new JsExpression("function(params) { return {q:params.term}; }")
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(res) { return res.text; }'),
                        'templateSelection' => new JsExpression('function (res) { return res.id; }'),
                    ],
                ]);?></td>
            <td><?= !empty($v->defaultSupplierDetail) ? $v->defaultSupplierDetail->supplier_name : ''?></td>
            <td>
                <?php
                $price =  !empty($v->supplierQuote) ? $v->supplierQuote->supplierprice : '';
                echo Html::input('text',"SupplierQuotes[$v->id][supplierprice]",$price,['readonly'=>true,'class'=>'price dbclick','style'=>'width:70px' ,'type'=>'price']);
                ?>
            </td>
            <td>
                <?php
                $link = !empty($v->supplierQuote) ? $v->supplierQuote->supplier_product_address : '';
                echo Html::input('text',"SupplierQuotes[$v->id][link]",$link,['readonly'=>true,'class'=>'link dbclick','type'=>'link']);
                ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
    <?php $applyUrls  = Url::toRoute('/supplier-goods/apply');?>
    <?php
    $js = <<<JS
    $(document).on('click', '.checkAll', function () {
        if($(this).is(':checked')){
            $('.check').prop('checked',true);
            console.log(1);
        }else {
            console.log(2);
            $('.check').prop('checked',false);
        }
    });

        $('.apply').click(function(){
        var data = new Array();
        $('input.check').each(function(){
            if($(this).is(':checked')){
                var skudata = {quoteId:$(this).attr('quotesid'),sku:$(this).val(),suppliercode:$(this).attr('suppliercode'),price:$(this).attr('price'),link:$(this).attr('link')}
                data.push(skudata);
            }
        })
        if(data.length == 0){
            alert('请至少选择一个！');
        }else {
            $.ajax({
            url:'{$applyUrls}',
            data:{data:data},
            type: 'post',
            dataType:'json',
            success:function(data){
                window.location.reload();
            }
        });
        }
    });

    //双击编辑报价
    $("input.dbclick").dblclick(function(){
            $(this).removeAttr("readonly");
        });
    //失焦添加readonly
    $("input.dbclick").blur(function(){
        var type = $(this).attr('type');
        $(this).attr("readonly","true");
        //将修改的值传入复选框属性
        $(this).closest('tr').find('input.check').attr(type,$(this).val());
    });

    $("select").change(function(){
            var supplier = $(this).select2("val");
            console.log(supplier);
            //将数据写入复选框属性
            $(this).closest('tr').find('input.check').attr('suppliercode',supplier);
        });

JS;

    $this->registerJs($js);
    ?>
</div>