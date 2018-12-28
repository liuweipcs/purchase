<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>

<h4 class="modal-title">采购单跟踪备注</h4>
<div class="row">

    <table class="table table-bordered">

        <thead>
        <tr>
            <th>id</th>
            <th>快递号</th>
            <th>运费</th>
            <th>快递公司</th>
            <th>内容</th>
            <th>添加人</th>
            <th>添加时间</th>
        </tr>
        </thead>
        <tbody>
        <?php

        if(is_array($model)){
            foreach($model as $v){

                ?>
                <tr>
                    <td><?=$v->id?></td>
                    <td><?=$v->express_no?></td>
                    <td><?=$v->freight?></td>
                    <td><?php

                        if (preg_match("/[\x7f-\xff]/", $v->cargo_company_id)){
                            echo $v->cargo_company_id;
                        } else{
                            //echo BaseServices::getLogisticsCarrier($v->cargo_company_id)->name;
                        }
                        ?></td>
                    <td><?=$v->note?></td>
                    <td><?=BaseServices::getEveryOne($v->create_user_id)?></td>
                    <td><?=$v->create_time?></td>

                </tr>
            <?php }?>

        <?php }?>
        </tbody>

    </table>
</div>


