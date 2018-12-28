<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%return_goods}}".
 *
 * @property string $id
 * @property string $pur_number
 * @property string $return_number
 * @property string $supplier_code
 * @property string $supplier_name
 * @property integer $qty
 * @property string $sku
 * @property string $pro_name
 * @property string $create_user
 * @property integer $create_time
 * @property string $buyer
 * @property integer $state
 */
class ReturnGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%return_goods}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['qty', 'create_time', 'state', 'cargo_company_id'], 'integer'],
            [['pur_number', 'supplier_code', 'sku', 'create_user', 'return_number', 'express_no'], 'string', 'max' => 50],
            [['supplier_name'], 'string', 'max' => 30],
            [['pro_name'], 'string', 'max' => 100],
            [['buyer', 'cargo_company'], 'string', 'max' => 20],
            [['freight','note'], 'safe'],
        ];
    }
    public static function FindOnes($data)
    {

        $datas =[];
        if(is_array($data))
        {
            foreach ($data as $k => $v)
            {
                $model = self::find()->where(['return_number' => $v['return_number']])->one();
                if (!empty($model))
                {
                    $model->express_no    = $v['express_no'];
                    $model->freight       = $v['freight'];
                    $model->cargo_company = $v['cargo_company'];
                    $model->state         = 2;
                    $model->save();
                    $datas['success_list'][] = $model->attributes['return_number'];
                    $datas['failure_list'][] = '';
                } else {

                    continue;
                }

            }
        } else {
            $datas='不是数组';
        }

        return $datas;
    }


}
