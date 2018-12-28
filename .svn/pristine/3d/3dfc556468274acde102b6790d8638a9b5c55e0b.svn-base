<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="basic-form">
    <?= Html::beginForm(['warehouse-create'], 'post', ['enctype' => 'multipart/form-data']) ?>
        <label>补货模式:</label>
        <?= Html::radioList('data[pattern]', $data['pattern']?$data['pattern']:'min', ['min' => '最小'], ['class' => 'form-control radio','id'=>'warehouse_pattern']); ?>
        <?= Html::hiddenInput('data[warehouse_code]', $data['warehouse_code'])?>
        <?php if($data['pattern']=='def'):?>
        <div id='warehouse_table_def'>
            <label>采购系数:</label>
            <table class='table table-hover table-bordered table-striped' >
                <tr>
                    <th>销量类型</th>
                    <th>生产(天)</th>
                    <th>物流(天)</th>
                    <th>安全库存(天)</th>
                    <th>采购频率(天)</th>
                </tr>
                <?php if(isset($data['warehousePurchaseTactics'])&&$data['warehousePurchaseTactics']):?>
                    <?php foreach ($data['warehousePurchaseTactics'] as $val):?>
                    <tr>
                        <td>
                            <?= Html::input('text', '', Yii::$app->params['type'][$val['type']], ['class' => 'form-control readonly','readonly'=>true]) ?>
                            <?= Html::input('hidden', 'data[warehousePurchaseTactics][type][]',$val['type'], ['class' => 'form-control readonly','readonly'=>true]) ?>    
                        </td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_product][]', $val['days_product'], ['class' => 'form-control number required','min'=>0]) ?></td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_logistics][]', $val['days_logistics'], ['class' => 'form-control number','min'=>0]) ?></td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_safe_stock][]', $val['days_safe_stock'], ['class' => 'form-control number','min'=>0]) ?></td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_frequency_purchase][]', $val['days_frequency_purchase'], ['class' => 'form-control number','min'=>0]) ?></td>
                    </tr>
                    <?php endforeach;?>
                <?php else:?>
                    <?php foreach (Yii::$app->params['type'] as $key=>$type):?>
                    <tr>
                        <td>
                            <?= Html::input('text', '', $type, ['class' => 'form-control readonly','readonly'=>true]) ?>
                            <?= Html::input('hidden', 'data[warehousePurchaseTactics][type][]',$key, ['class' => 'form-control readonly','readonly'=>true]) ?> 
                        </td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_product][]', 0, ['class' => 'form-control number','min'=>0]) ?></td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_logistics][]', 0, ['class' => 'form-control number','min'=>0]) ?></td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_safe_stock][]', 0, ['class' => 'form-control number','min'=>0]) ?></td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_frequency_purchase][]', 0, ['class' => 'form-control number','min'=>0]) ?></td>
                    </tr>
                    <?php endforeach;?>
                <?php endif;?>
            </table>
        </div>
        <!--点击补货模式切换的时候用-->
        <div id='warehouse_table_min' hidden="true">
            <table class='table table-hover table-bordered table-striped' >
                <tr>
                    <td><p>安全库存天数：</p><?= Html::input('number', 'data[warehouseMin][days_safe]', 0, ['class' => 'form-control number','min'=>0]) ?></td>
                    <td><p>最小补货天数：</p><?= Html::input('number', 'data[warehouseMin][days_min]', 0, ['class' => 'form-control number','min'=>0]) ?></td>
                    <td><p>安全调拨天数：</p><?= Html::input('number', 'data[warehouseMin][days_safe_transfer]', 0, ['class' => 'form-control number','min'=>0]) ?></td>
                </tr>
            </table>
        </div>
        <?php else:?>
        <div id='warehouse_table_min'>
            <table class='table table-hover table-bordered table-striped' >
                <?php if(isset($data['warehouseMin'])&&$data['warehouseMin']):?>
                    <tr>
                        <td><p>安全库存天数：</p><?= Html::input('number', 'data[warehouseMin][days_safe]', $data['warehouseMin']['days_safe'], ['class' => 'form-control number','min'=>0]) ?></td>
                        <td><p>最小补货天数：</p><?= Html::input('number', 'data[warehouseMin][days_min]', $data['warehouseMin']['days_min'], ['class' => 'form-control number','min'=>0]) ?></td>
                        <td><p>安全调拨天数：</p><?= Html::input('number', 'data[warehouseMin][days_safe_transfer]', $data['warehouseMin']['days_safe_transfer'], ['class' => 'form-control number','min'=>0]) ?></td>
                    </tr>
                <?php else:?>
                    <tr>
                        <td><p>安全库存天数：</p><?= Html::input('number', 'data[warehouseMin][days_safe]', 0, ['class' => 'form-control number','min'=>0]) ?></td>
                        <td><p>最小补货天数：</p><?= Html::input('number', 'data[warehouseMin][days_min]', 0, ['class' => 'form-control number','min'=>0]) ?></td>
                        <td><p>安全调拨天数：</p><?= Html::input('number', 'data[warehouseMin][days_safe_transfer]', 0, ['class' => 'form-control number','min'=>0]) ?></td>
                    </tr>
                <?php endif;?>
            </table>
        </div>
        <!--点击切换的时候用-->
        <div id='warehouse_table_def' hidden="true">
            <table class='table table-hover table-bordered table-striped' >
                <tr>
                    <th>销量类型</th>
                    <th>生产(天)</th>
                    <th>物流(天)</th>
                    <th>安全库存(天)</th>
                    <th>采购频率(天)</th>
                </tr>
                <?php foreach (Yii::$app->params['type'] as $key => $type): ?>
                    <tr>
                        <td>
                            <?= Html::input('text', '', $type, ['class' => 'form-control readonly', 'readonly' => true]) ?>
                            <?= Html::input('hidden', 'data[warehousePurchaseTactics][type][]', $key, ['class' => 'form-control readonly', 'readonly' => true]) ?> 
                        </td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_product][]', 0, ['class' => 'form-control number', 'min' => 0]) ?></td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_logistics][]', 0, ['class' => 'form-control number', 'min' => 0]) ?></td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_safe_stock][]', 0, ['class' => 'form-control number', 'min' => 0]) ?></td>
                        <td><?= Html::input('number', 'data[warehousePurchaseTactics][days_frequency_purchase][]', 0, ['class' => 'form-control number', 'min' => 0]) ?></td>
                    </tr>
                <?php endforeach; ?>
                
            </table>
        </div>
        <?php endif;?>
        <p style="text-align:right"><?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?></p>
    <?= Html::endForm() ?>
</div>
<?php
$js = <<<JS
    $(function(){
        $("#warehouse_pattern").find("input:radio[name='data[pattern]']").change(function(){
            var pattern=this.value;
            if(pattern=='def'){
                $("div#warehouse_table_min").attr("hidden","true");
                $("div#warehouse_table_def").removeAttr("hidden");
            }else if(pattern=='min'){
                $("div#warehouse_table_min").removeAttr("hidden");
                $("div#warehouse_table_def").attr("hidden","true");
            }
        });
    });
JS;
$this->registerJs($js);
?>