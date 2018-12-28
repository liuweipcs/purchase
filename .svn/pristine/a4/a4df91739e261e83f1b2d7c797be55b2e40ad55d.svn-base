<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel app\models\MemberSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '用户列表');
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
$form = ActiveForm::begin(['action' => ['member/post'],'method'=>'post',]); ?>
 
<?= $form->field($model, 'username')->textInput(['maxlength' => 20]) ?>
<?= $form->field($model, 'alias_name')->textInput(['maxlength' => 20]) ?>
<?= $form->field($model, 'status')->dropDownList(['10'=>'正常','0'=>'禁用'], ['prompt'=>'请选择','style'=>'width:120px']) ?>

<?= Html::submitButton('提交', ['class'=>'btn btn-primary','name' =>'submit-button']) ?>
<?= Html::resetButton('重置', ['class'=>'btn btn-primary','name' =>'submit-button']) ?>
<?php ActiveForm::end(); ?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
<?php // echo $this->render('_search', ['model' => $searchModel]);  ?>

    <p>
    <?= Html::a(Yii::t('app', '新增'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>
    <?= GridView::widget(
        [
            'dataProvider'=>$dataProvider,
            'filterModel' => $searchModel,
            'columns'=>[
                ['class' => 'yii\grid\SerialColumn'],
                'username',
                'alias_name',
                'email:email',
                [
                    'attribute'=>'role',
                    'value'=>function($data){
                        $DbManager = new yii\rbac\DbManager();
                        $role = $DbManager->getRolesByUser($data->id);
                        //var_dump($role);
                        return key($role);
                    }
                ],
                [
                    'attribute'=>'status',
                    'content'=>function($data){
                        return $data->status==10?
                            Html::tag('span','正常',['class'=>'label label-sm label-success']) :
                            Html::tag('span','禁用',['class'=>'label label-sm label-danger']);
                    }
                ],
                [
                    'attribute' => 'created_at',
                    'format' => ['date', 'php:Y-m-d H:i:s']
                ],
            ]
        ]
        );
    ?>
<?php Pjax::end(); ?>
</div>
