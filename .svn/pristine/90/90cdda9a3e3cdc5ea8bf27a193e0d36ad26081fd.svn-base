<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%purchase_grade_audit}}".
 *
 * @property string $id
 * @property integer $grade
 * @property integer $type
 * @property string $audit_price
 * @property string $create_user
 * @property string $create_time
 */
class PurchaseGradeAudit extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_grade_audit}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_user'], 'required'],
            [['create_time','type'], 'safe'],
            [['create_user','audit_price'], 'string', 'max' => 50],
            ['audit_user', //只有 name 能接收错误提示，数组['name','shop_id']的场合，都接收错误提示
                'unique', 'targetAttribute'=>['audit_user'] ,
                'comboNotUnique' => '已存在一条此用户的数据，请去首页更新！' //错误信息
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_user' => '审核人',
            'type' => '用户类型',
            'audit_price' => '审核金额',
            'create_user' => '创建人',
            'create_time' => '创建时间',
        ];
    }
    /**
     * 获取审核金额
     */
    public static function getAuditPrice($audit_user, $type=null)
    {
        $where = [];
        if (!empty($audit_user)) {
            $where['audit_user'] = $audit_user;
        }
        if (!empty($type)) {
            $where['type'] = $type;
        }
        $info = self::find()->where($where)->asArray()->one();
        return !empty($info['audit_price']) ? $info['audit_price'] : 0;
    }
}
