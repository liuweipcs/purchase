<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\SkuSingleTacticMain;

/**
 * SkuSingleTacticMainSearch represents the model behind the search form about `app\models\SkuSingleTacticMain`.
 */
class SkuSingleTacticMainSearch extends SkuSingleTacticMain
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['sku', 'warehouse', 'date_start', 'date_end', 'user', 'create_date'], 'safe'],
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
     *  国内
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SkuSingleTacticMain::find();

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
            //'date_start' => $this->date_start,
            //'date_end' => $this->date_end,
            'create_date' => $this->create_date,
            'status' => $this->status,
        ]);
        $query->where(['in','warehouse','SZ_AA']);
        $query->andFilterWhere(['like', 'sku', trim($this->sku)])
            ->andFilterWhere(['like', 'warehouse', trim($this->warehouse)])
            ->andFilterWhere(['like', 'user', trim($this->user)]);

        if(!empty($this->date_start)){
            if(strpos($this->date_start,'/')){
                $times = explode('-',$this->date_start);
                $query->andFilterWhere(['between', 'date_start',strtotime($times[0]),strtotime($times[1])+86400]);
            }else{
                $query->andFilterWhere(['between', 'date_start',strtotime($this->date_start),strtotime($this->date_start)+86400]);
            }
        }

        if(!empty($this->date_end)){
            if(strpos($this->date_end,'/')){
                $times = explode('-',$this->date_end);
                $query->andFilterWhere(['between', 'date_end',strtotime($times[0]),strtotime($times[1])+86400]);
            }else{
                $query->andFilterWhere(['between', 'date_end',strtotime($this->date_end),strtotime($this->date_end)+86400]);
            }
        }

        return $dataProvider;
    }

    /**
     * 海外
     * @param $params
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
        $query = SkuSingleTacticMain::find();

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
            //'date_start' => $this->date_start,
            //'date_end' => $this->date_end,
            'create_date' => $this->create_date,
            'status' => $this->status,
        ]);
        $query->where(['not in','warehouse','SZ_AA']);
        $query->andFilterWhere(['like', 'sku', trim($this->sku)])
            ->andFilterWhere(['like', 'warehouse', $this->warehouse])
            ->andFilterWhere(['like', 'user', $this->user]);

        if(!empty($this->date_start)){
            if(strpos($this->date_start,'/')){
                $times = explode('-',$this->date_start);
                $query->andFilterWhere(['between', 'date_start',strtotime($times[0]),strtotime($times[1])+86400]);
            }else{
                $query->andFilterWhere(['between', 'date_start',strtotime($this->date_start),strtotime($this->date_start)+86400]);
            }
        }

        if(!empty($this->date_end)){
            if(strpos($this->date_end,'/')){
                $times = explode('-',$this->date_end);
                $query->andFilterWhere(['between', 'date_end',strtotime($times[0]),strtotime($times[1])+86400]);
            }else{
                $query->andFilterWhere(['between', 'date_end',strtotime($this->date_end),strtotime($this->date_end)+86400]);
            }
        }

        return $dataProvider;
    }
}
