<style>
    .modal-content{width: 1100px}
</style>
<span  class="tab_index" tab_index="<?= $tab_index?>"></span>

    <?php
        $warehouse_list = \app\services\BaseServices::getWarehouseCode();
        $category_list  = \app\services\BaseServices::getCategory();
        $index = 1;
        foreach ($ruleData as $k=>$v){
    ?>
    <?php foreach ($v as $key=>$value){?>
        <?php foreach ($value as $tran=>$dataValue){?>
<div>
    <a class="a_href" name="<?=$k.$key.'-'.$tran.'-'.$tab_index?>"></a>
        <?php
            if($index == 1){
                $data = "<h4 style='float: left;padding-right: 50px;'>全选: <input type='checkbox' id='theadInp'></h4>";
                echo $data;
            }
            $index++;
        ?>
        <h4 style="float: left;padding-right: 50px;color: red "><?= '供应商：'.\app\services\SupplierServices::getSupplierName($k) ?></h4>
        <h4 style="float: left;padding-right: 50px ;color: red"><?= '采购仓：'.(isset($warehouse_list[$key])?$warehouse_list[$key]:'');?></h4>
        <h4 style="float: left ;color: red"><?= '物流类型：'.\app\services\PlatformSummaryServices::getTransportStyle($tran)?></h4>
        <div style="clear: both"></div>
        <table border="solid 1px">
            <thead>
            <th width="1%"></th>
            <th width="10%">产品信息</th>
            <th width="5%">平台</th>
            <th width="5%">数量/金额</th>
            <th width="6%">采购仓</th>
            <th width="5%">是否中转</th>
            <th width="8%">中转仓</th>
            <th width="5%">物流类型</th>
            <th width="5%">需求信息</th>
            <th width="5%">需求状态</th>
            <th width="5%">同意(驳回)信息</th>
            <th width="10%">销售备注</th>
            <th width="5%">操作</th>
            </thead>
        <tbody id="type_2">
        <?php
            $ids_list = [];
            foreach($dataValue as $ids_value){
                $ids_list[] = $ids_value->id;
            }
            $ids_list = implode(',',$ids_list);

            foreach ($dataValue as $kes=>$vs){
            if(empty($vs) OR !isset($vs->id)) continue; ?>
            <tr>
                <td style="text-align: center">
                    <input type="checkbox" name="revoke_id" value="<?php echo $vs->id ?>" class="revoke">
                </td>
                <td>
                    <?php
                    $data = '产品名：'.$vs->product_name.'<br/>';
                    $data.= '产品分类：'.(isset($category_list[$vs->product_category])?$category_list[$vs->product_category]:'').'<br/>';
                    $data.= '<span style="color:red">sku:'.$vs->sku.'</span><br/>';
                    $data.= '<span style="color:#00a65a">需求单号:'.$vs->demand_number.'</span><br/>';
                    $suppliercode =!empty($vs->supplierQuotes['quotes_id'])?\app\models\SupplierQuotes::getFileds($vs->supplierQuotes['quotes_id'],'suppliercode')->suppliercode:'';
                    $data.= !empty($suppliercode)?'<span style="color:#00a65a">供应商:'.\app\services\BaseServices::getSupplierName($suppliercode).'</span></br>':'<span style="color:#00a65a">供应商:</span></br>';
                    $data.= $vs->is_purchase==1?'是否生成采购计划：<span style="color:red">未生成</span>':'是否生成采购计划：<span style="color:#00a65a">已生成</span>';
                    echo  $data;
                    ?>
                </td>
                <td>
                    <?= $vs->platform_number?>
                </td>
                <td>
                    <?php
                    $data ='采购数：'.'<span style="color:red">'.$vs->purchase_quantity.'</span><br/>';
                    $data .='中转数：'.'<span style="color:red">'.$vs->transit_number.'</span><br/>';
                    $price = \app\models\ProductProvider::find()
                        ->select('q.supplierprice')
                        ->alias('t')
                        ->where(['t.sku'=>$vs->sku])
                        ->andWhere(['t.is_supplier'=>1])
                        ->leftJoin(\app\models\SupplierQuotes::tableName().' q','t.quotes_id=q.id')
                        ->scalar();
                    if($price!==false){
                        $data.='采购单价：'.'<span style="color:red">'.$price.'</span><br/>';
                        $data.='采购金额：'.'<span style="color:red">'.$vs->purchase_quantity*$price.'</span>';
                    }
                    echo  $data;
                    ?>
                </td>
                <td>
                    <?= isset($warehouse_list[$vs->purchase_warehouse])?$warehouse_list[$vs->purchase_warehouse]:$vs->purchase_warehouse;?>
                </td>
                <td>
                    <?=$vs->is_transit==1?'<span style="color:red">否</span>':'<span style="color:#00a65a">是</span>';?>
                </td>
                <td>
                    <?php echo  isset($warehouse_list[$vs->transit_warehouse])?$warehouse_list[$vs->transit_warehouse]:'';?>
                </td>
                <td>
                    <?=
                    $vs->transport_style ? \app\services\PurchaseOrderServices::getTransport($vs->transport_style) : '';
                    ?>
                </td>
                <td>
                    <?php
                    $data = '需求人:'.$vs->create_id.'<br/>';
                    $data .='需求时间:'.$vs->create_time;
                    echo   $data;
                    ?>
                </td>
                <td>
                    <?php
                    if($vs->level_audit_status==1)
                    {
                        $str ='';
                        $str .= '<span style="color:#00a65a">'.Yii::$app->params['demand'][$vs->level_audit_status].'</span>';
                        echo  $str;

                    } elseif($vs->level_audit_status==2){

                        $str = '<span style="color:red">'.Yii::$app->params['demand'][$vs->level_audit_status].'</span><br/>';
                        $str .= '原因：'.$model->audit_note;
                        echo $str;

                    } elseif($vs->level_audit_status==4){

                        $str = '<span style="color:red">'.Yii::$app->params['demand'][$vs->level_audit_status].'</span><br/>';
                        $str .= '原因：'.$vs->purchase_note;
                        echo $str;

                    }elseif($vs->level_audit_status==6){
                        $str = '<span style="color:red">'.Yii::$app->params['demand'][$vs->level_audit_status].'</span><br/>';
                        $str .= '原因：'.$vs->audit_note;
                        echo $str;
                    } else{
                        echo  Yii::$app->params['demand'][$vs->level_audit_status];
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if($vs->level_audit_status==4)
                    {
                        $data = '采购驳回人:'.$vs->buyer.'<br/>';
                        $data .= '采购驳回时间:'.$vs->purchase_time;
                        echo  $data;
                    } else{

                        $data = '同意(驳回)人:'.$vs->agree_user.'<br/>';
                        $data .= '同意(驳回)时间:'.$vs->agree_time;
                        echo  $data;
                    }
                    ?>
                </td>
                <td>
                    <?php
                    echo   $vs->sales_note;
                    ?>
                </td>
                <?php if($kes==0){ ?>
                <td rowspan="<?=count($dataValue)?>" style="text-align: center">
                    <?php
                    echo \yii\helpers\Html::a('修改', ['batch-update'], ['class' => 'batch-update','data-toggle' => 'modal', 'data-target' => '#create-modal','suplier_code'=>$k,'warehouse_code'=>$key,'num'=>count($value),'transport'=>$tran]);
                    ?>
                    <?php
                    echo \yii\helpers\Html::a('撤销', ['batch-revoke'], ['class' => 'batch-update','data-toggle' => 'modal', 'data-target' => '#create-modal','suplier_code'=>$k,'warehouse_code'=>$key,'num'=>count($value),'transport'=>$tran,'ids_list'=>$ids_list]);
                    ?>
                </td>
                <?php }?>
                <?php }?>
            </tr>
        <?php }?>
        </tbody>
        </table>
                </div>
    <?php }?>
<?php }
$pagination = new \yii\data\Pagination(['totalCount' => $count,'pagesize' => isset($_GET['per-page'])?$_GET['per-page']:20]);
echo yii\widgets\LinkPager::widget(['pagination' => $pagination,]);
?>

<?php
$js = <<<JS
$(function() {
    //实现全选反选
    $("#theadInp").on('click', function() {
        $("#w6-tab2 input:checkbox").prop("checked", $(this).prop('checked'));
    })
    $("tbody input:checkbox").on('click', function() {
        //当选中的长度等于checkbox的长度的时候,就让控制全选反选的checkbox设置为选中,否则就为未选中
        if($("tbody input:checkbox").length === $("tbody input:checked").length) {
            $("#theadInp").prop("checked", true);
        } else {
            $("#theadInp").prop("checked", false);
        }
    })
})

//  $(document).on('click', '.batch-update', function () {
//      var supplierCode = $(this).attr('suplier_code');
//      var warehouseCode = $(this).attr('warehouse_code');
//      var num = $(this).attr('num');
//     $.get($(this).attr('href'), {supplier_code:supplierCode,warehouse_code:warehouseCode,num:num},
//         function (data) {
//             $('.modal-body').html(data);
//         }
//     );
// });


      
JS;
$this->registerJs($js);
?>


