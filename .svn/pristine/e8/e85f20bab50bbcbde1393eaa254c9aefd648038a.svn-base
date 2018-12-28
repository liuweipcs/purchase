<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "{{%product_provider}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $supplier_code
 * @property string $is_supplier
 * @property string $is_exemption
 * @property string $is_push
 * @property string $quotes_id
 *
 *
 */
class ProductProvider extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_supplier}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'supplier_code'], 'required'],
            [['sku', 'supplier_code'], 'string', 'max' => 20],
            [['sku', 'supplier_code'], 'unique', 'targetAttribute' => ['sku', 'supplier_code'], 'message' => 'The combination of Sku and Supplier Code has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'sku' => Yii::t('app', 'Sku'),
            'supplier_code' => Yii::t('app', 'Supplier Code'),
            'is_supplier' => Yii::t('app', 'is_supplier'),
            'is_exemption'=> Yii::t('app', 'is_exemption'),
            'is_push'     => Yii::t('app', 'is_push'),
            'quotes_id'   => Yii::t('app', 'quotes_id'),
        ];
    }

    /**
     * 关联供应商报价表
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(SupplierQuotes::className(), ['suppliercode' => 'supplier_code']);

    }

    /**
     * 更新默认供应商
     * @param $sku
     * @param $code
     * @return bool|void
     */
    public  static  function  UpdateDefaultSupplier($sku, $code)
    {
        //查询一条把他修改成不是默认供应商
        $sku= self::findOne(['sku'=>$sku,'is_supplier'=>1]);
        if (!$sku)
        {
            return false;
        }
        $sku->is_supplier = 0;
        if ($sku->save())
        {
            $code    = self::findOne(['sku'=>$sku,'supplier_code'=>$code]);
            $code->is_supplier = 1;
            $status = $code->save();
            return $status;
        }
        return false;
    }
}
