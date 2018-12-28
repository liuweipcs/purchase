<div style="margin-bottom: 20px">
    <div class="glyphicon glyphicon-yen">我方富友账户信息</div>
    <?php
        $selfPayName = \app\models\DataControlConfig::find()->select('values')->where(['type'=>'self_pay_name'])->scalar();
        $self_pay_name_array = $selfPayName ? explode(',',$selfPayName) : ['合同运费','合同运费走私账'];
        if(!empty($model)){
            foreach ($model as $payData){
                $account_type_array[]=in_array($payData->pay_name,$self_pay_name_array) ? 2 : ($is_drawback==1 ? 2 : 1);
            }
        }
        if(count(array_unique($account_type_array))!=1){
            Yii::$app->end('当前选择的付款数据无法准确获取银行卡信息无法支付');
        }
        $account_type = $account_type_array[0];//退税走公账不退税走私账
        $supplierBankInfo = \app\models\SupplierPaymentAccount::find()
                            ->where(['supplier_code'=>$supplier_code])
                            ->andWhere(['payment_platform'=>6])
                            ->andWhere(['account_type'=>$account_type])
                            ->one();//供应商对应银行卡信息
        $bankCardTp = $account_type==2 ? '01':'02';
    ?>
    <?= \yii\helpers\Html::dropDownList('Fuiou[PayAccount]','pay_001',\app\models\UfxFuiou::getPayAccount('array'),['class'=>'form-control','prompt'=>'请选择','required'=>true])?>

</div>
<div style="margin-bottom: 20px">
    <table>
        <th scope="row"><?=Yii::t('app','账号简称')?></th>
        <th scope="row"><?= \yii\helpers\Html::dropDownList('Fuiou[PayInfo]',!empty($bank->id)?$bank->id:'',\app\services\BaseServices::getBankCard(null,'account_abbreviation'),['class'=>'form-control bank','value'=>!empty($bank) ? $bank->id : '']) ?></th>
        <th scope="row"><?=Yii::t('app','支行:')?></th>
        <td><input type="text" class="form-control banks"  value="<?= !empty($bank->branch)?$bank->branch:''?>"  readonly></td>

        <th scope="row"><?=Yii::t('app','开户账号:')?></th>
        <td><input type="text" class="form-control account"  value="<?= !empty($bank->account_number)?$bank->account_number:''?>"  readonly></td>
        <th scope="row"><?=Yii::t('app','开户人:')?></th>
        <td><input type="text" class="form-control holder"  value="<?= !empty($bank->account_holder)?$bank->account_holder:''?>"  readonly></td>
    </table>
</div>
<div class="glyphicon glyphicon-yen">收款方账户信息</div>

