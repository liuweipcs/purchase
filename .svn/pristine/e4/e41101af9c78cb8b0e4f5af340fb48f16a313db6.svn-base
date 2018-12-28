<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "pur_basic_tactics".
 *
 * @property string $id
 * @property string $type
 * @property double $days_3
 * @property double $days_7
 * @property double $days_14
 * @property double $days_30
 */
class BasicTactics extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_basic_tactics';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'required'],
            [['type'], 'string'],
            [['days_3', 'days_7', 'days_15', 'days_30'], 'number'],
            [['type'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '销售走势',
            'days_3' => '3天',
            'days_7' => '7天',
            'days_15' => '15天',
            'days_30' => '30天',
        ];
    }
    /**
     * @desc 循环插入数据
     * @param array $data 需要插入的数据
     */
    public function batchInsert($data) {
        foreach ($data as $val){
            $map['type']=$val['type'];
            $model = $this::find()->where($map)->one();
            if($this->Check($val['days_3'],$val['days_7'],$val['days_15'],$val['days_30']))
            {
                if ($model) {
                    //更新
                    Yii::$app->db->createCommand()->update($this->tableName(), $val, $map)->execute();
                    $day = $model->days_3 . '->' . $val['days_3'] . ';';
                    $day .= $model->days_7 . '->' . $val['days_7'] . ';';
                    $day .= $model->days_15 . '->' . $val['days_15'] . ';';
                    $day .= $model->days_30 . '->' . $val['days_30'];
                    $msg             = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '对id' . $model->id . '=====' . $day . '进行了更新';
                    $data['type']    = 8;
                    $data['pid']     = $model->id;
                    $data['module']  = '国内加权系数';
                    $data['content'] = $msg;
                    Vhelper::setOperatLog($data);
                } else {
                    //插入
                    $day = $val['days_3'] . ';';
                    $day .= $val['days_7'] . ';';
                    $day .= $val['days_15'] . ';';
                    $day .= $val['days_30'];
                    Yii::$app->db->createCommand()->insert($this->tableName(), $val)->execute();
                    $msg             = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '=====' . $day . '进行了插入';
                    $data['type']    = 8;
                    $data['pid']     = '';
                    $data['module']  = '国内加权系数';
                    $data['content'] = $msg;
                    Vhelper::setOperatLog($data);
                }
            } else{
                return false;
            }
        }
    }
    public  function  Check($day1,$day2,$day3,$day4)
    {
        $day = $day1+$day2+$day3+$day4;

        if($day<=1)
        {

            return true;
        }else{

            return false;
        }
    }

}
