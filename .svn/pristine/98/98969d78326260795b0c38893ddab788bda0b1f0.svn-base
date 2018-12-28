<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;


class TransferOrderChangeLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{transfer_order_change_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['suggest_id', 'sku', 'qty', 'changed_qty'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'suggest_id' => '采购建议id',
            'sku' => '产品SKU',
            'qty' => '原始建议数据量',
            'changed_qty' => '变更后的建议数据量',
        ];
    }
    /**
     * 添加日志
     */
    public static function addLog($data)
    {
        $model = new self();
        $model->suggest_id = $data['suggest_id']; //采购建议id
        $model->sku = $data['sku'];//产品SKU
        $model->qty = $data['qty']; //原始建议数据量
        $model->changed_qty = $data['changed_qty']; //变更后的建议数据量
        $status = $model->save(false);
        return $status;
    }
}
