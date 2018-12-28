<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */
?>
<?php
//基础数据设置

Modal::begin([
    'id' => 'warehouse-update',
    'header' => '<h4 class="modal-title">更改仓库补货策略</h4>',
    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',
]);
$url = Url::toRoute('warehouse-update');
$js = <<<JS
$(.btn-success).click(funciton(){
   alert('I am here!'); 
   });
$.post('{$url}', {},
function (data) {
$('#warehouse-update').find('.modal-body').html(data);
} 
);
JS;
$this->registerJs($js);
Modal::end();
?>
<div class="basic-form">
    <?php $form = ActiveForm::begin(); ?>
    <table class="table table-hover ">
        <thead>
            <tr>
                <th>仓库</th>
                <th>补货模式</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody class="pay">
            <?php foreach ($data as $val):?>
            <tr class="pay_list ">
                <td><?=$val['warehouse_name'].' ['.$val['warehouse_code'].']'?></td>
                <td><?=$val['pattern']?$val['pattern']:'无'?></td>
                <td>
                    <?= Html::a('编辑', '#', ['class' => 'btn btn-success warehouse-update1','data-toggle' => 'modal','data-target' => '#warehouse-update']) ?>
                    <?= Html::a('日志', '#', ['class' => 'btn btn-success','data-toggle' => 'modal','data-target' => '#warehouse-log']) ?>
                    <?= Html::a('删除', '#', ['class' => 'btn btn-success','data-toggle' => 'modal','data-target' => '#warehouse-delete']) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php ActiveForm::end(); ?>
</div>
