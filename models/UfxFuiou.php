<?php
namespace app\models;

use app\models\base\BaseModel;
use app\config\Vhelper;
use linslin\yii2\curl\Curl;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 20:42
 */
class UfxFuiou {
    private    $partnerId ='';//商户代码
    private    $md5Key ='';//密码解析密钥
    private  $transNo='';
    private  $timeStamp='';
    private  $messageVersion='001';
    private  $dataDirection='R';  //R-请求 A-应答
    private  $messageCode='011';
    private  $encryptionCode='100';
    private  $isNeedReview = '02'; //是否需要复核，01否02 是默认是
    private  $fuiouUrl='';
    //private  $fuiouUrl= 'http://www-1.fuiou.com:19032/eics/common';//测试地址
    //private  $fuiouUrl= 'https://ufx.fuiou.com/eics/common';//线上地址
    //private  $backNotifyUrl = 'http://caigou.yibainetwork.com/v1/ufx-fuiou/accept-result';//银行卡转账回调地址
    private  $requestParams =[];
    private  $payAccount='pay_001';

    /*
     * 详情地址http://www-1.fuiou.com:19032/onloadDoc/toLinkAddress?linkAddress=appendix_tradeControl
     * 交易控制码 messageCode
     * 001  	账户充值
     * 002  	余额查询
     * 003  	交易明细查询
     * 011  	转账至银行卡
     * 176  	电子回单
     * 301  	基本信息校验
     *
     */

    //类实例化是设置流水号和当前时间戳
    function __construct($params=[]){
        if(is_array($params)){
            foreach ($params as $k=>$v){
                if(property_exists($this,$k)){
                    $this->$k = $v;
                }
            }
        }
        $this->transNo = date('YmdHis',time()).'YIBAI'.substr(md5(uniqid(rand())), 0, 11);
        $this->timeStamp = date('YmdHis',time());
        //设置商户id和密钥
        $this->setPayInfo($this->payAccount);
    }

    //单独设置属性值
    public function setOption($name,$value){
        if(property_exists($this,$name)){
            $this->$name = $value;
        }
    }

    //批量设置属性值
    public function setOptions($optionValues){
        foreach ($optionValues as $key=>$value){
            if(property_exists($this,$key)){
                $this->$key = $value;
            }
        }
    }

    //获取指定属性值
    public function getOption($name){
        if(property_exists($this,$name)){
            return $this->$name;
        }
        return '';
    }

    //获取请求头部信息
    public function getHeadParams(){
        return [
            'partnerId'=>$this->partnerId,
            'transNo'=>$this->transNo,
            'timeStamp'=>$this->timeStamp,
            'messageVersion'=>$this->messageVersion,
            'dataDirection'=>$this->dataDirection,  //R-请求 A-应答
            'messageCode'=>$this->messageCode,
            'encryptionCode'=>$this->encryptionCode,
        ];
    }

    //数组转XML
    public function getXmlStr($paramsArray){
        $str = '';
        if(empty($paramsArray)){
            return '';
        }
        foreach ($paramsArray as $key=>$value){
            if(is_array($value)){
                $str.="<$key>".$this->getXmlStr($value)."</$key>";
            }else{
                $str.="<$key>".$value."</$key>";
            }
        }
        return $str;
    }

    //获取请求内容字符串
    public function getRequestStr($headParams,$bodyParams){
        $headStr = $this->getXmlStr($headParams);
        $bodyStr = $this->getXmlStr($bodyParams);
        $requestStr = "<?xml version='1.0' encoding='UTF-8' ?><AP><head>$headStr</head>
            <body>$bodyStr</body></AP>";
        return['head'=>"<head>$headStr</head>",'body'=>"<body>$bodyStr</body>",'requestStr'=>$requestStr];
    }

    //获取请求签名
    public function getSign($requestStr){
         return md5($requestStr.'|'.$this->md5Key);
    }

    //xml转数组
    public function xml_unserialize($xml){
        $objectxml = @simplexml_load_string($xml);//将文件转换成 对象
        $xmljson= json_encode($objectxml );//将对象转换个JSON
        $xmlarray=json_decode($xmljson,true);
        return $xmlarray;
    }

