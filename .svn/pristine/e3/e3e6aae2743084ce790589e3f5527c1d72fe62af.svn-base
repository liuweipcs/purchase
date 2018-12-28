<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%purchase_reply}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $note
 * @property string $create_time
 * @property integer $create_id
 * @property integer $purchase_type
 * @property integer $replay_type
 */
class PurchaseReply extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_reply}}';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],

                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => date('Y-m-d H:i:s',time()),
            ],
            [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_id'],

                ],
                'value' => Yii::$app->user->id,
            ],


        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'replay_type'], 'required'],
            [['create_time'], 'safe'],
            [['create_id', 'purchase_type', 'replay_type'], 'integer'],
            [['pur_number'], 'string', 'max' => 50],
            [['note'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'Pur Number',
            'note' => 'Note',
            'create_time' => 'Create Time',
            'create_id' => 'Create ID',
            'purchase_type' => 'Purchase Type',
            'replay_type' => 'Replay Type',
        ];
    }
    /**
     * 获取回复信息
     */
    public static function getReplyInfo($pur_number, $replay_type=null)
    {
        $where['pur_number'] = $pur_number;
        if (!empty($replay_type)) {
            $where['replay_type'] = $replay_type;
        }
        return self::find()->where($where)->orderBy('id desc')->asArray()->all();
    }
}
