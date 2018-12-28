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
class TongToolPurchaseSearch extends PurchaseHistory
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['pur_number', 'warehouse_code', 'supplier_code', 'pur_type', 'shipping_method', 'operation_type', 'creator', 'account_type', 'pay_type', 'currency_code', 'sku', 'buyer', 'purchas_status', 'create_type', 'audit_return', 'created_at', 'singletype', 'reference', 'start_time', 'end_time', 'pur_type', 'is_arrival', 'complete_type', 'qc_abnormal_status', 'receiving_exception_status', 'code', 'ss.supplier_type', 'sku_type', 'merchandiser',], 'safe'],
            [['pay_ship_amount'], 'number'],
        ];
    }

    public function attributes()
    {
        // 添加关联字段到可搜索属性集合
        return array_merge(parent::attributes(), ['items.sku', 'ss.supplier_type']);
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
     * 采购确认
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = PurchaseHistory::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        //$query->where(['in', 'purchas_status', ['1', '2']]);
        $query->orderBy('purchase_time desc');
        $this->load($params);
        // grid filtering conditions
        $query->andFilterWhere([
            'buyer'           => trim($this->buyer),
            'pur_number'           => trim($this->pur_number),
            'sku'             => trim($this->sku),
        ]);
        //$query->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);


        return $dataProvider;
    }

}
