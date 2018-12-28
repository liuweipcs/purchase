<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseOrder;
use yii\helpers\ArrayHelper;
use app\api\v1\models\SupplierSkuDetail;
use yii\db\Query;
use app\services\BaseServices;
/**
 * PurchaseOrderSearch represents the model behind the search form about `app\models\PurchaseOrder`.
 */
class PurchaseAmountSearch extends PurchaseOrder
{

    public $start_time;
    public $end_time;
    public $sku;
    public $suppliercode;
    public $supplier_code;
    public $product_category_id;
    public $purchase_type;
    public $price;
    public $qty_13;
    public $days_sales_3;
    public $days_sales_15;
    public $days_sales_30;
    public $days_sales_60;
    public $days_sales_90;
    public $product_line;
    public $purchas_status;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['pur_number','product_line','purchase_type','suppliercode','product_category_id','sku','warehouse_code', 'supplier_code', 'pur_type', 'shipping_method', 'operation_type', 'creator', 'account_type', 'pay_type', 'currency_code','items.sku','buyer','purchas_status','refund_status','create_type','audit_return','created_at','singletype','reference','start_time','end_time','pur_type','is_arrival','complete_type','qc_abnormal_status','receiving_exception_status','code','ss.supplier_type','sku_type','merchandiser','buyer','supplier_name','pay_status','ship.express_no','page_size'], 'safe'],

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
  	$fields= ['pur_purchase_order_items.*','pur_purchase_order.supplier_name as supplier_name','pur_purchase_order.submit_time as submit_time','pur_supplier.create_time as createtime'];
//     	$query = PurchaseOrder::find();
    	$query = PurchaseOrderItems::find()->andFilterWhere(['not in', 'purchas_status', [1,2,4,10]]);

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => !empty($params['PurchaseOrderSearch']['page_size']) ? $params['PurchaseOrderSearch']['page_size'] : 20,
            ],
        ]);

//         $query->where(['in','purchas_status',['1']]);
//         $query->andFilterWhere(['not in','purchase_type',['3']]);
//         $query->orderBy('id desc');

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
//         $orders=PurchaseOrderItems::find()->select('pur_number,sku,name,price,qty')->groupBy('pur_number,sku')->asArray()->all();
//         Vhelper::dump($orders);
//         $puid= PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
//         $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
//         if(in_array(Yii::$app->user->id,$ids))
//         {

//         } else {
//             $query->andWhere(['in', 'buyer',Yii::$app->user->identity->username]);
//         }

        //单号
//        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);
       // $query->andFilterWhere(['=', 'creator', Yii::$app->user->identity->username]);
// 	       $query->joinWith('demand');
	       $query->joinWith('purNumber');
	       $query->joinWith('suppliers');
	       $query->joinWith('product');
//            $query->joinWith('demand');
// 	       $query->demand();
// 	       Vhelper::dump($query);


        // grid filtering conditions	    
	    $query->andFilterWhere(['between', 'pur_purchase_order.created_at', $this->start_time, $this->end_time]);
        $query->andFilterWhere([
//             'buyer'            => $this->buyer,
//             'purchas_status'   => $this->purchas_status,
//             'create_type'      => $this->create_type,
//             'audit_return'     => $this->audit_return,       		
            'pur_purchase_order.purchase_type'   => trim($this->purchase_type),
        	'pur_product.product_category_id' => trim($this->product_category_id),
            'pur_purchase_order.supplier_code' => trim($this->supplier_code),
//             'shipping_method'  => $this->shipping_method,
            'pur_purchase_order_items.sku' => trim($this->sku),
        ]);
