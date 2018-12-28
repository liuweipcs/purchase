<?php
namespace app\modules\manage\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Created by PhpStorm.
 * User: Wr
 * Date: 2018/6/13
 * Time: 15:56
 */
class SupplierManageConfigSearch extends SupplierManageConfig {
    public function rules()
    {
        return[

        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
    public function search($params,$dataCondition=false){
        $query = self::find();
        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        // $query->where(['sku'=>'JM00042']);
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if($dataCondition){
            return $query;
        }
        return $dataProvider;
    }
}