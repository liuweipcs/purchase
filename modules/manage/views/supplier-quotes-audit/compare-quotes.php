<?php
use app\config\Vhelper;
?>
<table class="table table-bordered">
    <thead>
        <th></th>
        <?php foreach ($supplier_code as $code) { ?>
            <th>
                <?php
                    $supplier = \app\models\Supplier::find()->select('supplier_code,supplier_name')->where(['supplier_code'=>$code])->one();
                    $name = empty($supplier) ? '' : $supplier->supplier_name.'<br/>';
                    $name .= \yii\helpers\Html::a('查看详情',['supplier'],['class'=>'btn btn-info btn-sm']);
                    echo  $name;
                ?>
            </th>
        <?php } ?>
    </thead>
    <tbody>
        <?php foreach ($compareDatas as $key=>$value){?>
            <tr>
                <td style="text-align: center"> <?php
                    $defaultPrice = \app\models\ProductProvider::find()
                                    ->select('q.supplierprice')
                                    ->alias('t')
                                    ->leftJoin(\app\models\SupplierQuotes::tableName().' q','t.quotes_id=q.id')
                                    ->where(['t.sku'=>$key,'t.is_supplier'=>1])
                                    ->scalar();
                    $html= '<p style="font-size: large">'.$key.'</p><br/>';
                    $html.= '<p style="color: orange">现单价：'.$defaultPrice.'</p>';
                    echo  $html;
                    ?></td>
            <?php foreach ($value as $v){ ?>
                <td>
                    <?php
                    $supplierPrice='';
                    if(!empty($v)){
                        if(!empty($v->quotesItems)){
                            if($v->type==1){
                                foreach ($v->quotesItems as $item){
                                    $supplierPrice .= ($item->supplier_price>$defaultPrice ? '<span style="color: red">'.$item->supplier_price.'</span>':'<span style="color: green">'.$item->supplier_price.'</span>').'<br/>';
                                }
                            }
                            if($v->type==2){
                                foreach ($v->quotesItems as $item){
                                    $supplierPrice .= $item->amount_min.'-'.$item->amount_max.'件：单价：'.($item->supplier_price>$defaultPrice ? '<span style="color: red">'.$item->supplier_price.'</span>':'<span style="color: green">'.$item->supplier_price.'</span>').'元<br/>';
                                }
                            }
                        }
                        $supplierPrice.= '是否现货：'.($v->is_in_stock == 1?'<span class="label label-success">是</span>':'<span class="label label-danger">否</span>').'<br/>';
                        $supplierPrice.= '交期天数：'.$v->delivery_time .'天<br/>';
                        $supplierPrice.= '报价原因：'."<span title={$v->reason} >".Vhelper::toSubStr($v->reason,10,10);
                    }
                    echo $supplierPrice;
                    ?>
                </td>
            <?php }?>
            </tr>
        <?php }?>
    </tbody>
</table>
