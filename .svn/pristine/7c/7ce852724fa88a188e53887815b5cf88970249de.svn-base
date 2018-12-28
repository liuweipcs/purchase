<?php
namespace app\services;
use app\config\Vhelper;
use app\models\AlibabaAccount;
use app\models\ProductLine;
use app\models\PurchaseUser;
use app\models\SupervisorGroupBind;
use Yii;
use app\models\BankCardManagement;
use app\models\DataControlConfig;
use app\models\LogisticsCarrier;
use app\models\ProductProvider;
use app\models\Supplier;
use app\models\User;
use app\models\Warehouse;
use app\models\Region;
use yii\helpers\ArrayHelper;
use app\models\ProductCategory;
use linslin\yii2\curl;
use yii\helpers\Json;
/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:42
 */
class  BaseServices
{
    /***
     * 获取所有的用户名  作用于采购员与跟单员下拉列表
     * @return mixed
     */
   public static  function  getEveryOne($id=null,$name=null)
   {
        $User   = User::find()->select('id,username,alias_name');

       if (!empty($id))
       {
           $User->andWhere(['id' => $id]);
           $result = $User->asArray()->one();
           return $result['username'];
       } else{
           $user= $User->asArray()->all();
           $result = empty($name)?ArrayHelper::map($user,'id','username'):ArrayHelper::map($user,'username','username');
           arsort($result);
           return $result;
       }


   }

    /***
     * 根据条件获取用户信息
     * @return mixed
     */
    public static function getInfoByCondition ($param)
    {
        return User::find()->select('id,username,alias_name')->where($param)->asArray()->one();
    }

    /**
     * 获取品类
     * 有id就单条  没有就查全部
     * @param null $id
     * @return array|mixed|null|\yii\db\ActiveRecord
     */
    public static  function  getCategory($id=null)
    {
        $User   = ProductCategory::find()->select('id,category_cn_name')->where(['category_status' => 1]);

        if (!empty($id))
        {
            $User->andWhere(['id'=>$id]);
            $result = $User->asArray()->one();
            return $result['category_cn_name'];
        } else {

            $user= $User->asArray()->all();
            $result = ArrayHelper::map($user,'id','category_cn_name');
            return $result;
        }


    }

    /***
     * 获取1688账号
     * @return mixed
     */
    public static  function  getAlibaba($id=null,$name=null)
    {
        $Alibaba   = AlibabaAccount::find()->select('id,account')->where(['status' => 1]);

        if (!empty($id))
        {
            $Alibaba->andWhere(['id' => $id]);
            $result = $Alibaba->asArray()->one();
            return $result['username'];
        } else{
            $alibaba= $Alibaba->asArray()->groupBy('account')->all();
            $result = empty($name) ? ArrayHelper::map($alibaba,'account','account') : ArrayHelper::map($alibaba,'account','account');
            return $result;
        }
    }
    /**
     * 获取产品线
     * 有id就单条  没有就查全部
     * @param null $id
     * @return array|mixed|null|\yii\db\ActiveRecord
     */
    public static  function  getProductLine($id=null)
    {
        $User   = ProductLine::find()->select('product_line_id,linelist_cn_name');

        $firstId = self::getProductLineFirst($id);
        if(!empty($id)&&empty($firstId)){
            return '数据有误提醒更新';
        }
        if (!empty($firstId))
        {
            $User->andWhere(['product_line_id'=>$firstId]);
            $result = $User->asArray()->one();
            return $result['linelist_cn_name'];
        } else {
            $User->where(['linelist_parent_id' =>0]);
            $user= $User->asArray()->all();
            $result = ArrayHelper::map($user,'product_line_id','linelist_cn_name');
            return $result;
        }


    }

    /**
     * 获取子级产品线
     * 有id就单条  没有就查全部
     * @param null $id
     * @return array|mixed|null|\yii\db\ActiveRecord
     */
    public static  function  getProductLineList($id=null)
    {
        $User   = ProductLine::find()->select('product_line_id,linelist_cn_name');
        $User->where(['linelist_parent_id' =>$id]);
        $user= $User->asArray()->all();
        $arr['']='--请选择--';
        $result = ArrayHelper::map($user,'product_line_id','linelist_cn_name');
        return $arr+$result;
    }

