<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseUser;
use yii\db\Query;
use yii\helpers\BaseArrayHelper;
/**
 * SkuSalesStatisticsSearch represents the model behind the search form about `app\models\SkuSalesStatistics`.
 */
class StockOwesSearch extends StockOwes
{
    public  $start_time;
    public  $end_time;
    public  $time_limit;
    public  $supplier_name;// 供应商名称

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['left_stock', 'status'], 'integer'],
            [['earlest_outofstock_date','time_limit','creater','start_time','end_time','default_buyer','product_status','sku','supplier_name'], 'safe'],
            [['sku'], 'string', 'max' => 100],
            [['warehouse_code'], 'string', 'max' => 50],
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

    public function search($params,$type=true)
    {
        $query = self::find();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
         //只查询易佰东莞仓库
        $query->where(['warehouse_code'=>'SZ_AA']);
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if($this->time_limit){
            $query->andFilterWhere(['<=','earlest_outofstock_date',date('Y-m-d H:i:s',time()-60*60*$this->time_limit)]);
        }
        if($this->default_buyer){
            $query->joinWith('buyer');
            if(is_numeric($this->default_buyer)){
                $user = PurchaseUser::find()->where(['pur_user_id'=>$this->default_buyer])->one();
                if($user){
                    $query->andFilterWhere(['pur_supplier_buyer.buyer' => $user->pur_user_name,]);
                }
            }else{
                $group=[
                    'g1'=>'1',
                    'g2'=>'2',
                    'g3'=>'3',
                    'g4'=>'4',
                    'g5'=>'5',

                ];
                $gid=isset($group[$this->default_buyer])?$group[$this->default_buyer]:[];
                $puid=PurchaseUser::find()->select('pur_user_name')->where(['group_id'=>$gid])->asArray()->all();
                if(count($puid)>0){
                    $query->andFilterWhere(['in', 'pur_supplier_buyer.buyer', array_values(BaseArrayHelper::map($puid,'pur_user_name','pur_user_name'))]);
                }else{
                    $query->andFilterWhere(['in', 'pur_supplier_buyer.buyer', ['null']]);
                }
            }
        }
        //$query->andFilterWhere(['status'=>1]);
        $query->andFilterWhere(['like','pur_stock_owes.sku',trim($this->sku)])
            ->andFilterWhere(['warehouse_code'=>$this->warehouse_code]);
            //->andFilterWhere(['between','earlest_outofstock_date',$this->start_time,$this->end_time]);
        if(!$this->start_time && !$this->end_time){
            $start_time = date('Y-m-d',strtotime("-6 month"));
            $end_time = date('Y-m-d');
            $query->andFilterWhere(['between','earlest_outofstock_date',$start_time,$end_time]);
        }else{
            $query->andFilterWhere(['between','earlest_outofstock_date',$this->start_time,$this->end_time]);
        }
        if($type==false){
            return $query;
        }
        return $dataProvider;
    }

    public function search1($params,$type=true)
    {
        $query = self::find();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->alias('sl');
        $query->select(['sku'=>'sl.sku','left_stock'=>'sum(sl.left_stock)']);
        //只查询易佰东莞仓库
        $query->where(['sl.warehouse_code'=>'SZ_AA']);
        $query->groupBy('sl.sku');
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if($this->product_status){
            $query->joinWith('product');
            $query->andFilterWhere(['pur_product.product_status'=>$this->product_status=='all' ? null : $this->product_status]);
        }
        if($this->default_buyer){
            $query->joinWith('buyer');
            if(is_numeric($this->default_buyer)){
                $user = PurchaseUser::find()->where(['pur_user_id'=>$this->default_buyer])->one();
                if($user){
                    $query->andFilterWhere(['pur_supplier_buyer.buyer' => $user->pur_user_name,]);
                }
            }else{
                $group=[
                    'g1'=>'1',
                    'g2'=>'2',
                    'g3'=>'3',
                    'g4'=>'4',
                    'g5'=>'5',
                ];
                $gid=isset($group[$this->default_buyer])?$group[$this->default_buyer]:[];
                $puid=PurchaseUser::find()->select('pur_user_name')->where(['group_id'=>$gid])->asArray()->all();
                if(count($puid)>0){
                    $query->andFilterWhere(['in', 'pur_supplier_buyer.buyer', array_values(BaseArrayHelper::map($puid,'pur_user_name','pur_user_name'))]);
                }else{
                    $query->andFilterWhere(['in', 'pur_supplier_buyer.buyer', ['null']]);
                }
            }
        }
        if($this->creater){
            $query->joinWith('product');
            $query->andFilterWhere(['like','pur_product.create_id',trim($this->creater)]);
        }
        if($this->time_limit){
            $query->andFilterWhere(['<=','earlest_outofstock_date',date('Y-m-d H:i:s',time()-60*60*$this->time_limit)]);
        }
        if($this->supplier_name){
            // 查找供应商代码
            $supplierInfo = SupplierSearch::find()->where("supplier_code=:supplier_name OR supplier_name=:supplier_name",array(":supplier_name" => $this->supplier_name))->one();
            $supplier_code = ($supplierInfo)?($supplierInfo->supplier_code):'NULL';
            // 查询该供应商下所有SKU
            $subQuery   = ProductProvider::find()->select('sku')->where("supplier_code='$supplier_code'");
            $query->andFilterWhere(['in','sl.sku',$subQuery]);
        }
        $query->andFilterWhere(['like','sl.sku',trim($this->sku)])
            ->andFilterWhere(['warehouse_code'=>$this->warehouse_code])
            ->andFilterWhere(['between','earlest_outofstock_date',$this->start_time,$this->end_time]);

        if($type==false){
            return $query;
        }
        return $dataProvider;
    }
}
