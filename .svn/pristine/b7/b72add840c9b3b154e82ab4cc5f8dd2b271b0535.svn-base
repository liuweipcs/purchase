<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%sku_sales_statistics_total}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $platform_code
 * @property integer $warehouse_id
 * @property string $warehouse_code
 * @property integer $days_sales_3
 * @property integer $days_sales_7
 * @property integer $days_sales_15
 * @property integer $days_sales_30
 * @property integer $days_sales_60
 * @property integer $days_sales_90
 * @property string $statistics_date
 * @property string $create_time
 * @property string $update_time
 * @property integer $is_suggest
 */
class SkuSalesStatisticsTotal extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sku_sales_statistics_total}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku'], 'required'],
            [['warehouse_id', 'days_sales_3', 'days_sales_7', 'days_sales_15', 'days_sales_30', 'days_sales_60', 'days_sales_90', 'is_suggest'], 'integer'],
            [['statistics_date', 'create_time', 'update_time'], 'safe'],
            [['sku'], 'string', 'max' => 30],
            [['sku', 'warehouse_code'], 'unique', 'targetAttribute' => ['sku', 'warehouse_code'], 'message' => 'The combination of 产品SKU and 仓库编码 has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'sku' => Yii::t('app', '产品SKU'),
            'platform_code' => Yii::t('app', '平台code'),
            'warehouse_id' => Yii::t('app', '仓库id'),
            'warehouse_code' => Yii::t('app', '仓库编码'),
            'days_sales_3' => Yii::t('app', 'SKU3天销量'),
            'days_sales_7' => Yii::t('app', 'SKU7天销量'),
            'days_sales_15' => Yii::t('app', 'SKU15天销量'),
            'days_sales_30' => Yii::t('app', 'SKU30天销量'),
            'days_sales_60' => Yii::t('app', 'SKU60天销量'),
            'days_sales_90' => Yii::t('app', 'SKU90天销量'),
            'statistics_date' => Yii::t('app', '统计时间'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'is_suggest' => Yii::t('app', '是否生成采购建议(0没有1有)'),
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
