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
use app\models\ProductTaxRate;
use app\models\PurchaseOrderTaxes;
use app\models\Product;
use app\models\SupplierQuotes;


$suppurl = \yii\helpers\Url::to(['/supplier/search-supplier']);
$this->title = '采购确认';
$this->params['breadcrumbs'][] = '海外仓';
$this->params['breadcrumbs'][] = '采购计划单';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .row {
        padding: 10px;
    }
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

<?php $form = ActiveForm::begin(['id' => 'submit-audit-form',
//        'enableAjaxValidation' => true,
//        'validationUrl' => Url::toRoute(['validate-form']),
    ]
); ?>

<p>
    <?= Html::a('导出Excel', ["purchase-order-confirm/export?id={$id[0]}"], ['class' => 'btn btn-success print','id'=>'export']) ?>
</p>

<?php

foreach($models as $ak=>$vb):
    // 预设默认值 结算
    $vb->account_type = $vb->account_type ? $vb->account_type : 2;
    $vb->account_type = empty($vb->supplier->supplier_settlement) ? $vb->account_type : $vb->supplier->supplier_settlement;

    // 支付
    $vb->pay_type = $vb->pay_type ? $vb->pay_type : 2;
    $vb->pay_type = empty($vb->supplier->payment_method) ? $vb->pay_type : $vb->supplier->payment_method;

    // 运输方式
    $vb->shipping_method = $vb->shipping_method ? $vb->shipping_method : 2;

    //供应商
    $vb->supplier_name = $vb->supplier_name ? $vb->supplier_name : '';
    $vb->supplier_name = empty($vb->supplier->supplier_name) ? $vb->supplier_name : $vb->supplier->supplier_name;

    $purchase_acccount = '';
    $platform_order_number = '';
    $purchase_acccount = '';
    $freight_formula_mode = 'weight';
    $settlement_ratio = '';
    $purchase_source = 1;
    if(!empty($vb->purchaseOrderPayType)) {
        $purchase_acccount = $vb->purchaseOrderPayType->purchase_acccount;
        $platform_order_number = $vb->purchaseOrderPayType->platform_order_number;
        $purchase_acccount = $vb->purchaseOrderPayType->purchase_acccount;
        $freight_formula_mode = $vb->purchaseOrderPayType->freight_formula_mode;
        $settlement_ratio = $vb->purchaseOrderPayType->settlement_ratio;
        $purchase_source = $vb->purchaseOrderPayType->purchase_source;
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
                <label class="control-label" for="purchaseorder-carrier">结算方式</label>
                <?= Html::dropDownList('settlement[]',$vb->account_type,SupplierServices::getSettlementMethod(),['class'=>'form-control settlement','required'=>'required' ])?>
            </div>
            <div class="col-md-3">
                <label>结算比例</label>
                <div class="input-group">
                    <input type="text" name="PurchaseOrder[settlement_ratio][]" class="form-control settlement_ratio" value="<?= $settlement_ratio ?>" readonly>
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

            <div class="col-md-1"><?= $form->field($vb, 'pay_type[]')->dropDownList(\app\services\SupplierServices::getDefaultPaymentMethod(),['options' => [$vb->pay_type => ['selected' => 'selected']]]) ?></div>
            <div class="col-md-1"><?= $form->field($vb, 'shipping_method[]')->dropDownList(\app\services\PurchaseOrderServices::getShippingMethod(),['options' => [$vb->shipping_method => ['selected' => 'selected']]]) ?></div>

        </div>


        <div class="row">



            <?php if($vb->purchase_type!=1){
                $vb->is_transit=1;
                if (empty($vb->is_drawback)) {
                    $vb->is_drawback=1;
                }
                ?>
                <div class="col-md-2" style="display: block"><?=$form->field($vb, 'is_transit[]')->dropDownList(['2'=>'否','1'=>'是'],['options' => [$vb->is_transit => ['selected' => 'selected']]])->label('是否中转')?></div>
                <div class="col-md-1"><?= $form->field($vb, 'transit_warehouse[]')->dropDownList(['shzz'=>'上海中转仓库','AFN'=>'东莞中转仓库'],['options' => [$vb->transit_warehouse => ['selected' => 'selected']]]) ?></div>

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

                <!--<div class="col-md-2" style="display: block">
                    <?/*=$form->field($vb, 'is_drawback[]')->dropDownList(['2'=>'是','1'=>'否'],['class'=> 'is_drawback','options' => [$vb->is_transit => ['selected' => 'selected']]])->label('是否退税') */?>
                </div>-->
            <?php }else{
                $vb->is_transit=2;
                ?>
                <!--<div class="col-md-2" style="display: block"><?/*=$form->field($vb, 'is_transit[]')->dropDownList(['2'=>'否','1'=>'是'],['options' => [$vb->is_transit => ['selected' => 'selected']]])->label('是否中转') */?></div>
                <div class="col-md-2" style="display: block"><?/*= $form->field($vb, 'transit_warehouse[]')->dropDownList(\app\services\BaseServices::getWarehouseCode(),['readonly'=>'readonly']) */?></div>-->

            <?php }?>

            <div class="col-md-2">

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


            <div class="col-md-1">
                <label>运费</label>
                <input type="text" class="form-control" name="PurchaseOrder[freight][]" value="<?= !empty($vb->purchaseOrderPayType) ? $vb->purchaseOrderPayType->freight : 0; ?>">
            </div>

            <div class="col-md-1">
                <div class="form-group">
                    <label>运费计算方式</label>
                    <select name="PurchaseOrder[freight_formula_mode][]" class="form-control">
                        <?php if($freight_formula_mode == 'volume'): ?>
                            <option value="weight">重量</option>
                            <option value="volume" selected>体积</option>
                        <?php else: ?>
                            <option value="weight" selected>重量</option>
                            <option value="volume">体积</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-1">
                <label>优惠额</label>
                <input type="text" class="form-control" name="PurchaseOrder[discount][]" value="<?= !empty($vb->purchaseOrderPayType) ? $vb->purchaseOrderPayType->discount : 0; ?>">
            </div>


        </div>

        <div class="row">

            <div class="col-md-2">
                <div class="form-group">
                    <label>采购来源</label>
                    <select name="PurchaseOrder[purchase_source][]" class="form-control purchase_source">
                        <?php
                        $purchase_source_list = [
                            '1' => '合同采购',
                            '2' => '网络采购',
                            '3' => '账期采购',
                        ];
                        foreach($purchase_source_list as $k=>$v):
                            ?>
                            <option value="<?= $k ?>" <?php if($purchase_source == $k) { echo 'selected';} ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <label>账号</label>
                <select name="PurchaseOrder[purchase_acccount][]" class="form-control purchase_acccount">
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
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label>拍单号</label>
                    <input type="text" name="PurchaseOrder[platform_order_number][]" class="form-control platform_order_number" value="<?= $platform_order_number ?>">
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-12">

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>出口退税税率</th>
                        <th>采购开票点</th>
                        <th>图片</th>
                        <th>SKU</th>
                        <th>产品名</th>
                        <th>采购数量</th>
                        <th>单价</th>
                        <th>金额</th>
                        <th>产品链接</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody class="bs">
                    <?php
                    $total =0;
                    foreach($vb->purchaseOrderItems as $k=> $v){
                        //单价
                        $price = Product::getProductPrice($v->sku);
                        $price = !empty($price) ? $price : $v->price;
                        $totalprice = $v->ctq*$price;
                        $totalprices = $v->qty*$price;
                        $total += $totalprice?$totalprice:$totalprices;

                        $img=\toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($v['sku'])]);

                        //产品链接
                        $plink= !empty(SupplierQuotes::getUrl($v->sku)) ?  SupplierQuotes::getUrl($v->sku) : $v->product_link;
                        
                        ?>
                        <tr>
                            <td>
                                <input type="text"  class="form-control hq" name="" value="<?=ProductTaxRate::getRebateTaxRate($v['sku']); ?>" style="width: 80px" disabled>
                            </td>
                            <td>
                                <?= Html::input('number', 'ProductTaxRate[taxes][]', PurchaseOrderTaxes::getABDTaxes($v['sku'],$v['pur_number']),['class'=>'hq','style'=>['width'=>'60px'],'max'=>100,'sku'=>$v['sku'],'id'=>$v['sku']."_tax"]) ?>
                                <?= Html::input('hidden', 'ProductTaxRate[sku][]', $v->sku) ?>
                                <?= Html::input('hidden', 'ProductTaxRate[pur_number][]', $v->pur_number) ?>
                            </td>
                            <td>
                                <?=Html::a($img,['#'], ['class' => "img", 'data-skus' => $v['sku'],'data-imgs' => $v['product_img'], 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal'])?>
                            </td>

                            <?= Html::input('hidden', 'purchaseOrderItems[pur_number][]', $v->pur_number, ['class' => 'pur_number']) ?>
                            <?= Html::input('hidden', 'purchaseOrderItems[sku][]', $v->sku) ?>

                            <td title="<?=$v->sku?>">
                                <?=Html::a($v->sku,['#'], ['class' => "sales", 'data-sku' =>$v->sku, 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal',]).(empty(Product::findOne(['product_is_new'=>1,'sku'=>$v->sku]))?'':'<sub><font size="1" color="red">新</font></sub>')?>
                            </td>

                            <td title="<?=$v->name?>">
                                <a href="<?=Yii::$app->params['SKU_ERP_Product_Detail'].$v->sku?>" target="_blank"><?=$v->name?></a>
                            </td>

                            <td class="ctqs">
                                <?= Html::input('number', 'purchaseOrderItems[ctq][]', $v->ctq?$v->ctq:$v->qty, ['id'=>$v->sku."_ctq",'class' => 'ctq', 'sku'=>$v->sku ,'pur'=>$v->pur_number, 'onchange' =>"etaNumberChange(this)",  'data-ctq'=>$v->ctq?$v->ctq:$v->qty,'required'=>true,'min'=>1,'max'=>1000000]) ?>
                            </td>

                            <td>
                                <?= Html::input('text', 'purchaseOrderItems[price][]', round($price,4), ['id'=>$v->sku."_price",'class' => 'price', 'sku'=>$v->sku ,'pur'=>$v->pur_number, 'onchange' => "unitPriceChange(this)", 'data-price'=>round($v->price,4),'required'=>true,'style'=>'width:60px;']) ?>
                            </td>

                            <td><?= Html::input('text', 'purchaseOrderItems[totalprice][]',$totalprice?$totalprice:$totalprices, ['id'=>$v->sku."_total",'class' => 'payable_amount','onchange' => "aaa(this)",'readonly'=>'readonly']) ?></td>
                            <td>
                                <?php
                                echo Html::input('text', 'purchaseOrderItems[product_link][]',$plink, ['class' => '']) ?>
                                <a href='<?=$plink?>' title='' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>
                            </td>
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
    <?= Html::submitButton($vb->isNewRecord ? Yii::t('app', '确定') : Yii::t('app', '确认'), ['class' => 'btn btn-success abd_submit', 'id'=>'btn-submit']) ?>
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
    //刷新自动计算金额  税金税金
    $('[name="PurchaseOrder[is_drawback][]"]').trigger("change");
    
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
    
    $('#btn-submit').click(function() {
        var flag = true;
        $('.purchase_source').each(function() {
            var type = $(this).val();
            if(type == 2) {
                var parent = $(this).parents('.row');
                purchase_acccount = parent.find('.purchase_acccount').val();
                platform_order_number = parent.find('.platform_order_number').val();
                if(purchase_acccount == 0 || platform_order_number == '') {
                    layer.alert('你有选择了网络采购，但是没有选择平台账号或没有填写平台拍单号的，请完善这些信息后，才可以提交。', {icon: 5});
                    flag = false;
                    return false;
                }
            } else if(type == 1) {
                var parent = $(this).parents('.container-fluid');
                settlement_ratio = parent.find('.settlement_ratio').val();
                if(settlement_ratio == '') {
                    layer.alert('你有选择了合同采购，但是没有选择结算比例，务必完善这些信息，否则后期无法请款。', {icon: 5});
                    flag = false;
                    return false;
                }
            }
        });
        $('.settlement').each(function() {
          if($(this).val()==null){
               layer.alert('请选择结算方式', {icon: 5});
               flag = false;
               return false;
          }
        });
        if(flag) {
             $('#submit-audit-form').submit();
        }
    });
    
    $('.purchase_source').change(function() {
        var type = $(this).val();
        if(type == 3) {
            var parent = $(this).parents('.container-fluid');
            settlement_ratio = parent.find('.settlement_ratio').val('100%');
        }  else {
            var parent = $(this).parents('.container-fluid');
            settlement_ratio = parent.find('.settlement_ratio').val('');
        }
    });
    
    $(".hq").change(function() {
        var s = $(this).parents('.bs').find('tr');
        for(var i=0; i<s.length-1; i++) {
            var inputs = $(s[i]).find('input.hq');
            var tax_rate = parseFloat($(inputs[0]).val());
            var taxes = parseFloat($(inputs[1]).val());
            if ((tax_rate - taxes) >=1 ) {
                if (taxes <=0) {
                    $(this).parents('.container-fluid').find('.is_drawback').val('1');
                } else {
                    $(this).parents('.container-fluid').find('.is_drawback').val('2');
                }
                break;
            }
            $(this).parents('.confirm_class').find('select.is_drawback').val(1);
        }
    });
    
    $('[name="PurchaseOrder[supplier_code][]"]').on("select2:select",function(e){
        var obj= $(this);
　　  $.get('$settlementurl', {supplierCode:$(this).val()},
            function (data) {
                var response = $.parseJSON(data);
                obj.closest('.container-fluid').find('[name="PurchaseOrder[account_type][]"]').val(response.account_type);
                obj.closest('.container-fluid').find('.settlement').val(response.account_type);
                if(response.status=='error'){
                    obj.closest('.container-fluid').find('.settlement').attr('disabled',true);
                }else{
                    obj.closest('.container-fluid').find('.settlement').attr('disabled',false);
                }
            }
        );
    });
    
    $('[name="PurchaseOrder[supplier_code][]"]').trigger('select2:select');
    
    $('.settlement').change(function() {
      $(this).closest('.container-fluid').find('[name="PurchaseOrder[account_type][]"]').val($(this).val());
    });
    
    
    
    
    

    
    for(i=0; i<$count_arr; i++){
        $('#select2-supplier_code'+i+'-container').text($('.suppname'+i).text());
    }   
    
    });

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
        $(".pai").parent().show();
        
    //税金税金税金    
    $('input[name="ProductTaxRate[taxes][]"]').on("change",function(e){
          var tax = $(this).val();
          var is_drawback = $("select[name='PurchaseOrder[is_drawback][]'] :selected").val();
          var sku = $(this).attr('sku');
          var ctq = $("#"+sku+"_ctq").val();
          var price = $("#"+sku+"_price").val();
          if(is_drawback==2 && (tax>0 && tax<=100)){
              tax = floatAdd(FloatDiv(tax,100),1);
              $("#"+sku+"_total").val(accMul(accMul(tax,ctq),price));
          }else{
              $("#"+sku+"_total").val(accMul(ctq,price));
          }
    }); 
    
    
    $('[name="PurchaseOrder[is_drawback][]"]').on("change",function(e){
          var is_drawback = $("select[name='PurchaseOrder[is_drawback][]'] :selected").val();
            $(".sales").each(function(){
                var sku = $(this).attr("data-sku");
                var tax = $("#"+sku+"_tax").val();
                var ctq = $("#"+sku+"_ctq").val();
                var price = $("#"+sku+"_price").val();
                tax = floatAdd(FloatDiv(tax,100),1);
                if(is_drawback == 2){
                    $("#"+sku+"_total").val(accMul(accMul(tax,ctq),price));
                }else{
                    $("#"+sku+"_total").val(accMul(ctq,price));    
                }
            })
    });
    //加
    function floatAdd(arg1,arg2){    
         var r1,r2,m;    
         try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}    
         try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}    
         m=Math.pow(10,Math.max(r1,r2));    
         return (arg1*m+arg2*m)/m;    
    } 
    //乘
    function accMul(arg1, arg2) {
        var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
        try {
            m += s1.split(".")[1].length;
        }
        catch (e) {
        }
        try {
            m += s2.split(".")[1].length;
        }
        catch (e) {
        }
        return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
    }
    //除
    function FloatDiv(arg1,arg2){
        var t1=0,t2=0,r1,r2;
        try{t1=arg1.toString().split(".")[1].length}catch(e){}
        try{t2=arg2.toString().split(".")[1].length}catch(e){}
        with(Math){
            r1=Number(arg1.toString().replace(".",""));
            r2=Number(arg2.toString().replace(".",""));
            return (r1/r2)*pow(10,t2-t1);
        }
    }
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
            layer.alert('下降：' + bi + '\n修改后的数量小于采购数量！');

        } else if (tv>tctq){
            var bi = Math.round((tv-tctq)/tctq * 10000) / 100.00 + "%";
            layer.alert('增长：'+ bi+'\n修改后的数量大于采购数量！');
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
