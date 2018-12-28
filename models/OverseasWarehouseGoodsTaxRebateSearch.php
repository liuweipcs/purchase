<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\OverseasWarehouseGoodsTaxRebate;

/**
 * OverseasWarehouseGoodsTaxRebateSearch represents the model behind the search form about `app\models\OverseasWarehouseGoodsTaxRebate`.
 */
class OverseasWarehouseGoodsTaxRebateSearch extends OverseasWarehouseGoodsTaxRebate
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'state'], 'integer'],
            [['sku', 'country', 'create_user', 'create_time'], 'safe'],
            [['tax_rate'], 'number'],
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
        $query = OverseasWarehouseGoodsTaxRebate::find();

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
            'tax_rate' => trim($this->tax_rate),
            'state' => $this->state,
            //'create_time' => $this->create_time,
        ]);

        $query->andFilterWhere(['like', 'sku', trim($this->sku)])
            ->andFilterWhere(['like', 'country', trim($this->country)])
            ->andFilterWhere(['like', 'create_user', trim($this->create_user)]);

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
