<?php

namespace app\modules\manage\models;

use Yii;

/**
 * This is the model class for table "pur_supplier_permission_items".
 *
 * @property integer $id
 * @property string $supplier_code
 * @property integer $permission_id
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 * @property string $create_user_name
 * @property string $create_user_ip
 * @property string $update_user_name
 * @property string $update_user_ip
 */
class SupplierPermissionItems extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_permission_items';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supplier_code', 'permission_id', 'create_time', 'create_user_name'], 'required'],
            [['permission_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['supplier_code', 'create_user_name', 'update_user_name'], 'string', 'max' => 150],
            [['create_user_ip', 'update_user_ip'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_code' => 'Supplier Code',
            'permission_id' => 'Permission ID',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
            'create_user_name' => 'Create User Name',
            'create_user_ip' => 'Create User Ip',
            'update_user_name' => 'Update User Name',
            'update_user_ip' => 'Update User Ip',
        ];
    }

    public static function savePermission($newDatas,$oldDatas,$supplier_code){
        $insertPermission = array_diff($newDatas,$oldDatas);
        $deletePermission = array_diff($oldDatas,$newDatas);
        $insertDatas = [];
        foreach ($insertPermission as $key=>$value){
            $insertDatas[$key][]= $supplier_code;
            $insertDatas[$key][]= $value;
            $insertDatas[$key][]= date('Y-m-d H:i:s',time());
            $insertDatas[$key][]= 1;
            $insertDatas[$key][]= Yii::$app->user->identity->username;
            $insertDatas[$key][]= Yii::$app->request->userIP;
        }
        Yii::$app->db->createCommand()->batchInsert(self::tableName(),
            ['supplier_code','permission_id','create_time','status','create_user_name','create_user_ip'],$insertDatas)->execute();
        self::updateAll(['status'=>0,'update_user_name'=> Yii::$app->user->identity->username,'update_user_ip'=>Yii::$app->request->userIP],
            ['supplier_code'=>$supplier_code,'permission_id'=>$deletePermission]);
        return ['status'=>'success','message'=>'权限分配成功'];
    }
}
