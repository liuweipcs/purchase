<?php
namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use app\services\PurchaseSuggestQuantityServices;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseSuggest;
use app\models\PurchaseSuggestMrp;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseArrayHelper;

/**
 * PurchaseSuggestSearch represents the model behind the search form about `app\models\PurchaseSuggestMrp`.
 */
class PurchaseSuggestMrpSearch extends PurchaseSuggestMrp
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
        $query = PurchaseSuggestMrp::find();
        $query->select('pur_purchase_suggest_mrp.*,pur_product.create_time,pur_product.product_cost');
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

        } else{
            $query->orderBy('pur_purchase_suggest_mrp.left_stock asc');
        }

        $warehouse_code = PurchaseSuggestQuantityServices::getSuggestWarehouseCode();
        $query->andWhere(['in','pur_purchase_suggest_mrp.warehouse_code',$warehouse_code]);
        $query->andWhere(['>','pur_purchase_suggest_mrp.qty',0]);
        $query->andWhere(['!=','pur_purchase_suggest_mrp.sku','XJFH0000']);
        $query->andWhere(['in','pur_purchase_suggest_mrp.purchase_type',1]);

        $query->leftJoin('pur_product','pur_purchase_suggest_mrp.sku=pur_product.sku');

        //$user = ['史家松','张涛涛','王开伟','吴洋'];
        $puid=PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
        $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
        if(in_array(Yii::$app->user->id,$ids))
        {

        } else {
            $query->andWhere(['in', 'pur_purchase_suggest_mrp.buyer_id',Yii::$app->user->id]);
        }
        if (!$this->validate()) {
            return $dataProvider;
        }

        if(!empty($params)){
            if(!empty($params['PurchaseSuggestMrpSearch']['buyer_id'])){
                if(is_numeric($this->buyer_id)){
                    $query->andFilterWhere(['pur_purchase_suggest_mrp.buyer_id' => $this->buyer_id,]);
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

                    $query->andFilterWhere(['in', 'pur_purchase_suggest_mrp.buyer_id', array_values(BaseArrayHelper::map($puid,'pur_user_id','pur_user_id'))]);
            }
            }
            $query->andFilterWhere(['between', 'pur_purchase_suggest_mrp.created_at', $this->start_time, $this->end_time]);
        }
        //判断创建时间
        if(empty($this->start_time) || empty($this->end_time))
        {
            $start_time = date('Y-m-d 00:00:00');
            $end_time   = date('Y-m-d 23:59:59');
            $query->andFilterWhere(['between', 'pur_purchase_suggest_mrp.created_at', $start_time, $end_time]);
        }

        $query->leftJoin(ProductSourceStatus::tableName().' pst','pur_purchase_suggest_mrp.sku=pst.sku');
        //货源状态搜索
        if(empty($this->sourcing_status)){// 为空则加载 货源为正常的
            $query->andWhere(['or',['pst.sourcing_status'=>1,'pst.status'=>1],['pst.sourcing_status'=>null]]);
        }else{
            if($this->sourcing_status != 'all'){// 等于 all 的不设置查询条件，否则根据状态查询
                if($this->sourcing_status==1){
                    $query->andWhere(['or',['pst.sourcing_status'=>$this->sourcing_status,'pst.status'=>1],['pst.sourcing_status'=>null]]);
                }else{
                    $query->andFilterWhere(['pst.sourcing_status'=>$this->sourcing_status,'pst.status'=>1]);
                }
            }
        }
        //销售导入数据筛选
        if(!empty($params['PurchaseSuggestMrpSearch']['sales_import']) && $params['PurchaseSuggestMrpSearch']['sales_import'] == 1){
            $quantity =  PurchaseSuggestQuantity::find()
                ->select('pur_purchase_suggest_quantity.sku,pur_purchase_suggest_quantity.purchase_warehouse')
                ->Where(['between','pur_purchase_suggest_quantity.create_time',date('Y-m-d 00:00:00',time()-86400),date('Y-m-d 23:59:59',time()-86400)])->asArray()->all();
            if($quantity && count($quantity)>0){
                $condition = ['or'];
                foreach ($quantity as $key => $val){
                    $list = [];
                    $list[] = 'and';
                    $list[] = "pur_purchase_suggest_mrp.sku= '{$val['sku']}'";
                    $list[] = "pur_purchase_suggest_mrp.warehouse_code= '{$val['purchase_warehouse']}'";
                    array_push($condition,$list);
                }
                if(count($condition) > 1){
                    $query->andWhere($condition);
                }
            }
        }

        if(isset($this->left) AND $this->left==1)
        {
            $query->andFilterWhere(['<','pur_purchase_suggest_mrp.left_stock',0]);
        } elseif(isset($this->left) AND  $this->left==2){
            $query->andFilterWhere(['>=','pur_purchase_suggest_mrp.left_stock',0]);
        }else{

        }
        if($this->amount_1 <= $this->amount_2)
        {
            $query->andFilterWhere(['>', 'pur_purchase_suggest_mrp.qty', $this->amount_1]);
            $query->andFilterWhere(['<=', 'pur_purchase_suggest_mrp.qty', $this->amount_2]);
        }
        $query->andFilterWhere([
            'pur_purchase_suggest_mrp.id' => $this->id,
            'pur_purchase_suggest_mrp.state' => !empty($this->state) ? ($this->state!='all'? $this->state : [0,1,2]):'0' ,
            'pur_purchase_suggest_mrp.price' => $this->price,
            'pur_purchase_suggest_mrp.payment_method' => $this->payment_method,
            'pur_purchase_suggest_mrp.supplier_settlement' => $this->supplier_settlement,
            'pur_purchase_suggest_mrp.created_at' => $this->created_at,
            'pur_purchase_suggest_mrp.product_category_id' => $this->product_category_id,
            'pur_purchase_suggest_mrp.on_way_stock' => $this->on_way_stock,
            'pur_purchase_suggest_mrp.available_stock' => $this->available_stock,
            'pur_purchase_suggest_mrp.stock' => $this->stock,
            'pur_purchase_suggest_mrp.days_sales_3' => $this->days_sales_3,
            'pur_purchase_suggest_mrp.days_sales_7' => $this->days_sales_7,
            'pur_purchase_suggest_mrp.days_sales_15' => $this->days_sales_15,
            'pur_purchase_suggest_mrp.days_sales_30' => $this->days_sales_30,
            'pur_purchase_suggest_mrp.sales_avg' => $this->sales_avg,
            'pur_purchase_suggest_mrp.replenish_type' => $this->replenish_type,
            'pur_purchase_suggest_mrp.ship_method' => $this->ship_method,
        ]);
        if (!empty($this->product_status)) {
            $query->andFilterWhere([
                'pur_purchase_suggest_mrp.product_status' => ($this->product_status != 'all') ? $this->product_status : null,
            ]);
        } else {
            $query->andWhere(['not in','pur_purchase_suggest_mrp.product_status',['0','5','6','7','100']]);
        }
        $query->andFilterWhere(['like', 'pur_purchase_suggest_mrp.warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['like', 'pur_purchase_suggest_mrp.warehouse_name', $this->warehouse_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest_mrp.sku', trim($this->sku)])
            ->andFilterWhere(['like', 'pur_purchase_suggest_mrp.name', $this->name])
            ->andFilterWhere(['like', 'pur_purchase_suggest_mrp.supplier_code', $this->supplier_code])
            ->andFilterWhere(['like', 'pur_purchase_suggest_mrp.supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest_mrp.buyer', $this->buyer])
            ->andFilterWhere(['like', 'pur_purchase_suggest_mrp.currency', $this->currency])
            ->andFilterWhere(['like', 'pur_purchase_suggest_mrp.is_purchase', $this->is_purchase])
            ->andFilterWhere(['like', 'pur_purchase_suggest_mrp.creator', $this->creator])
            ->andFilterWhere(['like', 'pur_purchase_suggest_mrp.category_cn_name', $this->category_cn_name])
            ->andFilterWhere(['like', 'pur_purchase_suggest_mrp.type', $this->type]);

        \Yii::$app->session->set('PurchaseSuggestMrpSearchData', $params);
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }


}
