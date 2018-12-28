<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Product;

/**
 * ProductSearch represents the model behind the search form about `app\models\Product`.
 */
class ProductSearch extends Product
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'product_category_id', 'product_status','supply_status'], 'integer'],
            [['sku', /*'uploadimgs', 'product_cn_link', 'product_en_link', 'create_id',*/ 'create_time'], 'safe'],
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
        $query = Product::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                    'pageSize' => 20,
            ],
        ]);
        $query->orderBy('id desc');
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'product_category_id' => $this->product_category_id,
            'product_status' => $this->product_status,
            'supply_status' => $this->supply_status,
            'create_time' => $this->create_time,
            'product_cost' => $this->product_cost,
        ]);

        $query->andFilterWhere(['=', 'sku', trim($this->sku)])
            ->andFilterWhere(['like', 'uploadimgs', $this->uploadimgs])
            ->andFilterWhere(['like', 'product_cn_link', $this->product_cn_link])
            ->andFilterWhere(['like', 'product_en_link', $this->product_en_link])
            ->andFilterWhere(['like', 'create_id', $this->create_id]);

        return $dataProvider;
    }
}
