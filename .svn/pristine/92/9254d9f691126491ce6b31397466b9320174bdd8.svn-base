<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Warehouse;

/**
 * WarehouseSearch represents the model behind the search form about `app\models\Warehouse`.
 */
class WarehouseSearch extends Warehouse
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'warehouse_type', 'is_custody', 'use_status', 'create_user_id', 'modify_user_id'], 'integer'],
            [['warehouse_name', 'warehouse_code', 'country', 'state', 'city', 'address', 'telephone', 'fax', 'zip_code', 'remark', 'create_time', 'modify_time', 'pattern'], 'safe'],
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
        $query = Warehouse::find();

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
            'warehouse_type' => $this->warehouse_type,
            'is_custody' => $this->is_custody,
            'use_status' => $this->use_status,
            'create_user_id' => $this->create_user_id,
            'modify_user_id' => $this->modify_user_id,
            'create_time' => $this->create_time,
            'modify_time' => $this->modify_time,
        ]);

        $query->andFilterWhere(['like', 'warehouse_name', trim($this->warehouse_name)])
            ->andFilterWhere(['like', 'warehouse_code', trim($this->warehouse_code)])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'state', $this->state])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'telephone', $this->telephone])
            ->andFilterWhere(['like', 'fax', $this->fax])
            ->andFilterWhere(['like', 'zip_code', $this->zip_code])
            ->andFilterWhere(['like', 'remark', $this->remark])
            ->andFilterWhere(['like', 'pattern', $this->pattern]);

        return $dataProvider;
    }
}
