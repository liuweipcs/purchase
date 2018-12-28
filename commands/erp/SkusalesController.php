<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/11
 * Time: 11:26
 */

namespace app\commands\erp;

use app\models\SkuSaleDetails;
use app\services\SkusalesServices;
use yii\console\Controller;

/**
 * sku销量统计
 * Class SkusalesController
 * @package app\commands\erp
 */
class SkusalesController extends Controller
{
    /**
     * 从erp同步数据sku销量详情
     * @param string $lastStatisticsDate
     * @param int $skuSalesTableFlag
     * @throws \Exception
     */
    public function actionDetail($lastStatisticsDate = '', $skuSalesTableFlag=1)
    {
        $cache = \Yii::$app->cache;
        //缓存销量日期统计时间点
        $cacheKey = 'sku_sales_detail_laststatisticsdate';
        if (empty($lastStatisticsDate)){
            $lastStatisticsDate = $cache->get($cacheKey);
            if (empty($lastStatisticsDate)){
                $lastStatisticsDate = date('Y-m-d', strtotime('-30 day'));
                $cache->set($cacheKey, $lastStatisticsDate, 864000);
            }
        }

        //上一次统计日期往前一周开始统计
        $sevenDayBeforeDate = date('Y-m-d', strtotime($lastStatisticsDate.' -7 day'));
        $dateArr = $this->rangeDate($sevenDayBeforeDate);

        $cacheSyncDateTmp = 'sync_date_%s';
        foreach ($dateArr as $saleDate){
            $dateTmp = str_replace('-', '', $saleDate);
            $cacheDateKey = sprintf($cacheSyncDateTmp, $dateTmp);
            $cacheDateVal = $cache->get($cacheDateKey);
            if (!empty($cacheDateVal)){
                echo $saleDate.'正在同步中……'.PHP_EOL;
                continue;
            }
            $cache->set($cacheDateKey, 1, 28800);
            echo $saleDate.PHP_EOL;
            //按日期同步销量统计数据
            //按销量日期拉取数据前，先清除该日期销量的统计数据
            SkuSaleDetails::deleteAllDataBySalesDate($saleDate);
            SkusalesServices::syncSkuSalesDetailsFromErp($saleDate, $skuSalesTableFlag);
            $cache->set($cacheKey, $saleDate, 864000);
        }
        echo 'Finished!'.PHP_EOL;
        exit(0);
    }

    /**
     * 同步、统计完成后计算分段排序值
     */
    public function actionSortsegment()
    {
        SkusalesServices::skuSortSegmentByWarehousePerDay();
        echo 'Finished!'.PHP_EOL;
        exit();
    }

    /**
     * 生成近三天日期的时间
     * @param $yesterdayDate
     * @return array
     * @throws \Exception
     */
    protected function rangeDate($yesterdayDate)
    {
        $dateArr = [];
        $dateStart = new \DateTime($yesterdayDate);
        //排除今天
        $endDate = date('Y-m-d', strtotime('-1 day'));
        $dateEnd = new \DateTime($endDate);
        $dateEnd = $dateEnd->modify('+1 day');
        $interval = new \DateInterval('P1D');
        $daterange = new \DatePeriod($dateStart, $interval, $dateEnd);
		
        foreach($daterange as $date){
            array_push($dateArr, $date->format('Y-m-d'));
        }

        return $dateArr;
    }
}