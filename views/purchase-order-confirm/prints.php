<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\config\Vhelper;

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
                <td colspan="2" style="font-size: 20px;font-weight: 900;">深圳市易佰网络科技有限公司</td>
            </tr>
            <tr>
                <td colspan="2">Shenzhen YiBai Tech Co., Ltd</td>
            </tr>
            <tr>
                <td colspan="2">TEL:  0755-85274761
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    FAX:  0755-85274761</td>
            </tr>
            <tr>
                <td colspan="2">采购员：弗兰克
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    联系电话：</td>
            </tr>
            <tr>
                <td colspan="2">地址：深圳市宝安区民治街道办民康路蓝坤大厦8层812室 </td>
            </tr>
            <tr>
                <td colspan="2" style="font-size: 20px;font-weight: 900;">采 购 订 单</td>
            </tr>
        </table>
        <!-- 供方联系方式 -->
        <table class="list" id="supplier_content">
            <tr id="supplier_content_tr">
                <td>供方：都是
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    电话：</td>
                <td>PO单号:     PO1751704110001</td>
            </tr>
            <tr>
                <td>地址：
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    联系人： </td>
                <td>订购日期：2017-04-11</td>
            </tr>
        </table>
        <table class="list">
            <tr id="purchase_title">
                <td>序号</td>
                <td align=center>SKU</td>
                <td>厂家型号</td>
                <td>英文名称</td>
                <td>中文名称</td>
                <td>配件信息</td>
                <td>单位</td>
                <td>数量</td>
                <td>单价</td>
                <td>金额</td>
                <td>入仓国家</td>
                <td>交货日期</td>
                <td>备注</td>
            </tr>
            <tr>
                <td>1</td>
                <td align=center><img width="87" height="69" src="http://dev-wms.eccang.com/default/system/view-product-img/id/11809?xx.jpg"><br/>00000</td>
                <td></td>
                <td>00000</td>
                <td>00000</td>
                <td></td>
                <td></td>
                <td>4</td>
                <td>100.00 RMB</td>
                <td>400.00 RMB</td>
                <td>美国</td>
                <td>
                    -0001-11-30
                </td>
                <td></td>
            </tr>

            <tr>
                <td colspan="13" style = "text-align: left;">
                    SKU种类：1
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    SKU总数：4
                </td>
            </tr>
            <tr>
                <td colspan="4">合计金额人民币（大写）:肆佰元整</td>
                <td colspan="6">合计金额人民币（小写）：400.00 RMB</td>
                <td colspan="3">付款方式:现金</td>
            </tr>
            <tr>
                <td colspan="13" align=left>运费:0.00&nbsp;RMB</td>
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

