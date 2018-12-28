<?php
namespace app\services;
use app\api\v1\models\Product;
use app\api\v1\models\SkuStatisticsInfo;
use app\models\DataControlConfig;
use Yii;
use app\config\Vhelper;
/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class  SupplierGoodsServices extends BaseServices
{

    /**
     * 获取产品状态
     * @param null $type
     * @return array
     */
    public  static  function  getProductStatus($type=null)
    {
        $types = [
            'all' => '全部',
            '4' =>'在售中',
            '0' =>'审核不通过',
            '1' =>'刚开发',
            '2' =>'编辑中',
            '3' =>'预上线',
            '5' =>'已滞销',
            '6' =>'待清仓',
            '7' =>'已停售',
            '8' =>'刚买样',
            '9' =>'待品检',
            '10' =>'拍摄中',
            '11' =>'产品信息确认',
            '12' =>'修图中',
            '14' =>'设计审核中',
            '15' =>'文案审核中',
            '16' =>'文案主管终审中',
            '17' =>'试卖编辑中',
            '18' =>'试卖在售中',
            '19'=>'试卖文案终审中',
            '20'=>'预上线拍摄中',
            '21'=>'物流审核中',
            '22'=>'缺货中',
            '27'=>'作图审核中',
            '29'=>'开发检查中',
            '30'=>'拍摄中、编辑中',
            '100' =>'未知'
        ];

        return isset($type) ?  $types[$type]:$types;
    }

    public  static  function  getProductSearchStatus($type=null)
    {
        $types = [
            '4' =>'在售中',
            '0' =>'审核不通过',
            '1' =>'刚开发',
            '2' =>'编辑中',
            '3' =>'预上线',
            '5' =>'已滞销',
            '6' =>'待清仓',
            '7' =>'已停售',
            '8' =>'刚买样',
            '9' =>'待品检',
            '10' =>'拍摄中',
            '11' =>'产品信息确认',
            '12' =>'修图中',
            '14' =>'设计审核中',
            '15' =>'文案审核中',
            '16' =>'文案主管终审中',
            '17' =>'试卖编辑中',
            '18' =>'试卖在售中',
            '19'=>'试卖文案终审中',
            '20'=>'预上线拍摄中',
            '21'=>'物流审核中',
            '22'=>'缺货中',
            '27'=>'作图审核中',
        ];
        return $types;
    }
    /**
     * 获取货源状态(erp取值)
     * @param null $type
     * @return array
     */
    public  static  function  getSupplyStatus($type=null)
    {
        $types = [

            '1' =>'正常',
            '2' =>'缺货',
            '3' =>'停产',


        ];

        return isset($type) ?  $types[$type]:$types;
    }

    /*
     * 货源状态（采购系统）
     */
    public static function getProductSourceStatus($sourcingStatus=null,$format='array'){
        $sourcingStatusArray=[
            1=>'正常',
            2=>'停产',
            3=>'断货'];
        if($format=='array'){
            return $sourcingStatusArray;
        }
        if($format=='string'){
            return isset($sourcingStatusArray[$sourcingStatus]) ? $sourcingStatusArray[$sourcingStatus] :'正常';
        }

    }
    /**
     * 获取币种
     * @param null $type
     * @return array
     */
    public  static  function  getCurrency($type=null)
    {
        $types = [
            'RMB' =>'RMB 人民币',
            'AUD' =>'AUD 澳元',
            'EUR' =>'EUR 欧元',
            'GBP' =>'GBP 英镑',
            'HKD' =>'HKD 港币',
            'JPY' =>'JPY 日元',
            'MXN' =>'MXN MXN',
            'USD' =>'USD 美元',

        ];

        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 获取产品在售还是不在售
     */
    public static function getSkuStatus($sku){
        $product = Product::find()->andWhere(['sku'=>$sku])->one();
        return $product&&$product->product_status == 4 ? "<sup style='color: green'>".'售'."</sup>" : "<sup style='color:red'>".'非'."</sup>";
    }

    public static function getSkuDown($sku){
        
                $num_limit = DataControlConfig::find()->select('values')->where(['type'=>'down_purchase-num_limit'])->scalar();
        $skuPurchaseNum = SkuStatisticsInfo::find()->select('purchase_num')->where(['sku'=>$sku])->scalar();
        return $skuPurchaseNum>=$num_limit ? "<sup ><span class='glyphicon glyphicon-arrow-down' style='color: red'></span></sup>" : '';
    }

}