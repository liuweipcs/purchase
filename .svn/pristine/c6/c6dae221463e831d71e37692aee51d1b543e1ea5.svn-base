<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\PurchaseHistory;
use app\config\Vhelper;
use app\models\PurchaseOrderItemsV2;
use kartik\select2\Select2;
use yii\web\JsExpression;
use dosamigos\datetimepicker\DateTimePicker;

$suppurl = \yii\helpers\Url::to(['/supplier/search-supplier']);
$this->title='采购确认';
?>
<style type="text/css">
    .img-rounded{width:60px; height:60px; !important;}
    .row-box{max-height: 650px; overflow-y: scroll}
    .row-box-p{border: 1px solid red; margin: 10px; padding: 10px 0;}
    #btn{position:fixed; bottom: 0; margin-bottom: 18px;}
</style>

<div class="row-box">
<?php $form = ActiveForm::begin(); ?>
    <input type="hidden" name="page" value="<?=$page?>" />

<?php if(is_array($models)){

    foreach($models as $ak=>$vb){
        //预设默认值 结算
        $vb->account_type=$vb->account_type ? $vb->account_type : 2;
        //支付
        $vb->pay_type=$vb->pay_type ? $vb->pay_type : 2;
        //运输
        $vb->shipping_method= $vb->shipping_method ? $vb->shipping_method : 2;
        ?>

        <div class="row row-box-p">
            <div class="col-xs-6 col-sm-3">
                <div class="form-group field-purchaseorder-carrier">
                    <label class="control-label" for="purchaseorder-carrier">PO号</label>
                    <input type="text"  class="form-control" name="PurchaseOrdersV2[pur_number][]" value="<?=$vb->pur_number?>"  readonly>
                    <input type="hidden"  class="form-control" name="PurchaseOrderShip[pur_number][]" value="<?=$vb->pur_number?>"  readonly>
                    <input type="hidden"  class="form-control" name="PurchaseNote[pur_number][]" value="<?=$vb->pur_number?>"  readonly>
                    <input type="hidden"  class="form-control" name="PurchaseOrderOrders[pur_number][]" value="<?=$vb->pur_number?>"  readonly>
                    <div class="help-block"></div>
                </div>
            </div>

            <div class="col-xs-6 col-sm-3">
                <?php
                $qsum=PurchaseOrderItemsV2::find()
                    ->select(['sum(qty) as qty, sum(ctq) as ctq, count(id) as id, sum(price) as price'])
                    ->where(['pur_number'=>$vb->pur_number])->asArray()->all();
                ?>
                <div class="form-group field-purchaseorder-carrier">
                    <label class="control-label" for="purchaseorder-carrier">SKU数量</label>
                    <input type="text"  class="form-control" name="" value="<?=!empty($qsum[0]['id']) ? $qsum[0]['id'] : '' ?>"  disabled>
                </div>
            </div>

            <div class="col-xs-6 col-sm-3">
                <div class="form-group field-purchaseorder-carrier">
                    <label class="control-label" for="purchaseorder-carrier">采购数量</label>
                    <input type="text"  class="form-control" name="" value="<?=!empty($qsum[0]['ctq']) ? $qsum[0]['ctq'] : $qsum[0]['qty'] ?>"  disabled>
                </div>
            </div>

            <div class="col-xs-6 col-sm-3">
                <div class="form-group field-purchaseorder-carrier">
                    <label class="control-label" for="purchaseorder-carrier">总金额</label>
                    <?php
                        $total_count=round(PurchaseOrderItemsV2::getCountPrice($vb->pur_number),2);
                        $total_sum=round($qsum[0]['qty']*$qsum[0]['price'],2);
                    ?>
                    <input type="text"  class="form-control" name="" value="<?=$total_count ? $total_count : $total_sum?>"  disabled>
                </div>
            </div>

            <div class="col-xs-6 col-sm-3"><?= $form->field($vb, 'account_type[]')->dropDownList(\app\services\SupplierServices::getSettlementMethod(),['options' => [$vb->account_type => ['selected' => 'selected']]]) ?></div>

            <div class="col-xs-6 col-sm-3"><label class="control-label" for="purchaseorder-carrier">订单号</label><input type="text" class="form-control pai" name="PurchaseOrderOrders[order_number][]" value="<?=!empty($vb->orderOrders->order_number)?$vb->orderOrders->order_number:''?>" required></div>

            <div class="col-xs-6 col-sm-3"><?= $form->field($model_ship, 'freight[]')->textInput(['maxlength' => true,'class'=>'form-control frig','value'=>!empty($vb->orderShip->freight)?round($vb->orderShip->freight,2):'0','required'=>'required'])?></div>

            <div class="col-xs-6 col-sm-3"><?= $form->field($vb, 'pay_type[]')->dropDownList(\app\services\SupplierServices::getDefaultPaymentMethod(),['options' => [$vb->pay_type => ['selected' => 'selected']]]) ?></div>
            <div class="col-xs-6 col-sm-3"><?= $form->field($vb, 'shipping_method[]')->dropDownList(\app\services\PurchaseOrderServices::getShippingMethod(),['options' => [$vb->shipping_method => ['selected' => 'selected']]]) ?></div>

            <?php
                if($vb->purchase_type!=1){
                    $vb->is_transit=1;
                    $vb->is_drawback=1;
            ?>
                <div class="col-xs-6 col-sm-3"><?= $form->field($vb, 'transit_warehouse[]')->dropDownList(['shzz'=>'上海中转仓库','AFN'=>'东莞中转仓库'],['options' => [$vb->transit_warehouse => ['selected' => 'selected']]]) ?></div>
                <div class="col"><?=$form->field($vb, 'is_drawback[]')->dropDownList(['2'=>'是','1'=>'否'],['options' => [$vb->is_transit => ['selected' => 'selected']]])->label('是否退税') ?></div>
            <?php }?>

            <div class="col-xs-6 col-sm-3">
                <?= $form->field($vb, 'date_eta[]')->widget(DateTimePicker::className(), [
                    'options' => ['placeholder' => '','value'=>$vb->date_eta,'id'=>'date_eta_'.$ak],
                    'language' => 'zh-CN',
                    'clientOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd hh:ii:ss', // if inline = false
                        'todayBtn' => true
                    ]
                ]);?>
            </div>

            <div class="col-xs-6 col-sm-3">
            <span class="suppname<?=$ak?>" style="display: none"><?=$vb->supplier_name?></span>
            <?= $form->field($vb, 'supplier_code[]')->widget(Select2::classname(), [
                'options' => ['placeholder' => '请选供应商','id'=>'supplier_code'.$ak,'value'=>$vb->supplier_code],
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
            </div>

            <div class="col-xs-6 col-sm-3">
                <a title="添加供应商" href="<?=Url::toRoute(['supplier/create'])?>" target="_blank" style="margin-top: 33px;" class="glyphicon glyphicon-plus add-supp"></a>
            </div>

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th><?=Yii::t('app','图片')?></th>
                    <th><?=Yii::t('app','SKU')?></th>
                    <th><?=Yii::t('app','产品名')?></th>
                    <th><?=Yii::t('app','采购数量')?></th>
                    <th><?=Yii::t('app','单价')?></th>
                    <th><?=Yii::t('app','金额')?></th>
                    <th><?=Yii::t('app','产品链接')?></th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody class="bs">
                <?php
                $total =0;
                foreach($vb->purchaseOrderItems as $k=> $v){

                    $totalprice = $v->ctq*$v->price;
                    $totalprices = $v->qty*$v->price;
                    $total += $totalprice?$totalprice:$totalprices;
                    $img=!empty($v['product_img'])?Vhelper::toSkuImg($v['sku'],$v['product_img']):'';
                    ?>
                    <tr>
                        <td>
                            <?=Html::a($img,['#'], ['class' => "img", 'data-skus' => $v['sku'],'data-imgs' => $v['product_img'], 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal'])?>
                        </td>

                        <?= Html::input('hidden', 'purchaseOrderItems[pur_number][]', $v->pur_number, ['class' => 'pur_number']) ?>
                        <?= Html::input('hidden', 'purchaseOrderItems[sku][]', $v->sku) ?>

                        <td title="<?=$v->sku?>">
                            <?=Html::a($v->sku,['#'], ['class' => "sales", 'data-sku' =>$v->sku, 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal']) ?>
                        </td>

                        <td title="<?=$v->name?>" width="160">
                            <a href="<?=Yii::$app->params['SKU_ERP_Product_Detail'].$v->sku?>" target="_blank"><?=$v->name?></a>
                        </td>

                        <td class="ctqs">
                            <?= Html::input('number', 'purchaseOrderItems[ctq][]', $v->ctq?$v->ctq:$v->qty, ['class' => 'ctq', 'onchange' =>"etaNumberChange(this)",  'data-ctq'=>$v->ctq?$v->ctq:$v->qty,'required'=>true,'min'=>1,'max'=>10000,'style'=>'width:60px;']) ?>
                        </td>

                        <td>
                            <?= Html::input('text', 'purchaseOrderItems[price][]', round($v->price,2), ['class' => 'price', 'onchange' => "unitPriceChange(this)", 'data-price'=>round($v->price,2),'required'=>true,'style'=>'width:60px;']) ?>
                        </td>

                        <td><?= Html::input('text', 'purchaseOrderItems[totalprice][]',$totalprice ? $totalprice : $totalprices, ['class' => 'payable_amount','readonly'=>true,'style'=>'width:80px;'])?></td>
                        <td>
                            <?php
                                $polink=\app\models\SupplierQuotes::getUrl($v->sku);
                                $plink=!empty($v->product_link) ? $v->product_link : $polink;
                                echo Html::input('text', 'purchaseOrderItems[product_link][]',$plink, ['class' => '']) ?>
                            <a href='<?=$plink?>' title='' target='_blank'>
                                <i class='fa fa-fw fa-internet-explorer'></i>
                            </a>
                        </td>
                        <td><a href="javascript:void(0);" onclick="delTr(<?=$k+1?>,this)" sku="<?=$v->sku?>" pur="<?=$v->pur_number?>" k="<?=$k+1?>" class="dels<?=$k+1?>" >删除</a></td>
                    </tr>

                <?php }?>
                <tr class="table-module-b1">
                    <td class="total" colspan="8">总额：<b style="color: red"><?=round($total,2).'&nbsp;&nbsp;'.$vb->currency_code?></b></td>
                </tr>
                </tbody>
            </table>

            <input type="hidden" class="form-control isbb" name="PurchaseOrderOrders[is_request][]" value="0">
            <div class="col-md-12"><div class="form-group field-purchasenote-note required">
                    <label class="control-label" for="purchasenote-note">确认备注</label>
                    <textarea id="purchasenote-note" class="form-control" name="PurchaseNote[note][]" rows="3" cols="10" required="" placeholder="比如说是阿里支付单号,这个给财务能看到"><?=!empty($vb->orderNote->note)?$vb->orderNote->note:''?></textarea>

                    <div class="help-block"></div>
                </div>
            </div>


            <div class="col-md-2">提交操作:</div>
            <div class="col-md-8">
                <label><input name="PurchaseOrdersV2[submit][<?=$ak?>]" type="radio" value="1" checked />保存</label>
                <label><input name="PurchaseOrdersV2[submit][<?=$ak?>]" type="radio" value="2" />提交</label></div>
        </div>
    <?php }?>

    <div class="form-group">
        <?= Html::submitButton($vb->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '确认'), ['class' => $vb->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'id'=>'btn']) ?>
    </div>

<?php }?>

<?php ActiveForm::end(); ?>

</div>

<?php
Modal::begin([
    'id' => 'created-modal',
    //'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',
    ],
]);
Modal::end();

$url_update_qty = Url::toRoute('edit-sku');
$url=Url::toRoute(['product/viewskusales']);
$imgurl=Url::toRoute(['purchase-suggest/img']);

$count_arr=count($models);

$js = <<<JS
    
    for(i=0; i<$count_arr; i++){
        $('#select2-supplier_code'+i+'-container').text($('.suppname'+i).text());
    }    

    $(document).on('click', '.sales', function () {
        $.get('$url', {sku:$(this).attr('data-sku')},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);
            }
        );
    });

    $(document).on('click', '.img', function () {
        $.get('$imgurl', {img:$(this).attr('data-imgs'),sku:$(this).attr('data-skus')},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);
            }
        );
    });

    $(".dels").click(function(){
        var sku=$(this).attr('sku');
        var pur=$(this).attr("pur");
        var k=$(".bs tr").length;
        if(k>2){
            if(confirm("确认删除")){
                alert('删除成功');
                $.post("$url_update_qty",{sku:sku,pur:pur},function(result){
                    if(result.code==1){
                        alert('删除成功');
                        window.location.reload();
                    }else{
                        alert('删除失败');
                    }
                },'json');
            }else{
                return false;
            }
        } else{
            alert('剩下一个sku了,请直接撤销采购单吧');
        }
    });

JS;

$this->registerJs($js);
?>

<script type="text/javascript">

    function delTr(obj,t){
        var obj=$('.dels'+obj);
        var sku=obj.attr('sku');
        var pur=obj.attr("pur");
        var k=$(".bs tr").length;
        if(k>2){
            if(confirm("确认删除")){
                $.post("<?=$url_update_qty?>",{sku:sku,pur:pur},function(result){
                     if(result.code==1){
                         var tr=t.parentNode.parentNode;
                         var tbody=tr.parentNode;
                         tbody.removeChild(tr);
                         alert('删除成功');
                        //window.location.reload();
                     }else{
                        alert('删除失败');
                     }
                 },'json');
            }else{
                return false;
            }
        } else{
            alert('剩下一个sku了,请直接撤销采购单吧');
        }
    }

    //eta数量change
    function etaNumberChange(object){

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

    $(".pai").attr("required",true);
    $(".pai").parent().show();
    $("#purchaseorder-account_type").change(function(){

        var name =$(this).children('option:selected').val();

        if(name==1 || name==3||name==5)
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
</script>