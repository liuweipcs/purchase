<?php
namespace app\models;

use app\models\base\BaseModel;

use Yii;
use app\config\Vhelper;
use app\services\PurchaseSuggestQuantityServices;
use app\services\SupplierServices;
use yii\db\Exception;

/**
 * This is the model class for table "{{%purchase_suggest_mrp}}".
 *
 * @property string $id
 * @property string $sku
 * @property string $name
 * @property string $warehouse_code
 * @property string $warehouse_name
 * @property string $supplier_code
 * @property string $supplier_name
 * @property string $buyer
 * @property string $buyer_id
 * @property integer $replenish_type
 * @property string $qty
 * @property string $price
 * @property string $currency
 * @property integer $payment_method
 * @property integer $supplier_settlement
 * @property string $ship_method
 * @property string $is_purchase
 * @property string $created_at
 * @property string $creator
 * @property string $product_category_id
 * @property string $category_cn_name
 * @property string $on_way_stock
 * @property string $available_stock
 * @property string $stock
 * @property integer $left_stock
 * @property string $days_sales_3
 * @property string $days_sales_7
 * @property string $days_sales_15
 * @property string $days_sales_30
 * @property string $sales_avg
 * @property string $type
 * @property integer $safe_delivery
 * @property string $product_img
 * @property string $transit_code
 * @property integer $purchase_type
 * @property string $demand_number
 * @property integer $state
 * @property double $weighted_3
 * @property double $weighted_7
 * @property double $weighted_15
 * @property double $weighted_30
 * @property double $weighted_60
 * @property double $weighted_90
 * @property integer $product_status
 * @property integer $lack_total
 * @property integer $qty_13
 * @property integer $is_change
 * @property integer $untreated_time
 */
