<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Modal;
use app\models\PurchaseHistory;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */

$this->title = 'FBA采购单产品';
$this->params['breadcrumbs'][] = ['label' => 'Purchase Suggests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<h3>生成采购单</h3>
<div class="purchase-suggest-create">
    <div class="purchase-suggest-form">
        <?php $form = ActiveForm::begin([]
        ); ?>
        <div class="col-md-2"><?= $form->field($model_tax, 'is_taxes')->dropDownList(['1'=>'含税','2'=>'不含税']) ?></div>
        <div class="col-md-2"><?= $form->field($model_tax, 'taxes')->textInput() ?></div>
        <div class="col-md-2"><?= $form->field($model, 'is_expedited')->dropDownList(['1'=>'不加急','2'=>'加急']) ?></div>
        <div class="col-md-12">采购计划说明：<?= Html::textarea('PurchaseNote[note]', '',['rows'=>3,'cols'=>10,'required'=>true,'placeholder' => '比如说是阿里支付单号,这个给财务能看到','style'=>"margin: 0px; width: 840px; height: 98px;"])?></div>
        <table class="table table-hover ">
            <tr>
                <th>图片</th>
                <th>SKU</th>
                <th>采购连接</th>
                <th>名称</th>
                <th>数量</th>
                <th>单价</th>
               <!-- <th>补货类型</th>
                <th>运输方式</th>-->
            </tr>
            <?php foreach ($data as $key=>$val)
            {
                $img=Vhelper::toSkuImg($val['sku'],$model_Items->product_img);
                ?>
                <tr class="pay_list">
                   <td><?=Html::a($img,['purchase-suggest/img', 'sku' => $val['sku'],'img' => $model_Items->product_img], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal'])?></td>
                    <td><?=$val['sku'].Html::a('',['product/viewskusales', 'sku' => $val['sku'],'warehouse_code'=>$model->warehouse_code], ['class' => "glyphicon glyphicon-signal b", 'style'=>'margin-right:5px;', 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal',]) ?></td>
                    <td><a href='<?=PurchaseHistory::getPurchaseLink($val['sku']) ?>' title='' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a></td>
                    <td><?=$val['product_name'] ?></td>
                    <td>
                        <?= Html::input('number', "PurchaseOrder[items][{$val['id']}][purchase_quantity]", $val['purchase_quantity'], ['min'=>1,'required'=>true])?>
                        <?= Html::input('hidden', "PurchaseOrder[items][{$val['id']}][sku]",$val['sku'])?>
                        <?= Html::input('hidden', "PurchaseOrder[items][{$val['id']}][product_name]",$val['product_name'])?>
                        <?= Html::input('hidden', "PurchaseOrder[purchase_warehouse]",$val['purchase_warehouse'])?>
                        <?= Html::input('hidden', "PurchaseOrder[is_transit]",$val['is_transit'])?>
                        <?= Html::input('hidden', "PurchaseOrder[transit_warehouse]",$val['transit_warehouse'])?>
                        <?= Html::input('hidden', "PurchaseOrder[transit_warehouse]",$val['transit_warehouse'])?>
                    </td>
                    <td><?=PurchaseHistory::getField($val['sku'],'purchase_price') ?></td>
                   <!-- <td><?/*=Yii::$app->params['pur_type'][$val['level_audit_status']]*/?></td>
                    <td><?/*=Yii::$app->params['shipping_method'][$val['level_audit_status']] */?></td>-->
                </tr>

            <?php }?>
        </table>

        <?= Html::submitButton($model->isNewRecord ? '提交' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?php ActiveForm::end(); ?>
    </div>
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
?>
<?php
$js = <<<JS
    $(document).on('click', '.b', function () {

        $.get($(this).attr('href'), {sku:$(".sku").attr('sku')},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.img', function () {

        $.get($(this).attr('href'), {},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
JS;
$this->registerJs($js);
?>
