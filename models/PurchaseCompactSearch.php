<?php
namespace app\models;

use app\models\base\BaseModel;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\services\BaseServices;

class PurchaseCompactSearch extends PurchaseCompact
{
    public $start_time;
    public $end_time;
    public function search($params, $source = null)
    {
        $query = PurchaseCompact::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if($source) {
            $query->andWhere(['source' => $source]);
        }
        
        $query->orderBy('id desc');
        $this->load($params);

        $query->andFilterWhere([
            'compact_number' => $this->compact_number,
            'create_person_name' => $this->create_person_name,
            'supplier_code' => $this->supplier_code
        ]);


        if($this->create_time) {
            $times = explode(' - ', $this->create_time);
            $query->andFilterWhere(['>', 'create_time', $times[0]]);
            $query->andFilterWhere(['<', 'create_time', $times[1]]);
            $this->start_time = $times[0];
            $this->end_time = $times[1];
        }

        if(!$this->validate()) {
            return $dataProvider;
        }
        return $dataProvider;
    }
}
