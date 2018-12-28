<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\LogisticsCarrier;

/**
 * LogisticsCarrierSearch represents the model behind the search form about `app\models\LogisticsCarrier`.
 */
class LogisticsCarrierSearch extends LogisticsCarrier
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [['name', 'carrier_code', 'create_time', 'update_time', 'site_url'], 'safe'],
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
        $query = LogisticsCarrier::find();

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
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
            'create_id' => $this->create_id,
            'update_id' => $this->update_id,
        ]);

        $query->andFilterWhere(['like', 'name', trim($this->name)])
            ->andFilterWhere(['like', 'carrier_code', trim($this->carrier_code)])
            ->andFilterWhere(['like', 'site_url', trim($this->site_url)]);

        return $dataProvider;
    }
}
