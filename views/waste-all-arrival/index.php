<?php
use \mdm\admin\components\Helper;

$this->title = Yii::t('app', '采购系统异常处理');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$username = Yii::$app->user->identity->username;
$route = ['刘伟', '王瑞'];
?>
<div class="account-l fl">
    <a class="list-title">账户概览</a>
    <ul id="accordion" class="accordion">
        <li>
            <div class="link"><i class="fa fa-leaf"></i>拉取数据<i class="fa fa-chevron-down"></i></div>
            <ul class="submenu">
                <?php
                    // 拉取sku ok
                    if(Helper::checkRoute('select-pull-erp-sku')) {
                        echo '<li><a href="select-pull-erp-sku" target="myFrameName">拉取sku</a></li>';
                    }

                    //拉取物流信息 ok
                    if(Helper::checkRoute('select-pull-logistics')) {
                        echo '<li><a href="select-pull-logistics" target="myFrameName">拉取物流信息</a></li>';
                    }
                ?>
            </ul>
        </li>
        <li>
            <div class="link"><i class="fa fa-shopping-cart"></i>修改采购单相关状态<i class="fa fa-chevron-down"></i></div>
            <ul class="submenu">
                <?php
                // 采购单--修改仓库 ok
                if(Helper::checkRoute('select-purchase-order-warehouse-code')) {
                    echo '<li><a href="select-purchase-order-warehouse-code" target="myFrameName">采购单--仓库</a></li>';
                }

                // 采购单：修改采购单状态 ok
                if(Helper::checkRoute('select-purchas-status')) {
                    echo '<li><a href="select-purchas-status" target="myFrameName">采购单--采购单和退款状态和采购员</a></li>';
                }
                // 采购单收款表：修改收款状态 ok
                if(Helper::checkRoute('select-purchase-order-receipt-pay-status')) {
                    echo '<li><a href="select-purchase-order-receipt-pay-status" target="myFrameName">采购单--【收款】</a></li>';
                }
                // 采购付款：修改采购付款状态 ok
                if(Helper::checkRoute('select-purchase-order-pay-status')) {
                    echo '<li><a href="select-purchase-order-pay-status" target="myFrameName">采购单--【付款】</a></li>';
                }
                // 采购单：修改供应商 ok
                if(Helper::checkRoute('select-supplier')) {
                    echo '<li><a href="select-supplier" target="myFrameName">采购单--【供应商】</a></li>';
                }
                // 采购单：修改是否退税 ok
                if(Helper::checkRoute('select-purchase-order-is-drawback')) {
                    echo '<li><a href="select-purchase-order-is-drawback" target="myFrameName">采购单--退税和推送和结算和支付方式</a></li>';
                }
                ?>
            </ul>
        </li>
        <li>
            <div class="link"><i class="fa fa-bell"></i>修改订单其他费用和数据<i class="fa fa-chevron-down"></i></div>
            <ul class="submenu">
                <?php
                //修改运费优惠金额 ok
                if(Helper::checkRoute('select-ship-discount-price')) {
                    //echo '<li><a href="select-ship-discount-price" target="myFrameName">修改--运费优惠金额</a></li>';
                }
                //修改单价和数量 ok
                if(Helper::checkRoute('select-order-items-price')) {
                    //echo '<li><a href="select-order-items-price" target="myFrameName">修改--产品名称和单价和数量</a></li>';
                }
                //修改派单号 ok
                if(Helper::checkRoute('select-order-number')) {
                    echo '<li><a href="select-order-number" target="myFrameName">修改--派单号</a></li>';
                }
                ?>
            </ul>
        </li>
        <li>
            <div class="link"><i class="fa fa-unlock-alt"></i>修改采购建议相关数据<i class="fa fa-chevron-down"></i></div>
            <ul class="submenu">
                <?php
                    // 采购建议：修改产品状态 ok
                    if(Helper::checkRoute('select-suggest-product-status')) {
                        echo '<li><a href="select-suggest-product-status" target="myFrameName">采购建议--产品状态</a></li>';
                    }
                ?>
            </ul>
        </li>
        <li>
            <div class="link"><i class="fa fa-pencil-square-o"></i>采购单异常问题<i class="fa fa-chevron-down"></i></div>
            <ul class="submenu">
                <?php
                    //海外仓-采购建议-供应商：新建采购计划单报错问题 ok
                    if(Helper::checkRoute('select-is-purchase')) {
                        echo '<li><a href="select-is-purchase" target="myFrameName">海外仓-采购建议-供应商：建单报错</a></li>';
                    }

                    //处理 作废单被入错库 ok
                    if(Helper::checkRoute('select-waste-all-arrival')) {
                        echo '<li><a href="select-waste-all-arrival" target="myFrameName">作废单被入错库</a></li>';
                    }
                ?>
            </ul>
        </li>
        <li>
            <div class="link"><i class="fa fa-file-text"></i>增删改查<i class="fa fa-chevron-down"></i></div>
            <ul class="submenu">
                <?php
                //删除数据
                if(Helper::checkRoute('delete-data')) {
                    echo '<li><a href="delete-data" target="myFrameName">删除数据</a></li>';
                }
                //修改
                if(Helper::checkRoute('update-data')) {
                    echo '<li><a href="update-data" target="myFrameName">修改数据</a></li>';
                }
                //查询
                if(Helper::checkRoute('select-data')) {
                    echo '<li><a href="select-data" target="myFrameName">查询数据</a></li>';
                }
                ?>
            </ul>
        </li>
        <li>
            <div class="link"><i class="fa fa-globe"></i>查看数据<i class="fa fa-chevron-down"></i></div>
            <ul class="submenu">
                <?php
                //在途库存：查看在途库存信息 ok
                if(Helper::checkRoute('select-stock')) {
                    echo '<li><a href="select-stock" target="myFrameName">查看在途库存信息</a></li>';
                }
                //温馨小提示 ok
                if(Helper::checkRoute('prompt')) {
                    echo '<li><a href="prompt" target="myFrameName">温馨小提示</a></li>';
                }
                //对接人 ok
                if(Helper::checkRoute('pick-up')) {
                    echo '<li><a href="pick-up" target="myFrameName">对接人</a></li>';
                }
                ?>
            </ul>
        </li>
        <li>
            <div class="link"><i class="fa fa-star"></i>超级管理员权限<i class="fa fa-chevron-down"></i></div>
            <ul class="submenu">
                <?php
                //修改入库信息
                if(Helper::checkRoute('select-warehouse-results')) {
                    echo '<li><a href="select-warehouse-results" target="myFrameName">修改--入库信息</a></li>';
                }
                //修改采购到货记录
                if(Helper::checkRoute('select-arrival-record')) {
                    echo '<li><a href="select-arrival-record" target="myFrameName">修改--采购到货记录</a></li>';
                }
                //修改采购采购需求
                if(Helper::checkRoute('select-platform-summary')) {
                    echo '<li><a href="select-platform-summary" target="myFrameName">修改--采购需求</a></li>';
                }
                //修改供应商整合表
                if(Helper::checkRoute('select-supplier-update-apply')) {
                    echo '<li><a href="select-supplier-update-apply" target="myFrameName">修改--供应商整合</a></li>';
                }
                //修改样品检验
                if(Helper::checkRoute('select-sample-inspect')) {
                    echo '<li><a href="select-sample-inspect" target="myFrameName">修改--样品检验</a></li>';
                }
                ?>
            </ul>
        </li>
        <!-- 视情况而定 -->
        <li>
            <div class="link"><i class="fa fa-signal"></i>统计管理<i class="fa fa-chevron-down"></i></div>
            <ul class="submenu">
                <li><a>月贸易量</a></li>
            </ul>
        </li>
        <li>
            <div class="link"><i class="fa fa-credit-card"></i>白条管理<i class="fa fa-chevron-down"></i></div>
            <ul class="submenu">
                <li><a>开通白条</a></li>
            </ul>
        </li>
    </ul>

</div>


<iframe name="myFrameName"  width="70%" height="1500px" scrolling="false" style="/*border:1px solid red;*/position:relative;left:350px;" src="prompt"></iframe>





<?php
$this->registerCssFile('@web/js/jQueryLeftNav/leftnav.css', ['depends' => ['app\assets\AppAsset']]);
//$this->registerCssFile('@web/js/jQueryLeftNav/font-awesome.min.css', ['depends' => ['app\assets\AppAsset']]);
$this->registerJsFile('@web/js/jQueryLeftNav/leftnav.js', ['depends' => ['app\assets\AppAsset']]);
$this->registerJsFile('@web/js/jQueryLeftNav/jquery.js', ['depends' => ['app\assets\AppAsset']]);
$js = <<<JS
$(function(){
    
});
JS;
$this->registerJs($js);
?>