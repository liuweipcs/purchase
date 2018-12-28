<?php

namespace app\models;

use app\models\base\BaseModel;
use app\config\Vhelper;
use app\services\BaseServices;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the model class for table "{{%purchase_note}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $note
 * @property string $purchase_type
 */
class PurchaseNote extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_note}}';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],

                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => date('Y-m-d H:i:s',time()),
            ],
            [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_id'],

                ],
                'value' => Yii::$app->user->id,
            ],


        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number','note'], 'required'],
            [['id','purchase_type'], 'integer'],
            [['pur_number'], 'string', 'max' => 50],
            [['note'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => '采购单号',
            'note' => '备注',
        ];
    }

    /**
     * 更新备注
     * @param $data
     * @return bool
     */
    public function saveNotes($data)
    {
        foreach($data as $v)
        {
            $pur = self::findOne(['pur_number'=>$v['pur_number']]);
            if($pur)
            {
                $pur->note = $v['note'];

                //表修改日志
                $change_content = TablesChangeLog::updateCompare($pur->attributes, $pur->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_note', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                $status    = $pur->save(false);
            } else{
                $status=$this->saveNote($v);
            }
        }

        return $status;
    }

    /**
     * 增加备注
     * @param $data
     * @return bool
     */
    public function saveNote($data)
    {
        $model                = new self;
        $model->note          = $data['note'];
        $model->pur_number    = $data['pur_number'];
        $model->purchase_type = Vhelper::getNumber($data['pur_number']);
        $status               = $model->save(false);

        //表修改日志-新增
        $change_content = "insert:新增id值为{$model->id}的记录";
        $change_data = [
            'table_name' => 'pur_purchase_note', //变动的表名称
            'change_type' => '1', //变动类型(1insert，2update，3delete)
            'change_content' => $change_content, //变更内容
        ];
        TablesChangeLog::addLog($change_data);

        return $status;
    }
    public  static  function  getNote($pur_number)
    {
        $model = self::find()->select('note,create_id')->where(['pur_number'=>$pur_number])->asArray()->all();

        $str ='';
        if($model)
        {

            foreach($model as $v)
            {
                $str .="\r\n".BaseServices::getEveryOne($v['create_id']).':'.$v['note'];
            }
        }
        return $str;

    }
    /**
     * 海外仓-采购单
     * 付款通知
     * 出纳付款
     */
    public static function getPurchaseNote($id)
    {
        $is_hetong = strstr($id,'-');
        if ($is_hetong == false) {
            # 采购单号
            $compactItems = PurchaseCompactItems::find()->select('compact_number')->where(['pur_number'=>$id,'bind'=>1])->asArray()->all();
            if (!empty($compactItems)) {
                $pur_numbers = array_column($compactItems, 'compact_number');
                array_push($pur_numbers, $id);
            } else {
                $pur_numbers[] = $id;
            }
        } else {
            #合同号
            $compactItems = PurchaseCompactItems::find()->select('pur_number')->where(['compact_number'=>$id,'bind'=>1])->asArray()->all();
            if (!empty($compactItems)) {
                $pur_numbers = array_column($compactItems, 'pur_number');
                array_push($pur_numbers, $id);
            } else {
                $pur_numbers[] = $id;
            }
        }
        return PurchaseNote::find()->where(['in', 'pur_number', $pur_numbers])->all();
    }
}
