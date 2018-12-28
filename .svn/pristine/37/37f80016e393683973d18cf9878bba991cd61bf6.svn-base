<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\CostTypes;

/**
 * CostTypesSearch represents the model behind the search form about `app\models\CostTypes`.
 */
class CostTypesSearch extends CostTypes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'create_id', 'update_id'], 'integer'],
            [['cost_code', 'cost_en', 'cost_cn', 'notice', 'create_time', 'update_time'], 'safe'],
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
        $query = CostTypes::find();

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
            'create_id' => $this->create_id,
            'update_id' => $this->update_id,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ]);

        $query->andFilterWhere(['like', 'cost_code', trim($this->cost_code)])
            ->andFilterWhere(['like', 'cost_en', trim($this->cost_en)])
            ->andFilterWhere(['like', 'cost_cn', trim($this->cost_cn)])
            ->andFilterWhere(['like', 'notice', $this->notice]);

        return $dataProvider;
    }
}
