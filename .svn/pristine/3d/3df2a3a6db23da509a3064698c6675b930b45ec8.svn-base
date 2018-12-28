<?php
use yii\widgets\ActiveForm;
use app\services\BaseServices;
?>
<?php ActiveForm::begin();?>

<table class="table table-bordered">

    <thead>
    <tr>
        <th>#</th>
        <th>备注</th>
        <th>创建人</th>
        <th>创建时间</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach($notes as $k => $note): ?>
        <tr>
            <td><?= $k+1 ?></td>
            <td><?= $note['note'] ?></td>
            <td><?= !empty($note['create_id']) ? BaseServices::getEveryOne($note['create_id']) : ''; ?></td>
            <td><?= $note['create_time'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>

</table>

<table class="table table-bordered">
    <tr>
        <td style="width: 100px;">添加备注</td>
        <td><textarea rows="3" class="form-control" name="note" placeholder="请输入备注"></textarea></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <input type="hidden" name="pur_number" value="<?= $cpn ?>">
            <button type="submit" class="btn btn-success">提交</button>
        </td>
    </tr>
</table>






<?php ActiveForm::end(); ?>