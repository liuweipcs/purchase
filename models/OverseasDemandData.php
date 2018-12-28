<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

class OverseasDemandData extends PlatformSummary
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[], 'required'],
            [[], 'safe'],
        ];
    }
}
