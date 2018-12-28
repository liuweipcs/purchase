<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_supplier_check_user".
 *
 * @property integer $id
 * @property integer $check_id
 * @property string $check_user_name
 * @property integer $check_user_id
 * @property integer $status
 * @property string $update_user_name
 * @property string $create_user_name
 * @property string $create_time
 * @property string $update_time
 */
class SupplierCheckUser extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_check_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['check_id'], 'required'],
            [['check_id', 'check_user_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['check_user_name'], 'string', 'max' => 255],
            [['update_user_name', 'create_user_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'check_id' => 'Check ID',
            'check_user_name' => 'Check User Name',
            'check_user_id' => 'Check User ID',
            'status' => 'Status',
            'update_user_name' => 'Update User Name',
            'create_user_name' => 'Create User Name',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
