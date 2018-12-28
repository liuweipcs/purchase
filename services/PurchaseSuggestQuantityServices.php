<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/9
 * Time: 13:43
 */

namespace app\services;


use app\config\Vhelper;
use app\models\DataControlConfig;

class PurchaseSuggestQuantityServices extends BaseServices
{
    /**
     * 采购建议状态：导入的需求是否使用过
     * @param null $type
     * @return array|mixed
     */
    public static function getSuggestStatus($type=null){
        $types = [
            '1' =>'<span class="label label-danger">'.'未使用过'.'</span>',
            '2' =>'<span class="label label-success">'.'使用过'.'</span>',
        ];
        return isset($type) ?  (!empty($types[$type])?$types[$type]:'未知状态') :$types;
    }

    /**
     * 采购建议状态：导入的需求是否使用过
     * @param null $type
     * @return array|mixed
     */
    public static function getSuggestStatusText($type=null){
        $types = [
            '1' =>'未使用过',
            '2' =>'使用过',
        ];
        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 采购建议查询时，所包含的仓库名称
     */
    public static function getSuggestWarehouseCode($type=null)
    {
        $warehouse_code = DataControlConfig::getMrpSuggestViewWarehouse();
        if (!empty($type)) {
            $warehouse_code = "'" . implode("','", $warehouse_code) . "'";
        }
        return $warehouse_code;

    }

    /**
     * 采购建议查询时，所包含的仓库名称
     */
    public static function getFbaSuggestWarehouseCode($type=null)
    {
        $warehouse_code = ['FBA_SZ_AA'];
        if (!empty($type)) {
            $warehouse_code = "'" . implode("','", $warehouse_code) . "'";
        }
        return $warehouse_code;

    }
}