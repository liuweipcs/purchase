<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\SupplierQuotes;

/**
 * WlClientRecieveInvoicesSearch represents the model behind the search form about `app\models\WlClientRecieveInvoices`.
 */
class SupplierQuotesSearch extends SupplierQuotes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [[,'product_sku','product_number','supplierprice'],'safe']
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
        $query = SupplierQuotes::find();
    
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                //'pageSize' => 20,//要多少写多少吧
            ],
        ]);
        $query->orderBy('id desc');
        $query->andFilterWhere(['status'=>1]);
        //$query->groupBy('suppliercode');
        //$query->joinWith('items as qoutes');
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'product_sku' => $this->sku,
//            'supplier_settlement' => $this->supplier_settlement,
//            'supplier_code'    =>$this->supplier_code,
//            'buyer'             =>$this->buyer,
//            'merchandiser'    =>$this->merchandiser,
//            'main_category'    =>$this->main_category,
        ]);

//        $query->andFilterWhere(['like', 'supplier_name', $this->supplier_name]);



        return $dataProvider;
    }
}
