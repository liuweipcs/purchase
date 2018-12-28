<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseUser;

/**
 * PurchaseUserSearch represents the model behind the search form about `app\models\PurchaseUser`.
 */
class PurchaseUserSearch extends PurchaseUser
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'group_id', 'grade'], 'integer'],
            [['pur_user_name','type'], 'safe'],
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
        $query = PurchaseUser::find();

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
            'type' => $this->type,
            'pur_user_id' => $this->pur_user_id,
            'group_id' => $this->group_id,
            'grade' => $this->grade,
            'crate_time' => $this->crate_time,
            'edit_time' => $this->edit_time,
            'creator' => $this->creator,
            'editor' => $this->editor,
        ]);

        $query->andFilterWhere(['like', 'pur_user_name', trim($this->pur_user_name)]);

        return $dataProvider;
    }
}
