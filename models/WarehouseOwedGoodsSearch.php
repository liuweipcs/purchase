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
class WarehouseOwedGoodsSearch extends WarehouseOwedGoods
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['sku'], 'safe'],
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
     * 仓库欠货
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $fields                     = ['*','sum(quantity_goods) as total_quantity_goods'];
        $query = WarehouseOwedGoods::find()->select($fields);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        //$query->where(['is_purchase'=>0]);
        $query->groupBy(['sku','platform_code']);
        //$query->orderBy('quantity_goods asc');
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'sku'           => trim($this->sku),
            'platform_code'      => $this->platform_code,
            'warehouse_code'             => $this->warehouse_code,
            'order_pay_time'             => $this->order_pay_time,
        ]);
        //$query->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);


        return $dataProvider;
    }

}
