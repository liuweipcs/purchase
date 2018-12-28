<?php
$this->title = '合同采购确认';
$this->params['breadcrumbs'][] = '海外仓';
$this->params['breadcrumbs'][] = '采购计划单';
$this->params['breadcrumbs'][] = $this->title;
$url = \yii\helpers\Url::to(['/purchase-compact/search-compact']);
?>
<div class="my-box" style="margin-bottom: 45px;">
    <div class="bg-line no">
        <span>1</span>
        <p>确认采购单信息</p>
    </div>
    <div class="bg-line">
        <span>2</span>
        <p>确认合同信息</p>
    </div>
</div>


<div class="my-box">

    <table class="my-table">
        <thead>
        <tr>
            <th>名称</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach($tpls as $tpl): ?>
        <tr>
            <td><a href="?pos=<?= $pos ?>&tid=<?= $tpl['id'] ?>" title="使用模板"><?= $tpl['name'] ?></a></td>
        </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

</div>


<?php
$js = <<<JS

$(function() {
    
    $('#join_compact').change(function() {
        if($(this).val() == '') {
            return false;
        }
        var cid = $(this).val();
        var url = location.search + '&cid='+cid;
        location.href = url;
    });
    
    $('.img-list li').click(function() {
        var tid = $(this).attr('data-tid');
        var url = location.search + '&tid='+tid;
        location.href = url;
    });
    
});


JS;
$this->registerJs($js);
?>





