<?php

namespace app\controllers;

use yii;
use yii\db\Query;
use app\models\PurchaseCompact;
use app\models\PurchaseCompactItems;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderPayType;

/**
 * Created by PhpStorm.
 * User: Jolon
 * Date: 2018/10/25
 * Time: 15:33
 */
class CorrectionDataController extends BaseController{

    public static $_type = null;

    /**
     * 获取 数据保存路径
     * @return string
     */
    public static function getDataFilePath(){
        $filePath = Yii::$app->basePath.'/web/files/correction-data/';
        return $filePath;
    }

    /**
     * 判断文件夹是否存在，不存在则尝试创建文件夹
     * @param $fileDir
     * @return bool
     */
    public static function createLogDir($fileDir){

        $result_flag = false;
        if(!is_dir($fileDir)){// 判断文件夹是否存在，不存在则创建文件夹
            $num = 3;
            do{
                $num -- ;
                if(mkdir($fileDir,0777)){
                    $result_flag = true;
                }
            }while($num > 0 AND $result_flag === false);
        }else{
            if(chmod($fileDir, 0777)){// 设置可写
                $result_flag = true;
            }
        }

        return $result_flag;
    }

    /**
     * 判断 文件是否存在，不存在则尝试创建文件
     * @param $file_name
     * @return bool
     */
    public static function createLogFile($file_name){
        $fileDir =  self::getDataFilePath().'logs/';
        $filePath = $fileDir.$file_name;

        $result_flag = false;
        if(file_exists($filePath)){// 判断日志文件是否存在
            if(is_writable($filePath) || chmod($file_name,0777)){// 判断文件可写权限，没有权限则设置权限
                $result_flag = true;
            }
        }else{
            $flag = self::createLogDir($fileDir);
            if($flag){// 文件夹创建成功，创建日志文件
                if( fopen($filePath, "w") ){
                    $result_flag = true;
                }
            }
        }

        if($result_flag) return $filePath;

        return $result_flag;
    }

    /**
     * 保存日志信息
     * @param $file_name
     * @param $log_list
     * @return int
     */
    public static function saveLogs($file_name,$log_list){
        $success = 0;
        $file_name = $file_name.'-'.date('Y-m-d').'.csv';

        if($filePath = self::createLogFile($file_name)){
            if($log_list){
                foreach($log_list as $list){

                    if( !is_string($list) ){// 不是字符串 则转成字符串
                        if(is_array($list)){
                            $list = implode(',',$list);
                        }else{
                            $list = json_encode($list);
                        }
                    }
                    file_put_contents($filePath,$list.PHP_EOL,FILE_APPEND);
                    $success ++;
                }
            }
        }

        return $success;
    }

    /**
     * 展示 日志文件
     *      path:/web/files/correction-data/logs/
     */
    public static function showLogFile(){
        $fileList   = self::readAllFile(self::getDataFilePath().'logs/');
        $hots       = Yii::$app->request->getHostInfo();

        foreach($fileList as $list){
            $url        = $hots.'/files/correction-data/logs'.$list;
            $show_name  = substr($url,strrpos($url,'/')+1);

            echo "<a href='$url'>$show_name</a><br/>";
        }

        return true;
    }

    /**
     * 获取文件夹下所有文件
     * @param string            $fileDir        目标文件夹路径
     * @param bool              $isRealPath     是否返回真实路径
     * @return array|bool
     */
    public static function readAllFile($fileDir,$isRealPath = true)
    {
        $fileList   = [];
        if (!is_dir($fileDir)) return $fileList;

        $handle     = opendir($fileDir);
        if ($handle) {
            while (($nowFile = readdir($handle)) !== false) {
                $temp = DIRECTORY_SEPARATOR . $nowFile;// 文件或文件夹路径
                if ($nowFile != '.' AND $nowFile != '..') {
                    if($isRealPath){
                        $fileList[] = $temp;// 返回的是绝对路径
                    }else{
                        $fileList[] = $nowFile;// 返回的是文件名
                    }
                }
            }
        }
        return $fileList;
    }

    /**
     * 验证文件是否存在
     * @param $file_name
     * @return bool
     */
    public static function checkDataFileExists($file_name){
        if(file_exists(self::getDataFilePath().$file_name)){
            return true;
        }else{
            echo '文件不存在【'.(self::getDataFilePath().$file_name).'】';
            exit;
        }
    }


