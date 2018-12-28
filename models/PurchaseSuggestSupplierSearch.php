<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseSuggest;
use yii\helpers\ArrayHelper;

/**
 * PurchaseSuggestSearch represents the model behind the search form about `app\models\PurchaseSuggest`.
 */
class PurchaseSuggestSupplierSearch extends PurchaseSuggest
{

    public $product_is_new; //判断是否为新品
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','qty', 'product_category_id', 'on_way_stock', 'available_stock', 'stock', 'left_stock', 'days_sales_3', 'days_sales_7', 'days_sales_15', 'days_sales_30'], 'integer'],
            [['warehouse_code', 'warehouse_name', 'sku', 'name', 'supplier_code', 'supplier_name', 'buyer', 'replenish_type', 'ship_method', 'is_purchase', 'created_at', 'creator', 'category_cn_name','payment_method','supplier_settlement','currency','product_is_new'], 'safe'],
            [['price'], 'number'],
        ];
    }

    public function attributes()
    {
        // 添加关联字段到可搜索属性集合
        return array_merge(parent::attributes(), ['pro.product_is_new']);
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
     * 国内供应商
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $field=['warehouse_code','warehouse_name','sku','name','supplier_code','supplier_name','buyer','price','id','count(sku) as num_sku','sum(qty) as num_qty','sum(qty*price) as money','created_at'];
        $query = PurchaseSuggest::find()->select($field)->groupBy(['supplier_code','warehouse_code','buyer']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->andWhere(['>','qty',0]);
        $query->andWhere(['in','warehouse_code',['DG','SZ_AA','xnc','ZDXNC','CDxuni','ZMXNC_WM','ZMXNC_EB','LAZADA-XNC']]);
        $query->andWhere(['in','purchase_type',1]);
        //判断创建时间
        if(empty($this->start_time) || empty($this->end_time))
        {
            $start_time = date('Y-m-d 00:00:00');
            $end_time   = date('Y-m-d 23:59:59');
            $query->andFilterWhere(['between', 'created_at', $start_time, $end_time]);
        }
        $puid=PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
        $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
        if(in_array(Yii::$app->user->id,$ids))
        {

        } else {
            $query->andWhere(['in', 'buyer_id',Yii::$app->user->id]);
        }
        if(!empty($this->product_status))
        {
            $query->andFilterWhere(['=','product_status',$this->product_status]);
        } else {
            $query->andFilterWhere(['=','product_status',4]);
        }
        $this->load($params);

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
        ]);
        $query->andFilterWhere(['like', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['like', 'warehouse_name', $this->warehouse_name])
            ->andFilterWhere(['like', 'sku', trim($this->sku)])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'supplier_code', $this->supplier_code])
            ->andFilterWhere(['like', 'supplier_name', trim($this->supplier_name)])
            ->andFilterWhere(['like', 'buyer', $this->buyer])
            ->andFilterWhere(['like', 'replenish_type', $this->replenish_type])
            ->andFilterWhere(['like', 'ship_method', $this->ship_method])
            ->andFilterWhere(['like', 'is_purchase', $this->is_purchase])
            ->andFilterWhere(['like', 'creator', $this->creator])
            ->andFilterWhere(['like', 'category_cn_name', $this->category_cn_name]);

        return $dataProvider;
    }
    /**
     * 海外供应商
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
        $field=['warehouse_code','warehouse_name','pur_purchase_suggest.sku','name','supplier_code','pur_purchase_suggest.supplier_name','buyer','price','pur_purchase_suggest.id','count(pur_purchase_suggest.sku) as num_sku','sum(qty) as num_qty','sum(qty*price) as money','created_at','state'];
        $query = PurchaseSuggest::find()->select($field)->groupBy(['supplier_code','warehouse_code','buyer']);
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        $query->where(['is_purchase'=>'Y']);
        $query->andWhere(['>','qty',0]);
        $query->andWhere(['in','purchase_type',['4']]);
        $query->andWhere(['not in','warehouse_code',['SZ_AA']]);
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
        $username = Yii::$app->user->identity->username;
        $roles_user = ['张建蓉','范晶晶','周美霞','杨娅妮','何怡','毛丹','胡不为','龙菁','尹小婷','李新'];
        if(!in_array('超级管理员组',array_keys($roles))&& !in_array($username,$roles_user) &&!in_array('采购经理组',array_keys($roles)) &&!in_array('采购组--海外--采购员--查看权限',array_keys($roles))){
            $query->andFilterWhere(['buyer'=>$username]);
        }
        if(!empty($params['sort']))
        {
            //$query->orderBy('supplier_code desc');
        } else{
            $query->orderBy('supplier_code asc');
        }

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        //是否未新品
        if (isset($this->product_is_new))
        {
            $query->leftJoin('pur_product','pur_product.sku = pur_purchase_suggest.sku');
            $query->andFilterWhere(['pur_product.product_is_new'=>$this->product_is_new]);

        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id'                  => $this->id,
            'qty'                 => $this->qty,
            'price'               => $this->price,
            'created_at'          => $this->created_at,
            'pur_purchase_suggest.product_category_id' => $this->product_category_id,
            'on_way_stock'        => $this->on_way_stock,
            'available_stock'     => $this->available_stock,
            'stock'               => $this->stock,
            'left_stock'          => $this->left_stock,
            'days_sales_3'        => $this->days_sales_3,
            'days_sales_7'        => $this->days_sales_7,
            'days_sales_15'       => $this->days_sales_15,
            'days_sales_30'       => $this->days_sales_30,
        ]);

        $query->andFilterWhere(['like', 'warehouse_code', trim($this->warehouse_code)])
            ->andFilterWhere(['like', 'warehouse_name', trim($this->warehouse_name)])
            ->andFilterWhere(['like', 'pur_purchase_suggest.sku', trim($this->sku)])
            ->andFilterWhere(['like', 'name', trim($this->name)])
            ->andFilterWhere(['like', 'supplier_code', $this->supplier_code])
            ->andFilterWhere(['like', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'buyer', $this->buyer])
            ->andFilterWhere(['like', 'replenish_type', $this->replenish_type])
            ->andFilterWhere(['like', 'ship_method', $this->ship_method])
            ->andFilterWhere(['like', 'is_purchase', $this->is_purchase])
            ->andFilterWhere(['like', 'creator', $this->creator])
            ->andFilterWhere(['like', 'category_cn_name', $this->category_cn_name]);


        return $dataProvider;
    }
}
