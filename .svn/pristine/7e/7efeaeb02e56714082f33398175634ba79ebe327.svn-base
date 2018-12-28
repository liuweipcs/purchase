<?php

namespace app\models;

use app\models\base\BaseModel;

use app\services\CommonServices;
use Yii;

/**
 * This is the model class for table "{{%platform_summary}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $platform_number
 * @property string $product_name
 * @property integer $purchase_quantity
 * @property string $purchase_warehouse
 * @property string $transit_warehouse
 * @property integer $is_transit
 * @property string $create_id
 * @property string $create_time
 */
class PlatformSummarys extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%platform_summarys}}';
    }
    public $uploadimgs;
    public $total_purchase_quantity;
    public $start_time;
    public $end_time;
    public $file_execl;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['purchase_quantity', 'is_transit','level_audit_status','purchase_type','group_id'], 'integer'],
            [['create_time','agree_time','transit_number','transport_style'], 'safe'],
            [['sku', 'purchase_warehouse', 'transit_warehouse', 'create_id','agree_user'], 'string', 'max' => 30],
            [['platform_number'], 'string', 'max' => 20],
            [['sku'], 'trim'],
            [['audit_note','sales_note','purchase_note'], 'string', 'max' => 500],
            [['product_name','demand_number'], 'string', 'max' => 100],
            [['create_id'], 'default','value'=>!empty(Yii::$app->user->identity->username)?Yii::$app->user->identity->username:1],
            [['create_time'], 'default','value'=>date('Y-m-d H:i:s',time())],
            [['sku','purchase_quantity','product_name','platform_number','purchase_warehouse','product_category'], 'required'],
        ];
    }
    /**
     * 关联产品名
     * @return $this
     */
    public  function  getDesc()
    {
        return $this->hasOne(ProductDescription::className(), ['sku' => 'sku'])->where(['language_code'=>'Chinese']);
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
     * 关联历史采购单供应商
     * @return \yii\db\ActiveQuery
     */
    public  function  getHistory()
    {
        return $this->hasOne(PurchaseHistory::className(), ['sku' => 'sku'])->orderBy('id desc');
    }
    /**
     * 关联历史采购单供应商
     * @return \yii\db\ActiveQuery
     */
    public  function  getHistoryB()
    {
        return $this->hasOne(PurchaseHistory::className(), ['sku' => 'sku']);
    }
    public  function  getSupplierQuotes()
    {
        return $this->hasOne(SupplierQuotes::className(), ['product_sku'=>'sku']);
    }
    /**
     *库存综合查询表
     * @return $this
     */
    public function getSkusales()
    {
        return $this->hasOne(SkuSalesStatistics::className(), ['sku' => 'sku']);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => Yii::t('app', 'ID'),
            'sku'                => Yii::t('app', 'sku'),
            'platform_number'    => Yii::t('app', '平台'),
            'product_name'       => Yii::t('app', '产品名'),
            'purchase_quantity'  => Yii::t('app', '数量'),
            'purchase_warehouse' => Yii::t('app', '采购仓'),
            'transit_warehouse'  => Yii::t('app', '中转仓'),
            'is_transit'         => Yii::t('app', '中转'),
            'is_purchase'        => Yii::t('app', '是否生成采购计划'),
            'create_id'          => Yii::t('app', '需求人'),
            'create_time'        => Yii::t('app', '需求时间'),
            'level_audit_status' => Yii::t('app', '需求状态'),
            'agree_user'         => Yii::t('app', '同意(驳回)人'),
            'agree_time'         => Yii::t('app', '同意(驳回)时间'),
            'demand_number'      => Yii::t('app', '需求单号'),
            'product_category'   => Yii::t('app', '产品类别'),
            'audit_note'         => Yii::t('app', '备注'),
            'transit_number'     => Yii::t('app', '中转数量'),
            'transport_style'    => Yii::t('app', '运输方式'),
            'sales_note'         => Yii::t('app', '销售备注'),
            'buyer'              => Yii::t('app', '销售备注'),
            'purchase_time'      => Yii::t('app', '采购驳回时间'),
            'purchase_note'      => Yii::t('app', '采购驳回备注'),
            'group_id'      => Yii::t('app', '亚马逊小组'),
        ];
    }

    /**
     * 统计数量
     * @param $sku
     * @param string $filed
     * @return false|null|string
     */
    public static function   getSku($sku,$purchase_warehouse,$transit_warehouse,$filed='*',$purchase_type,$level=1,$product_id=null)
    {
        if($product_id)
        {
            return self::find()->select($filed)
                ->where(['id'=>$product_id,'sku'=>$sku,'is_purchase'=>1,'level_audit_status'=>$level])
                ->andWhere(['purchase_warehouse'=>$purchase_warehouse,'transit_warehouse'=>$transit_warehouse,'purchase_type'=>$purchase_type])
                ->sum('purchase_quantity');
        } else{

            return self::find()->select($filed)
                ->where(['sku'=>$sku,'is_purchase'=>1,'level_audit_status'=>$level])
                ->andWhere(['purchase_warehouse'=>$purchase_warehouse,'transit_warehouse'=>$transit_warehouse,'purchase_type'=>$purchase_type])
                ->sum('purchase_quantity');

        }

    }
    /**
     *
     * @param $sku
     * @param string $filed
     * @return false|null|string
     */
    public static function   getSkus($sku,$filed='*',$level=1,$purchase_type=2,$product_id=null)
    {
        if($product_id)
        {
            return self::find()->select($filed)->where(['id'=>$product_id,'sku'=>$sku,'is_purchase'=>1,'level_audit_status'=>$level,'purchase_type'=>$purchase_type])->scalar();
        } else{

            return self::find()->select($filed)->where(['sku'=>$sku,'is_purchase'=>1,'level_audit_status'=>$level,'purchase_type'=>$purchase_type])->scalar();
        }

    }

    /**
     * 获取字段
     * @param $number
     * @param string $filed
     * @return array|null|\yii\db\ActiveRecord
     */
    public  static  function  getField($number,$filed='*')
    {
        return self::find()->select($filed)->where(['demand_number'=>$number])->one();
    }

}
