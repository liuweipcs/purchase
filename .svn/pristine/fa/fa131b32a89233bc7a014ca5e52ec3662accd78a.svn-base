<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use app\services\BaseServices;
use Yii;

/**
 * This is the model class for table "{{%purchase_estimated_time}}".
 *
 * @property string $id
 * @property string $pur_number
 * @property string $sku
 * @property string $estimated_time
 * @property integer $purchase_type
 */
class PurchaseEstimatedTime extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_estimated_time}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'sku'], 'required'],
            [['estimated_time'], 'safe'],
            [['purchase_type'], 'integer'],
            [['pur_number'], 'string', 'max' => 20],
            [['sku'], 'string', 'max' => 30],
            [['pur_number', 'sku'], 'unique', 'targetAttribute' => ['pur_number', 'sku'], 'message' => 'The combination of Pur Number and Sku has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'Pur Number',
            'sku' => 'Sku',
            'estimated_time' => 'Estimated Time',
            'purchase_type' => 'Purchase Type',
        ];
    }
    /**
     * 增加sku预计到货时间
     * @param $data
     * @return bool
     */
    public function saveEstimatedTime($data,$purchase_type=1)
    {

        if(!empty($data))
        {
            foreach($data as $v)
            {
                $models  = self::find()->where(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']])->one();
                if($models)
                {
                    $models->pur_number     = $v['pur_number'];
                    $models->sku            = $v['sku'];
                    $models->estimated_time = $v['estimated_time'];
                    $models->purchase_type = !empty($v['purchase_type'])?$v['purchase_type']:$purchase_type;
                    $status                 = $models->save(false);
                } else {
                    $model                  = new self;
                    $model->pur_number      = $v['pur_number'];
                    $model->sku             = $v['sku'];
                    $model->estimated_time  = $v['estimated_time'];
                    $model->purchase_type = !empty($v['purchase_type'])?$v['purchase_type']:$purchase_type;
                    $status                 = $model->save(false);
                }
            }
            return $status;
        }
    }
    /**
     * 获取sku到货时间
     * @param string $pur_number
     * @return bool|mixed
     */
    public static function getEstimatedTime($sku,$pur_number)
    {
        $res = self::find()->where(['sku'=>$sku,'pur_number'=>$pur_number])->one();
        if (empty($res)) {
            return '';
        } else {
            return !empty($res->estimated_time) ? date('Y-m-d',strtotime($res->estimated_time)) : null;
        }
    }
    /**修改和新增备注
     * @param $data
     * @param null $purchase_type
     * @return bool|int
     */
    public static function updateNote($data,$purchase_type=null)
    {
        $data['creator'] = Yii::$app->user->identity->username;
        $data['create_time'] = date('Y-m-d H:i:s',time());
        if (!empty($purchase_type)) {
            $data['purchase_type'] = $purchase_type;
        }

        $exists = PurchaseEstimatedTime::find()->where(['sku'=>$data['sku']])->andWhere(['pur_number'=>$data['pur_number']])->exists();
        if ($exists) {
            $model=new PurchaseEstimatedTime();

            $str = BaseServices::getStrData($data);
            //表修改日志-更新
            $change_data = [
                'table_name' => 'pur_purchase_estimated_time', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => "update:sku:{$data['sku']},pur_number:{$data['pur_number']},{$str}", //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            $status = $model->updateAll($data,['sku'=>$data['sku'],'pur_number'=>$data['pur_number']]);
        } else {
            $models= new PurchaseEstimatedTime();
            $models->sku                 = $data['sku'];
            $models->pur_number           =$data['pur_number'];
            $models->note          =$data['note'];
            $models->creator        =$data['creator'];
            $models->create_time        =$data['create_time'];
            $status = $models->save();

            //表修改日志-新增
            $change_content = "insert:新增id值为{$models->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_estimated_time', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
        }
        return $status;
    }
    /**
     * 修改和新增采购到货日期
     * 单个修改
     */
    public static function saveArrivalDate($sku,$pur_number,$arrival_time,$is_warehouse=false)
    {
        $create_time = date('Y-m-d H:i:s', time());

        if ($is_warehouse) {
            $creator = '仓库';
        } else {
            $creator = Yii::$app->user->identity->username;
        }
        

        $model = PurchaseEstimatedTime::findOne(['sku'=>$sku,'pur_number'=>$pur_number]);
        if (!empty($model)) {
            //修改
            $model->estimated_time = empty($arrival_time) ? null : $arrival_time;
            $model->operation_count = empty($is_warehouse) ? ($model->operation_count +1) : 2;
            $model->create_time = $create_time;
            $model->creator = $creator;
        } else {
            //新增
            $model = new PurchaseEstimatedTime();
            $model->sku = $sku;
            $model->pur_number = $pur_number;
            $model->estimated_time = empty($arrival_time) ? null : $arrival_time;
            $model->purchase_type = 2;
            $model->create_time = $create_time;
            $model->creator = $creator;
            $model->operation_count = empty($is_warehouse) ? 1 : 2;
        }
        $status = $model->save(false);
        return $status;
    }
    /**
     * 获取操作次数
     */
    public static function getOperationCount($sku,$pur_number)
    {
        return self::find()->select('operation_count')->where(['sku'=>$sku,'pur_number'=>$pur_number])->scalar();
    }
}
