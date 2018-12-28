<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseSuggestQuantity;

class PurchaseSuggestQuantitySearch extends PurchaseSuggestQuantity
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'purchase_quantity','suggest_status','suggest_status'], 'integer'],
            [['sku', 'purchase_warehouse', 'create_id', 'platform_number', 'sales_note','create_time','start_time','end_time'], 'safe'],
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
    public function search($params, $noDataProvider = false)
    {
        $query = PurchaseSuggestQuantity::find();
//        $create_id = Yii::$app->user->identity->username;
//        $query->where(['=','create_id',$create_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        //默认id排序
        if(!empty($params['sort']))
        {
            //$query->orderBy('supplier_code desc');
        } else{
            $query->orderBy('id asc');
        }
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        /*//采购仓
        if (!empty($this->purchase_warehouse)) {
            $purchase_warehouse = Warehouse::find()
                ->select('warehouse_code')
                ->where(['use_status'=>1])
                ->andWhere(['like','warehouse_name',trim($this->purchase_warehouse)])
                ->asArray()
                ->one()['warehouse_code'];
            if (empty($purchase_warehouse)) {
                $purchase_warehouse = '仓库不存在';
            }
        } else {
            $purchase_warehouse = '';
        }*/
        $query->andFilterWhere([
//            'id' => $this->id,
            'purchase_warehouse' => $this->purchase_warehouse, //采购仓
            'suggest_status' => $this->suggest_status, //状态
            'create_id' => $this->create_id, //创建人
        ]);
        $query->andFilterWhere(['like', 'platform_number', trim($this->platform_number)]) //平台号
//            ->andFilterWhere(['like', 'sales_note', trim($this->sales_note)]) //备注
            ->andFilterWhere(['like', 'sku', trim($this->sku)]);

        if (!empty($this->start_time)) {
            $query->andFilterWhere(['between', 'create_time', $this->start_time, $this->end_time]);
        } else {
            $query->andFilterWhere(['between', 'create_time', date('Y-m-d 00:00:00',time()), date('Y-m-d 23:59:59',time())]);
        }
        \Yii::$app->session->set('PurchaseSuggestQuantitySearchData', $params);
        if ($noDataProvider){
            return $query;
        }
//        Vhelper::dump($query->createCommand()->getRawSql(),$params);
        return $dataProvider;
    }
}
