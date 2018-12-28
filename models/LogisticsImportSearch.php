<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\LogisticsImport;

/**
 * LogisticsImportSearch represents the model behind the search form about `app\models\LogisticsImport`.
 */
class LogisticsImportSearch extends LogisticsImport
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [['logistics_num', 'purchase_order_num' , 'create_name', 'create_time',  'push_status', 'is_deleted'], 'safe'],
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
        $query = LogisticsImport::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
            'defaultOrder' => [
                'create_time' => SORT_DESC,//按创建时间排序 desc
                ] 
            ]
        ]);
  
/*        $dataProvider->setSort([
               'attributes' => [
                   'create_time' => [
                       'desc' => ['create_time' => SORT_DESC],
                       'label' => 'create_time'
                   ],
               ]
           ]); */


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([ 
            'id' => $this->id,
            'create_time' => $this->create_time,
            'create_id' => $this->create_id,
            'create_name' => $this->create_name,
            'push_status' => $this->push_status,
            'is_deleted' => 1,
        ]);

        $query->andFilterWhere(['like', 'logistics_num', trim($this->logistics_num)])
            ->andFilterWhere(['like', 'push_status', trim($this->push_status)]);

        return $dataProvider;
    }
}
