<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\datetime\DateTimePicker;
use app\models\PurchaseHistory;
use app\models\PurchaseOrderAccount;
use app\models\PurchaseDemand;
use app\models\Product;
use app\services\BaseServices;
use kartik\select2\Select2;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use app\models\PurchaseOrderTaxes;
use app\models\Stock;
use app\config\Vhelper;
use kartik\date\DatePicker;
use app\models\OverseasPurchaseOrderSearch;

$this->title = '采购确认';
$this->params['breadcrumbs'][] = 'FBA采购';
$this->params['breadcrumbs'][] = 'FBA采购单';
$this->params['breadcrumbs'][] = $this->title;?>


<style type="text/css">
    .modal-lg{width: 75%; !important;}
    .row{padding:10px;}
    .label-box {
        position: absolute;
        top: 40px;
        left: 0;
        border: 1px solid #8BC34A;
        padding: 8px;
        display: none;
        z-index: 500;
        background-color: #fff;
    }
    .label-box span:hover {
        background-color: red !important;
        cursor: pointer;
    }
</style>

<?php $form = ActiveForm::begin(['id'=>'submit-form']); ?>
<h3 class="">采购确认</h3>
<p></p>

<?php
$i = 0;
foreach($models as $ak=>$vb):
    $i++;
    // 预设默认值 结算
    $vb->account_type = $vb->account_type ? $vb->account_type : 2;
    // 支付
    $vb->pay_type = $vb->pay_type ? $vb->pay_type : 2;
    // 运输
    $vb->shipping_method = $vb->shipping_method ? $vb->shipping_method : 2;

    $purchase_acccount = '';
    $platform_order_number = '';
    $settlement_ratio = '100%'; //结算比例

    if(!empty($vb->purchaseOrderPayType)) {
        $purchase_acccount = $vb->purchaseOrderPayType->purchase_acccount;
        $platform_order_number = $vb->purchaseOrderPayType->platform_order_number;
        $settlement_ratio = $vb->purchaseOrderPayType->settlement_ratio;
    } else {
        $platform_order_number = !empty($vb->orderOrders->order_number)?$vb->orderOrders->order_number:'';
    }
?>

<div class="container-fluid" id="container-fluid<?php echo $i;?>" style="border: 2px solid #FF5722;margin-bottom: 10px;">
<div class="row">
    <div class="col-md-1">
        <label>PO号：</label>
        <strong><?= $vb->pur_number ?></strong>
        <input type="hidden" name="PurchaseOrder[pur_number][]" value="<?=$vb->pur_number?>">
        <input type="hidden" name="PurchaseNote[pur_number][]" value="<?=$vb->pur_number?>">
        <input type="hidden" name="PurchaseOrder[supplier_name][]" value="<?=$vb->supplier_name?>">
        <input type="hidden" name="PurchaseOrder[supplier_code][]" value="<?=$vb->supplier_code?>">
    </div>
    <!--关联供应商的结算方式-->
    <div class="col-md-2"><?= $form->field($vb, 'account_type[]')->dropDownList(SupplierServices::getSettlementMethod(),['options' => [ $vb->account_type => ['selected' => 'selected']]]) ?></div>
    <!--关联供应商的支付方式-->
    <div class="col-md-2">
        <?= $form->field($vb, 'pay_type[]')->dropDownList(SupplierServices::getDefaultPaymentMethod(),['options' => [$vb->pay_type => ['selected' => 'selected']]])->label('支付方式') ?>
    </div>
    <div class="col-md-3">
        <label>结算比例</label>
        <div class="input-group">
            <input type="text" name="PurchaseOrder[settlement_ratio][]" class="form-control settlement_ratio settlement_ratio_text" value="<?= $settlement_ratio ?>" readonly>
            <div class="input-group-btn">
                <button class="btn btn-default settlement_ratio_clear" type="button"><span class="glyphicon glyphicon-remove"></span></button>
                <button class="btn btn-default settlement_ratio_define" type="button">定义</button>
            </div>
            <div class="label-box">
                <p>
                    <span class="label label-info">10%</span>
                    <span class="label label-info">20%</span>
                    <span class="label label-info">30%</span>
                    <span class="label label-info">40%</span>
                    <span class="label label-info">50%</span>
                </p>
                <p>
                    <span class="label label-info">60%</span>
                    <span class="label label-info">70%</span>
                    <span class="label label-info">80%</span>
                    <span class="label label-info">90%</span>
                    <span class="label label-info">100%</span>
                    <span class="label label-danger">关闭</span>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-1">
        <?= $form->field($vb, 'shipping_method[]')->dropDownList(\app\services\PurchaseOrderServices::getShippingMethod(),['options' => [$vb->shipping_method => ['selected' => 'selected']]]) ?>
    </div>
    <div class="col-md-1">
        <label class="control-label">运费：</label>
        <input type="text" class="form-control" name="PurchaseOrder[freight][]" value="<?= !empty($vb->purchaseOrderPayType) ? $vb->purchaseOrderPayType->freight : 0; ?>">
    </div>
    <div class="col-md-1">
        <label class="control-label">优惠额：</label>
        <input  class="form-control" type="text" name="PurchaseOrder[discount][]" value="<?= !empty($vb->purchaseOrderPayType) ? $vb->purchaseOrderPayType->discount : 0; ?>">
    </div>
    <div class="col-md-1">
        <label class="control-label">账号：</label>
        <select name="PurchaseOrder[purchase_acccount][]" class="purchase_acccount form-control">
            <option value="0">请选择...</option>
            <?php
            $accountes = BaseServices::getAlibaba();
            foreach($accountes as $k=>$v): if($purchase_acccount == $v): ?>
                <option value="<?= $k ?>" selected><?= $v ?></option>
            <?php else: ?>
                <option value="<?= $k ?>"><?= $v ?></option>
            <?php endif; endforeach; ?>
        </select>
    </div>