    public function actionIndex(){
        header('Content-type:text/html;charset=utf-8');
        set_time_limit(0);
        ini_set('memory_limit','512M');
        $start_time = time();

        $params = Yii::$app->request->queryParams;
        if(!isset($params['type'])){
            echo '未设置请求类型';
            exit;
        }

        $type = trim($params['type']);// 获取请求类型

        self::$_type = $type;// 保存请求类型

        $file_list = [
            'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_1' => "1025/on_net_purchase_order(no_tongtu_order)_001.csv",
            'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_2' => "1025/on_net_purchase_order(no_tongtu_order)_002.csv",
            'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_3' => "1025/on_net_purchase_order(no_tongtu_order)_003.csv",
            'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_4' => "1025/on_net_purchase_order(no_tongtu_order)_004.csv",
            'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_5' => "1025/on_net_purchase_order(no_tongtu_order)_005.csv",
            'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_6' => "1025/on_net_purchase_order(no_tongtu_order)_006.csv",
            'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_7' => "1025/on_net_purchase_order(no_tongtu_order)_007.csv",
            'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_8' => "1025/on_net_purchase_order(no_tongtu_order)_008.csv",
            'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_9' => "1025/on_net_purchase_order(no_tongtu_order)_009.csv",
        ];


        switch ($type){
            case 'PUR_ORDER_FREIGHT_DISCOUNT_FOR_PRIVATE':// 对私合同单
                $result = self::repairPurOrderFreightAndDiscount("1025/to_private_compact_order_freight_discount.xlsx");
                break;
            case 'PUR_ORDER_FREIGHT_DISCOUNT_FOR_PUBLIC':// 对公合同单
                $result = self::repairPurOrderFreightAndDiscount("1025/to_public_compact_order_freight_discount.xlsx");
                break;
            case 'PUR_ORDER_FREIGHT_DISCOUNT_FOR_PUBLIC_FOR_FUYOU':// 富有合同单
                $result = self::repairPurOrderFreightAndDiscount("1025/fuyou_to_public_compact_order_freight_discount.xlsx");
                break;
            case 'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_1';// 网菜单
            case 'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_2';
            case 'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_3';
            case 'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_4';
            case 'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_5';
            case 'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_6';
            case 'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_7';
            case 'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_8';
            case 'NET_PURCHASE_ORDER_FREIGHT_DISCOUNT_9';
                $file_name  = $file_list[$type];
                $result     = self::repairNetPurchaseOrderFreightAndDiscount($file_name);
                break;


            case 'SHOW_LOG_FILE':
                $result = self::showLogFile();
                break;
            default :
                $result = false;
                echo '没设置请求类型,我什么也不会做的<br/>';
                break;
        }

        echo '<br/><br/>执行结果：'.var_dump($result);
        echo '<br/><br/>执行耗时：【'.(time()-$start_time).' 】秒';
        echo '<br/><br/>执行结束';
        exit;
    }

    /**
     * 验证 采购单编号是否正确
     * @param $pur_number
     * @return bool
     */
    public static function checkPurNumber($pur_number){

        if(empty($pur_number)) return false;

        if(strpos($pur_number,'PO') === false AND strrpos($pur_number,'FBA') === false AND strpos($pur_number,'ABD') === false){
            return false;
        }

        return true;
    }


