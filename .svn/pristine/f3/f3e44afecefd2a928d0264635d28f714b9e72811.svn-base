<?php
namespace app\models;

use app\models\base\BaseModel;

/**
 * This is the model class for table "pur_purchase_tactics_warehouse".
 */
class PurchaseTacticsWarehouse extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_tactics_warehouse}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tactics_id','warehouse_code'],'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tactics_id' => '备货策略ID',
            'warehouse_name' => '适用仓库名称',
            'warehouse_code' => '适用仓库Code',
        ];
    }

}
