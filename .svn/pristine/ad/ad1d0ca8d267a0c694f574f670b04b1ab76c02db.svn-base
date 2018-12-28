<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use app\models\ProductTaxRate;
use app\models\PurchaseOrderTaxes;



$this->title = '合同采购确认';
$this->params['breadcrumbs'][] = '海外仓';
$this->params['breadcrumbs'][] = '采购计划单';
$this->params['breadcrumbs'][] = '关联合同';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .col-md-1, .col-md-2, .col-md-3 {
        padding: 5px;
    }
</style>

<?php $form = ActiveForm::begin(['id' => 'compact-confirm']); ?>

<div class="box box-success">

    <div class="box-header">合同信息</div>

    <div class="box-body">

        <div class="col-md-1">
            <label>采购来源</label>
            <input type="text" class="form-control" value="合同采购" disabled>
            <input type="hidden" name="PurchaseOrder[source]" value="1"><!-- 合同 -->
            <input type="hidden" name="PurchaseOrder[submit]" value="2"><!-- 直接提交 -->
            <input type="hidden" name="System[compact_number]" value="<?= $cpn ?>">
            <input type="hidden" name="System[pur_number]" value="<?= $opn ?>">
            <input type="hidden" name="PurchaseOrder[pur_number]" value="<?= $opn ?>">
        </div>

        <div class="col-md-3">
            <label>供应商</label>
            <input type="text" name="PurchaseOrder[supplier_name]" class="form-control" value="<?= $model->supplier_name ?>" readonly>
            <input type="hidden" name="PurchaseOrder[supplier_code]" class="form-control" value="<?= $model->supplier_code ?>">
        </div>

        <div class="col-md-2">
            <label>支付方式</label>
            <input type="text" name="PurchaseOrder[pay_type]" class="form-control" value="<?= !empty($model->pay_type) ? SupplierServices::getDefaultPaymentMethod($model->pay_type) : ''; ?>" disabled>
            <input type="hidden" name="PurchaseOrder[pay_type]" class="form-control" value="<?= $model->pay_type ?>">
        </div>

        <div class="col-md-1">
            <label>供应商运输</label>
            <input type="text" name="PurchaseOrder[shipping_method]" class="form-control" value="<?= !empty($model->shipping_method) ? PurchaseOrderServices::getShippingMethod($model->shipping_method) : ''; ?>" disabled>
            <input type="hidden" name="PurchaseOrder[shipping_method]" class="form-control" value="<?= $model->shipping_method ?>">
        </div>

        <div class="col-md-2">
            <label>结算方式</label>
            <input type="text" name="PurchaseOrder[account_type]" class="form-control" value="<?= !empty($model->account_type) ? SupplierServices::getSettlementMethod($model->account_type) : ''; ?>" disabled>
            <input type="hidden" name="PurchaseOrder[account_type]" class="form-control" value="<?= $model->account_type ?>">
        </div>

        <div class="col-md-2">
            <label>预计到货时间</label>
            <input type="text" name="PurchaseOrder[date_eta]" class="form-control" value="<?= $model->date_eta ?>" readonly>
        </div>

        <div class="col-md-1">
            <label>是否中转</label>
            <?php $a = ($model->is_transit == 0) ? '否' : '是'; ?>
            <input type="text" class="form-control" value="<?= $a ?>" disabled>
            <input type="hidden" name="PurchaseOrder[is_transit]" class="form-control" value="<?= $model->is_transit ?>">
        </div>

        <div class="col-md-1">
            <label>结算比例</label>
            <input type="text" id="settlement_ratio" name="PurchaseOrderPayType[settlement_ratio]" class="form-control" value="<?= $compact->settlement_ratio ?>" readonly>
        </div>

        <div class="col-md-2">
            <label>中转仓库</label>
            <input type="text" name="PurchaseOrder[transit_warehouse]" class="form-control" value="<?= $model->transit_warehouse ?>" readonly>
        </div>

        <div class="col-md-1">
            <label>是否退税</label>
            <?php $t = ($compact->is_drawback == 2) ? '是' : '否'; ?>
            <input type="text" name="PurchaseOrder[is_drawback]" class="form-control" value="<?= $t ?>" disabled>
            <input type="hidden" name="PurchaseOrder[is_drawback]" value="<?= $compact->is_drawback ?>">
        </div>

    </div>

</div>

