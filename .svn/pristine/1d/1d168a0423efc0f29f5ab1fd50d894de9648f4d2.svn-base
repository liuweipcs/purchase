<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/10
 * Time: 17:57
 */
?>
<div>
    <span>关联请款单号及付款状态</span>&nbsp;&nbsp;<span class="glyphicon glyphicon-refresh refresh" style="color: red" tran_no="<?=$tran?>">刷新付款单状态</span>
</div>
<table class="table">

    <thead>
        <th>采购（合同）单号</th>
        <th>请款单号</th>
        <th>付款状态</th>
    </thead>
    <tbody>
        <?php if (!empty($pay_info)){ ?>
            <?php foreach ($pay_info as $pay){?>
                <tr>
                    <td><?=$pay['pur_number']?></td>
                    <td><?=$pay['requisition_number']?></td>
                    <td><?=\app\services\PurchaseOrderServices::getPayStatusType($pay['pay_status'])?></td>
                </tr>
            <?php }?>
        <?php }else{?>
            <tr>
                <td colspan="3">无关联请款单信息</td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<table class="table">
    <caption>富友请款信息</caption>
    <?php
    if(!empty($data['responseBody']['resultSet']['result'])){
        $ufdata=$data['responseBody']['resultSet']['result'];
        ?>
        <tr>
            <td >富友流水号</td>
            <td><?= empty($ufdata['fuiouTransNo']) ? '' : $ufdata['fuiouTransNo']; ?></td>
            <td>商户流水号</td>
            <td><?= empty($ufdata['transNo']) ? '' : $ufdata['transNo']; ?></td>
            <td >交易时间</td>
            <td><?= empty($ufdata['transTime']) ? '' : $ufdata['transTime']; ?></td>
        </tr>
        <tr>
            <td>对方名称</td>
            <td><?= empty($ufdata['oppositeName']) ? '' : $ufdata['oppositeName']; ?></td>
            <td >对方银行卡号</td>
            <td><?= empty($ufdata['oppositeBankCardNo']) ? '' : $ufdata['oppositeBankCardNo']; ?></td>
            <td >交易类型</td>
            <td><?php
                $typeArray=[
                    'transfer'=>'转账（到可用余额）',
                    'tfreeze' =>'转账后直接冻结（到冻结金额）',
                    'unfreeze'=>'解冻',
                    'gateway' =>'网关支付',
                    'bankcard'=>'转账至银行卡'];
                echo empty($typeArray[$ufdata['transType']]) ? '' : $typeArray[$ufdata['transType']]; ?></td>
        </tr>
        <tr>
            <td>交易金额(分)</td>
            <td><?= empty($ufdata['amt']) ? '' : $ufdata['amt']; ?></td>
            <td>实际到账金额(分)</td>
            <td><?= empty($ufdata['actualAmt']) ? '' : $ufdata['actualAmt']; ?></td>
            <td>手续费(分)</td>
            <td><?= empty($ufdata['fee']) ? '' : $ufdata['fee']; ?></td>
        </tr>
        <tr>
            <td>冻结状态</td>
            <td>
                <?php
                    $freetype = [
                        'Y'=>'冻结',
                        'N'=>'未冻结',
                        'A'=>'已解冻'];
                    echo   empty($freetype[$ufdata['freezeSt']]) ? '' : $freetype[$ufdata['freezeSt']];
                ?>
            </td>
            <td>交易状态</td>
            <td><?php
                $tranType=[
                    '00'=>'交易处理中',
                    '01'=>'交易成功',
                    '02'=>'交易失败'];
               echo  empty($tranType[$ufdata['transSt']]) ? '' : $tranType[$ufdata['transSt']]; ?></td>
            <td>入账状态</td>
            <td>
                <?php
                    $inOutType = [
                        '5002'=>'已受理',
                        '5003'=>'已审核，待划款',
                        '5004'=>'划款中',
                        '5005'=>'划款成功',
                        '5006'=>'划款失败',
                        '5007'=>'划款失败，资金已退回',
                        '5008'=>'划款成功',
                        '5009'=>'状态未知'];
                    echo empty($inOutType[$ufdata['inOutSt']]) ? '' : $inOutType[$ufdata['inOutSt']];
                ?>
            </td>
        </tr>
        <tr>
            <td>资金流向</td>
            <td>
                <?php
                    $typeArray=['00'=>'入账','01'=>'出账'];
                    echo  empty($typeArray[$ufdata['inOut']]) ? '' : $typeArray[$ufdata['inOut']];
                ?>
            </td>
            <td>失败原因</td>
            <td><?= empty($ufdata['reason']) ? '' : $ufdata['reason']; ?></td>
            <td>备注</td>
            <td><?= empty($ufdata['remark']) ? '' : $ufdata['remark']; ?></td>
        </tr>
    <?php } else{?>
        <tr>
            <td style="text-align: center">
                <?=empty($data['responseBody']['rspDesc']) ? '':$data['responseBody']['rspDesc'];?>
            </td>
        </tr>
    <?php }?>
</table>
<?php
$refreshUrl = \yii\helpers\Url::toRoute(['get-fuiou-pay-info']);
$js = <<<JS
$('.refresh').on('click',function() {
        var tran_no = $(this).attr('tran_no');
        layer.confirm('是否根据富友请款状态刷新所有关联的请款单状态',{title:"刷新提示"},function() {
          $.post('{$refreshUrl}',{tran_no:tran_no},function(data) {
                layer.msg(data);
          });
        },function() {
            
        });
    });
JS;
$this->registerJs($js);
?>