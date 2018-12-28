<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 10:47
 */
?>
<table class="layui-table"  lay-even>
    <tbody>
        <tr>
            <td>公司名称</td>
            <td><?=empty($company_info->name) ?'': $company_info->name?></td>
            <td>别称</td>
            <td><?=empty($company_info->alias) ?'': $company_info->alias?></td>
            <td>英文名</td>
            <td><?=empty($company_info->property3) ?'': $company_info->property3?></td>
        </tr>
        <tr>
            <td>法人代表</td>
            <td><?=empty($company_info->legal_person_name) ?'': $company_info->legal_person_name?></td>
            <td>法人类型</td>
            <td>
                <?php
                $typeArray=[1=>'个人',2=>'公司'];
                echo isset($typeArray[$company_info->type]) ? $typeArray[$company_info->type] :'未知类型';
                ?>
            </td>
            <td>公司类型</td>
            <td><?=empty($company_info->company_org_type) ?'': $company_info->company_org_type?></td>
        </tr>
        <tr>
            <td>注册资金</td>
            <td><?=empty($company_info->reg_capital) ?'': $company_info->reg_capital?></td>
            <td>注册地址</td>
            <td><?=empty($company_info->reg_location) ?'': $company_info->reg_location?></td>
            <td>省份简称</td>
            <td><?=empty($company_info->base) ?'': $company_info->base?></td>
        </tr>
        <tr>
            <td>核准时间</td>
            <td><?=empty($company_info->approved_time) ?'': $company_info->approved_time?></td>
            <td>核准机构</td>
            <td><?=empty($company_info->org_approved_institute) ?'': $company_info->org_approved_institute?></td>
            <td>登记机关</td>
            <td><?=empty($company_info->reg_institute) ?'': $company_info->reg_institute?></td>
        </tr>
        <tr>
            <td>组织机构代码</td>
            <td><?=empty($company_info->org_number) ?'': $company_info->org_number?></td>
            <td>纳税人识别号</td>
            <td><?=empty($company_info->tax_number) ?'': $company_info->tax_number?></td>
            <td>社会统一信用代码</td>
            <td><?=empty($company_info->credit_code) ?'': $company_info->credit_code?></td>
        </tr>
        <tr>
            <td>经营状态</td>
            <td><?=empty($company_info->reg_status) ?'': $company_info->reg_status?></td>
            <td>成立日期</td>
            <td><?=empty($company_info->estiblish_time) ?'': date('Y-m-d H:i:s',$company_info->estiblish_time/1000)?></td>
            <td>经营结束时间</td>
            <td><?=empty($company_info->to_time)|| strlen($company_info->to_time)>13 ?'': date('Y-m-d H:i:s',$company_info->to_time/1000)?></td>
        </tr>
        <tr>
            <td>行业</td>
            <td><?=empty($company_info->industry) ?'': $company_info->industry?></td>
            <td>行业分数</td>
            <td><?=empty($company_info->category_score) ?'': $company_info->category_score?></td>
            <td>企业评分</td>
            <td><?=empty($company_info->percentile_score) ?'': $company_info->percentile_score?></td>
        </tr>
        <tr>
            <td>数据来源</td>
            <td><?=empty($company_info->source_flag) ?'': $company_info->source_flag?></td>
            <td>数据刷新时间</td>
            <td><?=empty($company_info->refresh_time) ?'': $company_info->refresh_time?></td>
            <td>人数范围</td>
            <td><?=empty($company_info->staff_num_range) ?'': $company_info->staff_num_range?></td>
        </tr>
        <tr>
            <td>经营范围</td>
            <td colspan="5"><?=empty($company_info->business_scope) ?'': $company_info->business_scope?></td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: center">股东信息<i class="layui-icon layui-icon-group" id="info_control" style="font-size: 15px; color: #1E9FFF;" title="点击查看股东信息"></i>  </td>
        </tr>
    </tbody>
</table>
        <table class="company_holder_info layui-table" style="display: none">
            <tbody>
        <?php if(!empty($company_info->companyHolderList)){?>
            <?php foreach ($company_info->companyHolderList as $value){?>
                <tr>
                    <td>拥有公司个数</td>
                    <td><?= $value->toco?></td>
                    <td>出资金额</td>
                    <td><?= $value->amount?></td>
                    <td>股东类型</td>
                    <td><?php $typeArray=[1=>'个人',2=>'公司'];
                        echo isset($typeArray[$company_info->type]) ? $typeArray[$company_info->type] :'未知类型';?></td>
                </tr>
                <tr>
                    <td>股东名</td>
                    <td><?= $value->name?></td>
                    <td>股东状态</td>
                    <td><?= $value->status==1 ? '正常': '删除'?></td>
                    <td>更新时间</td>
                    <td><?=$value->update_time?></td>
                </tr>
                <tr>
                    <td colspan="3">认缴金额</td>
                    <td colspan="2">出资形式</td>
                    <td colspan="2">出资比例</td>
                </tr>
                <?php if(!empty($value->holderCapital)){?>
                    <?php foreach ($value->holderCapital as $val){?>
                        <tr>
                            <td colspan="3"><?=empty($val->amomon) ? '' :$val->amomon?></td>
                            <td colspan="2"><?=empty($val->paymet) ? '' :$val->paymet?></td>
                            <td colspan="2"><?=empty($val->percent) ? '' :$val->percent?></td>
                        </tr>
                    <?php }?>
                <?php }?>
            <?php }?>
        <?php } ?>
    </tbody>
</table>
<?php
$js=<<<JS
$('#info_control').on('click',function() {
    if(!$('.company_holder_info').is(':visible')){
        $('.company_holder_info').show();
        $(this).attr('title','点击隐藏股东信息');
    }else {
         $('.company_holder_info').hide();
        $(this).attr('title','点击显示股东信息');
    }
  
});

JS;
$this->registerJs($js);
?>
