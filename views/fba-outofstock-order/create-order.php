<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
$this->title = '采购确认';
$this->params['breadcrumbs'][] = 'FBA采购';
$this->params['breadcrumbs'][] = 'FBA采购单';
$this->params['breadcrumbs'][] = $this->title;?>
<?php $form = ActiveForm::begin([
//        'id' => 'form-id',
//        'enableAjaxValidation' => true,
//        'validationUrl' => Url::toRoute(['validate-form']),
    ]
); ?>
<?= Html::hiddenInput('AmazonOrderId',$ids)?>
<table class="table">
    <thead>
        <th>sku</th>
        <th>缺货数量(亚马逊)</th>
        <th>缺货数量(所有平台)</th>
        <th>虚拟仓、东莞仓在途（可用）数量</th>
        <th>建议数量</th>
    </thead>
    <tbody>
    <?php foreach ($datas['purchaseItems'] as $sku=>$value){?>
        <tr>
            <td><?=$sku?></td>
            <td><?=$value['outofstock']?></td>
            <td><?=$value['alloutofstock']?></td>
            <td><?=$value['have_num']?></td>
            <td><?=$value['suggest_num']?></td>
        </tr>
    <?php }?>
    </tbody>

</table>
<div class="form-group">
    <?= Html::submitButton( Yii::t('app', '确认生成采购单'), ['class' =>  'btn btn-primary',]) ?>
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



	
	
JS;
$this->registerJs($js);
?>




