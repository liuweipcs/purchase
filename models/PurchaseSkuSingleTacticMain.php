<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

class PurchaseSkuSingleTacticMain extends BaseModel
{

    public static function tableName()
    {
        return 'pur_sku_single_tactic_main';
    }
    public  function  getContent()
    {
        return $this->hasOne(SkuSingleTacticMainContent::className(),['single_tactic_main_id'=>'id']);
    }
}