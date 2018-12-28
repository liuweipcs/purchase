<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\config\Vhelper;
use kartik\file\FileInput;
use kartik\datetime\DateTimePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use app\models\ProductTaxRate;

$this->title = '合同单付款';
$this->params['breadcrumbs'][] = '费用管理';
$this->params['breadcrumbs'][] = '应付';
$this->params['breadcrumbs'][] = '出纳付款';
$this->params['breadcrumbs'][] = $this->title;

$supplierName = !empty($model->purchaseCompact[0]->purchaseOrder->supplier_name)?$model->purchaseCompact[0]->purchaseOrder->supplier_name:null;
if (empty($supplierName)) $supplierName = BaseServices::getSupplierName($model->supplier_code);

?>
<style type="text/css">
    .tt {
        width: 200px;
        text-align: center;
        font-weight: bold;
    }
    .cc {
        color: red;
    }
</style>

<div class="my-box">
    <a class="btn btn-info" href="/purchase-compact/view?cpn={$model['pur_number']}" target="_blank">查看采购订单合同</a>
</div>
<div class="my-box">
    <table class="my-table">
        <tr>
            <th colspan="6">基本信息</th>
        </tr>
        <tr>
            <td><strong>结算对象类型</strong></td>
            <td><?= $supplierName ?></td>
            <td><strong>申请人</strong></td>
            <td><?= PurchaseOrderServices::getEveryOne($model->applicant); ?></td>
            <td><strong>申请时间</strong></td>
            <td><?= $model->application_time ?></td>
        </tr>
        <tr>
            <td><strong>结算比例</strong></td>
            <td><?= $model->js_ratio ?></td>
            <td><strong>付款人</strong></td>
            <td><?= !empty($model->payer) ? BaseServices::getEveryOne($model->payer) : ''; ?></td>
            <td><strong>付款时间</strong></td>
            <td><?= $model->payer_time ?></td>
        </tr>

        <?php if($model->pay_category == 10): ?>
            <tr>
                <td><strong>账号</strong></td>
                <td><?= $model->purchase_account ?></td>
                <td><strong>拍单号</strong></td>
                <td><?= $model->pai_number ?></td>
            </tr>
        <?php endif; ?>

        <tr>
            <td><strong>采购备注</strong></td>
            <td colspan="3"><?= $model->create_notice ?></td>
            <td><strong>是否退税</strong></td>
            <td><?php if($compact->is_drawback == 1) {
                    echo '<span class="label label-success">不退税</span>';
                }else{
                    echo '<span class="label label-info">退税</span>';
                } ?></td>
        </tr>
    </table>
</div>

<?= Html::beginForm([''], 'post', ['enctype' => 'multipart/form-data', 'id' => 'compact-payment']) ?>

<?php if($model->pay_category !== 10): ?>

<div class="my-box">
    <?= $this->render("//template/tpls/{$tplPath}", ['model' => $form, 'print' => true]); ?>
</div>

<?php endif; ?>

<input type="hidden" name="PayWater[pur_number]" value="<?= $model->pur_number ?>"> <!-- 合同号 -->
<input type="hidden" name="PayWater[supplier_code]" value="<?= $model->supplier_code ?>"> <!-- 结算对像(供应商code) -->
<input type="hidden" name="PayWater[original_currency]" value="<?= $model->currency ?>"> <!-- 币种 -->
<input type="hidden" name="PayWater[original_currency]" value="<?= $model->currency ?>"> <!-- 币种 -->
<input type="hidden" name="PayWater[beneficiary_payment_method]" value="<?= $model->pay_type ?>"> <!-- 收款方支付方式 -->

<?php if($model->pay_category !== 10): ?>

<input type="hidden" name="PayWater[beneficiary_branch]" value="<?= $form->payment_platform_branch ?>"> <!-- 收款方支行 -->
<input type="hidden" name="PayWater[beneficiary_account]" value="<?= $form->account ?>"> <!-- 收款方帐号 -->
<input type="hidden" name="PayWater[beneficiary_account_name]" value="<?= !empty($sk_bank) ? $sk_bank->account_name : ''; ?>"> <!-- 收款方开户名 -->

<?php endif; ?>

<div class="my-box">
    <table class="my-table">
        <tr>
            <th colspan="4">我司付款信息</th>
        </tr>
        <tr>
            <td>账号简称</td>
            <td>银行卡号</td>
            <td>支行</td>
            <td>开户人</td>
        </tr>
        <tr>
            <td>
                <select name="PayWater[bank_id]" class="form-control" id="account_abbreviation">
                    <?php foreach($bank as $bk): ?>
                        <option value="<?= $bk['id'] ?>"><?= $bk['account_abbreviation'] ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" id="our_account_abbreviation" name="PayWater[our_account_abbreviation]" value="<?= $bank[0]['account_abbreviation'] ?>">
            </td>
            <td><input type="text" class="form-control" id="account_number" name="PayWater[account_number]" value="<?= $bank[0]['account_number'] ?>" readonly></td>
            <td><input type="text" class="form-control" id="our_branch" name="PayWater[our_branch]" value="<?= $bank[0]['branch'] ?>" readonly></td>
            <td><input type="text" class="form-control" id="our_account_holder" name="PayWater[our_account_holder]" value="<?= $bank[0]['account_holder'] ?>" readonly></td>
        </tr>
    </table>
