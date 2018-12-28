<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\helpers\Url;
use app\models\PurchaseOrderItems;

$types = [
    '0' => '<label class="label label-default">待审核</label>',
    '1' => '<label class="label label-success">通过</label>',
    '2' => '<label class="label label-danger">不通过</label>',
];

?>

<div class="my-box" style="border: 1px solid #FF9800;">

    <h4><?= $model->module ?> <small><?= isset($types[$model->status]) ? $types[$model->status] : '未设置'; ?></small></h4>


    <?php

    $order = $model->purchaseOrder;
    $orderType = $model->purchaseOrderPayType;
    $orderItems = $model->purchaseOrderItems;

    ?>

    <label>采购单号:</label>
    <input type="text" value="<?= $model->pur_number ?>" disabled>

    <label>供应商:</label>
    <input type="text" value="<?= $order->supplier_name ?>" style="width: 500px;" disabled >

    <label>总金额:</label>
    <input type="text" value="<?= round(PurchaseOrderItems::getCountPrice($model->pur_number),2)?>" disabled>


    <?php if($orderType): ?>

        <label>运费:</label>
        <input type="text" value="<?= $orderType['freight'] ?>"  disabled="disabled">

        <label>优惠:</label>
        <input type="text" value="<?= $orderType['discount'] ?>"  disabled="disabled">

    <?php endif; ?>


    <table class="my-table">
        <tr>
            <td>图片</td>
            <td>SKU</td>
            <td>产品名称</td>
            <td>采购数量</td>
            <td>单价( RMB )</td>
            <td>金额</td>
        </tr>
        <?php
        foreach ($orderItems as $v) {
           // $img = Vhelper::toSkuImg($v['sku'], $v['product_img']);
            $img = \toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($v['sku'])]);
            ?>
            <tr>
                <td><?=Html::a($img,['#'], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal', 'data-skus' => $v['sku'],'data-imgs' => $v['product_img']])?></td>
                <td><?= $v['sku'] ?></td>
                <td><?= $v['name'] ?></td>
                <td><?= $v['ctq'] ?></td>
                <td><?= $v['price'] ?></td>
                <td><?= $v['ctq']*$v['price'] ?></td>
            </tr>
        <?php } ?>
    </table>

    <?php $content = !empty($model->content) ? json_decode($model->content, 1) : ''; ?>

    <div class="fg">

        <?php
        if(isset($content['old'])):
            $old = $content['old'];
            $freight = isset($old['freight']) ? $old['freight'] : 0;
            $discount = isset($old['discount']) ? $old['discount'] : 0;
            $account = isset($old['purchase_account']) ? $old['purchase_account'] : 0;
            $order_number = isset($old['platform_order_number']) ? $old['platform_order_number'] : 0;
            $note = isset($old['note']) ? $old['note'] : 0;
            ?>

            <p>修改前的数据：
                <span class="old">运费：<?= $freight ?></span>
                <span class="old">优惠：<?= $discount ?></span>
                <span class="old">账号：<?= $account ?></span>
                <span class="old">拍单号：<?= $order_number ?></span>
                <span class="old">备注：<?= $note ?></span>
            </p>


        <?php else: ?>
            <p>没有数据</p>
        <?php endif; ?>

        <?php
        if(isset($content['new'])):
            $new = $content['new'];
            $new_freight = isset($new['freight']) ? $new['freight'] : 0;
            $new_discount = isset($new['discount']) ? $new['discount'] : 0;
            $new_account = isset($new['purchase_acccount']) ? $new['purchase_acccount'] : 0;
            $new_order_number = isset($new['platform_order_number']) ? $new['platform_order_number'] : 0;
            $new_note = isset($new['note']) ? $new['note'] : 0;

            $is_freight = 0;
            $is_discount = 0;
            $is_account = 0;
            $is_order_number = 0;
            $is_note = 0;
            if(isset($content['old'])) {
                $old          = $content['old'];
                $freight      = isset($old['freight']) ? $old['freight'] : 0;
                $discount     = isset($old['discount']) ? $old['discount'] : 0;
                $account      = isset($old['purchase_account']) ? $old['purchase_account'] : 0;
                $order_number = isset($old['platform_order_number']) ? $old['platform_order_number'] : 0;
                $note         = isset($old['note']) ? $old['note'] : 0;
                if($new_freight !== $freight){
                    $is_freight = 1;
                }
                if($new_discount !== $discount){
                    $is_discount = 1;
                }
                if($new_order_number !== $order_number){
                    $is_order_number = 1;
                }
                if($new_account !== $account){
                    $is_account = 1;
                }
                if($new_note !== $note){
                    $is_note = 1;
                }
            }else{
                if($new_freight !== ''){
                    $is_freight = 1;
                }
                if($new_discount !== ''){
                    $is_discount = 1;
                }
                if($new_order_number !== ''){
                    $is_order_number = 1;
                }
                if($new_account !== ''){
                    $is_account = 1;
                }
                if($new_note !== ''){
                    $is_note = 1;
                }
            }

        ?>

            <p>修改后的数据：
                <span class="new" <?php if($is_freight){ echo "style='color:red'";}?>>运费：<?= $new_freight ?></span>
                <span class="new" <?php if($is_discount){ echo "style='color:red'";}?>>优惠：<?= $new_discount ?></span>
                <span class="new" <?php if($is_account){ echo "style='color:red'";}?>>账号：<?= $new_account ?></span>
                <span class="new" <?php if($is_order_number){ echo "style='color:red'";}?>>拍单号：<?= $new_order_number ?></span>
                <span class="new" <?php if($is_note){ echo "style='color:red'";}?>>备注：<?= $new_note ?></span>
            </p>


        <?php else: ?>
            <p>没有数据</p>
        <?php endif; ?>

    </div>

    <div class="fg">
        <p>
            修改人：<span class="new"><?= $model->username ?></span>
            修改时间：<span class="new"><?= $model->create_date ?></span>
            <input type="radio" name="status[<?= $model->id ?>]" value="1" checked> 通过
            <input type="radio" name="status[<?= $model->id ?>]" value="2"> 不通过
            <a href="javascript:void(0)" class="btn btn-info btn-xs shenghe" data-id="<?= $model->id ?>">审核</a>
        </p>

    </div>

</div>
<?php

$imgurl=Url::toRoute(['purchase-suggest/img']);
$js = <<<JS


    $(document).on('click', '.img', function () {
        $.get('{$imgurl}', {img:$(this).attr('data-imgs'),sku:$(this).attr('data-skus')},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
$(function(){
    
    $('.shenghe').click(function() {
        var index = layer.load(2, {shade: [0.8, '#141617bd;']});
        var id = $(this).attr('data-id');
        var status = $(this).parent().find('input[type="radio"]');
        var s = 1;
        for(var i=0; i<status.length; i++) { 
            if(status[i].checked){  
                s = status[i].value; 
            } 
        } 
        $.ajax({
            url: '',
            data: {id: id, status: s},
            dataType: 'json',
            type: 'post',
            success: function(e) {
                layer.close(index);
                if(e.error == 0) {
                    location.reload();
                } else {
                    lay.msg(e.message, {time: 3000});
                }
            }
        });
    });
    
});

JS;
$this->registerJs($js);
?>

