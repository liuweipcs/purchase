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
class PurchaseOrderReceiptWaterSearch extends PurchaseOrderReceiptWater
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['supplier_code', 'transaction_number','start_time','end_time','pur_number'], 'safe'],

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
        $query = PurchaseOrderReceiptWater::find();

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

//            'pay_status' => $this->pay_status,
//            'settlement_method' => $this->settlement_method,
//            'pay_price' => $this->pay_price,
//            'applicant' => $this->applicant,
//            'auditor' => $this->auditor,
//            'approver' => $this->approver,
//            'application_time' => $this->application_time,
//            'review_time' => $this->review_time,
//            'processing_time' => $this->processing_time,
        ]);

        $query->andFilterWhere(['like', 'pur_number', trim($this->pur_number)])
              ->andFilterWhere(['like', 'transaction_number', trim($this->transaction_number)])
              ->andFilterWhere(['like', 'supplier_code', $this->supplier_code])
              ->andFilterWhere(['between', 'pay_time', $this->start_time, $this->end_time]);

        return $dataProvider;
    }
}
