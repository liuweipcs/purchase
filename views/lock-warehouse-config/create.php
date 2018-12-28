<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    <div class="fg">
        <label>sku：</label>
        <input type="text" name="sku">
    </div>
    <div class="fg">
        <label>仓库：</label>
        <select name="warehouse_code" style="width: 200px">
            <?php
                $warehouseList = BaseServices::getWarehouseCode();
                foreach($warehouseList as $key=>$v):
            ?>
            <?php if($key=='FBA_SZ_AA'):?>
                <option value="<?= $key ?>" selected><?= $v ?></option>
            <?php else:?>
                <option value="<?= $key ?>"><?= $v ?></option>
            <?php endif;?>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="fg">
        <label></label>
        <input type="submit" value="提交">
    </div>
<?php ActiveForm::end(); ?>