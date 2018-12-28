<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\models\SupplierSettlement;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderCancelSub;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;

if (isset($print)) {
    $compact_number = $model->compact_number;
    $is_drawback = $model->is_drawback;
    $j_company_name = $model->j_company_name;
    $j_linkman = $model->j_linkman;
    $j_phone = $model->j_phone;
    $j_address = $model->j_address;
    $supplier_code = $model->supplier_code;
    $supplier_name = $model->supplier_name;
    $y_address = $model->y_address;
    $y_linkman = $model->y_linkman;
    $y_phone = $model->y_phone;
    $j_linkman = $model->j_linkman;
    $guige = json_decode($model->guige, true);
    $purchase_pay = $purchase->purchaseOrderPayType;
    $supplier = $purchase->supplier;
    $huikuan_information = $model->huikuan_information;
} else {
    extract($data);
    $is_drawback = $purchase->is_drawback;
    $j_company_name = $j_company['name'];
    $j_address = $j_company['address'];
    $j_linkman = $purchase->buyer;
    $j_phone = '<input type="text" id="j_phone" name="compact[j_phone]" value="" />';
    $supplier_code = $purchase->supplier_code;
    $supplier_name = $purchase->supplier_name;
    $y_address = $supplier->supplier_address;
    $y_linkman = isset($supplierContent->contact_person)?$supplierContent->contact_person:'';
    $y_phone = isset($supplierContent->contact_number)?$supplierContent->contact_number:'';
    
    $account = $account_name = $payment_platform_branch = '';
    if($supplierAccount) {
        $account = $supplierAccount['account'];;
        $account_name = $supplierAccount['account_name'];
        $payment_platform_branch = $supplierAccount['payment_platform_branch'];
    }
    $huikuan_information = "收款账号：{$account}  户名： {$account_name}  开户行：{$payment_platform_branch}";
}

$supplier_settlement_name = SupplierSettlement::find()->where(['supplier_settlement_code'=>$purchase->account_type])->select('supplier_settlement_name')->scalar();
$freight_payer = $purchase_pay->freight_payer == 1 ? '甲方支付' : '乙方支付';

$transit_warehouse = PurchaseOrderServices::getTransitWarehouseInfo($purchase->transit_warehouse);
$delivery_address = str_replace('{buyer}', $j_linkman, $transit_warehouse['delivery_address']);

$this->title = '海外仓订单合同-'.($is_drawback == 2 ? '含税' : '不含税');
?>

