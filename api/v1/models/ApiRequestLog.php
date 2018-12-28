<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_api_request_log".
 *
 * @property integer $id
 * @property string $post_content
 * @property string $response_content
 * @property string $create_time
 * @property string $api_url
 * @property string $status
 */
class ApiRequestLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_api_request_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'post_content', 'response_content', 'create_time', 'api_url', 'status'], 'required'],
            [['id'], 'integer'],
            [['post_content', 'response_content'], 'string'],
            [['create_time'], 'safe'],
            [['api_url', 'status'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_content' => 'Post Content',
            'response_content' => 'Response Content',
            'create_time' => 'Create Time',
            'api_url' => 'Api Url',
            'status' => 'Status',
        ];
    }
}
