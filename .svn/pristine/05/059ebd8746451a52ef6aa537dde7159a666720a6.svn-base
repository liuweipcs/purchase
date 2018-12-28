<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\GroupAuditConfig;

/**
 * GroupAuditConfigSearch represents the model behind the search form about `app\models\GroupAuditConfig`.
 */
class GroupAuditConfigSearch extends GroupAuditConfig
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'group',  /*'uid','cdate'*/], 'integer'],
            [['values', /*'remark'*/], 'safe'],
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
        $query = GroupAuditConfig::find();

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
            'group' => $this->group,
            'uid' => $this->uid,
            'cdate' => $this->cdate,
        ]);

        $query->andFilterWhere(['like', 'values', $this->values])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }
}