    /**
     * 对公合同订单运费、实际付款时间和金额
     * 对私合同订单运费、实际付款时间和金额
     * 富有合同订单运费、实际付款时间和金额
     *
     * @param $file_name
     * @return bool
     */
    public static function repairPurOrderFreightAndDiscount($file_name){
        if(self::checkDataFileExists($file_name)){
            $filePath = self::getDataFilePath().$file_name;

            $PHPReader      = new \PHPExcel_Reader_Excel2007();
            $PHPReader      = $PHPReader->load($filePath);
            $currentSheet   = $PHPReader->getSheet(0);
            $totalRows      = $currentSheet->getHighestRow();

            $success_list   = [];

            $sheetData      = $currentSheet->toArray(null,true,true,true);

            if($sheetData){
                echo '数据总行数[合并前]：'.(count($sheetData)).'<br/>';
                $success_list[] = '数据总行数[合并前]：'.(count($sheetData));


                // 数组转换[过滤采购单号、合并采购单运费]
                $sheetDataTmp = array();
                foreach($sheetData as $data){
                    $pur_number     = trim($data['A']);// 采购单号
                    $freight        = trim($data['C']);// 运费
                    $supplier_name  = trim($data['J']);// 供应商名称

                    if(empty($pur_number) OR $pur_number == '订单号' OR empty($supplier_name)) continue;

                    $new_data                   = [];
                    $new_data['pur_number']     = $pur_number;
                    $new_data['freight']        = floatval($freight);
                    $new_data['supplier_name']  = $supplier_name;

                    if(isset($sheetDataTmp[$pur_number])){
                        $sheetDataTmp[$pur_number]['freight'] += $new_data['freight'];// 运费求和
                    }else{
                        $sheetDataTmp[$pur_number] = $new_data;
                    }
                }
                unset($sheetData);

                echo '订单总行数[合并后]：'.(count($sheetDataTmp)).'<br/>';
                $success_list[] = '订单总行数[合并后]：'.(count($sheetDataTmp));

//                print_r($sheetDataTmp);exit;

                foreach($sheetDataTmp as $value){
                    $pur_number         = trim($value['pur_number']);// 采购单号
                    $freight            = trim($value['freight']);// 运费
                    $supplier_name      = trim($value['supplier_name']);// 供应商

                    $pur_number_list = [];
                    if(strpos($pur_number,' ') !== false){
                        $pur_number_list = explode(' ',$pur_number);
                    }else{
                        $pur_number_list[] = $pur_number;
                    }

                    // 国内的要验证供应商(必须  采购单编号、供应商名称和采购系统一致)
                    foreach($pur_number_list as $key => $number_value){
                        if(strpos($number_value,'PO') === false){
                            continue;// 非国内采购单 无需验证
                        }else{
                            // 没有找到 满足采购单编号、供应商的采购单【此为通途的订单，排除其与采购系统单号相同】
                            $have = PurchaseOrder::findOne(['pur_number' => $number_value,'supplier_name' => $supplier_name,'purchase_type' => 1 ]);
                            if(empty($have)){
                                unset($pur_number_list[$key]);
                            }
                        }
                    }

                    if(empty($pur_number_list)) continue;
                    $pur_number_list_str    = implode(" ",$pur_number_list);

                    // 1、账单中 运费为0
                    // 1.1 采购系统运费不为0   ---》 更新采购系统 采购单运费、合同单运费为0
                    // 1.2 采购系统运费为0     ---》 更新运费到该合同号中  任一订单的运费、更新合同运费

                    // 2、账单中 运费不为0    采购系统中运费不为0 且两者不等
                    // 2.1 账单运费 大于 系统运费 ---》 将运费 差值 增加到PO1的运费上，更新合同运费为账单运费
                    // 2.2 账单运费 小于 系统运费 ---》 将运费 差值 从有运费的PO单上减去，更新合同运费为账单运费

                    // 优惠修复：运费进行修改后，采购订单实际优惠需要重新计算，计算公式：订单金额-运费-付款金额=优惠【暂不处理】

                    // 获取采购单合同总运费、采购单运费
                    $result_freight = self::getCompactNumberFreight($pur_number_list);
                    if($result_freight['code'] == 'success'){
                        $compact_number = isset($result_freight['compact_number'])?$result_freight['compact_number']:'';

                        if($freight == 0){// 【1】
                            // 存在合同运费记录（非虚拟合同单）
                            if(isset($result_freight['compactFreight'])){
                                $compactFreight = $result_freight['compactFreight'];
                                if($compactFreight != 0){// 【1.1】
                                    $model_compact = PurchaseCompact::findOne(['compact_number' => $compact_number]);
                                    if($model_compact){
                                        $text = "COMPACT,SUCCESS,$pur_number_list_str,$compact_number,,修改合同单[$compact_number]总运费由[$model_compact->freight]改为0";

                                        $model_compact->freight = 0;
                                        $res = $model_compact->save(false);
                                        if($res){
                                            $success_list[] = $text;
                                        }
                                    }
                                }
                            }
                            // 修改采购单运费[账单合同运费为0，则合同旗下所有采购单运费设为0]
                            if(isset($result_freight['orderTotalFreight'])){
                                $orderTotalFreight = $result_freight['orderTotalFreight'];
                                foreach($orderTotalFreight as $pur_number => $pur_freight){
                                    if($pur_freight != 0){
                                        $model_pay_type = PurchaseOrderPayType::findOne(['pur_number' => $pur_number]);
                                        if($model_pay_type){// 【1.1】
                                            $text = "ORDER,SUCCESS,$pur_number_list_str,$compact_number,$pur_number,修改采购单[$pur_number]运费由[$model_pay_type->freight]改为0";

                                            $model_pay_type->freight = 0;
                                            $res = $model_pay_type->save(false);
                                            if($res){
                                                $success_list[] = $text;
                                            }
                                        }
                                    }
                                }
                            }

                        }else{// 【2】
                            // 存在合同运费记录（非虚拟合同单）
                            if(isset($result_freight['compactFreight'])){
                                $compactFreight     = $result_freight['compactFreight'];
                                $freight_diff       = $freight - $compactFreight;// 账单运费 - 系统运费 = 运费差值
                                if($freight_diff == 0) continue;

                                // 修改合同的总运费
                                $model_compact = PurchaseCompact::findOne(['compact_number' => $compact_number]);
                                if($model_compact){// 【2.1】
                                    $text = "COMPACT,SUCCESS,$pur_number_list_str,$compact_number,,修改合同单[$compact_number]总运费由[$model_compact->freight]改为[$freight],运费增量[$freight_diff]";

                                    $model_compact->freight = $freight;
                                    $res = $model_compact->save(false);
                                    if($res){
                                        $success_list[] = $text;
                                    }
                                }
                            }

                            // 修改采购单金额
                            if(isset($result_freight['orderTotalFreight'])){
                                $orderTotalFreight  = $result_freight['orderTotalFreight'];

                                // 获取 运费
                                $compactFreight     = isset($result_freight['compactFreight'])?$result_freight['compactFreight']:array_sum($orderTotalFreight);
                                $freight_diff       = $freight - $compactFreight;// 账单运费 - 系统运费 = 运费差值

                                if($freight_diff > 0){// 【2.1】
                                    if($orderTotalFreight){// 【2.1】
                                        foreach($orderTotalFreight as $pur_number => $pur_freight){
                                            $model_pay_type = PurchaseOrderPayType::findOne(['pur_number' => $pur_number]);
                                            if($model_pay_type){// 【1.1】
                                                $new_freight = $model_pay_type->freight + $freight_diff;
                                                $text = "ORDER,SUCCESS,$pur_number_list_str,$compact_number,$pur_number,修改采购单[$pur_number]运费由[$model_pay_type->freight]改为[$new_freight],运费增量[$freight_diff]";

                                                $model_pay_type->freight = $new_freight;
                                                $res = $model_pay_type->save(false);
                                                if($res){
                                                    $success_list[] = $text;
                                                }

                                                break;// 只需要把 增加增加到第一个采购单的运费上
                                            }
                                        }
                                    }
                                }
                                elseif($freight_diff < 0){
                                    if($orderTotalFreight){// 【2.2】
                                        foreach($orderTotalFreight as $pur_number => $pur_freight){
                                            $model_pay_type = PurchaseOrderPayType::findOne(['pur_number' => $pur_number]);
                                            if($model_pay_type AND $model_pay_type->freight > 0){// 【1.1】

                                                if($freight_diff < 0){// 依次从采购单中 减去运费，直到减为0
                                                    if($model_pay_type->freight >= abs($freight_diff)){
                                                        $new_freight = $model_pay_type->freight - abs($freight_diff);
                                                        ($new_freight < 0.00001) AND ($new_freight = 0);// 浮点数相减 是非常小的小数，转成0
                                                        $freight_diff = 0;
                                                    }else{
                                                        $new_freight = 0;
                                                        $freight_diff = - ( abs($freight_diff) - abs($model_pay_type->freight));
                                                    }
                                                    $now_diff =  $new_freight - $model_pay_type->freight;

                                                    $text = "ORDER,SUCCESS,$pur_number_list_str,$compact_number,$pur_number,修改采购单[$pur_number]运费由[$model_pay_type->freight]改为[$new_freight],运费增量[$now_diff]";

                                                    $model_pay_type->freight = $new_freight;
                                                    $res = $model_pay_type->save(false);
                                                    if($res){
                                                        $success_list[] = $text;
                                                    }

                                                }else{
                                                    break;// 只需要把 增加增加到第一个采购单的运费上
                                                }
                                            }
                                        }
                                        // 扣减运费结束后，运费未扣减完报异常
                                        if($freight_diff < 0){
                                            $text               = "ORDER,WARNING,$pur_number_list_str,,$pur_number_list,修改采购单[$pur_number]运费异常,原因[合同下采购单运费不满足账单合同运费扣减]";
                                            $success_list[]     = $text;
                                        }
                                    }

                                }
                            }
                        }

                    }else{
                        $message            = $result_freight['message'];
                        $text               = "ORDER,ERROR,$pur_number_list_str,,$pur_number_list_str,修改采购单[$pur_number_list_str]失败,原因[$message]";

                        $success_list[]     = $text;
                    }
                }
            }else{
                $success_list[] = '数据读取失败';
            }

            $res = self::saveLogs(self::$_type,$success_list);

        }

        return true;
    }

