<?php
namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PurchaseOrderSearch represents the model behind the search form about `app\models\PurchaseOrder`.
 */
class PurchaseOrdersV2Search extends PurchaseOrdersV2
{

    public $start_time;
    public $end_time;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','review_status','all_status'], 'integer'],
            [['pur_number', 'warehouse_code', 'supplier_code', 'pur_type', 'shipping_method', 'operation_type', 'creator', 'account_type', 'pay_type', 'currency_code','items.sku','buyer','purchas_status','refund_status','create_type','audit_return','created_at','singletype','reference','start_time','end_time','pur_type','is_arrival','complete_type','qc_abnormal_status','receiving_exception_status','code','ss.supplier_type','sku_type','merchandiser','buyer','supplier_name','pay_status','ship.express_no'], 'safe'],

        ];
    }

    public function attributes()
    {
        // 添加关联字段到可搜索属性集合
        return array_merge(parent::attributes(), ['items.sku','ss.supplier_type','ship.express_no']);
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
        $query = PurchaseOrdersV2::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->where(['!=','purchas_status','4']);
        $query->andFilterWhere(['not in','purchase_type',['3']]);
        $query->orderBy('id desc');

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

        $user = ['左良良','张涛涛','admin'];

        if(empty($grade)){
            $query->andFilterWhere(['=', 'buyer', Yii::$app->user->identity->username]);
        }

        if(!in_array(Yii::$app->user->identity->username,$user) && !empty($grade) && empty($grade->grade)){

            $query->andFilterWhere(['=', 'buyer', Yii::$app->user->identity->username]);

            //if(!empty($grade) && $grade->grade){//审核权限控制
               // $range=GroupAuditConfig::findOne(['group'=>$grade->grade]);
                /*$manager_range=GroupAuditConfig::findOne(['group'=>3]);
                if(!empty($manager_range) && $manager_range->values && $grade->grade==3){
                    $query->andFilterWhere(['>', 'total_price', $manager_range->values]);
                }*/

                /*if($grade->grade==1){
                    $query->andFilterWhere(['<=', 'all_status', '2']);
                }

                if($grade->grade==2){
                    $query->andFilterWhere(['=', 'all_status', '3']);
                }

                if($grade->grade==3){
                    $query->andFilterWhere(['=', 'all_status', '4']);
                }*/

                /*if($grade->grade==1){
                    if(!empty($grade->group_id)){
                        $pus=PurchaseUser::findAll(['group_id'=>$grade->group_id]);
                        if(!empty($pus)){
                            foreach($pus as $k=>$v){
                                $dwhere[]=$v['pur_user_name'];
                            }
                        }
                    }
                }

                if(!empty($dwhere)){
                    $query->andOnCondition(['buyer'=>$dwhere]);
                }

            }else{
                $query->andFilterWhere(['=', 'buyer', Yii::$app->user->identity->username]);
            }*/
        }

        //单号
        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', $this->pur_number]);

        if ($this->getAttribute('items.sku'))
        {
            $query->joinWith('purchaseOrderItems AS items');
        }


        // grid filtering conditions
        $query->andFilterWhere([
            'buyer'            => $this->buyer,
            'purchas_status'   => $this->purchas_status,
            'pay_status'   => $this->pay_status,
            'create_type'      => $this->create_type,
            'audit_return'     => $this->audit_return,
            'warehouse_code'   => $this->warehouse_code,
            'shipping_method'  => $this->shipping_method,
            'review_status'  => $this->review_status,
            'all_status'  => $this->all_status,
           // 'items.sku' => $this->getAttribute('items.sku'),
        ]);

        $query->andFilterWhere(['like', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'items.sku', $this->getAttribute('items.sku')])
            ->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);

        return $dataProvider;
    }

    /**
     * 采购审核搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
        $query = PurchaseOrdersV2::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->where(['=', 'audit_return', '2']);
        $query->andFilterWhere(['!=','purchase_type','3']);
        $query->andFilterWhere(['in','purchas_status',['2']]);

        //$query->andFilterWhere(['=','purchas_status','2']);

        /*$grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

        if(!empty($grade->grade)){
            if($grade->grade==1){
                $query->andFilterWhere(['in','purchas_status',['2']]);
                $query->andFilterWhere(['=','review_status','0']);
            }

            if($grade->grade==2){
                $query->andFilterWhere(['in','purchas_status',['3']]);
                $query->andFilterWhere(['=','review_status','1']);
            }

            if($grade->grade==3){
                $query->andFilterWhere(['in','purchas_status',['3']]);
                $query->andFilterWhere(['=','review_status','2']);
            }
        }else{
            $query->andFilterWhere(['=','purchas_status','2']);
            //$query->andFilterWhere(['in','review_status',['1','2']]);
        }*/



        $query->orderBy('id desc');
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if ($params){
            $query->joinWith('purchaseOrderItems AS items');
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'buyer' => $this->buyer,
            //'items.sku' => $this->getAttribute('items.sku'),
        ]);
        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', $this->pur_number])
            ->andFilterWhere(['like', 'items.sku', $this->getAttribute('items.sku')])
            ->andFilterWhere(['=', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['=', 'pur_type', $this->pur_type])
            ->andFilterWhere(['like', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);

        return $dataProvider;
    }

    /**
     * 采购复审搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search2($params)
    {
        $query = PurchaseOrdersV2::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->where(['in','purchas_status',['2']]);
        $query->andFilterWhere(['in','audit_return',['3']]);
        $query->andFilterWhere(['not in','purchase_type',['3']]);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if ($params)
        {
            $query->joinWith('purchaseOrderItems AS items');
        }
        // grid filtering conditions


        //$query->andFilterWhere(['=','items.sku' , $this->getAttribute('items.sku')]);

        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', $this->pur_number])
            ->andFilterWhere(['like', 'items.sku', $this->getAttribute('items.sku')]);


            //->andFilterWhere(['=', 'items.supplier_code', $this->supplier_code]);




        return $dataProvider;
    }
    /**
     * 采购单搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search3($params)
    {
        $query = PurchaseOrdersV2::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
             'pageSize' => 20,
        ],
        ]);
        $query->where(['in','purchas_status',['3','5','6','7','8','9']]);
        $query->andFilterWhere(['in','pur_purchase_order.purchase_type',['1','2']]);
/*        $user = ['张涛涛'];
        if(in_array(Yii::$app->user->identity->username,$user))
        {

        } else {
            $query->andFilterWhere(['=', 'buyer', Yii::$app->user->identity->username]);
        }*/
        $this->load($params);
        $query->orderBy('submit_time desc');
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku'))
        {
            $query->innerJoinWith('purchaseOrderItems AS items');
        }

        if($this->getAttribute('ship.express_no'))
        {
            $query->innerJoinWith('orderShip AS ship');
        }
        if ($this->purchas_status==99)
        {
            $query->andwhere(['in','purchas_status',['7','8']]);

        }
        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', $this->pur_number]);


        //$query->andFilterWhere(['=','items.sku' , $this->getAttribute('items.sku')]);

        // grid filtering conditions
        $query->andFilterWhere([
            'pur_purchase_order.buyer' => $this->buyer,
            'is_arrival' => $this->is_arrival,
            'receiving_exception_status' => $this->receiving_exception_status,
            'qc_abnormal_status' => $this->qc_abnormal_status,
            'audit_return' => $this->audit_return,
            'pay_status' => $this->pay_status,
            'create_type' => $this->create_type,
            'account_type' => $this->account_type,
            'shipping_method' => $this->shipping_method,
            'supplier_code' => $this->supplier_code,
            'pur_purchase_order.merchandiser' => $this->merchandiser,
            'refund_status'   => $this->refund_status,
            //'items.sku' => $this->getAttribute('items.sku'),
            'ship.express_no' => $this->getAttribute('ship.express_no'),


        ]);

        $query->andFilterWhere(['=', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['like', 'items.sku', $this->getAttribute('items.sku')])
            ->andFilterWhere(['=', 'purchas_status', $this->purchas_status])
              ->andFilterWhere(['like', 'pur_purchase_order.supplier_name', $this->supplier_name])
              ->andFilterWhere(['=', 'pur_type', $this->pur_type]);

        $query->andFilterWhere(['between', 'audit_time', $this->start_time, $this->end_time]);
        return $dataProvider;
    }
    /**
     * 采购单异常搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search4($params)
    {
        $query = PurchaseOrdersV2::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        $query->where(['in','refund_status',['4']]);
        //$query->andFilterWhere(['=','audit_return',['2']]);
        $this->load($params);
        $query->orderBy('created_at desc');
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku'))
        {
            $query->innerJoinWith('purchaseOrderItems AS items');
            //$query->joinWith('supplier AS ss');

        }

        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', $this->pur_number])
            ->andFilterWhere(['like', 'items.sku', $this->getAttribute('items.sku')]);



        //$query->andFilterWhere(['=','items.sku' , $this->getAttribute('items.sku')]);

        // grid filtering conditions
        $query->andFilterWhere([
            'pur_purchase_order.buyer' => $this->buyer,

        ]);


        return $dataProvider;
    }
    /**
     * 采购单fba搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search5($params)
    {
        $query = PurchaseOrdersV2::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        $query->where(['not in','purchas_status',['4','10']]);
        $query->andFilterWhere(['in','purchase_type',['3']]);
        $this->load($params);
        $query->orderBy('id desc');
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku'))
        {
            $query->innerJoinWith('purchaseOrderItems AS items');
            //$query->joinWith('supplier AS ss');

        }
        if ($this->purchas_status==99)
        {
            $query->andwhere(['in','purchas_status',['7','8']]);

        }
        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', $this->pur_number]);


        //$query->andFilterWhere(['=','items.sku' , $this->getAttribute('items.sku')]);

        // grid filtering conditions
        $query->andFilterWhere([
            'pur_purchase_order.buyer' => $this->buyer,
            'is_arrival' => $this->is_arrival,
            'receiving_exception_status' => $this->receiving_exception_status,
            'qc_abnormal_status' => $this->qc_abnormal_status,
            'audit_return' => $this->audit_return,
            'pay_status' => $this->pay_status,
            'create_type' => $this->create_type,
            'account_type' => $this->account_type,
            'shipping_method' => $this->shipping_method,
            'pur_purchase_order.merchandiser' => $this->merchandiser,
            'refund_status'   => $this->refund_status,
            'items.sku' => $this->getAttribute('items.sku'),


        ]);

        $query->andFilterWhere(['=', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['=', 'purchas_status', $this->purchas_status])
            ->andFilterWhere(['like', 'pur_purchase_order.supplier_name', $this->supplier_name])
            ->andFilterWhere(['=', 'pur_type', $this->pur_type]);

        $query->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);
        return $dataProvider;
    }
}
