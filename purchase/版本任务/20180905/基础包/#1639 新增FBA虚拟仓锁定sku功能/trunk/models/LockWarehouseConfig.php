<?php

namespace app\models;

use Yii;


class LockWarehouseConfig extends \yii\db\ActiveRecord
{
    public $file_execl;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_lock_warehouse_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'warehouse_code'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'sku',
            'warehouse_code' => '仓库编码',
            'create_user' => '创建人',
            'create_time' => '创建时间',
            'is_lock' => '是否锁定'
        ];
    }

    /**
     *更改sku状态
     */
    public static function getStatus($status=null,$id=null)
    {
        $types = [
            '0' =>"<span class='label label-danger change-status' status='0' style='cursor: pointer' id='{$id}'>否</span>",
            '1' =>"<span class='label label-success change-status' status='1' style='cursor: pointer' id='{$id}'>是</span>",
        ];

        return isset($types[$status]) ?  $types[$status]:$types;
    }

    /**
     *显示sku状态
     */
    public static function getSkuStatus($status=null)
    {
        $types = [
            '0' =>"<span class='label label-danger'>否</span>",
            '1' =>"<span class='label label-success'>是</span>",
        ];

        return isset($types[$status]) ?  $types[$status]:$types;
    }
}
