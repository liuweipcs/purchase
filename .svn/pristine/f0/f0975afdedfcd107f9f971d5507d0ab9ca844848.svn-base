<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\models\PurchaseOrder;
use app\models\PurchaseCompact;
use app\models\WarehouseResults;
use app\services\SupplierServices;
$this->title = 'FBA仓订单合同-不含税';
$quankuan = [1, 6, 7, 8, 9, 16];
$dj_zhuangqi = [11, 12, 13, 15, 17, 18, 19]; //订金+尾款账期
?>

<?php if(isset($print)): ?>
    <table class="my-table2 table">
		<tr><th colspan="12" style="text-align: center;"><h4 style="font-weight: bold">YIBAI TECHNOLOGY LTD采购订单合同</h4></th></tr>
        <tr>
            <th colspan="12" style="text-align: right;">合同编号: <?= $model->compact_number ?></th>
        </tr>
        <tr>
            <th colspan="6">甲方</th>
            <th colspan="6">乙方</th>
        </tr>
        <tr>
            <th style="width: 78px;">单位名称</th>
            <td colspan="5"><?= $model->j_company_name ?></td>
            <th style="width: 78px;">单位名称</th>
            <td colspan="5"><?= $model->y_company_name ?></td>
        </tr>
        <tr>
            <th>地  址</th>
            <td colspan="5"><?= $model->j_address?></td>
            <th>地  址</th>
            <td colspan="5"><?= $model->y_address?></td>
        </tr>
        <tr>
            <th rowspan="2">授权代表(采购)</th>
            <td colspan="5" rowspan="2"><?= $model->j_linkman?></td>
            <th>法人代表</th>
            <td colspan="5"><?= $model->y_corporate?></td>
        </tr>
        <tr>

        	<th>联 系 人</th>
            <td colspan="5"><?= $model->y_linkman?></td>
        </tr>
        <tr>
            <th>电话</th>
            <td colspan="5"><?= $model->j_phone?></td>
            <th>电话</th>
            <td colspan="5"><?= $model->y_phone?></td>
        </tr>
        <tr>
            <th>邮箱</th>
            <td colspan="5"><?= $model->j_email?></td>
            <th>邮箱</th>
            <td colspan="5"><?= $model->y_email?></td>
        </tr>
		<tr><th colspan="12" style="text-align: center;">该合同由买卖双方共同签订，买卖双方同意按照以下规定条件和情况买卖下述商品</th></tr>
        <tr><th colspan="12" style="text-align: center;">一、货物名称、规格型号、计量单位、数量、单价、金额、供货时间</th></tr>
    </table>
        <table class="my-table2">
        <tr>
            <th colspan="1">采购单号</th>
            <th colspan="1">SKU</th>
            <th colspan="2">品名/规格说明</th>
            <th colspan="1">图片</th>
            <th colspan="1">单价(RMB)</th>
            <th colspan="1">数量(PCS)</th>
            <th colspan="1">金额</th>
            <th colspan="1">运费</th>
            <th colspan="1">优惠</th>
            <th colspan="1">采购时间</th>
            <th colspan="1">交货时间</th>
        </tr>
		<?php
        	$total_money = 0;
            $ctq_total = 0; //总采购数
            $freight_total = 0; //总运费
            $discount_total = 0; //总优惠
            foreach($products as $pur_number => $items):
                $data = PurchaseOrder::find()->where(['pur_number' => $pur_number])->one();
        ?>
                <?php
                foreach($items as $k=>$item):
                    $img = Html::img(Vhelper::downloadImg($item['sku'], $item['product_img'], 2), ['width' => '60px', 'height' => '60px']);
                    $settlement_ratio = $data->purchaseOrderPayType->settlement_ratio;

                    if( ($settlement_ratio == '100%') && in_array($data['account_type'], $quankuan) && ($data['pay_type'] == 3) ) {
                        $item['ctq'] = WarehouseResults::getInstockInfo($item['pur_number'],$item['sku'])['instock_qty_count'];
                    }
                    $total_money += $item['price']*$item['ctq'];
                    $ctq_total += $item['ctq'];
                    ?>
                    <tr>
                        <td colspan="1" style="vertical-align: middle;text-align: center;"><?= $pur_number ?></td>
                        <td colspan="1"><?= $item['sku'] ?></td> <!-- sku -->
                        <td colspan="2" ><?= $item['name'] ?></td> <!-- 品名/规格说明 -->
                        <td colspan="1"><?= $img ?></td> <!-- 图片 -->
                        <td colspan="1"><?= $item['price'] ?></td> <!-- 单价(RMB) -->
                        <td colspan="1"><?= $item['ctq'] ?></td> <!-- 数量（PCS） -->
                        <td colspan="1"><?= $item['price']*$item['ctq'] ?></td> <!-- 金额 -->

                        <?php if($k == 0):
                        $freight = $data->purchaseOrderPayType->freight;
                        $discount = $data->purchaseOrderPayType->discount;
						$freight_total += $freight;
			            $discount_total += $discount;
                        ?>

                        <?php endif; ?>
                        <td colspan="1"  style="vertical-align: middle;text-align: center;"><?= $freight ?></td> <!-- 运费 -->
                        <td colspan="1"  style="vertical-align: middle;text-align: center;"><?= $discount ?></td> <!-- 优惠额 -->
                        <td colspan="1"><?=  date('Y-m-d', strtotime($data['audit_time'])) ?></td> <!-- 采购时间 -->
                        <td colspan="1"><?=  date('Y-m-d', strtotime($data['date_eta'])) ?></td> <!-- 交货时间/预计到货时间 -->
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>

       <tr>
            <th>汇总</th>
            <td colspan="5"></td>
            <td><?= $ctq_total ?></td>
            <td><?= $total_money ?></td>
            <td><?= $freight_total ?></td>
            <td><?= $discount_total ?></td>
            <td></td>
            <td></td>
        </tr>
		<tr>
            <th rowspan="2">备注</th>
            <td>运费</td>
            <!-- 订单中有运费：甲方承担（含卸货），其他备注抓取订单中的备注 -->
			<!-- 无运费：乙方承担（含卸货）其他备注为空 -->
            <td colspan="10" style="text-align: center;"><?= $model->note_freight?></td>
        </tr>
        <tr>
            <td>其它</td>
            <td colspan="11" style="text-align: center;"><?= $model->note_other?></td>
        </tr>
		<tr>
            <th>送货地址</th>
            <td colspan="11"><?= $model->ship_method?></td>
        </tr>
		<tr>
            <th>总金额</th>
            <?php  $model->product_money?> <!-- 采购金额 -->
            <td colspan="4" style="text-align: center;">&yen;<?= $model->real_money?></td>
            <td colspan="2">总金额（大写）</td>
            <td colspan="5" style="text-align: center;"><?= Vhelper::num_to_rmb($model->real_money); ?></td>
        </tr>
        <tr>
            <th>付款方式</th>
            <td colspan="11">
                <?php if($model->dj_money != 0):?>
            	订金: <?= $model->dj_money?>
                尾款总额: <?= $model->wk_total_money?>
            	尾款: <?= $model->wk_money?>
                <?php else:?>
                采购金额：<?= $model->real_money?>&nbsp;&nbsp;
                <?php endif;?>
            	运费：<?= $model->freight?>
                优惠：<?= $model->discount?>
            </td>
        </tr>
        <tr>
            <th>付款说明</th>
            <td colspan="11"><?= $model->payment_explain ?></td>
        </tr>
		<tr>
            <th>合作要求</th>
            <td colspan="11"><?= $model->hezuo_reqiure ?></td>
        </tr>
		<tr>
            <th>乙方收款信息</th>
            <td colspan="11"><?= $model->huikuan_information ?></td>
        </tr>
        </table>
    <table class="my-table2">
		<tr><th colspan="12" style="text-align: center; ">包装要求</th></tr>
		<tr><td colspan="12"><?= $model->baozhuang_require ?></td></tr>
		<tr><th colspan="12" style="text-align: center; ">订货、交货要求</th></tr>
		<tr><td colspan="12"><?= $model->djhuo_require ?></td></tr>
		<tr><th colspan="12" style="text-align: center; ">质检要求</th></tr>
		<tr><td colspan="12"><?= $model->zhijian_require ?></td></tr>
		<tr><th colspan="12" style="text-align: center; ">售后条款</th></tr>
		<tr><td colspan="12"><?= $model->shouhou_clause ?></td></tr>
		<?php if(!empty($model->buchong_clause)):?>
        <tr><th colspan="12" style="text-align: center; ">补充条款</th></tr>
        <tr><td colspan="12"><?= $model->buchong_clause ?></td></tr>
        <?php endif;?>
		<tr><th colspan="12" style="text-align: center;color: red; ">注：合同公章扫描件具有法律效力</th></tr>
		<tr>
            <th width="100px">甲方签章</th>
            <td colspan="5">
                经办人签字：<br/><br />
                负责人签字：<br/><br />
                单位盖章：<br/><br />
                日期：
            </td>
            <th width="100px">乙方签章</th>
            <td colspan="5">
                经办人签字：<br/><br />
                负责人签字：<br/><br />
                单位盖章：<br/><br />
                日期：
            </td>
        </tr>
    </table>
