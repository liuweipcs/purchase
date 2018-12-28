<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Supplier;

/**
 * WlClientRecieveInvoicesSearch represents the model behind the search form about `app\models\WlClientRecieveInvoices`.
 */
class SupplierSearch extends Supplier
{
    public $start_time;
    public $end_time;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'status','supplier_settlement','supplier_level'], 'integer'],
            [['create_id','supplier_code','default_buyer','source','first_line','second_line','third_line','buyer','merchandiser','status','main_category','supplier_name','start_time','end_time','page_size','sul.audit_status','supplier_special_flag','special_flag_user','special_flag_time'],'safe']
        ];
    }
    public function attributes()
    {
        // 添加关联字段到可搜索属性集合
        return array_merge(parent::attributes(), ['sul.audit_status']);
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $noDataProvider = false)
    {
        $query = Supplier::find();
    
        // add conditions that should always apply here
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        $query->alias('t');


        if (isset($params['sort'])) {
            $dataProvider->setSort([
                'attributes' => [
                    'first_cooperation_time' => [
                        'desc' => ['first_cooperation_time' => SORT_DESC],
                        'asc' => ['first_cooperation_time' => SORT_ASC],
                        'label' => 'first_cooperation_time'
                    ],
                    'cooperation_time' => [
                        'desc' => ['cooperation_time' => SORT_DESC],
                        'asc' => ['cooperation_time' => SORT_ASC],
                        'label' => 'cooperation_time'
                    ],
                    'purchase_time' => [
                        'desc' => ['purchase_time' => SORT_DESC],
                        'asc' => ['purchase_time' => SORT_ASC],
                        'label' => 'purchase_time'
                    ],
                    'cooperation_price' => [
                        'desc' => ['cooperation_price' => SORT_DESC],
                        'asc' => ['cooperation_price' => SORT_ASC],
                        'label' => 'cooperation_price'
                    ],
                    'sku_num' => [
                        'desc' => ['sku_num' => SORT_DESC],
                        'asc' => ['sku_num' => SORT_ASC],
                        'label' => 'sku_num'
                    ],
                ]
            ]);
        } else {
            $query->orderBy('t.id desc');
        }



        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            't.supplier_settlement' => $this->supplier_settlement,
            't.supplier_code'    =>trim($this->supplier_code),
            't.create_id'    =>$this->create_id,
            't.source'    =>$this->source,
            't.supplier_special_flag'       => $this->supplier_special_flag,
        ]);

        if ($this->getAttribute('sul.audit_status'))
        {
            // $query->leftJoin('supplierUpdateLog AS sul')->orderBy('sul.id desc');
            $query->leftJoin('(SELECT * FROM (SELECT
    id,
    audit_status,
    supplier_code
FROM
    `pur_supplier_update_log`
ORDER BY
    id desc ) s GROUP BY s.supplier_code) sul','sul.supplier_code=t.supplier_code');

        }
        $query->andFilterWhere(['=','sul.audit_status' , $this->getAttribute('sul.audit_status')]);

        if(!empty($this->first_line)||!empty($this->second_line)||!empty($this->third_line)){
            $query->joinWith('line');
            if(!empty($this->first_line)){
                $query->andFilterWhere(['pur_supplier_product_line.first_product_line'=>$this->first_line]);
            }
            if(!empty($this->second_line)){
                $query->andFilterWhere(['pur_supplier_product_line.second_product_line'=>$this->second_line]);
            }
            if(!empty($this->third_line)){
                $query->andFilterWhere(['pur_supplier_product_line.third_product_line'=>$this->third_line]);
            }
        }
        if(!empty($this->default_buyer)){
            $query->joinWith('buyerList');
            $query->andFilterWhere(['pur_supplier_buyer.buyer'=>$this->default_buyer]);
        }
        if(!empty($this->status)){
            $query->andFilterWhere(['t.status'=>$this->status]);
        }else{

        }
        //供应商搜索
        if(!empty($params['SupplierSearch']['order_type'])){
            $start_time = date('Y-m-d H:i:s');
            $end_time   = date('Y-m-d H:i:s' ,strtotime('-3 month'));
            $supplier_code = PurchaseOrder::find()
                ->select('supplier_code')
                ->where(['between', 'created_at', $end_time, $start_time])
                ->andWhere(['purchase_type'=>3])
                ->groupBy('supplier_code')
                ->asArray()
                ->all();
            if($supplier_code && count($supplier_code)>0){
                $condition = [];
                foreach ($supplier_code as $key => $val){
                    $condition[] = $val['supplier_code'];
                }
                if(count($condition) > 1){
                    $query->andWhere(['in','t.supplier_code',$condition]);
                }
            }
        }
        //供应商等级
        if(!empty($params['SupplierSearch']['supplier_level'])){
            $query->andWhere(['t.supplier_level'=>$params['SupplierSearch']['supplier_level']]);
        }

        $query->andFilterWhere(['like', 't.supplier_name', trim($this->supplier_name)]);


        \Yii::$app->session->set('SupplierSearch', $params);
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }

    /**
     * 跨境宝供应商列表 搜索
     * @param      $params
     * @param bool $noDataProvider
     * @return ActiveDataProvider|\yii\db\ActiveQuery
     */
    public function search2($params, $noDataProvider = false)
    {
        $query = Supplier::find();

        $pageSize   = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
           'query' => $query,
           'pagination' => [
               'pageSize' => $pageSize,
           ],
        ]);

        $query->alias('t');

        if (isset($params['sort'])) {
            $dataProvider->setSort([
               'attributes' => [
                   'supplier_code' => [
                       'desc' => ['supplier_code' => SORT_DESC],
                       'asc' => ['supplier_code' => SORT_ASC],
                       'label' => 'supplier_code'
                   ],
                   'supplier_name' => [
                       'desc' => ['supplier_name' => SORT_DESC],
                       'asc' => ['supplier_name' => SORT_ASC],
                       'label' => 'supplier_name'
                   ],
                   'special_flag_user' => [
                       'desc' => ['special_flag_user' => SORT_DESC],
                       'asc' => ['special_flag_user' => SORT_ASC],
                       'label' => 'special_flag_user'
                   ],
                   'special_flag_time' => [
                       'desc' => ['special_flag_time' => SORT_DESC],
                       'asc' => ['special_flag_time' => SORT_ASC],
                       'label' => 'special_flag_time'
                   ],
               ]
           ]);
        } else {
            $query->orderBy('t.id desc');
        }

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andWhere(['t.supplier_special_flag' => 1]);
        $query->andFilterWhere(['like','t.supplier_name',trim($this->supplier_name)]);
        $query->andFilterWhere(['t.supplier_code' => trim($this->supplier_code)]);

        \Yii::$app->session->set('SupplierSpecialSearch', $params);
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }

}
