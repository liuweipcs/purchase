<?php

namespace app\api\v1\models;

use app\models\PurchaseOrderPayType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the model class for table "{{%purchase_order_ship_log}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $note
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $cargo_company_id
 * @property string $express_no
 * @property string $freight
 * @property string $pay_number
 */
class PurchaseOrderShip extends \yii\db\ActiveRecord
{
   
    public static function tableName()
    {
        return '{{%purchase_order_ship}}';
    }

    public static function saveData($pur_number,$expressCompany,$expressNo){
        $model = self::find()->where(['pur_number'=>$pur_number,'express_no'=>$expressNo,'cargo_company_id'=>$expressCompany])->one();
        if(empty($model)){
            $model = new self();
        }
        $model->pur_number = $pur_number;
        $model->express_no = $expressNo;
        $model->cargo_company_id = $expressCompany;
        $model->create_user_id  = 1;
        $model->purchase_type = (strpos($pur_number,'ABD') !== false) ? 2 : ( (strpos($pur_number,'FBA') !== false) ? 3 :1);
        $model->create_time = date('Y-m-d H:i:s',time());
        return $model->save();
    }
}
