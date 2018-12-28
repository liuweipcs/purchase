<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\Url;

$platform = \app\models\PlatformSummarySearch::overseasPlatformList(null,true);
$platform = array_change_key_case($platform,CASE_UPPER);
?><div class="user-form">
        <?php $form = ActiveForm::begin(['id'=>'demand-form']); ?>
        <table>
            <thead>
                <th>sku</th>
                <th>平台</th>
                <th>产品名称</th>
                <th>需求状态</th>
                <th>采购数量</th>
                <th>采购单价</th>
                <th>中转数量</th>
                <th>采购金额</th>
            </thead>
            <tbody>
            <?php
            $totalPrice =0;
            ?>
        <?php foreach ($datas as $key=>$model){
            // 产品信息不全时 没有供应商及报价，设置默认值避免报错
            if(isset($model->defaultSupplier)){
                $supplier_code = $model->defaultSupplier->supplier_code;
            }else{
                $supplier_code = '';
            }
            if(isset($model->defaultQuotes)){
                $supplierprice = $model->defaultQuotes->supplierprice;
            }else{
                $supplierprice = 0;
            }

            ?>
            <?= Html::hiddenInput("PlatformSummary[$key][demand_number]",$model->demand_number,['key'=>$key,'element'=>'demand_number'])?>
            <?= Html::hiddenInput("PlatformSummary[$key][purchase_warehouse]",$model->purchase_warehouse,['key'=>$key,'element'=>'purchase_warehouse'])?>
            <?php
                $totalPrice += $model->purchase_quantity*($supplierprice*1000)/1000;
                $ids[]= $model->id;
                if(isset($type) && $type){
            ?>
                <tr class="<?=$supplier_code?>">
            <?php }else{?>
                <tr class="<?=$supplier_code."_".$model->purchase_warehouse?>">
           <?php } ?>
            <td>
                <div class="form-group">
                    <label class="control-label"></label>
                    <?= Html::textInput("PlatformSummary[$key][sku]",$model->sku,['class'=>'form-control','maxlength'=>true,'check_rule'=>1,'readonly'=>true,'title'=>$model->sku,'key'=>$key,'element'=>'sku'])?>
                    <?= Html::hiddenInput("PlatformSummary[$key][transport_style]",$model->transport_style,['key'=>$key,'element'=>'transport_style'])?>
                </div>
            </td>
            <td>
                <div class="form-group">
                    <label class="control-label"></label>
                    <?= Html::dropDownList("",strtoupper($model->platform_number),$platform,['class'=>'form-control','disabled'=>true])?>
                </div>
            </td>
            <td width="20%">
                <div class="form-group">
                    <label class="control-label"></label>
                    <?=Html::textInput('',$model->product_name,['class'=>'form-control','readonly'=>true,'title'=>$model->product_name])?>
                <div
            </td>
            <td>
                <?php
                if($model->level_audit_status==1)
                {
                    $str ='';
                    $str .= Yii::$app->params['demand'][$model->level_audit_status];
                } elseif($model->level_audit_status==2){
                    $str = Yii::$app->params['demand'][$model->level_audit_status];
                    $str .= '原因：'.$model->audit_note;
                } elseif($model->level_audit_status==4){
                    $str = Yii::$app->params['demand'][$model->level_audit_status];
                    $str .= '原因：'.$model->purchase_note;
                }elseif($model->level_audit_status==6){
                    $str = Yii::$app->params['demand'][$model->level_audit_status];
                    $str .= '原因：'.$model->audit_note;
                } else{
                    $str =  Yii::$app->params['demand'][$model->level_audit_status];
                }
                ?>
                <div class="form-group">
                    <label class="control-label"></label>
                    <?= Html::input('text','',$str,['class'=>'form-control','readonly'=>true,'title'=>$str])?>
                </div>
            </td>
            <td style="width: 10%">
                <div class="form-group">
                    <label class="control-label"></label>
            <?php if(isset($type) && $type){?>
                <?=Html::Input('number',"PlatformSummary[$key][purchase_quantity]",$model->purchase_quantity,['class'=>'form-control quantity','readonly'=>isset($view)&&$view=='revoke'?true:false,'supplier_code'=>$supplier_code,'purchase_warehouse'=>$model->purchase_warehouse,'is_seven'=>'seven_days','data_id'=>$model->id,'key'=>$key,'element'=>'purchase_quantity']) ?>
            <?php }else{?>
                <?=Html::Input('number',"PlatformSummary[$key][purchase_quantity]",$model->purchase_quantity,['class'=>'form-control quantity','readonly'=>isset($view)&&$view=='revoke'?true:false,'supplier_code'=>$supplier_code,'purchase_warehouse'=>$model->purchase_warehouse,'data_id'=>$model->id,'key'=>$key,'element'=>'purchase_quantity']) ?>
            <?php }?>
                </div>
            </td>

                <td>
                    <div class="form-group">
                    <label class="control-label"></label>
                    <?= Html::input('number','',$supplierprice,['class'=>'form-control price','step'=>'0.001','readonly'=>true])?>
                    </div>
                </td>
            <td>
                <div class="form-group">
                    <label class="control-label"></label>
            <?= Html::Input('number',"PlatformSummary[$key][transit_number]",$model->transit_number,['class'=>'form-control transit_number','readonly'=>isset($view)&&$view=='revoke'?true:false,'key'=>$key,'element'=>'transit_number']);?>
                </div>
            </td>
            <td>
                <div class="form-group">
                    <label class="control-label"></label>
                <?= Html::input('number','',$model->purchase_quantity*$supplierprice,['class'=>'form-control purchaseTotal','step'=>'0.001','readonly'=>true]); ?>
                </div>
            </td>
            </tr>
            <?php
                //非7天3小时
                if(isset($total[$supplier_code][$model->purchase_warehouse])){
                    $total[$supplier_code][$model->purchase_warehouse]['count']++;
                    if($total[$supplier_code][$model->purchase_warehouse]['total']  == 1 || $total[$supplier_code][$model->purchase_warehouse]['total'] == $total[$supplier_code][$model->purchase_warehouse]['count']){
            ?>
                <tr>
                    <td style="color: red" colspan="2">
                        <?= '供应商：'.\app\services\SupplierServices::getSupplierName($supplier_code) ?>
                    </td>
                    <td style="color: red" colspan="2">
                        <?= '采购仓：'.\app\models\Warehouse::find()->select('warehouse_name')->where(['warehouse_code'=>$model->purchase_warehouse])->scalar() ?>
                    </td>

                    <td style="width: 10%" colspan="2" id="<?=$supplier_code."_".$model->purchase_warehouse?>">总采购金额: <?=$total[$supplier_code][$model->purchase_warehouse]['price'] ?></td>
                </tr>
            <?php
                    }}elseif(isset($total[$supplier_code]) ){

                    //7天3小时
                        $total[$supplier_code]['count'] ++;
                        if($total[$supplier_code]['total'] == $total[$supplier_code]['count']){
            ?>
                <tr>
                    <td style="color: red" colspan="2">
                        <?= '供应商：'.\app\services\SupplierServices::getSupplierName($supplier_code) ?>
                    </td>

                    <td style="width: 10%" colspan="2" id="<?=$supplier_code?>">总采购金额: <?=$total[$supplier_code]['price'] ?></td>
                </tr>
        <?php }}} ?>
            </tbody>
        </table>
        <div class="form-group" style="clear: both">
            <?php
                if(isset($view)&&$view=='revoke'){
                    echo Html::submitButton( Yii::t('app', '撤销'), ['class' =>'btn btn-primary submit']);
                }else{
                    echo Html::button( Yii::t('app', '更新'), ['class' =>'btn btn-primary update','ids'=>implode(',',$ids)]);
                }
            ?>
        </div>
        <span style="color: #2fd419">注：采购单价|采购金额 为0 可能是没有供应商报价</span>
        <?php ActiveForm::end(); ?>
    </div>
