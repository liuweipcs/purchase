<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%stock_owes}}".
 *
 * @property integer $id
 * @property string $warehouse_code
 * @property string $sku
 * @property integer $left_stock
 * @property integer $status
 * @property string $statistics_date
 */
class StockOwes extends BaseModel
{
    public $product_status;
    public $creater;
    public $default_buyer;
    public $excep_time;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%stock_owes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['left_stock', 'status'], 'integer'],
            [['statistics_date'], 'safe'],
            [['warehouse_code'], 'string', 'max' => 100],
            [['sku'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'warehouse_code' => Yii::t('app', 'Warehouse Code'),
            'sku' => Yii::t('app', 'Sku'),
            'left_stock' => Yii::t('app', 'Left Stock'),
            'status' => Yii::t('app', '状态'),
            'statistics_date' => Yii::t('app', '欠货统计时间'),
        ];
    }

    public function getProductDesc(){
        return $this->hasOne(ProductDescription::className(),['sku'=>'sku'])->where(['language_code'=>'Chinese']);
    }

    public function getStock(){
        return $this->hasOne(Stock::className(),['sku'=>'sku'])->select(['on_way_stock'=>'sum(on_way_stock)','available_stock'=>'sum(available_stock)']);
    }
    //只查询易佰东莞仓库数据
    public function getSzStock(){
        return $this->hasOne(Stock::className(),['sku'=>'sku'])->where(['pur_stock.warehouse_code'=>'SZ_AA'])->select(['on_way_stock'=>'sum(on_way_stock)','available_stock'=>'sum(available_stock)']);
    }

    public function  getBuyer(){
        return $this->hasOne(SupplierBuyer::className(),['supplier_code'=>'supplier_code'])->where(['type'=>1])->via('defaultSupplier');
    }
    public function getDefaultSupplier(){
        return $this->hasOne(ProductProvider::className(),['sku'=>'sku'])->where(['is_supplier'=>1]);
    }
    public function getProduct(){
        return $this->hasOne(Product::className(),['sku'=>'sku']);
    }

    public function getWarehouse(){
        return $this->hasOne(Warehouse::className(),['warehouse_code'=>'warehouse_code']);
    }
    public function getSkuOutofstockStatisitics(){
        return $this->hasMany(SkuOutofstockStatisitics::className(),['sku'=>'sku']);
    }
}
