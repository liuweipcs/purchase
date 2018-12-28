<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\User;

/**
 * MemberSearch represents the model behind the search form about `app\models\User`.
 */
class MemberSearch extends User
{


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
        $query = User::find();

        // add conditions that should always apply here

        $this->load($params);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->attributes = $params;

        $query->andFilterWhere([
            'id' => $this->id,
            'role' => $this->role,
            'status' => $this->status,
            'username' => $this->username,
            'user_number' => $this->user_number,
        ]);

        $query->orderBy(['id' => SORT_DESC]);

        return $dataProvider;
    }
}
