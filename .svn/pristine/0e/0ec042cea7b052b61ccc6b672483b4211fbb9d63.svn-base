<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use app\services\PurchaseSuggestQuantityServices;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseSuggest;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseArrayHelper;

/**
 * PurchaseSuggestSearch represents the model behind the search form about `app\models\PurchaseSuggest`.
 */
class PurchaseSuggestSearch extends PurchaseSuggest
{
    public $start_time;
    public $end_time;
    public $amount_1;
    public $amount_2;
    public $sourcing_status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'qty', 'payment_method', 'supplier_settlement', 'product_category_id', 'on_way_stock', 'available_stock', 'stock', 'left_stock', 'days_sales_3', 'days_sales_7', 'days_sales_15', 'days_sales_30', 'sales_avg'], 'integer'],
            [['warehouse_code', 'sourcing_status','warehouse_name', 'sku', 'name', 'supplier_code', 'supplier_name', 'buyer', 'replenish_type', 'currency', 'ship_method', 'is_purchase', 'created_at', 'creator', 'category_cn_name', 'type','process_qty','start_time','end_time','buyer_id','left','product_status','state','page_size','amount_1','amount_2','untreated_time'], 'safe'],
            [['price'], 'number'],
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
     * 国内仓采购建议搜索
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $noDataProvider = false)
    {
        $query = PurchaseSuggest::find();
        $query->select('pur_purchase_suggest.*,pur_product.create_time,pur_product.product_cost');
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);
        if(!empty($params['sort']))
        {
            //$query->orderBy('supplier_code desc');
        } else{
            $query->orderBy('pur_purchase_suggest.left_stock asc');
        }
        //$query->groupBy(['pur_purchase_suggest.sku','pur_purchase_suggest.warehouse_code']);
        //$query->orderBy('left_stock asc');

        $warehouse_code = PurchaseSuggestQuantityServices::getSuggestWarehouseCode();
        $query->andWhere(['in','pur_purchase_suggest.warehouse_code',$warehouse_code]);
        $query->andWhere(['>','pur_purchase_suggest.qty',0]);
        $query->andWhere(['!=','pur_purchase_suggest.sku','XJFH0000']);
        $query->andWhere(['in','pur_purchase_suggest.purchase_type',1]);

        $query->leftJoin('pur_product','pur_purchase_suggest.sku=pur_product.sku');

