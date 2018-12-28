<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%purchase_demand}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $demand_number
 */
class PurchaseDemand extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_demand}}';
    }
    public function getPlatformSummary(){
        return $this->hasOne(PlatformSummary::className(),['demand_number'=>'demand_number']);
    }

    public  static  function getDemand($data)
    {
        $dats = [];
        if($data)
        {
            foreach($data as $v)
            {

                    $demand = self::find()->select('demand_number')->where(['pur_number'=>$v])->asArray()->all();
                    $status = PlatformSummary::updateAll(['is_push'=>0],['in','demand_number',$demand]);
                    $dats['success'][] =$v;
                    $dats['failure'][] ='';

            }
        }
        return $dats;

    }

}
