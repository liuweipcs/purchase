<?php

namespace app\synchcloud\models;

use Yii;


class CloudsModel extends yii\base\Model  {

    /**
     * db config key
     *  
     * @return string
     */
    public function getDbKey() {
        return 'db_k3cloud';
    }

}