    /**
     * 网采运费汇总1-8月(去除通途订单)
     *  采购单号列 为空的单元格必须先用上一单元格填充
     * @param $file_name
     * @return bool
     */
    public static function repairNetPurchaseOrderFreightAndDiscount($file_name){
        if(self::checkDataFileExists($file_name)){
            $filePath = self::getDataFilePath().$file_name;

            $success_list   = [];

            $flag       = false;

            do{
                $sheetData      = self::readCsvLines($filePath);

                if(empty($sheetData)) break;
                echo '数据总行数[合并前]：'.(count($sheetData)).'<br/>';
                $success_list[] = '数据总行数[合并前]：'.(count($sheetData));

                // 对所有记录 根据采购单号进行合并，运费求和
                $sheetDataTmp = [];
                foreach($sheetData as $value){
                    $pur_number = trim($value[0]);
                    $freight    = trim($value[3]);

                    if(self::checkPurNumber($pur_number)){
                        if(isset($sheetDataTmp[$pur_number])){
                            $sheetDataTmp[$pur_number] += $freight;
                        }else{
                            $sheetDataTmp[$pur_number] = $freight;
                        }
                    }
                }
                unset($sheetData);

                echo '订单总行数[合并后]：'.(count($sheetDataTmp)).'<br/>';
                $success_list[] = '订单总行数[合并后]：'.(count($sheetDataTmp));

                $model_purchase_order_pay_type = new PurchaseOrderPayType();
                // 保存数据
                foreach($sheetDataTmp as $pur_number => $freight){
                    // 查询已经存在的判断运费记录
                    $model_pay_type = PurchaseOrderPayType::findOne(['pur_number' => $pur_number]);
                    if($model_pay_type){// 运费记录存在 则更新
                        $now_diff =  $freight - $model_pay_type->freight;
                        if($now_diff == 0) continue;

                        $text = "ORDER,SUCCESS,$pur_number,修改采购单[$pur_number]运费由[$model_pay_type->freight]改为[$freight],运费增量[$now_diff]";

                        $model_pay_type->freight = $freight;
                        $res = $model_pay_type->save(false);
                        if($res){
                            $success_list[] = $text;
                        }
                    }
                    else{// 插入运费
                        $PurchaseOrder = PurchaseOrder::find()->where(['pur_number' => $pur_number])->asArray()->all();
                        if($PurchaseOrder){
                            $skuTotalPrice = PurchaseOrderItems::find()->select("SUM(qty*price) as total_price")->where(['pur_number' => $pur_number])->asArray()->scalar();

                            // 获取订单实际金额：商品总额 + 运费 - 优惠
                            foreach($PurchaseOrder as $k => &$v) {
                                // 设置空值
                                $v['freight_formula_mode']      = '';
                                $v['settlement_ratio']          = '';
                                $v['purchase_source']           = 2;// 采购来源  2.网采单
                                $v['purchase_acccount']         = '';
                                $v['platform_order_number']     = '';

                                $v['freight']                   = $freight;
                                $v['discount']                  = 0;

                                $v['real_price'] = $skuTotalPrice + $v['freight'] - $v['discount'];
                            }

                            $res = $model_purchase_order_pay_type->saveOrderPayType($PurchaseOrder);  // 订单子表
                            if($res){
                                $insert_id      = PurchaseOrderPayType::find()->select('id')->where(['pur_number' => $pur_number])->orderBy('id desc')->scalar();
                                $text           = "ORDER,SUCCESS,$pur_number,增加采购单[$pur_number]运费,新增记录成功,ID[$insert_id]";
                                $success_list[] = $text;
                            }else{
                                $success_list[] = "ORDER,ERROR,$pur_number,增加采购单[$pur_number]运费,新增记录失败";
                            }
                        }else{
                            $success_list[] = "ORDER,ERROR,$pur_number,修改采购单[$pur_number]运费,失败[未找到采购单]";
                            continue;
                        }
                    }

                    if(count($success_list) > 1000){
                        self::saveLogs(self::$_type,$success_list);
                        $success_list = [];
                    }
                }
                unset($sheetDataTmp);

                $res = self::saveLogs(self::$_type,$success_list);

            }while($flag);
        }

        return true;

    }

