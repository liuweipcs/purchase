<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\SkuSalesStatistics;
use app\models\Stock;
use app\services\SupplierGoodsServices;
use app\config\Vhelper;

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
                    <tr class="pay_list" style="width: 10%">
                        <td><?=Vhelper::toSkuImg($v->sku,$v->product_img)?></td>
                        <td><?=Yii::t('app','产品代码:')?><?=$v->sku?><br/>
                            <?=Yii::t('app','产品名称:')?><?=$v->name?><br/>
<!--                            --><?php //Yii::t('app','供应商品号:')?><!--<br/>-->
                            <?=Yii::t('app','销售状态:')?><?=SupplierGoodsServices::getProductStatus($v->sales_status)?><br/>

                        </td>
                        <td><?=$v->qty?></td>
                        <td><?=$v->ctq?></td>
                        <td><?=$v->price?></td>
                        <td style="width: 20%">
                            <?php /*$skusales= \app\models\SupplierQuotes::getQuotes($v->sku)*/?><!--
                            最高采购价：<?/*=$skusales['max']*/?>  <br/>
                            最低采购价：<?/*=$skusales['mix']*/?>  <br/>
                            平均采购价：<?/*=$skusales['avg']*/?>  <br/>
                            总采购价：  <?/*=$skusales['sum']*/?>  <br/>
                            采购次数：  <?/*=$skusales['count']*/?> <br/>-->
                            <?=Html::a('查看历史报价', ['#'],[
                            'data-toggle' => 'modal',
                            'data-target' => '#created-modal',
                                'code' => $codes,
                                'class'=>'data-updates',
                                'sku'  => $v->sku,
                            ])?>
                        </td>
                        <td><?=$v->items_totalprice?></td>
                        <td style="width: 15%">
                            <?php $SkuSales=Stock::getStock($v->sku,$code)?>
                            <?=Yii::t('app','实际数量：')?><?=!empty($SkuSales->stock)?$SkuSales->stock:''?><br/>
                            <?=Yii::t('app','在途数量：')?><?=!empty($SkuSales->on_way_stock)?$SkuSales->on_way_stock:''?><br/>
                            <?=Yii::t('app','可用数量：')?><?=!empty($SkuSales->available_stock)?$SkuSales->available_stock:''?><br/>
                            <?=Yii::t('app','欠货数量：')?><?=!empty($SkuSales->left_stock)?$SkuSales->left_stock:''?><br/>
                        </td>
                        <td style="width: 10%">
                            <?php $SkuSales=SkuSalesStatistics::getStatistics($v->sku)?>
                            <?=Yii::t('app','3天:')?> <?=!empty($SkuSales->days_sales_3)?$SkuSales->days_sales_3:''?><br/>
                            <?=Yii::t('app','7天:')?> <?=!empty($SkuSales->days_sales_7)?$SkuSales->days_sales_7:''?><br/>
                            <?=Yii::t('app','15天:')?><?=!empty($SkuSales->days_sales_15)?$SkuSales->days_sales_15:''?><br/>
                            <?=Yii::t('app','30天:')?><?=!empty($SkuSales->days_sales_30)?$SkuSales->days_sales_30:''?><br/>
                            <?=Yii::t('app','60天:')?><?=!empty($SkuSales->days_sales_60)?$SkuSales->days_sales_60:''?><br/>
                            <?=Yii::t('app','90天:')?><?=!empty($SkuSales->days_sales_90)?$SkuSales->days_sales_90:''?><br/>

                        </td>
                    </tr>
                <?php }?>
                </tbody>

            </table>
</div>
<?php
Modal::begin([
    'id' => 'created-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    //'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗
        'z-index' =>'-1',

    ],
]);
Modal::end();
$historys         = Url::toRoute(['tong-tool-purchase/get-history']);
$js = <<<JS

    function modal_close(data)
    {
        $('#created-modal').modal("hide");


    }
$(document).on('click','.data-updates', function () {

        $.get('{$historys}', {sku:$(this).attr('sku')},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);

            }
        );
    });


JS;
$this->registerJs($js);
?>