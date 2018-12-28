<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "pur_stock".
 *
 * @property string $id
 * @property string $sku
 * @property string $warehouse_code
 * @property string $on_way_stock
 * @property string $available_stock
 * @property string $stock
 * @property integer $left_stock
 * @property string $created_at
 * @property string $update_at
 */
class Stock extends BaseModel
{
    public $state;
    public $purchas_status;
    public $sourcing_status;
    public $warn_status;
    public $suggest_note;
    public $is_pass;
    public $purchase_type;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_stock';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'warehouse_code', 'created_at', 'update_at'], 'required'],
            [['on_way_stock', 'available_stock', 'stock', 'left_stock'], 'integer'],
            [['created_at', 'update_at'], 'safe'],
            
            [['sku', 'warehouse_code'], 'safe', 'on' => 'timesku'],

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
            'on_way_stock' => '在途库存',
            'available_stock' => '可用库存',
            'stock' => '可用库存',
            'left_stock' => '欠货数量',
            'created_at' => '创建时间',
            'update_at' => '更新时间',
        ];
    }
    /**
     * @desc 和仓库表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-05-05 16:37:11
     */
    public function getWarehouse(){
        return $this->hasOne(Warehouse::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和仓库补货策略-采购系数表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-05-05 14:38:11
     */
    public function getWarehousePurchaseTactics(){
        return $this->hasMany(WarehousePurchaseTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和仓库补货策略-采购系数表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-05-05 14:38:11
     */
    public function getSkuSalesStatistics(){
        return $this->hasOne(SkuSalesStatistics::className(), ['warehouse_code' => 'warehouse_code','sku'=>'sku']);
    }

    /**
     * 一对一关联采购订单详情表
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrderItems(){
        return $this->hasOne(PurchaseOrderItems::className(), ['sku'=>'sku']);
    }

    /**
     * 一对一  通过中间表（采购订单详情表）关联采购订单主表
     * @return $this
     */
    public function getPurchaseOrder(){
        return $this->hasOne(PurchaseOrder::className(), ['pur_number'=>'pur_number'])->via('purchaseOrderItems');;
    }

    /**
     * 根据sku和仓库编码获取$code
     * @param $sku
     * @param $code
     * @author ztt
     */
    public static function getStock($sku,$code='SZ_AA')
    {

        return self::find()->where(['sku'=>$sku,'warehouse_code'=>$code])->one();
    }

    /**
     * 审批通过既更新在途库存
     * @param $sku
     * @param string $code
     * @return bool
     */
    public static function  saveStock($sku,$code='SZ_AA')
    {
        if(is_array($sku))
        {
            $model = self::find()->where(['in','sku',$sku])->andWhere(['warehouse_code'=>$code])->all();
            if(!empty($model)){
                foreach($model as $k=>$v){
                    $v->on_way_stock +=$sku[$k]['ctq'];
                    $status = $v->save(false);
                }
                return $status;
            }else{
                return true;
            }


        }

    }

    /**
     * 作废即减去在途
     * @param $sku
     * @param string $code
     * @return bool
     */
    public static  function updateStock($sku,$code='SZ_AA')
    {
        if(is_array($sku))
        {
            $model = self::find()->where(['in','sku',$sku])->andWhere(['warehouse_code'=>$code])->all();

            if($model)
            {
                foreach ($model as $k=>$v)
                {

                    if($v->on_way_stock>= $sku[$k]['ctq'])
                    {
                        $v->on_way_stock -=$sku[$k]['ctq'];
                        $status = $v->save(false);
                    } else{
                        $status = true;
                    }
                }
            } else{

                return true;
            }

            return $status;
        }
    }



}