</div>


<div class="row">
    <div class="col-md-2">
        <label class="control-label">拍单号：</label>
        <input  class="form-control" type="text" name="PurchaseOrder[platform_order_number][]" value="<?= $platform_order_number ?>">
    </div>
    <div class="col-md-3">
        <label class="control-label" for="purchaseorder-carrier">供应商</label>
        <div class="form-control" ><?=$vb->supplier_name?></div>
    </div>
    <div class="col-md-1">
        <?=$form->field($vb, 'is_drawback_black[]')->dropDownList(['1'=>'不含税','2'=>'含税'],['disabled'=>'disabled','required'=>'required','value'=>!empty($vb->is_drawback)? $vb->is_drawback : 2])->label('是否含税') ?>
        <input type="hidden" name="PurchaseOrder[is_drawback][]" value="<?php echo $vb->is_drawback;?>" />
        <input type="hidden" name="PurchaseOrderTaxes[is_taxes]" class="taxes" value="<?= !empty($vb->is_drawback)? $vb->is_drawback : 2 ?>">
    </div>
    <div class="col-md-1">
        <?= $form->field($vb, 'is_expedited[]')->dropDownList(['1'=>'不加急','2'=>'加急采购单'],['options' => [$vb->is_expedited => ['selected' => 'selected']]]) ?>
    </div>
    <div class="col-md-2">
        <?php echo '<label>预计到货时间</label>';
        $time = !empty($vb->date_eta) ? strtotime($vb->date_eta) : time();
        echo DatePicker::widget([
            'name' => 'PurchaseOrder[date_eta][]',
            'options' => ['placeholder' => '','onchange' => 'dateEtaChange(this)'],
            'value' => date('Y-m-d',$time),
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'todayHighlight' => true
            ]
        ]);?>
    </div>
	<div class="col-md-3">
		tip:<br>
		含税采购自动指定仓库为: 退税仓<br> 
		不含税采购自动指定仓库为: 东莞仓FBA虚拟仓
		<?php if ($vb->is_drawback == 2 && $vb->supplier->payment_method != 3) : ?>
		<br><font style="color:red">*含税采购支付方式必须是【银行卡转账】, 请先修改供应商支付方式</font>
		<?php endif; ?>
	</div>
