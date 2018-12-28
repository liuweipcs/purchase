<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_purchase_abnomal".
 *
 * @property string $id
 * @property string $express_no
 * @property string $package_qty
 * @property string $send_addr
 * @property string $send_name
 * @property integer $status
 * @property integer $is_del
 * @property string $note
 * @property string $create_user
 * @property string $create_time
 * @property string $update_time
 */
class PurchaseAbnomal extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_abnomal';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'express_no', 'send_addr', 'send_name', 'status', 'is_del', 'create_user', 'create_time', 'update_time'], 'required'],
            [['id', 'package_qty', 'status', 'is_del'], 'integer'],
            [['create_time', 'update_time','buyer','handle_time','handle_id','handle_note'], 'safe'],
            [['express_no', 'send_name'], 'string', 'max' => 30],
            [['send_addr'], 'string', 'max' => 50],
            [['note'], 'string', 'max' => 500],
            [['create_user'], 'string', 'max' => 20],
            [['express_no'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'express_no' => '快递单号',
            'package_qty' => '包裹数量',
            'send_addr' => '发货地址',
            'send_name' => '发货人',
            'status' => '状态',
            'is_del' => '删除',
            'note' => '备注',
            'create_user' => '创建人',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'buyer' => '采购员',
            'handle_time' => '处理时间',
            'handle_id' => '处理人',
            'handle_note' => '处理结果',
            'img' => '异常图片',
        ];
    }
}