    /**
     * 读取CSV文件中指定的行数
     * @param string $csv_file csv文件路径
     * @param int $lines 读取的行数(0为返回所有行)
     * @param int $offset 跳过的行数
     * @return array|bool
     */
    public static function readCsvLines($csv_file = '', $lines = 0, $offset = 0)
    {
        // 打开并读取文件
        if (!$fp = fopen($csv_file, 'r')) {
            return false;
        }
        $i = $j = 0;
        // 获取指向文件的行数，计算偏移量
        if ($offset > 0) {
            while (++$i <= $offset) {
                if (false !== ($line = fgets($fp))) {
                    continue;
                }
                break;
            }
        }
        $data = array();
        if ($lines > 0) {// 大于0则读取 $lines 的行数
            while ((($j++ < $lines) && !feof($fp))) {
                $data[] = fgetcsv($fp);
            }
        } else {// 读取所用行数据
            while (!feof($fp)) {
                $data[] = fgetcsv($fp);
            }
        }
        fclose($fp);
        return $data;
    }


    /**
     * 根据采购单号获取 合同号
     * @param $pur_numbers
     * @return array|yii\db\ActiveRecord[]
     */
    public static function getCompactNumberByPurNumber($pur_numbers){
        $compactNumber = PurchaseCompactItems::find()
            ->select('compact_number')
            ->where(['in','pur_number',$pur_numbers])
            ->groupBy('compact_number')
            ->asArray()
            ->all();
        
        return $compactNumber;
    }

