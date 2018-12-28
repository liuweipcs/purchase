<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_order_account}}".
 *
 * @property string $id
 * @property string $pur_number
 * @property string $account
 * @property integer $purchase_type
 */
class PurchaseOrderAccount extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'account'], 'required'],
            [['purchase_type'], 'integer'],
            [['pur_number'], 'string', 'max' => 20],
            [['account'], 'string', 'max' => 100],
            [['pur_number', 'account'], 'unique', 'targetAttribute' => ['pur_number', 'account'], 'message' => 'The combination of Pur Number and Account has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'Pur Number',
            'account' => '账号',
            'purchase_type' => 'Purchase Type',
        ];
    }
    /**
     * 增加账号
     * @param $data
     * @return bool
     */
    public function saveOrderAccount($data,$purchase_type=1)
    {
        if(!empty($data))
        {
            foreach($data as $v)
            {
                $models  = self::find()->where(['pur_number'=>$v['pur_number']])->one();


                if($models)
                {
                    $models->pur_number     = $v['pur_number'];
                    $models->account = $v['account'];
                    $models->purchase_type  = !empty($v['purchase_type'])? $v['purchase_type'] : $purchase_type;
                    $status                 = $models->save(false);
                } else {
                    $model                  = new self;
                    $model->pur_number      = $v['pur_number'];
                    $model->account         = $v['account'];
                    $model->purchase_type   = !empty($v['purchase_type'])? $v['purchase_type'] : $purchase_type;
                    $status                 = $model->save(false);
                }
            }
            return $status;
        }
    }

    /**
     * 获取账号
     * @param string $pur_number
     * @return bool|mixed
     */
    public static function getOrderAccount($pur_number)
    {
        $res = self::find()->where(['pur_number'=>$pur_number])->one();
        if (empty($res)) {
            return false;
        } else {
            return $res['account'];
        }
    }
}
