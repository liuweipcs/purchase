<?php
use app\config\Vhelper;
use app\models\Product;

$this->title = '收货异常处理';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<table class='table table-hover table-bordered table-striped' >
    <tr>
        <th width="3%">No.</th>
        <th width="20%">产品</th>
        <th width="10%">图片</th>
        <th width="10%">数量</th>
        <th width="10%">单价</th>
        <th width="10%">状态</th>
        <th width="15%">操作人员</th>
        <th width="10%">时间</th>
        <th width="20%">留言</th>
        <th width="20%">仓库图片</th>
    </tr>
    <?php foreach ($data as $key=>$val):?>
    <tr>
        <td><?=$key+1?></td>
        <td>
            SKU:<?=$val['sku']?></br>
            名称:<?=$val['name']?>
        </td>

        <td>
            <?php
                $img=Product::findOne(['sku'=>$val['sku']]);
                echo $img ? Vhelper::toSkuImg($val['sku'],$img['uploadimgs']) : '';
            ?>
        </td>

        <td>
            预期: <?=$val['qty']?></br>
            到货: <?=$val['delivery_qty']?></br>
            赠送: <?=$val['presented_qty']?>
        </td>
        <td><?=$val['price']?></td>
        <td>
            <?=Yii::$app->params['receive_status'][$val['receive_status']]?></br>
            方式:<?=Yii::$app->params['handle_type'][$val['handle_type']]?></br>
            <?php if($val['handle_type']=='stop'):?>
            退款:<?=Yii::$app->params['boolean'][$val['is_return']]?>
            <?php endif;?>
        </td>
        <td>
            创建人: <?=$val['creator']?></br>
            处理人: <?=$val['handler']?></br>
            <!--审核人: --><?/*=$val['auditor']*/?>
        </td>
        <td>
            创建时间: <?=$val['created_at']?></br>
            处理时间: <?=$val['time_handle']?></br>
            <!--审核时间: --><?/*=$val['time_audit']*/?>
        </td>
        <td>
            仓库留言:<?=$val['note']?></br>
            处理留言:<?=$val['note_handle']?></br>
           <!-- 审核留言:--><?/*=$val['note_audit']*/?>
        </td>
        <td>
            <?php if($val['img']){
                $img =json_decode($val['img']);
                foreach($img as $v){

                    ?>
            <a href="<?php echo $v?>" target="_blank"><img src="<?php echo $v?>" width="50px"></a>
            <?php }}?>
        </td>
    </tr>
    <?php endforeach;?>
</table>