</div>

        <div class="col-md-12"><div class="form-group field-purchasenote-note required">
                    <label class="control-label" for="purchasenote-note">确认备注</label>
                    <textarea id="purchasenote-note" class="form-control" name="PurchaseNote[note][]" rows="3" cols="10" placeholder="比如说是阿里支付单号,这个给财务能看到"><?=!empty($vb->orderNote->note)?$vb->orderNote->note:''?></textarea>

                    <div class="help-block"></div>
                </div></div>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th><?=Yii::t('app','采购图片')?></th>
                    <th><?=Yii::t('app','产品代码')?></th>

                    <th><?=Yii::t('app','建议数量')?></th>

                    <th><?=Yii::t('app','产品名')?></th>
                    <th><?=Yii::t('app','可用库存')?></th>
                    <th><?=Yii::t('app','确认数量')?></th>
                    <th><?=Yii::t('app','税点')?></th>
                    <th><?=Yii::t('app','采购单价')?></th>
                    <th><?=Yii::t('app','历史采购信息')?></th>
                    <th><?=Yii::t('app','销量库存')?></th>
                    <th><?=Yii::t('app','总金额')?></th>
                    <th><?=Yii::t('app','sku到货时间')?></th>
                    <th><?=Yii::t('app','操作')?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $total =0;
                $qty_total = 0; //建议数量汇总
                foreach($vb->purchaseOrderItems as $k=> $v){


                    $v->pur_ticketed_point = OverseasPurchaseOrderSearch::getSkuQuoteValue($v->sku, 'pur_ticketed_point');

                    $price = Product::getProductPrice($v->sku);
                    $price = !empty($price) ? $price : $v->base_price;
                    if ($vb->is_drawback == 2) {
                        $userprice = round($price*(1+$v->pur_ticketed_point/100),4);
                    } else {
                        $userprice = $price;
                    }
                    $totalprices = !empty($v->ctq) ? $v->ctq*$userprice : $v->qty*$userprice;
                    $total += $totalprices;

                    $qty_total += $v->qty;
                    //$img=Vhelper::toSkuImg($v['sku'],$v['product_img']);
                    $img=Html::img(Vhelper::downloadImg($v['sku'],$v['product_img'],2),['width'=>'110px']);

                    $purchase_packaging = Product::getSkuCode($v->sku);// 显示采购来料包装
                    $purchase_packaging = '<br/><span style="color:#29608b">' .$purchase_packaging.'</span>';
                    ?>
                    <tr>
                        <!--采购图片-->
                        <td><?=Html::a($img,['purchase-suggest/img', 'sku' => $v['sku'],'img' => $v['product_img']], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal3'])?></td>
                        <?= Html::input('hidden', 'purchaseOrderItems[pur_number][]', $v->pur_number, ['class' => 'pur_number','readonly'=>'readonly','style'=>'width:85px;']) ?>
                        <?= Html::input('hidden', 'purchaseOrderItems[sku][]', $v->sku, ['class' => 'sku','readonly'=>'readonly','style'=>'width:85px;'])?>
                        <!--产品代码-->
                        <td><?=Html::a($v->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$v->sku,['target'=>'blank'])
                            /*.Html::a('',['product/viewskusales', 'sku' =>$v->sku], ['class' => "glyphicon glyphicon-signal b", 'style'=>'float: right', 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal3',])*/ ?>
                            <a href="<?=$v->getSkuPurchaseLink()?>" title='' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>
                            <?= \app\models\ProductRepackageSearch::getPlusWeightInfo($v->sku,true)?>
                        </td>
                        <!--建议数量-->
                        <td style="width:50px;"><?=$v->qty?></td>
                        <!--产品名-->
                        <td  width="200" style="word-break:break-all;"  class="td1" onmouseover="this.className='td2'" onmouseout="this.className='td1'">
                            <?=$v->name?>
                            <br/><span style="color:red"><?=$purchase_packaging ?></span>
                        </td>
                        <!--可用库存-->
                        <td style="width:30px;"><?php
                            $stock = \app\models\Stock::getStock($v->sku,$vb->warehouse_code);
                            echo !empty($stock)?$stock->stock:'0';
                            ?></td>
                        <!--确认数量-->

                        <td class="ctqs"><?= Html::input('number', 'purchaseOrderItems[ctq][]', !empty($v->ctq) ? $v->ctq : $v->qty, ['class' => 'ctq', 'onchange' =>"etaNumberChange(this,{$i},'{$vb->warehouse_code}','{$v->ctq}','{$v->qty}','{$v['sku']}')",'required'=>true,'style'=>'width:45px;','min'=>1,'id'=>"{$v['sku']}_ctq"]) ?></td>
                        <!--税点-->
                        <td><input type="text" style="width:20px;<?php if($vb->is_drawback == 1){echo 'display:none';}?>" class="input-tax" name="PurchaseOrderTaxes[taxes][<?=$k?>][taxes]" readonly="readonly" value="<?=$v->pur_ticketed_point?>" data-point="<?=$v->pur_ticketed_point?>" required>%</td>
                        <input type="hidden" name="PurchaseOrderTaxes[taxes][<?=$k?>][sku]" value="<?=$v->sku?>">

                        <input type="hidden"  class="form-control" name="PurchaseOrderTaxes[taxes][<?=$k?>][pur_number]" value="<?=$vb->pur_number?>"  readonly>
                        <!--采购单价-->
                        <td><?= Html::input('text', 'purchaseOrderItems[price][]', $userprice, ['class'=>'price','readonly'=>'readonly','sku'=>$v->sku ,'pur'=>$vb->pur_number, 'data-price'=>round($price,4),'data-rate-price'=>round($price*(1+$v->pur_ticketed_point/100),4),'required'=>true,'style'=>'width:65px;']) ?></td>
                        <!--历史采购信息-->
                        <td><?=PurchaseHistory::getLastPrice($v->sku)?><Br/><?=Html::a('<span class="glyphicon glyphicon-eye-open" style="font-size:10px;color:cornflowerblue;" title="历史采购记录"></span>', ['#'],[
                                'data-toggle' => 'modal',
                                'data-target' => '#created-modal3',
                                'code' => $vb->warehouse_code,
                                'class'=>'data-updatess',
                                'sku'  => $v->sku,
                            ])?></td>
                        <td ><?=Html::a('<span class="glyphicon glyphicon-stats" style="font-size:10px;color:cornflowerblue;" title="销量库存"></span>', ['product/get-stock-sales','sku'=>$v->sku],[
                                'data-toggle' => 'modal',
                                'data-target' => '#created-modal3',
                                //'code' => $vb->warehouse_code,
                                'class'=>'sales',
                                'sku'  => $v->sku,
                            ])?></td>
                        <td><?= Html::input('text', 'purchaseOrderItems[totalprice][]',$totalprices, ['class' => 'payable_amount','readonly'=>'readonly','style'=>'width:45px;']) ?></td>
                        <!--sku到货时间-->
                        <td>
                            <input  type="hidden" name="PurchaseEstimatedTime[sku][]" value="<?=$v->sku?>">
                            <input type="hidden"  class="form-control" name="PurchaseEstimatedTime[pur_number][]" value="<?=$vb->pur_number?>"  readonly>
                            <input type="hidden"  class="form-control" name="PurchaseEstimatedTime[purchase_type][]" value="<?=$vb->purchase_type?>">
                            <?php
                            $time = $model_estimated_time->getEstimatedTime($v->sku,$vb->pur_number);
                            $time = empty($time) ? time() : strtotime($time);
                            echo DatePicker::widget([
                                'name' => "PurchaseEstimatedTime[estimated_time][]",
                                'options' => ['placeholder' => '','class' => 'estimated_time'],
                                //注意，该方法更新的时候你需要指定value值
//                                'value' => date('Y-m-d',time()),
                                'value' => date('Y-m-d',$time),
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                    'todayHighlight' => true
                                ]
                            ]);?>
                        </td>
                        <td><?= Html::a('删除','#',['class'=>'deleteSku','sku'=>$v['sku'],'i'=>$i,'pur'=>$vb->pur_number,'num'=>count($vb->purchaseOrderItems)]) ?></td>
                    </tr>

                <?php }?>
                <tr class="table-module-b1">
                    <td class="ec-center" colspan="2" style="text-align: left;"><b>汇总：</b></td>
                    <td colspan="1"><?=$qty_total?></td>
                    <td colspan="6"></td>
                    <td colspan="3" class="total">总应付：<b><?=number_format($total,3).'&nbsp;&nbsp;'.$vb->currency_code?></b></td>

                </tr>
                <tr class="table-module-b1">
                    <td class="ec-center" colspan="9" style="text-align: left;"><b>优惠后总金额 = 实际总金额 + 运费 - 优惠</b></td>
                    <td colspan="3">优惠后：<b><?php echo  !empty($vb->purchaseDiscount->total_price) ? $vb->purchaseDiscount->total_price : '';?></b></td>
                </tr>
                </tbody>
            </table>
            <div class="col-md-2">提交操作:</div>
            <div class="col-md-8"><label><input name="PurchaseOrder[submit][<?=$ak?>]" type="radio" value="1" checked />保存</label>
                <label><input name="PurchaseOrder[submit][<?=$ak?>]" type="radio" value="2" />提交审批 </label></div>
        </div>
    <?php endforeach; ?>

    <div class="form-group">
        <?= Html::button(Yii::t('app', '确认'), ['class' => 'btn btn-primary fba_submit']) ?>
    </div>

