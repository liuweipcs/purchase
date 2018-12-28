<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%purchase_demand}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $demand_number
 */
class PurchaseDemand extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_demand}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number'], 'required'],
            [['pur_number', 'demand_number'], 'string', 'max' => 100],
            [['create_id'], 'string', 'max' => 50],
            [['confirm_number'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pur_number' => Yii::t('app', '采购单号'),
            'demand_number' => Yii::t('app', '需求单号'),
        ];
    }

    public function getPlatformSummary(){
        return $this->hasOne(PlatformSummary::className(),['demand_number'=>'demand_number']);
    }
    public function getPurchaseOrder(){
        return $this->hasOne(PurchaseOrder::className(),['pur_number'=>'pur_number']);
    }
    /**
     * 保存一条数据
     * @param $data
     */
    public static function saveOne($pur_number,$data)
    {
        foreach($data as $v)
        {
            $model =self::find()->where(['pur_number'=>$pur_number,'demand_number'=>$v['demand_number']])->one();
            if($model)
            {
                continue;
            } else{
                $model                = new self;
                $model->pur_number    = $pur_number;
                $model->demand_number = $v['demand_number'];
                $model->create_id     = $v['create_id'];
                $model->create_time   = $v['create_time'];
                $model->save(false);
            }

        }

    }

    /**
     * 采购单作废 采购单与需求单关系删除 需求单重新推送
     * @param $pur_number
     * @throws \Exception
     * @throws \Throwable
     */
    public static  function  UpdateOne($pur_number)
    {
        $demand = PurchaseDemand::find()->where(['in','pur_number',$pur_number])->all();

        if($demand)
        {
            foreach ($demand as $b)
            {
                //PlatformSummary::updateAll(['is_push' =>0], ['demand_number' => $b->demand_number]);
                $findone = PurchaseDemand::find()->where(['pur_number' =>$pur_number])->one();
                if ($findone)
                {
//                    $findone->delete();
                }
            }
        }
    }
    /**
     * 增加确认数量
     * @param $data
     * @return bool
     */
    public function saveConfirmNumber($data)
    {
        if(!empty($data))
        {
            foreach($data as $v)
            {
                $models  = self::find()->where(['demand_number'=>$v['demand_number']])->one();

                if($models)
                {
                    $models->demand_number     = $v['demand_number'];
                    $models->confirm_number    = $v['confirm_number'];
                    $status                    = $models->save(false);
                } else {
                    $model                     = new self;
                    $model->demand_number      = $v['demand_number'];
                    $model->confirm_number     = $v['confirm_number'];
                    $status                    = $model->save(false);
                }
            }
            return $status;
        }
    }

    /**
     * 获取确认数量
     * @param $demand_number
     * @return int|mixed
     */
    public static function getConfirmNumber($demand_number, $ctq=null) {
        $findone = PurchaseDemand::find()->where(['demand_number' =>$demand_number])->one();
        $ctq = !empty($ctq) ? ("<span style='color: red'>单个sku总数量：</span>" . $ctq) : '';
        if (empty($findone)) {
            return $ctq;
        } else {
            return !empty($findone['confirm_number'])?$findone['confirm_number']:(!empty($ctq) ? $ctq : '');
        }
    }
    /**
     * FBA采购单作废 采购单与需求单关系删除 需求单重新推送
     */
    public static  function  deleteRelation($pur_number)
    {
        $demand = PurchaseDemand::find()->where(['in','pur_number',$pur_number])->all();

        if($demand)
        {
            foreach ($demand as $b)
            {
                //PlatformSummary::updateAll(['is_push' =>0], ['demand_number' => $b->demand_number]);
                $findone = PurchaseDemand::find()->where(['pur_number' =>$pur_number])->one();
                if ($findone)
                {
                   $findone->delete();
                }
            }
        }
    }
}
