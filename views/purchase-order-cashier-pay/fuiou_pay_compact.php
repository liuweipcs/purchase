<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/9
 * Time: 11:04
 */
use yii\widgets\ActiveForm;
use app\models\PurchaseCompactItems;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderPay;
?>
<style>
    .pay_span {padding:0px 1% 0px 1%;color:black;float:left;font-weight:bold;}
</style>
<?php
if(!empty($model)) {
?>
<?php $form = ActiveForm::begin(['id'=>'fuiou_pay_form']); ?>
<?=\yii\helpers\Html::hiddenInput('ids',implode(',',$ids))?>
<p class="glyphicon glyphicon-folder-open"></p>&nbsp;&nbsp;&nbsp;基本资料
<table class="table">
    <tr>
        <td>供应商名称</td>
        <td><?php
            $supplier_code = isset($model[0]) ? $model[0]->supplier_code :'';
            $supplier_name = \app\models\Supplier::find()->select('supplier_name')->where(['supplier_code'=>$supplier_code])->scalar();
            $ufxiouCharge  = \app\models\DataControlConfig::find()->select('values')
                ->where(['type'=>'ufxiouCharge'])->scalar();
            $ufxiouCharge = $ufxiouCharge ? $ufxiouCharge : 1 ;
            echo $supplier_name ? $supplier_name :'';
            ?>
        </td>
    </tr>
</table>
<a class="glyphicon glyphicon-bell compact_button" title="点击展示合同详情">付款合同信息<a/>
<table class="table compact_info" style="vertical-align: middle;display: none">
    <thead>
    <th>合同单号</th>
    <th>采购单号</th>
    <th>sku</th>
    <th>单价</th>
    <th>采购数量</th>
    <th>金额</th>
    <th>运费</th>
    <th>优惠</th>
    <th>请款金额</th>
    <th>请款比例</th>
    <th>结算比例</th>
    <th>请款类型</th>
    <th>请款时间</th>
    <th>请款人</th>
    <th>请款备注</th>
    </thead>
    <tbody>
    <?php $totalPrice = 0;
    $itemsPrice=0;
    $itemsAmount=0;
    $orderFright=0;
    $orderDiscount=0;
    $payNoteRowspan = PurchaseOrderPay::find()
        ->alias('t')
        ->leftJoin(PurchaseCompactItems::tableName().' orderCompact','orderCompact.compact_number=t.pur_number')
        ->leftJoin(PurchaseOrder::tableName().' order','orderCompact.pur_number=order.pur_number')
        ->leftJoin(PurchaseOrderItems::tableName().' orderItem','orderCompact.pur_number=orderItem.pur_number')
        ->where(['orderCompact.bind'=>1])
        ->andWhere(['t.id'=>$ids])
        ->count('orderItem.id');
    ?>
    <?php foreach ($model as $key=>$value){?>
        <?php $totalPrice += 1000*$value->pay_price;$circly=$key;?>
        <?php
        $purNumbers = PurchaseCompactItems::findAll(['compact_number'=>$value->pur_number,'bind'=>1]);
        $compactRowspan = PurchaseCompactItems::find()
            ->alias('t')
            ->leftJoin(PurchaseOrder::tableName().' order','t.pur_number=order.pur_number')
            ->leftJoin(PurchaseOrderItems::tableName().' orderItem','t.pur_number=orderItem.pur_number')
            ->where(['t.compact_number'=>$value->pur_number,'t.bind'=>1])
            ->count('orderItem.id');
        ?>
        <?php foreach ($purNumbers as $k=>$purNumber){?>
            <?php $ordercircly = $k;?>
            <?php $purchaseOrderItems = PurchaseOrderItems::findAll(['pur_number'=>$purNumber->pur_number])?>
            <?php $purchaseOrderPayInfo = PurchaseOrderPayType::findOne(['pur_number'=>$purNumber->pur_number]) ?>
            <?php foreach ($purchaseOrderItems as $itemKey=>$item){?>
            <tr>
            <?php if($circly==$key){?>
                <td rowspan="<?=$compactRowspan?>" style="vertical-align: middle"><?= \yii\helpers\Html::a($value->pur_number,['purchase-compact/show-form','id'=>$value->id],['data-toggle' => 'modal',
                        'data-target' => '#compact-pay','class'=>'pay_apply'])?></td>
            <?php }?>
                <?php if($ordercircly==$k){?>
            <td style="vertical-align: middle" rowspan="<?=count($purchaseOrderItems)?>"><?=$purNumber->pur_number?></td>
                    <?php }?>
            <td style="vertical-align: middle"><?=$item->sku?></td>
            <td style="vertical-align: middle"><?=$item->price?></td>
            <td style="vertical-align: middle"><?=$item->ctq?></td>
            <td style="vertical-align: middle"><?=($item->ctq*($item->price*1000))/1000?></td>
            <?php if($ordercircly==$k){?>
                <?php
                    $freight = empty($purchaseOrderPayInfo->freight) ? 0 : $purchaseOrderPayInfo->freight;
                    $discount = empty($purchaseOrderPayInfo->discount) ? 0 : $purchaseOrderPayInfo->discount;
                    $orderFright = (1000*$orderFright+$freight*1000)/1000;
                    $orderDiscount = (1000*$orderDiscount+$discount*1000)/1000;
                ?>
            <td  rowspan="<?=count($purchaseOrderItems)?>" style="vertical-align: middle"><?=$freight?></td>
            <td  rowspan="<?=count($purchaseOrderItems)?>" style="vertical-align: middle"><?=$discount?></td>
            <?php }?>
            <?php
                $itemsAmount+=$item->ctq;
                $itemsPrice= ((1000*$itemsPrice)+($item->ctq*($item->price*1000)))/1000;
            ?>
            <?php if($circly==$key){?>
                <td rowspan="<?=$compactRowspan?>"  style="vertical-align: middle"><?= $value->pay_price?></td>
                <td rowspan="<?=$compactRowspan?>"  style="vertical-align: middle"><?= $value->pay_ratio?></td>
                <td rowspan="<?=$compactRowspan?>"  style="vertical-align: middle"><?= $value->js_ratio?></td>
                <td rowspan="<?=$compactRowspan?>"  style="vertical-align: middle"><?= $value->pay_name?></td>
                <td rowspan="<?=$compactRowspan?>"  style="vertical-align: middle"><?= $value->application_time?></td>
                <td rowspan="<?=$compactRowspan?>"  style="vertical-align: middle">
                    <?php
                    $username=\app\models\User::find()->select('username')->where(['id'=>$value->applicant])->scalar();
                    echo $username?$username :'';
                    ?>
                </td>
                <td rowspan="<?=$compactRowspan?>"  style="vertical-align: middle"><?= $value->create_notice?></td>
            <?php }?>
            <?php $circly++;$ordercircly++;?>
            </tr>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    <tr>
        <td colspan="4" style="text-align: right">合计</td>
        <td><?=$itemsAmount?></td>
        <td><?=$itemsPrice?></td>
        <td><?=$orderFright?></td>
        <td><?=$orderDiscount?></td>
        <td><?=$totalPrice/1000?></td>
    </tr>
    </tbody>
</table>
<span class="pay_detail" totalprice="<?=round($totalPrice/10)?>" charge =<?=$ufxiouCharge*100?> >
    <div style="border: solid red 1px ;height: 80px;text-align:center;margin-bottom: 20px">
        <div style="line-height: 40px;float:right;width: 100%;">
        <span class="pay_span" style="padding-left: 15%">请款金额(元)：</span>
        <span class="pay_span" style="color: red"><?=round($totalPrice/1000,2)?></span>
        <span class="pay_span" style="padding-left: 10%">到账金额(元)：</span>
        <span class="pay_span arrival_price" style="color: red"><?= round(($totalPrice-$ufxiouCharge*1000)/1000,2)?></span>
        <span class="pay_span" style="padding-left: 10%">手续费(元):</span>
        <span class="pay_span" style="color: red"><?=$ufxiouCharge?></span>
        </div>
        <div style="line-height: 40px;float:left;width: 100%;" >
            <span class="pay_span " style="padding-left: 15%" >我方承担手续费(元):</span>
            <span class="pay_span">
                <?= \yii\helpers\Html::radioList('Fuiou[charge]','01',['01'=>'否','02'=>'是'])?>
            </span>
            <span class="pay_span " style="padding-left: 20%">实际扣除金额(元):</span>
            <span class="pay_span tran_price" style="color: red"><?=round(($totalPrice)/1000,2)?></span>
        </div>
    </div>
    <div style="clear: both"></div>
    <?= $this->render('_payee_info',['totalPrice'=>$totalPrice-$ufxiouCharge*1000,'supplier_code'=>$model[0]->supplier_code,'is_drawback'=>$is_drawback,'bank'=>$bank,'model'=>$model])?>
    <?= \yii\helpers\Html::submitButton('确认付款', ['class' => 'pay_submit btn btn-warning']); ?>
    <?php ActiveForm::end();?>
    <?php
    }
    ?>
    <?php
    \yii\bootstrap\Modal::begin([
        'id' => 'compact-pay',
        'header' => '<h4 class="modal-title">付款申请书</h4>',
       // 'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
        'closeButton' =>false,
        'size'=>'modal-lg',
        'options'=>[
        ],
    ]);
    \yii\bootstrap\Modal::end();
    $js = <<<JS
$("#compact-pay").on("hidden.bs.modal",function(){
    $(document.body).addClass("modal-open");
});

    //手续费变更获取最新转账金额
    $(document).on('change','[name="Fuiou[charge]"]',function() {
        var totalPrice = Number($('.pay_detail').attr('totalprice'));
        var charge  = Number($('.pay_detail').attr('charge'));
        $('[name="Fuiou[charge]"]').each(function() {
          if($(this).is(':checked')&&$(this).val()=='01'){
              $('.arrival_price').text(Math.round(totalPrice-charge)/100);
              $('.tran_price').text(Math.round(totalPrice)/100);
              $('[name="Fuiou[amt]"]').val(Math.round(totalPrice-charge)/100);
          }
          if($(this).is(':checked')&&$(this).val()=='02'){
              $('.arrival_price').text(Math.round(totalPrice)/100);
              $('.tran_price').text(Math.round(totalPrice+charge)/100);
              $('[name="Fuiou[amt]"]').val(Math.round(totalPrice)/100);
          }
        });
    });
    var click_index=0;
$(document).on('submit','#fuiou_pay_form',function() {
    $(".pay_submit").attr('disabled','disabled');
        click_index++;
        if(click_index>1){
            layer.msg('不可多次提交');
            return false;
        }
      var loading = layer.load(6 , {shade : [0.5 , '#BFE0FA']});
    });
    $('.pay_apply').on('click',function() {
        $('#compact-pay .modal-body').html('正在请求中。。。。');
           $.get($(this).attr('href'),{},
                function (data) {
                    $('#compact-pay .modal-body').html(data);
                }
            );
    });
    
    $('.compact_button').on('click',function() {
        if($('.compact_info').is(':hidden')){
            $('.compact_button').attr('title','点击隐藏合同单详情');
            $('.compact_info').show();
        }else {
            $('.compact_button').attr('title','点击展示合同单详情');
            $('.compact_info').hide();
        }
    })
JS;
    $this->registerJs($js);
    ?>
