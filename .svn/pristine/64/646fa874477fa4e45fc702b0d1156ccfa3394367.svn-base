<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_stock".
 *
 * @property string $time
 * @property string $end_time
 * @property string $num
 * @property string $type

 */
class SupplierNum extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_num';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [['time','end_time','num','type'], 'safe'],

        ];
    }

}
