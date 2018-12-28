<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseGradeAudit;

/**
 * PurchaseGradeAuditSearch represents the model behind the search form about `app\models\PurchaseGradeAudit`.
 */
class PurchaseGradeAuditSearch extends PurchaseGradeAudit
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'audit_user', 'type'], 'integer'],
            [['audit_price'], 'number'],
            [['create_user', 'create_time'], 'safe'],
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
        $query = PurchaseGradeAudit::find();

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
            'audit_user' => $this->audit_user,
            'type' => $this->type,
            'audit_price' => $this->audit_price,
            'create_time' => $this->create_time,
        ]);

        $query->andFilterWhere(['like', 'create_user', $this->create_user]);

        return $dataProvider;
    }
}