    //拼接接口头部信息和具体参数信息
    public function request($log=true){
        $headParams = self::getHeadParams();
        $requestInfo = self::getRequestStr($headParams,$this->requestParams);
        $response = self::ufxCurl($requestInfo['requestStr']);
        if($log){
            UfxfuiouRequestLog::saveRequestLog($this->transNo,$requestInfo['requestStr'],$response);
        }
        return $response;
    }

    //富友接口请求方法;返回XML字符串
    public function ufxCurl($request){
        $sign = self::getSign($request);
        $curl = new Curl();
        $response = $curl->setPostParams(['reqStr'=>$request,'sign'=>$sign])
            ->post($this->fuiouUrl);
        return $response;
    }

    //获取XML字符串之间的内容，index 0 包含标签自己 1 不包含标签自己 ，不支持嵌套标签
    public static function getXmlStrByTag($xmlString,$tagName,$index=0){
        $pattern = "/<$tagName>(.*?)<\/$tagName>/";
        preg_match($pattern, $xmlString, $matches);
        return isset($matches[$index]) ? $matches[$index] :'';
    }

    //验证银行卡转账接口数据完整性
    public static function checkPayDatas($payDatas){
        $ids = isset($payDatas['ids']) ? $payDatas['ids'] :'';
        if(empty($ids)){
            return ['status'=>'error','message'=>'待付款数据为空'];
        }
        $payStatusArray = PurchaseOrderPay::find()->select('pay_status')->where(['id'=>explode(',',$ids)])->column();
        if(count(array_unique($payStatusArray))!=1||!in_array(4,$payStatusArray)){
            return ['status'=>'error','message'=>'请款数据不是待付款数据，请检查仔细！'];
        }
        if(!isset($payDatas['Fuiou'])){
            return ['status'=>'error','message'=>'必要参数为空'];
        }
        if(empty($payDatas['Fuiou']['PayAccount'])){
            return ['status'=>'error','message'=>'富友账号不能为空'];
        }
        if(empty($payDatas['Fuiou']['bankCardTp'])){
            return ['status'=>'error','message'=>'收款方卡属性不能为空'];
        }
        if(empty($payDatas['Fuiou']['oppositeName'])){
            return ['status'=>'error','message'=>'账户名不能为空'];
        }
        if(empty($payDatas['Fuiou']['oppositeIdNo'])){
            return ['status'=>'error','message'=>'证件号不能为空'];
        }
        if(empty($payDatas['Fuiou']['bankNo'])){
            return ['status'=>'error','message'=>'主行不能为空'];
        }
        if(empty($payDatas['Fuiou']['bankId'])){
            return ['status'=>'error','message'=>'支行不能为空'];
        }
        if(empty($payDatas['Fuiou']['provNo'])){
            return ['status'=>'error','message'=>'省不能为空'];
        }
        if(empty($payDatas['Fuiou']['cityNo'])){
            return ['status'=>'error','message'=>'市不能为空'];
        }
        if(empty($payDatas['Fuiou']['amt'])){
            return ['status'=>'error','message'=>'转账金额不能为空'];
        }
        if(!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $payDatas['Fuiou']['amt'])){
			return ['status'=>'error','message'=>'转账金额最多两位小数'];
        }
        if(empty($payDatas['Fuiou']['isNotify'])){
            return ['status'=>'error','message'=>'是否通知不能为空'];
        }
        if($payDatas['Fuiou']['isNotify']=='01'&&empty($payDatas['Fuiou']['oppositeMobile'])){
            return ['status'=>'error','message'=>'到账通知为是,手机号码不能为空'];
        }
        //20180921 1508 备注可以为空
//        if(empty($payDatas['Fuiou']['remark'])){
//            return ['status'=>'error','message'=>'备注不能为空'];
//        }
        return ['status'=>'success','message'=>'数据校验成功'];
    }

    //验证返回数据
    public function checkResponse($response){
        $bodyString = self::getXmlStrByTag($response,'body');
        $responseArray= self::xml_unserialize($response);
        if(!isset($responseArray['body'])||!isset($responseArray['sign'])){
            return false;
        }
        $responseVerify = self::verifyReturnInfo($bodyString,$responseArray['sign']);
        if($responseVerify){
            return $response;
        }
        return false;
    }

    //验证接口返回数据是否正确
    public function verifyReturnInfo($xmlString,$sign){
        return md5($xmlString.'|'.$this->md5Key)===$sign;
    }

    /*
     * $bodyParams = [
     * 'backNotifyUrl'=>''//回调地址
            'bankCardNo'=>'6226622201525753',
            'oppositeName'=>'田俊楚',
            'oppositeIdNo'=>'330226199703145970',
            'oppositeMobile'=>'15026831102',
            'bankCardTp'=>'01',
            'bankNo'=>'0001',
            'cityNo'=>'0101',
            'amt'=>'10',
            'remark'=>'测试',
            'isNeedReview'=>'02',
            'bankId'=>'402303000139',
        ];
        请求富友银行卡转账接口
     */
    //银行卡转账接口实现方法
    public static function bankCardPay($payDatas){
        $dataCheck = self::checkPayDatas($payDatas);
        if($dataCheck['status']=='error'){
            return $dataCheck;
        }
        $payModel = new self(['messageCode'=>'011','payAccount'=>$payDatas['Fuiou']['PayAccount']]);
        $bodyParams = [
            'backNotifyUrl'=>$payModel->backNotifyUrl,
            'bankCardNo'=>$payDatas['Fuiou']['bankCardNo'],
            'oppositeName'=>$payDatas['Fuiou']['oppositeName'],
            'oppositeIdNo'=>$payDatas['Fuiou']['oppositeIdNo'],
            'oppositeMobile'=>$payDatas['Fuiou']['oppositeMobile'],
            'bankCardTp'=>$payDatas['Fuiou']['bankCardTp'],
            'bankNo'=>$payDatas['Fuiou']['bankNo'],
            'cityNo'=>$payDatas['Fuiou']['cityNo'],
            'amt'=>$payDatas['Fuiou']['amt']*100,
            'remark'=>$payDatas['Fuiou']['remark'],
            'isNeedReview'=>$payModel->isNeedReview,
            'bankId'=>$payDatas['Fuiou']['bankId'],
        ];
        $payModel->requestParams = $bodyParams;
        $response = $payModel->request();
        $checkResponse = $payModel->checkResponse($response);
        if($checkResponse){
            $responseBody = self::getXmlStrByTag($checkResponse,'body',0);
            $responseBodyArray = $payModel->xml_unserialize($responseBody);
            return ['status'=>'success','tran'=>$payModel->transNo,'response'=>$checkResponse,'responseBody'=>$responseBodyArray];
        }
        return ['status'=>'error','message'=>'富友接口请求失败'];
    }


    public static function getPayBack($tranNum){
        $payModel = new self(['messageCode'=>'176']);
        $bodyParams = [
            'origSsn'=>$tranNum,
        ];
        $payModel->requestParams = $bodyParams;
        $response = $payModel->request($log=false);
        if(strpos($response,'PDF')){
            $pickey = Vhelper::genuuid();
            $filename = $pickey;
            $uploadpath = 'Uploads/' . date('Ymd') . '/';  //上传路径
            // 图片保存在本地的路径：images/Uploads/当天日期/文件名，默认放置在basic/web/下
            $dir = '/images/' . $uploadpath;
            //如果文件夹不存在，则新建文件夹
            $filepath = Vhelper::fileExists(\Yii::getAlias('@app') . '/web' . $dir);
            file_put_contents($filepath.$filename.'.pdf',$response);
            $pur_tran_num =UfxfuiouPayDetail::find()->select('pur_tran_num')->where(['ufxfuiou_tran_num'=>$tranNum])->scalar();
            $requisition_number =PurchaseOrderPayUfxfuiou::find()
                ->select('requisition_number')
                ->where(['pur_tran_num'=>$pur_tran_num])
                ->andWhere(['status'=>1])->column();
            $tran = \Yii::$app->db->beginTransaction();
            try{
                UfxfuiouPayDetail::updateAll(['is_get_back'=>1],['ufxfuiou_tran_num'=>$tranNum]);
                PurchaseOrderPay::updateAll(['images'=>json_encode([$dir.$filename.'.pdf'])],['requisition_number'=>$requisition_number]);
                $tran->commit();
                return ['status'=>true,'message'=>'付款回单抓取成功'];
            }catch (Exception $e ){
                $tran->rollBack();
                return ['status'=>false,'message'=>$e->getMessage()];
            }
        }else{
            return ['status'=>false,'message'=>serialize($response)];
        }
    }
    //查询交易详情
    public static function getTransferResult($pur_tran_no){
        $ufxiouModel = new self(['messageCode'=>'003']);
        $bodyParams = ['transNo'=>$pur_tran_no];
        $ufxiouModel->requestParams = $bodyParams;
        $response = $ufxiouModel->request();
        $checkResponse = $ufxiouModel->checkResponse($response);
        if($checkResponse){
            $responseBody = self::getXmlStrByTag($checkResponse,'body',0);
            $responseBodyArray = $ufxiouModel->xml_unserialize($responseBody);
            return ['status'=>'success','tran'=>$pur_tran_no,'response'=>$checkResponse,'responseBody'=>$responseBodyArray];
        }else{
            return ['status'=>'error','tran'=>$pur_tran_no,'response'=>$checkResponse,'responseBody'=>[]];
        }
    }

    //获取银行主行信息
    public static function getMasterBankInfo($bankCode=null){
        $bankInfo =  MasterBankInfo::find()->select('bank_code,master_bank_name')->asArray()->all();
        $masterBank = ArrayHelper::map($bankInfo,'bank_code','master_bank_name');
        if(empty($bankCode)){
            return $masterBank;
        }
        return isset($masterBank[$bankCode]) ? $masterBank[$bankCode] :'';
    }

    public static function getProvInfo($provCode=null){
        $provInfo = BankCityInfo::find()->select('prov_code,prov_name')->groupBy('prov_code')->asArray()->all();
        $provInfo = ArrayHelper::map($provInfo,'prov_code','prov_name');
        if(empty($provCode)){
            return $provInfo;
        }
        return isset($provInfo[$provCode]) ? $provInfo[$provCode] :'';
    }
    public static function getCityInfo($cityCode=null,$prov=null){
        $query = BankCityInfo::find()->select('city_code,city_name')->groupBy('city_code');
        if(!empty($prov)){
            $query->andFilterWhere(['prov_code'=>$prov]);
        }
        $cityInfo = $query->asArray()->all();
        $cityInfo = ArrayHelper::map($cityInfo,'city_code','city_name');
        if(empty($cityCode)){
            return $cityInfo;
        }
        return isset($cityInfo[$cityCode]) ? $cityInfo[$cityCode] :'';
    }

    //返回支付账号列表
    public static function getPayAccount($format='array',$payAccount=null){
        if(\Yii::$app->request->hostInfo =='http://caigou.yibainetwork.com'){
            $pay_array = [
                'pay_001'=>'YIBAI TECHNOLOGY LIMITED',
            ];
        }else{
            $pay_array = [
                'pay_001'=>'11113333@qq.com',
            ];
        }
        if($format=='array'){
            return $pay_array;
        }else{
            return !empty($payAccount)&&isset($pay_array[$payAccount]) ? $pay_array[$payAccount]:'找不到改支付账号';
        }
    }

    //根据支付账号给对象赋值
    public  function setPayInfo($payAccount){
        if(\Yii::$app->request->hostInfo =='http://caigou.yibainetwork.com'){
            $payinfoArray = [
                'pay_001'=>[
                    'partnerId'=>'FL000049',
                    'md5Key'=>'vXLfAYBXexpRKFJWmySrlEEZDZgZB6NX',
                    'fuiouUrl'=>'https://ufx.fuiou.com/eics/common',
                    'backNotifyUrl'=>'http://caigou.yibainetwork.com/v1/ufx-fuiou/accept-result',
                    ]
            ];
        }else{
            $payinfoArray = [
                'pay_001'=>[
                    'partnerId'=>'FL000008',
                    'md5Key'=>'XQQfxYEHn6lV9ipfE1cVkX3sC8CRKlov',
                    'fuiouUrl'=>'http://www-1.fuiou.com:19032/eics/common',
                    'backNotifyUrl'=>\Yii::$app->request->hostInfo.'/v1/ufx-fuiou/accept-result',
                ]
            ];
        }
        $this->partnerId = isset($payinfoArray[$payAccount]['partnerId']) ? $payinfoArray[$payAccount]['partnerId'] :'';
        $this->md5Key = isset($payinfoArray[$payAccount]['md5Key']) ? $payinfoArray[$payAccount]['md5Key'] :'';
        $this->fuiouUrl = isset($payinfoArray[$payAccount]['fuiouUrl']) ? $payinfoArray[$payAccount]['fuiouUrl'] :'';
        $this->backNotifyUrl = isset($payinfoArray[$payAccount]['backNotifyUrl']) ? $payinfoArray[$payAccount]['backNotifyUrl'] :'';
    }
}