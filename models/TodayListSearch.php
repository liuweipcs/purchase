<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PurchaseOrderSearch represents the model behind the search form about `app\models\PurchaseOrder`.
 */
class TodayListSearch extends TodayList
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['developer_id'], 'safe'],
        ];
    }

    public function attributes()
    {
        // 添加关联字段到可搜索属性集合
        return array_merge(parent::attributes(), []);
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
     * 采购确认
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = $this->find();

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
            'developer_id' => $this->developer_id,
            'buyer_id' => $this->buyer_id
        ]);
        return $dataProvider;
    }
}
