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

<h4 class="modal-title">采购单的一生</h4>
<div class="row">

    <table class="table table-bordered">

        <thead>
        <tr>
            <th>id</th>
            <th>采购单号</th>
            <th>名称</th>
            <th>添加人</th>
            <th>添加时间</th>
            <th>IP</th>
        </tr>
        </thead>
        <tbody>
        <?php

        if(($model)){
            foreach($model as $v){

                ?>
                <tr>
                    <td><?=$v->id?></td>
                    <td><?=$v->pur_number?></td>
                    <td><?=$v->note?></td>
                    <td><?=$v->create_user?></td>
                    <td><?=$v->create_time?></td>
                    <td><?=$v->ip?></td>

                </tr>
            <?php }?>

        <?php }?>
        </tbody>

    </table>
</div>


