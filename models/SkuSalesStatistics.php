<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
/**
 * This is the model class for table "pur_sku_sales_statistics".
 *
 * @property integer $id
 * @property string $sku
 * @property string $warehouse_code
 * @property string $days_sales_3
 * @property string $days_sales_7
 * @property string $days_sales_15
 * @property string $days_sales_30
 * @property string $days_sales_60
 * @property string $days_sales_90
 * @property string $statistics_date
 */
class SkuSalesStatistics extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sku_sales_statistics';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'warehouse_code'], 'required'],
            [['days_sales_3', 'days_sales_7', 'days_sales_15', 'days_sales_30', 'days_sales_60', 'days_sales_90'], 'integer'],
            [['statistics_date'], 'safe'],
            [['sku', 'warehouse_code'], 'string', 'max' => 30],
            [['sku', 'warehouse_code'], 'unique', 'targetAttribute' => ['sku', 'warehouse_code'], 'message' => 'The combination of Sku and Warehouse Code has already been taken.'],
        ];
    }

        /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'warehouse_code' => '仓库编码',
            'days_sales_3' => '3天销量',
            'days_sales_7' => '7天销量',
            'days_sales_15' => '15天销量',
            'days_sales_30' => '30天销量',
            'days_sales_60' => '60天销量',
            'days_sales_90' => '90天销量',
            'statistics_date' => '统计时间',
        ];
    }
    /**
     * @desc 和仓库表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 11:42:11
     */
    public function getWarehouse(){
        return $this->hasOne(Warehouse::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和仓库补货策略最小备货表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 13:56:11
     */
    public function getWarehouseMin(){
        return $this->hasOne(WarehouseMin::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和仓库补货策略-采购系数表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 14:21:11
     */
    public function getWarehousePurchaseTactics(){
        return $this->hasMany(WarehousePurchaseTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和库补货策略-客单量系数表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 15:22:11
     */
    public function getWarehouseQtyTactics(){
        return $this->hasMany(WarehouseQtyTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和仓库补货策略-销售系数表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 15:23:11
     */
    public function getWarehouseSalesTactics(){
        return $this->hasMany(WarehouseSalesTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和加权系数-基础数据表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 15:37:11
     */
    public function getBasicTactics(){
        return $this->hasMany(BasicTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和仓库补货策略最小备货表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-11 10:25:11
     */
    public function getProduct(){
        return $this->hasOne(Product::className(), ['sku' => 'sku']);
    }
    /**
     * @desc 根据SKU到描述表里面获取产品名称。
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-11 10:25:11
     */
    public function getProductChName(){
        return $this->hasOne(ProductDescription::className(), ['sku' => 'sku']);
    }
    /**
     * @desc 根据SKU到描述表里面获取产品名称。
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-11 10:25:11
     */
    public function getProductDefSupplier(){
        return $this->hasOne(ProductSupplier::className(), ['sku' => 'sku']);
    }
    /**
     *库存综合查询表
     * @return $this
     */
    public function getStock()
    {
        return $this->hasOne(Stock::className(), ['sku' => 'sku']);
    }
    /**
     * @desc 通过sku和仓库获取销量统计。
     * @return $sku
     * @author ZTT
     * @date 2017-04-11 10:25:11
     */
    public static function  getStatistics($sku)
    {
        return self::find()->where(['sku'=>$sku])->one();
    }
    
}
