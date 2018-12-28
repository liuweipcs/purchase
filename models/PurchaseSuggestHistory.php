<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/30
 * Time: 10:51
 */
namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_purchase_suggest_history".
 *
 * @property string $id
 * @property string $warehouse_code
 * @property string $warehouse_name
 * @property string $sku
 * @property string $name
 * @property string $supplier_code
 * @property string $supplier_name
 * @property string $buyer
 * @property string $replenish_type
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
 */
class PurchaseSuggestHistory extends BaseModel
{
    public $num_qty;//建议采购量
    public $num_sku;//建议采购SKU数
    public $money;//预计采购金额
    public $left;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_suggest_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_code', 'warehouse_name', 'sku', 'name', 'supplier_code', 'supplier_name', 'buyer', 'replenish_type', 'currency', 'payment_method', 'supplier_settlement', 'ship_method', 'created_at', 'creator', 'product_category_id', 'category_cn_name', 'type','buyer_id'], 'required'],
            [['qty', 'payment_method', 'supplier_settlement', 'product_category_id', 'on_way_stock', 'available_stock', 'stock', 'left_stock', 'days_sales_3', 'days_sales_7', 'days_sales_15', 'days_sales_30', 'sales_avg', 'ship_method', 'replenish_type','buyer_id','state'], 'integer'],
            [['price'], 'number'],
            [['is_purchase', 'type'], 'string'],
            [['created_at','transit_code','purchase_type','demand_number','product_status','create_time'], 'safe'],
            [['warehouse_code', 'sku', 'supplier_code', 'buyer'], 'string', 'max' => 30],
            [['warehouse_name', 'category_cn_name'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 300],
            [['supplier_name'], 'string', 'max' => 100],
            [['currency', 'creator'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'warehouse_code' => 'Warehouse Code',
            'warehouse_name' => 'Warehouse Name',
            'sku' => 'Sku',
            'name' => 'Name',
            'supplier_code' => 'Supplier Code',
            'supplier_name' => 'Supplier Name',
            'buyer' => 'Buyer',
            'replenish_type' => '补货类型',
            'qty' => 'Qty',
            'price' => 'Price',
            'currency' => 'Currency',
            'payment_method' => 'Payment Method',
            'supplier_settlement' => 'Supplier Settlement',
            'ship_method' => '运输方式',
            'is_purchase' => 'Is Purchase',
            'created_at' => 'Created At',
            'creator' => 'Creator',
            'product_category_id' => 'Product Category ID',
            'category_cn_name' => 'Category Cn Name',
            'on_way_stock' => 'On Way Stock',
            'available_stock' => 'Available Stock',
            'stock' => 'Stock',
            'left_stock' => 'Left Stock',
            'days_sales_3' => 'Days Sales 3',
            'days_sales_7' => 'Days Sales 7',
            'days_sales_15' => 'Days Sales 15',
            'days_sales_30' => 'Days Sales 30',
            'sales_avg' => 'Sales Avg',
            'type' => 'Type',
            'buyer_id' => 'Buyer ID',
            'process_qty'=> '已生成采购单数量',
            'state'=> '处理状态',
            'create_time'=> '数据拉取时间'
        ];
    }
    /**
     * 通过code获取供应商名
     * @param $code
     * @return false|null|string
     */
    public static function  GetCode($code)
    {
        return self::find()->select('supplier_name')->where(['supplier_code'=>$code])->scalar();
    }

    /**
     * 关联未处理备注一对一
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseSuggestNote()
    {
        return $this->hasOne(PurchaseSuggestNote::className(),['sku'=>'sku','warehouse_code'=>'warehouse_code']);
    }

    /**
     * 返回采购建议的统计状态数量
     * @param $status
     * @return int|string
     */
    public static function  getStatus($status,$type=null)
    {
        if(empty($type))
        {
            return self::find()->where(['product_status'=>$status,'is_purchase'=>'Y','purchase_type'=>1])->andWhere(['>','qty',0])->andWhere(['in','warehouse_code',['DG','SZ_AA','xnc','ZDXNC','CDxuni','ZMXNC_WM','ZMXNC_EB','HW_XNC']])->count();
        } else{
            return self::find()->where(['is_purchase'=>'Y','purchase_type'=>1])->andWhere(['NOT', ['product_status' => null]])->andWhere(['in','warehouse_code',['DG','SZ_AA','xnc','ZDXNC','CDxuni','ZMXNC_WM','ZMXNC_EB','HW_XNC']])->andWhere(['>','qty',0])->count();
        }

    }
    /**
     * 采购审核通过后，修改采购建议处理状态为：已完成
     * @param $data
     * @return bool
     */
    public function updateState($data)
    {
        foreach ($data as $v)
        {
            $model = self::findOne(['sku' =>$v['sku'],'supplier_code'=>$v['supplier_code'],'state'=>1]);
            if (empty($model)) {
                //采购建议
                $model_suggest = new PurchaseSuggest();
                $model_suggest->updateState($data);
                $status = false;
            } else {
                $model->state = 2;

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_suggest_history', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                $status = $model->save(false);
            }
        }
        return $status;
    }

}