//         $query->andFilterWhere(['is_purchase'=>1]);
//        Vhelper::dump($query->createCommand()->getRawSql());
//         $query->andFilterWhere(['like', 'supplier_name', $this->supplier_name])
//             ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))])
//             ->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);
//         $query->groupBy('pur_number,sku');
        return $dataProvider;
    }

    /**
     * 采购审核搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
    //    $query  = PurchaseOrderItems::find()->select('max(pur_purchase_order_items.id) as ids,pur_purchase_order_items.sku,pur_purchase_order_items.pur_number,pur_purchase_order_items.name,pur_purchase_order_items.price,items_totalprice')->groupBy('sku');

        // add conditions that should always apply here
      // $query  = SupplierSkuDetail::find();   //从中间表查询
    	//$query=SkuSalesStatistics::find()->alias('a')->select('sum(a.days_sales_3) as days_sales_3,sum(a.days_sales_15) as days_sales_15,sum(a.days_sales_30) as days_sales_30,sum(a.days_sales_60) as days_sales_60,sum(a.days_sales_90) as days_sales_90,b.sku,b.supplier_name')
        //                               ->rightJoin('pur_supplier_detail as b','{{a.sku}}={{b.sku}}')
        //                               ->groupBy('b.sku,b.pur_number');
//         $query=SupplierSkuDetail::find()->alias('b')->select('sum(a.days_sales_3) as days_sales_3,sum(a.days_sales_15) as days_sales_15,sum(a.days_sales_30) as days_sales_30,sum(a.days_sales_60) as days_sales_60,sum(a.days_sales_90) as days_sales_90,b.sku as sku,b.supplier_name as supplier_name')
//                                        ->leftJoin('pur_sku_sales_statistics as a','{{a.sku}}={{b.sku}}')
//                                        ->groupBy('b.sku,b.pur_number');
        $query=SupplierSkuDetail::find();
        $query->joinWith('supplierbuyer');
//         $query->select('pur_supplier_detail.*,pur_supplier_buyer.buyer as buyer');
        $query->andFilterWhere(['pur_supplier_buyer.type'=>1 , 'pur_supplier_buyer.status'=>1]);
//         $query->groupBy('sku');
//        $query->from(['c'=>$sales])->rightJoin('pur_supplier_detail as b','{{c.sku}}={{b.sku}}');
//        $query->select('c.days_sales_3,c.days_sales_15,c.days_sales_30,c.days_sales_60,c.days_sales_90,b.*');
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        	'pagination' => [
        			'pageSize' => $pageSize,
        	],
        ]);
      
//         $query->where(['=', 'audit_return', '2']);
//         $query->andFilterWhere(['!=','purchase_type','3']);
//         $query->andFilterWhere(['in','purchas_status',['2']]);

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
//         $query->joinWith('purchaseSupplier');
//        $query->joinWith('skuSale');
//         $query->joinWith('skustock');
//         $query->joinWith('product');
//         Vhelper::dump($query->createCommand()->getRawSql());
//         if ($this->getAttribute('items.sku'))
//         {
//             $query->joinWith('purchaseOrderItems AS items');
//         }
        // grid filtering conditions
//         $query->andFilterWhere([
//             'buyer' => $this->buyer,
//             'supplier_code' => $this->supplier_code,
//             //'items.sku' => $this->getAttribute('items.sku'),
//         ]);
// Vhelper::dump($query->createCommand()->getRawSql());

$dataProvider->setSort([
		'attributes' => [
				/* 其它字段不要动 */
				/*  下面这段是加入的 */
				/*=============*/
				'days_sales_60' => [
						'desc' => ['pur_supplier_detail.days_sales_60' => SORT_DESC],
						'asc' => ['pur_supplier_detail.days_sales_60' => SORT_ASC],
						'label' => 'days_sales_60'
				],
				'days_sales_90' => [
						'desc' => ['pur_supplier_detail.days_sales_90' => SORT_DESC],
						'asc' => ['pur_supplier_detail.days_sales_90' => SORT_ASC],			
						'label' => 'days_sales_90'
				],
				'days_sales_30' => [
						'desc' => ['pur_supplier_detail.days_sales_30' => SORT_DESC],
						'asc' => ['pur_supplier_detail.days_sales_30' => SORT_ASC],
						'label' => 'days_sales_30'
				],
				'days_sales_15' => [
						'desc' => ['pur_supplier_detail.days_sales_15' => SORT_DESC],
						'asc' => ['pur_supplier_detail.days_sales_15' => SORT_ASC],
						'label' => 'days_sales_15'
				],
				'days_sales_3' => [
						'desc' => ['pur_supplier_detail.days_sales_3' => SORT_DESC],
						'asc' => ['pur_supplier_detail.days_sales_3' => SORT_ASC],
						'label' => 'days_sales_3'
				],
