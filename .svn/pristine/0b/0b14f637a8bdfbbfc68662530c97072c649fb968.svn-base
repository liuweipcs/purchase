<?php
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 15:57
 */

$supplier_buyer = \app\models\SupplierBuyer::find()->andFilterWhere(['supplier_code'=>$model->supplier_code,'status'=>1])->select('id, type,buyer')->indexBy('type')->asArray()->all();

//账号信息
if (!empty($auditInfo['supplier_buyer'])) {
    foreach ($supplier_buyer as $mk => $mv) {
        if (!empty($auditInfo['supplier_buyer'][$mv['id']]['buyer'])) {
            $supplier_buyer[$mk]['buyer'] = $auditInfo['supplier_buyer'][$mv['id']]['buyer'];
            echo "<style>";
            echo '#buyer_info_content tbody tr:nth-child('. $mv['type'] .') td:nth-child(2){color:red;}';
            echo "</style>";
        }
    }
}

    $inbuyer = isset($supplier_buyer[1])&&!empty($supplier_buyer[1]) ? 'checked="checked"' : '';
    $inbuyername = isset($supplier_buyer[1])&&!empty($supplier_buyer[1]) ? $supplier_buyer[1]['buyer'] : '';
    $hwcbuyer = isset($supplier_buyer[2])&&!empty($supplier_buyer[2]) ? 'checked="checked"' : '';
    $hwcbuyername = isset($supplier_buyer[2])&&!empty($supplier_buyer[2]) ? $supplier_buyer[2]['buyer'] : '';
    $FBAbuyer = isset($supplier_buyer[3])&&!empty($supplier_buyer[3]) ? 'checked="checked"' : '';
    $FBAbuyername = isset($supplier_buyer[3])&&!empty($supplier_buyer[3]) ? $supplier_buyer[3]['buyer']: '';
    $model_buyer = new \app\models\SupplierBuyer();
    $inbuyerhtml = $form->field($model_buyer, 'buyer[]')->widget(Select2::classname(), [
    'options' => ['placeholder' => '请输入采购员 ...','id'=>'IN','value'=>$inbuyername],
    'data' =>BaseServices::getEveryOne('','name'),
    'pluginOptions' => [
    'language' => [
    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),],
    ],])->label('');
    $hwcbuyerhtml = $form->field($model_buyer, 'buyer[]')->widget(Select2::classname(), [
    'options' => ['placeholder' => '请输入采购员 ...','id'=>'HWC','value'=>$hwcbuyername],
    'data' =>BaseServices::getEveryOne('','name'),
    'pluginOptions' => [
    'language' => [
    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),],
    ],])->label('') ;
    $FBAbuyerhtml =$form->field($model_buyer, 'buyer[]')->widget(Select2::classname(), [
    'options' => ['placeholder' => '请输入采购员 ...','id'=>'FBA','value'=>$FBAbuyername],
    'data' =>BaseServices::getEveryOne('','name'),
    'pluginOptions' => [
    'language' => [
    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),],
    ],])->label('');
   ?>
    <table class="table table-hover ">
        <thead>
        <tr>
            <th></th>
            <th>部门</th>
            <th>采购员</th>
        </tr>
        </thead>
        <tbody class="buyer">
        <tr class="buyer_list ">
            <td><input type="checkbox" name="SupplierBuyer[type][]" value=1 <?= $inbuyer?> ></td>
            <td>国内仓</td>
            <td><?=$inbuyerhtml?>
            </td>
        </tr>
        <tr class="buyer_list ">
            <td><input type="checkbox" name="SupplierBuyer[type][]" value=2 <?= $hwcbuyer?> ></td>
            <td>海外仓</td>
            <td><?= $hwcbuyerhtml?>
            </td>
        </tr>
        <tr class="buyer_list ">
            <td><input type="checkbox"  class="FBABuyer" name="SupplierBuyer[type][]" value=3 <?= $FBAbuyer?> ></td>
            <td>FBA</td>
            <td><?=$FBAbuyerhtml?>
            </td>
        </tr>
        </tbody>
    </table>
    <div>
        <?= \yii\helpers\Html::button('上一步',['class'=>'btn btn-info','id'=>'supplier_buyer_info_up'])?>
        <?= \yii\helpers\Html::button('下一步',['class'=>'btn btn-warning','id'=>'supplier_buyer_info_next'])?>
    </div>
<?php
$js = <<<JS
    $('#supplier_buyer_info_up').on('click',function() {
        $('#contact_info_li').tab('show');
        $('#contact_info_content').addClass('active in');
        $('#buyer_info_content').removeClass('active in');
    });
    $('#supplier_buyer_info_next').on('click',function() {
        var str = '';
        $('[name="SupplierBuyer[type][]"]').each(function(){
            if($(this).is(':checked')){
                str+=$(this).val();
            }
        });
        if(str==''){
            layer.msg('必须选择一个部门');
            return false;
        }
        $('#img_info_li').tab('show');
        $('#img_info_content').addClass('active in');
        $('#buyer_info_content').removeClass('active in');
    });
  
JS;
$this->registerJs($js);
?>