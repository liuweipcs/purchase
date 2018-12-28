<?php
use yii\helpers\Html;
use app\config\Vhelper;

use app\models\PurchaseOrderTaxes;



?>
<style type="text/css">
    .my-table2 {
        border-collapse: collapse;
        background-color: #fff;
        border: 1px solid #aaa;
        width: 756px;
        margin: 0px auto;
    }
    .my-table2 td {
        padding: 6px 15px 6px 6px;
        border: 1px solid #607D8B;
    }
    .my-table2 th {
        vertical-align: middle;
        padding: 5px 15px 5px 6px;
        border: 1px solid #3F3F3F;
        text-align: center;
        font-weight: bold;
    }
</style>

<table class="my-table2">
    <tr>
        <th colspan="8">深圳市易佰网络科技有限公司采购订单合同</th>
    </tr>
    <tr>
        <th colspan="4">甲方信息</th>
        <th colspan="4">乙方信息</th>
    </tr>
    <tr>
        <th>公司名</th>
        <td colspan="3"><?= $model->j_company_name ?></td>
        <th>公司名</th>
        <td colspan="3"><?= $model->y_company_name ?></td>
    </tr>
    <tr>
        <th>地  址</th>
        <td colspan="3" style="width: 100px;"><?= $model->j_address ?></td>
        <th>地  址</th>
        <td colspan="3" style="width: 100px;"><?= $model->y_address ?></td>
    </tr>
    <tr>
        <th>联 系 人</th>
        <td colspan="3"><?= $model->j_linkman ?></td>
        <th>联 系 人</th>
        <td colspan="3"><?= $model->y_linkman ?></td>
    </tr>
    <tr>
        <th>电话</th>
        <td colspan="3"><?= $model->j_phone ?></td>
        <th>电话</th>
        <td colspan="3"><?= $model->y_phone ?></td>
    </tr>
</table>

<table class="my-table2">

    <tr>
        <th>采购单号</th>
        <th style="width: 50px;">SKU</th>
        <th style="width: 100px;">品名</th>
        <th>单价</th>
        <th>数量</th>
        <th>金额</th>
        <th>图片</th>
    </tr>

    <?php
    foreach($products as $pur_number=>$items):
        $data = \app\models\PurchaseOrder::find()->where(['pur_number' => $pur_number])->one();
        if(count($items) > 1):
            ?>
            <?php
            foreach($items as $k=>$item):

                $img = Html::img(Vhelper::downloadImg($item['sku'], $item['product_img'], 2), ['width' => '60px', 'height' => '60px']);

                //$img = \toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($item['sku']),'width' => '60px', 'height' => '60px']);

                if($data['is_drawback'] == 2) {

                    $rate = PurchaseOrderTaxes::getABDTaxes($item['sku'],$item['pur_number']);




                    $price = ((int)$rate*$item['price'])/100 + $item['price'];
                } else {
                    $price = $item['price'];
                }


                ?>
                <tr>
                    <?php if($k == 0): ?>

                        <td rowspan="<?= count($items) ?>" style="vertical-align: middle;text-align: center;"><?= $pur_number ?></td>

                    <?php endif; ?>

                    <td><?= $item['sku'] ?></td>
                    <td><?= $item['name'] ?></td>
                    <td style="text-align: center;"><?= $price ?></td>
                    <td style="text-align: center;"><?= $item['ctq'] ?></td>
                    <td style="text-align: center;"><?= $price*$item['ctq'] ?></td>
                    <td style="text-align: center;"><?= $img ?></td>
                </tr>

            <?php endforeach; ?>

        <?php
        else:

            $img = Html::img(Vhelper::downloadImg($items[0]['sku'], $items[0]['product_img'], 2), ['width' => '60px', 'height' => '60px']);

            //$img = \toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($items[0]['sku']),'width' => '60px', 'height' => '60px']);

            if($data['is_drawback'] == 2) {




                $rate = PurchaseOrderTaxes::getABDTaxes($item[0]['sku'],$item[0]['pur_number']);


                $price = ((int)$rate*$items[0]['price'])/100 + $items[0]['price'];

            } else {
                $price = $items[0]['price'];
            }

            ?>

            <tr>
                <td style="vertical-align: middle;text-align: center;"><?= $pur_number ?></td>
                <td><?= $items[0]['sku'] ?></td>
                <td><?= $items[0]['name'] ?></td>
                <td style="text-align: center;"><?= $price ?></td>
                <td style="text-align: center;"><?= $items[0]['ctq'] ?></td>
                <td style="text-align: center;"><?= $price*$items[0]['ctq'] ?></td>
                <td style="text-align: center;"><?= $img ?></td>
            </tr>

        <?php endif; ?>

    <?php endforeach; ?>

    <tr>
        <th rowspan="2">备注</th>
        <td>运费</td>
        <td colspan="5"><?= $model->note_freight ?></td>
    </tr>
    <tr>
        <td>其它</td>
        <td colspan="5"><?= $model->note_other ?></td>
    </tr>
    <tr>
        <th>送货方式</th>
        <td colspan="6"><?= $model->ship_method ?></td>
    </tr>

    <tr>
        <th>收货地址</th>
        <td colspan="6"><?= $model->shouhuo_address ?></td>
    </tr>

    <tr>
        <th>总金额</th>
        <td><?= $model->real_money ?></td>

        <td colspan="5">
            订金： <?= $model->dj_money ?>
            尾款： <?= $model->wk_money ?><br/>
            尾款总额：<?= $model->wk_total_money ?>
            运费： <?= $model->freight ?>
            优惠金额：<?= $model->discount ?>
        </td>
    </tr>

    <tr>
        <th>付款说明</th>
        <td colspan="6"><?= $model->payment_explain ?></td>
    </tr>

    <tr>
        <th>合作要求</th>
        <td colspan="6"><?= $model->hezuo_reqiure ?></td>
    </tr>

    <tr>
        <th>汇款信息</th>
        <td colspan="6"><?= $model->huikuan_information ?></td>
    </tr>

    <tr>
        <th colspan="7" style="text-align: center;">合约要求</th>
    </tr>
    <tr>
        <td colspan="7"><?= $model->heyue_require ?></td>
    </tr>

    <tr>
        <th colspan="7" style="text-align: center;">质检要求</th>
    </tr>
    <tr>
        <td colspan="7"><?= $model->zhijian_require ?></td>
    </tr>

</table>

<table class="my-table2">

    <tr>
        <th>订购方签章</th>
        <td colspan="3">
            经办人签字：<br/>
            负责人签字：<br/>
            单位盖章：<br/>
            日期：
        </td>
        <th>供应商签章</th>
        <td colspan="3">
            经办人签字：<br/>
            负责人签字：<br/>
            单位盖章：<br/>
            日期：
        </td>
    </tr>

</table>


