<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Modal;
use app\models\PurchaseHistory;
use app\config\Vhelper;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */

$this->title = '采购单产品';
$this->params['breadcrumbs'][] = ['label' => 'Purchase Suggests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-suggest-create">
    <div class="purchase-suggest-form">
        <?= Html::beginForm(['purchase-suggest-mrp/create-purchase'], 'post', ['enctype' => 'multipart/form-data','id'=>'create-purchase-submit-form']) ?>
        <?= Html::input('hidden', 'flag', isset($flag)?$flag:0)?>
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
                <td>参考号：<?= Html::input('text', 'PurchaseOrder[reference]', '')?></td>
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
        <div class="col-md-12">采购计划说明：<?= Html::textarea('PurchaseNote[note]', '',['rows'=>3,'cols'=>10,'required'=>true,'placeholder' => '比如说是阿里支付单号,这个给财务能看到','style'=>"margin: 0px; width: 840px; height: 98px;"])?></div>

        <div style="height: 350px; padding: 10px; margin-bottom: 10px; overflow-y: scroll; overflow-x: hidden">
        <table class="table table-hover ">
            <tr>
                <th>图片</th>
                <th>SKU</th>
                <th>采购连接</th>
                <th>名称</th>
                <th>数量</th>
                <th>单价</th>
            </tr>
            <?php foreach ($data as $key=>$val){

                $img=Html::img(Vhelper::downloadImg($val['sku'],'',2));
                $defaultPrice = \app\models\ProductProvider::find()
                    ->select('q.supplierprice')
                    ->alias('t')
                    ->leftJoin(\app\models\SupplierQuotes::tableName().'q','t.quotes_id=q.id')
                    ->where(['t.sku'=>$val['sku'],'t.is_supplier'=>1])->scalar();
                $price = empty($defaultPrice) ? $val['price'] : $defaultPrice;
                ?>
                <tr class="pay_list">
                    <td><?=Html::a($img,['img', 'sku' => $val['sku'],'img' => $val['product_img']], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal'])?></td>
                    <td><?=$val['sku'].Html::a('',['product/viewskusales', 'sku' => $val['sku'],'warehouse_code'=>$data[0]['warehouse_code']], ['class' => "glyphicon glyphicon-signal b", 'style'=>'margin-right:5px;', 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal',]) ?></td>
                    <td><a href='<?=PurchaseHistory::getPurchaseLink($val['sku']) ?>' title='' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a></td>
                    <td><?=$val['name'] ?></td>
                    <td>
                        <?= Html::input('number', "PurchaseOrder[items][{$val['id']}][qty]", $val['qty'], ['min'=>1,'required'=>true])?>
                        <?= Html::input('hidden', "PurchaseOrder[items][{$val['id']}][sku]",$val['sku'])?>
                        <?= Html::input('hidden', "PurchaseOrder[items][{$val['id']}][name]",$val['name'])?>
                        <?= Html::input('hidden', "PurchaseOrder[items][{$val['id']}][price]",$price)?>
                    </td>
                    <td><?=$price ?></td>
                </tr>

            <?php }?>
        </table>
        </div>

        <p style="text-align:right"><?= Html::submitButton('提交', ['class' => 'btn btn-primary create_purchase_order']) ?></p>
        <?= Html::endForm() ?>
    </div>
</div>

<?php
Modal::begin([
    'id' => 'created-modal',
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
    $(function() {
      $('.create_purchase_order').click(function() {
          // $('#create-purchase-submit-form').submit();
          // $(this).prop('disabled',true);
      });
    });
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
