<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use \yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */

?>
<style>
    .supplier-table {
        border: 1px solid #ccc;
        border-collapse: collapse;
        background-color: white;
    }
    .supplier-table th,.supplier-table td {
        text-align: left;
        border: 1px solid #ccc;
    }
    .supplier-table .form {
        padding: 0 1% 0 1%
    }
</style>

<div class="row">
    <?php $form = ActiveForm::begin(); ?>
    <table class="supplier-table" width="95%">
        <tbody>
            <tr>
                <td></td>
                <td>改善措施</td>
                <td colspan="2"><?= $form->field($model,'improvement_measure')->textInput()->label('')?></td>
            </tr>
        </tbody>
    </table>
    <div>
        <?= Html::submitButton(Yii::t('app', '提交'), ['class' => 'btn btn-success']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>



