<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Warehouse */

$this->title = '查看资料';
$this->params['breadcrumbs'][] = ['label' => 'Warehouses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= Html::a('批量下载','#',['class'=>'btn btn-primary batch_download'])?>
<div class="warehouse-view">
    <table class="table">
        <thead>
            <th><input type="checkbox" value="0" class="all"></th>
            <th>
                文件类型
            </th>
            <th>
                资料名称
            </th>
            <th>
                操作
            </th>
        </thead>
    <tbody>
<?php
if(!empty($data)){
    foreach ($data as $v){
        $typeArray = [1=>'验厂报告',2=>'资料档案',3=>'验货报告',4=>'检验报告',5=>'产品及包装照片',6=>'不良照片'];
        $html ='<tr><td><input type="checkbox" name="download" value='.$v['id'].' ></td>';
        $html.='<td>'.$typeArray[$v['type']].'</td>';
        $html.="<td>".$v['file_name']."</td>";
        $html.="<td>".Html::a('下载',['download','id'=>$v['id']]).'&nbsp&nbsp&nbsp'.Html::a('删除',['delete-file','id'=>$v['id']])."</td>";
        $html.'</tr>';
        echo $html;
    }
}else{
    echo '<tr><td colspan="2" style="text-align: center">没有上传任何资料</td></tr>';
}?>
    </tbody>
    </table>
</div>
<?php
$batch_url = Yii::$app->request->hostInfo.\yii\helpers\Url::toRoute('batch-download');
$js = <<<JS
 $(document).on('click','.all',function() {
    if($(this).is(':checked')){
        $('[name="download"]').prop('checked',true);
    }else {
        $('[name="download"]').prop('checked',false);
    }
 });
  $(document).on('click','.batch_download',function() {
    var file_id = new Array();
    $('[name="download"]').each(function() {
        if($(this).is(':checked')){
            file_id.push($(this).val());
        }
    });
    if(file_id.length==0){
        layer.msg('请先勾选');
    }else {
        window.location.href="{$batch_url}"+"?ids="+file_id.join(',');
    }
  });
JS;
$this->registerJs($js);
?>

