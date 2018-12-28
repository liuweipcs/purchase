<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;

?>
    <style type="text/css">
        .img-rounded{width: 60px; height: 60px; !important;}
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
                <th>产品链接</th>
                <th>金额</th>
                <th>预计到货时间</th>
            </tr>
            </thead>
            <tbody class="pay">
            <?php
            foreach($purchaseOrderItems as $v){
                $results = \app\models\WarehouseResults::getResults($v->pur_number,$v->sku,'instock_user,instock_date');
                $img=Vhelper::toSkuImg($v['sku'],$v['product_img']);

                $style1='';
                $style2='';
                $style3='';
                $style4='';

                ?>
                <tr class="pay_list" style="width: 10%">
                    <td>
                    <?=Html::a($img,['#'], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal', 'data-skus' => $v['sku'],'data-imgs' => $v['product_img']])?>
                    </td>

                    <td>
                        <?=Html::a($v['sku'],['#'], ['class' => "sales", 'data-sku' =>$v['sku'], 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal',]) ?>
                    </td>

                    <td>
                        <a href="<?=Yii::$app->params['SKU_ERP_Product_Detail'].$v->sku?>" target="_blank"><?=$v->name?></a>
                    </td>

                    <?php
                        if(!empty($e_date_eta) && $review_status < 3){
                            $style1="style='color:red'";
                        }
                        if(!empty($v->e_ctq) && $v->ctq != $v->qty && $review_status < 3){
                            $style2="style='color:red'";
                        }
                        if(!empty($v->e_price) && $v->e_price!=0  && $review_status < 3){
                            $style3="style='color:red'";
                        }
                        if(!empty($v->product_link)  && $review_status < 3){
                            $style4="style='color:red'";
                        }
                    ?>
                    <td <?=$style2?>><?=$v->ctq ? $v->ctq : $v->qty?></td>
                    <td <?=$style3?>><?=round($v->price,2)?></td>
                    <td>
                        <?php
                            $plink=\app\models\SupplierQuotes::getUrl($v->sku);
                            if($v->product_link){
                                $prolink=$v->product_link;
                            }else{
                                $prolink=$plink;
                            }
                        ?>
                        <a href="<?=$prolink?>" title="<?=$prolink?>" <?=$style4?> target="_blank"><?=Vhelper::toSubStr($prolink,1,10)?></a>
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
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',
    ],
]);
Modal::end();

$historys = Url::toRoute(['tong-tool-purchase/get-history']);
$url=Url::toRoute(['product/viewskusales']);
$imgurl=Url::toRoute(['purchase-suggest/img']);

$js = <<<JS

    $(document).on('click', '.sales', function () {
        $.get('{$url}', {sku:$(this).attr('data-sku')},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    
    $(document).on('click', '.img', function () {
        $.get('{$imgurl}', {img:$(this).attr('data-imgs'),sku:$(this).attr('data-skus')},
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


JS;
$this->registerJs($js);
?>