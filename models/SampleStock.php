<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "pur_sample_stock".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $on_way_stock
 * @property integer $available_stock
 * @property string $warehouse_code
 */
class SampleStock extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sample_stock';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku'], 'required'],
            [['on_way_stock', 'available_stock'], 'integer'],
            [['sku'], 'string', 'max' => 100],
            [['warehouse_code'], 'string', 'max' => 255],
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
            'on_way_stock' => 'On Way Stock',
            'available_stock' => 'Available Stock',
            'warehouse_code' => 'Warehouse Code',
        ];
    }

    public static function saveStock($sku,$onWay=0,$available=0,$warehouse_code,$stock=0){
        $exist = self::find()->andWhere(['sku'=>$sku,'warehouse_code'=>$warehouse_code])->exists();
        if($exist){

            //表修改日志-更新
            $change_data = [
                'table_name' => 'pur_sample_stock', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => "update:sku:{$sku},warehouse_code:=>{$warehouse_code}", //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            self::updateAllCounters(['on_way_stock'=>$onWay,'available_stock'=>$available,'real_stock'=>$stock],['sku'=>$sku,'warehouse_code'=>$warehouse_code]);
        }else{
            Yii::$app->db->createCommand()->insert(self::tableName(),[
                'sku'               =>$sku,
                'on_way_stock'      =>$onWay,
                'available_stock'   =>$available,
                'warehouse_code'    =>$warehouse_code,
                'real_stock'    =>$stock
            ])->execute();

            $id = Yii::$app->db->getLastInsertID();
            //表修改日志-新增
            $change_content = "insert:新增id值为{$id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
        }
    }
}
