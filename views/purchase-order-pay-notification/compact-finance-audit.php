<?php
use yii\widgets\ActiveForm;
?>
<?php ActiveForm::begin();?>

<?= $this->render('_compact_public', ['data' => $data, 'model' => $model, 'compact' => $compact]); ?>

<table class="table table-bordered">
    <tr>
        <td style="width: 100px;">审批备注</td>
        <td><textarea rows="3" class="form-control" name="payment_notice" placeholder="请输入备注"></textarea></td>
    </tr>
    <tr>
        <td>是否通过</td>
        <td>
            <input type="radio" name="status" value="4" checked> 是
            <input type="radio" name="status" value="3"> 否
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <input type="hidden" name="id" value="<?= $model->id ?>">
            <button type="submit" class="btn btn-success">提交</button>
        </td>
    </tr>
</table>

<?php ActiveForm::end(); ?>
