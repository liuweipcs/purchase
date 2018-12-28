<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%group_audit_config}}".
 *
 * @property string $id
 * @property integer $type
 * @property integer $group
 * @property string $values
 * @property string $remark
 * @property integer $uid
 * @property integer $cdate
 */
class GroupAuditConfig extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%group_audit_config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group', 'values'], 'required'],
            [['type', 'group', 'uid', 'cdate'], 'integer'],
            [['values'], 'string', 'max' => 100],
            [['remark'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', '审核模块'),
            'group' => Yii::t('app', '分组'),
            'values' => Yii::t('app', '范围'),
            'remark' => Yii::t('app', '备注'),
            'uid' => Yii::t('app', '最后修改人'),
            'cdate' => Yii::t('app', '最后修改时间'),
        ];
    }
}
