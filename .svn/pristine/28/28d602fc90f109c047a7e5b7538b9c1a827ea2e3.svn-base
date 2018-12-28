<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AlibabaAccount;

/**
 * AlibabaAccountSearch represents the model behind the search form about `app\models\AlibabaAccount`.
 */
class AlibabaAccountSearch extends AlibabaAccount
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'last_update_time', 'modify_user_id', 'expires_in', 'bind_account'], 'integer'],
            [['account', 'access_token', 'refresh_token', 'app_key', 'secret_key', 'redirect_uri', 'code', 'refresh_token_timeout'], 'safe'],
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
        $query = AlibabaAccount::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'status' => $this->status,
            'last_update_time' => $this->last_update_time,
            'modify_user_id' => $this->modify_user_id,
            'expires_in' => $this->expires_in,
            'bind_account' => $this->bind_account,
        ]);

        $query->andFilterWhere(['like', 'account', $this->account])
            ->andFilterWhere(['like', 'access_token', $this->access_token])
            ->andFilterWhere(['like', 'refresh_token', $this->refresh_token])
            ->andFilterWhere(['like', 'app_key', $this->app_key])
            ->andFilterWhere(['like', 'secret_key', $this->secret_key])
            ->andFilterWhere(['like', 'redirect_uri', $this->redirect_uri])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'refresh_token_timeout', $this->refresh_token_timeout]);

        return $dataProvider;
    }
}