//        $query->andWhere(['!=','product_status',7]);
        //$user = ['史家松','张涛涛','王开伟','吴洋'];
        $puid=PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
        $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
        if(in_array(Yii::$app->user->id,$ids))
        {

        } else {
            $query->andWhere(['in', 'pur_purchase_suggest.buyer_id',Yii::$app->user->id]);
        }
        //$query->andWhere(['>','price',0]);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if(!empty($params)){
            if(!empty($params['PurchaseSuggestSearch']['buyer_id'])){
                if(is_numeric($this->buyer_id)){
                    $query->andFilterWhere(['pur_purchase_suggest.buyer_id' => $this->buyer_id,]);
                }else{
                    $group=[
                        'g1'=>'1',
                        'g2'=>'2',
                        'g3'=>'3',
                        'g4'=>'4',
                        'g5'=>'5',
                        
                    ];

                    $gid=$group[$this->buyer_id];

                    $puid=PurchaseUser::find()->select('pur_user_id')->where(['group_id'=>$gid])->asArray()->all();

                    $query->andFilterWhere(['in', 'pur_purchase_suggest.buyer_id', array_values(BaseArrayHelper::map($puid,'pur_user_id','pur_user_id'))]);
            }
            }
            $query->andFilterWhere(['between', 'pur_purchase_suggest.created_at', $this->start_time, $this->end_time]);
        }
        //判断创建时间
        if(empty($this->start_time) || empty($this->end_time))
        {
            $start_time = date('Y-m-d 00:00:00');
            $end_time   = date('Y-m-d 23:59:59');
            $query->andFilterWhere(['between', 'pur_purchase_suggest.created_at', $start_time, $end_time]);
        }
        $query->leftJoin(ProductSourceStatus::tableName().' pst','pur_purchase_suggest.sku=pst.sku');

        //货源状态搜索
        if(!empty($this->sourcing_status)){
            if($this->sourcing_status==1){
                $query->andWhere(['or',['pst.sourcing_status'=>$this->sourcing_status,'pst.status'=>1],['pst.sourcing_status'=>null]]);
            }elseif($this->sourcing_status=='all'){

            }else
            {
                $query->andFilterWhere(['pst.sourcing_status'=>$this->sourcing_status,'pst.status'=>1]);
            }
        }else{
            $query->andWhere('(pst.sourcing_status <>2 and pst.status=1) or isnull(pst.id)');
        }
        //销售导入数据筛选
        if(!empty($params['PurchaseSuggestSearch']['sales_import']) && $params['PurchaseSuggestSearch']['sales_import'] == 1){
            $quantity =  PurchaseSuggestQuantity::find()
                ->select('pur_purchase_suggest_quantity.sku,pur_purchase_suggest_quantity.purchase_warehouse')
                ->Where(['between','pur_purchase_suggest_quantity.create_time',date('Y-m-d 00:00:00',time()-86400),date('Y-m-d 23:59:59',time()-86400)])->asArray()->all();
            if($quantity && count($quantity)>0){
                $condition = ['or'];
                foreach ($quantity as $key => $val){
                    $list = [];
                    $list[] = 'and';
                    $list[] = "pur_purchase_suggest.sku= '{$val['sku']}'";
                    $list[] = "pur_purchase_suggest.warehouse_code= '{$val['purchase_warehouse']}'";
                    array_push($condition,$list);
                }
                if(count($condition) > 1){
                    $query->andWhere($condition);
                }
            }
        }

        if($this->left==1)
        {
//            $query->andFilterWhere(['<','lack_total',0]);
            $query->andFilterWhere(['<','pur_purchase_suggest.left_stock',0]);
        } elseif($this->left==2){
//            $query->andFilterWhere(['>=','lack_total',0]);
            $query->andFilterWhere(['>=','pur_purchase_suggest.left_stock',0]);
        }else{

        }
        if($this->amount_1 <= $this->amount_2)
        {
            $query->andFilterWhere(['>', 'pur_purchase_suggest.qty', $this->amount_1]);
            $query->andFilterWhere(['<=', 'pur_purchase_suggest.qty', $this->amount_2]);
        }
        //Vhelper::dump($params);
        // grid filtering conditions
        $query->andFilterWhere([
            'pur_purchase_suggest.id' => $this->id,
            //'qty' => $this->qty,
//            'state' => $this->state,
//            'state' => !empty($this->state) ? $this->state : '0',
            'pur_purchase_suggest.state' => !empty($this->state) ? ($this->state!='all'? $this->state : [0,1,2]):'0' ,
//            'product_status' => !empty($this->product_status) ?($this->product_status != 'all' ? $this->product_status : null) : 4 ,
            'pur_purchase_suggest.price' => $this->price,
            'pur_purchase_suggest.payment_method' => $this->payment_method,
            'pur_purchase_suggest.supplier_settlement' => $this->supplier_settlement,
            'pur_purchase_suggest.created_at' => $this->created_at,
            'pur_purchase_suggest.product_category_id' => $this->product_category_id,
            'pur_purchase_suggest.on_way_stock' => $this->on_way_stock,
            'pur_purchase_suggest.available_stock' => $this->available_stock,
            'pur_purchase_suggest.stock' => $this->stock,
//            'left_stock' => $this->left_stock,
            'pur_purchase_suggest.days_sales_3' => $this->days_sales_3,
            'pur_purchase_suggest.days_sales_7' => $this->days_sales_7,
            'pur_purchase_suggest.days_sales_15' => $this->days_sales_15,
            'pur_purchase_suggest.days_sales_30' => $this->days_sales_30,
            'pur_purchase_suggest.sales_avg' => $this->sales_avg,
            'pur_purchase_suggest.replenish_type' => $this->replenish_type,
            'pur_purchase_suggest.ship_method' => $this->ship_method,
        ]);
        if (!empty($this->product_status)) {
            $query->andFilterWhere([
                'pur_purchase_suggest.product_status' => ($this->product_status != 'all') ? $this->product_status : null,
            ]);
        } else {
            $query->andWhere(['not in','pur_purchase_suggest.product_status',['0','5','6','7','100']]);
        }
        $query->andFilterWhere(['like', 'pur_purchase_suggest.warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['like', 'pur_purchase_suggest.warehouse_name', $this->warehouse_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.sku', trim($this->sku)])
            ->andFilterWhere(['like', 'pur_purchase_suggest.name', $this->name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.supplier_code', $this->supplier_code])
            ->andFilterWhere(['like', 'pur_purchase_suggest.supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.buyer', $this->buyer])
            ->andFilterWhere(['like', 'pur_purchase_suggest.currency', $this->currency])
            ->andFilterWhere(['like', 'pur_purchase_suggest.is_purchase', $this->is_purchase])
            ->andFilterWhere(['like', 'pur_purchase_suggest.creator', $this->creator])
            ->andFilterWhere(['like', 'pur_purchase_suggest.category_cn_name', $this->category_cn_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.type', $this->type]);
       // Vhelper::dump($query->createCommand()->getRawSql());

        \Yii::$app->session->set('PurchaseSuggestSearchData', $params);
      //  echo $query->createCommand()->getRawSql();exit();
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }

    /**
     * 海外仓采购建议搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
        $query = PurchaseSuggest::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $this->load($params);
        $query->orderBy('left_stock asc');
        $query->where(['is_purchase'=>'Y']);
        $query->andwhere(['purchase_type'=>'4']);
        $query->andWhere(['>','qty',0]);
        $query->andWhere(['>','price',0]);
        $query->andWhere(['not in','warehouse_code',['DG','SZ_AA']]);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'qty' => $this->qty,
            'price' => $this->price,
            'payment_method' => $this->payment_method,
            'supplier_settlement' => $this->supplier_settlement,
            'created_at' => $this->created_at,
            'product_category_id' => $this->product_category_id,
            'on_way_stock' => $this->on_way_stock,
            'available_stock' => $this->available_stock,
            'stock' => $this->stock,
            'left_stock' => $this->left_stock,
            'days_sales_3' => $this->days_sales_3,
            'days_sales_7' => $this->days_sales_7,
            'days_sales_15' => $this->days_sales_15,
            'days_sales_30' => $this->days_sales_30,
            'sales_avg' => $this->sales_avg,
            'replenish_type' => $this->replenish_type,
            'ship_method' => $this->ship_method,
        ]);

        $query->andFilterWhere(['like', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['like', 'warehouse_name', $this->warehouse_name])
            ->andFilterWhere(['like', 'sku', trim($this->sku)])
            ->andFilterWhere(['like', 'name', trim($this->name)])
            ->andFilterWhere(['like', 'supplier_code', $this->supplier_code])
            ->andFilterWhere(['like', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'buyer', trim($this->buyer)])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'is_purchase', $this->is_purchase])
            ->andFilterWhere(['like', 'creator', $this->creator])
            ->andFilterWhere(['like', 'category_cn_name', $this->category_cn_name])
            ->andFilterWhere(['like', 'type', $this->type]);
        return $dataProvider;
    }


    /**
     * 获取所有有建议数量（建议数量大于0）的国内仓采购建议
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchQuantityCorrection($params, $noDataProvider = false)
    {
        $query = PurchaseSuggest::find();
        $query->select('distinct(pur_purchase_suggest.sku),pur_purchase_suggest.warehouse_code,pur_purchase_suggest.qty,
            pur_purchase_suggest.sales_avg,pur_purchase_suggest.id')->groupBy(['sku','warehouse_code']);
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);

        $query->andWhere(['in','pur_purchase_suggest.warehouse_code',['FBA_SZ_AA','SZ_AA','HW_XNC','ZDXNC']]);
        $query->andWhere(['>','pur_purchase_suggest.qty',0]);
        $query->andWhere(['!=','pur_purchase_suggest.sku','XJFH0000']);
        $query->andWhere(['in','pur_purchase_suggest.purchase_type',1]);

        $query->leftJoin('pur_product','pur_purchase_suggest.sku=pur_product.sku');

        $puid=PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
        $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
        if(in_array(Yii::$app->user->id,$ids))
        {

        } else {
            $query->andWhere(['in', 'pur_purchase_suggest.buyer_id',Yii::$app->user->id]);
        }

        if (!$this->validate()) {
            return $dataProvider;
        }

        if(!empty($params)){
            if(!empty($params['PurchaseSuggestSearch']['buyer_id'])){
                if(is_numeric($this->buyer_id)){
                    $query->andFilterWhere(['pur_purchase_suggest.buyer_id' => $this->buyer_id,]);
                }else{
                    $group=[
                        'g1'=>'1',
                        'g2'=>'2',
                        'g3'=>'3',
                        'g4'=>'4',
                        'g5'=>'5',
                        
                    ];

                    $gid=$group[$this->buyer_id];

                    $puid=PurchaseUser::find()->select('pur_user_id')->where(['group_id'=>$gid])->asArray()->all();

                    $query->andFilterWhere(['in', 'pur_purchase_suggest.buyer_id', array_values(BaseArrayHelper::map($puid,'pur_user_id','pur_user_id'))]);
                }
            }
            $query->andFilterWhere(['between', 'pur_purchase_suggest.created_at', $this->start_time, $this->end_time]);
        }
        //销售导入数据筛选
        if(!empty($params['PurchaseSuggestSearch']['sales_import']) && $params['PurchaseSuggestSearch']['sales_import'] == 1){
            $quantity =  PurchaseSuggestQuantity::find()
                ->select('pur_purchase_suggest_quantity.sku,pur_purchase_suggest_quantity.purchase_warehouse')
                ->Where(['between','pur_purchase_suggest_quantity.create_time',date('Y-m-d 00:00:00',time()-86400),date('Y-m-d 23:59:59',time()-86400)])->asArray()->all();
            if($quantity && count($quantity)>0){
                $condition = ['or'];
                foreach ($quantity as $key => $val){
                    $list = [];
                    $list[] = 'and';
                    $list[] = "pur_purchase_suggest.sku= '{$val['sku']}'";
                    $list[] = "pur_purchase_suggest.warehouse_code= '{$val['purchase_warehouse']}'";
                    array_push($condition,$list);
                }
                if(count($condition) > 1){
                    $query->andWhere($condition);
                }
            }
        }

        if($this->amount_1 <= $this->amount_2)
        {
            $query->andFilterWhere(['>', 'pur_purchase_suggest.qty', $this->amount_1]);
            $query->andFilterWhere(['<=', 'pur_purchase_suggest.qty', $this->amount_2]);
        }

        $query->andFilterWhere([
            'pur_purchase_suggest.id' => $this->id,
            //'qty' => $this->qty,
//            'state' => $this->state,
//            'state' => !empty($this->state) ? $this->state : '0',
            'pur_purchase_suggest.state' => !empty($this->state) ? ($this->state!='all'? $this->state : [0,1,2]):'0' ,
//            'product_status' => !empty($this->product_status) ?($this->product_status != 'all' ? $this->product_status : null) : 4 ,
            'pur_purchase_suggest.price' => $this->price,
            'pur_purchase_suggest.payment_method' => $this->payment_method,
            'pur_purchase_suggest.supplier_settlement' => $this->supplier_settlement,
            'pur_purchase_suggest.created_at' => $this->created_at,
            'pur_purchase_suggest.product_category_id' => $this->product_category_id,
            'pur_purchase_suggest.on_way_stock' => $this->on_way_stock,
            'pur_purchase_suggest.available_stock' => $this->available_stock,
            'pur_purchase_suggest.stock' => $this->stock,
//            'left_stock' => $this->left_stock,
            'pur_purchase_suggest.days_sales_3' => $this->days_sales_3,
            'pur_purchase_suggest.days_sales_7' => $this->days_sales_7,
            'pur_purchase_suggest.days_sales_15' => $this->days_sales_15,
            'pur_purchase_suggest.days_sales_30' => $this->days_sales_30,
            'pur_purchase_suggest.sales_avg' => $this->sales_avg,
            'pur_purchase_suggest.replenish_type' => $this->replenish_type,
            'pur_purchase_suggest.ship_method' => $this->ship_method,
        ]);
        if (!empty($this->product_status)) {
            $query->andFilterWhere([
                'pur_purchase_suggest.product_status' => ($this->product_status != 'all') ? $this->product_status : null,
            ]);
        } else {
            $query->andWhere(['not in','pur_purchase_suggest.product_status',['0','5','6','7','100']]);
        }
        $query->andFilterWhere(['like', 'pur_purchase_suggest.warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['like', 'pur_purchase_suggest.warehouse_name', $this->warehouse_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.sku', trim($this->sku)])
            ->andFilterWhere(['like', 'pur_purchase_suggest.name', $this->name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.supplier_code', $this->supplier_code])
            ->andFilterWhere(['like', 'pur_purchase_suggest.supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.buyer', $this->buyer])
            ->andFilterWhere(['like', 'pur_purchase_suggest.currency', $this->currency])
            ->andFilterWhere(['like', 'pur_purchase_suggest.is_purchase', $this->is_purchase])
            ->andFilterWhere(['like', 'pur_purchase_suggest.creator', $this->creator])
            ->andFilterWhere(['like', 'pur_purchase_suggest.category_cn_name', $this->category_cn_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.type', $this->type]);
        // Vhelper::dump($query->createCommand()->getRawSql());
        \Yii::$app->session->set('PurchaseSuggestSearchData', $params);
        if ($noDataProvider)
            return $query;

        return $dataProvider;
    }

    //fba采购建议查询
    public function searchFba($params, $noDataProvider = false)
    {
        $query = PurchaseSuggest::find();
        $query->select('pur_purchase_suggest.*,pur_product.create_time,pur_product.product_cost');
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);
        if(!empty($params['sort']))
        {
            //$query->orderBy('supplier_code desc');
        } else{
            $query->orderBy('pur_purchase_suggest.left_stock asc');
        }
        $warehouse_code = PurchaseSuggestQuantityServices::getFbaSuggestWarehouseCode();
        $query->andWhere(['in','pur_purchase_suggest.warehouse_code',$warehouse_code]);
        $query->andWhere(['>','pur_purchase_suggest.qty',0]);
        $query->andWhere(['in','pur_purchase_suggest.purchase_type',3]);

        $query->leftJoin('pur_product','pur_purchase_suggest.sku=pur_product.sku');
        if (!$this->validate()) {
            return $dataProvider;
        }
        //判断创建时间
        if(empty($this->start_time) || empty($this->end_time))
        {
            $start_time = date('Y-m-d 00:00:00');
            $end_time   = date('Y-m-d 23:59:59');
            $query->andFilterWhere(['between', 'pur_purchase_suggest.created_at', $start_time, $end_time]);
        }
        $query->leftJoin(ProductSourceStatus::tableName().' pst','pur_purchase_suggest.sku=pst.sku');

        //货源状态搜索
        if(!empty($this->sourcing_status)){
            if($this->sourcing_status==1){
                $query->andWhere(['or',['pst.sourcing_status'=>$this->sourcing_status,'pst.status'=>1],['pst.sourcing_status'=>null]]);
            }elseif($this->sourcing_status=='all'){

            }else
            {
                $query->andFilterWhere(['pst.sourcing_status'=>$this->sourcing_status,'pst.status'=>1]);
            }
        }else{
            $query->andWhere('(pst.sourcing_status <>2 and pst.status=1) or isnull(pst.id)');
        }
        //销售导入数据筛选
        if(!empty($params['PurchaseSuggestSearch']['sales_import']) && $params['PurchaseSuggestSearch']['sales_import'] == 1){
            $quantity =  PurchaseSuggestQuantity::find()
                ->select('pur_purchase_suggest_quantity.sku,pur_purchase_suggest_quantity.purchase_warehouse')
                ->Where(['between','pur_purchase_suggest_quantity.create_time',date('Y-m-d 00:00:00',time()-86400),date('Y-m-d 23:59:59',time()-86400)])->asArray()->all();
            if($quantity && count($quantity)>0){
                $condition = ['or'];
                foreach ($quantity as $key => $val){
                    $list = [];
                    $list[] = 'and';
                    $list[] = "pur_purchase_suggest.sku= '{$val['sku']}'";
                    $list[] = "pur_purchase_suggest.warehouse_code= '{$val['purchase_warehouse']}'";
                    array_push($condition,$list);
                }
                if(count($condition) > 1){
                    $query->andWhere($condition);
                }
            }
        }

        if($this->left==1)
        {
            $query->andFilterWhere(['<','pur_purchase_suggest.left_stock',0]);
        } elseif($this->left==2){
            $query->andFilterWhere(['>=','pur_purchase_suggest.left_stock',0]);
        }else{

        }
        if($this->amount_1 <= $this->amount_2)
        {
            $query->andFilterWhere(['>', 'pur_purchase_suggest.qty', $this->amount_1]);
            $query->andFilterWhere(['<=', 'pur_purchase_suggest.qty', $this->amount_2]);
        }

        $query->andFilterWhere([
            'pur_purchase_suggest.id' => $this->id,
            'pur_purchase_suggest.state' => !empty($this->state) ? ($this->state!='all'? $this->state : [0,1,2]):'0' ,
            'pur_purchase_suggest.price' => $this->price,
            'pur_purchase_suggest.payment_method' => $this->payment_method,
            'pur_purchase_suggest.supplier_settlement' => $this->supplier_settlement,
            'pur_purchase_suggest.created_at' => $this->created_at,
            'pur_purchase_suggest.product_category_id' => $this->product_category_id,
            'pur_purchase_suggest.on_way_stock' => $this->on_way_stock,
            'pur_purchase_suggest.available_stock' => $this->available_stock,
            'pur_purchase_suggest.stock' => $this->stock,
            'pur_purchase_suggest.buyer_id' => $this->buyer_id,
            'pur_purchase_suggest.days_sales_3' => $this->days_sales_3,
            'pur_purchase_suggest.days_sales_7' => $this->days_sales_7,
            'pur_purchase_suggest.days_sales_15' => $this->days_sales_15,
            'pur_purchase_suggest.days_sales_30' => $this->days_sales_30,
            'pur_purchase_suggest.sales_avg' => $this->sales_avg,
            'pur_purchase_suggest.replenish_type' => $this->replenish_type,
            'pur_purchase_suggest.ship_method' => $this->ship_method,
        ]);
        if (!empty($this->product_status)) {
            $query->andFilterWhere([
                'pur_purchase_suggest.product_status' => ($this->product_status != 'all') ? $this->product_status : null,
            ]);
        } else {
            $query->andWhere(['not in','pur_purchase_suggest.product_status',['0','5','6','7','100']]);
        }
        $query->andFilterWhere(['like', 'pur_purchase_suggest.warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['like', 'pur_purchase_suggest.warehouse_name', $this->warehouse_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.sku', trim($this->sku)])
            ->andFilterWhere(['like', 'pur_purchase_suggest.name', $this->name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.supplier_code', $this->supplier_code])
            ->andFilterWhere(['like', 'pur_purchase_suggest.supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.buyer', $this->buyer])
            ->andFilterWhere(['like', 'pur_purchase_suggest.currency', $this->currency])
            ->andFilterWhere(['like', 'pur_purchase_suggest.is_purchase', $this->is_purchase])
            ->andFilterWhere(['like', 'pur_purchase_suggest.creator', $this->creator])
            ->andFilterWhere(['like', 'pur_purchase_suggest.category_cn_name', $this->category_cn_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest.type', $this->type]);
        \Yii::$app->session->set('FbaPurchaseSuggestSearchData', $params);
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }
}