class PurchaseSuggestMrp extends BaseModel
{
    public $num_qty;//建议采购量
    public $num_sku;//建议采购SKU数
    public $money;//预计采购金额
    public $left;
    public $page_size;
    public $xcx_pur_number;
    public $create_time;
    public $sales_import;
    public $product_cost;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_suggest_mrp}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'name', 'warehouse_code', 'warehouse_name', 'supplier_code', 'supplier_name', 'buyer', 'buyer_id', 'currency', 'payment_method', 'supplier_settlement', 'ship_method', 'created_at', 'creator', 'product_category_id', 'category_cn_name', 'type'], 'required'],
            [['buyer_id', 'replenish_type', 'qty', 'payment_method', 'supplier_settlement', 'product_category_id', 'on_way_stock', 'available_stock', 'stock', 'left_stock', 'days_sales_3', 'days_sales_7', 'days_sales_15', 'days_sales_30', 'safe_delivery', 'purchase_type', 'state', 'product_status', 'lack_total', 'qty_13', 'is_change', 'untreated_time'], 'integer'],
            [['price', 'sales_avg', 'weighted_3', 'weighted_7', 'weighted_15', 'weighted_30', 'weighted_60', 'weighted_90'], 'number'],
            [['is_purchase', 'type'], 'string'],
            [['created_at'], 'safe'],
            [['sku', 'transit_code'], 'string', 'max' => 200],
            [['name'], 'string', 'max' => 300],
            [['warehouse_code', 'supplier_code', 'buyer', 'ship_method'], 'string', 'max' => 30],
            [['warehouse_name'], 'string', 'max' => 50],
            [['supplier_name', 'category_cn_name', 'demand_number'], 'string', 'max' => 100],
            [['currency', 'creator'], 'string', 'max' => 20],
            [['product_img'], 'string', 'max' => 2000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => '产品SKU',
            'name' => '产品名称',
            'warehouse_code' => '仓库编码',
            'warehouse_name' => '仓库名称',
            'supplier_code' => '供应商编码',
            'supplier_name' => '供应商名称',
            'buyer' => '采购员',
            'buyer_id' => '采购员ID',
            'replenish_type' => '补货类型:1缺货入库,2警报入库,3特采入库,4正常入库,5样品采购入库,6备货采购,7试销采购',
            'qty' => '建议数据量',
            'price' => '单价',
            'currency' => '币种',
            'payment_method' => '支付方式',
            'supplier_settlement' => '供应商结算方式',
            'ship_method' => '运输方式:1自提，2快递，3物流，4送货',
            'is_purchase' => '是否生成采购建议',
            'created_at' => '创建时间',
            'creator' => '创建人',
            'product_category_id' => '分类ID',
            'category_cn_name' => '分类中文名',
            'on_way_stock' => '在途数量',
            'available_stock' => '可用数量',
            'stock' => '实际数量',
            'left_stock' => '欠货数量',
            'days_sales_3' => 'SKU3天销量',
            'days_sales_7' => 'SKU7天销量',
            'days_sales_15' => 'SKU15天销量',
            'days_sales_30' => 'SKU30天销量',
            'sales_avg' => '平均销量',
            'type' => '销量走势',
            'safe_delivery' => '安全交期',
            'product_img' => 'sku图片',
            'transit_code' => '中转仓',
            'purchase_type' => '采购类型(1国,2海,3FBA)',
            'demand_number' => '需求单号',
            'state' => '处理状态',
            'weighted_3' => '三天加权',
            'weighted_7' => '7天加权',
            'weighted_15' => '15天加权',
            'weighted_30' => '30天加权',
            'weighted_60' => '60天加权',
            'weighted_90' => '90天加权',
            'product_status' => '产品状态',
            'lack_total' => '在途库存+可用库存+欠货库存',
            'qty_13' => '安全天数减去两天的数量',
            'is_change' => '是否变更采购员0否1是',
            'untreated_time' => '未处理时间',
        ];
    }



    /**
     * @desc 根据sku获取sku绑定供应商编码及名称,支付方式，结算方式
     * @param $data
     * @return array
     * return ['is_success'=>true,'data'=>$data];
     */
    public static function getProductSupplier($data){
        $defaultSupplier = ProductProvider::find()->alias('t')
            ->select('t.supplier_code,s.supplier_name,s.payment_method,s.supplier_settlement')
            ->leftJoin(Supplier::tableName().' s','t.supplier_code=s.supplier_code')
            ->where(['t.is_supplier'=>1,'t.sku'=>$data['sku']])
            ->asArray()->one();
        if(empty($defaultSupplier)){
            return ['is_success'=>false,'data'=>[]];
        }
        $data['supplier_code'] = $defaultSupplier['supplier_code'];
        $data['supplier_name'] = $defaultSupplier['supplier_name'];
        $data['payment_method'] = $defaultSupplier['payment_method'];
        $data['supplier_settlement'] = $defaultSupplier['supplier_settlement'];
        return ['is_success'=>true,'data'=>$data];
    }

    /**
     * @desc 根据sku获取绑定采购员
     * @param $data
     * @return array
     */
    public static function getProductBuyer($data){
        if($data['warehouse_code']=='FBA_SZ_AA'){
            $supplierProductLine = SupplierProductLine::find()->select('first_product_line')->where(['supplier_code'=>$data['supplier_code']])->scalar();
            $buyer=PurchaseCategoryBind::getBuyer($supplierProductLine);
            $buyer= $buyer ? $buyer : 'admin';
        }else{
            $buyer_s =SupplierBuyer::getBuyer($data['supplier_code'],1);
            $buyer = !empty($buyer_s)?$buyer_s:'王开伟';
        }
        $data['buyer'] = $buyer;
        $data['buyer_id'] = User::find()->select('id')->where(['username'=>$buyer])->scalar();
        return $data;
    }

    /**
     * @desc 根据sku获取sku分类id分类名称，图片地址
     * @param $data
     * @return mixed
     */
    public static function getProductInfo($data){
        $productInfo = Product::find()
                    ->select('t.product_category_id,pc.category_cn_name,t.uploadimgs,pd.title')
                    ->alias('t')
                    ->leftJoin(ProductCategory::tableName().' pc','t.product_category_id=pc.id')
                    ->leftJoin(ProductDescription::tableName().' pd','t.sku=pd.sku')
                    ->where(['t.sku'=>$data['sku']])
                    ->andWhere(['pd.language_code'=>'Chinese'])
                    ->asArray()->one();
        $data['product_img'] = isset($productInfo['product_category_id']) ? $productInfo['product_category_id'] :'';
        $data['category_cn_name'] = isset($productInfo['category_cn_name']) ? $productInfo['category_cn_name'] :'';
        $data['category_cn_name'] = isset($productInfo['category_cn_name']) ? $productInfo['category_cn_name'] :'';
        $data['name'] = isset($productInfo['title']) ? $productInfo['title'] :'';
        return $data;
    }

    /**@desc 根据已有数据新增或者更新一条采购建议
     * @param $model
     * @param $data
     * @return mixed
     */
    public static function saveOne($model,$data){
        $model->sku = isset($data['sku']) ? $data['sku'] : '';
        $model->warehouse_code = isset($data['warehouse_code']) ? $data['warehouse_code'] : '';
        $model->warehouse_name = isset($data['warehouse_name'])&&!empty($data['warehouse_name']) ? $data['warehouse_name'] : '无仓库名';
        $model->supplier_code = isset($data['supplier_code']) ? $data['supplier_code'] : '';
        $model->supplier_name = isset($data['supplier_name']) ? $data['supplier_name'] : '';
        $model->name = isset($data['name']) ? $data['name'] : '无产品名称';
        $model->buyer = isset($data['buyer'])&&!empty($data['buyer']) ? $data['buyer'] : 'admin';
        $model->buyer_id = isset($data['buyer_id'])&&!empty($data['buyer_id']) ? $data['buyer_id'] : 1;
        $model->replenish_type = isset($data['replenish_type']) ? $data['replenish_type'] : 6;
        $model->qty = isset($data['qty']) ? $data['qty'] : '';
        $model->price = isset($data['price']) ? $data['price'] : '';
        $model->currency = isset($data['currency']) ? $data['currency'] : 'RMB';
        $model->payment_method = isset($data['payment_method']) ? $data['payment_method'] : '';
        $model->supplier_settlement = isset($data['supplier_settlement']) ? $data['supplier_settlement'] : '';
        $model->ship_method = isset($data['ship_method']) ? $data['ship_method'] : '2';
        $model->is_purchase = isset($data['is_purchase']) ? $data['is_purchase'] : 'Y';
        $model->created_at  = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s',time());
        $model->creator = isset($data['creator']) ? $data['creator'] : 'admin';
        $model->product_category_id = isset($data['product_category_id']) ? $data['product_category_id'] : 0;
        $model->category_cn_name = isset($data['category_cn_name']) ? $data['category_cn_name'] : '';
        $model->on_way_stock = isset($data['on_way_stock']) ? $data['on_way_stock'] : '';
        $model->stock = isset($data['stock']) ? $data['stock'] : '';
        $model->left_stock = isset($data['left_stock']) ? $data['left_stock'] : '';
        $model->days_sales_3 = isset($data['days_sales_3']) ? $data['days_sales_3'] : '';
        $model->days_sales_7 = isset($data['days_sales_7']) ? $data['days_sales_7'] : '';
        $model->days_sales_15 = isset($data['days_sales_15']) ? $data['days_sales_15'] : '';
        $model->days_sales_30 = isset($data['days_sales_30']) ? $data['days_sales_30'] : '';
        $model->sales_avg = isset($data['sales_avg']) ? $data['sales_avg'] : '';
        $model->type = isset($data['type']) ? $data['type'] : 'last_up';
        $model->safe_delivery = isset($data['safe_delivery']) ? $data['safe_delivery'] : '';
        $model->product_img = isset($data['product_img']) ? $data['product_img'] : '';
        $model->transit_code = isset($data['transit_code']) ? $data['transit_code'] : '';
        $model->purchase_type = in_array($data['warehouse_code'],['SZ_AA','ZDXNC','HW_XNC']) ? 1 : ($data['warehouse_code']=='FBA_SZ_AA' ? 3 : 1);
        $model->demand_number = isset($data['demand_number']) ? $data['demand_number'] : '';
        $model->state = isset($data['state']) ? $data['state'] : 0;
        $model->weighted_3 = isset($data['weighted_3']) ? $data['weighted_3'] : '';
        $model->weighted_7 = isset($data['weighted_7']) ? $data['weighted_7'] : '';
        $model->weighted_15 = isset($data['weighted_15']) ? $data['weighted_15'] : '';
        $model->weighted_30 = isset($data['weighted_30']) ? $data['weighted_30'] : '';
        $model->weighted_60 = isset($data['weighted_60']) ? $data['weighted_60'] : '';
        $model->weighted_90 = isset($data['weighted_90']) ? $data['weighted_90'] : '';
        $model->product_status = isset($data['product_status']) ? $data['product_status'] : '';
        $model->lack_total = isset($data['lack_total']) ? $data['lack_total'] : '';
        $model->qty_13 = isset($data['qty_13']) ? $data['qty_13'] : '';
        $model->is_change = isset($data['is_change']) ? $data['is_change'] : '';
        $model->untreated_time = isset($data['untreated_time']) ? $data['untreated_time'] : '';
        $model->new_price_limit = isset($data['new_price_limit']) ? $data['new_price_limit'] : '';
        $model->is_new = 1;
        $model->new_stock_hold = isset($data['new_stock_hold']) ? $data['new_stock_hold'] : '';
        return $model->save();
    }


    /**
     * @desc 更新待生成采购建议为已完成
     * @param $sku
     * @param $warehouseCode
     */
    public static function updateStatus($sku,$warehouseCode){
        SkuSalesStatisticsTotalMrp::updateAll(['is_suggest'=>1],['sku'=>$sku,'warehouse_code'=>$warehouseCode]);
    }


    /**
     * 返回采购建议的统计状态数量
     * @param $status
     * @return int|string
     */
    public static function  getStatus($status,$type=null)
    {
        $start_time = date('Y-m-d 00:00:00');
        $end_time   = date('Y-m-d 23:59:59');
        $warehouse_code = PurchaseSuggestQuantityServices::getSuggestWarehouseCode();
        if(empty($type))
        {
            $datas= self::find()->select('id')->where(['product_status'=>$status,'purchase_type'=>1,'state'=>[0,1,2]])->andWhere(['>','qty',0])->andWhere(['in','warehouse_code',$warehouse_code])->andWhere(['between', 'created_at', $start_time, $end_time])->asArray()->all();
            return !empty($datas) ? count(array_column($datas,'id')) : 0;
//            return self::find()->where(['product_status'=>$status,'purchase_type'=>1,'state'=>[0,1,2]])->andWhere(['>','qty',0])->andWhere(['in','warehouse_code',['DG','SZ_AA','xnc','ZDXNC','CDxuni','ZMXNC_WM','ZMXNC_EB','HW_XNC','LAZADA-XNC']])->andWhere(['between', 'created_at', $start_time, $end_time])->groupBy('buyer_id')->count();
        } else{
            $datas =  self::find()->select('id')->where(['purchase_type'=>1,'state'=>[0,1,2]])->andWhere(['in','warehouse_code',$warehouse_code])->andWhere(['>','qty',0])->andWhere(['between', 'created_at', $start_time, $end_time])->asArray()->all();
            $ignoresku = self::find()->select('id')->where(['purchase_type'=>1,'state'=>[0,1,2]])->andWhere(['sku'=>'XJFH0000'])->andWhere(['in','warehouse_code',$warehouse_code])->andWhere(['>','qty',0])->andWhere(['between', 'created_at', $start_time, $end_time])->asArray()->all();
            return !empty($datas) ? count(array_column($datas,'id'))-count($ignoresku) : 0;
            //            return self::find()->where(['purchase_type'=>1,'state'=>[0,1,2]])->andWhere(['in','product_status',['0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','100']])->andWhere(['in','warehouse_code',['DG','SZ_AA','xnc','ZDXNC','CDxuni','ZMXNC_WM','ZMXNC_EB','HW_XNC','LAZADA-XNC']])->andWhere(['>','qty',0])->andWhere(['between', 'created_at', $start_time, $end_time])->groupBy('buyer_id')->count();
        }

    }


    // 获取可批量生成采购单的采购建议数量
    public static function getLeadSuggests($ids=null)
    {
        $user_id = Yii::$app->user->identity->id;
        $time_from = date('Y-m-d', time()).' 00:00:00';
        $time_to = date('Y-m-d', time()).' 24:00:00';
        $query = self::find();
        $field = [
            'id',
            'warehouse_code',
            'supplier_code',
            'supplier_name',
            'purchase_type',
            'replenish_type',
            'ship_method',
            'currency',
            'payment_method',
            'supplier_settlement',
            'transit_code',
            'GROUP_CONCAT(id) as ids'
        ];
        $condition = [
            //'is_purchase'=>'Y',
            'state' => 0, // 处理状态 未处理
            'buyer_id' => $user_id, // 采购员
            //        'product_status' => 4, // 产品状态
            'purchase_type' => 1, // 采购类型 国内
//            'warehouse_code' => 'SZ_AA'
        ];
        //没勾选加上产品状态
        if(empty($ids)){
            $condition['product_status'] = 4;
        }
        $query->select($field)
            ->from('pur_purchase_suggest_mrp')
            ->where($condition)
            ->andWhere(['in','warehouse_code',['SZ_AA','ZDXNC','HW_XNC','LAZADA-XNC']])
            ->andWhere(['>', 'qty', 0])
            ->andWhere(['>', 'created_at', $time_from])
            ->andWhere(['<', 'created_at', $time_to]);
        if(!empty($ids)){
            $query->andWhere(['in', 'id', $ids]);
            $query->groupBy(['supplier_code','warehouse_code']);
        }else{
            $query->groupBy(['supplier_code','warehouse_code']);
        }
        return $query;
    }

    /**
     * 返回采购建议的统计状态数量
     * @return int|string
     */
    public static function getStatusStatistics()
    {
        $statusStatistics = [];
        $start_time = date('Y-m-d 00:00:00');
        $end_time   = date('Y-m-d 23:59:59');
        $warehouse_code = PurchaseSuggestQuantityServices::getSuggestWarehouseCode();
        $query = self::find()->select("product_status, count(1) as count")
            ->where(['purchase_type'=>1,'state'=>[0,1,2]])
            ->andWhere(['>','qty',0])
            ->andWhere(['in','warehouse_code',$warehouse_code])
            ->andWhere(['between', 'created_at', $start_time, $end_time])
            ->groupBy("product_status");
        /**
         * @var $query \yii\db\ActiveQuery
         */
        $sql = $query->createCommand()->getRawSql();
        $sql .= ' ORDER BY NULL';
        $query->sql = $sql;
        $datas = $query->asArray()->all();
        $total = 0;
        if (!empty($datas))
            foreach ($datas as $row)
            {
                $count = (int)$row['count'];
                $statusStatistics[$row['product_status']] = $count;
                $total += $row['count'];
            }
        $statusStatistics['total'] = $total;
        return $statusStatistics;
    }

    /**
     * 关联未处理备注一对一
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseSuggestNote()
    {
        return $this->hasOne(PurchaseSuggestNote::className(),['sku'=>'sku','warehouse_code'=>'warehouse_code'])
            ->where(['status'=>1])
            ->orderBy('id DESC');
    }
    /**
     * 关联采购单详情表一对多
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItems::className(),['sku'=>'sku']);
    }

    /**
     * 关联采购单主表一对多（通过中间表：采购单详情表）
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseOrder()
    {
        return $this->hasMany(PurchaseOrder::className(),['pur_number'=>'pur_number'])->via('purchaseOrderItems');
    }

    /**
     * 关联PurchaseHistory表一对多
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseHistory()
    {
        return $this->hasMany(PurchaseHistory::className(),['sku'=>'sku']);
    }

    /**
     * 关联Product表一对一
     * @return \yii\db\ActiveQuery
     */
    public function getProduct(){
        return $this->hasOne(Product::className(),['sku'=>'sku']);
    }


}
