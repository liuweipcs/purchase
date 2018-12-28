<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseOrderPay;

/**
 * PurchaseOrderPaySearch represents the model behind the search form about `app\models\PurchaseOrderPay`.
 */
class PurchaseOrderPayWaterSearch extends PurchaseOrderPayWater
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['supplier_code', 'pur_number','billing_object_type','start_time','end_time','transaction_number'], 'safe'],

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
        $query = PurchaseOrderPayWater::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->orderBy('id desc');
        $this->load($params);
        //$query->joinWith('purchaseOrder AS items');
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'billing_object_type' => $this->billing_object_type,
            'supplier_code' => $this->supplier_code,
            'pur_number' => trim($this->pur_number),
            'transaction_number' => trim($this->transaction_number),
        ]);

        $query->andFilterWhere(['between', 'pay_time', $this->start_time, $this->end_time]);

        return $dataProvider;
    }
}
