<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
$img = Html::img(Vhelper::downloadImg($mod->sku, $img, 2), ['width' => '110px', 'class' => 'img-thumbnail']);
?>

<style type="text/css">
    em {
        color: red;
        font-weight: bold;
        font-style: normal;
    }
    .row {
        padding: 10px;
    }
    .important {
        color: red;
    }
    input[type=number] {
        width: 60px;
    }
</style>
<?php ActiveForm::begin(['id' => 'fm']); ?>
<h5>报损信息审核</h5>
<div class="container-fluid" style="border:1px solid #ccc;">

    <input type="hidden" name="id" value="<?= $mod->id ?>">
    <input type="hidden" name="price" value="<?= $mod->price ?>">

    <div class="row">
        <div class="col-md-2">
            <?= $img ?>
        </div>

        <div class="col-md-6">
            <p>采购单号：<strong><?= $mod->pur_number ?></strong></p>
            <p>SKU：<strong><?= $mod->sku ?></strong></p>
            <p>单价：<strong><?= $mod->price ?></strong></p>
            <p><?= $mod->name ?></p>
        </div>

        <div class="col-md-4">
            <p>订单数量：<strong><?= $mod->ctq ?></strong></p>
            <p>入库数量：<strong><?= $mod->qty ?></strong></p>
            <p>取消数量：<strong><?= \app\models\PurchaseOrderCancelSub::getCancelCtq($mod->pur_number,$mod->sku); ?></strong></p>
            <p>报损数量：<input type="number" name="breakage_num" id="breakage_num" data-price="<?= $mod->price ?>" value="<?= $mod->breakage_num ?>" min="0" max="<?= $mod->ctq ?>"></p>
            <p>报损金额：<strong class="important"><?= $mod->items_totalprice ?></strong></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <label>审核备注</label>
            <textarea class="form-control" name="audit_notice" rows="3" disabled><?= $mod->audit_notice ?></textarea>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <label>申请备注</label>
            <textarea class="form-control" name="apply_notice" id="apply_notice"  rows="3"><?= $mod->apply_notice ?></textarea>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <span class="btn btn-success" id="btn-submit">提交</span>
        </div>
    </div>

</div>

<?php
ActiveForm::end();
?>
<?php
$js=<<<JS
$(function() {
    
    function accMul(arg1, arg2)
    {
        var m=0,s1=arg1.toString(),s2=arg2.toString();
        try{m+=s1.split(".")[1].length}catch(e){}
        try{m+=s2.split(".")[1].length}catch(e){}
        return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m); 
    }
    
    $('#breakage_num').click(function() {
        var num = $(this).val();
        price = $(this).attr('data-price');
        var money = accMul(num, price);
        $('.important').text(money);
    });
    
    $('#btn-submit').click(function() {
        if($('#apply_notice').val() == '') {
            layer.alert('需要填写备注');
            return false;
        }
        $('#fm').submit();
    });
    
});
JS;
$this->registerJs($js);
?>