    /**
     * 获取产品产品线一级
     * @param null $id
     * @return mixed|null|string
     */
    public static  function  getProductLineFirst($id=null)
    {
        $line = ProductLine::find()->where(['product_line_id'=>$id])->one();
        if(empty($line)){
            return '';
        }
        if($line->linelist_parent_id == 0){
            return $line->product_line_id;
        }
        $parentLine = ProductLine::find()->where(['product_line_id'=>$line->linelist_parent_id])->one();
        if(empty($parentLine)){
            return $line->product_line_id;;
        }
        if($parentLine->linelist_parent_id == 0){
            return $parentLine->product_line_id;
        }
        $firstLine  = ProductLine::find()->where(['product_line_id'=>$parentLine->linelist_parent_id])->one();
        if(empty($firstLine)){
            return $parentLine->product_line_id;
        }
        return $firstLine->product_line_id;
    }

    /**
     * 获取产品线所有子级编号
     * @param null $id
     * @return array|mixed|string
     */
    public static  function  getProductLineChild($id=null)
    {
        $line = ProductLine::find()->where(['product_line_id'=>$id])->one();
        if(empty($line)){
            return [];
        }
        $firstChildLine[] = $line->product_line_id;
        $secondChildLine  = ProductLine::find()->select('product_line_id')->where(['linelist_parent_id'=>$line->product_line_id])->asArray()->all();
        $secondChildLine = ArrayHelper::getColumn($secondChildLine,'product_line_id');
        if($secondChildLine){
            $thirdChildLine   = ProductLine::find()->select('product_line_id')->andFilterWhere(['in','linelist_parent_id',$secondChildLine])->asArray()->all();
            $thirdChildLine  = ArrayHelper::getColumn($thirdChildLine,'product_line_id');
        }else{
            $thirdChildLine = [];
        }
        return array_merge($firstChildLine,$secondChildLine,$thirdChildLine);
    }

    /**
     * 获取供应商名与code
     * 有id就单条  没有就查全部
     * @param null $id
     * @return array|mixed|null|\yii\db\ActiveRecord
     */
    public static  function  getSupplier($id=null)
    {
        $User   = Supplier::find()->select('supplier_code,supplier_name')->where(['status' => 1]);

        if (!empty($id))
        {
            $User->andWhere(['id'=>$id]);
            $result = $User->asArray()->one();
            return $result['supplier_name'];
        } else {
            $user = $User->asArray()->limit(200)->all();
//            $key  = md5(__CLASS__);
//            $data = \Yii::$app->cache->get($key);
//            if ($data===false)
//            {
//                \Yii::$app->cache->set($key, $user,60*60);
//            }
            $result = ArrayHelper::map($user,'supplier_code','supplier_name');
            return $result;
        }


    }

    /**
     * 通过供应商编码获取供应商名
     * @param $code
     * @return false|null|string
     */
    public static function  getSupplierName($code)
    {
        $name = Supplier::find()->select('supplier_name')->where(['supplier_code'=>$code])->scalar();
        return $name;
    }
    /**
     * 通过供应商编码获取供应商名
     * @param $name
     * @param $filed
     * @return false|null|string
     */
    public static function  getSupplierCode($name,$filed='*')
    {
        $name = Supplier::find()->select($filed)->where(['status' =>1])->andWhere(['like','supplier_name',$name])->scalar();
        return $name;
    }

    /**
     * 获取默认供应商
     * @param $code
     * @return static
     */
    public static function  getDefaultSupplier($code,$sku)
    {

       $codes = ProductProvider::findOne(['sku'=>$sku,'supplier_code'=>$code,'is_supplier'=>1]);
        if($codes)
        {
            return '<i class="glyphicon glyphicon-user" ></i>';
        }
        return ;
    }

    /**
     * 获取仓库代码
     * @param null $id
     * @return array|mixed|null|\yii\db\ActiveRecord
     */

    public  static  function  getWarehouseCode($id=null)
    {
        $Warehouse   = Warehouse::find()->select('id,warehouse_code,warehouse_name')->where(['use_status'=>1]);

        if (!empty($id))
        {
            $Warehouse->andWhere(['warehouse_code'=>$id]);
            $result = $Warehouse->asArray()->one();
            return $result['warehouse_name'];
        } else {

            $Warehouse= $Warehouse->asArray()->all();
            $result = ArrayHelper::map($Warehouse,'warehouse_code','warehouse_name');
            return $result;
        }
    }

    /**
     * 获取物流供应商
     * @param null $id
     * @return array|mixed|null|\yii\db\ActiveRecord
     */

    public  static  function  getLogisticsCarrier($id=null, $name = 'carrier_code')
    {
        $Warehouse   = LogisticsCarrier::find()->select('id,carrier_code,name')->where(['status'=>1]);

        if (!empty($id))
        {
            $Warehouse->andWhere(['carrier_code'=>$id]);
            $result = $Warehouse->one();
            return $result;
        } else {

            $Warehouse= $Warehouse->asArray()->all();
            $result = ArrayHelper::map($Warehouse,$name,'name');
            return $result;
        }
    }
    

