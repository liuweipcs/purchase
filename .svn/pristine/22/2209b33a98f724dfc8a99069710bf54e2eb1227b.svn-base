<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OverseasWarehouseSkuPaidWaitSearch 海外仓-SKU已付款未到货列表
 * user:zwl
 * date:2018-09-04
 */
class OverseasWarehouseSkuPaidWaitSearch extends Supplier
{
    public $id;
    public $supplier_code;
    public $start_time;
    public $end_time;
    public $supplier_name;
    public $totalCount;
    public $totalAmount;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'],'safe'],
            [['supplier_code'],'safe'],
            [['supplier_name'],'safe'],
            [['totalCount'],'safe'],
            [['totalAmount'],'safe'],
        ];
    }
    public function attributes()
    {
        // 添加关联字段到可搜索属性集合
        return [
//            'supplier_name'
        ];
    }

    public function safeAttributes()
    {
        // 添加关联字段到可搜索属性集合
        return ['supplier_name'];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                         => Yii::t('app', 'ID'),
            'supplier_code'              => Yii::t('app', '供应商代码'),
            'supplier_name'              => Yii::t('app', '供应商中文名'),
            'totalCount'                 => Yii::t('app', '未到货总数量'),
            'totalAmount'                => Yii::t('app', '未到货总金额'),
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
        $query = self::find();

        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
            'sort' => [
                'defaultOrder'  => ['totalAmount' => SORT_DESC],
                'attributes'    => [
                    'id'            => ['asc' => ['t.id' => SORT_ASC],'desc' => ['t.id' => SORT_DESC]],
                    'supplier_code' => ['asc' => ['t.supplier_code' => SORT_ASC],'desc' => ['t.supplier_code' => SORT_DESC]],
                    'supplier_name' => ['asc' => ['t.supplier_name' => SORT_ASC],'desc' => ['t.supplier_name' => SORT_DESC]],
                    'totalCount'    => ['asc' => ['totalCount' => SORT_ASC],'desc' => ['totalCount' => SORT_DESC]],
                    'totalAmount'   => ['asc' => ['totalAmount' => SORT_ASC],'desc' => ['totalAmount' => SORT_DESC]],
                ]
            ],
        ]);

        $query->select = array(
            0 => 't.id',
            1 => 't.supplier_code',
            2 => 't.supplier_name',
            3 => 'SUM(b.ctq) - SUM(IFNULL(b.rqy,0)) AS totalCount',
            4 => 'SUM(b.price*(b.ctq - IFNULL(b.rqy,0))) AS  totalAmount'
        );

        $query->alias('t');

        $query->innerJoin("pur_purchase_order a","a.supplier_code=t.supplier_code");
        $query->innerJoin("pur_purchase_order_items b","a.pur_number=b.pur_number");

        $query->groupBy("t.supplier_code");
        //$query->orderBy('totalAmount desc');

        // 加载查询条件
        $this->load($params);

        $query->andFilterWhere(['=', 'a.pay_status', 5]);// 已付款状态的的订单
        $query->andFilterWhere(['NOT IN','a.purchas_status',[4,6,9,10]]);// 撤销\全到货\部分到货不等待剩余\作废
        $query->andFilterWhere(['!=', 'IFNULL(a.refund_status,0)', 2]);// 退款状态（0默认1未退款2已退款）
        $query->andFilterWhere(['>', 'b.ctq-IFNULL(b.rqy,0)', 0]);// 已付款状态的的订单

        if($this->supplier_name){
            $query->andFilterWhere(['like', 't.supplier_name', trim($this->supplier_name)]);
        }
        if(isset($params['supplier_ids']) AND $params['supplier_ids']){// 根据供应商ID查询
            $supplier_ids = $params['supplier_ids'];
            if($supplier_ids){
                $query->andFilterWhere(['in', 't.id', explode(',',$supplier_ids)]);
            }
            unset($params['supplier_ids']);
        }

        \Yii::$app->session->set('OverseasWarehouseSkuPaidWaitSearch', $params);// 把查询条件的参数缓存起来
        if ($noDataProvider)
            return $query;

        return $dataProvider;
    }
}
