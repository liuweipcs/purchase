<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use app\services\BaseServices;

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
class OverseasBasicTactics extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_overseas_basic_tactics';
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
            $log_data = BaseServices::getStrData($val);

            if($model){
                //更新
                Yii::$app->db->createCommand()->update($this->tableName(), $val, $map)->execute();
                //表修改日志-更新
                $change_data = [
                    'table_name' => 'pur_overseas_basic_tactics', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => "update:{$log_data}", //变更内容
                ];
                TablesChangeLog::addLog($change_data);
            }else{
                //插入
                Yii::$app->db->createCommand()->insert($this->tableName(), $val)->execute();

                //表修改日志-新增
                $change_content = "insert:{$log_data}";
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
            }
        }
    }

}
