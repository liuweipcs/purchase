<?php
namespace app\models;

use app\config\Vhelper;
use app\models\base\BaseModel;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseWarehouseAbnormal;
class PurchaseWarehouseAbnormalSearch extends PurchaseWarehouseAbnormal
{

    public $buyer;

    public function scenarios()
    {
        return Model::scenarios();
    }

    // 查找入库单
    public function search($params, $type = null)
    {
        $query = PurchaseWarehouseAbnormal::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if(!$this->validate()) {
            return $dataProvider;
        }

        if(!is_null($type)) {
            $query->andFilterWhere(['abnormal_type' => $type]);
        }

        $query->andFilterWhere([
            'defective_id' => trim($this->defective_id),
            'express_code' => trim($this->express_code),
            'buyer'        => Vhelper::chunkBuyerByNumeric($this->buyer),
            'handler_type' => $this->handler_type,
            'is_handler'   => $this->is_handler,
        ]);

        $query->andFilterWhere(['like', 'purchase_order_no', trim($this->purchase_order_no)]);

        $query->andFilterWhere(['like', 'sku', trim($this->sku)]);

        if($this->pull_time) {
            $times = explode(' - ', $this->pull_time);
            $query->andFilterWhere(['>', 'pull_time', $times[0]]);
            $query->andFilterWhere(['<', 'pull_time', $times[1]]);
        }

        $query->orderBy('pull_time desc');

        return $dataProvider;
    }


    public static function checkIsExp($pur_number){
        //入库单存在异常
        $ruku = PurchaseWarehouseAbnormal::find()
            ->where(['abnormal_type'=>1])
            ->andWhere(['purchase_order_no'=>trim($pur_number)])
            ->one();

        if($ruku && ($ruku->is_handler == 0 || $ruku->is_push_to_warehouse == 0)){
            return  1;
        }
        //次品存在异常
        $cipin = PurchaseWarehouseAbnormal::find()
            ->where(['abnormal_type' => 2])
            ->andWhere(['purchase_order_no' => trim($pur_number)])
            ->one();
        if($cipin && ($cipin->is_handler == 0 || $cipin->is_push_to_warehouse == 0 || $cipin->handler_type == 15)){
            return 2;
        }
        //次品存在异常
        $zhijian = PurchaseWarehouseAbnormal::find()
            ->where(['abnormal_type' => 3])
            ->andWhere(['purchase_order_no' => trim($pur_number)])
            ->one();
        if($zhijian && ($zhijian->is_handler == 0 || $zhijian->is_push_to_warehouse == 0 || $zhijian->handler_type == 15)){
            return 3;
        }


        return '';
    }
}
