<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 * PurchaseOrderSearch represents the model behind the search form about `app\models\PurchaseOrder`.
 */
class SampleInspectSearch extends SampleInspect
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku','apply_user','qc_result'], 'safe'],
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
     * 申请列表查询
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,//要多少写多少吧
            ],
        ]);
        $query->alias('t');
        $query->orderBy('t.id DESC');
        $query->joinWith('apply');
        //$query->andFilterWhere(['status'=>1]);
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        // grid filtering conditions
        if(!empty($this->apply_user)){
            $query->andFilterWhere(['pur_supplier_update_apply.create_user_name'=>$this->apply_user]);
        }
        $query->andFilterWhere(['like', 't.sku', trim($this->sku)]);
        if(!empty($this->qc_result) && $this->qc_result != 'all'){
            $query->andFilterWhere(['t.qc_result'=>$this->qc_result]);
        }
        //$query->
        return $dataProvider;
    }
}
