<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_token".
 *
 * @property integer $id
 * @property string $token
 * @property string $create_time
 */
class Token extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['token', 'create_time'], 'required'],
            [['token'], 'string', 'max' => 255],
            [['create_time'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'token' => 'Token',
            'create_time' => 'Create Time',
        ];
    }

    public static function getToken(){
        return self::find()->select('token')->orderBy('id DESC')->scalar();
    }
}
