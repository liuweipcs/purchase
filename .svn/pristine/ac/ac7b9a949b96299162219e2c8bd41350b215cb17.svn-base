<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%return_goods}}".
 *
 * @property string $id
 * @property string $pur_number
 * @property string $return_number
 * @property string $supplier_code
 * @property string $supplier_name
 * @property integer $qty
 * @property string $sku
 * @property string $pro_name
 * @property string $create_user
 * @property integer $create_time
 * @property string $buyer
 * @property integer $state
 */
class ReturnGoods extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%return_goods}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['qty', 'create_time', 'state', 'cargo_company_id'], 'integer'],
            [['pur_number', 'supplier_code', 'sku', 'create_user', 'return_number', 'express_no'], 'string', 'max' => 50],
            [['supplier_name'], 'string', 'max' => 30],
            [['pro_name'], 'string', 'max' => 100],
            [['buyer', 'cargo_company'], 'string', 'max' => 20],
            [['freight','note','qc_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pur_number' => Yii::t('app', '采购单'),
            'supplier_code' => Yii::t('app', '供应商编码'),
            'supplier_name' => Yii::t('app', '供应商名称'),
            'qty' => Yii::t('app', '数量'),
            'sku' => Yii::t('app', 'SKU'),
            'pro_name' => Yii::t('app', '产品名称'),
            'create_user' => Yii::t('app', '创建人'),
            'create_time' => Yii::t('app', '创建时间'),
            'buyer' => Yii::t('app', '采购员'),
            'state' => Yii::t('app', '状态'),
            'return_number' => Yii::t('app', '退货单号'),
            'cargo_company_id' => Yii::t('app', '快递公司ID'),
            'cargo_company' => Yii::t('app', '快递公司'),
            'freight' => Yii::t('app', '运费'),
            'express_no' => Yii::t('app', '快递单号'),
            'qc_id' => Yii::t('app', 'qc_id'),
            'note' => Yii::t('app', '备注'),
        ];
    }

    public static function changeState()
    {

        return $state=[''=>'请选择','0'=>'未处理','1'=>'处理中','2'=>'仓库已发货','3'=>'未退款','4'=>'已退款'];
    }

    /**
     * 保存推货记录
     * @param $data
     * @return bool
     */
    public function returnSave($data)
    {
        if (!empty($data)) {
            $this->pur_number    = $data['pur_number'];
            $this->supplier_code = $data['supplier_code'];
            $this->supplier_name = $data['supplier_name'];
            $this->warehouse_code = $data['warehouse_code'];
            $this->qty           = isset($data['bad_products_qty'])?$data['bad_products_qty']:$data['qty'];
            $this->sku           = $data['sku'];
            $this->pro_name      = $data['name'];
            $this->create_user   = $data['handler'];
            $this->create_time   = time();
            $this->buyer         = $data['buyer'];
            $this->state         = 0;
            $this->qc_id         = $data['qc_id'];
            $this->return_number = 'RE' . date('YmdHis') . mt_rand(10, 99);
            return $this->save(false);

        }
    }
}
