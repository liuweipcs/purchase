<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/9
 * Time: 17:29
 */
?>

<?php $form = ActiveForm::begin([
        //'id' => 'form-id',
        //'enableAjaxValidation' => true,
        //'validationUrl' => Url::toRoute(['validate-form']),
    ]
); ?>
    <div class="row">
        <label>采购单号：</label>
        <input type="text"  name="SampleInspect[pur_number]">
    </div>
        <table class="table table-bordered">
            <thead>
                <th>sku</th>
                <th>申请人</th>
                <th>供应商名称</th>
                <th>采购单号</th>
            </thead>
            <tbody>
            <?php foreach ($data as $v){?>
            <tr>
                <td><?= $v->sku?></td>
                <td><?= !empty($v->apply) ? $v->apply->create_user_name : '';?></td>
                <td><?= !empty($v->apply) ? !empty($v->apply->newSupplier) ? $v->apply->newSupplier->supplier_name : '' : '';?></td>
                <td><?= $v->pur_number ?></td>
            </tr>
            <?php }?>
            </tbody>
        </table>
    <div class="form-group">
        <?= Html::submitButton('提交', ['class' =>'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end(); ?>