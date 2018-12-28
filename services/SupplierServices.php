<?php
namespace app\services;
use app\models\BankConfig;
use app\models\SupplierSettlement;
use Yii;
use app\config\Vhelper;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class  SupplierServices extends BaseServices
{


    /**
     * 获取供应商等级
     * @param $level
     * @return array
     */
    public static function  getSupplierLevel($level=null)
    {
        $levels = [
            '0'=>'',
            '1' =>'A',
            '2' =>'B',
            '3' =>'C',
            '4' =>'D',
            '5'=>'T',
            '6'=>'L'

        ];

        return isset($level) ?  (!empty($levels[$level]) ? $levels[$level] : null):$levels;

    }

    /**
     * 获取合作类型
     * @param $type
     * @return array
     */
    public static function  getCooperation($type=null)
    {
        $types = [
            '1' =>'正常',
            '2' =>'临时',
            '3' =>'备用',

        ];

        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 供应商类型
     * @param $type
     * @return array
     */
    public  static  function  getSupplierType($type=null)
    {
            /*$types = [
                '1' =>'零售',
                '2' =>'批发',
                '3' =>'生产商',
                '4' =>'通用虚拟',
                '5' =>'线上',
                '6' =>'市场',

            ];*/
            $types=[
                '7'=>'贸易商',
                '8'=>'工厂'
            ];

        return !empty($type) ?  isset($types[$type]) ? $types[$type] :'':$types;
    }

    /**
     * 支付周期类型
     * @param $type
     * @return array
     */
    public  static  function  getPaymentCycle($type=null)
    {
        $types = [
            '1' =>'月结',
            '2' =>'隔月结',
            '3' =>'日结',
            '4' =>'周结',
            '5' =>'半月结',

        ];

        return isset($type) ?  $types[$type]:$types;
    }
    /**
     * 结算方式
     * @param $type
     * @return array
     */
    public  static  function  getSettlementMethod($type=null)
    {
        $types = SupplierSettlement::find()->select('supplier_settlement_name,supplier_settlement_code');
        if($type == null){
            $types->andWhere(['settlement_status'=>1]);
        }
        $result = $types->asArray()->all();
        $settleArray = ArrayHelper::map($result,'supplier_settlement_code','supplier_settlement_name');
        return !empty($type) ? isset($settleArray[$type]) ? $settleArray[$type] : '':$settleArray;
    }

    /**
     * 默认支付方式
     * @param string $type
     * @param bool $force true.只查询输入的类型
     * @return array
     */
    public  static  function  getDefaultPaymentMethod($type=null,$force = false)
    {
        if(!empty($type)){
            $types = [
                '1' => '现金',
                '2' => '支付宝',
                '3' => '银行卡转帐',
                '4' => 'PayPal',
                '5' => '富友',
            ];
        }else{
            $types = [
//                '1' => '现金',
                '2' => '支付宝',
                '3' => '银行卡转帐',
//                '4' => 'PayPal',
//                '5' => '富友',
            ];
        }
        if($force){
            return isset($types[$type]) ?  $types[$type]:'';
        }

        return isset($types[$type]) ?  $types[$type]:$types;
    }
    /**
     * 运输承担方
     * @param $type
     * @return array
     */
    public  static  function  getTransportParty($type=null)
    {
        $types = [
            '1' =>'供应商',
            '2' =>'采购方',
        ];
        return isset($type) ?  $types[$type]:$types;

    }

    /**
     * 不良品处理
     * @param null $type
     * @return array
     */
    public  static  function  getBadProductHandling($type=null)
    {
        $types = [
            '1' =>'退货',
            '2' =>'换货',
            '3' =>'采购方承担',
        ];
        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 支付银行获取
     * @param null $type
     * @return array
     */
    public  static  function  getPayBank($type=null)
    {
        $types = BankConfig::find()->select('id,bank_name')->asArray()->all();
        $bankArray = ArrayHelper::map($types,'id','bank_name');
        if(isset($type)){
            return isset($bankArray[$type]) ? $bankArray[$type] :'';
        }else{
            return $bankArray;
        }
    }

    /**
     * 获取状态
     * @param null $type
     * @return array
     */
    public static function  getStatus($type=null)
    {
        $types = [
            '0' => '已删除',
            '1' =>'可用',
            '2' =>'不可用',
        ];
        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 获取支付平台
     * @param null $type
     * @return array
     */
    public static  function  getPaymentPlatform($type=null)
    {
        $showTypeArray=[
            1=>'paypal',
            2=>'财付通',
            3=>'支付宝',
            4=>'快钱',
            5=>'网银',
            6=>'富友转账',
        ];
        $insertTypeArray=[
            6=>'富友转账',
            5=>'网银'
        ];
        return !empty($type) ? isset($showTypeArray[$type]) ? $showTypeArray[$type] :'未知支付平台': $insertTypeArray;
    }
    /**
     * 获取支付平台
     * @param null $type
     * @return array
     */
    public static  function  getAccountType($type=null)
    {
        $types = ['1'=>'对公','2'=>'对私'];
        return isset($type) ?  $types[$type]:$types;

    }
    public static  function  getPayMe($type=null)
    {
        $types = [
            '定期结算' =>'3',
            '货到付款' =>'1',
            '款到发货' =>'2',
            '快递代收' =>'4',
        ];
        return isset($type) ?  $types[$type]:$types;
    }
    public static  function  getPaymentPlatforms($type=null)
    {
        $types = ['PayPal'=>'4','现金'=>'1','银行转账'=>'3','支付宝'=>'2'];
        return isset($type) ?  $types[$type]:$types;

    }

    public static function getFreightPayType(){
        return [
            '1'=>'仓库到付',
            '2'=>'至物流商提付',
            '3'=>'供应商垫付',
        ];
    }

    public static function getUseType(){
        return [
            '1'=>'国内小包',
            '2'=>'海外仓',
            '3'=>'FBA',
        ];
    }

    public static function getSex(){
        return ['1'=>'男','2'=>'女'];
    }

    public static function getCheckStatus(){
        return ['1'=>'待审核','2'=>'已审核'];
    }

    //获取修改申请审核状态
    public static function  getApplyStatus($type=null){
        $types = [
            '1' =>'<span class="label label-primary">'.'待审核'.'</span>',
            '2' =>'<span class="label label-success">'.'审核通过'.'</span>',
            '3' =>'<span class="label label-danger">'.'审核不通过'.'</span>',
        ];

        return isset($type) ?  $types[$type]:$types;
    }

    //获取供应商整合状态
    public static function  getIntegratStatus($type=null,$a=1){
        if($a ==1){
            $types = [
                '1' =>'<span class="label label-primary">'.'待确认'.'</span>',
                '2' =>'<span class="label label-success">'.'整合成功'.'</span>',
                '3' =>'<span class="label label-danger">'.'整合不成功'.'</span>',
            ];
        }else{
            $types = [
                '1' =>'待确认',
                '2' =>'整合成功',
                '3' =>'整合不成功',
            ];
        }

        return isset($type) ?  $types[$type]:$types;
    }
    //获取是否拿样状态
    public static function getSampleStatus($type=null){
        $types = [
            '1' =>'<span class="label label-primary">'.'待确认'.'</span>',
            '2' =>'<span class="label label-danger">'.'否'.'</span>',
            '3' =>'<span class="label label-success">'.'是'.'</span>',
        ];
        return isset($type) ?  $types[$type]:$types;
    }

    public static function getSampleStatusText($type=null){
        $types = [
            '1' =>'待确认',
            '2' =>'否',
            '3' =>'是',
        ];
        return isset($type) ?  $types[$type]:$types;
    }
    //获取供应链、品控收发货状态
    public static function getSendTakeStatus($type=null){
        $types = [
            '1' =>'<span class="label label-danger">'.'否'.'</span>',
            '2' =>'<span class="label label-success">'.'是'.'</span>',
        ];
        return isset($type) ?  $types[$type]:$types;
    }

    //获取样品质检结果
    public static function getSampleResultStatus($type=null){
        $types = [
            '1' =>'<span class="label label-primary">'.'待确认'.'</span>',
            '2' =>'<span class="label label-success">'.'合格'.'</span>',
            '3' =>'<span class="label label-danger">'.'不合格'.'</span>',
        ];
        return isset($type) ?  $types[$type]:$types;
    }
    public static function getSampleResultStatusText($type=null){
        $types = [
            '1' =>'待确认',
            '2' =>'合格',
            '3' =>'不合格',
        ];
        return isset($type) ?  $types[$type]:$types;
    }

    //获取供应商产品修改类型
    public static function getApplyTypeText($type){
        //1 只修改默认供应商单价不变 2，只修改单价 3，单价供应商都修改'
        $typeList = [
            1=>'修改供应商',
            2=>'修改单价',
            3=>'修改单价供应商',
            4=>'修改链接',
            5=>'添加备用供应商',
            6=>'采购单通过审核'
        ];
        return isset($typeList[$type]) ? $typeList[$type] : '未知';
    }

    //获取供应商验厂类型
    public static function getCheckType($type=null){
        $typeList = [
            1=>'首次',
            2=>'二次',
            3=>'多次'
        ];
        return !empty($type) ? $typeList[$type] : $typeList;
    }
    //获取是否开发票
    public static function getInvoice($type=null){
        $typeList = [
            1=>'否',
            2=>'增值税发票',
            3=>'普票'];
        return !empty($type) ? $typeList[$type] : $typeList;
    }
    //获取采购员的状态 ？？
    public static function getBuyerStatus($type=null){
        $typeList = [
            1=>'启用',
            2=>'停用',
            3=>'删除'];

        return !empty($type) ? $typeList[$type] : $typeList;
    }
    //获取支付账号的支付方式
    public static function getPaymentMethod($type=null){
        $typeList = [
            1=>'',
            2=>'在线',
            3=>'银行卡'
        ];

        return !empty($typeList[$type]) ? $typeList[$type] : '';
    }

    //获取供应商验厂原因
    public static function getCheckReason($type=null){
        $typeList = [
            1=>'<span class="label label-info">首次验厂</span>',
            2=>'<span class="label label-danger">产品质量多次不合格</span>',
            3=>'其他',
            4=>'<span class="label label-success">上门验货</span>',
            5=>'<span class="label label-primary">账期</span>'
        ];
        return !empty($type) ? $typeList[$type] : null;
    }

    //获取供应商验厂状态
    public static function getSupplierCheckStatus($status){
        $typeList = [
            1=>'<span class="label label-primary">'.'未安排'.'</span>',
            2=>'<span class="label label-info">'.'已安排'.'</span>',
            3=>'<span class="label label-success">'.'完成'.'</span>',
            4=>'<span class="label label-danger">'.'已删除'.'</span>',
            5=>'<span class="label label-warning">'.'待评价'.'</span>',
            6=>'<span class="label label-default">'.'待采购确认'.'</span>',
            7=>'<span class="label label-info">'.'无资料'.'</span>',
        ];
        return !empty($status) ? $typeList[$status] : null;
    }
    //供应商信息审核状态
    public static function getSupplierAuditStatus($status){
        $typeList = [
            1=>'<span class="label label-default">待采购审核</span>',
            2=>'<span class="label label-danger">'.'采购审核-驳回'.'</span>',
            3=>'<span class="label label-primary">'.'待供应链审核'.'</span>',
            4=>'<span class="label label-danger">'.'供应链审核-驳回'.'</span>',
            5=>'<span class="label label-info">'.'待财务审核'.'</span>',
            6=>'<span class="label label-danger">'.'财务审核-驳回'.'</span>',
            7=>'<span class="label label-success">审核通过</span>',
        ];
        return !empty($status) ? $typeList[$status] : null;
    }

    public static function getSupplierStatus($status){
        $statusArray = [
            1=>'正常',
            2=>'禁用',
            3=>'删除',
            4=>'待审',
            5=>'审核不通过',
        ];
        return $status ? isset($statusArray[$status]) ? $statusArray[$status] :'未知状态':$statusArray;
    }

    //供应商信息审核状态
    public static function getAuditStatus($status=null){
        $typeList = [
           1=>'待采购审核',
           2=>'采购审核-驳回',
           3=>'待供应链审核',
           4=>'供应链审核-驳回',
           5=>'待财务审核',
           6=>'财务审核-驳回',
           7=>'审核通过'
        ];
        return !empty($status) ? $typeList[$status] : $typeList;
    }
    public static function publicInfo($type)
    {
        $typeList = [
            //供应商信息--修改
            'id' => 'ID',
            'supplier_code' => '供应商代码',
            'buyer' => '采购员',
            'merchandiser' => '跟单员',
            'main_category' => '主营品类',
            'supplier_name' => '供应商名',
            'supplier_level' => '供应商等级',
            'supplier_type' => '供应商类型',
            'supplier_settlement' => '供应商结算方式',
            'payment_method' => '支付方式',
            'cooperation_type' => '合作类型',
            'payment_cycle' => '支付周期类型',
            'transport_party' => '运输承担方',
            'product_handling' =>  '不良品处理',
            'commission_ratio' => '供应商佣金比例',
            'purchase_amount' => '合同采购金额',
            'create_time' => '创建时间',
            'update_time' => '修改时间 ',
            'create_id' => '创建人ID',
            'update_id' => '修改人ID',
            'status' => '状态(1启用2停用3删除)',
            'esupplier_name' => '供应商英文名',
            'contract_notice' => '备注',
            'province' => '所在省',
            'city' => '所在市',
            'area' => '所在区',
            'is_push' => '是否推送(0未推送1已推送）',
            'source' => '数据来源（1 erp| 2 purchase）',
            'supplier_address' => '供应商详细地址',
            'is_taxpayer' => '是否为一般纳税人',
            'taxrate' => '税率',
            'use_type' => '部门',
            'business_scope' =>  '经营范围',
            'supplier_status' => '供应商审核: 1 待审核， 2 已审核',
            'financial_status' => '财务审核: 1 待审核， 2 已审核',
            'freight_pay_type' => '运费付费方式',
            'use_type_m' => '部门类型多选',
            'store_link' => '店铺链接',
            'first_cooperation_time' =>  '首次合作时间',
            'invoice' => '是否开发票',
            'search_status' => '搜索状态',
            'credit_code' => '统一社会信用代码',
//供应商支付帐号表--修改
//            'supplier_id' => '供应商ID',
//            'payment_method' => '支付方式',
            'payment_platform' => '支付平台',
            'account_type' => '账户类型',
            'account' => '账户',
            'account_name' => '账户名',
//            'status' => '状态',
            'payment_platform_bank' => '主行名称',
            'payment_platform_branch' => '具体支行名称',
//            'supplier_code' => '供应商代码',
            //供应商联系方式--修改
//            'supplier_id' =>  '',
            'contact_person' => '联系人',
            'contact_number' => '联系电话',
            'corporate' => '法人代表',
            'contact_fax' => 'fax',
            'chinese_contact_address' => '中文联系地址',
            'english_address' => '英文联系地址',
            'contact_zip' => '联系邮编',
            'qq' => 'QQ',
            'micro_letter' => '微信',
            'email' => '邮箱',
            'want_want' => '旺旺',
            'skype' => 'Skype',
            'sex' => '性别',
            //供应商附图 -- 修改????
            'supplier_id' => '图片ID',
            'image_url' => '图片URL',
            //供应商绑定采购员 -- 修改
//            'supplier_code' => '供应商编码',
            'type' => '所属部门 1，国内仓 2，海外仓 3，FBA',
//            'buyer' => '采购员名字',
//            'status' => '',
//            'supplier_name' => '',
            //供应商绑定产品线--修改、新增 !!!!翻译合并
//            'supplier_code' => '供应商编码',
            'first_product_line' => '一级产品线',
            'second_product_line' => '二级产品线',
            'third_product_line' => '三级产品线',
            'prov_code'          =>'分行区域（省）',
            'city_code'          =>'分行区域（市）',
            'phone_number'          =>'到账通知手机号',
            'id_number'          =>'证件号',
//            'status' => '状态',
            'contact_id' => '联系人ID',

        ];
        return !empty($typeList[$type]) ? $typeList[$type] : '未知';
    }

    /**
     * 跨境宝 供应商状态列表
     * @return array
     */
    public static function supplierSpecialFlag(){
        return ['0' => '否', '1' => '是'];
    }

    /**
     * 供应商二次检验原因
     * @param $reasonFromat
     * @param $format 返回数据格式
     */
    public static function getSupplierReviewReason($reasonFormat=null,$format='array'){
        $reasonArray =[
            1=>'生产进度',
            2=>'产品质量',
            3=>'无设备支持',
            4=>'供应商不配合'
        ];
        return $format=='array' ? $reasonArray : (!empty($reasonArray[$reasonFormat])&&isset($reasonArray[$reasonFormat]) ? $reasonArray[$reasonFormat] : '');
    }

}