<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_warehouse".
 *
 * @property string $id
 * @property string $warehouse_name
 * @property integer $warehouse_type
 * @property integer $is_custody
 * @property string $warehouse_code
 * @property string $country
 * @property string $state
 * @property string $city
 * @property string $address
 * @property string $telephone
 * @property string $fax
 * @property string $zip_code
 * @property string $remark
 * @property integer $use_status
 * @property integer $create_user_id
 * @property integer $modify_user_id
 * @property string $create_time
 * @property string $modify_time
 * @property string $pattern
 *
 * @property WarehouseLog[] $warehouseLogs
 * @property WarehousePurchaseTactics[] $warehousePurchaseTactics
 * @property WarehouseQtyTactics[] $warehouseQtyTactics
 * @property WarehouseSalesTactics[] $warehouseSalesTactics
 */
class Warehouse extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_warehouse';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_name', 'warehouse_type', 'warehouse_code', 'address'], 'required'],
            [['warehouse_type', 'is_custody', 'use_status', 'create_user_id', 'modify_user_id'], 'integer'],
            [['country', 'state', 'city', 'telephone', 'fax', 'zip_code', 'remark', 'create_user_id', 'modify_user_id'
                , 'create_time', 'modify_time'], 'safe'],
            [['pattern'], 'string'],
            [['warehouse_name', 'warehouse_code', 'country', 'state', 'city', 'telephone', 'fax'], 'string', 'max' => 50],
            [['address'], 'string', 'max' => 255],
            [['zip_code'], 'string', 'max' => 20],
            [['remark'], 'string', 'max' => 500],
            [['warehouse_code'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'warehouse_name' => '仓库名称',
            'warehouse_type' => '仓库类型',
            'is_custody' => '是否为托管',
            'warehouse_code' => '仓库code',
            'country' => '仓库所在国家',
            'state' => '仓库所在省份',
            'city' => '仓库所在市',
            'address' => '仓库详细地址',
            'telephone' => '联系电话',
            'fax' => '传真',
            'zip_code' => '邮编',
            'remark' => '备注',
            'use_status' => '是否起用',
            'create_user_id' => 'Create User ID',
            'modify_user_id' => 'Modify User ID',
            'create_time' => '创建时间',
            'modify_time' => 'Modify Time',
            'pattern' => '补货模式',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseLogs()
    {
        return $this->hasMany(WarehouseLog::className(), ['warehouse_code' => 'warehouse_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehousePurchaseTactics()
    {
        return $this->hasMany(WarehousePurchaseTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseQtyTactics()
    {
        return $this->hasMany(WarehouseQtyTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseSalesTactics()
    {
        return $this->hasMany(WarehouseSalesTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseMin()
    {
        return $this->hasOne(WarehouseMin::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public static function getWarehouseByCode($warehouse_code=null)
    {
        return self::find()->where(['warehouse_code'=>$warehouse_code])->asArray()->one();
    }
    
}