<?php else: ?>
    <style type="text/css">
        .compact-content {
            width: 756px;
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
    <?php ActiveForm::begin(['id' => 'createCompace-form']); ?>
    <div class="compact-content">
        <table class="my-table2">
            <tr><th colspan="12">YIBAI TECHNOLOGY LTD采购订单合同</th></tr>
            <tr>
                <th colspan="6">甲方</th>
                <th colspan="6">乙方</th>
            </tr>
            <tr>
                <th style="width: 78px;">单位名称</th>
                <td colspan="5"><input type="text" name="Compact[j_company_name]" value="YIBAI TECHNOLOGY LTD" style="width: 100%;border: 0px;" readonly></td>
                <th style="width: 78px;">单位名称</th>
                <td colspan="5">
					<textarea name="Compact[y_company_name]" style="width: 100%;border: 0px;" readonly><?= !empty($data['supplier']) ? $data['supplier']->supplier_name : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <th>地  址</th>
                <td colspan="5">
                	<textarea name="Compact[j_address]" style="width: 100%;border: 0px;" readonly>UNIT 04,7/F BRIGHT WAY TOWER NO.33 MONG KOK RD KL</textarea>
                </td>
                <th>地  址</th>
                <td colspan="5">
					<textarea name="Compact[y_address]" style="width: 100%;border: 0px;" readonly><?= !empty($data['supplier']) ? $data['supplier']->supplier_address : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <th rowspan="2">授权代表(采购)</th>
                <td colspan="5" rowspan="2"><input type="text" name="Compact[j_linkman]" value="<?= $data['buyer'] ?>" style="border: 0px;width: 250px;"></td>
                <th>法人代表</th>
                <td colspan="5"><input type="text" name="Compact[y_corporate]" value="<?= !empty($data['supplierContent']) ? $data['supplierContent']->corporate : ''; ?>" style="border: 0px;width: 250px;"></td>
            </tr>
            <tr>
            	<th>联 系 人</th>
                <td colspan="5"><input type="text" name="Compact[y_linkman]" value="<?= !empty($data['supplierContent']) ? $data['supplierContent']->contact_person : ''; ?>" style="border: 0px;width: 250px;"</td>
            </tr>
            <tr>
                <th>电话</th>
                <td colspan="5"><input type="text" name="Compact[j_phone]" value="<?= $data['telephone']?>" style="border: 0px;width: 250px;"></td>
                <th>电话</th>
                <td colspan="5"><input type="text" name="Compact[y_phone]" value="<?= !empty($data['supplierContent']) ? $data['supplierContent']->contact_number : ''; ?>" style="border: 0px;width: 250px;"></td>
            </tr>
            <tr>
                <th>邮箱</th>
                <td colspan="5"><input type="text" name="Compact[j_email]" value="<?= $data['email']?>" style="border: 0px;width: 250px;"></td>
                <th>邮箱</th>
                <td colspan="5"><input type="text" name="Compact[y_email]" value="<?= !empty($data['supplierContent']) ? $data['supplierContent']->email : ''; ?>" style="border: 0px;width: 250px;"></td>
            </tr>
			<tr><th colspan="12" style="text-align: center;">该合同由买卖双方共同签订，买卖双方同意按照以下规定条件和情况买卖下述商品</th></tr>
            <tr><th colspan="12" style="text-align: center;">一、货物名称、规格型号、计量单位、数量、单价、金额、供货时间</th></tr>            
            <tr>
                <th>采购单号</th>
                <th>SKU</th>
                <th colspan="2">品名/规格说明</th>
                <th>图片</th>
                <th>单价(RMB)</th>
                <th>数量(PCS)</th>
                <th>金额</th>
                <th>运费</th>
                <th>优惠</th>
                <th>采购时间</th>
                <th>交货时间</th>
            </tr>
            <?php
            $total_money = 0;
            $ctq_total = 0; //总采购数
            $freight_total = 0; //总运费
            $discount_total = 0; //总优惠
            $jiaohuo_time = 0;
            foreach($data['purchaseOrderItems'] as $pur_number => $items):
                $orderData = PurchaseOrder::find()->where(['pur_number' => $pur_number])->one();

            	$jiaohuo_time = ($jiaohuo_time-strtotime($orderData['date_eta']))>0?$jiaohuo_time:strtotime($orderData['date_eta']); //计算最晚交货日期
                foreach($items as $k=>$item):
                    $img = Html::img(Vhelper::downloadImg($item['sku'], $item['product_img'], 2), ['width' => '60px', 'height' => '60px']);
                    if( ($data['settlement_ratio'] == '100%') && in_array($data['account_type'], $quankuan) && ($data['pay_type'] == 3) ) {
                        $item['ctq'] = WarehouseResults::getInstockInfo($item['pur_number'],$item['sku'])['instock_qty_count'];
                    }
                    
                    $total_money += $item['price']*$item['ctq'];
                    $ctq_total += $item['ctq'];
            ?>
                    <tr>
                        <?php if($k == 0): ?><td rowspan="<?= count($items) ?>" style="vertical-align: middle;text-align: center;"><?= $pur_number ?></td><?php endif; ?>
                        <td><?= $item['sku'] ?></td> <!-- sku -->
                        <td colspan="2"><?= $item['name'] ?></td> <!-- 品名/规格说明 -->
                        <td><?= $img ?></td> <!-- 图片 -->
                        <td><?= $item['price'] ?></td> <!-- 单价(RMB) -->
                        <td><?= $item['ctq'] ?></td> <!-- 数量(PCS) -->
                        <td><?= $item['price']*$item['ctq'] ?></td> <!-- 金额 -->
                        <?php if($k == 0):
                        $freight = $item->purchaseOrderPayType->freight;
                        $discount = $item->purchaseOrderPayType->discount;
						$freight_total += $freight;
			            $discount_total += $discount;
                        ?>
                            <td rowspan="<?= count($items) ?>" style="vertical-align: middle;text-align: center;"><?= $freight ?></td> <!-- 运费 -->
                            <td rowspan="<?= count($items) ?>" style="vertical-align: middle;text-align: center;"><?= $discount ?></td> <!-- 优惠额 -->
                        <?php endif; ?>
                        <td><?= date('Y-m-d', strtotime($orderData['audit_time'])) ?></td> <!-- 采购时间 -->
                        <td><?= date('Y-m-d', strtotime($orderData['date_eta'])) ?></td> <!-- 交货时间/预计到货时间 -->
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <tr>
                <th>汇总</th>
                <td colspan="5"></td>
                <td><?= $ctq_total ?></td>
                <td><?= $total_money ?></td>
                <td><?= $freight_total ?></td>
                <td><?= $discount_total ?></td>
                <td></td>
                <td></td>
            </tr>
			<tr>
                <th rowspan="2">备注</th>
                <td>运费</td>
                <!-- 订单中有运费：甲方承担(含卸货)，其他备注抓取订单中的备注 -->
				<!-- 无运费：乙方承担(含卸货)其他备注为空 -->
                <td colspan="10" style="text-align: center;"><textarea name="Compact[note_freight]" rows="1" style="width: 100%;border: 0px;" readonly><?= (int)$freight_total>0?'甲方承担(含卸货)':'乙方承担(含卸货)'; ?></textarea></td>
            </tr>
            <tr>
                <td>其它</td>
                <td colspan="11" style="text-align: center;"><textarea name="Compact[note_other]" rows="3" style="width: 100%;"></textarea></td>
            </tr>
            <tr>
                <th>送货地址</th>
                <td colspan="11">
<textarea name="Compact[ship_method]" rows="1" style="width: 100%;border: 0px;" readonly>
广东省东莞市塘厦镇科苑城科苑大道16号君盈购物广场一楼FBA仓101 <?= $data['buyer'] ?>转赵容17817013672
</textarea>
                </td>
            </tr>
            <tr>
                <th>总金额</th>
                <td colspan="4" style="text-align: center;">
                	<input type="hidden" name="Compact[product_money]" id="product_money" value="<?= $total_money ?>">
                	&yen;<input type="text" name="Compact[real_money]" value="<?= $total_money+$freight_total-$discount_total ?>" style="width: 100px;border: 0px;" readonly>
                </td>
                <td colspan="2">总金额（大写）</td>
                <td colspan="5" style="text-align: center;"><?= Vhelper::num_to_rmb($total_money+$freight_total-$discount_total); ?></td>
            </tr>
            <?php $plan = PurchaseCompact::FbaPaymentPlan($data['settlement_ratio'], $total_money, $freight_total, $discount_total); ?>
            <tr>
                <th>付款方式</th>
                <td colspan="11">
                    <?php if (!empty($plan['dj'])):?>
                	订金: <input type="text" name="Compact[dj_money]" value="<?= $plan['dj'] ?>" style="width: 60px;border: 0px;" readonly>
                    尾款总额: <input type="text" name="Compact[wk_total_money]" value="<?= $plan['wwk'] ?>" style="width: 60px;border: 0px;" readonly>
                	尾款: <input type="text" name="Compact[wk_money]" value="<?= $plan['wk'] ?>" style="width: 60px;border: 0px;" readonly>
                    <?php else:?>
                    采购金额：<?= $total_money+$freight_total-$discount_total ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="hidden" name="Compact[dj_money]" value="<?= $plan['dj'] ?>" style="width: 60px;border: 0px;" readonly>
                    <input type="hidden" name="Compact[wk_money]" value="<?= $plan['wk'] ?>" style="width: 60px;border: 0px;" readonly>
                    <input type="hidden" name="Compact[wk_total_money]" value="<?= $plan['wwk'] ?>" style="width: 60px;border: 0px;" readonly>
                    <?php endif;?>
                	运费：<input type="text" name="Compact[freight]" value="<?= $freight_total; ?>" style="width: 60px;border: 0px;" readonly>
                    优惠：<input type="text" name="Compact[discount]" value="<?= $discount_total; ?>" style="width: 60px;border: 0px;" readonly>
                </td>
            </tr>
            <?php
            $texts = [];
            $_ratio0 = $_ratio1 = 0;
            $_ratio = explode('+', $data['settlement_ratio']);
            if ((count($_ratio) == 2)) {
                $_ratio0 = $_ratio[0];
                $_ratio1 = $_ratio[1];
            }

			$tests[1] = "、付款方式：" . SupplierServices::getSettlementMethod($data['account_type']) . "<br />";
			$tests[2] = "、货款应在货物送达甲方指定地点并经甲方验收合格，且甲方收到乙方正式等额发票（包括但不限于增值税发票，采购订单合同或者形式发票等）后开始启动付款期，乙方出示大货图发于甲方安排发货<br />";
			$tests[3] = "、甲方向乙方预付订单" . $_ratio0 . "的货款，进行生产。<br />";
			$tests[4] = "、大货完成后支付".$_ratio1."的尾款，尾款支付后当天算起，乙方必须在三天内发货并出示物流信息。乙方出示大货图发于甲方安排发货。<br />";
			$tests[5] = "、货款应在货物送达甲方指定地点并经甲方验收合格，且甲方收到乙方正式等额发票（包括但不限于增值税发票，采购订单合同或者形式发票等）后开始启动".$_ratio1."尾款付款期，乙方出示大货图发于甲方安排发货<br />";
			$tests[6] = "、大货完成后，拍大货图由甲方确认后申请全款；<br />";
			$tests[7] = "、全款支付后当天算起，乙方必须在三天内发货并出示物流信息。<br />";
			$tests[8] = "、乙方未按时交货，自逾期起需每日向甲方支付全款金额的0.8‰作为违约滞纳金。其它未尽事宜，大家友好协商解决。<br />";
			$tests[9] = "、如甲方已支付预付款项，乙方则需配合甲方处理出口退税的相关事宜。<br />";
			$tests[10] = "、甲方的付款行为不得视为对乙方承担产品质量保证责任及履行本协议项下其他义务的豁免。";


            // if( ($data['settlement_ratio'] == '100%') && in_array($data['account_type'], $quankuan) && ($data['pay_type'] == 3) ) {
            if( (count($_ratio) == 1) && in_array($data['account_type'], $quankuan) && ($data['pay_type'] == 3) ) {
            	//全款账期 账期，银行卡转账 100% 1.2.9.10
            	//$data['account_type'] 1.6.7.8.9.16
            	//$data['pay_type'] 3
                $payment_explain = Vhelper::pluck($tests, [1, 2, 9, 10]);
            // } elseif( ($data['settlement_ratio'] == '30%+70%') && ($data['account_type']==2) && $data['pay_type']==3 ) {
            } elseif( (count($_ratio) == 2) && ($data['account_type']==2) && $data['pay_type']==3 ) {
            	//预付订金尾款 款到发货，银行卡转账  30%+70% 3.4.8.9.10
            	//$data['account_type'] 2
            	//$data['pay_type'] 3
                $payment_explain = Vhelper::pluck($tests, [3, 4, 8, 9, 10]);
            // } elseif( ($data['settlement_ratio'] == '100%') && ($data['account_type']==2) && ($data['pay_type']==3) ) {
            } elseif( (count($_ratio) == 1) && ($data['account_type']==2) && ($data['pay_type']==3) ) {
            	//预付全款 款到发货，银行卡转账 100% 4.7.8.9.10
            	//$data['account_type'] 2
            	//$data['pay_type'] 3
                $payment_explain = Vhelper::pluck($tests, [6, 7, 8, 9, 10]);
            // } elseif ( ($data['settlement_ratio'] == '30%+70%') && in_array($data['account_type'], $dj_zhuangqi) && $data['pay_type']==3 ) {
            } elseif ( (count($_ratio) >= 2) && in_array($data['account_type'], $dj_zhuangqi) && $data['pay_type']==3 ) {
            	//预付定金尾款账期 订金+尾款账期，银行卡转账 30%+70% 3.5.9.10
            	//$data['account_type']
            	//$data['pay_type'] 3
                $payment_explain = Vhelper::pluck($tests, [3, 5, 9, 10]);
            } else {
                $payment_explain = '没有该规则，请重新设置';
            }

            ?>
            <tr>
                <th>付款说明</th>
                <td colspan="11">
                	<textarea name="Compact[payment_explain]" rows="5" style="width: 100%;" readonly="readonly"><?= $payment_explain ?></textarea>
                </td>
            </tr>
            <tr>
                <th>合作要求</th>
                <td colspan="11">
                	<textarea name="Compact[hezuo_reqiure]" rows="3" style="width: 100%;" readonly="readonly">
如果有我司工作人员索要回扣，影响正常合作，请致电我司总经理电话：<br />【胡范金15012616166（微信）   庄俊超 13713710103（微信）】
                	</textarea>
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
                <th>乙方收款信息</th>
                <td colspan="11">
                	<textarea name="Compact[huikuan_information]" rows="3" style="width: 100%;" readonly="readonly">收款账号：<?= $account ?>  户名： <?= $account_name ?>  开户行：<?= $payment_platform_branch ?></textarea>
                </td>
            </tr>
			<tr><th colspan="12" style="text-align: center;">包装要求</th></tr>
			<tr>
				<td colspan="12">
<textarea name="Compact[baozhuang_require]" rows="21" style="width: 100%;background-color: #E2EFD9;" readonly="readonly">
<b>一、包装要求：</b><br />
1、所有产品按甲方要求的包装袋包装，卖方送货产品必须粘贴买方提供的条码，标识的内容必须与实物的款式、颜色、码数、规格等信息相符，条码标准样式按各仓库规定执行；<br />
<b>二、装箱要求：</b><br />
1、同一包装箱产品必须是同一订单的产品，同订单不同SKU非特殊情况不可混装，如有混装需用不同包装进行隔离区分<br />
<b>三、外箱要求：</b><br />
1、外箱材质要求结实牢固，符合国际长途运输条件，无钉,不能破损，受潮，变形；外箱封口透明胶带需按“工”字型进行密封包装；<br />
2、按买方要求格式印刷箱唛，将买方提供的电子档用A4纸打印，箱唛请贴在外箱两侧(A4纸贴的箱唛，需要用透明胶纸固定，避免运输过程中脱落)；字迹清晰工整，每箱需满箱，不满一箱需割箱处理；<br />
3、未按买方要求进行包装产生的二次包装操作费由卖方承担；<br />
<b>四、送货要求：</b><br />
1、卖方每批送货产品需附有与实物一致的送货单；按照订单号分开打印附在箱内，并按照实际交货内容标明买方名称,订单号,产品SKU,产品型号,数量,订单总箱数等,无需显示单价；<br />
2、产品包装及箱内不得出现和产品无关或其他未经买方书面确认的异物，否则如因该异物引起的运输、存储等过程中的人身安全、意外伤害等产生的损失和责任均由卖方承担，给买方造成其他损失的，卖方负责全额赔偿。<br />
</textarea>
				</td>
			<tr><th colspan="12" style="text-align: center;">订货、交货要求</th></tr>
			<tr>
				<td colspan="12">
<textarea name="Compact[djhuo_require]" rows="23" style="width: 100%;background-color: #E2EFD9;" readonly="readonly">
一、双方往来文件可以通过寄送或邮件进行，通过本合同约定的地址、邮件往来的文件对双方有效。通过邮件往来的盖章的扫描件具有法律效力。<br />
二、1.买方向卖方下达采购订单，卖方需在2个工作日内予以回复确认，同时将该订单签字盖章后回传。下单、回复、订单回传等，双方均可通过邮件往来，已通邮件往来确认的订单等对双方具有法律效力。<br />
   2.交货日期：<span style=color:red><?= date('Y年m月d', $jiaohuo_time)?></span>日前交货。卖方应严格按照订单约定日期交货，如交期延迟,乙方应提前至少5个工作日通知甲方协商，卖方需按照该SKU延迟交货数量采购总金额的0.8‰日向买方支付违约金，且买方有权取消订单或更改订单；如甲方要求取消订单且已支付预付款，乙方需在3个工作日内根据原支付途径退回预付款项，并支付甲方订单金额的10%作为不能履行合同违约金；<br />
三、乙方按经甲方签字确认的样品安排生产及交货，乙方负责全检、甲方负责抽检.乙方每批次到货甲方仓库时，因发现产品因质量问题而影响到甲方入仓进度，需要甲方配合全检或做二次包装或额外要求甲方质检人员配合时其它请求时，甲方有权要求乙方向甲方支付相关人工检测费用或额外加工费。<br />
四、1.乙方生产大货前，应提供样品给甲方确认，方可生产发货。提供给甲方的每批次产品质量与样品质量等级完全一致，乙方应附随货物书面提供出厂合格证，质量检验报告，送货单等信息。乙方必须保证产品质量，如有非人为损坏的质量问题产品，乙方应负责换货。<br />
    2.经甲方确认通过的样品进行任何产品升级、材料变更、参数功能调整、包装修改、配件调整等，须经甲方确认方可进行。否则甲方一旦发现将有权要求乙方支付订单金额的30%作为赔偿。<br />
五、乙方有义务按甲方要求提供商品的材质成分、物料清单、必要的出口认证资料(包括但不仅限于CE,FCC,FDA,FSE,ROHS,C-TICK,HDMI,UN108.3,En/en,CSA,IC,EK,MTC,SASO,IRAM,NOM),说明书等，乙方提供及全力配合后期的证询并协助甲方进行清关，如因资料不全、产品质量问题或虚假认证等问题导致无法正常清关，乙方承担全部责任（包括但不限于货物成本、运输成本、报关费用、销毁费用等）。<br />
六、卖方应避免其提供的产品有任何知识产权侵权行为的发生，因产品侵权问题产生的全部责任由卖方承担，由此给买方造成任何损失的，卖方负责全额赔偿 。
</textarea>
				</td>
			</tr>
			<tr><th colspan="12" style="text-align: center;">质检要求</th></tr>
			<tr>
				<td colspan="12">
<textarea name="Compact[zhijian_require]" rows="23" style="width: 100%;background-color: #E2EFD9;" readonly="readonly">
一、甲方所下达订单，乙方需在交货期前5天完成并通知甲方采购员，以便甲方确认是否按排人员前去乙方验货。所有甲方人员验货时，乙方均需提供OQC检验报表以及其他相关生产资料，供甲方验货人员参考。<br/>二、买方验货前，卖方须保证所有产品务必按该订单要求生产包装完毕，卖方进行自检合格后，同时附上装箱清单(装箱清单的SKU顺序须与订单上的SKU顺序保持一致）、自检合合格报告。<br />三、甲方有权对乙方的生产现场、生产流程、作业方式等进行审核，并提出改善建议；乙方对甲方的质量稽查须予以支持和配合，不允许有以任何形式隐瞒产品质量的现象。<br/>
四、当满足以下条件之一时，甲方可安人员排对产品及乙方的生产进行稽核，并对乙方提出生产改善建议<br/>
1、初次下单采购的产品（甲方条件允许的情况下，样品审核阶段，甲方将对乙方进行生产考核）；<br/>
2、对于功能复杂、需对产品功能做严格测试的产品；<br/>
3、在销售过程中，产品在某个问题上出现批量异常或各类型问题累计过多；<br/>
4、甲方未安排人员去乙方检验，多次来货后检测合格率偏低的产品。<br/>
五、经检验当满足以下条件之一时，甲方有权拒收产品<br/>
1、甲方正常提出验货，乙方拒不配合；<br/>
2、甲方在检验过程中，不良品超出AQL验收标准，乙方拒不全检；<br/>
3、甲方在检验过程中，不良品未超出AQL验收标准，乙方拒不更换不良品；<br/>
4、甲方提出改善建议，并与乙方达成一致后，再次订货乙方未将改善方案实施。<br/>
六、验收标准：<br/>
1、确定被检验货品数量（假设数量为500）；<br/>
2、确定抽样方案，在没有特别要求下，应按“一般检验标准Ⅱ”进行抽样 ；<br/>
3、在合格质量水平栏找出要求的AQL值，在没有特别要求下，以AQL值为1.5作检验标准；<br/>
七、乙方需配合甲方进行跌落测试（测试要求为1.5米6面三角）；乙方需保质保量向买方交货，单批产品验货1次及以上不合格的，每增加一次验货,卖方需承担500RMB/次的误工和交通费用；乙方需接到甲方采购人员验货合格通知后方可安排送货；<br/>
</textarea>
				</td>
			</tr>
			<tr><th colspan="12" style="text-align: center;">售后条款</th></tr>
			<tr>
				<td colspan="12">
<textarea name="Compact[shouhou_clause]" rows="12" style="width: 100%;background-color: #E2EFD9;" readonly="readonly">
一、 乙方产品的质保期为从甲方仓库人员签收之日起14个月，无论双方因任何原因终止合作，合作期间乙方提供的产品存在售后问题，乙方务必依据售后规定进行处理。<br />
二、在产品保质期内，甲方客户因商品质量、包装破损、配件缺失，少说明书等问题产生的售后配件需求或维修费用，乙方应免费补发相关配件及承担相应的国内外物流费用或支付甲方等值货款。<br />
三、在产品保质期内，如甲方向乙方提供甲方客户因商品质量、功能缺失、包装、配件等问题给甲方造成的售后退件及投诉的相关数据（因甲方销售渠道为线上电商平台，投诉截图、差评截图或其它对甲方店铺造成直接或间接损失的亚马逊平台数据，皆可作为凭证），乙方需在1个工作日内跟进处理，给予方案和技术上的指导，造成甲方客户投诉率（包括但不限于产品功能、性能缺失、产品做工工艺、产品不能自动升级兼容对应的质量投诉）在3%以上的，乙方须对甲方进行1:1（被投诉货物数，以亚马逊平台数据为准）赔款处理，且承担相关头程运输费用,FBA仓储费用等实际损失，并在10个工作日将赔款支付甲方；若要求甲方对不良品进行返还处理的，乙方需承担国内外退换货费用及产生的其他相关费用。如退款率过高对甲方的店铺账号造成的损失，甲方有权保留对乙方的追责权利。
</textarea>
				</td>
			</tr>
			<tr><th colspan="12" style="text-align: center;">补充条款</th></tr>
			<tr>
				<td colspan="12"><textarea name="Compact[buchong_clause]" rows="3" style="width: 100%;"></textarea></td>
			</tr>
			<tr><th colspan="12" style="text-align: center;color: red">注：合同公章扫描件具有法律效力</th></tr>
			<tr>
                <th>甲方签章</th>
                <td colspan="5">
                    经办人签字：<br/>
                    负责人签字：<br/>
                    单位盖章：<br/>
                    日期：
                </td>
                <th>乙方签章</th>
                <td colspan="5">
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
            <input type="hidden" name="Compact[is_drawback]" value="1">

            <input type="hidden" name="System[tid]" value="<?= $tid ?>">
            <input type="hidden" name="System[pos]" value="<?= $pos ?>">
            <input type="hidden" name="System[platform]" value="<?= $platform ?>">

            <button type="submit" class="btn btn-success">确认生成合同</button>
            <a href="javascript:void(0)" style="display: inline-block; margin-left: 25px;" onclick="javascript :history.back(-1)">返回上一步</a>
        </div>
    </div>

    <?php ActiveForm::end();?>
<?php endif; ?>

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