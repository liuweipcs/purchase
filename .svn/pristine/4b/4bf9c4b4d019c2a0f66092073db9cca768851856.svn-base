<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use  app\services\SupplierGoodsServices;

/* @var $this yii\web\View */
/* @var $model app\models\Stockin */

?>

    <div class="stockin-update">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th><?=Yii::t('app','图片')?></th>
                <th><?=Yii::t('app','产品代码')?></th>
                <th><?=Yii::t('app','预期数量')?></th>
                <th><?=Yii::t('app','确认数量')?></th>
                <th><?=Yii::t('app','单价(RMB)')?></th>
                <th><?=Yii::t('app','报价情况(RMB)')?></th>
                <th><?=Yii::t('app','总金额(RMB)')?></th>
                <th><?=Yii::t('app','库存')?></th>
                <th><?=Yii::t('app','日均销量')?></th>
            </tr>
            </thead>
            <tbody class="pay">
            <?php foreach($purchaseOrderItems as $v){?>
                <tr class="pay_list">
                    <td><?=Html::img(Yii::$app->request->hostInfo . '/images/timg.jpg', ['alt' => '产品图片','width'=>'110px','class'=>"img-rounded"])?></td>
                    <td><?=Yii::t('app','产品代码:')?><?=$v->sku?><br/>
                        <?=Yii::t('app','产品名称:')?><?=$v->name?><br/>
                        <?php Yii::t('app','供应商品号:')?><br/>
                        <?=Yii::t('app','销售状态:')?><?=SupplierGoodsServices::getProductStatus($v->sales_status)?><br/>

                    </td>
                    <td><?=$v->qty?></td>
                    <td><?=$v->ctq?></td>
                    <td><?=$v->price?></td>
                    <td>目标采购价：500.000<br/>
                        上次采购价：500.00  采购量：1<br/>
                        二次采购价：500.00  采购量：100<br/>
                        最低采购价：500.00  采购量：1<br/>
                        <?=Html::a('查看历史报价', ['#'],[
                            'data-toggle' => 'modal',
                            'data-target' => '#creates-modal',
                        ])?>
                    </td>
                    <td><?=$v->items_totalprice?></td>
                    <td>在途：100<br/>
                        待上架：0<br/>
                        可售：0<br/>
                        缺货：0<br/>
                        可售天数：--<br/>
                        预计可售天数：--</td>
                    <td>
                        13天：0<br/>
                        7天：0<br/>
                        14天：0<br/>
                        30天：0<br/>
                        系统日均：0<br/>
                    </td>
                </tr>
            <?php }?>
            </tbody>

        </table>
    </div>
<?php
Modal::begin([
    'id' => 'creates-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary" onclick="modal_close(this)" >Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
        'z-index' =>'-1',

    ],
]);
Modal::end();
$js = <<<JS

    function modal_close(data)
    {
        $('#creates-modal').modal("hide");
    }



JS;
$this->registerJs($js);
?>