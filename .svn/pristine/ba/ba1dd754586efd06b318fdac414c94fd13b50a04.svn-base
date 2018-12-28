<?php

namespace app\models;

use app\models\base\BaseModel;
use app\config\Vhelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the model class for table "{{%purchase_order_ship_log}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $note
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $cargo_company_id
 * @property string $express_no
 * @property string $freight
 * @property string $pay_number
 */
class PurchaseOrderShip extends BaseModel
{
    /**
     * @inheritdoc
     */
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
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_user_id'],
                ],
                'value' => Yii::$app->user->id,
            ],


        ];
    }
    public static function tableName()
    {
        return '{{%purchase_order_ship}}';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['express_no','pur_number','cargo_company_id','purchase_type'], 'required'],
            [['create_time','pay_number','note',], 'safe'],
            [['create_user_id'], 'integer'],
            [['express_no'], 'unique'],
            [['express_no'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'               => Yii::t('app', 'ID'),
            'pur_number'       => Yii::t('app', '采购订单号'),
            'note'             => Yii::t('app', '备注'),
            'create_time'      => Yii::t('app', '创建时间'),
            'create_user_id'   => Yii::t('app', '创建人'),
            'cargo_company_id' => Yii::t('app', '快递公司'),
            'express_no'       => Yii::t('app', '快递号'),
            'freight'          => Yii::t('app', '运费'),
            'pay_number'       => Yii::t('app', '支付单号'),
            'purchase_type'    => Yii::t('app', '采购类型'),
        ];
    }

    /**
     * 保存物流
     * @param $data
     * @return bool
     */
    public function saveShip($data)
    {

        foreach ($data as $v)
        {
            $models = self::find()->where(['pur_number'=>$v['pur_number']])->one();
            if($models)
            {

                if(!empty($v['freight']) && $models->freight != $v['freight']){
                    $models->e_freight= $v['freight'];
                }

                $models->note             = isset($v['note'])?$v['note']:'no';
                $models->freight          = !empty($v['freight'])?$v['freight']:'0';
                $status = $models->save(false);
            } else {
                $model = new self;
                $model->pur_number       = $v['pur_number'];
                $model->note             = isset($v['note'])?$v['note']:'no';
                $model->freight          = !empty($v['freight'])?$v['freight']:'0';

                if(!empty($v['freight'])){
                    $model->e_freight= $v['freight'];
                }

                $status = $model->save(false);
            }

        }
        return $status;
    }

    /**获取物流信息
     * @param $pur_number
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public static function getExpressNo($pur_number)
    {
        $ship_info = self::find()
            ->select(['express_no','cargo_company_id'])
            ->where(['pur_number'=>$pur_number])
            ->asArray()
            ->all();

        $data = '';
        if (!empty($ship_info)) {
            if (!empty($ship_info)) {
                foreach($ship_info as $key => $value) {
                    if(!empty($value['cargo_company_id'])) {
                        $data .= $value['express_no'] . '<br />';
                    }
                }
            }
        }
        return $data;
    }

}
