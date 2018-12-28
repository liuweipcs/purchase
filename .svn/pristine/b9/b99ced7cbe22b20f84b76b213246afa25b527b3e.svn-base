<?php
namespace app\models;

use app\models\base\BaseModel;
use yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use app\config\Vhelper;
class ProductTicketedPointLog extends BaseModel
{
    //添加一条记录
    public static function insertLog($sku, $point, $is_back_tax) {
        $model = new self;
        $model->sku = $sku;
        $model->pur_ticketed_point = $point;
        $model->create_time = date('Y-m-d H:i:s');
        $model->is_back_tax = $is_back_tax;
        $model->is_push = 0;
        return $model->save(false);
    }
}