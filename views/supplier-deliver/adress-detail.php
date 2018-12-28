<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/9
 * Time: 15:02
 */
?>
<h3 style="color: red"><?= $supplier_name ?></h3>
<?= \yii\helpers\Html::button('异常标记已处理',['class'=>' btn btn-info check-adress','data-toggle' => 'modal',
    'data-target' => '#adress-detail',])?>
<table class="table border">
    <thead>
        <th>
            省
        </th>
        <th>
            市
        </th>
        <th>
            区
        </th>
        <th>
            地址
        </th>
        <th>
            地址状态
        </th>
    </thead>
    <tbody>
        <?php foreach ($data as $v){?>
            <tr>
                <td>
                    <?= $v->province?>
                </td>
                <td>
                    <?= $v->city?>
                </td>
                <td>
                    <?= $v->area?>
                </td>
                <td>
                    <?= $v->adress?>
                </td>
                <td>
                    <?= $v->is_check==0 ? '未处理' :'已处理'?>
                </td>
            </tr>
        <?php }?>
    </tbody>

</table>

<?php
\yii\bootstrap\Modal::begin([
    'id' => 'adress-detail',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        'tabindex' => false
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
\yii\bootstrap\Modal::end();
$check = \yii\helpers\Url::toRoute(['check-adress','supplier_code'=>$supplier_code]);
$js = <<<JS

$(document).on('click','.check-adress',function() {
  $.get('{$check}',{},function(data) {
        $('#adress-detail').find('.modal-body').html(data);
    });
})

JS;
$this->registerJs($js);
?>
