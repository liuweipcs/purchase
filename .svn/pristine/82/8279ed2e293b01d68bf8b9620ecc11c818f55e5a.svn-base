<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * WarehouseSearch represents the model behind the search form about `app\models\Warehouse`.
 */
class LockWarehouseConfigSearch extends LockWarehouseConfig
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'is_lock'], 'integer'],
            [['id', 'sku', 'warehouse_code', 'create_user', 'create_time', 'is_lock'], 'safe'],
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
        $query = LockWarehouseConfig::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if (!empty($this->sku)) {
            $query->where(['like', 'sku', trim($this->sku)]);
        }

        $query->orderBy('id desc');

        return $dataProvider;
    }
}
