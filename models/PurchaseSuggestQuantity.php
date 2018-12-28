<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%purchase_suggest_quantity}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $platform_number
 * @property integer $purchase_quantity
 * @property string $purchase_warehouse
 * @property string $create_id
 * @property string $create_time
 * @property integer $suggest_status
 * @property string $sales_note
 * @property integer $purchase_type
 */
class PurchaseSuggestQuantity extends BaseModel
{
    public $file_execl;
    public $end_time;
    public $start_time;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_suggest_quantity}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'create_id', 'create_time'], 'required'],
            [['purchase_quantity', 'suggest_status', 'purchase_type'], 'integer'],
            [['create_time'], 'safe'],
            [['sku', 'purchase_warehouse', 'create_id'], 'string', 'max' => 30],
            [['platform_number'], 'string', 'max' => 20],
            [['sales_note'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'platform_number' => 'Platform Number',
            'purchase_quantity' => 'Purchase Quantity',
            'purchase_warehouse' => 'Purchase Warehouse',
            'create_id' => 'Create ID',
            'create_time' => 'Create Time',
            'suggest_status' => 'Suggest Status',
            'sales_note' => 'Sales Note',
            'purchase_type' => 'Purchase Type',
        ];
    }
    /**
     * 该采购建议的建议数量是否含导入的数据量
     */
    public static function isExportQuantity($sku, $warehouse_code)
    {
        //主仓
        $main_warehouse = DataControlConfig::getMrpWarehouseMain();
        //合仓
        $he_warehouse = DataControlConfig::getMrpWarehouseHe();

        if (!empty($main_warehouse) && !empty($he_warehouse) && in_array($warehouse_code, $main_warehouse)) {
            # 如果此仓库是主仓
            $warehouse_code = array_unique(array_merge($main_warehouse, $he_warehouse));
        }

        $status = self::find()
            ->select('id')
            ->where(['sku'=>$sku])
            ->andWhere(['in', 'purchase_warehouse', $warehouse_code])
            ->andWhere(['between','create_time',date('Y-m-d 00:00:00',time()-86400),date('Y-m-d 23:59:59',time()-86400)])
            ->asArray()->one();
        if (!empty($status)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 经审核后，仓库导入的需求改变状态
     */
    public function updateSuggestStatus($data)
    {

        foreach ($data as $v)
        {
            $model = self::findOne(['sku' =>$v['sku'],'purchase_warehouse'=> $v['warehouse_code'],'suggest_status'=>1,'purchase_type'=>5]);
            if (empty($model)) {
                $status = false;
            } else {
                $model->suggest_status = 2;

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_suggest_quantity', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                $status = $model->save(false);
            }
        }
        return $status;
    }
}
