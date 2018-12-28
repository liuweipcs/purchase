<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%supplier_check_note}}".
 *
 * @property integer $id
 * @property integer $check_id
 * @property string $supplier_code
 * @property string $create_user
 * @property string $create_time
 * @property string $check_note
 */
class SupplierCheckNote extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier_check_note}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['check_id'], 'required'],
            [['check_id'], 'integer'],
            [['create_time'], 'safe'],
            [['supplier_code', 'create_user'], 'string', 'max' => 50],
            [['check_note'], 'string', 'max' => 1000],
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
            'supplier_code' => 'Supplier Code',
            'create_user' => 'Create User',
            'create_time' => 'Create Time',
            'check_note' => 'Check Note',
        ];
    }
    /**
     *获取验厂备注
     */
    public static function getCheckNote($check_id=null,$supplier_code=null)
    {
        $where = [];
        if (!empty($check_id)) {
            $where['check_id'] = $check_id;
        }
        if (!empty($supplier_code)) {
            $where['supplier_code'] = $supplier_code;
        }
        return self::find()->select('check_note')->where($where)->asArray()->all();
    }
    /**
     * 获取备注信息
     */
    public static function getSupplierCheckNote($check_id=null,$supplier_code=null)
    {
        $where = [];
        if (!empty($check_id)) {
            $where['check_id'] = $check_id;
        }
        if (!empty($supplier_code)) {
            $where['supplier_code'] = $supplier_code;
        }
        $where['type'] = 0;
        return self::find()->where($where)->asArray()->all();
    }
    /**
     * 保存信息
     */
    public static function saveNote($data)
    {
        $model = new self();
        $model->check_id = $data['check_id'];
        $model->supplier_code = !empty($data['supplier_code']) ? $data['supplier_code'] : null;
        $model->create_user = yii::$app->user->identity->username;
        $model->create_time = date('Y-m-d H:i:s', time());
        $model->check_note = $data['check_note'];
        return $model->save();
    }
    /**
     * 保存评价信息
     */
    public static function saveAuditNote($data)
    {
        $model = new self;
        $user_id = Yii::$app->user->identity->id;
        $user_name = Yii::$app->user->identity->username;
        $roles = Yii::$app->authManager->getRolesByUser($user_id);

        $where['check_id'] = $data['check_id'];
        //一次评价
        $supplier_model = SupplierCheck::find()->where(['id'=>$data['check_id']])->one();
        $supplier_model->status = 3;
        $supplier_model->save(false);



        $model->check_id = $data['check_id'];
        $model->create_user = $user_name;
        $model->create_time = date('Y-m-d H:i:s');
        $model->check_note = $data['check_note'];
        $model->type = 1;
        if ( array_key_exists('供应链', $roles) ) {
            $model->role = '供应链';
        }elseif ( array_key_exists('品控', $roles)) {
            $model->role = '品控';
        }else {
            $model->role = '结果';
        }

        $status = $model->save();
        

        return $status;
    }
    /**
     * 获取评价信息
     */
    public static function getAuditNote($where)
    {
        $where['type'] = 1;
        return self::find()->where($where)->asArray()->all();
    }
}