</div>


<div class="my-box">

    <table class="my-table">
        <tr>
            <th colspan="6">付款操作</th>
        </tr>

        <tr>
            <td class="tt">申请金额</td>
            <td>

                <?php
                $msg = $model->pay_name;
                if($compact->is_drawback == 1) {
                    switch ($model->pay_category) {
                        case 11:
                            $msg = "含运费<strong class='cc'>{$compact['freight']}</strong>";
                            break;
                        case 20:
                            $msg = "含运费<strong class='cc'>{$compact['freight']}</strong>";
                            break;
                    }
                }
                ?>

                <h3 class="cc"><?= $model->pay_price ?></h3>
                <p><?= $msg ?></p>

                <input type="hidden" name="PayWater[price]" value="<?= $model->pay_price ?>">

            </td>
        </tr>

        <tr>
            <td class="tt">实际付款金额</td>
            <td><input type="text" name="real_pay_price" class="form-control" value="<?= $model->pay_price ?>"></td>
        </tr>

        <tr>
            <td class="tt">实际付款时间</td>
            <td>
                <?= DateTimePicker::widget([
                     'name' => 'payer_time',
                     'value' => date('Y-m-d H:i:s', strtotime('-2 days')),
                     'options' => ['placeholder' => '请选择日期 ...'],
                     'pluginOptions' => [
                         'autoclose' => true,
                         'format' => 'yyyy-m-dd hh:ii:ss',
                         'todayHighlight' => true,
                     ]
                 ]); ?>
            </td>
        </tr>

        <tr>
            <td class="tt">付款回单</td>
            <td>
                <?= FileInput::widget([
                    'model' => $model,
                    'attribute' => 'images[]',
                    'options' => ['multiple' => true],
                    'pluginOptions' => [
                        'allowedFileExtensions' => ['jpg', 'jpeg', 'gif', 'png', 'bmp'],
                        'uploadUrl' => Url::toRoute(['/purchase-order-cashier-pay/upload-receipt']), // 异步上传的接口地址设置
                        'uploadAsync'  => true,
                        'minFileCount' => 1,     // 最少上传的文件个数限制
                        'maxFileCount' => 3,     // 最多上传的文件个数限制
                        'maxFileSize' => 2000,  // 限制图片最大200kB

                        'showCaption' => false,  // 不显示那个input框，没什么卵用
                        'browseClass' => 'btn btn-primary',
                        'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                        'browseLabel' => '选择付款回单',

                        // 图片上的操作选项
                        'fileActionSettings' => [
                            'showZoom'   => false,
                            'showUpload' => false,
                            'showRemove' => false,
                            'indicatorNew' => '等待上传...',
                            'indicatorSuccess' => '上传成功',
                        ],
                        'layoutTemplates' => ['progress' => ''], // 不显示进度条，没什么卵用
                    ],
                    'pluginEvents' => [
                        // 上传成功后的回调方法，需要的可查看data后再做具体操作，一般不需要设置
                        'fileuploaded' => 'function(event, data, previewId, index) {
                            $("#compact-payment").append(data.response.imgfile);
                        }',
                    ],
                ]);
                ?>
            </td>
        </tr>

        <tr>
            <td class="tt">付款备注</td>
            <td><textarea rows="3" name="payment_notice" class="form-control" placeholder="请输入备注"></textarea></td>
        </tr>
        <tr>
            <td class="tt">操作</td>
            <td>
                <input type="radio" name="status" value="5" checked> 付款
                <input type="radio" name="status" value="12"> 驳回
            </td>
        </tr>
    </table>

</div>

<div class="my-box">
    <input type="hidden" name="id" value="<?= $model->id ?>">
    <input type="hidden" name="source" value="1">
    <button class="btn btn-success" type="button" id="sub-btn">提交付款</button>
</div>

<?= Html::endForm() ?>

<?php
$url = Url::to(['get-bank']);
$js = <<<JS
$(function() {
    
    $('#account_abbreviation').change(function() {
        var id = $(this).val();
        $.ajax({
            url: '{$url}',
            type: 'GET',
            async: true,
            data: {
                id: id,
            },
            timeout: 5000, 
            dataType: 'json', 
            success: function(data) {
                
                $('#our_account_abbreviation').val(data.account_abbreviation);
                $('#account_number').val(data.account_number);
                $('#our_branch').val(data.branch);
                $('#our_account_holder').val(data.account_holder);
            }
        });
    });
    
    // var load = function() {
    //   var is_drawback = {$compact->is_drawback};
    //   if(is_drawback==2){
    //       $('#account_abbreviation').val(60);
    //       $('#account_abbreviation').trigger('change');
    //   }
    // }
    // load();
      
    
   $('#sub-btn').click(function() {
       var status = $('input:radio[name="status"]:checked').val();
       if(status == 12 && $('textarea[name="payment_notice"]').val() == '') {
           layer.alert('驳回操作，必须填写备注');
           return false;
       }
       $('#compact-payment').submit();
   });
   
});


JS;
$this->registerJs($js);
?>



