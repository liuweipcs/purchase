<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseAbnomal;

/**
 * PurchaseAbnomalSearch represents the model behind the search form about `app\models\PurchaseAbnomal`.
 */
class PurchaseAbnomalSearch extends PurchaseAbnomal
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'package_qty', 'status', 'is_del'], 'integer'],
            [['express_no', 'send_addr', 'send_name', 'note', 'create_user', 'create_time', 'update_time'], 'safe'],
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
        $query = PurchaseAbnomal::find();

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
        $query->orderBy('create_time desc');
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'package_qty' => $this->package_qty,
            'status' => $this->status,
            'is_del' => $this->is_del,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ]);

        $query->andFilterWhere(['like', 'express_no', trim($this->express_no)])
            ->andFilterWhere(['like', 'send_addr', $this->send_addr])
            ->andFilterWhere(['like', 'send_name', $this->send_name])
            ->andFilterWhere(['like', 'note', $this->note])
            ->andFilterWhere(['like', 'create_user', $this->create_user]);

        return $dataProvider;
    }
}
