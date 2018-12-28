<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;


$this->title = '收货异常处理';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= Html::beginForm(['handle-save'], 'post', ['enctype' => 'multipart/form-data']) ?>
<table class='table table-hover table-bordered table-striped' >
    <tr>
        <td>快递单号 : 
            <?=Html::input('text', 'PurchaseOrderShip[express_no]',$data['express_no'],['required'=>true,'readonly'=>true])?>
            <?=Html::input('hidden', 'id', $data['id'])?>
        </td>
        <td>采购单 : <?=Html::input('text', 'PurchaseOrderShip[pur_number]','',['required'=>true])?></td>
    </tr>
    <tr>
        <td>快递公司 : <?= Html::dropDownList('PurchaseOrderShip[cargo_company_id]', '', \app\services\BaseServices::getLogisticsCarrier(),['prompt'=>'Choose','required'=>true]) ?></td>
        <td> 运 &nbsp;&nbsp;&nbsp;&nbsp;费 : <?=Html::input('text', 'PurchaseOrderShip[freight]','',['min'=>0])?></td>
    </tr>
    <tr>
        <td>支付单号 : <?=Html::input('text', 'PurchaseOrderShip[pay_number]','',[])?></td>
        <td> 备 &nbsp;&nbsp;&nbsp;&nbsp;注 : <?=Html::textarea('PurchaseOrderShip[note]')?></td>
    </tr>
    <tr>
        <td>仓库异常图片:
            <?php if($data['img']){
                $img =json_decode($data['img']);
                foreach($img as $v){

                    ?>
                    <a href="<?php echo $v?>" target="_blank"><img src="<?php echo $v?>" width="50px"></a>
                <?php }}?>
        </td>
    </tr>
</table>
<p style="text-align:right"><?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?></p>
<?= Html::endForm() ?>