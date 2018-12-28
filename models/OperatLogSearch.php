<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\OperatLog;

/**
 * OperatLogSearch represents the model behind the search form about `app\models\OperatLog`.
 */
class OperatLogSearch extends OperatLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'uid', 'pid'], 'integer'],
            [['username', 'content', /*'create_date',*/ 'ip', 'module', 'status', 'pur_number'], 'safe'],
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

    public function formName()
    {
        return '';
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
        $query = OperatLog::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        $query->orderBy('id desc');
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'create_date' => $this->create_date,
            'uid' => $this->uid,
            'pid' => $this->pid,
        ]);

        $query->andFilterWhere(['like', 'username', trim($this->username)])
            ->andFilterWhere(['like', 'content', trim($this->content)])
            ->andFilterWhere(['like', 'ip', trim($this->ip)])
            ->andFilterWhere(['like', 'module', trim($this->module)]);

        return $dataProvider;
    }


    public function search1($params)
    {
        $query = OperatLog::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->attributes = $params;

        $query->orderBy('id desc');

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'create_date' => $this->create_date,
            'uid' => $this->uid,
            'pid' => $this->pid,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'username', trim($this->username)]);
        $query->andFilterWhere(['like', 'pur_number', trim($this->pur_number)]);

        return $dataProvider;
    }
}