<?php ActiveForm::end(); ?>
<?php
Modal::begin([
    'id' => 'created-modal3',
    //'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',

    ],
]);
Modal::end();
?>
<?php
//$historys         = Url::toRoute(['tong-tool-purchase/get-history']);
$historys         = Url::toRoute(['purchase-suggest/histor-purchase-info']);
$delete         = Url::toRoute(['delete-sku']);
$surl= Url::toRoute(['product/viewskusales']);
$js = <<<JS
$(function () {
    $('.fba_submit').click(function () {
        var flag = true;
          $(".settlement_ratio_text").each(function(){
              var settlement_ratio = $(this).val();
              if (settlement_ratio == '') {
                flag = false;
              }
          });
          if (flag == false) {
            layer.alert('结算比例不能为空');
            return false;
          } else {
            $('#submit-form').submit();
            $(this).prop('disabled',true);
          }
    });

    $('.settlement_ratio_define').click(function() {
        var parent = $(this).parents('.input-group');
        parent.find('.label-box').toggle();
    });
    
    $('.settlement_ratio_clear').click(function() {
        var parent = $(this).parents('.input-group');
        parent.find('input[name="PurchaseOrder[settlement_ratio][]"]').val('');
    });
    
    $('.label-box span').click(function() {
        var parent = $(this).parents('.input-group');
            ratio  = parent.find('input[name="PurchaseOrder[settlement_ratio][]"]');
            _ratio = ratio.val();
        if($(this).text() == '关闭') {
            parent.find('.label-box').toggle();    
            return true;
        }
        if(_ratio == '') {
             _ratio += $(this).text();
        } else {
            var _ratioes = _ratio.split('+');
            var total = parseInt($(this).text());
            for(i = 0; i < _ratioes.length; i++) {
                total += parseInt(_ratioes[i]);
            }
            if(total > 100) {
                layer.tips('总百分比不能超过100', ratio, {tips: 1});
                return false;
            }
             _ratio += '+'+$(this).text();
        }
        parent.find('input[name="PurchaseOrder[settlement_ratio][]"]').val(_ratio);
    });
});
    $('#created-modal3').on('shown.bs.modal', function (e){
            var scrollTop = $('#create-modal').scrollTop();
            $(this).find('.modal-dialog').css({
            top:scrollTop,
            });
    });
    $(document).on('click', '.b', function () {

        $.get($(this).attr('href'), {sku:$(".sku").attr('sku')},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.img', function () {

        $.get($(this).attr('href'), {},
            function (data) {
               $('#created-modal3').find('.modal-body').html(data);
            }
        );
    });

    //改变是否含税
    function changeTax(value,i) {
        if (value == 1) {
            $("#container-fluid"+i).find('input[class*=input-tax]').hide();
            $("#container-fluid"+i).find('input[class*=input-tax]').val(0);
            $("#container-fluid"+i+" .table tbody tr .price").each(function(){
                $(this).val($(this).attr('data-price'));
            });
        } else {
            $("#container-fluid"+i).find('input[class*=input-tax]').show();
            $("#container-fluid"+i+" .table tbody tr .input-tax").each(function(){
                $(this).val($(this).attr('data-point'));
            });
            $("#container-fluid"+i+" .table tbody tr .price").each(function(){
                $(this).val($(this).attr('data-rate-price'));
            });
            if ($("#pay_type_"+i).val() != 3) {
                layer.msg('如果选择含税采购，支付方式必须是【银行卡转帐】，请先修改供应商支付方式');
            }
            //$("#container-fluid"+i+" #purchaseorder-pay_type").val(3);
        }
        setTotal(i);
    }

    //eta数量change
	function etaNumberChange(object,i,warehouse_code,ctq,qty,sku){
        var change_ctq = $(object).val();//当前更改的值
        if(ctq==''){
            ctq=qty;
        }
        //仓库是退税仓和FBA虚拟仓的订单确认数量只能改小，不能改大
        if((warehouse_code=='FBA_SZ_AA' || warehouse_code=='TS') && parseInt(change_ctq)>parseInt(ctq)){
            layer.alert('仓库是退税仓和FBA虚拟仓的订单确认数量只能改小，不能改大');
            $("#"+sku+"_ctq").val(ctq);
            return;
        }
		var obj = $(object);
		var objTr = obj.parent().parent();
		//判断输入的数据是否是数字类型
		if(!testRegex(obj,obj.val())){
			return;
		}
		//获取单价
        
		//var unitPrice = objTr.find(".price").val()*1;
		//var sum = accMul(unitPrice,obj.val());
		//objTr.find(".payable_amount").val(sum);
		setTotal(i);
	}

	//单价change
	function unitPriceChange(object){
        return false;
	    var tvp=parseFloat(object.value),
            tp=$(object).attr('data-price');

        var sku=$(object).attr('sku');
        var pur=$(object).attr("pur");
        $.ajax({
            url: '/overseas-purchase-order-confirm/view-update-log',
            data: {sku:sku,pur:pur},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                var bi = Math.round((tvp-tp)/tp * 10000) / 100.00 + "%";
                if(tvp>tp){
                    layer.alert('增长：' + bi + '修改后的单价大于系统单价<br />' + data.message +  '<br /><span color="red">修改单价请到【供货商商品管理】页面修改</span>');
                } else if (tvp<tp){
                    layer.alert('下降：' + bi + '修改后的单价小于系统单价<br />' + data.message +  '<br /><span style="color:red">修改单价请到【供货商商品管理】页面修改</span>');
                }
                $('.fba_submit').prop('disabled',true);
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


    function setTotal(i){
        var s=0;
        $("#container-fluid"+i+" .table tbody tr .ctqs").each(function(){
            var tds = parseInt($(this).find('input[class*=ctq]').val())*parseFloat($(this).parent().find('input[class*=price]').val());
            $(this).parent().find('input[class*=payable_amount]').val(tds.toFixed(2));
            s+=tds;
        });

        $("#container-fluid"+i+" .total").html('总计金额:'+s.toFixed(2)+'RMB');
    }

        $(".pai").attr("required",true);
        $(".pai").parent().show();
        $("#purchaseorder-account_type").change(function(){

            var name =$(this).children('option:selected').val();
            if(name==1 || name==3)
            {
                $(".pai").attr("required",false);
                $(".pai").attr("value",'12345789');
                $(".isbb").attr("value",'1');
                $(".pai").parent().hide();
            } else {

                $(".pai").attr("required",true);
                $(".pai").parent().show();
                $(".isbb").attr("value",'2');
            }
        });
    $(document).on('click','.data-updatess', function () {
         $('#created-modal3').find('.modal-body').html('正在请求数据....');
        $.get('{$historys}', {sku:$(this).attr('sku')},
            function (data) {
                $('#created-modal3').find('.modal-body').html(data);
            }
        );
    });

    $(document).on('click','.deleteSku', function () {
        if($(this).attr('num') ==1){
            alert('产品只剩一个不可删除！');
        }else {
            $.get('{$delete}', {sku:$(this).attr('sku'),purNumber:$(this).attr('pur')},
                function (data) {
                    $('#created-modal3').find('.modal-body').html(data);
                }
            );
        }
    });

    $(document).on('change','.drawback', function () {
        //console.log($(this).val());
        $('.taxes').val($(this).val());
    });
    
    //订单预计到货时间改变
	function dateEtaChange(object){
		var obj = $(object); //订单预计到货时间
		// var objTr = obj.parent().parent().parent();
		var objTr = obj.parents();
		console.log(objTr);
		objTr.find(".estimated_time").attr("value",obj.val());
	}
	
	$(function(){
        $(document).on('click','.sales', function () {
            $('#created-modal3').find('.modal-body').html('正在请求数据....');
            $.get($(this).attr('href'), {},
                function (data) {
                    $('#created-modal3').find('.modal-body').html(data);
                }
            );
            return false;
        });
	});
JS;
$this->registerJs($js);
?>