<?php ActiveForm::begin(['id' => 'createCompace-form']); ?>
<table class="my-table2" style="font-size: 14px">
    <tr>
        <th colspan="9" style="text-align: center;"><h4 style="font-size:18px;font-weight: bold"><?php echo $j_company_name;?>采购订单合同</h4></th>
    </tr>
    <tr>
        <th colspan="9" style="text-align: right;">合同编号: <?= $compact_number; ?></th>
    </tr>
    <tr>
        <th colspan="4">甲方信息</th>
        <th colspan="5">乙方信息</th>
    </tr>
    <tr>
        <th>公司名</th>
        <td colspan="3">
        	<?= $j_company_name; ?>
        	<input type="hidden" name="compact[j_company_name]" value="<?= htmlspecialchars($j_company_name); ?>" >
        </td>
        <th colspan="2">公司名</th>
        <td colspan="3">
        	<?php echo $supplier_name?>
        	<input type="hidden" name="compact[supplier_code]" value="<?= htmlspecialchars($supplier_code); ?>" >
        	<input type="hidden" name="compact[supplier_name]" value="<?= htmlspecialchars($supplier_name); ?>" >
        	<input type="hidden" name="compact[y_company_name]" value="<?= htmlspecialchars($supplier_name); ?>" >
        </td>
    </tr>
    <tr>
        <th>地址</th>
        <td colspan="3">
        	<?= $j_address; ?>
        	<input type="hidden" name="compact[j_address]" value="<?= htmlspecialchars($j_address); ?>" >
        </td>
        <th colspan="2">地址</th>
        <td colspan="3">
        	<?php echo $y_address; ?>
        	<input type="hidden" name="compact[y_address]" value="<?= htmlspecialchars($y_address) ?>" >
        </td>
    </tr>
    <tr>
        <th>联系人</th>
        <td colspan="3">
        	<?= $j_linkman; ?>
        	<input type="hidden" name="compact[j_linkman]" value="<?= htmlspecialchars($j_linkman); ?>" >
        </td>
        <th colspan="2">联系人</th>
        <td colspan="3">
        	<?php echo $y_linkman;?>
        	<input type="hidden" name="compact[y_linkman]" value="<?= htmlspecialchars($y_linkman); ?>" >
        </td>
    </tr>
    <tr>
        <th>电话</th>
        <td colspan="3">
        	<?= $j_phone; ?>
        </td>
        <th colspan="2">电话</th>
        <td colspan="3">
        	<?php echo $y_phone;?>
            <input type="hidden" name="compact[y_phone]" value="<?= htmlspecialchars($y_phone); ?>" >
        </td>
    </tr>
    <tr>
        <th>采购单号</th>
        <th style="width: 50px;">SKU</th>
        <th style="width: 100px;">品名</th>
        <th>规格</th>
        <?php if($is_drawback == 2):?>
            <th>含税单价</th>
        <?php else: ?>
            <th>单价</th>
        <?php endif;?>
        <th>数量</th>
        <th>金额</th>
        <th>图片</th>
        <th>备注</th>
    </tr>
    <?php

        $pur_numbers = array_keys($purchaseItems);
        $cancelInfo = PurchaseOrderCancelSub::getCancelCtq($pur_numbers);
        //判断是否付款：有取消数量时：未付款的，合同金额和sku刷新
        if (!empty($model)) {
            $isPay = PurchaseOrderPay::find()->where(['pur_number'=>$model->compact_number])->andWhere(['in', 'pay_status', [5, 6]])->exists();
        } else {
            $isPay = false;
        }

        $totalprice = 0;
        foreach($purchaseItems as $pur_number => $items):
            $is_first = true;
            //合并单元格
            $cancel_items = $items;
            foreach($cancel_items as $ck=>$cv):
                $csku = strtoupper($cv['sku']);
                if (!empty($cancelInfo[$pur_number][$csku]) && ($cancelInfo[$pur_number][$csku]==$cv['ctq'])) {
                    unset($cancel_items[$ck]);
                    continue;
                }
            endforeach;

            //遍历数据
            foreach($items as $k=>$item):
                $sku = strtoupper($item['sku']);
                if (!empty($cancelInfo[$pur_number][$sku]) && ($cancelInfo[$pur_number][$sku]==$item['ctq'])) {
                     # 已付款
                    if ($isPay==true) $totalprice += $item['price']*$item['ctq'];
                    unset($items[$k]);
                    continue;
                } else {
                    $totalprice += $item['price']*$item['ctq'];
                }
                // $items = array_values($items);

                $img = Html::img(Vhelper::downloadImg($item['sku'], $item['product_img'], 2), ['width' => '60px', 'height' => '60px']);
        ?>
        <tr>
            <?php if($is_first == true): $is_first=false;?>
                <td rowspan="<?= count($cancel_items) ?>" style="vertical-align: middle;text-align: center;"><?= $pur_number ?></td>
            <?php endif; ?>
            <td><?= $item['sku'] ?></td>
            <td><?= iconv("UTF-8", "UTF-8//IGNORE", $item['name']); ?></td>
            <td>
            <?php if (isset($print)) : ?>
            <?php echo $guige[$pur_number][$sku]?>
            <?php else : ?>
            <input name="compact[guige][<?php echo $pur_number;?>][<?php echo $sku?>]" value="" style="width:70px" />
            <?php endif; ?>
            </td>
            <td style="text-align: center;"><?= $item['price'] ?></td>
            <td style="text-align: center;"><?= $item['ctq'] ?></td>
            <td style="text-align: center;"><?= $item['price']*$item['ctq'] ?></td>
            <td style="text-align: center;"><?= $img ?></td>
            <td></td>
        </tr>
     <?php endforeach; endforeach; ?>
     <tr>
     	<td colspan="2">
     		运费支付：<?php echo $freight_payer;?>
     	</td>
     	<td colspan="4"></td>
     	<td style="text-align: center;">
     		<?php echo $totalprice;?>
     		<input type="hidden" name="compact[product_money]" value="<?= $totalprice; ?>" >
     	</td>
     	<td colspan="2"></td>
     </tr>
     <tr>
        <td>送货方式</td>
        <td colspan="2">乙方需运送至甲方公司指定仓库地址并安排卸货</td>
        <td style="text-align: center;" colspan="3">交货日期</td>
        <td style="text-align: center;" colspan="3"><?php echo date('Y-m-d',strtotime($purchase->date_eta) - 86400*5)?></td>
    </tr>
    <tr>
    	<td>收货地址</td>
    	<td style="text-align: center;" colspan="8">
    		<?php echo $delivery_address?>
    	</td>
    </tr>
    <tr>
    	<td>总金额</td>
    	<td><?php echo $totalprice;?></td>
    	<td colspan="7">
    		<?php $settlement_ratio = explode('+',$purchase_pay->settlement_ratio);?>
    		<?php if ($settlement_ratio[0] != "100%") : ?>
    		订金：<?php $damount = round(intval($settlement_ratio[0])*$totalprice/100,3); echo $damount;?> &nbsp;
    		尾款：<?php echo $totalprice - $damount;?>  &nbsp;
    		尾款总额：<?php echo $totalprice - $damount;?>
    		<?php endif; ?>
    		<input type="hidden" name="compact[settlement_ratio]" value="<?php echo htmlspecialchars($purchase_pay->settlement_ratio);?>" />
    	</td>
    </tr>
    <?php 
        $pay_notes = [];
        $pay_notes[] = '付款方式：'.$supplier_settlement_name;
        if ($settlement_ratio[0] != "100%") {
            $pay_notes[] = '甲方向乙方预付订单<font style="color:red">'.$settlement_ratio[0].'</font>货款，进行生产。';
            $pay_percent = $settlement_ratio[1];
            if (isset($settlement_ratio[2])) $pay_percent .= '+'.$settlement_ratio[2];
            $pay_notes[] = '大货完成后支付<font style="color:red">'.$pay_percent.'</font>的尾款，乙方出示大货图发于甲方安排发货。';
        }
        $pay_notes[] = '乙方未按时交货，自逾期起需每日向甲方支付尾款金额的<font style="color:red">5‰</font>作为违约滞纳金。';
    ?>
    <tr>
    	<td rowspan="<?php echo count($pay_notes); ?>">付款说明</td>
    	<td colspan="8">1、<?php echo $pay_notes[0]; ?></td>
    </tr>
    <?php foreach ($pay_notes as $k=>$v) : if ($k == 0) continue; ?>
    <tr>
    	<td colspan="9"><?php echo ($k+1).'、'.$v;?></td>
    </tr>
    <?php endforeach; ?>
    <tr>
    	<td>开票要求</td>
    	<td colspan="4">供应商开具此合同等同金额增值税发票，发票可用作出口退税使用。</td>
    	<td colspan="2">开票金额合计</td>
    	<td colspan="2"><?php echo $is_drawback == 1 ? 0 : $totalprice;?></td>
    </tr>
    <tr>
    	<td rowspan="3">包装要求</td>
    	<td colspan="8">1：产品单个包装，每套包装请用中性无logo内盒，如产品原厂包装为彩盒时，必须用非透明包装袋进行二次包装，包装不符合规定，仓库拒收该订单商品。</td>
    </tr>
    <tr>
    	<td colspan="8">2:发货时，每个外箱必须有唛头，唛头内容包括（“PO NO.”“SKU:”“采购员”“订单数量”“箱内数量”“箱数”“是否出口退税：”“所属仓库：海外仓”</td>
    </tr>
    <tr>
    	<td colspan="8">3：每批产品发货前，将发货清单，放置第一箱，并在外箱上标注，箱内有发货清单。</td>
    </tr>
    <tr>
    	<td>合作要求</td>
    	<td colspan="8">
    	如果有我司工作人员索要回扣，影响正常合作，请致电我司总经理电话：【胡范金15012616166 （微信） 庄俊超 13713710103（微信）】
    	</td>
    </tr>
    <tr>
    	<td>汇款信息</td>
    	<td colspan="8">
    		<?php echo $huikuan_information;?>
    		<input type="hidden" name="compact[huikuan_information]" value="<?= htmlspecialchars($huikuan_information); ?>" >
    	</td>
    </tr>
    <tr>
    	<td colspan="9" style="text-align:center;font-size:16px;font-weight:600">合约要求</td>
    </tr>
    <tr>
    	<td colspan="9">1、合同公章扫描件具有法律效力，因合同所引起的争议和纠纷，双方应通过谈判和协商解决，协商不能达成一致，在甲方所在地人民法院提起诉讼。</td>
    </tr>
    <tr>
    	<td colspan="9">2、甲方向乙方下达采购订单，乙方需在2个工作日内确认并回复，将订单盖章回传（必须为公章或合同专用章）.卖方应严格按照订单确认交期交货，如交期延迟，并至少提前五个工作日内以书面方式通知甲方协商，且甲方有权取消订单或更改订单。乙方未及时通知,自逾期起每日向甲方赔偿本订单全款金额的5‰作为违约金。如甲方取消订单且甲方已支付订金，乙方需在2个工作日内根据原支付途径退回订金。   </td>
    </tr>
    <tr>
    	<td colspan="9">3、乙方按经甲方签字确认的样品安排生产及交货，乙方负责全检、甲方负责抽检。 乙方每批次到货时，因发现产品质量问题而影响到甲方仓库入库进度时，需甲方配合全检或二次包装或额外要求甲方质检人员配合时，产生的相关人工检测费用或额外加工费由乙方支付。</td>
    </tr>
    <tr>
    	<td colspan="9">4、抽检产品合格率应在99%以上，产品出现批量不良，乙方需重新按甲方签字确认的样品生产，因此给甲方造成的损失由乙方承担。</td>
    </tr>
    <tr>
    	<td colspan="9">5、产品的质量保证期为12个月，如有非人为损坏的质量问题产品，乙方应负责换货。</td>
    </tr>
    <tr>
    	<td colspan="9">6、乙方应避免其提供的产品有任何知识产权侵权行为，因产品侵权问题 产生的全部责任由乙方承担，由此给甲方造成任何损失的，乙方负责全额赔偿。</td>
    </tr>
    <tr>
    	<td colspan="9">7：如需外验，经甲乙方双方确认后，甲方安排质检部门到乙方工厂验货，因未完成生产或质量问题而导致第二次检测，所产生的所有费用由乙方承担。（此条款只限于单批次出货总额大于或等于8000元人民币订单）</td>
    </tr>
    <tr>
    	<td colspan="9" style="text-align:center;font-size:16px;font-weight:600">质检要求</td>
    </tr>
    <tr>
    	<td colspan="9">甲方所下达订单，乙方需在交货期前5天完成并通知甲方采购员，以便甲方确认是否按排人员前去乙方验货。所 有甲方人员验货时，乙方均需提供OQC检验报表以及其他相关功能测试报表以供参考。</td>
    </tr>
    <tr>
    	<td colspan="9">甲方有权对乙方的生产现场、生产流程、作业方式等进行审核，并提出改善建议；乙方对甲方的质量稽查须予 以支持和配合，不允许有以任何形式隐瞒产品质量的现象。</td>
    </tr>
    <tr>
    	<td colspan="9">一、当满足以下条件之一时，甲方可安人员排对产品及乙方的生产进行稽核，并对乙方提出生产改善建议。  </td>
    </tr>
    <tr>
    	<td colspan="9">1、初次下单采购的产品（甲方条件允许的情况下，样品审核阶段，甲方将对乙方进行生产考核）；</td>
    </tr>
    <tr>
    	<td colspan="9">2、对于功能复杂、需对产品功能做严格测试的产品；</td>
    </tr>
    <tr>
    	<td colspan="9">3、在销售过程中，产品在某个问题上出现批量异常或各类型问题累计过多；</td>
    </tr>
    <tr>
    	<td colspan="9">4、甲方未安排人员去乙方检验，多次来货后检测合格率偏低的产品。</td>
    </tr>
    <tr>
    	<td colspan="9">经检验当满足以下条件之一时，甲方有权拒收产品：      </td>
    </tr>
    <tr>
    	<td colspan="9">1、甲方正常提出验货，乙方拒不配合。</td>
    </tr>
    <tr>
    	<td colspan="9">2、甲方在检验过程中，不良品超出AQL验收标准，乙方拒不全检。</td>
    </tr>
    <tr>
    	<td colspan="9">3、甲方在检验过程中，不良品未超出AQL验收标准，乙方拒不更换不良品。</td>
    </tr>
    <tr>
    	<td colspan="9">4、甲方提出改善建议，并与乙方达成一致后，再次订货乙方未将改善方案实施。 </td>
    </tr>
    <tr>
    	<td colspan="9">二、验收标准：</td>
    </tr>
    <tr>
    	<td colspan="9">1、确定被检验货品数量（假设数量为500）；</td>
    </tr>
    <tr>
    	<td colspan="9">2、确定抽样方案，在没有特别要求下，应按“一般检验标准Ⅱ”进行抽样。</td>
    </tr>
    <tr>
    	<td colspan="9">3、在合格质量水平栏找出要求的AQL值，在没有特别要求下，以AQL值为2.5作检验标准；2.5对应的检验标准   为：AC(可接受)=2，Re(不可接受)=3。</td>
    </tr>
    <tr>
        <th>订购方签章</th>
        <td colspan="3">
            经办人签字：<br/>
            负责人签字：<br/>
            单位盖章：<br/>
            日期：
        </td>
        <th>供应商签章</th>
        <td colspan="4">
            经办人签字：<br/>
            负责人签字：<br/>
            单位盖章：<br/>
            日期：
        </td>
    </tr>
</table>
<?php if (!isset($print)) : ?>
<div style="width: 756px;text-align: center;padding-top: 20px;">
    <button type="submit" onclick="return checkform()" class="btn btn-success">确认</button>
    <a href="javascript:void(0)" style="display: inline-block; margin-left: 25px;" onclick="javascript :history.back(-1)">返回上一步</a>
</div>
<script type="text/javascript">
function checkform() {
	var j_phone = $("#j_phone").val();
	if (!j_phone) {
		layer.msg('请填写甲方联系人电话');
		return false;
	}
	return true;
}
</script>
<?php endif; ?>
<?php ActiveForm::end(); ?>
