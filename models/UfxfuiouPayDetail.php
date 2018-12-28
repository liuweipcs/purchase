<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_ufxfuiou_pay_detail".
 *
 * @property integer $id
 * @property string $pur_tran_num
 * @property string $ufxfuiou_tran_num
 * @property string $pay_status
 * @property integer $tran_money
 * @property string $payee_card_number
 * @property string $payee_user_name
 * @property string $payee_id_number
 * @property string $payee_phone_number
 * @property string $bank_code
 * @property string $city_code
 * @property string $remark
 * @property string $is_need_review
 * @property string $branch_bank
 * @property string $is_notify
 * @property string $create_time
 * @property string $create_user_name
 * @property string $create_ip
 * @property string $charge
 * @property integer $status
 * @property string $ufxfuiou_account
 */
class UfxfuiouPayDetail extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_ufxfuiou_pay_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_tran_num'], 'required'],
            [['tran_money','status'], 'integer'],
            [['create_time'], 'safe'],
            [['pur_tran_num'], 'string', 'max' => 30],
            [['ufxfuiou_tran_num'], 'string', 'max' => 250],
            [['pay_status', 'branch_bank', 'create_user_name', 'create_ip'], 'string', 'max' => 255],
            [['payee_card_number', 'payee_id_number','payee_phone_number', 'bank_code', 'city_code'], 'string', 'max' => 50],
            [['payee_user_name','ufxfuiou_account'], 'string', 'max' => 200],
            [['remark'], 'string', 'max' => 512],
            [['is_need_review', 'is_notify'], 'string', 'max' => 2],
            [['pur_tran_num'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_tran_num' => 'Pur Tran Num',
            'ufxfuiou_tran_num' => 'Ufxfuiou Tran Num',
            'pay_status' => 'Pay Status',
            'tran_money' => 'Tran Money',
            'payee_card_number' => 'Payee Card Number',
            'payee_user_name' => 'Payee User Name',
            'payee_phone_number' => 'Payee Phone Number',
            'bank_code' => 'Bank Code',
            'city_code' => 'City Code',
            'remark' => 'Remark',
            'is_need_review' => 'Is Need Review',
            'branch_bank' => 'Branch Bank',
            'is_notify' => 'Is Notify',
            'create_time' => 'Create Time',
            'create_user_name' => 'Create User Name',
            'create_ip' => 'Create Ip',
            'status' => 'Status',
            'charge' => 'Charge',
            'ufxfuiou_account'=>'Ufxfuiou Account',
            'payee_id_number'=>'Payee Id Number',
        ];
    }

    public static function savePayDetail($fuiouPayDatas,$fuiouPay){
        $model = new self();
        $model -> pur_tran_num = isset($fuiouPay['tran']) ? $fuiouPay['tran'] : '';
        $model -> ufxfuiou_tran_num = isset($fuiouPay['responseBody']['fuiouTransNo']) ? is_array($fuiouPay['responseBody']['fuiouTransNo'] ) ? implode(',',$fuiouPay['responseBody']['fuiouTransNo']) :$fuiouPay['responseBody']['fuiouTransNo']: '';
        $model -> pay_status = '5002';//默认状态已受理
        $model -> tran_money = isset($fuiouPayDatas['Fuiou']['amt']) ? $fuiouPayDatas['Fuiou']['amt']*100 :0;
        $model -> payee_card_number = isset($fuiouPayDatas['Fuiou']['bankCardNo']) ? $fuiouPayDatas['Fuiou']['bankCardNo'] :'';
        $model -> payee_user_name = isset($fuiouPayDatas['Fuiou']['oppositeName']) ? $fuiouPayDatas['Fuiou']['oppositeName'] :'';
        $model -> payee_id_number= isset($fuiouPayDatas['Fuiou']['oppositeIdNo']) ? $fuiouPayDatas['Fuiou']['oppositeIdNo'] :'';
        $model -> payee_phone_number = isset($fuiouPayDatas['Fuiou']['oppositeMobile']) ? $fuiouPayDatas['Fuiou']['oppositeMobile'] :'';
        $model -> bank_code = isset($fuiouPayDatas['Fuiou']['bankNo']) ? $fuiouPayDatas['Fuiou']['bankNo'] :'';
        $model -> city_code = isset($fuiouPayDatas['Fuiou']['cityNo']) ? $fuiouPayDatas['Fuiou']['cityNo'] :'';
        $model -> remark = isset($fuiouPayDatas['Fuiou']['remark']) ? $fuiouPayDatas['Fuiou']['remark'] :'';
        $model -> is_need_review = isset($fuiouPayDatas['Fuiou']['is_need_review']) ? $fuiouPayDatas['Fuiou']['is_need_review'] :'02';
        $model -> branch_bank = isset($fuiouPayDatas['Fuiou']['bankId']) ? $fuiouPayDatas['Fuiou']['bankId'] :'';
        $model -> is_notify = isset($fuiouPayDatas['Fuiou']['isNotify']) ? $fuiouPayDatas['Fuiou']['isNotify'] :'02';
        $model -> charge = isset($fuiouPayDatas['Fuiou']['charge']) ? $fuiouPayDatas['Fuiou']['charge'] :'02';
        $model -> create_time = date('Y-m-d H:i:s',time());
        $model -> create_user_name = Yii::$app->user->identity->username;
        $model -> create_ip = Yii::$app->request->userIP;
        $model -> ufxfuiou_account = isset($fuiouPayDatas['Fuiou']['PayAccount']) ?$fuiouPayDatas['Fuiou']['PayAccount'] :'';
        $model -> status = 13;
        return $model->save();
    }
}
