<?php
namespace app\models;

use app\models\base\BaseModel;
use yii\db\Query;

/**
 * This is the model class for table "pur_purchase_tactics".
 */
class PurchaseTactics extends BaseModel
{
    public $warehouse_list;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_tactics}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tactics_name','warehouse_list'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tactics_name' => '备货名称',
            'warehouse_list' => '适用仓库',
            'prepare_new_products' => '配置新品备货',
            'inventory_holdings' => '库存持有量',
            'reserved_max' => '保留最大值',
            'daily_days' => '销量平均值',
            'daily_value' => '比值',
            'creator' => '创建人',
            'created_at' => '创建时间',
            'updateor' => '更新人',
            'update_at' => '更新时间',
            'status' => '是否启用',
            'operation' => '操作',
        ];
    }

    /**
     * 关联 采购建议逻辑 表 一对多
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseTacticsSuggest()
    {
        return $this->hasMany(PurchaseTacticsSuggest::className(),['tactics_id'=>'id']);
    }

    /**
     * 关联 适用仓库列 表 一对多
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseTacticsWarehouse()
    {
        return $this->hasMany(PurchaseTacticsWarehouse::className(),['tactics_id'=>'id']);
    }

    /**
     * 关联 销量平均值天数权值 表 一对多
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseTacticsDailySales()
    {
        return $this->hasMany(PurchaseTacticsDailySales::className(),['tactics_id'=>'id']);
    }
    /**
     * 查询获取配置值
     */
    public static function getStatisticsId($sku, $warehouseCode)
    {
        //sku备货逻辑:tactics_type：2sku补货策略，status：1启用
        $tacticsData = PurchaseTactics::find()
        ->alias('pt')
        ->leftJoin(PurchaseTacticsWarehouse::tableName().' ptw','ptw.tactics_id=pt.id')
        ->where(['sku'=>$sku,'tactics_type'=>2,'status'=>1])
        ->andWhere(['OR', "warehouse_code='$warehouseCode'", 'warehouse_code IS NULL'])
        ->one();

        $flag = false; //默认：跑仓库备货逻辑
        if (!empty($tacticsData)) {
            //填了开始和结束时间
            if (!empty($tacticsData->end_time) && !empty($tacticsData->start_time)) {
                $end_time = !empty($tacticsData->end_time)?strtotime($tacticsData->end_time):0;
                $start_time = !empty($tacticsData->end_time)?strtotime($tacticsData->start_time):0;
                //在时间范围内
                if ($start_time<=time() && time()<=$end_time) {
                    $flag = true;
                } else {
                    $flag = false;
                }

            } else {
                $flag = true;
            }
        } else {
            $flag = false;
        }

        //如果有数据 且 在备货时间段内
        if (!empty($tacticsData) && $flag== true) {
            //sku补货策略
            $tactics_id = $tacticsData['id']; //补货策略ID
        } else {
            //仓库补货策略
            $tacticsData = PurchaseTacticsWarehouse::find()
                ->select('t.tactics_id')
                ->alias('t')
                ->leftJoin(PurchaseTactics::tableName().' pt','t.tactics_id=pt.id')
                ->where(['t.warehouse_code'=>$warehouseCode,'pt.status'=>1])
                ->andwhere(['tactics_type'=>[0,1]])
                ->asArray()->orderBy('t.id DESC')->one();

            if (empty($tacticsData['tactics_id'])) return ['is_success'=>false, 'message'=>'无法获取配置值'];
            $tactics_id = $tacticsData['tactics_id']; //补货策略ID
        }

        //查询获取配置值
        $configInfo = PurchaseTacticsWarehouse::find()
            ->select('t.tactics_id, pt.weight_avg_period_value_range,pt.lead_time_value_range,pt.single_price,pt.inventory_holdings')
            ->alias('t')
            ->leftJoin(PurchaseTactics::tableName().' pt','t.tactics_id=pt.id')
            ->where(['pt.status'=>1])
            ->andWhere(['pt.id'=>$tactics_id]) //->where(['t.warehouse_code'=>$warehouseCode])
            ->asArray()->orderBy('t.id DESC')->one();
        if (empty($configInfo)) return ['is_success'=>false,'message'=>'无法获取配置值'];

        return['is_success'=>true, 'configInfo'=>$configInfo];
    }


    /**
     * 【SKU补货策略】验证 SKU 仓库 补货策略是否已经存在
     * @param string    $sku                SKU
     * @param array     $warehouse_list     仓库列表
     * @param int       $tactics_id         SKU补货策略ID
     * @return bool
     */
    public static function checkSkuWarehouseIsExists($sku,$warehouse_list,$tactics_id = 0){
        if(empty($sku) OR empty($warehouse_list)) return false;


        $list = (new Query())->select('p_p_t.id')
            ->from('pur_purchase_tactics as p_p_t')
            ->innerJoin('pur_purchase_tactics_warehouse as p_p_t_w','p_p_t_w.tactics_id=p_p_t.id')
            ->where("p_p_t.sku='$sku'")
            ->andWhere(['in','p_p_t_w.warehouse_code',$warehouse_list])
            ->andFilterWhere(['!=','p_p_t.id',$tactics_id])
            ->createCommand()
            ->queryAll();


        if($list) return true;

        return false;
    }


}
