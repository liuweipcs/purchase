<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_supplier_sync_log".
 *
 * @property integer $id
 * @property string $sku
 * @property string $supplier_name
 * @property integer $supplier_status
 */
class SupplierSyncLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_sync_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supplier_status'], 'integer'],
            [['sku', 'supplier_name'], 'string', 'max' => 255],
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
            'supplier_name' => 'Supplier Name',
            'supplier_status' => 'Supplier Status',
        ];
    }

    public static function saveOne($sku,$supplier_name,$supplier_status){
        $model = self::find()->andWhere(['sku'=>$sku,'supplier_name'=>$supplier_name])->one();
        if(empty($model)){
            $model = new self;
        }
        $model->sku = $sku;
        $model->supplier_name = $supplier_name;
        $model->supplier_status = $supplier_status;
        $model->sync_time   = date('Y-m-d H:i:s',time());
        $model->save(false);
    }
}