// 				'qty_13' => [
// 						'desc' => ['pur_purchase_suggest.qty_13' => SORT_DESC],
// 						'asc' => ['pur_purchase_suggest.qty_13' => SORT_ASC],					
// 						'label' => 'qty_13'
// 				],
				'qty_13' => [
						'desc' => ['pur_supplier_detail.qty_13' => SORT_DESC],
						'asc' => ['pur_supplier_detail.qty_13' => SORT_ASC],
						'label' => 'qty_13'
				],
				'price' => [
						'desc' => ['pur_supplier_detail.price' => SORT_DESC],
						'asc' => ['pur_supplier_detail.price' => SORT_ASC],
						'label' => 'price'
				],
				/*=============*/
		]
]);
if(!empty($this->product_line)){
	$query->andFilterWhere(['in','pur_supplier_detail.product_linelist_id',BaseServices::getProductLineChild($this->product_line)]);
}
if(!empty($this->sku)){
	$query->andFilterWhere(['like','pur_supplier_detail.sku',trim($this->sku)]);
}
$query->andFilterWhere([
// 		'pur_purchase_order.purchase_type'   => trim($this->purchase_type),
// 		'pur_product.product_category_id' => trim($this->product_category_id),
		'pur_supplier_detail.supplier_code' => trim($this->supplier_code),
// 		'pur_supplier_detail.sku' => trim($this->sku),
// 		'pur_product.product_category_id' => trim($this->product_category_id),
// 		'pur_sku_sales_statistics.days_sales_60' => $this->days_sales_60,
// 		'pur_sku_sales_statistics.days_sales_90' => $this->days_sales_90,
// 		'pur_sku_sales_statistics.days_sales_30' => $this->days_sales_30,
// 		'pur_sku_sales_statistics.days_sales_15' => $this->days_sales_15,
// 		'pur_sku_sales_statistics.days_sales_3' => $this->days_sales_3,
// 		'pur_sku_sales_statistics.days_sales_60' => $this->days_sales_60,
// 		'pur_purchase_suggest.qty_13' => $this->qty_13,
]);
if (!empty($this->days_sales_60)){
$query->andFilterWhere(['=', 'pur_supplier_detail.days_sales_60', $this->days_sales_60]) ;
}//<=====加入这句
if (!empty($this->days_sales_30)){
$query->andFilterWhere(['=', 'pur_supplier_detail.days_sales_30', $this->days_sales_30]) ;
}
if (!empty($this->days_sales_90)){
$query->andFilterWhere(['=', 'pur_supplier_detail.days_sales_90', $this->days_sales_90]) ;
}
if (!empty($this->days_sales_15)){
$query->andFilterWhere(['=', 'pur_supplier_detail.days_sales_15', $this->days_sales_15]) ;
}
if (!empty($this->days_sales_3)){
$query->andFilterWhere(['=', 'pur_supplier_detail.days_sales_3', $this->days_sales_3]) ;
}
if (!empty($this->qty_13)){
$query->andFilterWhere(['=', 'pur_supplier_detail.qty_13', $this->qty_13]) ;}
if (!empty($this->price)){
	$query->andFilterWhere(['=', 'pur_supplier_detail.price', $this->price]) ;}
return $dataProvider;
    }

    /**
     * 采购复审搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search2($params)
    {
        $query = PurchaseOrder::find();

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
            ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);


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
        $query = PurchaseOrder::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => !empty($params['PurchaseOrderSearch']['page_size']) ? $params['PurchaseOrderSearch']['page_size'] : 20,
            ],
        ]);
        $query->where(['in','purchas_status',['3','5','6','7','8','9','10']]);
        $query->andFilterWhere(['in','pur_purchase_order.purchase_type',['1','2','4']]);

        /*$user = ['张涛涛','admin'];
        $user_name=Yii::$app->user->identity->username;

        $pur_user=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

        if(!in_array($user_name,$user) && !empty($pur_user) && $pur_user->grade != 3){
               if($pur_user->type==0){
                   $query->andFilterWhere(['=', 'pur_purchase_order.purchase_type', 1]);
               }elseif($pur_user->type==1){
                   $query->andFilterWhere(['=', 'pur_purchase_order.purchase_type', 2]);
               }else{
                   $query->andFilterWhere(['=', 'buyer', Yii::$app->user->identity->username]);
               }
        }*/
        /*$puid= PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
        $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
        if(in_array(Yii::$app->user->id,$ids))
        {

        } else {
            $query->andWhere(['in', 'buyer',Yii::$app->user->identity->username]);
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
        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);


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
            'ship.express_no' => trim($this->getAttribute('ship.express_no')),


        ]);

        $query->andFilterWhere(['=', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))])
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
        $query = PurchaseOrder::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        $query->where(['and',['in','refund_status',['4']],['not in','purchase_type',['1']]]);
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
        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)])
            ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);



        //$query->andFilterWhere(['=','items.sku' , $this->getAttribute('items.sku')]);

        // grid filtering conditions
        $query->andFilterWhere([
            'pur_purchase_order.buyer' => trim($this->buyer),

        ]);

//        Vhelper::dump($query->createCommand()->getRawSql());
        return $dataProvider;
    }
    /**
     * 采购单fba搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search5($params)
    {
        $query = PurchaseOrder::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
        $query->where(['not in','purchas_status',['4']]);
        $query->andFilterWhere(['in','pur_purchase_order.purchase_type',['3']]);
        $this->load($params);
        $query->joinWith(['purchaseOrderAccount']);
        $query->orderBy('submit_time asc');
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
        if($this->getAttribute('ship.express_no'))
        {
            $query->innerJoinWith('orderShip AS ship');
        }
        if(!empty($this->purchas_status)){
            $query->andFilterWhere(['in','purchas_status',$this->purchas_status]);
        }
        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);


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
            'items.sku' => trim($this->getAttribute('items.sku')),
            'ship.express_no' => trim($this->getAttribute('ship.express_no')),


        ]);

        $query->andFilterWhere(['=', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['like', 'pur_purchase_order.supplier_name', trim($this->supplier_name)])
            ->andFilterWhere(['=', 'pur_type', $this->pur_type]);

        $query->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);
        return $dataProvider;
    }
}
