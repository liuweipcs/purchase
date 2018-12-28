<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_excep_return_info".
 *
 * @property integer $id
 * @property string $express_no
 * @property string $pur_number
 * @property string $excep_number
 * @property string $create_time
 * @property string $return_user
 * @property string $return_time
 * @property string $return_status
 * @property integer $data_id
 * @property string $update_time
 */
class ExcepReturnInfo extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_excep_return_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'return_time', 'update_time'], 'safe'],
            [['data_id'], 'integer'],
            [['express_no', 'pur_number', 'excep_number'], 'string', 'max' => 100],
            [['return_user'], 'string', 'max' => 150],
            [['return_status'], 'string', 'max' => 255],
            [['data_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'express_no' => 'Express No',
            'pur_number' => 'Pur Number',
            'excep_number' => 'Excep Number',
            'create_time' => 'Create Time',
            'return_user' => 'Return User',
            'return_time' => 'Return Time',
            'return_status' => 'Return Status',
            'data_id' => 'Data ID',
            'update_time' => 'Update Time',
        ];
    }
}
