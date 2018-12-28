<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%data_control_config}}".
 *
 * @property string $id
 * @property string $type
 * @property string $values
 * @property string $remark
 */
class DataControlConfig extends BaseModel
{
    public static $mrp_warehouse_he=[];
    public static $mrp_warehouse_main=[];
    public static $mrp_warehouse_he_stock=[];
    public static $po_demand_import_warehouse=[];
    public static $mrp_suggest_view_warehouse=[];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%data_control_config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'values'], 'required'],
            [['type'], 'string', 'max' => 100],
            [['values', 'remark'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'values' => Yii::t('app', 'Values'),
            'remark' => Yii::t('app', 'Remark'),
        ];
    }
    /**
     * 根据仓库列表运行多仓库MRP
     */
    public static function getMrpWarehouseList()
    {
        $list = self::findOne(['type' => 'mrp_warehouse_list']);

        if($list){
            $list_arr = explode(',',$list['values']);
            return $list_arr;
        }
        return [];
    }
    /**
     * 获取合仓数据
     */
    public static function getMrpWarehouseHe()
    {
        $return_res = [];
        if(empty(self::$mrp_warehouse_he)){
            $mrp_warehouse_he = DataControlConfig::findOne(['type' => 'mrp_warehouse_he']);
            if (!empty($mrp_warehouse_he)) {
                $return_res = explode(',',$mrp_warehouse_he['values']);
            }
            self::$mrp_warehouse_he = $return_res;
        }

        return self::$mrp_warehouse_he;
    }
    /**
     * 获取合仓数据-主仓
     */
    public static function getMrpWarehouseMain()
    {
        $return_res = [];
        if (empty(self::$mrp_warehouse_main)) {
            $mrp_warehouse_main = DataControlConfig::findOne(['type' => 'mrp_warehouse_main']);
            if (!empty($mrp_warehouse_main)) {
                $return_res = explode(',',$mrp_warehouse_main['values']);
            }
            self::$mrp_warehouse_main = $return_res;
        }

        return self::$mrp_warehouse_main;
    }
    /**
     * 获取合仓数据-在途、在库、缺货合并
     */
    public static function getMrpWarehouseHeStock()
    {
        $return_res = [];
        if (empty(self::$mrp_warehouse_he_stock)) {
            $mrp_warehouse_he_stock = DataControlConfig::findOne(['type' => 'mrp_warehouse_he_stock']);
            if (!empty($mrp_warehouse_he_stock)) {
                $return_res = explode(',',$mrp_warehouse_he_stock['values']);
            }
            self::$mrp_warehouse_he_stock = $return_res;
        }

        return self::$mrp_warehouse_he_stock;
    }
    /**
     * 获取根据缺货列表跑出的销量汇总->采购建议-缺货列表仓库
     */
    public static function getMrpWarehouseStockList()
    {
        $return_res = [];
        $mrp_warehouse_list = self::getMrpWarehouseList(); //仓库列表
        $mrp_warehouse_main = self::getMrpWarehouseMain(); //主仓
        $mrp_warehouse_he_stock = self::getMrpWarehouseHeStock(); //合仓-在途在库缺货合并

        $return_res = array_unique(array_merge($mrp_warehouse_list,$mrp_warehouse_main, $mrp_warehouse_he_stock));
        return $return_res;
    }
    /**
     * 获取国内仓-采购单需求导入的仓库
     */
    public static function getPoDemandImportWarehouse()
    {
        $return_res = [];
        if (empty(self::$po_demand_import_warehouse)) {
            $po_demand_import_warehouse = DataControlConfig::findOne(['type' => 'po_demand_import_warehouse']);
            if (!empty($po_demand_import_warehouse)) {
                $return_res = explode(',',$po_demand_import_warehouse['values']);
            }
            self::$po_demand_import_warehouse = $return_res;
        }

        return self::$po_demand_import_warehouse;
    }
    /**
     * 获取国内仓-Mrp采购建议查看仓库
     */
    public static function getMrpSuggestViewWarehouse()
    {
        $return_res = [];
        if (empty(self::$mrp_suggest_view_warehouse)) {
            $mrp_suggest_view_warehouse = DataControlConfig::findOne(['type' => 'mrp_suggest_view_warehouse']);
            if (!empty($mrp_suggest_view_warehouse)) {
                $return_res = explode(',',$mrp_suggest_view_warehouse['values']);
            }
            self::$mrp_suggest_view_warehouse = $return_res;
        }

        return self::$mrp_suggest_view_warehouse;
    }
}
