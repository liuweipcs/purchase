<?php
namespace app\models;

use app\models\base\BaseModel;
use yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
class Address extends Model
{

    public function getProvinceList()
    {
        $sql = "select * from pur_provincial";
        $db = Yii::$app->db;
        $data = $db->createCommand($sql)->queryAll();
        return ArrayHelper::map($data, 'id', 'name');
    }

    public function getCityByPid($pid)
    {
        $sql = "select id, name from pur_city where pid = {$pid} order by sort asc";
        $db = Yii::$app->db;
        return $db->createCommand($sql)->queryAll();
    }

    public function getProvinceName($id)
    {
        $sql = "select name from pur_provincial where id = {$id}";
        $db = Yii::$app->db;
        return $db->createCommand($sql)->queryScalar();
    }







}