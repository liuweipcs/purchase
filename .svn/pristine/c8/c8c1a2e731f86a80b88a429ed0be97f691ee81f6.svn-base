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
        <h4>温馨小提示:相应的问题对应的对接人</h4>
        <ol style="color:red;font-weight: bold;">
            <li class="border_style" style="color: #141a1d;">抓取物流信息
                <ol>
                    <li>系统管理-阿里账号管理：根据：账号和绑定用户  获取到绑定用户的ID</li>
                    <!-- <li>之后运行：【http://caigou.yibainetwork.com/v1/alibaba/get-logistics?account=177】 这个链接（177绑定用户的ID）</li> -->
                </ol>
            </li>
            <li class="border_style" style="color: #141a1d;">采购系统中无该sku或状态不对 -- 【有待更新】
                <ol>
                    <!-- <li>http://120.78.243.154/services/products/product/productsall/sku/ES-TJ00106-01</li> -->
                    <!-- <li>http://dc.yibainetwork.com/index.php/products/insertProductToMongoBysku?sku=QC05271</li> -->
                    <!-- <li>http://dc.yibainetwork.com/index.php/products/pushProductToPurchase</li> -->
                </ol>
            </li>

            <li class="border_style">徐梦梦  国内</li>
            <li class="border_style">【采购建议数量】 = 日权均销量（sales_avg）* 安全备货天数15+缺货数量（left_stock）- 在途库存（on_way_stock）- 可用库存（available_stock）</li>
            <li class="border_style">海外仓-采购需求汇总：采购需求推送到【海外仓】
                <ol>
                    <li>要有采购单关联才推送</li>
                    <li>你先看这些需求和采购单关联上没</li>
                </ol>
            </li>
            <li class="border_style">仓库的全部到货是说已经全部收下了这批货 然后去质检 然后被删除 再就没有然后的上架了 上架了数据才会传到采购系统</li>
            <li class="border_style">【采购建议】当sku生成订单后，采购建议中的sku状态没有变
                <ol>
                    <li>可能是你撤销了一个采购单，包含这个sku</li>
                    <li>因为：撤销的sku，会回退到最初始的状态</li>
                </ol>
            </li>
            <!--  未解决  -->
            <li class="border_style">在途库存为0  问题：？？ -- 周美霞
                <ol>
                    <li>pur_stock【怎么看】</li>
                    <li>采购准备-》采购建议</li>
                    <li>采购管理-》采购单-》详情</li>
                </ol>
            </li>
            <li class="border_style">国内采购？？ -- 刘玲俐
                <ol>
                    <li>JM02843  这个sku  19号买了  21号买了  22买了  今天又跳出来了</li>
                    <li>同一个sku连续几天下单</li>
                </ol>
            </li>
            <li class="border_style">采购系统中修改订单状态，仓库那边未更新（同步）-- ？？
                <ol>
                    <li>例如：采购系统中将-部分到货不等待剩余-变成-部分到货等待剩余   仓库那边还是：部分到货不等待剩余</li>
                    <li>将订单中的is_push,状态改为0 ？？</li>
                </ol>
            </li>
            <li class="border_style">供应商被禁用了，找谁？？</li>
            <li class="border_style">erp显示在售   采购建议显示刚买样  产品表中显示在售 -- 刘玲俐
                <ol>
                    <li>可直接修改状态</li>
                    <li>注意：如果有  导入的需求，要重新执行 采购建议计划？？</li>
                </ol>
            </li>
            <li class="border_style">关务这边显示：不退税，作废  采购这边显示：退税，已付款、全到货 -- 邓仪
                <ol>
                    <li>怎么和关务那边同步数据？？</li>
                </ol>
            </li>
        </ol>
    </div>
</div>
<?php
$this->beginContent('@app/views/layouts/waste.php');
$this->endContent();
?>