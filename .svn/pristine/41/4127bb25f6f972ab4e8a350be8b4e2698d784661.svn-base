<?php
use app\services\BaseServices;

?>

<h4 class="modal-title">采购单跟踪备注</h4>
<div class="row">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>序号</th>
            <th>快递号</th>
            <th>运费</th>
            <th>快递公司</th>
            <th>内容</th>
            <th>添加人</th>
            <th>添加时间</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if(is_array($model)){
            foreach($model as $k=>$v){
                $s =!empty($v->cargo_company_id) ? $v->cargo_company_id : '';
                $url ='https://www.kuaidi100.com/chaxun?com='.$s.'&nu='.$v->express_no;

                $express_no = !preg_match ("/^[a-z]/i",$v->cargo_company_id)?"<a target='_blank' href='$url'>$v->express_no</a>":"<a target='_blank' href='$url'>$v->express_no</a>";

                ?>
                <tr>
                    <td><?=$k+1?></td>
                    <td><?=$express_no?></td>
                    <td><?=$v->freight?></td>
                    <td><?php
                        if (preg_match("/[\x7f-\xff]/", $v->cargo_company_id)){
                            echo $v->cargo_company_id;
                        } else{
                            echo '';
                        }
                        ?></td>
                    <td><?=$v->note?></td>
                    <td><?=BaseServices::getEveryOne($v->create_user_id)?></td>
                    <td><?=$v->create_time?></td>
                </tr>
            <?php }?>
        <?php }?>
        </tbody>
    </table>
</div>