<?php if(empty($supplierBankInfo)){?>
    <?= '<span style="text-align: center;color: red">该供应商没有可用的富友收款账号</span>';exit();?>
    <table class="table">
        <tr>
            <td>卡类型</td>
            <td>
                <?= \yii\helpers\Html::dropDownList('Fuiou[bankCardTp]','',['01'=>'对私','02'=>'对公'],['class'=>'form-control','prompt'=>'请选择','required'=>true])?>
            </td>
            <td>户名</td>
            <td><?= \yii\helpers\Html::input('text','Fuiou[oppositeName]','',['class'=>'form-control','required'=>'required'])?></td>
            <td>证件号码</td>
            <td colspan="2"><?= \yii\helpers\Html::input('text','Fuiou[oppositeIdNo]','',['class'=>'form-control','required'=>'required'])?></td>
        </tr>
        <tr>
            <td>发卡主行</td>
            <td>
                <?= \yii\helpers\Html::dropDownList('Fuiou[bankNo]','' ,\app\models\UfxFuiou::getMasterBankInfo(),['class'=>'form-control masterBank','prompt'=>'请选择','required'=>true])?>
            </td>
            <td>发卡支行</td>
            <td>
                <select class="form-control branchBank" name="Fuiou[bankId]"  required='required'>
                    <option value="">请选择</option>
                </select>
            </td>
            <td>开户行所在地</td>
            <td>
                <?= \yii\helpers\Html::hiddenInput('Fuiou[provNo]','',['class'=>'form-control'])?>
                <?= \yii\helpers\Html::input('text','Fuiou[provName]','',['class'=>'form-control','required'=>'required','readonly'=>'readonly'])?>
            </td>
            <td>
                <?= \yii\helpers\Html::hiddenInput('Fuiou[cityNo]','',['class'=>'form-control'])?>
                <?= \yii\helpers\Html::input('text','Fuiou[cityName]','',['class'=>'form-control','required'=>'required','readonly'=>'readonly'])?>
            </td>


        </tr>
        <tr>
            <td>卡号</td>
            <td>
                <?= \yii\helpers\Html::input('text','Fuiou[bankCardNo]','',['class'=>'form-control','required'=>'required'])?>
            </td>
            <td>转账金额(单位：元)(不含手续费)</td>
            <td >
                <?= \yii\helpers\Html::input('text','Fuiou[amt]',round($totalPrice/1000,2),['class'=>'form-control','required'=>'required','readonly'=>'readonly'])?>
            </td>

        </tr>
        <tr>
            <td>到账通知</td>
            <td>
                <?= \yii\helpers\Html::radioList('Fuiou[isNotify]','01',['01'=>'是','02'=>'否'])?>
            </td>
            <td>手机号码</td>
            <td colspan="2">
                <?= \yii\helpers\Html::input('text','Fuiou[oppositeMobile]','',['class'=>'form-control'])?>
            </td>
        </tr>
        <tr>
            <td>备注</td>
            <td colspan="3">
                <?= \yii\helpers\Html::textarea('Fuiou[remark]','',['rows'=>"5", 'cols'=>"75"])?>
            </td>
        </tr>
    </table>
<?php }else{ ?>
    <?php
        $exist = \app\models\UfxfuiouPayDetail::find()->where(['payee_card_number'=>$supplierBankInfo?$supplierBankInfo->account:''])
            ->andWhere(['pay_status'=>'5005','payee_user_name'=>$supplierBankInfo?$supplierBankInfo->account_name : ''])
            ->exists();
    ?>
<table class="table">
    <tr>
        <td>卡类型</td>
        <td>
            <?= \yii\helpers\Html::hiddenInput('Fuiou[bankCardTp]',$bankCardTp)?>
            <?= \yii\helpers\Html::textInput('',$bankCardTp=='01'?'对私':'对公',['readonly'=>'readonly','class'=>'form-control']); ?>
        </td>
        <td>户名</td>
        <td><?= \yii\helpers\Html::input('text','Fuiou[oppositeName]',$supplierBankInfo?$supplierBankInfo->account_name : '',['class'=>'form-control','readonly'=>'readonly','required'=>'required'])?></td>
        <td>证件号码</td>
        <td colspan="2"><?= \yii\helpers\Html::input('text','Fuiou[oppositeIdNo]',$supplierBankInfo?$supplierBankInfo->id_number : '',['class'=>'form-control','readonly'=>'readonly','required'=>'required'])?></td>
    </tr>
    <tr>
        <td>发卡主行</td>
        <td>
            <?= \yii\helpers\Html::hiddenInput('Fuiou[bankNo]',$supplierBankInfo?$supplierBankInfo->payment_platform_bank:'')?>
            <?= \yii\helpers\Html::dropDownList('Fuiou[bankNo]',$supplierBankInfo?$supplierBankInfo->payment_platform_bank:'' ,\app\models\UfxFuiou::getMasterBankInfo(),['class'=>'form-control masterBank','disabled'=>true,'prompt'=>'请选择','required'=>true])?>
        </td>
        <td>发卡支行</td>
        <td>
            <?= \yii\helpers\Html::textInput('Fuiou[bankId]',$supplierBankInfo?$supplierBankInfo->payment_platform_branch:'',['class'=>'form-control','readonly'=>'readonly','required'=>'required'])?>
        </td>
        <td>开户行所在地</td>
        <td>
            <?= \yii\helpers\Html::hiddenInput('Fuiou[provNo]',$supplierBankInfo?$supplierBankInfo->prov_code:'',['class'=>'form-control'])?>
            <?php
                $cityInfo = \app\models\BankCityInfo::find()->select('prov_name,city_name')
                    ->where(['city_code'=>$supplierBankInfo?$supplierBankInfo->city_code:null])
                    ->andWhere(['prov_code'=>$supplierBankInfo?$supplierBankInfo->prov_code:null])
                    ->one();
                $provName = empty($cityInfo) ? '': $cityInfo->prov_name;
                $cityName = empty($cityInfo) ? '': $cityInfo->city_name;
            ?>
            <?= \yii\helpers\Html::input('text','Fuiou[provName]',$provName,['class'=>'form-control','required'=>'required','readonly'=>'readonly'])?>
        </td>
        <td>
            <?= \yii\helpers\Html::hiddenInput('Fuiou[cityNo]',$supplierBankInfo?$supplierBankInfo->city_code:'',['class'=>'form-control'])?>
            <?= \yii\helpers\Html::input('text','Fuiou[cityName]',$cityName,['class'=>'form-control','required'=>'required','readonly'=>'readonly'])?>
        </td>


    </tr>
    <tr>
        <td style="color: <?= $exist ? 'green': 'red';?>">卡号</td>
        <td>
            <?= \yii\helpers\Html::input('text','Fuiou[bankCardNo]',$supplierBankInfo?$supplierBankInfo->account:'',['class'=>'form-control','required'=>'required','readonly'=>'readonly'])?>
        </td>
        <td>转账金额(单位：元)(不含手续费)</td>
        <td >
            <?= \yii\helpers\Html::input('text','Fuiou[amt]',round($totalPrice/1000,2),['class'=>'form-control','required'=>'required','readonly'=>'readonly'])?>
        </td>

    </tr>
    <tr>
        <td>到账通知</td>
        <td>
            <?= \yii\helpers\Html::radioList('Fuiou[isNotify]','01',['01'=>'是','02'=>'否'])?>
        </td>
        <td>手机号码</td>
        <td colspan="2">
            <?= \yii\helpers\Html::input('text','Fuiou[oppositeMobile]',$supplierBankInfo?$supplierBankInfo->phone_number:'',['class'=>'form-control'])?>
        </td>
    </tr>
    <tr>
        <td>备注</td>
        <td colspan="3">
            <?= \yii\helpers\Html::textarea('Fuiou[remark]','',['rows'=>"5", 'cols'=>"75"])?>
        </td>
    </tr>
</table>
<?php } ?>
<?php
$branchBankUrl = \yii\helpers\Url::toRoute('ufx-fuiou/get-branch-bank');
$branchBankCityUrl = \yii\helpers\Url::toRoute('ufx-fuiou/get-branch-bank-city');
$url = \yii\helpers\Url::toRoute('get-bank');
$js = <<<JS
//主行变更获取支行
    $(document).on('change','.masterBank',function() {
        var masterBankCode = $(this).val();
        $('.branchBank').html('<option value="">请选择</option>');
        $('[name="Fuiou[provName]"]').val('');
            $('[name="Fuiou[provNo]"]').val('');
            $('[name="Fuiou[cityNo]"]').val('');
            $('[name="Fuiou[cityName]"]').val('');
            var loading = layer.load(6 , {shade : [0.5 , '#BFE0FA']});
        $.get("{$branchBankUrl}",{masterBankCode:masterBankCode},function(data){
            layer.close(loading);
            $('.branchBank').html(data);
        });
    });
