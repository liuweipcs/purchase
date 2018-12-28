<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ExchangeGoods;

/**
 * ExchangeGoodsSearch represents the model behind the search form about `app\models\ExchangeGoods`.
 */
class ExchangeGoodsSearch extends ExchangeGoods
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'qty', 'state'], 'integer'],
            [['pur_number', 'supplier_code', 'supplier_name', 'sku', 'pro_name', 'create_user', 'buyer', 'create_time'
                , 'exchange_number', 'freight', 'express_no', 'cargo_company'], 'safe'],
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

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ExchangeGoods::find();

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
            'qty' => $this->qty,
            //'create_time' => $this->create_time,
            'state' => $this->state,
        ]);

        $query->andFilterWhere(['like', 'pur_number', trim($this->pur_number)])
            ->andFilterWhere(['like', 'supplier_code', $this->supplier_code])
            ->andFilterWhere(['like', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'exchange_number', trim($this->exchange_number)])
            ->andFilterWhere(['like', 'express_no', trim($this->express_no)])
            ->andFilterWhere(['like', 'pro_name', $this->pro_name])
            ->andFilterWhere(['like', 'create_user', $this->create_user])
            ->andFilterWhere(['like', 'buyer', $this->buyer]);

        if(!empty($this->create_time)){
            if(strpos($this->create_time,'/')){
                $times = explode('-',$this->create_time);
                $query->andFilterWhere(['between', 'create_time',strtotime($times[0]),strtotime($times[1])+86400]);
            }else{
                $query->andFilterWhere(['between', 'create_time',strtotime($this->create_time),strtotime($this->create_time)+86400]);
            }
        }

        return $dataProvider;
    }
}
