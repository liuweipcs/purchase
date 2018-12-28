<?php

namespace app\models;

use app\models\base\BaseModel;

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
class SupplierPaymentAccount extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier_payment_account}}';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supplier_id'], 'required'],
            [['pay_id', 'supplier_id', 'payment_method', 'payment_platform', 'status','payment_platform_bank'], 'integer'],
            [['account', 'account_name'], 'string', 'max' => 30],
            [['payment_platform_branch'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pay_id' => Yii::t('app', 'pay_ID'),
            'supplier_id' => Yii::t('app', '供应商ID'),
            'payment_method' => Yii::t('app', '支付方式'),
            'payment_platform' => Yii::t('app', '支付平台'),
            'payment_platform_bank' => Yii::t('app', '支行类型'),
            'payment_platform_branch' => Yii::t('app', '具体支行名称'),
            'account' => Yii::t('app', '帐号'),
            'account_name' => Yii::t('app', '帐号名'),
            'phone_number' => Yii::t('app', '到账通知手机号'),
            'id_number' => Yii::t('app', '证件号'),
            'prov_code'               => Yii::t('app', '省代码'),
            'city_code'               => Yii::t('app', '市代码'),
            'account_type'               => Yii::t('app', '卡类型'),
            'status' => Yii::t('app', '状态'),
        ];
    }
    /**
     * 保存数据
     * @param $data
     */
    public  function saveSupplierPay($data,$supplier_id,$bool=false)
    {
        $model = new self();
        $model->validate();
        if(!empty($data['SupplierPaymentAccount']))
        {
            $supplier_payment_account_info = [];
            foreach ($data['SupplierPaymentAccount'] as $c => $v)
            {
                $sb =[];
                foreach ($v as $d => $k)
                {
                    $sb[] = [
                        'supplier_id'             => $supplier_id['id'],
                        'supplier_code'           => $supplier_id['code'],
                        'payment_platform'        => !isset($data['SupplierPaymentAccount']['payment_platform'][$d]) ? 1 : $data['SupplierPaymentAccount']['payment_platform'][$d],
                        'payment_platform_bank'   => $data['SupplierPaymentAccount']['payment_platform_bank'][$d],
                        'payment_platform_branch' => $data['SupplierPaymentAccount']['payment_platform_branch'][$d],
                        'prov_code'               => $data['SupplierPaymentAccount']['prov_code'][$d],
                        'city_code'               => $data['SupplierPaymentAccount']['city_code'][$d],
                        'account'                 => $data['SupplierPaymentAccount']['account'][$d],
                        'account_name'            => $data['SupplierPaymentAccount']['account_name'][$d],
                        'status'                  => isset($data['SupplierPaymentAccount']['status'][$d])?$data['SupplierPaymentAccount']['status'][$d]:1,
                        'account_type'            => $data['SupplierPaymentAccount']['account_type'][$d],
                        'id_number'               => $data['SupplierPaymentAccount']['id_number'][$d],
                        'phone_number'               => $data['SupplierPaymentAccount']['phone_number'][$d],
                    ];
                }
            }

            foreach ($sb as $sk => $v)
            {
                if ($bool) {

                    $supplier_payment_account_info[$sk] = $v;
                } else {
                    Yii::$app->db->createCommand()->insert(SupplierPaymentAccount::tableName(),$v)->execute();
                }
            }
            if ($bool) {
                return $supplier_payment_account_info;
            }

        }


    }
}
