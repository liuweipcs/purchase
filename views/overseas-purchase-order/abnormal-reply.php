<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use yii\bootstrap\Modal;

$this->title='采购异常回复';
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="purchase-order-form">

    <?php $form = ActiveForm::begin(); ?>
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
            </tr>
            </thead>
            <tbody>
            <?php

            if(is_array($models))
            {
                foreach($models as $k=>$v){
                    ?>
                    <tr>
                        <td><?=$v->id?></td>
                        <td><?=$v->pur_number?></td>
                        <td><?=$v->note?></td>
                        <td><?=BaseServices::getEveryOne($v->create_id)?></td>
                        <td><?=$v->create_time?></td>
                    </tr>
                <?php }?>
            <?php }?>
            </tbody>
        </table>
    </div>

    <h4><?=Yii::t('app','添加备注')?></h4>
    <input type="hidden"  class="form-control" name="PurchaseReply[pur_number]" value="<?=$pur_number?>">
    <input type="hidden"  class="form-control" name="PurchaseReply[purchase_type]" value="2">
    <input type="hidden"  class="form-control" name="PurchaseReply[replay_type]" value="1">
    <input type="hidden"  class="form-control" name="flag" value="<?=$flag?>">

    <div class="col-md-12"><?= $form->field($model, 'note')->textarea(['rows'=>3,'cols'=>10,'required'=>true]) ?></div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '提交' : '修改', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
