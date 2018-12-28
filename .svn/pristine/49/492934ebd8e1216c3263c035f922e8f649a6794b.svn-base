<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_company_holder_capital".
 *
 * @property integer $id
 * @property string $amomon
 * @property string $time
 * @property string $percent
 * @property string $paymet
 * @property string $holder_id
 * @property integer $status
 * @property string $update_time
 */
class CompanyHolderCapital extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_company_holder_capital';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['update_time'], 'safe'],
            [['amomon','holder_id', 'time', 'percent', 'paymet'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'amomon' => 'Amomon',
            'time' => 'Time',
            'percent' => 'Percent',
            'paymet' => 'Paymet',
            'holder_id' => 'Holder ID',
            'status' => 'Status',
            'update_time' => 'Update Time',
        ];
    }
    public static function saveData($holderId,$datas){
        Yii::$app->db->createCommand()->update(self::tableName(),[
            'status'=>0,
            'update_time'=>date('Y-m-d H:i:s')],
            ['holder_id'=>$holderId,'status'=>1]
        )->execute();
        if(!empty($datas)){
            foreach ($datas as $v){
                Yii::$app->db->createCommand()->insert(self::tableName(),
                    [
                        'amomon'=>isset($v['amomon']) ? $v['amomon'] :'',
                        'time'=>isset($v['time']) ? $v['time'] :'',
                        'percent'=>isset($v['percent']) ? $v['percent'] : '',
                        'paymet'=>isset($v['paymet']) ? $v['paymet'] :'',
                        'holder_id'=>$holderId,
                        'status'=>1,
                        'update_time'=>date('Y-m-d H:i:s',time())
                    ]
                    )->execute();
            }
        }
    }
}