    /**
     * 获取银行卡管理
     * @param null $id
     * @return array|mixed|null|\yii\db\ActiveRecord
     */
    public static function  getBankCard($id=null,$field='branch')
    {
        $Warehouse   = BankCardManagement::find()->select('id,branch,account_abbreviation,account_holder,account_number')->where(['status'=>1]);

        if (!empty($id))
        {
            $Warehouse->andWhere(['id'=>$id]);
            $result = $Warehouse->asArray()->one();
            return $result;
        } else {

            $Warehouse= $Warehouse->asArray()->all();
            $result = ArrayHelper::map($Warehouse,'id',$field);
            return $result;
        }
    }

    /**
     * 获取国家
     * @param $pid
     * @return array
     */
    public static function getCityList($pid)
    {
        $model = Region::findAll(array('pid'=>$pid));
        return ArrayHelper::map($model, 'id', 'region_name');
    }

    /**
     * 获取亚马逊分组
     * @return bool
     */
    public static function getAmazonGroup(){
        $group_value=DataControlConfig::find()->where(['type'=>'amazon_supervisor_group'])->one()->values;
        $groupArr['all'] = '全部';
        $userGroup = SupervisorGroupBind::find()->where(['supervisor_name'=>Yii::$app->user->identity->username])->all();
        $groupId   = [];
        if(!empty($userGroup)){
            foreach($userGroup  as $value){
                $groupId[] = $value->group_id;
            }
        }
        if(!empty($group_value)&&empty($groupId)){
            $group_value = SupervisorGroupBind::find()->select('group_id')->groupBy('group_id')->asArray()->all();
            $resultArray= ArrayHelper::map($group_value,'group_id','group_id');
            foreach ($resultArray as $key=>$va){
                $groupArr[$key] = $va;
            }
        }
        if(!empty($groupId)){
            foreach($groupId as $v){
                $groupArr[$v] = $v;
            }
        }

        return $groupArr;
    }

    //通过登录名称获取销售分组
    public static function getGroupByUserName($type=1){
        $userGroup = SupervisorGroupBind::find()->where(['supervisor_name'=>Yii::$app->user->identity->username])->all();
        if($type==1){
            if(!empty($userGroup)){
                return $userGroup[0]->group_id;
            }else{
                return 'all';
            }
        }else{
            $groupId   = [];
            if(!empty($userGroup)){
                foreach($userGroup  as $value){
                    $groupId[] = $value->group_id;
                }
            }
            return $groupId;
        }
    }

    //获取分组名称
    public static function getAmazonGroupName($groupId){
//        $group_value=DataControlConfig::find()->where(['type'=>'amazon_supervisor_group'])->one()->values;
//        if($group_value) {
//            for ($i = 1; $i <= $group_value; $i++) {
//                $groupArr[$i] = Yii::t('app', '第' . $i . '组');
//            }
//        }
//        $groupArr[1000] = Yii::t('app', '第1000组');
        $group_value = SupervisorGroupBind::find()->select('group_id')->groupBy('group_id')->asArray()->all();
        $groupArr = ArrayHelper::map($group_value,'group_id','group_id');
        return isset($groupArr[$groupId]) ? $groupArr[$groupId] : '';
    }

    //根据角色名称获取用户名
    public static  function  getBuyerByRoleName($rolename,$name=null)
    {
        if(is_array($rolename)&&!empty($rolename)){
            $ids=[];
            foreach($rolename as $value){
                $buyer = Yii::$app->authManager->getUserIdsByRole($value);
                if(!empty($buyer)&&is_array($buyer)){
                    foreach($buyer as $v){
                        $ids[]=$v;
                    }
                }
            }
        }else{
            $ids = Yii::$app->authManager->getUserIdsByRole($rolename);
        }
        $User   = User::find()->select('id,username,alias_name')->where(['status' => User::STATUS_ACTIVE,'id'=>$ids]);
        $user= $User->asArray()->all();
        $result = empty($name)?ArrayHelper::map($user,'id','username'):ArrayHelper::map($user,'username','username');
        return $result;
    }

