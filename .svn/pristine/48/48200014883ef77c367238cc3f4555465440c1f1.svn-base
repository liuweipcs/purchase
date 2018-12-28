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

<h4 class="modal-title">采购单备注</h4>
<div class="row">

    <table class="table table-bordered">

        <thead>
        <tr>
            <th>id</th>
            <th>采购单号</th>

            <th>内容</th>
            <th>添加人</th>
            <th>添加时间</th>
            <th>删除</th>
        </tr>
        </thead>
        <tbody>
        <?php

        if(is_array($model)){
            foreach($model as $k=>$v){

                ?>
                <tr>
                    <td><?=$v->id?></td>
                    <td><?=$v->pur_number?></td>
                    <td><?=$v->note?></td>
                    <td><?=BaseServices::getEveryOne($v->create_id)?></td>
                    <td><?=$v->create_time?></td>
                    <td><?php
                        if($k>0)
                        {
                           echo  Html::a('删除', ['purchase-order/delete-note', 'id'=>$v->id], ['class' => 'profile-link']);
                        } else {

                        }


                        ?></td>

                </tr>
            <?php }?>

        <?php }?>
        </tbody>

    </table>
</div>


