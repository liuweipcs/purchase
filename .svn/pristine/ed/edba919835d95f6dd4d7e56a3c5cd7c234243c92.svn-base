<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_purchase_suggest_task".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $purchase_num
 * @property string $create_time
 */
class TransferOrderPage extends \yii\db\ActiveRecord
{
    const TASK_STATUS_FAILED            = 0;         //运行失败
    const TASK_STATUS_SUCCESS           = 1;          //运行成功

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_transfer_order_page';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page', 'status', 'execute_time', 'end_time', 'create_time', 'error_message'], 'safe'],
        ];
    }
}
