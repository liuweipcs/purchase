<?php
namespace app\models;

use app\models\base\BaseModel;
use yii;
use yii\base\Model;

class DemandLog extends BaseModel
{
    public function rules()
    {
        return [
            [['demand_number','pur_number','message','update_data','operator','operate_time'], 'safe'],
        ];
    }
}