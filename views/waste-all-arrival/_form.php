<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;

/* @var $this yii\web\View */
/* @var $model app\models\OverseasWarehouseGoodsTaxRebate */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
    .border_style{
        border-bottom: 1px solid #ccc;
    }
</style>
<!--========================================  顶层   ================================================-->
<div class="container-fluid">
    <div class="row">
        <!--******************  拉取erp中的sku【开始】  *************************-->
        <div class="col-md-4">
            <?php
            if(Helper::checkRoute('select-pull-erp-sku')) {
                echo $this->render('_form-pull-erp-sku', []);
            }
            ?>
        </div>
        <!--******************  拉取erp中的sku【结束】  *************************-->
        <!--******************  拉取阿里巴巴中的物流信息 【开始】  *************************-->
        <div class="col-md-4">
            <?php
            if(Helper::checkRoute('select-pull-logistics')) {
                echo $this->render('_form-pull-logistics', []);
            }
            ?>
        </div>
        <!--******************  拉取阿里巴巴中的物流信息【结束】  *************************-->
        <!--******************  采购单：修改仓库 【开始】  *************************-->
        <div class="col-md-4">
            <?php
            if(Helper::checkRoute('select-purchase-order-warehouse-code')) {
                echo $this->render('_form-update-purchase-order-warehouse-code', []);
            }
            ?>
        </div>
        <!--******************  采购单：修改仓库【结束】  *************************-->
<!--========================================  第一层   ================================================-->
        <!--******************  海外仓-采购建议-供应商：新建采购计划单报错问题 【开始】  *************************-->
        <div class="col-md-7">
            <?php
                if(Helper::checkRoute('select-is-purchase')) {
                    echo $this->render('_form-update-is-purchase', []);
                }
            ?>
        </div>
        <!--******************  海外仓-采购建议-供应商：新建采购计划单报错问题 【结束】  *************************-->
        <!--******************  处理 作废单被入错库【开始】  *************************-->
        <div class="col-md-5">
            <?php
            if(Helper::checkRoute('select-waste-all-arrival')) {
                echo $this->render('_form-waste-all-arrival', []);
            }
            ?>
        </div>
        <!--******************  处理 作废单被入错库【结束】  *************************-->
<!--========================================  第二层   ================================================-->
        <!--******************  采购单：修改采购单状态【开始】  *************************-->
        <div class="col-md-4">
            <?php
                if(Helper::checkRoute('select-purchas-status')) {
                    echo $this->render('_form-update-purchas-status', []);
                }
            ?>
        </div>
        <!--******************  采购单：修改采购单状态【结束】  *************************-->
        <!--******************  采购单收款表：修改收款状态【开始】  *************************-->
        <div class="col-md-4">
            <?php
                if(Helper::checkRoute('select-purchase-order-receipt-pay-status')) {
                    echo $this->render('_form-update-purchase-order-receipt-pay-status', []);
                }
            ?>
        </div>
        <!--******************  采购单收款表：修改收款状态【结束】  *************************-->
        <!--******************  采购付款：修改采购付款状态【开始】  *************************-->
        <div class="col-md-4">
            <?php
            if(Helper::checkRoute('select-purchase-order-pay-status')) {
                echo $this->render('_form-update-pay-status', []);
            }
            ?>
        </div>
        <!--******************  采购付款：修改采购付款状态【结束】  *************************-->
<!--========================================  第三层   ================================================-->
    <!--******************  采购单：修改供应商 【开始】  *************************-->
    <div class="col-md-4">
        <?php
        if(Helper::checkRoute('select-supplier')) {
            echo $this->render('_form-update-supplier', []);
        }
        ?>
    </div>
    <!--******************  采购单：修改供应商 【结束】  *************************-->
        <!--******************  采购建议：修改产品状态【开始】  *************************-->
        <div class="col-md-4">
            <?php
                if(Helper::checkRoute('select-suggest-product-status')) {
                    echo $this->render('_form-update-suggest-product-status', []);
                }
            ?>
        </div>
        <!--******************  采购建议：修改产品状态【结束】  *************************-->
        <!--******************  采购单：修改是否退税【开始】  *************************-->
        <div class="col-md-4">
            <?php
                if(Helper::checkRoute('select-purchase-order-is-drawback')) {
                    echo $this->render('_form-update-order-is-drawback', []);
                }
            ?>
        </div>
        <!--******************  采购单：修改是否退税【结束】  *************************-->
        <!--******************  删除数据【开始】  *************************-->
        <div class="col-md-4">
            <?php
                if(Helper::checkRoute('delete-data')) {
                    echo $this->render('_form-delete-data', []);
                }
            ?>
        </div>
        <!--******************  删除数据【结束】  *************************-->
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!--******************  在途库存：查看在途库存信息【开始】  *************************-->
        <div class="col-md-12">
            <?php
                if(Helper::checkRoute('select-stock')) {
                    echo $this->render('_form-select-stock', []);
                }
            ?>
        </div>
        <!--******************  在途库存：查看在途库存信息【结束】  *************************-->
    </div>
</div>
<!--===========================   温馨小提示    ======================================-->
<div class="container-fluid">
    <div class="row">
        <!--******************  温馨小提示【开始】  *************************-->
        <div class="col-md-6">
            <?php
            if(Helper::checkRoute('prompt')) {
                echo $this->render('_form-prompt', []);
            }
            ?>
        </div>
        <!--******************  温馨小提示【结束】  *************************-->
        <!--******************  对接人【开始】  *************************-->
        <div class="col-md-6">
            <?php
            if(Helper::checkRoute('pick-up')) {
                echo $this->render('_form-pick-up', []);
            }
            ?>
        </div>
        <!--******************  对接人【结束】  *************************-->
    </div>
</div>