<?php
    // 预设默认值 结算
    $order->account_type = $order->account_type ? $order->account_type : 2;
    // 支付
    $order->pay_type = $order->pay_type ? $order->pay_type : 2;
    // 运输
    $order->shipping_method = $order->shipping_method ? $order->shipping_method : 2;

    $freight_formula_mode = 'weight';
    if(!empty($order->purchaseOrderPayType)) {
        $freight_formula_mode = $order->purchaseOrderPayType->freight_formula_mode;
    }

    ?>

    <div class="my-box" style="border: 1px solid red;margin-bottom: 10px;">

        <label>PO号：</label>
        <input type="text" name="PurchaseOrderPayType[pur_number]" value="<?= $order->pur_number ?>" readonly>

        <label>运费：</label>
        <input type="text" name="PurchaseOrderPayType[freight]" value="<?= !empty($order->purchaseOrderPayType) ? $order->purchaseOrderPayType->freight : 0; ?>">

        <label>优惠额：</label>
        <input type="text" name="PurchaseOrderPayType[discount]" value="<?= !empty($order->purchaseOrderPayType) ? $order->purchaseOrderPayType->discount : 0; ?>">

        <label>运费计算方式：</label>
        <select name="PurchaseOrderPayType[freight_formula_mode]" class="freight_formula_mode">
            <option value="weight" selected>重量</option>
            <option value="volume">体积</option>
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
            foreach($orderItems as $k => $item):
                $totalprice = $item->ctq*$item->price;
                $totalprices = $item->qty*$item->price;
                $total += $totalprice ? $totalprice : $totalprices;
                $img = Vhelper::downloadImg($item['sku'], $item['product_img'],2);
                $img = Html::img($img, ['width' => 100]);
                ?>
                <tr>
                    <td>
                        <?= Html::a($img, ['#'], ['class' => "img", 'data-skus' => $item['sku'], 'data-imgs' => $item['product_img'], 'title' => '大图查看', 'data-toggle' => 'modal', 'data-target' => '#created-modal']) ?>
                    </td>



                    <td>
                        <input type="text"  class="hq" name="" value="<?=ProductTaxRate::getRebateTaxRate($item['sku']); ?>" style="width: 80px" disabled>
                    </td>

                    <?php
                        $taxe = PurchaseOrderTaxes::getTaxes($item['sku'], $item['pur_number']);
                    ?>

                    <td>

                        <?= Html::input('number', 'ProductTaxRate[taxes][]', $taxe, ['class' => 'hq', 'style' => ['width' => '60px'], 'max' => 100]); ?>
                        <?= Html::input('hidden', 'ProductTaxRate[sku][]', $item->sku) ?>
                        <?= Html::input('hidden', 'ProductTaxRate[pur_number][]', $item->pur_number) ?>
                    </td><!-- 彩购开票点 -->

                    <?= Html::input('hidden', 'PurchaseOrderItems[pur_number][]', $item->pur_number, ['class' => 'pur_number']) ?>
                    <?= Html::input('hidden', 'PurchaseOrderItems[sku][]', $item->sku) ?>


                    <td>
                        <?= Html::a($item->sku, ['#'], ['class' => "sales", 'data-sku' => $item->sku, 'title' => '销量统计', 'data-toggle' => 'modal', 'data-target' => '#created-modal']) ?>
                    </td>


                    <td style="width: 200px;">
                        <a href="<?=Yii::$app->params['SKU_ERP_Product_Detail']. $item->sku?>" target="_blank" title="<?= $item->name ?>"><?= $item->name?></a>
                    </td>



                    <td class="ctqs">
                        <?= Html::input('number', 'PurchaseOrderItems[ctq][]', $item->ctq ? $item->ctq : $item->qty, ['class' => 'ctq', 'sku' => $item->sku ,'pur'=>$item->pur_number, 'onchange' =>"etaNumberChange(this)",  'data-ctq'=>$item->ctq?$item->ctq:$item->qty,'required'=>true,'min'=>1,'max'=>1000000]) ?>
                    </td>



                    <td>
                        <?= Html::input('text', 'PurchaseOrderItems[price][]', round($item->price,2), ['class' => 'price', 'onchange' => "unitPriceChange(this)", 'data-price'=>round($item->price,2),'required'=>true,'style'=>'width:60px;']) ?>
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
                <td class="total" colspan="10">总额：<b><?=round($total,2).'&nbsp;&nbsp;'.$order->currency_code?></b></td>
            </tr>


            <tr>
                <td>确认备注</td>
                <td colspan="9">
                    <input type="hidden" name="PurchaseNote[pur_number]" value="<?= $opn ?>">
                    <textarea id="purchasenote-note" name="PurchaseNote[note]" rows="3" class="form-control"><?= !empty($order->orderNote->note) ? $order->orderNote->note : ''; ?></textarea>
                </td>
            </tr>

            </tbody>
        </table>

    </div>

<button type="button" id="btn-submit" class="btn btn-primary">保存</button>

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
//$count_arr = count($models);

$js = <<<JS

$(function() {
    
    // 表单提交验证
    $('#btn-submit').click(function() {
        $('#compact-confirm').submit();
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

        if(tvp>tp){
            alert('修改后的单价大于系统单价！');
        }

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