    //根据登录用户权限获取采购员
    public static function getBuyer($name=null){
        
//        $userRoleName = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
//        $fbaResult = [];
//        $hwcResult = [];
//        $inlandResult = [];
//        //如果用户角色包含FBA采购组，FBA采购经理组，FBA销售组，FBA销售经理组则返回FBA采购组
////        if(array_key_exists('FBA采购组',$userRoleName)||array_key_exists('FBA采购经理组',$userRoleName)||array_key_exists('FBA销售组',$userRoleName)||array_key_exists('FBA销售经理组',$userRoleName)){
////            $fbaBuyer = PurchaseUser::find()->andFilterWhere(['type'=>2])->asArray()->all();
////            $fbaResult= empty($name) ? ArrayHelper::map($fbaBuyer,'pur_user_id','pur_user_name') : ArrayHelper::map($fbaBuyer,'pur_user_name','pur_user_name');
////        }
//        /*
//         * 暂时屏蔽其他角色的下拉列表限制*/
//        //如果用户角色包含采购组-国内则返回国内采购组
////        if(array_key_exists('采购组-国内',$userRoleName)){
////            $inlandBuyer = PurchaseUser::find()->andFilterWhere(['type'=>0])->asArray()->all();
////            $inlandResult= empty($name) ? ArrayHelper::map($inlandBuyer,'pur_user_id','pur_user_name') : ArrayHelper::map($inlandBuyer,'pur_user_name','pur_user_name');
////        }
////        //如果用户角色包含采购组-海外则返回海外采购组
////        if(array_key_exists('采购组-海外',$userRoleName)){
////            $hwcBuyer = PurchaseUser::find()->andFilterWhere(['type'=>1])->asArray()->all();
////            $hwcResult= empty($name) ? ArrayHelper::map($hwcBuyer,'pur_user_id','pur_user_name') : ArrayHelper::map($hwcBuyer,'pur_user_name','pur_user_name');
////        }
//        $result = array_unique(array_merge($fbaResult,$hwcResult,$inlandResult));
//        if(empty($result) || array_key_exists('超级管理员组',$userRoleName)){
//            $User   = User::find()->select('id,username,alias_name')->where(['status' => User::STATUS_ACTIVE]);
//            $user   = $User->asArray()->all();
//            $result = empty($name) ? ArrayHelper::map($user,'id','username'):ArrayHelper::map($user,'username','username');
//        }
        $User   = User::find()->select('id,username,alias_name')->where(['status' => User::STATUS_ACTIVE]);
        $user   = $User->asArray()->all();
        $result = empty($name) ? ArrayHelper::map($user,'id','username'):ArrayHelper::map($user,'username','username');
        return $result;
    }
    /**
     * 判断用户是否是超级管理员
     * 操作类型：1国内，2海外，3FBA
     */
    public static function getIsAdmin($purchase_type=null)
    {
        $userRoleName = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
        $username = Yii::$app->user->identity->username;

        if ($purchase_type ===1) {
            $bool = SupervisorGroupBind::getGroupPermissions(41); //国内，审核权限
        } elseif ($purchase_type ===2) {
            $bool = SupervisorGroupBind::getGroupPermissions(42); //海外,审核权限
        } elseif ($purchase_type ===3) {
            $bool = SupervisorGroupBind::getGroupPermissions(43); //FBA，审核权限
        } else {
            $bool = false;
        }

        if (array_key_exists('超级管理员组',$userRoleName) || $bool ) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 将数组转换成字符串，用于存入数据库
     */
    public static function getStrData($data) {
        $str = '';
        foreach ($data as $dk=>$dv) {
            $str .= "{$dk}:=>{$dv},";
        }
        return $str;
    }

    public static function getBuyerCompany($is_drawback, $name = '')
    {
        $companys = [
            1 => [
                'name' => 'YIBAI TECHNOLOGY LTD',
                'address' => 'UNIT 04,7/F BRIGHT WAY TOWER NO.33 MONG KOK RD KL',
            ],
            2 => [
                'name' => '深圳市易佰网络科技有限公司',
                'address' => '深圳市龙华新区清湖社区清祥路清湖科技园二区B栋701',
            ],
        ];
        if ($name) {
            return isset($companys[$is_drawback]) ? $companys[$is_drawback][$name] : '';
        }
        return isset($companys[$is_drawback]) ? $companys[$is_drawback] : [];
    }
    /**
     * 获取销售账号
     */
    public static  function  getXiaoshouZhanghao($sales=false)
    {
        $return_res = $xiaoshou_zhanghao = [];

        $curl = new curl\Curl();
        $curl->setOption(CURLOPT_TIMEOUT,100);
        $url = Yii::$app->params['ERP_URL'] . '/services/amazon/amazonfbauser/index/user_name/' . $sales;
        $s = $curl->setPostParams([])->post($url);
        //验证json
        $sb = Vhelper::is_json($s);

        if(!$sb) {
            echo '请检查json'."\r\n";
            exit($s);
        } else {
            $xiaoshou_zhanghao = Json::decode($s);
        }
        if (empty($sales)) $return_res = [''=>'请选择'];
        if(!empty($xiaoshou_zhanghao) AND is_array($xiaoshou_zhanghao)){
            $return_res = array_merge($return_res, $xiaoshou_zhanghao);
        }

        return $return_res;
    }
}