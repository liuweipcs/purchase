<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the model class for table "{{%logistics_carrier}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $carrier_code
 * @property string $create_time
 * @property string $update_time
 * @property integer $create_id
 * @property integer $update_id
 * @property string $site_url
 */
class LogisticsCarrier extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%logistics_carrier}}';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['update_time'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => date('Y-m-d H:i:s',time()),
            ],
            [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_id'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['update_id'],
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
            [['name','carrier_code'], 'required'],
            [['id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['name'], 'string', 'max' => 20],
            [['carrier_code'], 'string', 'max' => 30],
            [['site_url'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '承运商名'),
            'carrier_code' => Yii::t('app', '承运商编码'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'create_id' => Yii::t('app', '创建人'),
            'update_id' => Yii::t('app', '更新人'),
            'site_url' => Yii::t('app', '网址'),
        ];
    }
}
