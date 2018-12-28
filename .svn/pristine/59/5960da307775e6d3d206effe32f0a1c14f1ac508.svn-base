<?php

namespace app\models;

use app\models\base\BaseModel;

use app\services\BaseServices;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\SupervisorGroupBind;

/**
 * SupervisorGroupBindSearch represents the model behind the search form about `app\models\SupervisorGroupBind`.
 */
class SupervisorGroupBindSearch extends SupervisorGroupBind
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'supervisor_id', 'creator_id'], 'integer'],
            [['supervisor_name','group_id'], 'safe'],
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
        $query = SupervisorGroupBind::find();
        $query->orderBy('group_id');

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
        if(empty($this->group_id)){
            $group_id = BaseServices::getGroupByUserName(2);
            $query->andFilterWhere(['in','group_id',$group_id]);
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'supervisor_id' => $this->supervisor_id,
            'group_id' => $this->group_id,
            'creator_id' => $this->creator_id,
            'create_time' => $this->create_time,
            'edit_time' => $this->edit_time,
        ]);

        $query->andFilterWhere(['like', 'supervisor_name', trim($this->supervisor_name)])
            ->andFilterWhere(['like', 'creator_name', $this->creator_name])
            ->andFilterWhere(['like', 'editor_name', $this->editor_name]);

        return $dataProvider;
    }
}
