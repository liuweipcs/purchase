<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\models\PurchaseOrderTaxes;
use app\models\ProductTaxRate;
$this->title = '海外仓订单合同-通用版';
?>

<?php if(isset($print)): ?>

    <table class="my-table2">
    <tr>
        <th colspan="8" style="text-align: center;"><h4>深圳市易佰网络科技有限公司采购订单合同</h4></th>
    </tr>
    <tr>
        <th colspan="8" style="text-align: right;">合同编号: <?= $model->compact_number ?></th>
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
        <td colspan="3" width="50px"><?= $model->j_address ?></td>
        <th>地  址</th>
        <td colspan="3"><?= $model->y_address ?></td>
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
    foreach($products as $pur_number => $items):
        $i = 0;
        $data = \app\models\PurchaseOrder::find()->where(['pur_number' => $pur_number])->one();
        if(count($items) > 1):
            ?>
            <?php
            foreach($items as $k=>$item):
                $img = Html::img(Vhelper::downloadImg($item['sku'], $item['product_img'], 2), ['width' => '60px', 'height' => '60px']);
                if($data['is_drawback'] == 2) {
                    $rate = PurchaseOrderTaxes::getABDTaxes($item['sku'],$item['pur_number']);
                    $price = ((float)$rate*$item['price'])/100 + $item['price'];
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

            <?php
                $i++;
                endforeach;
            ?>

        <?php
        else:
            $img = Html::img(Vhelper::downloadImg($items[0]['sku'], $items[0]['product_img'], 2), ['width' => '60px', 'height' => '60px']);
            if($data['is_drawback'] == 2) {
                $rate = PurchaseOrderTaxes::getABDTaxes($items[0]['sku'],$items[0]['pur_number']);
                $price = ((float)$rate*$items[0]['price'])/100 + $items[0]['price'];
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

        <?php
            $i++;
            endif;
        ?>


        <?php

            if($i > 9) {
                echo '<pagebreak></pagebreak>';
            }

        ?>

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
        <td colspan="5" style="font-size: 20px;">
            <?php if($model->settlement_ratio !== '100%'): ?>
            订金： <?= $model->dj_money ?>
            尾款： <?= $model->wk_money ?>
            尾款总额：<?= $model->wk_total_money ?>
            <?php endif; ?>

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
        <td colspan="6" style="font-size: 20px;"><?= $model->huikuan_information ?></td>
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

<?php else:
$this->title = '海外仓-合同采购确认';
$this->params['breadcrumbs'][] = '海外仓';
$this->params['breadcrumbs'][] = '采购计划单';
$this->params['breadcrumbs'][] = $this->title;
$freight = $data['freight_discount'];
?>
<style type="text/css">
    .compact-content {
        width: 756px;
        margin: 10px auto;
    }
    .sbox {
        border: 1px solid #9E9E9E;
        width: 32%;
        position: absolute;
        background-color: #2196F3;
        padding: 5px 15px;
        color: #fff;
        display: none;
    }
    .sbox p:hover {
        color: #21f333;
        cursor: pointer;
    }
</style>

<div class="my-box" style="margin-bottom: 45px;">
    <div class="bg-line no">
        <span>1</span>
        <p>确认采购单信息</p>
    </div>
    <div class="bg-line">
        <span>2</span>
        <p>确认合同信息</p>
    </div>
</div>

<?php ActiveForm::begin(['id' => 'createCompace-form']); ?>

<div class="compact-content">

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
        <td colspan="3">
            <input type="text" name="Compact[j_company_name]" value="深圳市易佰网络科技有限公司">
        </td>
        <th>公司名</th>
        <td colspan="3">
            <input type="text" name="Compact[y_company_name]" value="<?= !empty($data['supplier']) ? $data['supplier']->supplier_name : ''; ?>">
        </td>
    </tr>
    <tr>
        <th>地  址</th>
        <td colspan="3">
            <input type="text" name="Compact[j_address]" value="深圳市龙华新区清湖社区清祥路清湖科技园二区B栋701">
        </td>
        <th>地  址</th>
        <td colspan="3">
            <input type="text" name="Compact[y_address]" value="<?= !empty($data['supplierContent']) ? $data['supplierContent']->chinese_contact_address : ''; ?>">
        </td>
    </tr>
    <tr>
        <th>联 系 人</th>
        <td colspan="3">
            <input type="text" name="Compact[j_linkman]" value="<?= $data['buyer'] ?>">
        </td>
        <th>联 系 人</th>
        <td colspan="3">
            <input type="text" name="Compact[y_linkman]" value="<?= !empty($data['supplierContent']) ? $data['supplierContent']->contact_person : ''; ?>">
        </td>
    </tr>
    <tr>
        <th>电话</th>
        <td colspan="3">
            <input type="text" name="Compact[j_phone]" value="">
        </td>
        <th>电话</th>
        <td colspan="3">
            <input type="text" name="Compact[y_phone]" value="<?= !empty($data['supplierContent']) ? $data['supplierContent']->contact_number : ''; ?>">
        </td>
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
    $total_money = 0;
    foreach($data['purchaseOrderItems'] as $pur_number => $items):
    ?>
            <?php
            foreach($items as $k=>$item):
                $img = Html::img(Vhelper::downloadImg($item['sku'], $item['product_img'], 2), ['width' => '60px', 'height' => '60px']);
                if($data['is_drawback'] == 2) {
                    $rate = \app\models\PurchaseOrderTaxes::getABDTaxes($item['sku'],$pur_number); // 开票点
                    $price = ((float)$rate*$item['price'])/100 + $item['price'];
                } else {
                    $price = $item['price'];
                }
                $total_money += $price*$item['ctq'];
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
    <?php endforeach; ?>

    <tr>
        <th rowspan="2">备注</th>
        <td>运费</td>
        <td colspan="5"><textarea name="Compact[note_freight]" rows="3" style="width: 100%;"></textarea></td>
    </tr>
    <tr>
        <td>其它</td>
        <td colspan="5"><textarea name="Compact[note_other]" rows="3" style="width: 100%;"></textarea></td>
    </tr>
    <tr>
        <th>送货方式</th>
        <td colspan="6"><textarea name="Compact[ship_method]" rows="3" style="width: 100%;">乙方需运送至甲方公司指定仓库地址</textarea></td>
    </tr>

    <tr>
        <th>收货地址</th>
        <td colspan="6">
            <input type="text" name="Compact[shouhuo_address]" value="上海市 奉贤区 南桥镇江海园区富邦路5号  范明  18823716839" id="shouhuo_address" style="width: 100%;">
            <div class="sbox">
                <p>上海市 奉贤区 南桥镇江海园区富邦路5号  范明  18823716839</p>
                <p>东莞市塘厦镇科苑大道16号易佰网络海外仓102  周喜洋 13480775154</p>
                <p>浙江省宁波市镇海区西经堂路399弄66号易佰网络科技有限公司收 罗雄 13642355572</p>
                <span style="position: absolute; top: 0; right: 10px;"><a href="javascript:void(0)" class="close">x</a></span>
            </div>
        </td>
    </tr>

    <?php $realMoney = $total_money + $freight['freight'] - $freight['discount']; ?>

    <tr>
        <th>总金额</th>
        <td>
            <input type="hidden" name="Compact[product_money]" id="product_money" value="<?= $total_money ?>">
            <input type="text" name="Compact[real_money]" value="<?= $realMoney ?>" style="width: 100px;" readonly>
        </td>
        <td colspan="5">

            <?php
                $is_drawback = $data['is_drawback'] ? $data['is_drawback'] : 1; // 默认不含税

                $plan = \app\models\PurchaseCompact::PaymentPlan3($data['settlement_ratio'], $total_money, $freight['freight'], $freight['discount'], $is_drawback);
                $_ratio = explode('+', $data['settlement_ratio']);
                if(count($_ratio) > 1):
                    $djRatio = $_ratio[0];
                    $wkRatio = $_ratio[1];
                ?>

                <label><?= $djRatio ?>订金：</label>
                <input type="text" name="Compact[dj_money]" value="<?= $plan['dj'] ?>" style="width: 60px;" readonly>

                <label><?= $wkRatio ?>尾款：</label>
                <input type="text" name="Compact[wk_money]" value="<?= $plan['wk'] ?>" style="width: 60px;" readonly>

                <label>尾款总额：</label>
                <input type="text" name="Compact[wk_total_money]" value="<?= $plan['wwk'] ?>" style="width: 60px;" readonly>

            <?php endif; ?>

            <label>运费：</label>
            <input type="text" name="Compact[freight]" value="<?= $freight['freight'] ? $freight['freight'] : 0; ?>" style="width: 60px;" readonly>

            <label>优惠金额：</label>：
            <input type="text" name="Compact[discount]" value="<?= $freight['discount'] ? $freight['discount'] : 0; ?>" style="width: 60px;" readonly>

        </td>

    </tr>

    <?php

    $texts = [];
    $texts[0] = "全款支付后当天算起，乙方必须在三天内发货并出示物流信息，乙方未按时交货，自逾期起需每日向甲方支付全款金额的5%作为违约滞纳金。其它未尽事宜，大家友好协商解决。";
    $texts[1] = "1、甲方向乙方预付订单<span style=color:red>30%</span>货款，进行生产。<br/>2、大货完成后支付<span style=color:red>70%</span>的尾款，乙方出示大货图发于甲方安排发货。<br/>3、乙方未按时交货，自逾期起需每日向甲方支付尾款金额的5%作为违约滞纳金。";
    $texts[2] = "1、甲方向乙方预付订单<span style=color:red>10%</span>货款，进行生产。<br/>2、大货完成后支付<span style=color:red>30%</span>的尾款，乙方出示大货图发于甲方安排发货。<br/>3、甲方收到货一个月后支付<span style=color:red>60%</span>尾款。<br/>4、乙方未按时交货，自逾期起需每日向甲方支付尾款金额的<span style=color:red>5%</span>作为违约滞纳金。";

    if($data['settlement_ratio'] == '100%') {
        $payment_explain = $texts[0];
    } elseif($data['settlement_ratio'] == '30%+70%') {
        $payment_explain = $texts[1];
    } elseif($data['settlement_ratio'] == '10%+30%+60%') {
        $payment_explain = $texts[2];
    } else {
        $payment_explain = '';
    }

    ?>

    <tr>
        <th>付款说明</th>
        <td colspan="6">
            <textarea name="Compact[payment_explain]" rows="5" style="width: 100%;"><?= $payment_explain ?></textarea>
        </td>
    </tr>

    <tr>
        <th>合作要求</th>
        <td colspan="6">
            <textarea name="Compact[hezuo_reqiure]" rows="3" style="width: 100%;">如果有我司工作人员索要回扣，影响正常合作，请致电我司总经理电话：【胡范金15012616166（微信）   庄俊超 13713710103（微信）】</textarea>
        </td>
    </tr>

    <?php
    $account = '';
    $account_name = '';
    $payment_platform_branch = '';
    if(!empty($data['supplierAccount'])) {
        if($data['supplierAccount']['account']) {
            $account = $data['supplierAccount']['account'];
        }
        if($data['supplierAccount']['account_name']) {
            $account_name = $data['supplierAccount']['account_name'];
        }
        if($data['supplierAccount']['payment_platform_branch']) {
            $payment_platform_branch = $data['supplierAccount']['payment_platform_branch'];
        }
    }
    ?>

    <tr>
        <th>汇款信息</th>
        <td colspan="6">
            <textarea name="Compact[huikuan_information]" rows="3" style="width: 100%;">收款账号：<?= $account ?>  户名： <?= $account_name ?>  开户行：<?= $payment_platform_branch ?></textarea>
        </td>
    </tr>

    <tr>
        <th colspan="7" style="text-align: center;">合约要求</th>
    </tr>
    <tr>
        <td colspan="7">
            <textarea name="Compact[heyue_require]" rows="8" style="width: 100%;">
1、合同公章扫描件具有法律效力。<br/>
2、交货日期：<b style="color: red;"> 2018年02月10日 </b>前交货，如交期延迟,乙方应及时通知甲方协商,未及时通知,自逾期起每日向甲方赔偿本订单全款金额的5%作为违约金。<br/>
3、乙方按经甲方签字确认的样品安排生产及交货，乙方负责全检、甲方负责抽检。<br/>
4、抽检产品合格率应在99%以上，产品出现批量不良，乙方需重新按甲方签字确认的样品生产，因此给甲方造成的损失由乙方承担。<br/>
5、产品的质量保证期为12个月，如有非人为损坏的质量问题产品，乙方应负责换货。<br/>
            </textarea>
        </td>
    </tr>

    <tr>
        <th colspan="7" style="text-align: center;">质检要求</th>
    </tr>
    <tr>
        <td colspan="7">
            <textarea name="Compact[zhijian_require]" rows="25" style="width: 100%;background-color: #E2EFD9;">
甲方所下达订单，乙方需在交货期前5天完成并通知甲方采购员，以便甲方确认是否按排人员前去乙方验货。所有甲方人员验货时，乙方均需提供OQC检验报表以及其他相关功能测试报表以供参考。<br/>
甲方有权对乙方的生产现场、生产流程、作业方式等进行审核，并提出改善建议；乙方对甲方的质量稽查须予以支持和配合，不允许有以任何形式隐瞒产品质量的现象。<br/>
一、当满足以下条件之一时，甲方可安人员排对产品及乙方的生产进行稽核，并对乙方提出生产改善建议。<br/>
1、初次下单采购的产品（甲方条件允许的情况下，样品审核阶段，甲方将对乙方进行生产考核）；<br/>
2、对于功能复杂、需对产品功能做严格测试的产品；<br/>
3、在销售过程中，产品在某个问题上出现批量异常或各类型问题累计过多；<br/>
4、甲方未安排人员去乙方检验，多次来货后检测合格率偏低的产品。<br/>

经检验当满足以下条件之一时，甲方有权拒收产品：<br/>
1、甲方正常提出验货，乙方拒不配合。<br/>
2、甲方在检验过程中，不良品超出AQL验收标准，乙方拒不全检。<br/>
3、甲方在检验过程中，不良品未超出AQL验收标准，乙方拒不更换不良品。<br/>
4、甲方提出改善建议，并与乙方达成一致后，再次订货乙方未将改善方案实施。<br/>

二、验收标准：<br/>
1、确定被检验货品数量（假设数量为500）；<br/>
2、确定抽样方案，在没有特别要求下，应按“一般检验标准Ⅱ”进行抽样。<br/>
3、在合格质量水平栏找出要求的AQL值，在没有特别要求下，以AQL值为2.5作检验标准；2.5对应的检验标准为：AC(可接受)=2，Re(不可接受)=3。<br/>
            </textarea>
        </td>
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

    <div style="width: 100%;text-align: center;padding-top: 10px;">

        <input type="hidden" name="Compact[supplier_name]" value="<?= $data['supplier_name']; ?>">
        <input type="hidden" name="Compact[supplier_code]" value="<?= $data['supplier_code']; ?>">
        <input type="hidden" name="Compact[settlement_ratio]" value="<?= $data['settlement_ratio'] ?>">
        <input type="hidden" name="Compact[is_drawback]" value="<?= $is_drawback?>">

        <input type="hidden" name="System[tid]" value="<?= $tid ?>">
        <input type="hidden" name="System[pos]" value="<?= $pos ?>">

        <button type="submit" class="btn btn-success">提交审核</button>

        <a href="javascript:void(0)" style="display: inline-block; margin-left: 25px;" onclick="javascript :history.back(-1)">返回上一步</a>

    </div>

</div>

<?php
ActiveForm::end();
?>

<?php
$js = <<<JS

$(function() {
    
    $('#shouhuo_address').focus(function() {
        $(this).parent().find('.sbox').show();
    });

    $('.sbox p').click(function() {
        $(this).parent().prev().val($(this).text());
        $(this).parent().hide();
    });

    $('.close').click(function() {
        $(this).parents('.sbox').hide();
    });
    
});

JS;
$this->registerJs($js);
?>

<?php endif; ?>

