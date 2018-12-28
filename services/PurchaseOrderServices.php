<?php
namespace app\services;

use app\models\PurchaseGradeAudit;
use app\models\PurchaseOrderItems;
use app\models\PurchaseUser;
use app\models\User;
use Yii;
use app\config\Vhelper;
use app\models\DemandLog;
use app\models\StockLog;
use app\models\PurchaseDemand;
use app\models\OrderPayDemandMap;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderCancelSearch;
use app\models\PurchaseOrderCancel;
use app\api\v1\models\PlatformSummary;
/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class  PurchaseOrderServices extends BaseServices
{

    /**
     * 获取运输方式
     * @param null $type
     * @return array
     */
    public  static  function  getShippingMethod($type=null)
    {
        $types = [
            '0' => '',
            '1' =>'自提',
            '2' =>'快递',
            '3' =>'物流',
            '4' =>'送货',
        ];

        return isset($type) ?  $types[$type]:$types;
    }

    // 获取请款方式
    public static function getRequestPayoutType($type = null)
    {
        $t = is_null($type) ? 0 : $type;
        $types = [
            '0' => '全额付款',
            '1' => '按（入库数量）请款',
            '2' => '按（订单数量-已请款数量-已取消数量-未到货数量）请款',
            '3' => '按（订单数量-已请款数量-已取消数量）请款',
            '4' => '按（订单数量-已取消数量）手动请款',
            '5' => '比例支付',
        ];
        return isset($types[$t]) ? $types[$t] : $types;
    }
    /**
     * 获取请款单状态
     * @param null $type
     * @return array
     */
    public static function getPayStatusType($type = null)
    {
        $types = [
            '-1' => '<span class="label label-default">未提交</span>',
            '0'  => '<span class="label label-default">作废</span>',
            '1'  => '<span class="label label-default">未申请付款</span>',
            '2'  => '<span class="label label-default">待财务审批</span>',
            '3'  => '<span class="label label-danger">财务驳回</span>',
            '4'  => '<span class="label label-primary">待财务付款</span>',
            '5'  => '<span class="label label-info">已付款</span>',
            '6'  => '<span class="label label-info">已付款（部分付款）</span>',
            '10' => '<span class="label label-default">待经理审核</span>',
            '11' => '<span class="label label-danger">经理驳回</span>',
            '12' => '<span class="label label-danger">出纳驳回</span>',
            '13' => '<span class="label label-danger">富友付款待审核</span>',
            '14' => '<span class="label label-danger">富友付款失败</span>',
        ];
        return isset($type) ? $types[$type] : $types;
    }

    // 请款单状态，不带html
    public static function getPayStatus($type=null)
    {
        $types = [
            '-1' => '未提交',
            '0'  => '作废',
            '1'  => '未申请付款',
            '2'  => '待财务审批',
            '3'  => '财务驳回',
            '4'  => '待财务付款',
            '5'  => '已付款',
            '6'  => '已部分付款',
            '10' => '待采购经理审核',
            '11' => '经理驳回',
            '12' => '出纳驳回',
            '13'=> '富友付款待审核',
        ];
        return isset($type) ? $types[$type] : $types;
    }
    /**
     * 获取审核退回
     * @param null $type
     * @return array
     */
    public static  function  getAuditReturn($type=null)
    {
        $types = [
            '1' =>'是',
            '2' =>'否',


        ];

        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 获取推送
     * @param null $type
     * @return array
     */
    public static  function  getPush($type=null)
    {
        $types = [
            ''=>'无',
            '0' =>'未推送',
            '1' =>'已推送',
            '2' => '',


        ];
        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 获取运营方式
     * @param null $type
     * @return array
     */
    public static  function  getOperationType($type=null)
    {
        $types = [
            '1' =>'代运营',
            '2' =>'自运营',
        ];

        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 获取创建类型
     * @param null $type
     * @return array
     */
    public static  function  getCreateType($type=null)
    {
        $types = [
            '1' =>'系统生成',
            '2' =>'手工创建',


        ];

        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 获取采购单状态
     * @param null $type
     * @return array
     */
    public  static  function  getPurchaseStatus($type=null)
    {

        $types = [
            '1' =>'<span class="label label-primary">'.'待确认'.'</span>',
            '2' =>'<span class="label label-primary">'.'采购已确认'.'</span>',
            '3' =>'<span class="label label-primary">'.'已审批'.'</span>',
            '4' =>'<span class="label label-danger">'.'撤销'.'</span>',
            '5' =>'<span class="label label-danger">'.'部分到货'.'</span>',
            '6' =>'<span class="label label-success">'.'全到货'.'</span>',
            '7' =>'<span class="label label-danger">'.'等待到货'.'</span>',
            '8' =>'<span class="label label-danger">'.'部分到货等待剩余'.'</span>',
            '9' =>'<span class="label label-success">'.'部分到货不等待剩余'.'</span>',
            '10' =>'<span class="label label-danger">'.'已作废'.'</span>',
            '99' =>'<span class="label label-danger">'.'未全部到货'.'</span>',
        ];

        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 获取采购单状态
     * @param null $type
     * @return array
     */
    public  static  function  getPurchaseStatusText($type=null)
    {

        $types = [
            '' => '请选择',
            '1' =>'待确认',
            '2' =>'采购已确认',
            '3' =>'已审批',
            '4' =>'撤销',
            '5' =>'部分到货',
            '6' =>'全到货',
            '7' =>'等待到货',
            '8' =>'部分到货等待剩余',
            '9' =>'部分到货不等待剩余',
            '10' =>'已作废',
            '99' =>'未全部到货',
        ];

        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 补货方式
     * @param null $type
     * @return array
     */
    public static  function  getPurType($type=null)
    {
            $types = [
                '1' =>'缺货入库',
                '2' =>'警报入库',
                '3' =>'特采入库',
                '4' =>'正常入库',
                '5' =>'样品采购入库',
                '6' =>'备货采购',
                '7' =>'试销采购',


            ];

            return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 获取到货状态
     * @param null $type
     * @return array
     */
    public static function  getArrival($type=null)
    {
        $types = [
            '0'=>'',
            '2' =>'今日到',
            '1' =>'晚到',


        ];

        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 获取付款状态
     * @param null $type
     * @return array
     */
/*    public static function getPayStatus($type=null)
    {
        $types = [
            '0' =>'<span class="label label-danger">'.'作废'.'</span>',
            '1' =>'<span class="label label-danger">'.'未申请付款'.'</span>',
            '2' =>'<span class="label label-success">'.'已申请付款(待审批)'.'</span>',
            '3' =>'<span class="label label-danger">'.'审批不通过'.'</span>',
            '4' =>'<span class="label label-primary">'.'已审批(待付款)'.'</span>',
            '5' =>'<span class="label label-success">'.'已付款'.'</span>',
            '6' =>'<span class="label label-warning">'.'已部分审批（待付款）'.'</span>',
            '8' =>'<span class="label label-success">已付款（部分付款）</span>'
        ];
        return isset($type) ?  $types[$type]:$types;
    }*/


    /**
     * 获取退款状态
     * @param null $type
     * @return array
     */
    public static function getReceiptStatus($type=null)
    {
        $types = [
            '0' =>'作废',
            '1' =>'待收款',
            '2' =>'已收款',
            '3' =>'部分退款',
            '4' =>'全额退款',
            '5' =>'审核不通过',
            '6' =>'取消货物数量退款',
            '7' =>'取消货物数量退款（待审批）',
            '10' => '驳回',
        ];

        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 获取退款状态，加上样式
     */
    public static function getReceiptStatusCss($type=null)
    {
        $types = [
            '0' =>'<span class="label label-danger">'.'作废'.'</span>',
            '1' =>'<span class="label label-default">'.'待收款'.'</span>',
            '2' =>'<span class="label label-success">'.'已收款'.'</span>',
            '3' =>'<span class="label label-info">'.'部分退款'.'</span>',
            '4' =>'<span class="label label-info">'.'全额退款'.'</span>',
            '5' =>'<span class="label label-danger">'.'审核不通过'.'</span>',
            '6' =>'<span class="label label-primary">'.'取消货物数量退款'.'</span>',
            '7' =>'<span class="label label-parimary">'.'取消货物数量退款（待审批）'.'</span>',
            '10' => '<span class="label label-danger">驳回</span>',
        ];
        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 获取帐号标志
     * @param null $type
     * @return array
     */
    public  static function  getAccountSign($type=null)
    {

        $types = [
            '1' =>'对公帐号',
            '2' =>'对私帐号',


        ];

        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 获取支付类型
     * @param null $type
     * @return array
     */
    public  static function  getPaymentTypes($type=null)
    {

        $types = [
            '1' =>'银行卡',
            '2' =>'支付宝',


        ];

        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 获取应用业务
     * @param null $type
     * @return array
     */
    public  static function  getApplicationBusiness($type=null)
    {

        $types = [
            '1' =>'采购',


        ];

        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 获取收货异常
     *  @param null $type
     * @return array
     */
    public static function  getPurchaseEx($type=null)
    {

        $types = [
            '1' =>'无异常',
            '2' =>'有异常',
            '3' =>'全额退款',
            '4' =>'部分到货不等待剩余',
            '5' =>'部分到货等待剩余',
            '6' =>'已完成',
            '7' =>'入库',
            '8' =>'退货',


        ];

        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 获取qc异常
     *  @param null $type
     * @return array
     */
    public static function  getPurchaseExs($type=null)
    {

        $types = [
            '1' =>'无异常',
            '2' =>'有异常',
            '3' =>'销毁,采购方承担',
            '4' =>'销毁,供应商承担',
            '5' =>'退回,供应商退回款项',
            '6' =>'换货,供应商重新发货',
            '7' =>'不良品上架',
            '8' =>'已完成',


        ];

        return isset($type) ?  $types[$type]:$types;
    }
    public  static  function getTransport($type=null)
    {
        if(!empty($type)){
            $types = [
                '0'     => '未知',
                '1'     => '空运',
                '2'     => '海运',
                '3'     => '铁路',
                '15274' => '海运整柜（不包税）',
                '16171' => '海运整柜（包税）',
                '56'    => '海运散货（不包税）',
                '16166' => '海运散货（包税）',
                '57'    => '空运散货（不包税）',
                '16167' => '空运散货（包税）',
                '15272' => '空运整柜（不包税）',
                '16169' => '空运整柜（包税）',
                '15275' => '铁路散货（不包税）',
                '16172' => '铁路散货（包税）',
                '15273' => '铁路整柜（不包税）',
                '16170' => '铁路整柜（包税）',
                '58'    => '快递（不包税）',
                '16168' => '快递（包税）',
                '16162' => '陆运（不包税）',
                '16173' => '陆运（包税）',
            ];
        }else{
            $types = [
                '15274' => '海运整柜（不包税）',
                '16171' => '海运整柜（包税）',
                '56'    => '海运散货（不包税）',
                '16166' => '海运散货（包税）',
                '57'    => '空运散货（不包税）',
                '16167' => '空运散货（包税）',
                '15272' => '空运整柜（不包税）',
                '16169' => '空运整柜（包税）',
                '15275' => '铁路散货（不包税）',
                '16172' => '铁路散货（包税）',
                '15273' => '铁路整柜（不包税）',
                '16170' => '铁路整柜（包税）',
                '58'    => '快递（不包税）',
                '16168' => '快递（包税）',
                '16162' => '陆运（不包税）',
                '16173' => '陆运（包税）',
            ];
        }

        return isset($type) ? (isset($types[$type])?$types[$type]:''):$types;
    }

    /**
     * 采购单处理状态
     * @return array
     */
    public static function getProcesStatus(){
        return ['0'=>'未处理','1'=>'已生成PO','2'=>'已完成','all'=>'全部'];
    }

    /**
     * 采购小组
     * @return array
     */
    public static function getPurchaseGroup(){
        return [
            1=>'国内采购一组',
            2=>'国内采购二组',
            3=>'国内采购三组',
            4=>'国内采购四组',
            5=>'国内采购五组',
        ];
    }

    /**
     * 采购单所有操作的状态
     * @return array
     */
    public static function getAllOrdersStatus(){
        return [
            1=>'待提交',
            2=>'待组长审核',
            3=>'待主管审核',
            4=>'待经理审核',
            5=>'已通过/申请付款',
            6=>'待财务初审',
            7=>'待财务经理审核',
            8=>'已通过/待付款',
            9=>'已付款',
            10=>'组长驳回',
            11=>'主管驳回',
            12=>'经理驳回',
            13=>'财务初审驳回',
            14=>'财务经理驳回',
        ];
    }
    /**
     * 是否需要中转
     * @param null $type
     * @return array
     */
    public  static  function  getIsTransit($type=null)
    {
        $types = [
            '0' => '未知',
            '1' =>'是',
            '2' =>'否',

        ];

        return isset($type) ?  $types[$type]:$types;
    }

    /**完成类型
     * @param null $type
     * @return array|mixed
     */
    public static function getCompleteType($type=null)
    {
        $types = [
            '1' =>'标准',
            '2' =>'采购强制完成',
            '3' =>'收货强制完成',

        ];

        return isset($type) ?  $types[$type]:$types;
    }

    /**加急采购单
     * @param null $type
     * @return array|mixed
     */
    public static function getIsExpedited($type=null)
    {
        $types = [
            '1' =>'不加急',
            '2' =>'加急',
        ];

        return isset($type) ?  $types[$type]:$types;
    }
    /**采购类型
     * @param null $type
     * @return array|mixed
     */
    public static function getPurchaseType($type=null)
    {
        $types = [
            '1' =>'国内',
            '2' =>'海外',
            '3' =>'FBA',

        ];
        return isset($type) ?  $types[$type]:$types;
    }
    /**采购类型
     * @param null $type
     * @return array|mixed
     */
    public static function getPurchaseTypeList($type=null, $is_delete = false)
    {
        $types = [
            0 =>'所有',
            1 =>'国内',
            2 =>'海外',
            3 =>'FBA',

        ];
        if ($is_delete) {
            unset($types[$type]);
            return $types;
        }
        return isset($type) ?  $types[$type]:$types;
    }
    /**是否退税
     * @param null $type
     * @return array|mixed
     */
    public static function getIsDrawback($type=null)
    {
        $types = [
            '1' =>'不退',
            '2' =>'退',
        ];
        return isset($type) ?  $types[$type]:$types;
    }
    /**管理人员审核
     * @param null $type
     * @return array|mixed
     */
    public static function getReviewStatus($type=null)
    {
        $types = [
            '0' => ' ',
            '1' =>'组长',
            '2' =>'主管',
            '3' =>'经理',
        ];
        return isset($type) ?  $types[$type]:$types;
    }
    //根据当前登录用户获取可见采购员
    public static function getPurchaseOrderBuyerByRole(){
        $userRolesArray = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
        $userRoles      = array_keys($userRolesArray);

        if(in_array('超级管理员组',$userRoles) || BaseServices::getIsAdmin(1)){
            return true;
        }elseif(in_array('供应链',$userRoles)){
            $searchuserIds = Yii::$app->authManager->getUserIdsByRole('供应链');
            $buyername = User::find()->andWhere(['in','id',$searchuserIds])->select('username')->asArray()->all();
            return empty($buyername) ? [] : array_column($buyername,'username');
        }elseif(in_array('采购经理组',$userRoles)||in_array('采购组长',$userRoles)||in_array('采购组-国内',$userRoles)||in_array('浏览权限',$userRoles)||in_array('销售组',$userRoles)){
            $searchuserIds = Yii::$app->authManager->getUserIdsByRole('采购组长');
            $searchuserIdsz = Yii::$app->authManager->getUserIdsByRole('采购经理组');
            $searchuserIdsm = Yii::$app->authManager->getUserIdsByRole('采购组-国内');
            $searchgylIds   = Yii::$app->authManager->getUserIdsByRole('供应链');//排除供应链的人员
            $buyername = User::find()->andWhere(['in','id',array_diff(array_merge($searchuserIds,$searchuserIdsm,$searchuserIdsz),$searchgylIds)])->select('username')->asArray()->all();
            return empty($buyername) ? [] : array_column($buyername,'username');
        }elseif(in_array('FBA采购组',$userRoles)||in_array('FBA采购经理组',$userRoles)){
            $searchuserIds = Yii::$app->authManager->getUserIdsByRole('FBA采购组');
            $searchuserIdsz = Yii::$app->authManager->getUserIdsByRole('FBA采购经理组');
            $buyername = User::find()->andWhere(['in','id',array_merge($searchuserIds,$searchuserIdsz)])->select('username')->asArray()->all();
            return empty($buyername) ? [] : array_column($buyername,'username');
        }elseif(in_array('采购组-海外',$userRoles)){
            $searchuserIds = Yii::$app->authManager->getUserIdsByRole('采购组-海外');
            $buyername = User::find()->andWhere(['in','id',$searchuserIds])->select('username')->asArray()->all();
            return empty($buyername) ? [] : array_column($buyername,'username');
        }else{
            return true;
//            return [];
        }
    }


    public static function getHandlerType($type = null)
    {
        $types = [
            '1'=>'退货',
            '2'=>'次品退货',
            '3'=>'次品转正',
            '4'=>'正常入库',
            '5'=>'不做处理',
            '6'=>'优品入库',
            '7'=>'整批退货',
            '8'=>'二次包装',
            '9'=>'正常入库',
            '10'=>'不做处理',
        ];
        if(is_null($type)) {
            return $types;
        } else {
            return isset($types[$type]) ? $types[$type] : '未知类型';
        }
    }

    // 阿里巴巴开放平台订单支付状态
    public static function getAlibabaPayStatus($type)
    {
        $types = [
            'waitbuyerpay'        => '<span class="label label-default">等待买家付款</span>',
            'waitsellersend'      => '<span class="label label-warning">等待卖家发货</span>',
            'waitlogisticstakein' => '<span class="label label-warning">等待物流公司揽件</span>',
            'waitbuyerreceive'    => '<span class="label label-warning">等待买家收货</span>',
            'waitbuyersign'       => '<span class="label label-warning">等待买家签收</span>',
            'signinsuccess'       => '<span class="label label-success">买家已签收</span>',
            'confirm_goods'       => '<span class="label label-success">已收货</span>',
            'success'             => '<span class="label label-success">交易成功</span>',
            'cancel'              => '<span class="label label-danger">交易取消</span>',
            'terminated'          => '<span class="label label-danger">交易终止</span>',
        ];
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }

    // 报损状态
    public static function getBreakageStatus($type)
    {
        $types = [
            '0' => '<span class="label label-default">待经理审核</span>',
            '1' => '<span class="label label-info">待财务审批</span>',
            '2' => '<span class="label label-danger">经理驳回</span>',
            '3' => '<span class="label label-info">已通过</span>',
            '4' => '<span class="label label-danger">财务驳回</span>',
        ];
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }


    // 报损状态
    public static function getShipfeesAuditStatus($type)
    {
        $types = [
            '0' => '<span class="label label-danger">待审核</span>',
            '1' => '',
            '2' => '<span class="label label-danger">不通过</span>',
        ];
        return isset($types[$type]) ? $types[$type] : '未知';
    }

    // 合同主状态
    public static function getCompactStatusCss($type)
    {
        $types = [
            '0' => '<span class="label label-default">待审核</span>',
            '1' => '<span class="label label-info">待财务审批</span>',
            '2' => '<span class="label label-danger">经理驳回</span>',
            '3' => '<span class="label label-info">已通过</span>',
            '4' => '<span class="label label-danger">财务驳回</span>',
        ];
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }

    public static function getEarlyWarningStatus($status)
    {
        $statusArray = [
            1=>'付款申请超时',
            2=>'付款超时',
            3=>'获取物流超时',
            4=>'签收超时',
            5=>'上架超时'
        ];
        return isset($statusArray[$status]) ? $statusArray[$status] : '未知';
    }

    // 合同主状态
    public static function getCompactStatus($type = null)
    {
        $types = [
            '2' =>'<span class="label label-primary">'.'采购已确认'.'</span>',
            '3' =>'<span class="label label-primary">'.'已审批'.'</span>',
            '4' =>'<span class="label label-danger">'.'撤销'.'</span>',
            '5' =>'<span class="label label-danger">'.'部分到货'.'</span>',
            '6' =>'<span class="label label-success">'.'全到货'.'</span>',
            '7' =>'<span class="label label-danger">'.'等待到货'.'</span>',
            '8' =>'<span class="label label-danger">'.'部分到货等待剩余'.'</span>',
            '9' =>'<span class="label label-success">'.'部分到货不等待剩余'.'</span>',
            '10' =>'<span class="label label-danger">'.'已作废'.'</span>',
            '99' =>'<span class="label label-danger">'.'未全部到货'.'</span>',
        ];
        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }

    // 入库数量异常消息读取状态
    public static function getReadingStatus($type = null, $css = true)
    {
        if($css) {
            $types = [
                '0' => '未读',
                '1' => '已读'
            ];
            if(is_null($type)) {
                return $types;
            }
            return isset($types[$type]) ? $types[$type] : '未知类型';
        } else {
            $types = [
                '0' => '<label class="label label-default">未读</label>',
                '1' => '<label class="label label-success">已读</label>'
            ];
            if(is_null($type)) {
                return $types;
            }
            return isset($types[$type]) ? $types[$type] : '未知类型';
        }
    }

    // 异常处理类型
    public static function getExpHandlerType($type = null)
    {
        $types = [
            '1' =>'<span class="label label-danger">退货</span>',
            '2' =>'<span class="label label-primary">次品退货</span>',
            '3' =>'<span class="label label-primary">次品转正</span>',
            '4' =>'<span class="label label-primary">正常入库</span>',
            '5' =>'<span class="label label-danger">不做处理</span>',
            '6' =>'<span class="label label-success">优品入库</span>',
            '7' =>'<span class="label label-danger">整批退货</span>',
            '8' =>'<span class="label label-default">二次包装</span>',
            '9' =>'<span class="label label-success">正常入库</span>',
            '10' =>'<span class="label label-danger">不做处理</span>',
            '15' =>'<span class="label label-info">处理中...</span>',
        ];
        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }
    // 取消未到货状态
    public static function getCancelAuditStatusCss($type = null)
    {
        $types = [
            '1' =>'<span class="label label-default">待审核</span>',
            '2' =>'<span class="label label-success">审核通过</span>',
            '3' =>'<span class="label label-danger">审核驳回</span>',
            '4' =>'<span class="label label-danger">财务驳回</span>',
        ];
        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }
    // 取消未到货状态
    public static function getCancelAuditStatus($type = null)
    {
        $types = [
            '1' =>'待审核',
            '2' =>'审核通过',
            '3' =>'审核驳回',
        ];
        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }
    // 取消未到货：取消类型
    public static function getCancelTypeCss($type = null)
    {
        $types = [
            '1' =>'<span class="label label-default">部分取消</span>',
            '2' =>'<span class="label label-success">全部取消</span>',
        ];
        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }

    // 付款单种类明细
    public static function getPayCategory($key = null)
    {
        $types = [
            '0' => '合同或其他采购单付款',
            '10' => '合同运费走私账',
            '11' => '合同全额付款',//付款比例100%
            '12' => '合同付订金', //第一个比例
            '13' => '合同付中期款项', //中间比例
            '20' => '合同付尾款',//最后比例
            '21' => '合同付款手动输入金额', //手动请款

            '22' => '合同运费', //合同运费
            '23' => '合同请剩余数量', //fba-剩余数量
            '24' => '合同请到货数量', //fba-到货数量
            '25' => '合同请入库数量', //fba-入库数量
        ];
        if(is_null($key)) {
            return $types;
        }
        return isset($types[$key]) ? $types[$key] : '未知类型';
    }
    /**
     * 审核金额
     */
    public static function getAuditPrice($pur_number)
    {
        $user_id = Yii::$app->user->id;
        $grade = PurchaseUser::getUserGradeInt($user_id);
        $type = PurchaseUser::getType($user_id);
        $audit_price = PurchaseGradeAudit::getAuditPrice($grade, $type);
        $pay_price = round(PurchaseOrderItems::getCountPrice($pur_number),2);
        $userRoleName = BaseServices::getIsAdmin();

        if ($audit_price>= $pay_price || $userRoleName) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 获取运输方式
     * @param null $type
     * @return array
     */
    public  static  function  getDaoHuo($type=null)
    {
        $types = [
            '0' =>'<span class="label label-danger">未知</span>',
            '1' =>'<span class="label label-default">未到货</span>',
            '2' =>'<span class="label label-primary">部分到货</span>',
            '3' =>'<span class="label label-danger">异常</span>',
            '4' =>'<span class="label label-success">全部到货</span>',
        ];
        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 获取采购到货日期
     */
    public static function getArrivalStatusCss($type=null)
    {
        $types = [
            1 =>'<span class="label label-default">未到货</span>',
            2 =>'<span class="label label-primary">部分到货</span>',
            3 =>'<span class="label label-success">全部到货</span>',
        ];
        return isset($type) ?  $types[$type]:$types;
    }

    public static function getArrivalStatus($type=null)
    {
        $types = [
            1 =>'未到货',
            2 =>'部分到货',
            3 =>'全部到货',
        ];
        return isset($type) ?  $types[$type]:$types;
    }
    
    /**
     * 采购单日志
     * @param string $demand_number
     * @param string $message
     * @param string $pur_number
     */
    public static function writelog($demand_number, $message, $pur_number = '', $update_data = [])
    {
        if (!is_array($demand_number)) {
            $demand_number = [$demand_number];
        }
        $array = [];
        foreach ($demand_number as $_demand_number) {
            $set = [];
            $set[] = $_demand_number;
            $set[] = $pur_number;
            $set[] = $message;
            $set[] = !empty(Yii::$app->user->identity->username) ? Yii::$app->user->identity->username : '';
            $set[] = $update_data ? json_encode($update_data) : '';
            $set[] = date('Y-m-d H:i:s');
            $array[] = $set;
        }
        $fields = ['demand_number','pur_number','message','operator','update_data','operate_time'];
        Yii::$app->db->createCommand()->batchInsert(DemandLog::tableName(), $fields, $array)->execute();
    }
    
    public static function getBhTypes($type = null)
    {
        $data = [
            1 => '缺货入库',
            2 => '警报入库',
            3 => '特采入库',
            4 => '正常入库',
            5 => '样品采购入库',
            6 => '备货采购',
            7 => '试销采购',
        ];
        return isset($type) ? ( isset($data[$type]) ? $data[$type] : '' ) : $data;
    }
    
    public static function getOverseasOrderStatus($status = null)
    {
        $statuss = [
            1 => '等待采购询价',
            2 => '信息变更等待审核',
            3 => '待采购审核',
            5 => '待销售审核',
            6 => '等待生成进货单',
            7 => '等待到货',
            8 => '已到货待检测',
            9 => '全部到货',
            10 => '部分到货等待剩余到货',
            11 => '部分到货不等待剩余到货',
            12 => '作废订单待审核',
            13 => '作废订单待退款',
            14 => '已作废订单',
        ];
        return isset($status) ? ( isset($statuss[$status]) ? $statuss[$status] : '' ) : $statuss;
    }
    
    public static function getOverseasDemandPaidAmount($demand_number)
    {
        $paid_amount = OrderPayDemandMap::find()
            ->from(OrderPayDemandMap::tableName().' as a')
            ->leftJoin(PurchaseOrderPay::tableName().' as b', 'a.requisition_number = b.requisition_number')
            ->where(['a.demand_number'=>$demand_number])
            ->andWhere(['in','b.pay_status',[5,6]])
            ->select('sum(pay_amount) as pay_amount')
            ->scalar();
        return $paid_amount ? $paid_amount : 0;
    }
    
    public static function getOverseasDemandCancelCty($demand_number)
    {
        $cancel_ctq = PurchaseOrderCancelSub::find()
            ->from(PurchaseOrderCancelSub::tableName().' as a')
            ->leftJoin(PurchaseOrderCancel::tableName().' as b','b.id = a.cancel_id')
            ->where(['a.demand_number'=>$demand_number])
            ->andWhere(['in','b.is_push',[0,1]])
            ->select('sum(cancel_ctq) as cancel_ctq')
            ->scalar();
        return $cancel_ctq ? $cancel_ctq : 0;
    }
    
    public static function getlastPirce($sku, $create_time = '')
    {
        $query = \app\models\PurchaseOrderItems::find()
            ->select('t.price')
            ->from(\app\models\PurchaseOrderItems::tableName().' as t')
            ->leftJoin('pur_purchase_order as t1','t1.pur_number = t.pur_number')
            ->where(['t.sku'=>$sku,'t1.purchas_status'=>[3,5,6,7,8,9]]);
        if ($create_time) {
            $query->andwhere("t1.created_at < '$create_time'");
        }
        $last_order = $query->orderBy('t1.id DESC')->one();
        return !empty($last_order->price) ? round($last_order->price,4): '首次采购';
    }
    
    public static function getlastPirce2($sku, $create_time = '')
    {
        $query = \app\models\PurchaseOrderItems::find()
        ->select('t.price')
        ->from(\app\models\PurchaseOrderItems::tableName().' as t')
        ->leftJoin('pur_purchase_order as t1','t1.pur_number = t.pur_number')
        ->where(['t.sku'=>$sku,'t1.purchas_status'=>[3,5,6,7,8,9]]);
        if ($create_time) {
            $query->andwhere("t1.created_at < '$create_time'");
        }
        $last_order = $query->orderBy('t1.id DESC')->one();
        return !empty($last_order->price) ? round($last_order->price,4): '首次采购';
    }
    
    public static function getTransitWarehouseInfo($warehouse_code = null)
    {
        $warehouses = [
            'shzz' => [
                'name' => '宁波中转仓',
                'delivery_address' => '浙江省宁波市镇海区西经堂路399弄66号易佰网络科技有限公司收 罗雄（{buyer}）  13642355572',
            ],
            'AFN' => [
                'name' => '东莞中转仓',
                'delivery_address' => '广东省东莞市塘厦镇田心村莆心路1号鑫博盛科技园C栋 徐天龙（{buyer}） 15878497995    涂爱民（{buyer}） 15112550754',
            ],
        ];
        return isset($warehouse_code) ? ( isset($warehouses[$warehouse_code]) ? $warehouses[$warehouse_code] : [] ) : $warehouses;
    }
    
    public static function getTransitWarehouse($warehouse_code = null)
    {
        $warehouses = self::getTransitWarehouseInfo();
        $data = [];
        foreach ($warehouses as $key=>$val) {
            $data[$key] = $val['name'];
        }
        return isset($warehouse_code) ? ( isset($data[$warehouse_code]) ? $data[$warehouse_code] : $warehouse_code ) : $data;
    }

    /**
     * 开票信息状态
     */
    public static function getTicketOpenStatus($type = null, $css=null)
    {
        if ($css) {
            $types = [
                '' =>'<span class="label label-primary">请选择</span>',
                '0' =>'<span class="label label-primary">待采购处理</span>',
                '1' =>'<span class="label label-default">待财务审核</span>',
                '2' =>'<span class="label label-success">财务已审核</span>',
                '3' =>'<span class="label label-danger">财务驳回</span>',
            ];
        } else {
            $types = [
                '' => '请选择',
                0 =>'待采购处理',
                1 =>'待财务审核',
                2 =>'财务已审核',
                3 =>'财务驳回',
            ];
        }
        
        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }
    /**
     * 采购建议处理状态
     * @return array
     */
    public static function getState($type = null, $css=null)
    {
        if ($css) {
            $types = [
                '' =>'<span class="label label-primary">请选择</span>',
                '0' =>'<span class="label label-default">未处理</span>',
                '1' =>'<span class="label label-success">已生成PO</span>',
                '2' =>'<span class="label label-danger">已完成</span>',
            ];
        } else {
            $types = [''=> '请选择', '0'=>'未处理','1'=>'已生成PO','2'=>'已完成'];
        }
        
        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }
    /**
     * 货源状态
     */
    public static function getSourcingStatus($type = null, $css=null)
    {
        if ($css) {
            $types = [
                '' =>'<span class="label label-primary">请选择</span>',
                '1' =>'<span class="label label-default">正常</span>',
                '2' =>'<span class="label label-success">停产</span>',
                '3' =>'<span class="label label-danger">断货</span>',
            ];
        } else {
            $types = [
                '' =>'请选择',
                1 =>'正常',
                2 =>'停产',
                3 =>'断货',
            ];
        }
        
        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }
    /**
     * 预警状态
     */
    public static function getWarnStatus($type = null, $css=null)
    {
        if ($css) {
            $types = [
                '' =>'<span class="label label-primary">请选择</span>',
                '1' =>'<span class="label label-default">付款申请超时</span>',
                '2' =>'<span class="label label-success">付款超时</span>',
                '3' =>'<span class="label label-danger">获取物流超时</span>',
                '4' =>'<span class="label label-danger">签收超时</span>',
                '5' =>'<span class="label label-danger">上架超时</span>',
            ];
        } else {
            $types = [
                '' =>'请选择',
                1=>'付款申请超时',
                2=>'付款超时',
                3=>'获取物流超时',
                4=>'签收超时',
                5=>'上架超时'
            ];
        }
        
        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }
    /**
     * FBA-是否验货
     */
    public static function getIsCheckGoods($type = null, $css=null)
    {
        if ($css) {
            $types = [
                '' =>'<span class="label label-primary">请选择</span>',
                '1' =>'<span class="label label-success">是</span>',
                '2' =>'<span class="label label-danger">否</span>',
            ];
        } else {
            $types = [
                '' =>'请选择',
                1=>'是',
                2=>'否',
            ];
        }
        
        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }
    /**
     * 备货逻辑
     */
    public static function getStockLoginType($type = null, $css=null)
    {
        if ($css) {
            $types = [
                '' =>'<span class="label label-primary">请选择</span>',
                '0' =>'<span class="label label-success"></span>',
                '1' =>'<span class="label label-success">定期备货</span>',
                '2' =>'<span class="label label-danger">定量备货</span>',
                '3' =>'<span class="label label-danger">最大最小备货</span>',
            ];
        } else {
            $types = [
                '' =>'请选择',
                0=>'',
                1=>'定期备货',
                2=>'定量备货',
                3=>'最大最小备货',
            ];
        }
        
        if(is_null($type)) {
            return $types;
        }
        return isset($types[$type]) ? $types[$type] : '未知类型';
    }


    /**
     * 按百分比分割金额
     * @author Jolon
     * @param array     $settlement_ratio   请款比例
     * @param float     $sku_total_price    总金额
     * @return array|bool
     */
    public static function divideAmountByPercent($settlement_ratio,$sku_total_price){
        if(!is_array($settlement_ratio) or empty($sku_total_price)) return false;

        $settlement_ratio_price = [];// 按比例分割后的金额
        foreach ($settlement_ratio as $k=>$ratio){
            $ratio = intval($ratio);// 比例转成整数
            $ratio_price = $sku_total_price * $ratio/100;// 当前比例所占的金额
            $settlement_ratio_price[$ratio] = round($ratio_price,3);// 保留三位小数

            if(array_sum($settlement_ratio_price) > $sku_total_price){// 解决保留三位小数 四舍五入后的总金额不等于 原总金额
                unset($settlement_ratio_price[$ratio]);
                $settlement_ratio_price[$ratio] = $sku_total_price - array_sum($settlement_ratio_price);// 直接取剩余的金额
            }
        }
        return $settlement_ratio_price;
    }

}
