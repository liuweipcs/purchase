<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/11
 * Time: 14:06
 */

namespace app\services;

use app\config\HttpHelper;
use app\models\SkuSaleDetails;
use app\models\SkuSalesStatisticsTotalMrp;
use yii\helpers\ArrayHelper;

/**
 * sku销量统计
 * Class SkusalesServices
 * @package app\services
 */
class SkusalesServices
{
	 
    /**
     * 按日期同步销量统计数据
     * @param $saleDate
     * @return bool
     */
    public static function syncSkuSalesDetailsFromErp($saleDate, $skuSalesTableFlag=0)
    {
        $mainUrl = \Yii::$app->params['SKU_ERP_URL'];
        $requestUrlTpl = $mainUrl.'/services/api/caigou/syncskusalesdetails/table_flag/%s/sale_date/%s/page/%d/pagesize/%d';
        $page = 1;
        $totalPages = 1;
        $pagesize = 1000;
        $skuSalesTableFlag = intval($skuSalesTableFlag);
        if ($skuSalesTableFlag > 1){
            echo '0.获取sku销量统计原表；'.PHP_EOL.'1.获取sku销量统计数据（排除几大手工平台订单和重寄单）；'.PHP_EOL;
            exit();
        }

        while ($page <= $totalPages){
            echo '当前第'.$page.'/'.$totalPages.'页'.PHP_EOL;
            $requestUrl = sprintf($requestUrlTpl, $skuSalesTableFlag, $saleDate, $page, $pagesize);
            $page++;
            $response = HttpHelper::sendRequest($requestUrl);
            if($response === false){
                //$errorMsg = HttpHelper::getErrorMsg();
                return false;
            }
            $resultData = json_decode($response, true);
            if (empty($resultData)){
                return false;
            }
            $data = $resultData['data'];
            $totalPages = $data['totalPages'];
            $rows = $data['rows'];
            foreach ($rows as $row){
                $result = SkuSaleDetails::saveNewSkuSalesStatisticsData($row);
                if ($result['status'] == 0){
                    //保存同步数据失败原因$result['msg']
                    echo $result['msg'].PHP_EOL;
                }
            }
        }

        echo 'sku销量统计数据同步完成'.PHP_EOL;
    }

    /**
     * 每日销量排序分段统计
     * @param $saleDate
     */
    public static function skuSortSegmentByWarehousePerDay()
    {
        $data = SkuSalesStatisticsTotalMrp::dataListGroupByWarehouseIdSku();
        if (empty($data)){
            echo '没有要计算的数据';
            return false;
        }
        $statisticsDataByWarehouseCode = ArrayHelper::index($data, null, 'warehouse_code');
        //仓库总销量
        $allWarehouseTotalSalesData = SkuSalesStatisticsTotalMrp::allWarehouseTotalSalesData();
        $warehouseTotalSalesArr = array_column($allWarehouseTotalSalesData, 'total_sales', 'warehouse_code');
        foreach ($statisticsDataByWarehouseCode as $warehouseCode => $warehouseVal){
            $totalSales = $warehouseTotalSalesArr[$warehouseCode];
            echo $warehouseCode.'总销售量：'.$totalSales.PHP_EOL;
            //累积销量百分比
            $salePercentSum = 0;
            //将最后一个sku标记为百分之百
            $lastOne = array_pop($warehouseVal);
            self::saveSortSegment($lastOne, 100);
            foreach ($warehouseVal as $val){
                $skuSalePercent = $val['days_sales_30']/$totalSales;
                $skuSalePercentTmp = (round($skuSalePercent, 4) + $salePercentSum)*100;
                $salePercentSum += $skuSalePercent;
                echo $val['sku'].'  占比：'.$skuSalePercentTmp.'  累积占比：'.$salePercentSum.PHP_EOL;

                //保存销量百分比到分段字段
                self::saveSortSegment($val, $skuSalePercentTmp);
            }

        }

        return true;
    }

    /**
     * 保存所有统计结果的排序分段值
     * @param $row
     * @param $skuSalePercent
     */
    public static function saveSortSegment($row, $skuSalePercent)
    {
        $params = [
            'sku'=>$row['sku'],
            'warehouse_code'=>$row['warehouse_code'],
        ];
        SkuSalesStatisticsTotalMrp::updateSortSegment($params, $skuSalePercent);
    }
}