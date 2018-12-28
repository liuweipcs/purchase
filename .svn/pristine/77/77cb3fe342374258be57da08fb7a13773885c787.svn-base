<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/12
 * Time: 11:30
 */

namespace app\services;
use app\models\DataControlConfig;
use app\models\OverseasDemandRule;
use app\models\PlatformSummary;
use app\models\ProductProvider;
use app\models\PurchaseOrder;
use app\models\SupplierQuotes;
use Yii;
use app\config\Vhelper;
use app\models\OverseasCheckPriv;


class PlatformSummaryServices extends BaseServices
{
    /**
     * 是否需要中转
     * @param null $type
     * @return array
     */
    public  static  function  getIsTransit($type=null)
    {
        $types = [
            '1' =>'是',
            '2' =>'否',

        ];

        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 审核状态
     * @param null $type
     * @return array
     */
    public  static  function  getLevelAuditStatus($type=null)
    {
        $types = [
            '0' => '待同意',
            '1' =>'同意',
            '2' =>'驳回',
            '3' =>'撤销',
            '4' => '采购驳回',
            '5' => '删除',
            '6' => '规则拦截',
            '7' => '待提交',
			'8' => '需求作废',
        ];

        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 采购主管审核状态
     * @param null $type
     * @return array
     */
    public  static  function  getInitLevelAuditStatus($type=null, $css=null)
    {
        if ($css) {
            $types = [
                '' =>'<span class="label label-primary">请选择</span>',
                '0' =>'<span class="label label-default">否</span>',
                '1' =>'<span class="label label-success">待采购主管审核</span>',
            ];
        } else {
            $types = [
                '' =>'请选择',
                0=>'否',
                1=>'待采购主管审核',
            ];
        }

        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }
    /**
     * 是否已采购
     * @param null $type
     * @return array
     */
    public  static  function  getIsPurchase($type=null)
    {
        $types = [
            '1' =>'未采购',
            '2' =>'已采购',
        ];

        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 采购类型
     * @param null $type
     * @return array
     */
    public  static  function  getPurchaseType($type=null)
    {
        $types = [
            '1' =>'国内',
            '2' =>'海外',
            '3' =>'FBA',
        ];

        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 是否推送
     * @param null $type
     * @return array
     */
    public  static  function  getIsPush($type=null)
    {
        $types = [
            '0' =>'未推送',
            '1' =>'已推送',
        ];
        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 运输方式
     * @param null $type
     * @return array
     */
    public  static  function  getTransportStyle($type=null)
    {
        return PurchaseOrderServices::getTransport($type);// 入口唯一性 @author Jolon @date 2018-12-14

        if(!empty($type)){
            $types = [
                '0' => '未知',
                '1' =>'空运',
                '2' =>'海运',
                '3' =>'铁路',
                '56'=>'海运散货',
                '57'=>'空运散货',
                '58'=>'快递',
                '15272'=>'空运整柜',
                '15273'=>'铁路整柜',
                '15274'=>'海运整柜',
                '15275'=>'铁路散货',
                '16162'=>'陆运'
            ];
        }else{
            $types = [
                '56'=>'海运散货',
                '57'=>'空运散货',
                '58'=>'快递',
                '15272'=>'空运整柜',
                '15273'=>'铁路整柜',
                '15274'=>'海运整柜',
                '15275'=>'铁路散货',
                '16162'=>'陆运'
            ];
        }
        return isset($type) ?  $types[$type]:$types;
    }
    //海外仓拦截规则条件
    public static function getRuleOperator($type=null,$operator=null){
        if(empty($type)){
            $resultArray = [
                '>'=>'大于',
                '>='=>'大于等于',
                '<'=>'小于',
                '<='=>'小于等于'
            ];
        }elseif ($type=='min'){
            $resultArray = [
                ''=>'请选择',
                '>'=>'大于',
                '>='=>'大于等于',
            ];
        }else{
            $resultArray = [
                ''=>'请选择',
                '<'=>'小于',
                '<='=>'小于等于'
            ];
        }
        return isset($resultArray[$operator])&&!empty($operator) ? $resultArray[$operator] : $resultArray;
    }

    public static function getOverseasChecklevel($check_type, $amount)
    {
        $price = OverseasCheckPriv::getOverseasCheckPirce($check_type);
        return $amount > $price ? 2 : 1;
    }
}