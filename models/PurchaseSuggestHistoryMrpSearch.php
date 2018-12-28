<?php

namespace app\models;

use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseSuggestHistoryMrp;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseArrayHelper;

/**
 * PurchaseSuggestHistorySearch represents the model behind the search form about `app\models\PurchaseSuggestHistory`.
 */
class PurchaseSuggestHistoryMrpSearch extends PurchaseSuggestHistoryMrp
{
    public $start_time;
    public $end_time;
    public $amount_1;
    public $amount_2;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'qty', 'payment_method', 'supplier_settlement', 'product_category_id', 'on_way_stock', 'available_stock', 'stock', 'left_stock', 'days_sales_3', 'days_sales_7', 'days_sales_15', 'days_sales_30', 'sales_avg'], 'integer'],
            [['warehouse_code', 'warehouse_name', 'sku', 'name', 'supplier_code', 'supplier_name', 'buyer', 'replenish_type', 'currency', 'ship_method', 'is_purchase', 'created_at', 'creator', 'category_cn_name', 'type','process_qty','start_time','end_time','buyer_id','left','product_status','state','amount_1','amount_2'], 'safe'],
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
    public function search($params)
    {
        $query = PurchaseSuggestHistoryMrp::find()->select(['id','safe_delivery','supplier_code','left_stock','warehouse_code','qty','purchase_type','buyer_id','created_at','product_status','state','price','payment_method','supplier_settlement','product_category_id','on_way_stock','available_stock','stock','days_sales_3','days_sales_7','days_sales_15','days_sales_30','sales_avg','replenish_type','ship_method','warehouse_code','warehouse_name','sku','name','supplier_code','supplier_name','buyer','currency','is_purchase','creator','category_cn_name','type']);
       // $query = PurchaseSuggestHistory::find()->select(['id','buyer','sku','product_img','product_status','name','supplier_name','price','sales_avg','available_stock','on_way_stock','available_stock','left_stock','qty','created_at','state']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20, //100
            ],
        ]);
        if(empty($params)){
            $query->andWhere("1 = 0");
            return $dataProvider;
        }
        $this->load($params);
        $query->orderBy('id Desc');
        $query->andWhere(['in','warehouse_code',['DG','SZ_AA','xnc','ZDXNC','CDxuni','ZMXNC_WM','ZMXNC_EB','HW_XNC']]);
        $query->andWhere(['>','qty',0]);
        $query->andWhere(['in','purchase_type',1]);

    //    $query->andWhere(['!=','product_status',7]);
        //$user = ['史家松','张涛涛','王开伟','吴洋'];
        $puid=PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
        $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
        if(in_array(Yii::$app->user->id,$ids))
        {

        } else {
            $query->andWhere(['in', 'buyer_id',Yii::$app->user->id]);
        }
        //$query->andWhere(['>','price',0]);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if(!empty($params)){
            if(!empty($params['PurchaseSuggestHistoryMrpSearch']['buyer_id'])){
                if(is_numeric($this->buyer_id)){
                    $query->andFilterWhere(['buyer_id' => $this->buyer_id,]);
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
                    $query->andFilterWhere(['in', 'buyer_id', array_values(BaseArrayHelper::map($puid,'pur_user_id','pur_user_id'))]);
                }
            }
//            $query->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);
        }
        //判断创建时间
//        if(empty($this->start_time) || empty($this->end_time))
        /*if(empty($params['PurchaseSuggestHistorySearch']['end_time']))
        {
            $start_time = date('Y-m-d 00:00:00', time()-86400);
            $end_time   = date('Y-m-d 23:59:59', time()-86400);
            $query->andFilterWhere(['between', 'created_at', $start_time, $end_time]);
        } else {
            $query->andFilterWhere(['between', 'created_at', $params['PurchaseSuggestHistorySearch']['start_time'], $params['PurchaseSuggestHistorySearch']['end_time']]);
        }*/
        if (!empty($this->start_time)) {
            $this->start_time = $this->start_time . ' 00:00:00';
            $this->end_time = $this->end_time . ' 23:59:59';
            $query->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);
        } else {
            $query->andFilterWhere(['between', 'created_at', date('Y-m-d H:i:s',strtotime("last month")), date('Y-m-d H:i:s',time())]);
        }

        if($this->left==1)
        {
            $query->andFilterWhere(['<','left_stock',0]);
        } elseif($this->left==2){
            $query->andFilterWhere(['>=','left_stock',0]);
        }else{

        }

        if(isset($this->product_status))
        {
            $query->andFilterWhere(['=','product_status',$this->product_status]);
        } else {
            $query->andFilterWhere(['=','product_status',4]);
        }
        if($this->amount_1 <= $this->amount_2)
        {
            $query->andFilterWhere(['>', 'qty', $this->amount_1]);
            $query->andFilterWhere(['<=', 'qty', $this->amount_2]);
        }
        //Vhelper::dump($params);
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'qty' => $this->qty,
            'state' => $this->state,
            'price' => $this->price,
            'payment_method' => $this->payment_method,
            'supplier_settlement' => $this->supplier_settlement,
            'created_at' => $this->created_at,
            'product_category_id' => $this->product_category_id,
            'on_way_stock' => $this->on_way_stock,
            'available_stock' => $this->available_stock,
            'stock' => $this->stock,
            //'left_stock' => $this->left_stock,
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
            //->andFilterWhere(['like', 'sku', trim($this->sku)])
            ->andFilterWhere(['sku'=>trim($this->sku)])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['supplier_code'=>$this->supplier_code])
            ->andFilterWhere(['like', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['buyer'=>$this->buyer])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['is_purchase'=>$this->is_purchase])
            ->andFilterWhere(['like', 'creator', $this->creator])
            ->andFilterWhere(['like', 'category_cn_name', $this->category_cn_name])
            ->andFilterWhere(['like', 'type', $this->type]);

       // $a = $query->createCommand()->getRawSql(); Vhelper::dump($a);
        return $dataProvider;
    }

    /**
     * 海外仓采购建议搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
        $query = PurchaseSuggestHistoryMrp::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $this->load($params);
        $query->orderBy('left_stock asc');
        $query->where(['is_purchase'=>'Y']);
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
            ->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'supplier_code', $this->supplier_code])
            ->andFilterWhere(['like', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'buyer', $this->buyer])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'is_purchase', $this->is_purchase])
            ->andFilterWhere(['like', 'creator', $this->creator])
            ->andFilterWhere(['like', 'category_cn_name', $this->category_cn_name])
            ->andFilterWhere(['like', 'type', $this->type]);
        return $dataProvider;
    }
}
