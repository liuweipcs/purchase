<?php

namespace app\api\v1\controllers;

use Yii;
use yii\helpers\Json;
use app\models\PurchaseTactics;
use app\models\PurchaseTacticsSuggest;
use app\models\PurchaseTacticsDailySales;
use app\models\PurchaseTacticsWarehouse;

class PurchaseTacticsConfigController extends BaseController
{

    /**
     * 查询 备货策略 配置数据
     * @return array|string
     */
    public function  actionGetMrpConfig()
    {

        // 只查询 启用状态的备货策略
        $model_tactics_list = PurchaseTactics::findAll(['status' => 1]);
        $tacticsList = [];
        foreach($model_tactics_list as $list_value){

            $tactics = $list_value->attributes;

            // 备货策略 仓库配置数据
            $model_tactics_warehouse_list = $list_value->purchaseTacticsWarehouse;
            foreach($model_tactics_warehouse_list as $list_tactics_warehouse){
                $tactics_warehouse      = $list_tactics_warehouse->attributes;
                $tactics['warehouse'][] = $tactics_warehouse;
            }

            // 备货策略 日均销量数据
            $model_tactics_daily_sales_list = $list_value->purchaseTacticsDailySales;
            foreach($model_tactics_daily_sales_list as $list_tactics_daily_sales){
                $tactics_daily_sales        = $list_tactics_daily_sales->attributes;
                $tactics['daily_sales'][]   = $tactics_daily_sales;
            }

            // 备货策略 采购建议逻辑数据
            $model_tactics_daily_suggest = $list_value->purchaseTacticsSuggest;
            foreach($model_tactics_daily_suggest as $list_tactics_suggest){
                $tactics_suggest        = $list_tactics_suggest->attributes;
                $tactics['suggest'][]   = $tactics_suggest;
            }

            // 整合所有数据
            $tacticsList[$tactics['id']] = $tactics;

        }

        $param = ['status' => 'success','data' => $tacticsList,'message' => ''];
        echo Json::encode($param);
        exit;
    }


    /**
     * 查询 备货策略 仓库 配置数据
     * @return array|string
     */
    public function  actionGetMrpWarehouseConfig()
    {
        // 只查询启用状态的备货策略
        $model_tactics_list = PurchaseTactics::findAll(['status' => 1]);
        $warehouseList = [];
        foreach($model_tactics_list as $list_value){
            $tactics = $list_value->attributes;

            // 查找 仓库配置数据
            $model_tactics_warehouse_list = $list_value->purchaseTacticsWarehouse;
            foreach($model_tactics_warehouse_list as $list_tactics_warehouse){
                $tactics_warehouse                  = $list_tactics_warehouse->attributes;
                unset($tactics_warehouse['id']);

                $tactics_warehouse['reserved_max']          = $tactics['reserved_max'];// 大单配置[保留最大值]
                $tactics_warehouse['tactics_name']          = $tactics['tactics_name'];// 备货名称
                $tactics_warehouse['single_price']          = $tactics['single_price'];// 配置新品备货[单价]
                $tactics_warehouse['inventory_holdings']    = $tactics['inventory_holdings'];// 库存持有量
                $tactics_warehouse['sales_sd_value_range']              = $tactics['sales_sd_value_range'];// 销量标准差取值范围
                $tactics_warehouse['lead_time_value_range']             = $tactics['lead_time_value_range'];// 提前期取值范围
                $tactics_warehouse['weight_avg_period_value_range']     = $tactics['weight_avg_period_value_range'];// 权均交期取值范围

                $warehouseList[$tactics_warehouse['warehouse_code']] = $tactics_warehouse;
            }
        }

        $param = ['status' => 'success','data' => $warehouseList,'message' => ''];
        echo Json::encode($param);
        exit;
    }

}
