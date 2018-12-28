<?php

namespace app\models;

use app\models\base\BaseModel;
use Yii;
use app\config\Vhelper;
use yii\data\ActiveDataProvider;

class PurchaseOrderBreakage extends BaseModel
{

    public $start_time;
    public $end_time;

    public static function tableName()
    {
        return '{{%purchase_order_breakage}}';
    }

    public function formName()
    {
        return '';
    }
    /**
     * [rules description]
     * @return [type] [description]
     */
    public function rules()
    {
        return [
            [['pur_number', 'sku', '
            name', 'qty', 'price', 'ctq',
                'breakage_num', 'items_totalprice',
                'apply_time', 'apply_person', 'apply_notice', 'audit_time',
                'audit_person', 'audit_notice', 'status'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'                => Yii::t('app', 'ID'),
            'pur_number'        => Yii::t('app', '采购单号'),
            'name'              => Yii::t('app', '商品名称'),
            'qty'               => Yii::t('app', '预期数量'),
            'price'             => Yii::t('app', '单价'),
            'ctq'               => Yii::t('app', '确认数量'),
            'breakage_num'      => Yii::t('app', '报损数量'),
            'items_totalprice'  => Yii::t('app', '报损金额'),
            'apply_time'        => Yii::t('app', '申请时间'),
            'apply_person'      => Yii::t('app', '申请人'),
            'apply_notice'      => Yii::t('app', '申请备注'),
            'audit_notice'      => Yii::t('app', '审核备注'),
            'status'            => Yii::t('app', '状态'),
        ];
    }
    /**
     * 关联订单详情一对一
     * @return \yii\db\ActiveQuery
     */
    public function  getPurchaseOrderItems()
    {
        return $this->hasOne(PurchaseOrderItems::className(), ['pur_number' => 'pur_number', 'sku'=>'sku']);
    }

    public function search($params)
    {
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->orderBy('apply_time desc');
        $this->attributes = $params;

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'pur_number', trim($this->pur_number)])
            ->andFilterWhere(['like', 'apply_person', trim($this->apply_person)])
            ->andFilterWhere(['like', 'sku', trim($this->sku)])
            ->andFilterWhere(['status'=>trim($this->status)]);

        if($this->apply_time) {
            $times = explode(' - ', $this->apply_time);
            $query->andFilterWhere(['>', 'apply_time', $times[0]]);
            $query->andFilterWhere(['<', 'apply_time', $times[1]]);
            $this->start_time = $times[0];
            $this->end_time = $times[1];
        }
        return $dataProvider;
    }

    public static function saveOnes($data)
    {
        foreach($data as $v) {
            $model = self::find()->where(['pur_number' => $v['pur_number'], 'sku' => $v['sku']])->one();
            //判断是否完全报损，是：退运费和优惠额,否：删除之前的记录
            $nums_arr = array_column($data, 'num');
            $nums = array_sum($nums_arr);
            $ctqs = PurchaseOrderItems::find()->where(['pur_number' => $data[0]['pur_number']])->sum('ctq');
            $bcc = bccomp($nums, $ctqs);

            $v['items_totalprice'] = $v['price']*$v['num'];
            $type_data = PurchaseOrderPayType::getDiscountPrice($v['pur_number']);
            $freight = $type_data?$type_data['freight']:0; //运费
            $discount = $type_data?$type_data['discount']:0; //优惠额

            if ($bcc===0) {
                $main_res = PurchaseOrderBreakageMain::saveOne($data[0]['pur_number']);
                $v['items_totalprice'] = $v['price']*$v['num']+$freight-$discount;
            } else {
                $delete_res = PurchaseOrderBreakageMain::deleteAll(['pur_number'=>$data[0]['pur_number']]);
            }

            if($model) {
                $flag = true;
                $res = self::SaveOne($model, $v, true);
            } else {
                $flag = false;
                $model = new self();
                $res = self::SaveOne($model, $v, false);
            }
        }

        
        return $res;
    }

    public static function SaveOne($model, $data, $flag=true)
    {
        $model->sku              = $data['sku'];
        $model->pur_number       = $data['pur_number'];
        $model->name             = $data['name'];
        $model->price            = $data['price'];
        $model->qty              = $data['qty'];
        $model->ctq              = $data['ctq'];
        $model->breakage_num     = $data['num'];
        $model->items_totalprice = $data['items_totalprice'];
        $model->apply_time       = date('Y-m-d H:i:s', time());
        $model->apply_person     = Yii::$app->user->identity->username;
        $model->apply_notice     = $data['apply_notice'];

        if ($flag) {
            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order_breakage', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
        }
        $res = $model->save(false);

        if (!$flag) {
            //表修改日志-新增
            $change_content = "insert:新增id值为{$model->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_order_breakage', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
        }
        return $res;
    }

    //获取报损数量
    public static function getNumber($sku,$pur_number)
    {
        if(!$sku || !$pur_number)
            return 0;

        $number = self::find()->select('breakage_num')->where(['sku'=>$sku,'refund_status'=>1,'pur_number'=>$pur_number])->one();
        $breakage_num = 0;
        if(isset($number['breakage_num']) && $number['breakage_num']>0){
            $breakage_num = $number['breakage_num'];
        }

        return $breakage_num;
    }

    /**
     * 采购单页面新增报损状态的显示
     * @param $pur_number
     * @return array|string
     */
    public static function getStatus($pur_number){
        $breakages = self::find()->where(['pur_number'=>$pur_number])->all();
        if(!$breakages){
            return '';
        }
        $arr_break = [];
        foreach ($breakages as $breakage){
            $arr_break[$breakage->sku] = $breakage->status;
        }
        return $arr_break;
    }
}
