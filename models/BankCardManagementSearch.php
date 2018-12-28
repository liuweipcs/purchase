<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\BankCardManagement;

/**
 * BankCardManagementSearch represents the model behind the search form about `app\models\BankCardManagement`.
 */
class BankCardManagementSearch extends BankCardManagement
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'head_office', 'payment_types', 'account_sign', 'status', 'application_business', 'create_id', 'update_id'], 'integer'],
            [['branch', 'account_holder', 'account_number', 'account_abbreviation', 'payment_password', 'remarks', 'create_time', 'update_time','k3_bank_account'], 'safe'],
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
        $query = BankCardManagement::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->orderBy('id DESC');
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'head_office' => $this->head_office,
            'payment_types' => $this->payment_types,
            'account_sign' => $this->account_sign,
            'status' => $this->status,
            'application_business' => $this->application_business,
            'k3_bank_account' => $this->k3_bank_account,
        ]);

        $query->andFilterWhere(['like', 'branch', trim($this->branch)])
            ->andFilterWhere(['like', 'account_holder', trim($this->account_holder)])
            ->andFilterWhere(['like', 'account_number', $this->account_number])
            ->andFilterWhere(['like', 'account_abbreviation', trim($this->account_abbreviation)])
            ->andFilterWhere(['like', 'payment_password', $this->payment_password])
            ->andFilterWhere(['like', 'remarks', $this->remarks]);

        return $dataProvider;
    }
}