    /**
     * 获取合同单的运费
     * @param $pur_numbers
     * @return array|yii\db\ActiveRecord[]
     */
    public static function getCompactNumberFreight($pur_numbers){
        $compactNumber = self::getCompactNumberByPurNumber($pur_numbers);

        $result = array('code' => 'success','data' => '','message' => '');
        if(count($compactNumber) > 1){
            $compactNumber = implode(" ",array_column($compactNumber,'compact_number'));
            $result['code']     = 'error';
            $result['message']  = "合同单号个数大于[1]个[ $compactNumber ]";
        }elseif($compactNumber AND  isset($compactNumber[0]['compact_number'])){// 获取合同单的运费
            $compactNumber  = $compactNumber[0]['compact_number'];

            $result['compact_number'] = $compactNumber;
            // 根据合同单号找到 合同总运费
            $compactFreight = PurchaseCompact::findOne(['compact_number' => $compactNumber]);
            if($compactFreight){
                $result['compactFreight'] = $compactFreight->freight;// 合同总运费
                $result['compactSource'] = $compactFreight->source;// // 合同来源 1（国内） 2（海外） 3（FBA）
            }
            // 获取采购单 总运费
            $subQuery = (new Query())->select('pur_number')
                ->from(PurchaseCompactItems::tableName())
                ->where(['compact_number' => $compactNumber]);

            $orderTotalFreight = PurchaseOrderPayType::find()
                ->select('freight,pur_number')
                ->where(['in','pur_number',$subQuery])
                ->indexBy('pur_number')
                ->asArray()
                ->column();
            if($orderTotalFreight){
                $result['orderTotalFreight'] = $orderTotalFreight;
            }

            return $result;

        }else{
            $result['code']     = 'error';
            $result['message']  = '未找到合同单号';
        }

        return $result;
    }

}