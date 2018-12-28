<?php
use yii\helpers\Html;
use app\config\Vhelper;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\models\PurchaseOrderTaxes;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseCompact;
?>
<style type="text/css">
    .box-span {
        padding: 5px;
    }
    .box-span span {
        display: inline-block;
        padding: 0px 15px 0px 0px;
        font-size: 15px;
    }
</style>
<table class="table table-bordered">
    <tr>
        <th>供应商名称</th>
        <td colspan="3"><?= ($compact->supplier_name) ? : (BaseServices::getSupplierName($model->supplier_code)); ?></td>
    </tr>
    <tr>
        <th>支付方式</th>
        <td><?= !empty($model->pay_type) ? SupplierServices::getDefaultPaymentMethod($model->pay_type) : ''; ?></td>
    </tr>
    <tr>
        <th style="width: 130px;">是否退税</th>
        <td colspan="3">
            <?php
            if($compact->is_drawback == 1) {
                echo '<span class="label label-success">不退税</span>';
            } else {
                echo '<span class="label label-info">退税</span>';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th style="width: 130px;">合同创建时间</th>

        <td><?= $compact->create_time ?></td>
        <td>合同创建人</td>
        <td>
            <?= $compact->create_person_name ?>
        </td>
    </tr>
</table>

<?php if(in_array($model->pay_type,[3,5])){?>
    <div class="row">
        <?php
        $selfPayName = \app\models\DataControlConfig::find()->select('values')->where(['type'=>'self_pay_name'])->scalar();
        $self_pay_name_array = $selfPayName ? explode(',',$selfPayName) : ['合同运费','合同运费走私账'];
        $accountType = in_array($model->pay_name,$self_pay_name_array) ? 2 : ($compact->is_drawback==1 ? 2 : 1);//通过是否退税判断银行卡类型
        $bankCardInfo = \app\models\SupplierPaymentAccount::find()
            ->where(['supplier_code'=>$model->supplier_code])
            ->andWhere(['account_type'=>$accountType])
            ->andWhere(['status'=>1])->asArray()->all();
        ?>
        <table class="table">
            <thead>
            <th>开户名</th>
            <th>账号</th>
            <th>支付平台</th>
            <th>主行</th>
            <th>支行</th>
            </thead>
            <tbody>
            <?php if(empty($bankCardInfo)){
                echo '<tr><td colspan="5" style="text-align: center">无可用银行卡信息</td></tr>';
            }else{
                $html='';
                foreach ($bankCardInfo as $value){
                    $html.="<tr>";
                    $html.="<td>".$value['account_name']."</td>";
                    $html.="<td>".$value['account']."</td>";
                    $plat = is_array(SupplierServices::getPaymentPlatform($value['payment_platform'])) ? '异常' :SupplierServices::getPaymentPlatform($value['payment_platform']);
                    $html.="<td>".$plat."</td>";
                    $html.="<td>".\app\models\UfxFuiou::getMasterBankInfo($value['payment_platform_bank'])."</td>";
                    $html.="<td>".$value['payment_platform_branch']."</td>";
                    $html.="</tr>";
                }
                echo $html;
            }?>
            </tbody>
        </table>
    </div>
<?php }?>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">商品信息</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>采购单号</th>
                <th>商品</th>
                <th>数量</th>
                <th>小计</th>
            </tr>
            </thead>
            <?php
            $totalMoney = 0;
            foreach($data as $k => $value):
                $skus = $value['purchaseOrderItems'];
                ?>

                <?php
                foreach($skus as $i => $v):
                    $img = Html::img(Vhelper::downloadImg($v['sku'], $v['product_img'], 2), ['width' => '110px', 'class' => 'media-object']);

                    $rate = -1;
                    //判断是否是：FBA、海外，是（base_price）为原价
                    $type = Vhelper::getNumber($v['pur_number']);
                    if ($type!=1) {
                        $hanshui_price = $v['price']; // 含税单价
                        $v['price'] = (int)$v['base_price']>0?$v['base_price']:$v['price']; //原价
                    }

                    $o_price = $v['price'];
                    if($compact->is_drawback == 1) {
                        $price = $v['price'];
                    } else {
                        if(preg_match('/^ABD/', $v['pur_number'])) {
                            $rate = PurchaseOrderTaxes::getABDTaxes($v['sku'], $v['pur_number']);
                            if ($hanshui_price == $o_price) {
                                //保留三位小数，四舍五入
                                $price = sprintf("%.3f", ((float)$rate * $v['price']) / 100 + $v['price']);
                            } else {
                                $price = $hanshui_price;
                            }
                        } else {
                            $rate = PurchaseOrderTaxes::getTaxes($v['sku'], $v['pur_number']);
                            //保留三位小数，四舍五入
                            $price = sprintf("%.3f", ((float)$rate * $v['price']) / 100 + $v['price']);
                        }
                    }
                    $num = $v['ctq'] ? $v['ctq'] : 0;
                    $oneSkuMOney = $price*$num;
                    ?>

                    <tr>
                        <?php if($i == 0): ?>
                            <td rowspan="<?= count($skus) ?>" style="vertical-align: middle;text-align: center;"><?= $value['pur_number'] ?></td>
                        <?php endif; ?>

                        <td width="430px">
                            <div class="media">
                                <div class="media-left">
                                    <?= $img ?>
                                </div>
                                <div class="media-body">
                                    <p>SKU：<?= $v['sku'] ?></p>

                                    <?php if($rate == -1): ?>

                                    <p>单价：<strong style="color: red;"><?= $price ?></strong></p>

                                    <?php else: ?>

                                        <p>单价：<strong style="color: red;"><?= $price ?></strong>(原价格：<?= $o_price ?> 开票点：<?= $rate ?>)</p>

                                    <?php endif; ?>

                                    <p><?= $v['name'] ?></p>
                                </div>
                            </div>
                        </td>

                        <td width="250px;">
                            <ul class="list-unstyled">
                                <li>订单数量：<strong><?= $num ?></strong></li>
                                <li>取消数量：<strong><?= $v['quxiao_num'] ?></strong></li>
                                <li>收货数量：<strong><?= $v['shouhuo_num'] ?></strong></li>
                                <li>未到货数量：<strong><?= $v['weidaohuo_num'] ?></strong></li>
                                <li>入库数量：<strong><?= $v['ruku_num'] ?></strong></li>
                                <li>不良品数量：<strong><?= $v['nogoods'] ?></strong></li>
                            </ul>
                        </td>
                        <td><?= $oneSkuMOney ?></td>
                    </tr>

                <?php
                    $totalMoney += $oneSkuMOney;
                    endforeach;
                ?>
            <?php endforeach; ?>


            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td><?= $totalMoney ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="box box-warning">

    <div class="box-header">

        <div class="box-span">
            <span>本次请款比例：<strong style="color: red;"><?= $model->pay_ratio ?></strong></span>
            <?php
                $arr = explode('+', $model->js_ratio);
                $settlement_ratio = '';
                if(count($arr)>=3){
                    $settlement_ratio = '结算方式(月结)+10%定金+发货前30%尾款+到货后60%尾款月结';
                }else{
                    $settlement_ratio = $model->js_ratio;
                }
            ?>
            <span>总结算比例：<?= $settlement_ratio ?></span>
        </div>

        <div class="box-span">
            <span>本次请款金额：<strong style="color: red;"><?= $model->pay_price ?></strong></span>
            <span>总商品额：<?= $compact->product_money ?></span>
            <span>总运费：<?= $compact->freight ?></span>
            <span>总优惠：<?= $compact->discount ?></span>
            <span>实际总额：<?= $compact->real_money ?></span>
        </div>

        <div class="box-span">
            <span>申请人：<strong style="color: red;"><?= BaseServices::getEveryOne($model->applicant) ?></strong></span>
            <span>申请时间：<?= $model->application_time ?></span>
        </div>
        <div class="box-span">
            <span>取消金额：<strong style="color: red;"><?= PurchaseCompact::getCompactCancelPrice(false,$data[0]['pur_number']); ?></strong></span>
        </div>

    </div>

    <div class="box-body">
        <p style="padding: 0px 0px 0px 5px;">请款备注：<?= $model->create_notice ?></p>
    </div>

</div>














