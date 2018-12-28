<?php
namespace app\models;

use app\models\base\BaseModel;
use Yii;
class InformMessage extends BaseModel
{

    public $start_time;
    public $end_time;

    public function rules()
    {
        return [
            [[
                'pur_number',
                'sku',
                'history_intock',
                'purchase_total',
                'message',
                'status',
                'create_time',
                'confirm_time',
                'inform_user',
                'intock_num',
                'excep_num',
                'normal_num',
                'reason'
            ], 'safe']
        ];
    }

    public function formName()
    {
        return '';
    }

}
