<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_purchase_tactics".
 */
class PurchaseTacticsAbnormal extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_tactics_abnormal}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'warehouse_code', 'warehouse_name', 'warehouse_type', 'reason', 'date_time' ], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'sku',
            'name' => '产品名称',
            'warehouse_code' => '仓库编码',
            'warehouse_name' => '仓库名称',
            'warehouse_type' => '仓库类型',
            'supplier_name' => '供应商名称',
            'supplier_code' => '供应商代码',
            'buyer' => '采购员',
            'reason' => '异常原因',
            'date_time' => '操作时间'
        ];
    }

    /**
     * 关联 采购详情 表 一对一
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseOrderItems()
    {
        return $this->hasOne(PurchaseOrderItems::className(),['sku'=>'sku']);
    }
    /**
     * 关联 仓库 表 一对一
     * @return \yii\db\ActiveQuery
     */
    public  function  getWarehouse()
    {
        return $this->hasOne(Warehouse::className(),['warehouse_code'=>'warehouse_code'])->where(['use_status'=>1]);
    }
    /**
     * 关联 供应商 表 一对一
     * @return \yii\db\ActiveQuery
     */
    public  function  getSupplierBuyer()
    {
        return $this->hasOne(Supplier::className(),['supplier_code'=>'supplier_code'])->where(['status'=>1]);
    }
    /**
     * 保存mrp异常数据
     * @param array $params['sku'=>sku,'warehouse_code'=>warehouse_code,'supplier_code'=>supplier_code,'buyer'=>buyer,'reason'=>reason]
     * @return bool
     */
    public static function saveAbnomal($params){

        $data = $params;
        //echo "<pre>";var_dump($data);exit;
        if (empty($data)) {
            $status = false;
            return $status;
        }

        if (empty($data['name'])) {
            //获取产品信息
            $sku = $data['sku'];
            $product_name = ProductDescription::find()
                            ->select('title')
                            ->where(['sku'=>$sku])
                            ->asArray()
                            ->scalar();
        }

        //获取仓库信息
        $warehouse_code = $data['warehouse_code'];
        $warehouseInfo = Warehouse::find()
                        ->select('warehouse_code,warehouse_name,warehouse_type')
                        ->where(['warehouse_code'=>$warehouse_code, 'use_status'=>1])
                        ->asArray()
                        ->one();

        //获取供应商信息
        $supplier_code = $data['supplier_code'];
        $supplier_name = Supplier::find()
                        ->select('supplier_name')
                        ->where(['supplier_code'=>$supplier_code, 'status'=>1])
                        ->asArray()
                        ->scalar();         

        $model = new self();
        $model->attributes = $data;
        $model->name = $product_name ? : "";
        $model->supplier_code = $supplier_code;
        $model->buyer = $data['buyer'];
        $model->warehouse_name = $warehouseInfo['warehouse_name'] ? : "";
        $model->warehouse_type = $warehouseInfo['warehouse_type'] ? : "";
        $model->supplier_name = $supplier_name ? : "";
        $model->date_time = date('Y-m-d H:i:s', time());
        $status = $model->save(false);

        return $status;

    }
}
