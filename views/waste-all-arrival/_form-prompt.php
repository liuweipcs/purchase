<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;

/* @var $this yii\web\View */
/* @var $model app\models\OverseasWarehouseGoodsTaxRebate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="panel panel-success">
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <!--  已解决的问题 -->
            <li class="border_style" style="color: #141a1d;">在【海外仓-采购单】中用【浦江县富德盛水晶有限公司】去搜，但是出来的是【周造强】这家公司的单
                <ol>
                    <li>这种情况就是供应链那边整合了供应商，之前那家供应商停用了</li>
                </ol>
            </li>
            <li class="border_style" style="color: #141a1d;">
                如果下拉框中搜索不到【供应商】名称：
                <ol>
                    <li>有空格</li>
                    <li>或者是不是这家供应商禁用了【供货商管理】</li>
                </ol>
            </li>
            <li class="border_style" style="color: #141a1d;">【义乌市兆波电子商务商行】这个供应商新建的，且【供应链】确认审核了，但【采购系统】中还是搜不到这个供应商？
                <ol>
                    <li>找王曼，或者王范彬，或者符圮珍 在erp中添加供应商</li>
                    <li>再在采购系统中拉取过来</li>
                    <li>【供货商管理-供货商管理-拉取erp供应商】要求：知道供应链审核通过的供应商的名称</li>
                </ol>
            </li>

            <li class="border_style" style="color: #141a1d;">如果采购员下拉框中搜不到【采购员】【给用户开权限】：
                <ol>
                    <li>有空格</li>
                    <li>在【采购用户管理-新增】用户名（采购员）、采购小组（1组）、级别（组员）、用户类型（国内采购）</li>
                </ol>
            </li>
            
            <li class="border_style" style="color: #141a1d;">新增仓库【采购仓中搜索不到仓库】：
                <ol>
                    <li>先找【谭美清】-先让物流那边加上仓库，获取到仓库编码</li>
                    <li>也可在erp上面的【仓库管理-》仓库列表】中查到仓库编码</li>
                    <li>再在【仓库管理-》添加仓库】中添加仓库</li>
                </ol>
            </li>

            <li class="border_style" style="color: #141a1d;">反馈采购确认无法更换结算方式去找【供应链】
                <ol>
                    <li></li>
                </ol>
            </li>
            
            <li class="border_style" style="color: #141a1d;">【通途】的单找【陈望】 对接</li>
            <li class="border_style" style="color: #141a1d;">作废的订单，仓库还可以入吗？【陈望】 对接</li>
            <li class="border_style" style="color: #141a1d;">如果采购系统订单中的sku和仓库中的sku的数量或种类不一致
                <ol>
                    <li>对接人：陈望、王开伟</li>
                </ol>
            </li>
            <li class="border_style" style="color: #141a1d;">关于在途问题
                <ol>
                    <li>对接人：陈望</li>
                    <li>修改在途：范晶晶</li>
                </ol>
            </li>
            <li class="border_style">查销量 -- 张凡</li>
            <li class="border_style">采购erp图片不同步 -- 【产品管理-》产品列表-》图片重置】</li>
            <!-- 可自己操作的 -->
            <li class="border_style" style="color: #141a1d;">修改采购单中的供应商
                <ol>
                    <li>找到供应商表【pur_supplier】中的供应商名【supplier_name】和供应商编码【supplier_code】</li>
                    <li>将在供应商表中找到数据，替换采购单【pur_purchase_order】中的，供应商编码【supplier_code】和供应商名字【supplier_name】</li>
                    <li>将在供应商表中找到数据，替换支付表【pur_purchase_order_pay】中的，供应商编码【supplier_code】和供应商名字【supplier_name】</li>
                </ol>
            </li>
            <li class="border_style" style="color: #141a1d;">修改订单状态【pur_purchase_order  purchas_status】</li>
            <li class="border_style" style="color: #141a1d;">修改付款状态【pur_purchase_order pur_purchase_order_pay  pay_status】</li>
            <li class="border_style" style="color: #141a1d;">修改收款状态【pur_purchase_order pur_purchase_order_pay  pay_status pur_purchase_order_receipt】</li>
            <li class="border_style" style="color: #141a1d;">sku单价【pur_product_supplier pur_supplier_quotes】</li>
        </ol>
    </div>
</div>
<?php
$this->beginContent('@app/views/layouts/waste.php');
$this->endContent();
?>