<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
?>
<?php ActiveForm::begin(['id' => 'apply-breakage-form']); ?>
<h5>申请报损</h5>

    <div class="my-box">
        <table class="my-table">
            <thead>
            <tr>
                <th>图片</th>
                <th>sku</th>
                <th>名称</th>
                <th>单价</th>
                <th>订单数量</th>
                <th>入库数量</th>
                <th>报损数量</th>
            </tr>
            </thead>
            <tbody>

            <?php


            foreach($orderInfo['purchaseOrderItems'] as $k=>$v):
            $img = Html::img(Vhelper::downloadImg($v['sku'], $v['product_img'], 2), ['width' => '110px', 'class' => 'img-thumbnail']);


            ?>

            <tr>
                <td><?= $img ?></td>
                <td><?= $v['sku'] ?></td>
                <td><?= $v['name'] ?></td>
                <td><?= $v['price'] ?></td>

                <td><?= $v['ctq'] ?></td>
                <td><?= $v['ruku_num'] ?></td>
                <td><input type="number" class="breakage" name="breakage[<?= $k ?>][num]" value="0" style="width:60px;" min="0" max="<?= $v['ctq'] ?>"></td>


            </tr>

                <input type="hidden" name="breakage[<?= $k ?>][pur_number]" value="<?= $v['pur_number'] ?>">
                <input type="hidden" name="breakage[<?= $k ?>][sku]" value="<?= $v['sku'] ?>">
                <input type="hidden" name="breakage[<?= $k ?>][name]" value="<?= $v['name'] ?>">
                <input type="hidden" name="breakage[<?= $k ?>][price]" value="<?= $v['price'] ?>">
                <input type="hidden" name="breakage[<?= $k ?>][qty]" value="<?= $v['ruku_num'] ?>">
                <input type="hidden" name="breakage[<?= $k ?>][ctq]" value="<?= $v['ctq'] ?>">





            <?php endforeach; ?>

            </tbody>
        </table>


        <div class="fg">
            <label>申请备注</label>
            <textarea name="apply_notice" rows="3" cols="80"></textarea>
        </div>

        <div class="fg">
            <label></label>
            <input type="submit" value="提交">
        </div>

    </div>

<?php
ActiveForm::end();
?>