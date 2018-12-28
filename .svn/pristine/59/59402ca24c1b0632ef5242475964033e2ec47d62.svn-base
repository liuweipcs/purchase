<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%purchase_order_taxes}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property integer $is_taxes
 * @property string $taxes
 */
class PurchaseOrderTaxes extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_taxes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_taxes',], 'required'],
            [['id', 'is_taxes','create_id'], 'integer'],
            [['pur_number'], 'string', 'max' => 100],
            [['taxes'], 'string', 'max' => 30],
            [['pur_number'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'pur_number'  => Yii::t('app', '采购单号'),
            'is_taxes'    => Yii::t('app', '是否含税'),
            'taxes'       => Yii::t('app', '税率'),
            'sku'         => Yii::t('app', 'sku'),
            'create_id'   => Yii::t('app', '创建人'),
            'create_time' => Yii::t('app', '创建时间'),
        ];
    }
    /**
     * 增加税率
     * @param $data
     */
    public static function saveTax($data)
    {
        if($data['taxes'])
        {
            foreach($data['taxes'] as $v)
            {
                $models  = self::find()->where(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']])->one();

                if($models)
                {
                    $models->is_taxes    = !empty($data['is_taxes']) ? $data['is_taxes'] : '';
                    $models->taxes       = $v['taxes'];
                    $models->pur_number  = $v['pur_number'];
                    $models->sku         = $v['sku'];
                    $models->create_id   = Yii::$app->user->id;
                    $models->create_time = date('Y-m-d H:i:s',time());

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($models->attributes, $models->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order_taxes', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $status              = $models->save(false);
                } else {

                    $model              = new self;
                    $model->is_taxes    = !empty($data['is_taxes']) ? $data['is_taxes'] : '';
                    $model->taxes       = $v['taxes'];
                    $model->pur_number  = $v['pur_number'];
                    $model->sku         = $v['sku'];
                    $model->create_id   = Yii::$app->user->id;
                    $model->create_time = date('Y-m-d H:i:s',time());
                    $status             = $model->save(false);

                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$model->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_purchase_order_taxes', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                }
            }
            return $status;
        }

    }
    /**
     * 获取税率
     * @param string $pur_number
     * @return bool|mixed
     */
    public static function getTaxes($sku,$pur_number)
    {
        $models  = self::find()->where(['pur_number'=>$pur_number,'sku'=>$sku])->one();
        if (empty($models['is_taxes'])) {
            return 0;
        } else {
            return $models['taxes'];
        }
    }
    /**
     * 获取海外仓税率
     */
    public static function getABDTaxes($sku,$pur_number)
    {
        $models  = self::find()->where(['pur_number'=>$pur_number,'sku'=>$sku])->one();
        if (empty($models)) {
            $ticketed_point = ProductTaxRate::getTicketedPoint($sku);
            return $ticketed_point;
        } else {
            return $models['taxes'];
        }
    }
}
