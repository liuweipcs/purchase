<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\SkuSalesStatistics;
use app\config\Curd;
use app\models\PurchaseSkuSingleTacticMain;
use app\models\Warehouse;
use app\models\PurchaseSkuSingleTacticMainContent;
/**
 * SkuSalesStatisticsSearch represents the model behind the search form about `app\models\SkuSalesStatistics`.
 */
class SkuSalesStatisticsSearch extends SkuSalesStatistics
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'days_sales_3', 'days_sales_7', 'days_sales_15', 'days_sales_30', 'days_sales_60', 'days_sales_90'], 'integer'],
            [['sku', 'warehouse_code', 'statistics_date','warehouse_name','pattern'], 'safe'],
        ];
    }
    public function attributes()
    {
        // 添加关联字段到可搜索属性集合
        return array_merge(parent::attributes(), ['warehouse_name','pattern']);
    }
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * 销量查看
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search2($params)
    {
        $query = PurchaseSuggest::find();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'days_sales_3' => $this->days_sales_3,
            'days_sales_7' => $this->days_sales_7,
            'days_sales_15' => $this->days_sales_15,
            'days_sales_30' => $this->days_sales_30,
            'days_sales_60' => $this->days_sales_60,
            'days_sales_90' => $this->days_sales_90,
            't1.pattern' => $this->pattern,
        ]);

        $query->andFilterWhere(['=', 'sku', $this->sku])
            ->andFilterWhere(['like', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['like', 'warehouse_name', $this->warehouse_name]);
        return $dataProvider;
    }
    
    /**
     * 国内仓搜索
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SkuSalesStatisticsTotal::find();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        $query->Where(['is_suggest'=>0]);
        $query->andWhere(['in','pur_sku_sales_statistics_total.warehouse_code',['SZ_AA']]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions 
        $query->andFilterWhere([
            'id' => $this->id,
            'days_sales_3' => $this->days_sales_3,
            'days_sales_7' => $this->days_sales_7,
            'days_sales_15' => $this->days_sales_15,
            'days_sales_30' => $this->days_sales_30,
            'days_sales_60' => $this->days_sales_60,
            'days_sales_90' => $this->days_sales_90,
        ]);

        $query->andFilterWhere(['like', 'sku', trim($this->sku)])
              ->andFilterWhere(['like', 't1.warehouse_code', trim($this->warehouse_code)])
              ->andFilterWhere(['like', 'warehouse_name', trim($this->warehouse_name)]);
        return $dataProvider;
    }
    /**
     * 海外仓搜索
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
        $query = SkuSalesStatistics::find()->joinWith('warehouse as t1');
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->Where(['is_suggest'=>0]);
        $query->andWhere(['not in','pur_sku_sales_statistics.warehouse_code',['DG','SZ_AA']]);
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'days_sales_3'    => $this->days_sales_3,
            'days_sales_7'    => $this->days_sales_7,
            'days_sales_15'   => $this->days_sales_15,
            'days_sales_30'   => $this->days_sales_30,
            'days_sales_60'   => $this->days_sales_60,
            'days_sales_90'   => $this->days_sales_90,
            'statistics_date' => $this->statistics_date,
            't1.pattern'      => $this->pattern,
        ]);

        $query->andFilterWhere(['like', 'sku', trim($this->sku)])
            ->andFilterWhere(['like', 't1.warehouse_code', trim($this->warehouse_code)])
            ->andFilterWhere(['like', 'warehouse_name', trim($this->warehouse_name)]);
        return $dataProvider;
    }
    
    
    public static function getSinglePattern($sku, $warehosuecode){
         $sealmodel      = New Curd();   //封装方法
         $purchase_sku_single_tactic_main_model = New  PurchaseSkuSingleTacticMain();   //单独sku补货策略设置
         $result =   $sealmodel->getData($purchase_sku_single_tactic_main_model,'id','one',"where sku='".$sku."' and warehouse='".$warehosuecode."'");
         if($result){
                return $result['id'];
            return  1;
         }else{
//                return false;
            return 2;
         }
        
    }
    
    public static function getWarehouseSkuPattern($warehousecode){
        $sealmodel      = New Curd();   //封装方法
        $warehousemodel = New Warehouse(); //仓库模型
        $result         = $sealmodel->getData($warehousemodel,'pattern','one',"where warehouse_code='".$warehousecode."'");
        
        return $result['pattern'];
               
    }
    
    public static function getSinglePatternContent($id){
        $sealmodel      = New Curd();   //封装方法
        $purchase_sku_single_tactic_main_content_model =  New PurchaseSkuSingleTacticMainContent();
        $purchase_content=$sealmodel->getData($purchase_sku_single_tactic_main_content_model,'produce_days, 
                                                transport_days, safe_stock_days, resupply_span','one',
                                                "where single_tactic_main_id='".$id."'");
        
        
    }
    
    
    
}
