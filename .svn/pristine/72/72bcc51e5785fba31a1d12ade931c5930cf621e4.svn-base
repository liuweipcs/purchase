<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\datetime\DateTimePicker;
use app\models\PurchaseHistory;
use app\config\Vhelper;
use app\models\PurchaseOrderItems;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;

use kartik\date\DatePicker;
use app\models\ProductTaxRate;
use app\models\Product;
$suppurl = \yii\helpers\Url::to(['/supplier/search-supplier']);
$this->title = '合同采购确认';
$this->params['breadcrumbs'][] = '海外仓';
$this->params['breadcrumbs'][] = '采购计划单';
$this->params['breadcrumbs'][] = $this->title;

$model = $models[0];

$settlement_ratio = '';
if(!empty($model->purchaseOrderPayType)) {
    $settlement_ratio = $model->purchaseOrderPayType->settlement_ratio;
}

?>
<style type="text/css">
    .label-box {
        position: absolute;
        top: 34px;
        left: 0;
        width: 500px;
        border: 1px solid #3c8dbc;
        padding: 8px;
        display: none;
        z-index: 500;
        background-color: #fff;
    }
    .label-box span:hover {
        background-color: red !important;
        cursor: pointer;
    }
    .tc {
        color: red;
    }
    .is_drawback, .freight_formula_mode {
        width: 100px;
    }
</style>

<div class="my-box" style="margin-bottom: 45px;">
    <div class="bg-line">
        <span>1</span>
        <p>确认采购单信息</p>
    </div>
    <div class="bg-line no">
        <span>2</span>
        <p>确认合同信息</p>
    </div>
</div>

<div class="my-box">
    <h5>共选择了 <strong class="tc"><?= count($models) ?></strong> 个订单，供应商名称为<b class="tc"><?= $model->supplier_name ?></b></h5>
</div>

<?php $form = ActiveForm::begin(['id' => 'compact-confirm']); ?>

