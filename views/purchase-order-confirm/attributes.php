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
use kartik\date\DatePicker;
use app\models\Product;
use mdm\admin\components\Helper;
$suppurl = \yii\helpers\Url::to(['/supplier/search-supplier']);
$this->title = '采购确认';
$this->params['breadcrumbs'][] = '国内仓';
$this->params['breadcrumbs'][] = '采购计划单';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .row {
        padding: 10px;
    }
</style>
<span id="default_supplier_count"><?=$default_supplier_count?></span>
<?php $form = ActiveForm::begin(['id' => 'submit-audit-form',
//        'enableAjaxValidation' => true,
//        'validationUrl' => Url::toRoute(['validate-form']),
    ]
); ?>

<p>
    <?= Html::a('导出Excel', ["purchase-order-confirm/export?id={$id[0]}"], ['class' => 'btn btn-success print','id'=>'export']) ?>
    <?php 
        if(Helper::checkRoute('print-confirm'))
        {
            echo Html::a('打印', ["purchase-order-confirm/print-confirm?models_json={$models_json}"], ['class' => 'btn btn-success print-comfirm','target'=>'_blank']);
        }
    ?>
</p>

<?php
foreach($models as $ak=>$vb):
    // 预设默认值 结算
    $vb->account_type = $vb->account_type ? $vb->account_type : 2;
    // 支付
    $vb->pay_type = $vb->pay_type ? $vb->pay_type : 2;
    // 运输
    $vb->shipping_method = $vb->shipping_method ? $vb->shipping_method : 2;

    $purchase_acccount = '';
    $platform_order_number = '';

    if(!empty($vb->purchaseOrderPayType)) {


        $purchase_acccount = $vb->purchaseOrderPayType->purchase_acccount;


        $platform_order_number = $vb->purchaseOrderPayType->platform_order_number;



    } else {

        $platform_order_number = !empty($vb->orderOrders->order_number)?$vb->orderOrders->order_number:'';

    }











    ?>

    <div class="container-fluid" style="border: 2px solid #FF5722;margin-bottom: 10px;">

        <div class="row">

            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label" for="purchaseorder-carrier">PO号</label>
                    <input type="text"  class="form-control" name="PurchaseOrder[pur_number][]" value="<?=$vb->pur_number?>"  readonly>
                    <input type="hidden"  class="form-control" name="PurchaseNote[pur_number][]" value="<?=$vb->pur_number?>"  readonly>
                    <input type="hidden"  class="form-control" name="PurchaseOrderOrders[pur_number][]" value="<?=$vb->pur_number?>"  readonly>
                    <input type="hidden"  class="form-control" name="PurchaseOrder[account_type][]" value="<?=$vb->account_type?>">
                    <div class="help-block"></div>
                </div>
            </div>

            <div class="col-md-1">
                <?php
                $qsum = PurchaseOrderItems::find()
                    ->select(['sum(qty) as qty, sum(ctq) as ctq, count(id) as id, sum(price) as price'])
                    ->where(['pur_number' => $vb->pur_number])->asArray()->all();
                ?>
                <div class="form-group">
                    <label class="control-label" for="purchaseorder-carrier">SKU数量</label>
                    <input type="text"  class="form-control" name="" value="<?=!empty($qsum[0]['id']) ? $qsum[0]['id'] : '' ?>"  disabled>
                </div>
            </div>

            <div class="col-md-1">
                <div class="form-group">
                    <label class="control-label" for="purchaseorder-carrier">采购数量</label>
                    <input type="text"  class="form-control" name="" value="<?=!empty($qsum[0]['ctq']) ? $qsum[0]['ctq'] : $qsum[0]['qty'] ?>"  disabled>
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label class="control-label" for="purchaseorder-carrier">采购员</label>
                    <input type="text"  class="form-control" name="" value="<?=$vb->buyer?>"  disabled>
                </div>
            </div>
            <div class="col-md-1">
                <label class="control-label" for="purchaseorder-carrier">结算方式</label>
                <?= Html::dropDownList('settlement[]',$vb->account_type,SupplierServices::getSettlementMethod(),['prompt' => '请选择','class'=>'form-control settlement','required'=>'required','disabled'=>true ])?>
            </div>

            <div class="col-md-1"><?= $form->field($vb, 'pay_type[]')->dropDownList(\app\services\SupplierServices::getDefaultPaymentMethod(),['prompt' => '请选择','options' => [$vb->pay_type => ['selected' => 'selected']],'disabled'=>true]) ?></div>
            <div class="col-md-1"><?= $form->field($vb, 'shipping_method[]')->dropDownList(\app\services\PurchaseOrderServices::getShippingMethod(),['options' => [$vb->shipping_method => ['selected' => 'selected']]]) ?></div>


            <input type="hidden"  class="form-control" name="PurchaseOrder[settlement][]" value="<?=$vb->account_type?>">
            <input type="hidden"  class="form-control" name="PurchaseOrder[pay_type][]" value="<?=$vb->pay_type?>">
            <div class="col-md-1">
                <label>运费</label>
                <input type="text" class="form-control" name="PurchaseOrder[freight][]" value="<?= !empty($vb->purchaseOrderPayType) ? $vb->purchaseOrderPayType->freight : 0; ?>">
            </div>


            <div class="col-md-1">
                <label>优惠额</label>
                <input type="text" class="form-control" name="PurchaseOrder[discount][]" value="<?= !empty($vb->purchaseOrderPayType) ? $vb->purchaseOrderPayType->discount : 0; ?>">
            </div>











        </div>


        <div class="row">

            <?php if($vb->purchase_type!=1){
                $vb->is_transit=1;
                if (empty($vb->is_drawback)) {
                    $vb->is_drawback=1;
                }
                ?>

                <div class="col-md-2" style="display: block"><?=$form->field($vb, 'is_transit[]')->dropDownList(['2'=>'否','1'=>'是'],['options' => [$vb->is_transit => ['selected' => 'selected']]])->label('是否中转')?></div>
                <div class="col-md-2"><?= $form->field($vb, 'transit_warehouse[]')->dropDownList(['shzz'=>'宁波中转仓库','AFN'=>'东莞中转仓库'],['options' => [$vb->transit_warehouse => ['selected' => 'selected']]]) ?></div>

                <div class="col-md-1"><div class="form-group field-purchaseorder-transit_warehouse">
                        <label class="control-label" for="purchaseorder-transit_warehouse">是否退税</label>
                        <select id="purchaseorder-transit_warehouse" class="form-control is_drawback" name="PurchaseOrder[is_drawback][]">
                            <?php
                            if ($vb->is_drawback==2) {
                                echo '<option value="1">否</option>
                            <option value="2" selected="selected">是</option>';
                            } else {
                                echo '<option value="1" selected="selected">否</option>
                            <option value="2">是</option>';
                            }
                            ?>

                        </select>
                        <div class="help-block"></div>
                    </div>
                </div>

            <?php }else{
                $vb->is_transit=2;
                ?>

            <?php }?>



            <div class="col-md-2">

                <span class="suppname<?=$ak?>" style="display: none"><?=$vb->supplier_name?></span>

                <input type="hidden"  class="form-control" name="PurchaseOrder[supplier_code][]" value="<?=$vb->supplier_code?>">
                <?= $form->field($vb, 'supplier_code[]')->widget(Select2::classname(), [
                    'options' => ['placeholder' => '请选供应商','id'=>'supplier_code'.$ak,'value'=>$vb->supplier_code,'disabled' => true],
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
                <?= $form->field($vb, 'date_eta[]')->widget(DateTimePicker::classname(), [
                    'options' => ['placeholder' => '','value'=>!empty($vb->date_eta)?$vb->date_eta:date('Y-m-d',strtotime('+12 day')),'id'=>'date_eta_'.$ak],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                    ]
                ]); ?>

            </div>



            <label>账号：</label>
            <select name="PurchaseOrder[purchase_acccount][]" class="purchase_acccount">
                <option value="0">请选择...</option>
                <?php
                $accountes = BaseServices::getAlibaba();
                foreach($accountes as $k=>$v):
                    if($purchase_acccount == $v):
                        ?>
                        <option value="<?= $k ?>" selected><?= $v ?></option>
                    <?php else: ?>
                        <option value="<?= $k ?>"><?= $v ?></option>
                    <?php
                    endif;
                endforeach;
                ?>
            </select>



            <label>拍单号：</label>
            <input type="text" name="PurchaseOrder[platform_order_number][]" value="<?= $platform_order_number ?>" style="width:200px">

            <label>是否加急：</label>
            <input type="radio" name="PurchaseOrder[is_expedited][<?=$ak?>]" value="1" checked> 不加急
            <input type="radio" name="PurchaseOrder[is_expedited][<?=$ak?>]" value="2"> 加急


        </div>




        <div class="row">
            <div class="col-md-12">

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>编号</th>
                        <th>图片</th>
                        <th>SKU</th>
                        <th>产品名</th>
                        <th>采购数量</th>
                        <th>单价</th>
                        <th>上次采购单价</th>
                        <th>金额</th>
                        <th>产品链接</th>
                        <th>未处理原因</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody class="bs">
                    <?php
                    $total =0;
                    foreach($vb->purchaseOrderItems as $k=> $v){
                        $suggestNote = '';
                        $price = Product::getProductPrice($v->sku);
                        $price = !empty($price) ? $price : $v->price;

                        $totalprice = $v->ctq*$price;
                        $totalprices = $v->qty*$price;
                        $total += $totalprice?$totalprice:$totalprices;
                        $img=\toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($v['sku'])]);
                        //$img =Html::img($img,['width'=>100]);
                        ?>
                        <tr>
                            <td><?= $k+1 ?></td>
                            <td>
                                <?=Html::a($img,['#'], ['class' => "img", 'data-skus' => $v['sku'],'data-imgs' => $v['product_img'], 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal'])?>
                            </td>

                            <?= Html::input('hidden', 'purchaseOrderItems[pur_number][]', $v->pur_number, ['class' => 'pur_number']) ?>
                            <?= Html::input('hidden', 'purchaseOrderItems[sku][]', $v->sku) ?>

                            <td title="<?=$v->sku?>">
                                <?=Html::a($v->sku,['#'], ['class' => "sales", 'data-sku' =>$v->sku, 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal',]) . \app\models\ProductRepackageSearch::getPlusWeightInfo($v->sku,true,1);?>
                            </td>

                            <td title="<?=$v->name?>">
                                <a href="<?=Yii::$app->params['SKU_ERP_Product_Detail'].$v->sku?>" target="_blank"><?=$v->name?></a>
                            </td>

                            <td class="ctqs">
                                <?= Html::input('number', 'purchaseOrderItems[ctq][]', $v->ctq?$v->ctq:$v->qty, ['class' => 'ctq', 'sku'=>$v->sku ,'pur'=>$v->pur_number, 'onchange' =>"etaNumberChange(this)",  'data-ctq'=>$v->ctq?$v->ctq:$v->qty,'required'=>true,'min'=>1,'max'=>1000000]) ?>
                            </td>

                            <td>
                                <?= Html::input('text', 'purchaseOrderItems[price][]', round($price,4), ['class' => 'price', 'sku'=>$v->sku ,'pur'=>$v->pur_number, 'onchange' => "unitPriceChange(this)", 'data-price'=>round($price,4),'required'=>true,'style'=>'width:60px;','disabled'=>true]) ?>
                                <?= Html::input('hidden', 'purchaseOrderItems[price][]', $price) ?>

                            </td>

                            <td> <!--上次采购单价-->
                                <?php
                                $last_order = \app\models\PurchaseOrderItems::find()
                                    ->select('t.price')
                                    ->from(\app\models\PurchaseOrderItems::tableName().' as t')
                                    ->leftJoin('pur_purchase_order as t1','t1.pur_number = t.pur_number')
                                    ->where(['t.sku'=>$v->sku,'t1.purchas_status'=>[3,5,6,7,8,9]])
                                    ->orderBy('t1.id DESC')
                                    ->one();
                                echo round((!empty($last_order->price)?$last_order->price:'首次采购'),4);
                                ?>
                            </td>

                            <td><?= Html::input('text', 'purchaseOrderItems[totalprice][]',$totalprice?$totalprice:$totalprices, ['class' => 'payable_amount','onchange' => "aaa(this)",'readonly'=>'readonly']) ?></td>
                            <td>
                                <?php
                                $plink=$v->product_link ? $v->product_link : \app\models\SupplierQuotes::getUrl($v->sku);
                                echo Html::input('text', 'purchaseOrderItems[product_link][]',$plink, ['class' => '']) ?>
                                <a href='<?=$plink?>' title='' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>
                            </td>
                            <?php
                            $suggest = \app\models\PurchaseSuggestMrp::find()->where(['demand_number'=>$vb->pur_number,'sku'=>$v->sku,'warehouse_code'=>$vb->warehouse_code])->one();
                            // echo "<pre>";var_dump($vb->pur_number,$suggest);exit;
                            if(empty($suggest)){
                                $suggest = \app\models\PurchaseOrder::find()->where(['pur_number'=>$vb->pur_number,'warehouse_code'=>$vb->warehouse_code])->one();
                                if (!empty($suggest)) {
                                   $noteDatas = \app\models\PurchaseSuggestNote::find()
                                       ->select('suggest_note')
                                       ->where(['sku'=>$v->sku,'warehouse_code'=>$vb->warehouse_code])
                                       ->andWhere(['or',['and',['<=','create_time',$suggest->created_at],['or',['>=','update_time',$suggest->created_at],['update_time'=>null]]],['>=','create_time',$suggest->created_at]])
                                       ->column();
                                   $suggestNote = implode(',',$noteDatas); 
                                }else{
                                   $suggestNote = ''; 
                                }
                            }else{
                                $noteDatas = \app\models\PurchaseSuggestNote::find()
                                    ->select('suggest_note')
                                    ->where(['sku'=>$v->sku,'warehouse_code'=>$vb->warehouse_code])
                                    #->andWhere(['or',['and',['<=','create_time',$suggest->created_at],['or',['>=','update_time',$suggest->created_at],['update_time'=>null]]],['and',['>=','create_time',$suggest->created_at],['<=','create_time',date('Y-m-d 23:59:59',strtotime($suggest->created_at))]]])
                                    ->andWhere(['or',['and',['<=','create_time',$suggest->created_at],['or',['>=','update_time',$suggest->created_at],['update_time'=>null]]],['>=','create_time',$suggest->created_at]])
                                    ->column();
                                $suggestNote = implode(',',$noteDatas);
                            }

                            ?>
                            <td><?= Html::input('text', 'suggest-note',$suggestNote,['sku'=>$v->sku ,'warehouse_code'=>$vb->warehouse_code]) ?></td>
                            <td><a href="#" data-sku="<?=$v->sku?>" data-pur="<?=$v->pur_number?>" data-k="<?=$k+1?>" class="dels" >删除</a></td>
                        </tr>

                    <?php }?>
                    <tr class="table-module-b1">
                        <td class="total" colspan="8">总额：<b><?=round($total,2).'&nbsp;&nbsp;'.$vb->currency_code?></b></td>
                    </tr>
                    </tbody>
                </table>

            </div>


            <input type="hidden" class="form-control isbb" name="PurchaseOrderOrders[is_request][]" value="0">
            <div class="col-md-12"><div class="form-group field-purchasenote-note required">
                    <label class="control-label" for="purchasenote-note">确认备注</label>
                    <textarea id="purchasenote-note" class="form-control" name="PurchaseNote[note][]" rows="3"><?=!empty($vb->orderNote->note)?$vb->orderNote->note:''?></textarea>

                    <div class="help-block"></div>
                </div>
            </div>


            <div class="col-md-12">

                提交操作： <label><input name="PurchaseOrder[submit][<?=$ak?>]" type="radio" value="1" checked />保存</label>
                <label><input name="PurchaseOrder[submit][<?=$ak?>]" type="radio" value="2" />提交</label>

            </div>

        </div>

    </div>

<?php endforeach; ?> <!--主循环 End-->

<div class="form-group">
    <?= Html::submitButton($vb->isNewRecord ? Yii::t('app', '确定') : Yii::t('app', '确认'), ['class' => 'btn btn-success po_submit']) ?>
</div>

<?php ActiveForm::end(); ?>
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
$settlementurl=Url::toRoute(['supplier/get-supplier-issettlement']);

$count_arr=count($models);

$js = <<<JS
$(function() {
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

    $('[name="PurchaseOrder[supplier_code][]"]').on("select2:select",function(e){
        var obj= $(this);
　　  $.get('$settlementurl', {supplierCode:$(this).val()},
            function (data) {
                var response = $.parseJSON(data);
                if(response.status=='error'){
                    obj.closest('.container-fluid').find('.settlement').attr('disabled',true);
                    obj.closest('.container-fluid').find('[name="PurchaseOrder[account_type][]"]').val(response.account_type);
                    obj.closest('.container-fluid').find('.settlement').val(response.account_type);
                }else{
                    obj.closest('.container-fluid').find('.settlement').attr('disabled',true);
                }
            }
        );
    });
    
    $('[name="PurchaseOrder[supplier_code][]"]').trigger('select2:select');
    
    $('.settlement').change(function() {
      $(this).closest('.container-fluid').find('[name="PurchaseOrder[account_type][]"]').val($(this).val());
    });
    
    $('.settlement').trigger('change');
    
    $(".dels").click(function(){
        var sku=$(this).attr('data-sku');
        var pur=$(this).attr("data-pur");
        var k=$(".bs tr").length;
        if(k>2){
            if(confirm("确认删除")){
                $.get("$url_update_qty",{sku:sku,pur:pur},function(result){

                    if(result){
                        alert('删除成功');
                        window.location.reload();
                    }else{
                        alert('删除失败');

                    }
                });
            }else{
                return false;
            }
        } else{
            alert('剩下一个sku了,请直接撤销采购单吧');
        }
    });
    //失焦添加readonly
    $("input[name='suggest-note']").change(function(){
        //$(this).attr("readonly","true");
        var suggest_note = $(this).val();
        var sku   = $(this).attr('sku');
        var warehouse_code   = $(this).attr('warehouse_code');
        $.ajax({
            url:'/purchase-suggest/update-suggest-note',
            data:{suggest_note:suggest_note,sku:sku,warehouse_code:warehouse_code},
            type: 'get',
            dataType:'json'
        });
       });
    
     $(document).on('click', '#suggest-notes', function () {
         var ids = $('#grid_purchase').yiiGridView('getSelectedRows');
          if(ids==''){
                alert('请先选择!');
                return false;
            }else{
               $.get($(this).attr('href'), {id: ids},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
            }
    });
     
     if($("#default_supplier_count").html()){
         layer.alert("采购单中SKU默认供应商不同，单号：<br/>"+$("#default_supplier_count").html());
         $(".po_submit").hide();
         return false;
     }
});
JS;

$this->registerJs($js);
?>

<script>
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
            var bi = Math.round((tv-tctq)/tctq * 10000) / 100.00 + "%";
            alert('下降：' + bi + '\n修改后的数量小于采购数量！');
        } else if (tv>tctq){
            var bi = Math.round((tv-tctq)/tctq * 10000) / 100.00 + "%";
            alert('增长：'+ bi+'\n修改后的数量大于采购数量！');
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
            url: '/overseas-purchase-order-confirm/view-update-log',
            data: {sku:sku,pur:pur},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                var bi = Math.round((tvp-tp)/tp * 10000) / 100.00 + "%";
                if(tvp>tp){
                    layer.alert('增长：' + bi + '\n修改后的单价大于系统单价！<br />' + data.message  +  '<br /><span color="red">修改单价请到【供货商商品管理】页面修改</span>');
                } else if (tvp<tp){
                    layer.alert('下降：' + bi + '\n修改后的单价小于系统单价！<br />' + data.message  +  '<br /><span color="red">修改单价请到【供货商商品管理】页面修改</span>');
                }
                $('.po_submit').prop('disabled', true);
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
