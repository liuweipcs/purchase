<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_log}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $product_code
 * @property string $request_url

 * @property string $note
 * @property string $create_user
 * @property string $create_time
 */
class PurchaseLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_url'], 'required'],
            [['create_time'], 'safe'],
            [['pur_number'], 'string', 'max' => 30],
            [['create_user'], 'string', 'max' => 20],
            [['request_url', 'note'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => Yii::t('app', 'ID'),
            'pur_number'   => Yii::t('app', 'Pur Number'),
            'request_url'  => Yii::t('app', 'Request Url'),
            'note'         => Yii::t('app', 'Note'),
            'ip'           => Yii::t('app', 'ip'),
            'create_user'  => Yii::t('app', 'Create User'),
            'create_time'  => Yii::t('app', 'Create Time'),
        ];
    }
    /**
     * @param $action Action
     */
    public static function addLog($action)
    {
        $model              = new self();
        $model->pur_number  = $action['pur_number'];
        $model->note        = $action['note'];
        $model->request_url = strlen(Yii::$app->request->absoluteUrl)>250 ? Yii::$app->request->getPathInfo() : Yii::$app->request->absoluteUrl;
        $model->create_user = Yii::$app->user->identity['username'];
        $model->ip          = Yii::$app->request->userIP;
        $model->create_time = date('Y-m-d H:i:s');
        $model->save();
    }
}