<div class="my-box" style="border: 2px solid #3c8dbc; margin-bottom: 10px;">

    <h5><label class="label label-info">订单公共数据</label></h5>

    <div class="col-md-2">
        <div class="form-group">
            <label>采购来源</label>
            <input type="text" name="PurchaseOrder[purchase_source]" class="form-control" value="合同采购" disabled>
        </div>
    </div>

    <div class="col-md-2">


        <span class="suppname" style="display: none"><?= $model->supplier_name ?></span>


        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选供应商', 'id' => 'supplier_code', 'value' => $model->supplier_code],
            'pluginOptions' => [
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'allowClear' => true,
                'ajax' => [
                    'url' => $suppurl,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('供应商');
        ?>

        <a title="添加供应商" href="<?= Url::toRoute(['supplier/create'])?>" target="_blank" style="display: block;position: absolute;left: 65px;" class="glyphicon glyphicon-plus add-supp"></a>

    </div>


    <div class="col-md-2">
        <?= $form->field($model, 'pay_type')->dropDownList(SupplierServices::getDefaultPaymentMethod(), ['options' => [$model->pay_type => ['selected' => 'selected']]]); ?>
    </div>

    <div class="col-md-2">
        <?= $form->field($model, 'shipping_method')->dropDownList(PurchaseOrderServices::getShippingMethod(), ['options' => [$model->shipping_method => ['selected' => 'selected']]]); ?>
    </div>

    <div class="col-md-2">
        <?= $form->field($model, 'account_type')->dropDownList(SupplierServices::getSettlementMethod(), ['options' => [$model->account_type => ['selected' => 'selected']]]); ?>
    </div>

    <div class="col-md-2">
        <?= $form->field($model, 'date_eta')->widget(DateTimePicker::classname(), [
            'options' => ['placeholder' => '','value' => !empty($model->date_eta) ? $model->date_eta : date('Y-m-d', strtotime('+12 day'))],
            'pluginOptions' => [
                'autoclose' => true,
                'todayHighlight' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ]); ?>

    </div>

    <div class="col-md-2">
        <?= $form->field($model, 'is_transit')
            ->dropDownList(['2' => '否', '1' => '是'], ['options' => [$model->is_transit => ['selected' => 'selected']]])
            ->label('是否中转');
        ?>
    </div>

    <div class="col-md-2">
        <?= $form->field($model, 'transit_warehouse')
            ->dropDownList(['shzz' => '上海中转仓库', 'AFN' => '东莞中转仓库'], ['options' => [$model->transit_warehouse => ['selected' => 'selected']]]);
        ?>
    </div>

    <div class="col-md-3">
        <label>结算比例</label>
        <div class="input-group">
            <input type="text" id="settlement_ratio" name="PurchaseOrder[settlement_ratio]" class="form-control settlement_ratio" value="<?= $settlement_ratio ?>" readonly>
            <div class="input-group-btn">
                <button class="btn btn-default settlement_ratio_clear" type="button"><span class="glyphicon glyphicon-remove"></span></button>
                <button class="btn btn-default settlement_ratio_define" type="button">定义</button>
            </div>
            <div class="label-box">
                <span class="label label-info">10%</span>
                <span class="label label-info">20%</span>
                <span class="label label-info">30%</span>
                <span class="label label-info">40%</span>
                <span class="label label-info">50%</span>
                <span class="label label-info">60%</span>
                <span class="label label-info">70%</span>
                <span class="label label-info">80%</span>
                <span class="label label-info">90%</span>
                <span class="label label-info">100%</span>
                <span class="label label-danger">关闭</span>
            </div>
        </div>
    </div>

    <div class="col-md-1">
        <?= $form->field($model, 'is_drawback')
            ->dropDownList(['1' => '否', '2' => '是'], ['options' => [$model->is_drawback => ['selected' => 'selected']]])->label('是否退税');
        ?>
    </div>

    <p class="text_line"></p>

</div>

<?php

foreach($models as $k => $v):

    // 预设默认值 结算
    $v->account_type = $v->account_type ? $v->account_type : 2;
    // 支付
    $v->pay_type = $v->pay_type ? $v->pay_type : 2;
    // 运输
    $v->shipping_method = $v->shipping_method ? $v->shipping_method : 2;

    $freight_formula_mode = 'weight';
    if(!empty($v->purchaseOrderPayType)) {
        $freight_formula_mode = $v->purchaseOrderPayType->freight_formula_mode;
    }

    ?>

    <div class="my-box" style="border: 1px solid red;margin-bottom: 10px;">

        <label class="label label-info"><?= $k+1 ?></label>

        <label>PO号：</label>
        <input type="text" name="PurchaseOrders[pur_number][]" value="<?= $v->pur_number ?>" readonly>
        <input type="hidden" name="PurchaseNote[pur_number][]" value="<?= $v->pur_number ?>">

        <label>运费：</label>
        <input type="text" name="PurchaseOrders[freight][]" value="<?= !empty($v->purchaseOrderPayType) ? $v->purchaseOrderPayType->freight : 0; ?>">

        <label>优惠额：</label>
        <input type="text" name="PurchaseOrders[discount][]" value="<?= !empty($v->purchaseOrderPayType) ? $v->purchaseOrderPayType->discount : 0; ?>">

        <label>运费计算方式：</label>
        <select name="PurchaseOrders[freight_formula_mode][]" class="freight_formula_mode">
            <?php if($freight_formula_mode == 'volume'): ?>
                <option value="weight">重量</option>
                <option value="volume" selected>体积</option>
            <?php else: ?>
                <option value="weight" selected>重量</option>
                <option value="volume">体积</option>
            <?php endif; ?>
        </select>

        <table class="table table-bordered" style="margin-top: 10px;">
            <thead>
            <tr>
                <td>图片</td>
                <td>出口退税税率</td>
                <td>采购开票点</td>
                <td>SKU</td>
                <td>产品名</td>
                <td>采购数量</td>
                <td>单价</td>
                <td>金额</td>
                <td>产品链接</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody class="bs">

            <?php
            $total = 0;
            foreach($v->purchaseOrderItems as $k => $item):
                $price = Product::getProductPrice($item->sku);
                $price = !empty($price) ? $price : $item->price;

                $totalprice = $item->ctq*$price;
                $totalprices = $item->qty*$price;
                $total += $totalprice ? $totalprice : $totalprices;
                $img = Vhelper::downloadImg($item['sku'], $item['product_img'],2);
                $img = Html::img($img, ['width' => 100]);
                ?>
                <tr>
                    <td>
                        <?= Html::a($img, ['#'], ['class' => "img", 'data-skus' => $item['sku'], 'data-imgs' => $item['product_img'], 'title' => '大图查看', 'data-toggle' => 'modal', 'data-target' => '#created-modal']) ?>
                    </td>
                    <td>
                        <input type="text" class="hq" value="<?= ProductTaxRate::getRebateTaxRate($item['sku']); ?>" style="width: 80px" disabled>
                    </td>

                    <td>
                        <?= Html::input('number', 'ProductTaxRate[taxes][]', \app\models\PurchaseOrderTaxes::getABDTaxes($item['sku'],$item->pur_number), ['class' => 'hq', 'style' => ['width' => '60px'], 'max' => 100]); ?>
                        <?= Html::input('hidden', 'ProductTaxRate[sku][]', $item->sku) ?>
                        <?= Html::input('hidden', 'ProductTaxRate[pur_number][]', $item->pur_number) ?>
                    </td><!-- 彩购开票点 -->

                    <?= Html::input('hidden', 'PurchaseOrderItems[pur_number][]', $item->pur_number, ['class' => 'pur_number']) ?>
                    <?= Html::input('hidden', 'PurchaseOrderItems[sku][]', $item->sku) ?>

                    <td>
                        <?= Html::a($item->sku, ['#'], ['class' => "sales", 'data-sku' => $item->sku, 'title' => '销量统计', 'data-toggle' => 'modal', 'data-target' => '#created-modal']).(empty(\app\models\Product::findOne(['product_is_new'=>1,'sku'=>$item->sku]))?'':'<sub><font size="1" color="red">新</font></sub>') ?>
                    </td>


                    <td style="width: 200px;">
                        <a href="<?=Yii::$app->params['SKU_ERP_Product_Detail']. $item->sku?>" target="_blank" title="<?= $item->name ?>"><?= $item->name?></a>
                    </td>

                    <td class="ctqs">
                        <?= Html::input('number', 'PurchaseOrderItems[ctq][]', $item->ctq ? $item->ctq : $item->qty, ['class' => 'ctq', 'sku' => $item->sku ,'pur'=>$item->pur_number, 'onchange' =>"etaNumberChange(this)",  'data-ctq'=>$item->ctq?$item->ctq:$item->qty,'required'=>true,'min'=>1,'max'=>1000000]) ?>
                    </td>

                    <td>
                        <?= Html::input('text', 'PurchaseOrderItems[price][]', round($price,2), ['class' => 'price', 'sku'=>$item->sku ,'pur'=>$item->pur_number, 'onchange' => "unitPriceChange(this)", 'data-price'=>round($price,2),'required'=>true,'style'=>'width:60px;']) ?>
                    </td>

                    <td><?= Html::input('text', 'PurchaseOrderItems[totalprice][]',$totalprice ? $totalprice : $totalprices, ['class' => 'payable_amount', 'onchange' => "aaa(this)", 'readonly' => 'readonly']) ?></td>
                    <td>
                        <?php
                        $plink = $item->product_link ? $item->product_link : \app\models\SupplierQuotes::getUrl($item->sku);
                        echo Html::input('text', 'PurchaseOrderItems[product_link][]', $plink) ?>
                        <a href='<?= $plink ?>' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>
                    </td>

                    <td>
                        <a href="javascript:void(0)" data-sku="<?= $item->sku?>" data-pur="<?= $item->pur_number?>" data-k="<?= $k+1 ?>" class="dels">删除</a>
                    </td><!-- 删除sku -->
                </tr>

            <?php endforeach; ?>

            <tr class="table-module-b1">
                <td class="total" colspan="10">总额：<b><?=round($total,2).'&nbsp;&nbsp;'.$v->currency_code?></b></td>
            </tr>


            <tr>
                <td>确认备注</td>
                <td colspan="9">
                    <textarea id="purchasenote-note" name="PurchaseNote[note][]" rows="3" class="form-control"><?= !empty($v->orderNote->note) ? $v->orderNote->note : ''; ?></textarea>
                </td>
            </tr>


            </tbody>
        </table>

        <p class="text_line"></p>

    </div>

<?php endforeach; ?>

<button type="button" id="btn-submit" class="btn btn-primary abd_submit">保存并确认采购合同</button>

<?php ActiveForm::end(); ?>
<?php
Modal::begin([
    'id' => 'created-modal',
    'closeButton' => false,
    'size' => 'modal-lg',
]);
Modal::end();

$url_update_qty = Url::toRoute('edit-sku');
$url_sku_sales = Url::toRoute(['product/viewskusales']);
$imgurl = Url::toRoute(['purchase-suggest/img']);
$settlementurl = Url::toRoute(['supplier/get-supplier-issettlement']);
$count_arr = count($models);

$js = <<<JS
$(function() {
    
    $('[name="PurchaseOrder[supplier_code]"]').trigger('select2:select');
    
    $('#select2-supplier_code-container').text($('.suppname').text());
    
    // 定义结算比例
    $('.settlement_ratio_define').click(function() {
        $('.label-box').toggle();
    });
    
    // 清空结算比例
    $('.settlement_ratio_clear').click(function() {
        $('#settlement_ratio').val('');
    });
    
    // 设置结算比例
    $('.label-box span').click(function() {
        var parent = $(this).parents('.input-group');
        var _ratio = $('#settlement_ratio').val();

        if($(this).text() == '关闭') {
            $(this).parent().toggle();    
            return true;
        }
        if(_ratio == '') {
             _ratio += $(this).text();
        } else {
            var _ratioes = _ratio.split('+');
            if(_ratioes.length == 3) {
                layer.tips('最多只支持3个比例', $('#settlement_ratio'), {tips: 1});
                return false;
            }
            var total = parseInt($(this).text());
            for(i = 0; i < _ratioes.length; i++) {
                total += parseInt(_ratioes[i]);
            }
            if(total > 100) {
                layer.tips('总百分比不能超过100', $('#settlement_ratio'), {tips: 1});
                return false;
            }
             _ratio += '+'+$(this).text();
        }
        $('#settlement_ratio').val(_ratio);
    });
    
    // 表单提交验证
    $('#btn-submit').click(function() {
        var settlement_ratio = $('#settlement_ratio').val();
        if(settlement_ratio == '') {
            layer.alert('结算比例不能为空');
            return false;
        } 
        var _ratioes = settlement_ratio.split('+');
        var total = 0;
        for(i = 0; i < _ratioes.length; i++) {
            total += parseInt(_ratioes[i]);
        }
        if(total < 100) {
            layer.alert('结算比例总和必须等于100%');
            return false;
        }
        $('#compact-confirm').submit();
    });
    
    // 采购开票点改变事件
    $(".hq").change(function() {
        var s = $(this).parents('.bs').find('tr');
        for(var i=0; i<s.length-2; i++) {
            var inputs = $(s[i]).find('input.hq');
            var tax_rate = parseFloat($(inputs[0]).val()); // 出口退税税率
            var ticketed_point = parseFloat($(inputs[1]).val()); // 开票点
            if((tax_rate - ticketed_point) >= 3 ) {
                if(ticketed_point <=0 ) {
                    $(this).parents('.my-box').find('.is_drawback').val('1');
                } else {
                    $(this).parents('.my-box').find('.is_drawback').val('2');
                }
                break;
            }
        }
    });
    
    // sku销量查看
    $('.sales').click(function () {
        $.get('$url_sku_sales', {sku: $(this).attr('data-sku')},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    
    // 图片大图查看
    $('.img').click(function () {
        $.get('$imgurl', {img: $(this).attr('data-imgs'), sku: $(this).attr('data-skus')},
            function(data) {
                $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    
    // 删除sku
    $(".dels").click(function() {
        var sku = $(this).attr('data-sku');
        var pur = $(this).attr("data-pur");
        var k = $('.bs tr').length;
        if(k > 2) {
            layer.confirm('是否确认删除？', {
                btn: ['确定','取消']
            }, function() {
                $.get("$url_update_qty", {sku:sku, pur: pur}, function(result) {
                    if(result) {
                        layer.msg('删除成功');
                        window.location.reload();
                    } else {
                        layer.msg('删除失败');
                    }
                });
            });
        } else {
            layer.alert('剩下一个sku了，请直接撤销采购单');
        }
    });
    
});

JS;
$this->registerJs($js);
?>


<script type="text/javascript">

    //eta数量change
    function etaNumberChange(object){

        var sku=$(object).attr('sku');
        var pur=$(object).attr("pur");
        var ctq=object.value; //采购数量
        $.get("update-ctq",{sku:sku,pur:pur,ctq:ctq},function(result){
            console.log(result);
        });

        var tv=parseInt(object.value),
            tctq=$(object).attr('data-ctq');

        if(tv<tctq){
            alert('修改后的数量小于采购数量！');
        }

        var obj = $(object);
        var objTr = obj.parent().parent();
        //判断输入的数据是否是数字类型
        if(!testRegex(obj,obj.val())){
            return;
        }
        //获取单价
        var unitPrice = objTr.find(".price").val()*1;
        var sum = accMul(unitPrice,obj.val());
        objTr.find(".payable_amount").val(sum);
        setTotal();
    }

    //单价change
    function unitPriceChange(object){

        var tvp=parseFloat(object.value),
            tp=$(object).attr('data-price');

        var sku=$(object).attr('sku');
        var pur=$(object).attr("pur");
        $.ajax({
            url: 'view-update-log',
            data: {sku:sku,pur:pur},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                var bi = Math.round((tvp-tp)/tp * 10000) / 100.00 + "%";
                if(tvp>tp){
                    layer.alert('增长：' + bi + '\n修改后的单价大于系统单价！<br />' + data.message +  '<br /><span color="red">修改单价请到【供货商商品管理】页面修改</span>');
                } else if (tvp<tp){
                    layer.alert('下降：' + bi + '\n修改后的单价小于系统单价！<br />' + data.message +  '<br /><span color="red">修改单价请到【供货商商品管理】页面修改</span>');
                }
                $('.abd_submit').prop('disabled',true);
                console.log(data.message);
            }
        });


        var obj = $(object);
        var objTr = obj.parent().parent();
        //判断输入的数据是否是数字类型
        if(!testRegex(obj,obj.val())){
            return;
        }
        //获取单价
        var etaNumber = objTr.find(".ctq").val()*1;
        var sum =accMul(etaNumber,obj.val());
        objTr.find(".payable_amount").val(sum);
        setTotal();
    }
    //校验必须是数字
    function testRegex(object,number){
        var regex=/^[0-9]+\.?[0-9]*$/;
        if(regex.test(number)==false){
            //如果不是数字则提示客户
            alert('不是数字');
            return false;
        }
        return true;
    }
    function   accMul(arg1,arg2){
        //乘法
        var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
        try { m += s1.split(".")[1].length;}
        catch (e) {}
        try {m += s2.split(".")[1].length;}
        catch (e) {}
        return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
    }
    /*********************************计算所有的和****************************/


    function setTotal(){
        var s=0;
        $(".table tbody tr .ctqs").each(function(){

            s+=parseInt($(this).find('input[class*=ctq]').val())*parseFloat($(this).parent().find('input[class*=price]').val());

        });

        $(".total").html('总计金额:'+s.toFixed(2)+'RMB');
        // setTotal();
    }
</script>
