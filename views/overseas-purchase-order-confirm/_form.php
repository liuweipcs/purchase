<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\SupplierServices;
use \kartik\datetime\DateTimePicker;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierGoodsServices;
use yii\bootstrap\Modal;


?>

    <div class="purchase-order-form">
        <?php $form = ActiveForm::begin([
                'id'=>'proform',
        ]); ?>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'warehouse_code')->dropDownList(BaseServices::getWarehouseCode(),['prompt' => '请选仓库']) ?>
            </div>

            <div class="col-md-4">
                <?php //if($model->isNewRecord){$model->is_transit='0';} echo $form->field($model, 'is_transit')->radioList(['0'=>'需要中转','1'=>'直发'])->label('') ?>

                <div class="form-group field-purchaseorder-is_transit">
                    <label class="control-label"></label>
                    <input type="hidden" name="PurchaseOrder[is_transit]" value="">
                    <div id="purchaseorder-is_transit">
                        <label><input id="check-radio1" type="radio" name="PurchaseOrder[is_transit]" value="0"> 需要中转</label>
                        <label><input id="check-radio2" type="radio" name="PurchaseOrder[is_transit]" value="1" checked=""> 直发</label>
                    </div>
                    <div class="help-block"></div>
                </div>

            </div>

            <div class="col-md-4" id="warehouse">
                <?= $form->field($model, 'transit_warehouse')->dropDownList(BaseServices::getWarehouseCode(),['prompt' => '请选中转仓']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'pur_type')->dropDownList(PurchaseOrderServices::getPurType(),['prompt' => '请选补货方式']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'supplier_code')->dropDownList(BaseServices::getSupplier(),['prompt' => '请选供应商']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'buyer')->dropDownList(BaseServices::getEveryOne(),['prompt' => '请选采购员']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'currency_code')->dropDownList(SupplierGoodsServices::getCurrency(),['prompt' => '请选择币种']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'tracking_number')->textInput(['maxlength' => true,'placeholder'=>'跟踪单号']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'shipping_method')->dropDownList(PurchaseOrderServices::getShippingMethod(), ['prompt' => '供应商运输方式']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'pay_ship_amount')->textInput(['maxlength' => true,'placeholder'=>'运费']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'operation_type')->dropDownList(PurchaseOrderServices::getOperationType(), ['prompt' => '运营方式']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'merchandiser')->textInput(['maxlength' => true,'placeholder'=>'跟单员']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'pay_number')->textInput(['maxlength' => true,'placeholder'=>'支付单号']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'reference')->textInput(['maxlength' => true,'placeholder'=>'参考号']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'account_type')->dropDownList(SupplierServices::getSettlementMethod(), ['prompt' => '请选择结算方式']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'pay_type')->dropDownList(SupplierServices::getDefaultPaymentMethod(), ['prompt' => '请选择支付方式']) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'date_eta')->widget(DateTimePicker::className(),[
                    'options' => ['placeholder' => '','readonly'=>true],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd hh:ii:ss',
                    ]
                ]); ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'created_at')->widget(DateTimePicker::className(),[
                    'options' => ['readonly'=>true],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd hh:ii:ss',
                    ]
                ]); ?>
            </div>
        </div>
        <div class="form-group">
            <?= Html::a('创建', ['addproduct'], ['class' => 'btn btn-success','id'=>'createpro',/*'data-toggle' => 'modal','data-target' => '#create-modal'*/]) ?>

        </div>


        <?php ActiveForm::end(); ?>

    </div>

<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">添加产品</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$js=<<<JS
    $('#warehouse').hide();
    $('#check-radio2').on('click', function(){   
      $('#warehouse').hide();
    });
    
    $('#check-radio1').on('click', function(){
       $('#warehouse').show();
    });
    
    //添加产品
        $("a#createpro").click(function(){
        var data=$('#proform').serialize();
                       // alert(data);
            var url=$(this).attr("href");
            //var ids = $('#grid_purchase').yiiGridView('getSelectedRows');
            /*if(ids==''){
               alert('请选择要修改的采购员');
               return false;
            }else{*/
              // $.post(url, {},
                  //  function (data) {
                        
                        url     = url+'?data='+data;
                        $(this).attr('href',url);
                        //$('.modal-body').html(data);
                //    }
               //  );           
            //}
        });
JS;

$this->registerJs($js);
?>