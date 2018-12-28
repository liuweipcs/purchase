<?php
namespace app\models;
use app\config\Vhelper;
use Yii;

class PurchaseOrderPayType extends \yii\db\ActiveRecord
{

    public static function getPayType($pur_number)
    {
        $res = self::findOne(['pur_number' => $pur_number]);
        if($res) {
            return $res->request_type;
        } else {
            return 0;
        }
    }

    public static function setPayType($pur_number, $data)
    {
        $res = self::find()->where(['pur_number' => $pur_number])->one();
        if($res) {
            $res->request_type = $data['request_type'];
            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($res->attributes, $res->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            $result = $res->save();
        } else {
            $model = new self();
            $model->pur_number = $pur_number;
            $model->request_type = $data['request_type'];
            $result = $model->save();

            //表修改日志-新增
            $change_content = "insert:新增id值为{$model->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
        }
        return $result;
    }

    public function saveRows($data)
    {
        foreach($data as $v) {
            $res = self::findOne(['pur_number' => $v['pur_number']]);
            if($res) {
                $res->purchase_method = $v['purchase_method'];
                $result = $res->save();
            } else {
                $model = new self();
                $model->pur_number      = $v['pur_number'];
                $model->purchase_method = $v['purchase_method'];
                $result = $model->save();
            }
        }
        return $result;
    }

    public function saveOrderPayType($data)
    {
        foreach($data as $v) {
            $model = self::findOne(['pur_number' => $v['pur_number']]);
            if($model) {
                $model->pur_number              = $v['pur_number'];
                $model->freight                 = $v['freight'];
                $model->discount                = $v['discount'];
                $model->real_price              = $v['real_price'];
                $model->freight_formula_mode    = $v['freight_formula_mode'];
                $model->settlement_ratio        = $v['settlement_ratio'];
                $model->purchase_source         = $v['purchase_source'];
                $model->purchase_acccount       = $v['purchase_acccount'];
                $model->platform_order_number   = $v['platform_order_number'];

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $result = $model->save();
            } else {
                $model = new self();
                $model->pur_number              = $v['pur_number'];
                $model->freight                 = $v['freight'];
                $model->discount                = $v['discount'];
                $model->real_price              = $v['real_price'];
                $model->freight_formula_mode    = $v['freight_formula_mode'];
                $model->settlement_ratio        = $v['settlement_ratio'];
                $model->purchase_source         = $v['purchase_source'];
                $model->purchase_acccount       = $v['purchase_acccount'];
                $model->platform_order_number   = $v['platform_order_number'];
                $result = $model->save(false);

                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
            }
        }
        return $result;

    }

    public function saveOrderPayType2($data)
    {
        foreach($data as $v) {
            $model = self::findOne(['pur_number' => $v['pur_number']]);
            if($v['purchase_acccount'] || $v['platform_order_number']) {
                $purchase_source = 2;
            } else {
                $purchase_source = 1;
            }
            if($model) {
                $model->pur_number              = $v['pur_number'];
                $model->freight                 = $v['freight'];
                $model->discount                = $v['discount'];
                $model->real_price              = $v['real_price'];
                $model->purchase_source         = $purchase_source;
                $model->purchase_acccount       = $v['purchase_acccount'];
                $model->platform_order_number   = $v['platform_order_number'];
                if (!empty($v['settlement_ratio'])) {
                    $model->settlement_ratio = $v['settlement_ratio'];
                }

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                $result = $model->save();
            } else {
                $model = new self();
                $model->pur_number              = $v['pur_number'];
                $model->freight                 = $v['freight'];
                $model->discount                = $v['discount'];
                $model->real_price              = $v['real_price'];
                $model->purchase_source         = $purchase_source;
                $model->purchase_acccount       = $v['purchase_acccount'];
                $model->platform_order_number   = $v['platform_order_number'];
                if (!empty($v['settlement_ratio'])) {
                    $model->settlement_ratio = $v['settlement_ratio'];
                }
                $result = $model->save(false);

                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

            }
        }
        return $result;
    }

    // 用于保存合同采购确认模块的数据
    public function saveOrderPayType3($data)
    {
        foreach($data as $v) {
            $model = self::findOne(['pur_number' => $v['pur_number']]);
            $flag = true;
            if(!$model) {
                $flag = false;
                $model = new self();
            }
            $model->pur_number              = $v['pur_number'];
            $model->freight                 = $v['freight'];
            $model->discount                = $v['discount'];
            $model->real_price              = $v['real_price'];
            $model->purchase_source         = 1;
            $model->settlement_ratio        = $v['settlement_ratio'];

            $model->freight_formula_mode    = isset($v['freight_formula_mode']) ? $v['freight_formula_mode'] : '';

            if ($flag) {
                //修改
                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
            }
            $result = $model->save(false);

            if (!$flag) {
                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
            }
        }
        return $result;
    }
    /**
     * 关联订单记录
     * @return \yii\db\ActiveQuery
     */
    public  function  getOrders()
    {
        return $this->hasOne(PurchaseOrder::className(),['pur_number'=>'pur_number'])->where(['not in','purchas_status',[1,2,3,4,10]])->orderBy('id asc');
    }
    /**
     * 获取优惠信息
     */
    public static function getDiscountPrice($pur_number)
    {
        $discount_info = self::find()->where(['pur_number'=>$pur_number])->asArray()->one();
        if (!empty($discount_info)) {
            return $discount_info;
        } else {
            return false;
        }
    }

    // 获取订单的运费与优惠额的差值
    public static function getFreightMinusDiscount($opn)
    {
        $data = self::find()->where(['pur_number' => $opn])->asArray()->one();
        if(empty($data)) {
            return 0;
        } else {
            $f = !empty($data['freight']) ? $data['freight'] : 0;
            $d = !empty($data['discount']) ? $data['discount'] : 0;
            return $f - $d;
        }
    }

    //账号
    public static function getPurchaseAccount($pur_number)
    {
        $res = self::findOne(['pur_number' => $pur_number]);
        if($res) {
            return $res->purchase_acccount;
        } else {
            return '';
        }
    }

    //拍单号
    public static function getOrderNumber ($pur_number)
    {
        $res = self::findOne(['pur_number' => $pur_number]);
        if($res) {
            return $res->platform_order_number;
        } else {
            return '';
        }
    }

}
