<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ProductRepackage;
use app\models\Product;

/**
 * This is the model class for table "product_repackage".
 */
class ProductRepackageSearch extends ProductRepackage
{
    public $start_time;
    public $end_time;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku','audit_status','start_time','end_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public static function auditStatusList(){
        return [0 => '待审核',1 => '审核通过',2 => '审核不通过'];
    }


    /**
     * FBA 二次包装列表查询
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ProductRepackage::find();
        $query->alias('t');
        $query->andwhere(['=','t.sku_type',3]);
        $query->andwhere(['=','t.status',1]);

        $query->orderBy('t.id desc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => isset($params['per-page']) ? intval($params['per-page']) : 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if($params) {
            if(strpos($this->sku,' ')){
                $skuArr = explode(' ',$this->sku);
                $skuArr = array_diff(array_unique($skuArr),['']);

                $query->andFilterWhere(['in','t.sku',$skuArr]);
            }else{
                $query->andFilterWhere(['=','t.sku',trim($this->sku)]);
            }

            if($this->audit_status == 100){ $query->andFilterWhere(['!=','t.audit_status',0]);}
            elseif($this->audit_status == -1){ $query->andFilterWhere(['=','t.audit_status',0]);}
            else{ $query->andFilterWhere(['=','t.audit_status',$this->audit_status]);}

            $query->andFilterWhere(['between', 'add_time', $this->start_time, $this->end_time]);
        }

        return $dataProvider;
    }


    /**
     * 国内仓 二次包装列表查询
     * @param $params
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
        $query = ProductRepackage::find();
        $query->alias('t');
        $query->andwhere(['=','t.sku_type',1]);
        $query->andwhere(['=','t.status',1]);

        $query->orderBy('t.id desc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => isset($params['per-page']) ? intval($params['per-page']) : 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if($params) {
            if(strpos($this->sku,' ')){
                $skuArr = explode(' ',$this->sku);
                $skuArr = array_diff(array_unique($skuArr),['']);

                $query->andFilterWhere(['in','t.sku',$skuArr]);
            }else{
                $query->andFilterWhere(['=','t.sku',trim($this->sku)]);
            }

            if($this->audit_status == 100){ $query->andFilterWhere(['!=','t.audit_status',0]);}
            elseif($this->audit_status == -1){ $query->andFilterWhere(['=','t.audit_status',0]);}
            else{ $query->andFilterWhere(['=','t.audit_status',$this->audit_status]);}

            $query->andFilterWhere(['between', 'add_time', $this->start_time, $this->end_time]);
        }

        return $dataProvider;
    }


    /**
     * 判断SKU 是否是是加重类型：重 包 精 新
     * @param string    $sku      SKU
     * @param bool      $is_html  true 则返回 HTML
     * @param int       $show_type 展示类型 1.国内,2.海外,3.FBA
     * @return array|string
     */
    public static function getPlusWeightInfo($sku,$is_html = false,$show_type = 3){

        $sku_plug_weight = [];
        $sku_model = self::findOne(['sku' => $sku,'audit_status' => 1,'status' => 1]);
        ($sku_model) AND $sku_plug_weight['is_repackage'] = '1';// 二次包装SKU

        $sku_model = Product::findOne(['sku' => $sku]);
        ($sku_model AND $sku_model['is_boutique']) AND $sku_plug_weight['is_boutique'] = '1';// 精品SKU
        ($sku_model AND $sku_model['is_weightdot']) AND $sku_plug_weight['is_weightdot'] = '1';// 重点SKU

        $sku_model = Yii::$app->db->createCommand("SELECT sku FROM {{%cache_product_mark_data}} WHERE sku='$sku' AND is_new=1 ")->queryColumn();

        ($sku_model) AND $sku_plug_weight['is_new'] = '1';// 新品SKU(未采购过)

        if($is_html){// 拼接 HTML 字符串
            $weightSku = '';
            if($show_type == 1){// 1.国内
                $weightSku .= (isset($sku_plug_weight['is_repackage']) AND $sku_plug_weight['is_repackage'] == 1)?'包':'';
                $weightSku .= (isset($sku_plug_weight['is_new']) AND $sku_plug_weight['is_new'] == 1)?'新':'';
            }else{
                $weightSku .= (isset($sku_plug_weight['is_weightdot']) AND $sku_plug_weight['is_weightdot'] == 1)?'重':'';
                $weightSku .= (isset($sku_plug_weight['is_repackage']) AND $sku_plug_weight['is_repackage'] == 1)?'包':'';
                $weightSku .= (isset($sku_plug_weight['is_boutique']) AND $sku_plug_weight['is_boutique'] == 1)?'精':'';
                $weightSku .= (isset($sku_plug_weight['is_new']) AND $sku_plug_weight['is_new'] == 1)?'新':'';
            }

            if($weightSku) $weightSku = "<span style='position:absolute; color:red;font-size: 10px;'>$weightSku</span>";

            return $weightSku;
        }else{
            if($show_type == 1){
                unset($sku_plug_weight['is_boutique'],$sku_plug_weight['is_weightdot']);
            }
            return $sku_plug_weight;
        }
    }

    
    /**
     * 判断 采购单 是否是是加重类型：重 包 精 新
     * @param string $pur_number          采购单号
     * @param object $purchaseOrderItems  采购单明细
     * @return array|string
     */
    public static function getPlusWeightInfoByPurNumber($pur_number = null,$purchaseOrderItems = null,$show_type = 3){

        $subHtml = '';
        if($purchaseOrderItems){

        }elseif($pur_number){
            $model = \app\models\PurchaseOrder::findOne(['pur_number' => $pur_number]);
            $purchaseOrderItems = $model->purchaseOrderItems;
        }

        $list_tmp = [];

        // 根据采购单SKU展示 加重标记
        if($purchaseOrderItems){
            foreach($purchaseOrderItems as $value){
                $list = self::getPlusWeightInfo($value->sku,false,$show_type);// 加重SKU标记
                if(isset($list['is_weightdot']) AND $list['is_weightdot'] == 1) $list_tmp['is_weightdot'] = '重';
                if(isset($list['is_repackage']) AND $list['is_repackage'] == 1) $list_tmp['is_repackage'] = '包';
                if(isset($list['is_boutique'])  AND $list['is_boutique'] == 1)  $list_tmp['is_boutique']  = '精';
                if(isset($list['is_new'])       AND $list['is_new'] == 1)       $list_tmp['is_new']       = '新';
                if(count($list_tmp) == 2) break;
            }
        }
        //根据采购单号判断是否验货数据
        $check_tmp=[];
        if($pur_number){
            $exist = SupplierCheck::find()->where(['like','pur_number',$pur_number])->andWhere(['check_type'=>2])->andWhere('status<>4')->exists();
            if($exist){
                $check_tmp['is_check']       = '验';
            }
        }
        if(!empty($check_tmp)){
            $list_tmp = array_merge($check_tmp,$list_tmp);
        }
        if($list_tmp) $subHtml = "<span style='position:absolute; color:red;font-size: 10px;'>". implode(' ',$list_tmp)."</span>";

        return $subHtml;
    }

}
