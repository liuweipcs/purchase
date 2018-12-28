<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\web\JsExpression;
use Yii\helpers\Url;
use app\models\SupplierUpdateLog;
use app\services\SupplierServices;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use app\models\SupplierBuyer;
use app\models\SupplierImages;
use app\controllers\SupplierController;

?>
<div class="stockin-update">
    <?php $form = ActiveForm::begin(["options" => ["enctype" => "multipart/form-data"]]); ?>
<!--    <form class="needs-validation" action="audit" method="post" novalidate>-->
    <table class="table table-bordered">
        <thead>
            <tr>
                <td>字段名</td>
                <td>修改前</td>
                <td>修改后</td>
            </tr>
        </thead>
        <tbody>
        <?php
        $supplier_update_log_info = SupplierUpdateLog::find()->where(['in','supplier_code',$supplier_code])->andWhere(['in','audit_status',[1,3,5]])->one();

        $audit_status = !empty($supplier_update_log_info->audit_status) ? $supplier_update_log_info->audit_status : -1;

        $message = json_decode($supplier_update_log_info->message);
        $change_info = [];
        $is_update_bank = true;

            //供应商信息 -- 修改 ok ok
            if (!empty($message->supplier)) {
                $old = $message->supplier->old;
                $new = $message->supplier->new;
                foreach ($old as $k => $v) {
                    if ($new->$k != $v) {
                        $field_info = SupplierServices::publicInfo($k);

                        if ($k == 'supplier_level') { //供应商等级
                            $old_v = SupplierServices::getSupplierLevel($v);
                            $new_v = SupplierServices::getSupplierLevel($new->$k);

                            $old_v = is_array($old_v) ? '' : $old_v;
                            $new_v = is_array($new_v) ? '' : $new_v;
                        } elseif ($k == 'supplier_type') { //供应商类型
                            $old_v = SupplierServices::getSupplierType($v);
                            $new_v = SupplierServices::getSupplierType($new->$k);

                            $old_v = is_array($old_v) ? '' : $old_v;
                            $new_v = is_array($new_v) ? '' : $new_v;

                        } elseif ($k == 'supplier_settlement') { //结算方式
                            $old_v = SupplierServices::getSettlementMethod($v);
                            $new_v = SupplierServices::getSettlementMethod($new->$k);

                            $old_v = is_array($old_v) ? '' : $old_v;
                            $new_v = is_array($new_v) ? '' : $new_v;
                            /*if ($is_update_bank) {
                                echo '<input type="hidden" name="is_update_bank" value="1">';
                                $is_update_bank = false;
                            }*/
                        } elseif ($k == 'payment_method') { //支付方式
                            $old_v = SupplierServices::getDefaultPaymentMethod($v);
                            $new_v = SupplierServices::getDefaultPaymentMethod($new->$k);

                            $old_v = is_array($old_v) ? '' : $old_v;
                            $new_v = is_array($new_v) ? '' : $new_v;
                            /*if ($is_update_bank) {
                                echo '<input type="hidden" name="is_update_bank" value="1">';
                                $is_update_bank = false;
                            }*/
                        } elseif ($k == 'invoice') { //是否开发票
                            $old_v = SupplierServices::getInvoice($v);
                            $new_v = SupplierServices::getInvoice($new->$k);

                            $old_v = is_array($old_v) ? '' : $old_v;
                            $new_v = is_array($new_v) ? '' : $new_v;
                        } elseif ($k == 'province') { //所在省
                            $province_info = BaseServices::getCityList(1);
                            $province_old = $v;
                            $province_new = $new->$k;
                            $old_v = !empty($province_info[$v]) ? $province_info[$v] :'';
                            $new_v = !empty($province_info[$new->$k]) ? $province_info[$new->$k] : '';
                        } elseif ($k == 'city') { //城市
                            $city_info_old = BaseServices::getCityList($province_old);
                            $city_info_new = BaseServices::getCityList($province_new);

                            $city_old = $v;
                            $city_new = $new->$k;
                            $old_v = !empty($city_info_old[$v]) ? $city_info_old[$v] : '';
                            $new_v = !empty($city_info_new[$new->$k]) ? $city_info_new[$new->$k] : '';
                        } elseif ($k == 'area') { //地区
                            $area_info_old = BaseServices::getCityList($city_old);
                            $area_info_new = BaseServices::getCityList($city_new);

                            $old_v = !empty($area_info_old[$v]) ? $area_info_old[$v] : '';
                            $new_v = !empty($area_info_new[$new->$k]) ? $area_info_new[$new->$k] : '';
                        } elseif ($k == 'first_cooperation_time') { //首次合作时间
                            $old_v = $v;
                            $new_v = date('Y-m-d H:i:s',strtotime($new->$k));
                            if ($old_v == $new_v) {
                                continue;
                            }
                        } else {
                            $old_v = $v;
                            $new_v = $new->$k;
                        }
                        if(is_array($old_v)){
                            $old_v='';
                        }
                        if(is_array($new_v)){
                            $new_v='';
                        }
                        echo "<tr>";
                        echo "<td>{$field_info}</td>";
                        echo "<td>{$old_v}</td>";
                        echo "<td>{$new_v}</td>";
                        echo "</tr>";
                    } else {
                        if ($k == 'province') { //所在省
                            $province_old = $v;
                            $province_new = $new->$k;
                        } elseif ($k == 'city') { //城市
                            $city_old = $v;
                            $city_new = $new->$k;
                        }
                    }
                }
            }

            //供应商支付帐号表 -- 修改 ok ok
            if (!empty($message->supplier_payment_account_update)) {
                $old = $message->supplier_payment_account_update->old;
                $new = $message->supplier_payment_account_update->new;
                foreach ($old as $ok => $ov) {
                    foreach ($ov as $k=>$v) {
                        if ($new[$ok]->$k != $v) {
                            $field_info = SupplierServices::publicInfo($k);
                            if ($k == 'payment_platform') { //支付平台
                                $old_v = SupplierServices::getPaymentPlatform($v);
                                $new_v = SupplierServices::getPaymentPlatform($new[$ok]->$k);
                            }elseif ($k == 'payment_platform_bank') { //请录入支行名称
                                $old_v = !empty($v) ? \app\models\UfxFuiou::getMasterBankInfo($v) : '';
                                $new_v = !empty($new[$ok]->$k) ? \app\models\UfxFuiou::getMasterBankInfo($new[$ok]->$k) : '';
                            }elseif ($k =='prov_code'){
                                $old_v = !empty($v) ? \app\models\UfxFuiou::getProvInfo($v) : '';
                                $new_v = !empty($new[$ok]->$k) ? \app\models\UfxFuiou::getProvInfo($new[$ok]->$k) : '';
                            }elseif ($k =='city_code'){
                                $old_v = !empty($v) ? \app\models\UfxFuiou::getCityInfo($v) : '';
                                $new_v = !empty($new[$ok]->$k) ? \app\models\UfxFuiou::getCityInfo($new[$ok]->$k) : '';
                            } elseif ($k == 'account' || $k == 'account_name' || $k == 'account_type') { //账户、账户名、账户类型
                                $old_v = $v;

                                 
                                $new_v = $new[$ok]->$k;
                                if (!empty($old_v) || !empty($new_v)) {
                                    if ($is_update_bank) {
                                        echo '<input type="hidden" name="is_update_bank" value="1">';
                                        $is_update_bank = false;
                                    }
                                }
                                if ($k == 'account_type') { //账户类型
                                    $old_v = !empty($v) ? SupplierServices::getAccountType($v) : '';
                                    $new_v = !empty($new[$ok]->$k) ? SupplierServices::getAccountType($new[$ok]->$k) : '';
                                }
                            } else {
                                $old_v = $v;
                                $new_v = $new[$ok]->$k;
                            }

                            if(is_array($old_v)){
                                $old_v='';
                            }
                            if(is_array($new_v)){
                                $new_v='';
                            }
                            echo "<tr>";
                            echo "<td>{$field_info}</td>";
                            echo "<td>{$old_v}</td>";
                            echo "<td>{$new_v}</td>";
                            echo "</tr>";
                           
                        }
                    }
                }
            }

            //供应商支付帐号表 -- 新增 ？？ 未翻译 无数据测试
            if (!empty($message->supplier_payment_account_insert)) {
                foreach ($message->supplier_payment_account_insert as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        $field_info = SupplierServices::publicInfo($k);

                        if ($k == 'payment_method') {
                           $res = SupplierServices::getPaymentMethod($v);
                        }elseif ($k == 'payment_platform') { //支付平台
                            $res = SupplierServices::getPaymentPlatform($v);
                        }elseif ($k =='prov_code'){
                            $res = !empty($v) ? \app\models\UfxFuiou::getProvInfo($v) : '';
                        }elseif ($k =='city_code'){
                            $res = !empty($v) ? \app\models\UfxFuiou::getCityInfo($v) : '';
                        } elseif ($k == 'payment_platform_bank') { //请录入支行名称
                            $res = !empty($v) ? \app\models\UfxFuiou::getMasterBankInfo($v) : '';
                        } elseif ($k == 'account' || $k == 'account_name' || $k == 'account_type') { //账户、账户名、账户类型
                            if (!empty($v)) {
                                if ($is_update_bank) {
                                    echo '<input type="hidden" name="is_update_bank" value="1">';
                                    $is_update_bank = false;
                                }
                            }
                            $res = $v;
                            if ($k == 'account_type') { //账户类型
                                $res = !empty($v) ? SupplierServices::getAccountType($v) : '';
                            }
                        } else {
                            $res = $v;
                        }
                        if(is_array($res)){
                            $res='';
                        }
                        $info .= $field_info . '：' . $res . "<br />";
                    }
                    echo "<tr>";
                    echo "<td>新增支付帐号</td>";
                    echo "<td></td>";
                    echo "<td>{$info}</td>";
                    echo "</tr>";

                }
            }

        //供应商支付帐号表 -- 删除 ？？ 未翻译 无数据测试
        if (!empty($message->supplier_payment_account_delete)) {
            foreach ($message->supplier_payment_account_delete as $ok => $ov) {
                $info = '';
                foreach ($ov as $k=>$v) {
                    $field_info = SupplierServices::publicInfo($k);
                    if($field_info=='未知'){
                        continue;
                    }
                    if ($k == 'payment_method') {
                        $res = SupplierServices::getPaymentMethod($v);
                    }elseif ($k == 'payment_platform') { //支付平台
                        $res = SupplierServices::getPaymentPlatform($v);
                    } elseif ($k == 'payment_platform_bank') { //请录入支行名称
                        $res = !empty($v) ? \app\models\UfxFuiou::getMasterBankInfo($v) : '';
                    } elseif ($k == 'account' || $k == 'account_name' || $k == 'account_type') { //账户、账户名、账户类型
                        if (!empty($v)) {
                            if ($is_update_bank) {
                                echo '<input type="hidden" name="is_update_bank" value="1">';
                                $is_update_bank = false;
                            }
                        }
                        $res = $v;
                        if ($k == 'account_type') { //账户类型
                            $res = !empty($v) ? SupplierServices::getAccountType($v) : '';
                        }
                    } else {
                        $res = $v;
                    }
                    $res =   is_array($res) ? '' : $res;
                    $info .= $field_info . '：' . $res . "<br />";
                }
                echo "<tr>";
                echo "<td>删除支付账号</td>";
                echo "<td></td>";
                echo "<td>{$info}</td>";
                echo "</tr>";

            }
        }

            //供应商联系方式 -- 修改 ok ok
            if (!empty($message->supplier_contact_information_update)) {
                $old = $message->supplier_contact_information_update->old;
                $new = $message->supplier_contact_information_update->new;
                foreach ($old as $ok => $ov) {
                    foreach ($ov as $k=>$v) {
                        if ($new[$ok]->$k != $v) {
                            $field_info = SupplierServices::publicInfo($k);

                            echo "<tr>";
                            echo "<td>{$field_info}</td>";
                            echo "<td>{$v}</td>";
                            echo "<td>{$new[$ok]->$k}</td>";
                            echo "</tr>";
                        }
                    }
                }
            }

            //供应商联系方式 -- 新增 ？？ 未翻译 无数据测试
            if (!empty($message->supplier_contact_information_insert)) {
                foreach ($message->supplier_contact_information_insert as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        $field_info = SupplierServices::publicInfo($k);
                        $info .= $field_info . '：' . $v . "<br />";
                    }
                    echo "<tr>";
                    echo "<td>新增联系方式</td>";
                    echo "<td></td>";
                    echo "<td>{$info}</td>";
                    echo "</tr>";
                }
            }

            //供应商附图 -- 新增 ok ok
            if (!empty($message->supplier_images)) {
                $new = $message->supplier_images;
                $old = SupplierImages::find()->where(['supplier_id'=>$new->supplier_id, 'image_status'=>1])->one();
                if (empty($old)) {
                    foreach ($new as $nk => $nv) {
                        if (!empty($nv) && $nk != 'supplier_id') {
                            $info = '';
                            $info = "图片ID：{$nk}<br />图片URL：<img src='{$nv}' class='viewImg2' width='100' height='50'>";
                            echo "<tr>";
                            echo "<td>新增图片</td>";
                            echo "<td></td>";
                            echo "<td>{$info}</td>";
                            echo "</tr>";
                        }
                    }
                } else {
                    foreach ($new as $nk => $nv) {
                        if (!empty($nv) && $nk != 'supplier_id') {
                            $info = '';
                            $info = "图片ID：{$nk}<br />图片URL：<img src='{$nv}' class='viewImg2' width='100' height='50'>";
                            echo "<tr>";
                            echo "<td>新增图片</td>";
                            echo "<td></td>";
                            echo "<td>{$info}</td>";
                            echo "</tr>";
                        }
                    }
                }
            }

            //供应商绑定采购员 -- 修改/新增 ok ok
            if (!empty($message->supplier_buyer)) {
                $old = $message->supplier_buyer->old;
                $new = $message->supplier_buyer->new;
                foreach ($new as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        if (!empty($old[$ok])) { //如果存在，代表修改
                            if ($old[$ok]->$k != $v) {
                                if ($k == 'type') { //所属部门 1，国内仓 2，海外仓 3，FBA
                                    $old_v = PurchaseOrderServices::getPurchaseType($old[$ok]->$k);
                                    $new_v = PurchaseOrderServices::getPurchaseType($v);
                                    $old_v = is_array($old_v) ? '' : $old_v;
                                    $new_v = is_array($new_v) ? '' : $new_v;
                                } elseif ($k == 'status') { //状态(1启用2停用3删除)
                                    $old_v = SupplierServices::getBuyerStatus($old[$ok]->$k);
                                    $new_v = SupplierServices::getBuyerStatus($v);
                                    $old_v = is_array($old_v) ? '' : $old_v;
                                    $new_v = is_array($new_v) ? '' : $new_v;
                                } else {
                                    $old_v = $old[$ok]->$k;
                                    $new_v = $v;
                                }

                                $field_info = SupplierServices::publicInfo($k);
                                echo "<tr>";
                                echo "<td>{$field_info}</td>";
                                echo "<td>{$old_v}</td>";
                                echo "<td>{$new_v}</td>";
                                echo "</tr>";
                            }
                        } else { //新增
                            if ($k == 'id') {
                                continue;
                            }
                            $field_info = SupplierServices::publicInfo($k);
                            if ($k == 'type') { //所属部门 1，国内仓 2，海外仓 3，FBA
                                $res_v = PurchaseOrderServices::getPurchaseType($v);
                            } elseif ($k == 'status') { //状态(1启用2停用3删除)
                                $res_v = SupplierServices::getBuyerStatus($v);
                            } else {
                                $res_v = $v;
                            }
                            $info .= $field_info . '：' . $res_v . "<br />";
                            $old_v = $info;
                            $new_v = $info;
                        }
                    }
                    if (!empty($info)) {
                        echo "<tr>";
                        echo "<td>新增采购员</td>";
                        echo "<td></td>";
                        echo "<td>{$info}</td>";
                        echo "</tr>";
                    }
                }
            }

            //供应商绑定产品线--修改、新增 !!!!翻译合并(??每次都会新增)
            if (!empty($message->supplier_product_line)) {
                $supplier_product_line_info = $message->supplier_product_line->supplier_product_line;
                foreach ($supplier_product_line_info as $ok => $ov) {
                    $info = '';
                    foreach ($ov as $k=>$v) {
                        $field_info = SupplierServices::publicInfo($k);

                        if ($k == 'id') { //过滤掉id
                            continue;
                        } elseif ($k == 'first_product_line') { //一级产品线
                            $new_v = BaseServices::getProductLine($v);
                            $first_product_line = $v;
                        } elseif ($k == 'second_product_line') { //二级产品线
                            $new_v = BaseServices::getProductLineList($first_product_line)[$v];
                            $second_product_line = $v;
                        } elseif ($k == 'third_product_line') { //三级产品线
                            $new_v = !empty(BaseServices::getProductLineList($second_product_line)[$v]) ? BaseServices::getProductLineList($second_product_line)[$v] : '';
                        } elseif ($k == 'status') { //状态
                            continue;
                            $new_v = !empty($v) ? SupplierServices::getBuyerStatus($v) : '';
                        } else {
                            $new_v = $v;
                        }
                        $info .= $field_info . '：' . $new_v . "<br />";
                    }
                    echo "<tr>";
                    echo "<td>新增产品线</td>";
                    echo "<td></td>";
                    echo "<td>{$info}</td>";
                    echo "</tr>";
                }
            }
        if (!empty($message->supplier_product_line_insert)) {
            $supplier_product_line_insert = $message->supplier_product_line_insert;
            foreach ($supplier_product_line_insert as $ok => $ov) {
                $info = '';
                foreach ($ov as $k=>$v) {
                    $field_info = SupplierServices::publicInfo($k);

                    if ($k == 'id') { //过滤掉id
                        continue;
                    } elseif ($k == 'first_product_line') { //一级产品线
                        $new_v = BaseServices::getProductLine($v);
                        $first_product_line = $v;
                    } elseif ($k == 'second_product_line') { //二级产品线
                        $new_v = BaseServices::getProductLineList($first_product_line)[$v];
                        $second_product_line = $v;
                    } elseif ($k == 'third_product_line') { //三级产品线
                        $new_v = !empty(BaseServices::getProductLineList($second_product_line)[$v]) ? BaseServices::getProductLineList($second_product_line)[$v] : '';
                    } elseif ($k == 'status') { //状态
                        continue;
                        $new_v = !empty($v) ? SupplierServices::getBuyerStatus($v) : '';
                    } else {
                        $new_v = $v;
                    }
                    $info .= $field_info . '：' . $new_v . "<br />";
                }
                echo "<tr>";
                echo "<td>新增产品线</td>";
                echo "<td></td>";
                echo "<td>{$info}</td>";
                echo "</tr>";
            }
        }

        if (!empty($message->supplier_product_line_delete)) {
            $supplier_product_line_delete = $message->supplier_product_line_delete;
            foreach ($supplier_product_line_delete as $ok => $ov) {
                $info = '';
                foreach ($ov as $k=>$v) {
                    $field_info = SupplierServices::publicInfo($k);

                    if ($k == 'id') { //过滤掉id
                        continue;
                    } elseif ($k == 'first_product_line') { //一级产品线
                        $new_v = \app\models\ProductLine::find()->select('linelist_cn_name')->where(['product_line_id'=>$v])->scalar();
                        $new_v = $new_v ? $new_v : '';
                    } elseif ($k == 'second_product_line') { //二级产品线
                        $v = \app\models\ProductLine::find()->select('linelist_cn_name')->where(['product_line_id'=>$v])->scalar();

                        $new_v = $v ? $v  :'';
                    } elseif ($k == 'third_product_line') { //三级产品线
                        $v = \app\models\ProductLine::find()->select('linelist_cn_name')->where(['product_line_id'=>$v])->scalar();
                        $new_v = $v ? $v  :'';
                    } elseif ($k == 'status') { //状态
                        continue;
                        $new_v = !empty($v) ? SupplierServices::getBuyerStatus($v) : '';
                    } else {
                        $new_v = $v;
                    }
                    $info .= $field_info . '：' . $new_v . "<br />";
                }
                echo "<tr>";
                echo "<td>删除产品线</td>";
                echo "<td></td>";
                echo "<td>{$info}</td>";
                echo "</tr>";
            }
        }
            ?>

        <tr>
            <td rowspan="2" style="vertical-align:middle;">审核结果</td>
            <td colspan="2">
                <input class="form-check-input" type="radio" name="is_audit" id="pass_id" value="1" checked>
                <label class="form-check-label" for="pass_id" style="margin-right: 50px;">通过</label>

                <input class="form-check-input" type="radio" name="is_audit" id="overrule_id" value="0">
                <label class="form-check-label" for="overrule_id">不通过</label>

            </td>
        </tr>
        <tr>
            <td colspan="2"><textarea class="form-control" name="audit_note"></textarea></td>
            <input type="hidden" name="audit_status" value="<?=$audit_status?>">
            <input type="hidden" name="supplier_code" value="<?=$supplier_code?>">
        </tr>
        </tbody>
    </table>
    <div id="outerdiv" style="position:fixed;top:100px;left:30%;background:rgba(0,0,0,0.7);z-index:2000;display:none;">
        <div id="innerdiv" style="position:absolute;"><img id="bigimg" style="border:5px solid #fff;" src="" /></div>
    </div>
        <div class="form-group">
            <?php

            if($audit_status == 5) {
                echo '<button type="submit" class="btn btn-primary">待财务审核</button>';
            }else if($audit_status == 3) {
                echo '<button type="submit" class="btn btn-primary">待供应链审核</button>';
            } else if($audit_status == 1) {
                echo '<button type="submit" class="btn btn-primary">待采购审核</button>';
            }
            ?>
        </div>
<!--    </form>-->
    <?php ActiveForm::end(); ?>
</div>
<?php
//$applyUrls  = Url::toRoute('/supplier-goods/apply');
$js = <<<JS
//图片放大  
      $(function(){  
        $(".viewImg2").click(function(){  
            var _this = $(this);//将当前的pimg元素作为_this传入函数    
            imgShow("#outerdiv", "#innerdiv", "#bigimg", _this);    
          });
        });    
      
        function imgShow(outerdiv, innerdiv, bigimg, _this){  
            var src = _this.attr("src");//获取当前点击的pimg元素中的src属性    
            $('#outerdiv').attr('display','block');  
            $(bigimg).attr("src", src);//设置#bigimg元素的src属性    
             $(outerdiv).fadeIn("fast");  
            
        $(outerdiv).click(function(){//再次点击淡出消失弹出层    
            $(this).fadeOut("fast");    
        });    
    }  
JS;
$this->registerJs($js);
?>