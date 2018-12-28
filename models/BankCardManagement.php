<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the model class for table "{{%bank_card_management}}".
 *
 * @property integer $id
 * @property integer $head_office
 * @property string $branch
 * @property string $account_holder
 * @property string $account_number
 * @property string $account_abbreviation
 * @property string $payment_password
 * @property integer $payment_types
 * @property integer $account_sign
 * @property integer $status
 * @property integer $application_business
 * @property string $remarks
 * @property string $create_time
 * @property string $update_time
 * @property integer $create_id
 * @property integer $update_id
 * @property string $k3_bank_account
 */
class BankCardManagement extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bank_card_management}}';
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
            [['head_office', 'payment_types', 'account_sign', 'status', 'application_business', 'create_id', 'update_id'], 'integer'],
            [['head_office', 'payment_types', 'branch','account_sign', 'status', 'application_business','account_holder','account_number','account_abbreviation'], 'required'],
            [['create_time', 'update_time','k3_bank_account'], 'safe'],
            [['branch', 'account_abbreviation', 'payment_password','k3_bank_account'], 'string', 'max' => 50],
            [['account_holder'], 'string', 'max' => 30],
            [['account_number'], 'string', 'max' => 100],
            [['remarks'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'head_office' => Yii::t('app', '开户银行总行'),
            'branch' => Yii::t('app', '支行'),
            'account_holder' => Yii::t('app', '开户人'),
            'account_number' => Yii::t('app', '开户账号'),
            'account_abbreviation' => Yii::t('app', '账号简称'),
            'payment_password' => Yii::t('app', '支付密码'),
            'payment_types' => Yii::t('app', '支付类型'),
            'account_sign' => Yii::t('app', '账号标志'),
            'status' => Yii::t('app', '状态'),
            'application_business' => Yii::t('app', '应用业务'),
            'remarks' => Yii::t('app', '备注'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
            'create_id' => Yii::t('app', '创建人'),
            'update_id' => Yii::t('app', '更新人'),
            'k3_bank_account' => Yii::t('app', 'k3账号'),
        ];
    }
}
