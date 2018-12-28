<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%supplier_payment_account}}".
 *
 * @property integer $id
 * @property integer $supplier_id
 * @property integer $payment_method
 * @property integer $payment_platform
 * @property string $account
 * @property string $account_name
 * @property integer $status
 */
class SupplierPaymentAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier_payment_account}}';
    }





}