<?php
$url =  Url::toRoute('check-rule');

$js = <<<JS
    $('.quantity').change(function() {
      var num = $(this).val();
      var price = $(this).closest('tr').find('.price').val();
      var supplier_code = $(this).attr('supplier_code');
      var purchase_warehouse = $(this).attr('purchase_warehouse');
      var type =  $(this).attr('is_seven'); 
      $(this).closest('tr').find('.purchaseTotal').val(num*(price*1000)/1000);
      $(this).closest('tr').find('.transit_number').val(num);
      var totalprice = 0;
      if(type == 'seven_days'){
         $("."+supplier_code+" .purchaseTotal").each(function() {
            totalprice =   (totalprice*1000+$(this).val()*1000)/1000;
          });
          $('.total-price').text(totalprice);
          $("#"+supplier_code).text("总采购金额:"+totalprice); 
      }else{
          $("."+supplier_code+"_"+purchase_warehouse+" .purchaseTotal").each(function() {
            totalprice =   (totalprice*1000+$(this).val()*1000)/1000;
          });
          $('.total-price').text(totalprice);
          $("#"+supplier_code+"_"+purchase_warehouse).text("总采购金额:"+totalprice);
      }
    });
    $('.update').click(function() {
        var ids = $(this).attr('ids');
        var updateArr = new Array();
        $('.quantity').each(function() {
          updateArr.push($(this).attr('data_id')+'-'+$(this).val());
        });
        
        //组装参数
        var obj = [];
        $("input[name^='PlatformSummary']",$("#demand-form")).each(function(index,element) {
            var key = $(this).attr('key');
            if('undefined' == typeof obj[$(this).attr('key')]){
                obj[key] = [];
            }
            obj[key][$(this).attr('element')] = $(this).val();
        })
        var update_data = [];
        for(var k in obj){
            update_data.push({
              demand_number:obj[k].demand_number,
              purchase_warehouse :obj[k].purchase_warehouse ,
              sku:obj[k].sku,
              transport_style:obj[k].transport_style,
              purchase_quantity:obj[k].purchase_quantity,
              transit_number:obj[k].transit_number,
            })
        }
        

        $.get('{$url}', {ids:ids,updateArr:updateArr},
            function (data) {
                if(data.status=='error'){
                    layer.msg(data.message);
                }else if(data.status=='success'){
                    $.ajax({
                        url: '/overseas-purchase-demand/batch-update',
                        type: 'post',
                        data: {'update_arr': JSON.stringify(update_data)},
                        dataType: 'json',
                        success: function(data) {
                            if(data.status==1){
                                layer.alert(data.msg);
                            }else{
                                layer.alert(data.msg);
                            }
                        }
                    });
                }
            },'json'
        );
    });
JS;
$this->registerJs($js);
?>