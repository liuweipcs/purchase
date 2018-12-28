<?php

use yii\helpers\Html;
use app\config\Vhelper;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Modal;
use app\models\PurchaseHistory;
use app\services\PurchaseOrderServices;
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */

$this->title = '采购单产品';
$this->params['breadcrumbs'][] = ['label' => 'Purchase Suggests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="purchase-suggest-create">
        <div class="purchase-suggest-form">
            <?= Html::beginForm(['overseas-purchase-suggest/create-purchase'], 'post', ['enctype' => 'multipart/form-data']) ?>
            <?= Html::input('hidden', 'flag', 1)?>
            <table class="table table-hover">
                <tr>
                    <td>
                        采购仓库：<?="{$data[0]['warehouse_name']}"?>
                        <?= Html::input('hidden', 'PurchaseOrder[warehouse_name]', "{$data[0]['warehouse_name']}")?>
                        <?= Html::input('hidden', 'PurchaseOrder[warehouse_code]', "{$data[0]['warehouse_code']}")?>
                    </td>
                    <td>
                        供应商：<?="{$data[0]['supplier_name']}"?>
                        <?= Html::input('hidden', 'PurchaseOrder[supplier_name]', "{$data[0]['supplier_name']}")?>
                        <?= Html::input('hidden', 'PurchaseOrder[supplier_code]', "{$data[0]['supplier_code']}")?>
                        <?= Html::input('hidden', 'PurchaseOrder[buyer]', "{$data[0]['buyer']}")?>
                        <?= Html::input('hidden', 'PurchaseOrder[pay_type]', "{$data[0]['payment_method']}")?>
                        <?= Html::input('hidden', 'PurchaseOrder[account_type]', "{$data[0]['supplier_settlement']}")?>
                    </td>
                    <td>中转仓：<?= Html::dropDownList('PurchaseOrder[transit_warehouse]','',['shzz'=>'宁波中转仓库','AFN'=>'东莞中转仓库'],['prompt'=>'请选择','required'=>true])?></td>
                    <td><span style="color: red">加急采购单：</span><?= Html::dropDownList('PurchaseOrder[is_expedited]','', ['1'=>'不加急','2'=>'加急'])?></td>
                </tr>
                <tr>
                    <td>补货类型：<?=Html::dropDownList('PurchaseOrder[pur_type]', "{$data[0]['replenish_type']}", Yii::$app->params['pur_type'])?></td>
                    <?php $data[0]['currency']='RMB'?>
                    <td>跟单员： <?=Html::dropDownList('PurchaseOrder[merchandiser]',Yii::$app->user->identity->username,  ArrayHelper::map($users, 'username', 'username'),['value' =>Yii::$app->user->identity->username,'required'=>true])?></td>
                    <td>币&nbsp;&nbsp; 种：<?=Html::dropDownList('PurchaseOrder[currency_code]', "{$data[0]['currency']}", Yii::$app->params['currency_code'],['prompt' => 'Choose','required'=>true])?></td>
                    <td>运输方式： <?=Html::dropDownList('PurchaseOrder[shipping_method]', "{$data[0]['ship_method']}",  Yii::$app->params['shipping_method'])?></td>
                </tr>

            </table>
            <table class="table table-hover ">
                <tr>
                    <th>需求排除</th>
                    <th>图片</th>
                    <th>SKU</th>
                    <th>名称</th>
                    <th>一箱</th>
                    <th>数量</th>
                    <th>单价</th>
                    <th>中转数</th>
                    <th>需求单号</th>
                    <th>海外运输方式</th>
                    <th>平台</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($data as $key=>$val):?>
                    
                    <tr class="pay_list ">
                        <td><?= Html::input('checkbox',"PurchaseOrder[items][{$val['id']}][is_purchase]",'0',['checked'=>false])?></td>
                        <td><?=!empty($val['product_img'])?Vhelper::toSkuImg($val['sku'],$val['product_img']):'';?></td>
                        <td>
                            <?=$val['sku'].(!empty(\app\models\Product::findOne(['product_is_new'=>1,'sku'=>$val['sku']])->product_is_new)?'<sub><font size="1" color="red">新</font></sub>':'').Html::a('',['product/viewskusales', 'sku' => $val['sku'],'warehouse_code'=>$data[0]['warehouse_code']], ['class' => "glyphicon glyphicon-signal b", 'style'=>'margin-right:5px;', 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal',]) ?>
                            <a href='<?=\app\models\SupplierQuotes::getUrl($val['sku'])?>' title='' target='_blank'>
                                <i class='fa fa-fw fa-internet-explorer'></i>
                            </a>
                        </td>

                        <td><?=$val['name'] ?></td>

                        <td><?=\app\models\BoxSkuQty::getBoxQty($val['sku'])?></td>
                        <?php  $price = \app\models\Product::find()->select('pur_supplier_quotes.supplierprice')->where(['pur_product.sku'=>$val['sku']])->joinWith('supplierQuote')->scalar();?>
                        <td>
                            <?= Html::input('number', "PurchaseOrder[items][{$val['id']}][qty]", $val['qty'], ['min'=>1,'required'=>true])?>
                            <?= Html::input('hidden', "PurchaseOrder[items][{$val['id']}][sku]",$val['sku'])?>
                            <?= Html::input('hidden', "PurchaseOrder[items][{$val['id']}][name]",$val['name'])?>
                            <?= Html::input('hidden', "PurchaseOrder[items][{$val['id']}][price]",$price)?>
                            <?= Html::input('hidden', "PurchaseOrder[items][{$val['id']}][demand_number]",$val['demand_number'])?>
                        </td>
                        <td><?=$price ?></td>
                        <td><?= \app\models\PlatformSummary::getField($val['demand_number'],'transit_number')->transit_number ?></td>
                        <td><?=$val['demand_number']?></td>
                        <td><?=PurchaseOrderServices::getTransport(\app\models\PlatformSummary::getField($val['demand_number'],'transport_style')->transport_style)?></td>

                        <td><?=\app\models\PlatformSummary::getField($val['demand_number'],'platform_number')->platform_number?></td>
                        <td><?= Html::a('<i class="fa fa-fw fa-close"></i>采购驳回', ['purchase-disagree','demand_number'=>$val['demand_number'],'id'=>$val['id']], [
                                'title'       => Yii::t('app', '采购驳回'),
                                'class'       => 'btn btn-xs pdisagree',
                                'data-toggle' => 'modal',
                                'data-target' => '#created-modal',
                            ]);?></td>
                    </tr>
                <?php endforeach;?>
            </table>
            <div class="col-md-12">采购计划说明：<?= Html::textarea('PurchaseNote[note]', '',['rows'=>3,'cols'=>10,'required'=>true,'placeholder' => '比如说是阿里支付单号,这个给财务能看到','style'=>"margin: 0px; width: 840px; height: 98px;"])?></div>
            <p style="text-align:right"><?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?></p>
            <?= Html::endForm() ?>
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
$("#created-modal").on("hidden.bs.modal",function(){
    $(document.body).addClass("modal-open");
});
$(document).on('click', '.pdisagree', function () {

        $.get($(this).attr('href'), {},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);
            }
        );
    });


JS;
$this->registerJs($js);
?>