//支行变更获取所在地区
    $(document).on('change','.branchBank',function() {
            var unionBankCode = $(this).val();
            $('[name="Fuiou[provName]"]').val('');
            $('[name="Fuiou[provNo]"]').val('');
            $('[name="Fuiou[cityNo]"]').val('');
            $('[name="Fuiou[cityName]"]').val('');
            var loading = layer.load(6 , {shade : [0.5 , '#BFE0FA']});
            $.get("{$branchBankCityUrl}",{unionBankCode:unionBankCode},function(data){
                layer.close(loading);
                $('[name="Fuiou[provName]"]').val(data.provName);
            $('[name="Fuiou[provNo]"]').val(data.provNo);
            $('[name="Fuiou[cityNo]"]').val(data.cityNo);
            $('[name="Fuiou[cityName]"]').val(data.cityName);
            },'json');
        });
    //是否到账通知修改变更手机号是否必须属性
    $(document).on('change','[name="Fuiou[isNotify]"]',function() {
        $('[name="Fuiou[isNotify]"]').each(function() {
          if($(this).is(':checked')&&$(this).val()=='01'){
              $('[name="Fuiou[oppositeMobile]"]').prop('required',true);
          }
          if($(this).is(':checked')&&$(this).val()=='02'){
              $('[name="Fuiou[oppositeMobile]"]').prop('required',false);
          }
        });
    });
    
    //变更银行卡
    //切换我方银行账户信息
    $(".bank").change(function(){

       var id = $(this).val();
       $.ajax({
               url:'{$url}',
            type:'GET', //GET
            async:true,    //或false,是否异步
            data:{
                id:id,
            },
            timeout:5000,    //超时时间
            dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
            success:function(data,textStatus,jqXHR){

                $('.banks').val(data.branch);
                $('.account').val(data.account_number);
                $('.holder').val(data.account_holder);
            },
        });
    });

JS;

$this->registerJs($js);
?>