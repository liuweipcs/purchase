<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\MemberSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '用户列表');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <!--<h1><?/*= Html::encode($this->title) */?></h1>-->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '创建用户'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
  <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
      //重新定义分页样式
        //'layout'=> '{items}<div class="text-right tooltip-demo">{pager}</div>',
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            'id',
             'user_number',
             'username',
             'alias_name',
             //'access_token',
             'email:email',
             [
                'label' => '电话',
                'value' => function ($model) {
                    return $model->telephone;
                }
             ],
            [

                'attribute' => 'role',
                'content' => function($model){
                    $DbManager = new yii\rbac\DbManager();
                    $role = $DbManager->getRolesByUser($model->id);
                    if ($role && is_array($role)) {
                        $r = [];
                        foreach ($role as $key => $value) {
                            $r[] = $key;
                        }
                        /* 超级管理员判断 */
                        if(Yii::$app->params['admin'] == $model->id){
                            $r[] = '超管';
                            return Html::tag('span',implode(',', $r),['class'=>'label label-sm label-danger']);
                        }
                        return Html::tag('span',implode(',', $r),['class'=>'label label-sm label-success']);
                    }
                    return '';
                }
            ],
            [

                'attribute' => 'status',
                'options' => ['width' => '70px;'],
                'content' => function($model){
                    return $model->status ?
                        Html::tag('span','正常',['class'=>'label label-sm label-success']) :
                        Html::tag('span','禁用',['class'=>'label label-sm label-danger']);
                }
            ],
            [

                'attribute' => 'created_at',
                'options' => ['width' => '150px;'],
                'format' => ['date', 'php:Y-m-d H:i:s']
            ],
            [

                'attribute' => 'updated_at',
                'options' => ['width' => '150px;'],
                'format' => ['date', 'php:Y-m-d H:i:s']
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'header' => '操作',
                'template' => '{edit} {auth} {delete}{reset}',
                'options' => ['width' => '300px;'],
                'buttons' => [
                    'edit' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-pencil"></i> 更新', ['update','id'=>$key], [
                            'title' => Yii::t('app', '更新'),
                            'class' => 'btn btn-xs red'
                        ]);
                    },
                    'auth' => function ($url, $model, $key) {
                      if($key !=Yii::$app->params['admin']) {
                          return Html::a('<i class="fa fa-user"></i> 授权', ['admin/assignment/view', 'id' => $key], [
                              'title' => Yii::t('app', '授权'),
                              'class' => 'btn btn-xs purple'
                          ]);
                      }
                    },
                    'delete' => function ($url, $model, $key) {
                        if($key !=Yii::$app->params['admin']) {
                            return Html::a('<i class="glyphicon glyphicon-trash"></i>删除', ['delete', 'id' => $key], [
                                'title' => Yii::t('app', '删除'),
                                'class' => 'btn btn-xs red ajax-get confirm'
                            ]);
                        }
                    },
                    'reset' => function ($url, $model, $key) {
                        if($key !=Yii::$app->params['admin']) {
                            return Html::a('<i class="glyphicon glyphicon-refresh"></i>重置密码','#', [
                                'title' => Yii::t('app', '重置密码'),
                                'class' => 'btn btn-xs red reset',
                                'name'=>$model->username,
                                'id'=>$model->id
                            ]);
                        }
                    }
                ],
            ],
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            //'{export}',
        ],


        'pjax' => false,
        'bordered' => true,
        'striped' => false,
        'condensed' => false,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => false,

        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            //'before'=>false,
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
</div>
<?php
$resetUrl = \yii\helpers\Url::toRoute(['reset-password']);
$js = <<<JS
 
    $(document).on('click','.reset',function() {
        var name = $(this).attr('name');
        var id = $(this).attr('id');
        layer.confirm('是否重置'+name+'的密码',{
            title:"重置密码提示",
         btn: ['确认','取消'] 
         ,cancel: function(index, layero){
             layer.msg('取消成功');  
        }
        },function() {
          $.get('{$resetUrl}',{id:id},function(data) {
                var response = JSON.parse(data);
                layer.msg(response.message);
          });
        },function() {
          layer.msg('取消成功');
        });
    });
JS;
$this->registerJs($js);
?>
