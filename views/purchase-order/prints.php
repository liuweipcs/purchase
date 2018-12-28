<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\config\Vhelper;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
    body {
        font: 12px/150% Arial, Helvetica, sans-serif, '宋体';
    }
    #print_content {
        background: none repeat scroll 0 0 white;
        margin-left: auto;
        margin-right: auto;
        position: relative;
        text-align: center;
        width: 19cm;
        padding: 0px;
        font-size: 13px;
    }

    #print_content table {
        border-collapse: collapse;
        border: none;
        width: 100%;
    }

    .list td, .list th {
        border: solid #000000 1px;
        height: 27px;
        text-align: center;
    }
    .print-header{
        border:0;
    }
    #supplier_content_tr td,#zy_tr td,#purchase_title td{
        border-top: 0px;
    }

</style>

<!--  -->

<div id="print_content">
    <div style="clear:both;">
        <!-- 采购方信息 -->
        <table class="list">
            <tr>
                <td colspan="2" style="font-size: 20px;font-weight: 900;"><?=Yii::t('app','深圳市易佰网络科技有限公司')?></td>
            </tr>
            <tr>
                <td colspan="2">Shenzhen YiBai Tech Co., Ltd</td>
            </tr>
            <tr>
                <td colspan="2">TEL:  0755-22941390
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    FAX:  0755-22941390</td>
            </tr>
            <tr>
                <td colspan="2"><?=Yii::t('app','采购员：')?><?=$model->buyer?>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?=Yii::t('app','联系电话：')?>0755-22941390</td>
            </tr>
            <tr>
                <td colspan="2"><?=Yii::t('app','地址：深圳市龙华新区龙华街道清祥路清湖科技园B栋2楼268室')?> </td>
            </tr>
            <tr>
                <td colspan="2" style="font-size: 20px;font-weight: 900;"><?=Yii::t('app','采 购 订 单')?></td>
            </tr>
        </table>
        <!-- 供方联系方式 -->
        <table class="list" id="supplier_content">
            <tr id="supplier_content_tr">
                <td><?=Yii::t('app','供方：')?><?=$model->supplier_name?>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?=Yii::t('app','电话：')?></td>
                <td><?=Yii::t('app','PO单号:')?>     <?=$model->pur_number?></td>
            </tr>
            <tr>
                <td><?=Yii::t('app','地址：')?>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?=Yii::t('app','联系人：')?> </td>
                <td><?=Yii::t('app','订购日期：')?><?=$model->created_at?></td>
            </tr>
        </table>
        <table class="list">
            <tr id="purchase_title">
                <td><?=Yii::t('app','序号')?></td>
                <td align=center>SKU</td>
                <td><?=Yii::t('app','厂家型号')?></td>
                <td><?=Yii::t('app','英文名称')?></td>
                <td><?=Yii::t('app','中文名称')?></td>
                <td><?=Yii::t('app','配件信息')?></td>
                <td><?=Yii::t('app','单位')?></td>
                <td><?=Yii::t('app','数量')?></td>
                <td><?=Yii::t('app','单价')?></td>
                <td><?=Yii::t('app','金额')?></td>
                <td><?=Yii::t('app','入仓国家')?></td>
                <td><?=Yii::t('app','交货日期')?></td>
                <td><?=Yii::t('app','备注')?></td>
            </tr>
            <?php
            $skucount =0.00;
            $pricecount =0.00;
            foreach($model->purchaseOrderItems as $k=>$v){
                $skucount +=$v->ctq;
                $pricecount +=$v->items_totalprice;
                ?>
            <tr>
                <td><?=$k?></td>
                <td align=center><?= Vhelper::toSkuImg($v['sku'],$v['product_img']);?><br/><?=$v->sku?></td>
                <td></td>
                <td></td>
                <td><?=$v->name?></td>
                <td></td>
                <td></td>
                <td><?=$v->ctq?></td>
                <td><?=$v->price?><?=$model->currency_code?></td>
                <td><?=$v->items_totalprice?><?=$model->currency_code?></td>
                <td>中国</td>
                <td>
                   <?=$model->date_eta?>
                </td>
                <td></td>
            </tr>
            <?php }?>
            <tr>
                <td colspan="13" style = "text-align: left;">
                    <?=Yii::t('app','SKU种类：')?><?=count($model->purchaseOrderItems)?>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?=Yii::t('app','SKU总数：')?><?=$skucount?>
                </td>
            </tr>
            <tr>
                <td colspan="4">合计金额人民币（大写）:<?=Vhelper::number2chinese($pricecount,true,false)?><?=!is_int($pricecount)?'整':''?></td>
                <td colspan="6">合计金额人民币（小写）： <?=$pricecount?>&nbsp;<?=$model->currency_code?></td>
                <td colspan="3">付款方式:<?=!empty($model->pay_type)?\app\services\SupplierServices::getDefaultPaymentMethod($model->pay_type):''?></td>
            </tr>
            <tr>
                <td colspan="13" align=left>运费:<?=$model->orderShip['freight']?>&nbsp;<?=$model->currency_code?></td>
            </tr>
        </table>
        <table class="list">
            <tr id = "zy_tr">
                <td>注意事项</td>
                <td style="text-align: left;">

                    <!-- 					1.供方收到订单后须在24小时内确认并盖章回传，否则视为默认；请认真审核订单需求，有疑问请及时与采购人员联系。 -->
                    <!-- 						</br>2.供方须按期交货至订单地址，<u>包装必须良好，外箱标签注明：SKU、规格名称及装箱数量，标签清析明确，否则需方有权拒收。</u> -->
                    <!-- 						</br>3.供方所供物品必须符合国家或行业标准，且以双方签署的质量标准协议书为准，按C=0.AQL=0.25%标准抽样检查，符合环保ROHS/2002/95EC要求；质保期1年。 -->
                    <!-- 						</br>4.逾期交货按订单总额每天的5%处罚或者取消订单。;如因产品质量问题造成需方客户退货或罚款，所产生的一切经济损失由供方承担。 -->
                    <!-- 						</br>5.合同纠纷解决方式：若买卖双方履行本合同发生异议，由双方友好协商解决，若协商不成，买卖双方可向卖方所在地当地人民法院起诉解决。 -->
                </td>
            </tr>
        </table>
        <table class="list" style="text-align: left;">
            <tr>
                <td>供方代表（签字盖章）：</td>
                <td>需要方代表（签字盖章）：</td>
            </tr>
            <tr>
                <td>签订日期：</td>
                <td>签订日期：</td>
            </tr>
        </table>
    </div>
</div>

