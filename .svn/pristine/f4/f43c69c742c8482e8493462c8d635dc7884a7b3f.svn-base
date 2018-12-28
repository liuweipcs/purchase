<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\OverseasWarehouseGoodsTracking;

/**
 * OverseasWarehouseGoodsTrackingSearch represents the model behind the search form about `app\models\OverseasWarehouseGoodsTracking`.
 */
class OverseasWarehouseGoodsTrackingSearch extends OverseasWarehouseGoodsTracking
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'state', 'countdown_days', 'financial_payment_time', 'product_arrival_time'], 'integer'],
            [['owarehouse_name', 'sku', 'purchase_order_no', 'buyer'], 'safe'],
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
        $query = OverseasWarehouseGoodsTracking::find();

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
            'state' => $this->state,
            'countdown_days' => $this->countdown_days,
            'financial_payment_time' => $this->financial_payment_time,
            'product_arrival_time' => $this->product_arrival_time,
        ]);

        $query->andFilterWhere(['like', 'owarehouse_name', trim($this->owarehouse_name)])
            ->andFilterWhere(['like', 'sku', trim($this->sku)])
            ->andFilterWhere(['like', 'purchase_order_no', trim($this->purchase_order_no)])
            ->andFilterWhere(['like', 'buyer', trim($this->buyer)]);

        return $dataProvider;
    }
}
