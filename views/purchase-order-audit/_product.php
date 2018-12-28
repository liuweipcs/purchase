<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\SkuSalesStatistics;
use app\models\Stock;
use app\services\SupplierGoodsServices;
use app\config\Vhelper;
use app\models\SupplierQuotes;
use app\models\PurchaseOrderItems;

/* @var $this yii\web\View */
/* @var $model app\models\Stockin */


?>
    <style type="text/css">
        .img-rounded{width: 80px; !important;}
    </style>
    <div class="stockin-update">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>图片</th>
                <th>SKU</th>
                <th>产品名称</th>
                <th>采购数量</th>
                <th>单价( RMB )</th>
                <th>上次采购单价( RMB )</th>  
                <th>产品链接</th>
                <th>金额</th>
                <th>预计到货时间</th>
            </tr>
            </thead>
            <tbody class="pay">
            <?php
            foreach($purchaseOrderItems as $v){
                $results = \app\models\WarehouseResults::getResults($v->pur_number,$v->sku,'instock_user,instock_date');
                $supplierprice = SupplierQuotes::getQuotes($v['sku'],$supplier_code)['supplierprice'];
                $img=Vhelper::toSkuImg($v['sku'],$v['product_img']);

                $style1='';
                $style2='';
                $style3='';
                $style4='';

                ?>
                <tr class="pay_list" style="width: 10%">
                    <td><?=Html::a($img,['purchase-suggest/img', 'sku' => $v['sku'],'img' => $v['product_img']], ['class' => "img", 'style'=>'margin:0px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal'])?></td>
                    <td>
                        <?=Html::a($v->sku,['#'], ['class' => "sales", 'data-sku' =>$v->sku, 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal',]).SupplierGoodsServices::getSkuStatus($v->sku) ?>
                    </td>
                    <td><a href="<?=Yii::$app->params['SKU_ERP_Product_Detail'].$v->sku?>" target="_blank"><?php echo $v->name; ?></a></td>
                    <?php
                       /* if(!empty($e_date_eta) && !empty($grade) && $grade->grade<3){
                            $style1="style='color:red'";
                        }*/
                    $date_start = date('Y-m-d 00:00:00');
                    $date_end   = date('Y-m-d 23:23:59');
                    $model2=\app\models\PurchaseSuggest::find()->select('qty')->where(['sku'=>$v['sku'],'purchase_type'=>1])->andWhere(['>','qty',0])->andWhere(['between','created_at',$date_start,$date_end])->scalar();

                        if(!empty($model2) && $v->ctq != $model2){
                            $style2="style='color:red'";
                        }

                        if(!empty($v->e_price) && $v->e_price!=0 && !empty($grade) && $grade->grade<=3){
                            $style3="style='color:red'";
                        }
                        if (($v['price'] - $supplierprice) > 0) {
                            $style3="style='color:red;font-weight: bold;'";
                        } elseif(($v['price'] - $supplierprice) < 0) {
                            $style3="style='color: #04f751;font-weight: bold;'";
                        }

                        if(!empty($v->product_link) && !empty($grade) && $grade->grade<=3){
                            $style4="style='color:red'";
                        }
                    ?>
                    <td <?=$style2?>><?=$v->ctq.Html::a('', ['#'],[
                                'data-toggle' => 'modal',
                                'data-target' => '#created-modal',
                                'class'=>'data-updatesd glyphicon glyphicon-zoom-in',
                                'title'=>'建议',
                                'sku'  => $v['sku'],
                                'pur_number'  => $v['pur_number'],
                            ]);?></td>




                    <?php
                        
                    $price_bool = PurchaseOrderItems::comparePrice($v['sku'],$v->price,$supplier_code);
                    if ($price_bool == 2) {
                        $price_style="style='color:red;font-weight: bold;'";
                    } elseif($price_bool == 3) {
                        $price_style="style='color: #04f751;font-weight: bold;'";
                    } else {
                        $price_style="style='color: black;font-weight: bold;'";
                    }
                    ?>
                    <td <?=$price_style?> ><?php echo round($v->price,2) . Html::a('', ['#'],[
                            'data-toggle' => 'modal',
                            'data-target' => '#created-modal',
                            'class'=>'data-updatess glyphicon glyphicon-zoom-in',
                            'title'=>'历史采购记录',
                            'sku'  => $v['sku'],
                        ]);?></td>
                    <td><?php 
                        $last_order = \app\models\PurchaseOrderItems::find()
                                                        ->select('t.price')
                                                        ->from(\app\models\PurchaseOrderItems::tableName().' as t')
                                                        ->leftJoin('pur_purchase_order as t1','t1.pur_number = t.pur_number')
                                                        ->where(['t.sku'=>$v->sku,'t1.purchas_status'=>[3,5,6,7,8,9]])
                                                        ->orderBy('t1.id DESC')
                                                        ->one();
                        
                        echo round(($last_order?$last_order->price:'首次采购'),2)
                    ?></td><!--上次采购单价--> 
                    <td <?=$style4?>>
                        <?php
                        $plink=\app\models\SupplierQuotes::getUrl($v->sku);
                        if($v->product_link){
                            $prolink=$v->product_link;
                        }else{
                            $prolink=$plink;
                        }
                        ?>
                        <a href="<?=$prolink?>" target="_blank"><?=Vhelper::toSubStr($prolink,1,5)?></a>
                    </td>
                    <td><?=round($v->price*$v->ctq,2)?></td>
                    <td <?=$style1?>><?=$date_eta?></td>
                </tr>
            <?php }?>
            </tbody>

        </table>
    </div>
<?php
Modal::begin([
    'id' => 'created-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    //'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗
        'z-index' =>'-1',

    ],
]);
Modal::end();

//$historys = Url::toRoute(['tong-tool-purchase/get-history']);
$historys = Url::toRoute(['purchase-suggest/histor-purchase-info']);
$historyb = Url::toRoute(['purchase-suggest/suggest-quantity']);
$url=Url::toRoute(['product/viewskusales']);

$js = <<<JS

    $(document).on('click', '.sales', function () {
        $.get('{$url}', {sku:$(this).attr('data-sku')},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    
    $(document).on('click', '.img', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });


    function modal_close(data)
    {
        $('#created-modal').modal("hide");
    }
    
    $(document).on('click','.data-updatess', function () {
        $.get('{$historys}', {sku:$(this).attr('sku')},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);

            }
        );
    });
    $(document).on('click','.data-updatesd', function () {
        $.get('{$historyb}', {sku:$(this).attr('sku'),'pur':$(this).attr('pur_number')},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);

            }
        );
    });
    $("#created-modal").on("hidden", function() {
        $(this).removeData("modal");
    });
    $("#created-modal").on("hidden.bs.modal",function(){
        $(document.body).addClass("modal-open");
    });
    $("[z-index='-1'] .close").on("click",function(){
        $("#created-modal").modal('hide'); 
        
       /* return typeof jQuery !== "undefined" && jQuery.event.triggered !== e.type ?
                jQuery.event.dispatch.apply( elem, arguments ) : undefined;*/
        
        return false;
    })
JS;
$this->registerJs($js);
?>