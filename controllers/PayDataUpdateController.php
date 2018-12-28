<?php
namespace app\controllers;
use app\config\Vhelper;
use app\models\BankCardManagement;
use app\models\ProductDescription;
use app\models\PurchaseCompactItems;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPayBak;
use app\services\BaseServices;
use app\services\CommonServices;
use m35\thecsv\theCsv;
use yii\db\Exception;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/26
 * Time: 12:31
 */

class PayDataUpdateController extends BaseController{
    public function actionImport(){
        set_time_limit(0);
        $model = new PurchaseOrderPayBak();
        if (\Yii::$app->request->isPost && $_FILES)
        {
            $name= 'PurchaseOrderPayBak[file_execl]';
            $buyer_id = \Yii::$app->request->getBodyParam('PurchaseOrderPayBak')['buyer_id'];
            $data = Vhelper::upload($name);
            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['import']);
            }
            $this->update($data,$buyer_id);
            return $this->redirect('import');
        } else {
            return $this->render('import', ['model' => $model]);
        }
    }
    


    protected function getFileData($filePath,$dateColum=[]){
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($filePath,$encode='utf-8');
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();//取得总行数
        $highestColumn = $sheet->getHighestColumn();//取得总列数
        $data = array();
        for($i=2;$i<=$highestRow;$i++){
            for($j='A';$j<=$highestColumn;$j++){
                if(in_array($j,$dateColum)){
                    $data[$i][]= date('Y-m-d',\PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$j$i")->getValue()));
                }else{
                    $data[$i][] = $objPHPExcel->getActiveSheet()->getCell("$j$i")->getValue();
                }
            }
        }
        return $data;
    }

    /**
     * @param $filename 富友ufxpay.xlsx  对公public.xlsx  对私 self.xlsx
     * @param $payer_id 富友142  对公143 对私77
     * @throws Exception
     */
    protected function update($filename,$payer_id){
        set_time_limit(0);
//        $file_name = 'ufxpay.xlsx';
        $data =$this->getFileData($filename,['G']);
        /*
         * 0采购单号
         * 1合同单号
         * 2金额
         * 3运费
         * 4合计
         * 5手续费
         * 6付款时间
         * 7实付
         * 8收款人
         * 9收款账号
         * 10收款银行
         * 11供应商
         * 12付款银行
         * 13付款账号
         * 14备注
         */
        if(empty($data)){
            exit('无数据需要处理');
        }
        $errorLog=[];
        foreach ($data as $key=>$value){
            $value = array_slice($value,0,15);
            $pur_number = explode(' ',$value[0]);
            if(empty($pur_number)){
                $errorLog[] = $value+[15=>'请款单更新失败，数据表中的采购单数据为空',16=>$filename.'第'.$key.'行'];
                $this->saveLog('update_log.txt','error0:请款单更新失败，数据表中的采购单数据为空',$key,$value);
                continue;
            }
            $orderCount = PurchaseOrder::find()
                ->where(['supplier_name'=>$value[11]])
                ->andWhere(['in','pur_number',$pur_number])
                ->count('id');
            if($orderCount==0){
                $errorLog[] = $value+[15=>'请款单更新失败，数据表中的采购单和供应商在数据库找不到对应信息',16=>$filename.'第'.$key.'行'];
                $this->saveLog('update_log.txt','error1:请款单更新失败，数据表中的采购单和供应商在数据库找不到对应信息',$key,$value);
                continue;
            }
            if($orderCount!=count($pur_number)){
                $errorLog[] = $value+[15=>'请款单更新失败，数据表中的采购单和供应商在数据库找到的数据记录不一致',16=>$filename.'第'.$key.'行'];
                $this->saveLog('update_log.txt','error2:请款单更新失败，数据表中的采购单和供应商在数据库找到的数据记录不一致',$key,$value);
                continue;
            }
            if($value[12]=='花旗银行'){
                $errorLog[] = $value+[15=>'请款单更新失败，花旗银行不做处理',16=>$filename.'第'.$key.'行'];
                $this->saveLog('update_log.txt','error4:请款单更新失败，花旗银行不做处理',$key,$value);
                continue;
            }
            $compact_number=null;
            $compact_numbers = PurchaseCompactItems::find()->select('compact_number')->where(['in','pur_number',$pur_number])->andWhere(['bind'=>1])->column();
            if(count(array_unique($compact_numbers))>1){
                $errorLog[] = $value+[15=>'请款单更新失败，数据表的采购单号在数据库查到多个合同单号',16=>$filename.'第'.$key.'行'];
                $this->saveLog('update_log.txt','error5:请款单更新失败，数据表的采购单号在数据库查到多个合同单号',$key,$value);
                continue;
            }
            $saveCompact = $this->saveCompactNumber($pur_number);
            if($saveCompact['status']==false){
                $errorLog[] = $value+[15=>$saveCompact['message'],16=>$filename.'第'.$key.'行'];
                $this->saveLog('update_log.txt','error5:'.$saveCompact['message'],$key,$value);
                continue;
            }else{
                $compact_number = $saveCompact['compact_number'];
            }
            if($orderCount==count($pur_number)){
                $purchaseOrderPayData = PurchaseOrderPay::find()->where(['in','pur_number',$pur_number])->all();
                if(preg_match('/^合同号/',$value[1])){
                    $compact = mb_substr($value[1],4,12);
                }else{
                    $compact = $value[1];
                }
                $purchaseCompactPayData = PurchaseOrderPay::find()->where(['pur_number'=>$compact])->all();
                $tran =\Yii::$app->db->beginTransaction();
                try{
                    if(!empty($purchaseCompactPayData)){
                        $this->deletePayData($purchaseCompactPayData,$compact);
                    }
                    if(!empty($purchaseOrderPayData)){
                        $this->deletePayData($purchaseOrderPayData,$pur_number);
                    }
                    $saveNew = $this->saveNewPayData($value,$pur_number,$compact_number,$payer_id);
                    if(!$saveNew['status']){
                        throw new Exception($saveNew['message']);
                    }
                    $this->saveLog('success_log.txt','success:请款单更新成功',$key,$value);
                    $tran->commit();
                }catch (Exception $e){
                    $tran->rollBack();
                    $errorLog[] = $value+[15=>'请款单更新失败'.$e->getMessage(),16=>$filename.'第'.$key.'行'];
                    $this->saveLog('update_log.txt','error3:请款单更新失败'.$e->getMessage(),$key,$value);
                }

            }
        }
        if(!empty($errorLog)){
            $this->exportErrorLog($errorLog);
        }
    }

    protected function saveCompactNumber($pur_number){
        $existCompactNumbers = PurchaseCompactItems::find()->select('compact_number,pur_number')->where(['in','pur_number',$pur_number])->andWhere(['bind'=>1])->asArray()->all();

        if($existCompactNumbers){
            if(count(array_unique(array_column($existCompactNumbers,'compact_number')))>1){
                return ['status'=>false,'message'=>'表格内的采购单号有多个合同存在无法生成新的虚拟合同'];
            }
            $compact_number = array_unique(array_column($existCompactNumbers,'compact_number'))[0];
            //获取合同单号绑定的所有采购单号
            $compact_number_pur_number = PurchaseCompactItems::find()->select('pur_number')->where(['compact_number'=>$compact_number])->andWhere(['bind'=>1])->column();
            //获取已存在合同号的采购单号
            $existPurnumber = array_column($existCompactNumbers,'pur_number');
            if(!array_diff($compact_number_pur_number,$existPurnumber)&&!array_diff($existPurnumber,$compact_number_pur_number)&&!array_diff($existPurnumber,$pur_number)&&!array_diff($pur_number,$existPurnumber)){
                return ['status'=>true,'message'=>'虚拟合同生成成功','compact_number'=>$compact_number];
            }else{
                return ['status'=>false,'message'=>'数据存在异常无法生成新的虚拟合同'];
            }
        }
        $compact_number = CommonServices::getNumber('XN-HT');
        $insertData =[];
        foreach ($pur_number as $key=>$value){
            $insertData[$key][] = $compact_number;
            $insertData[$key][] = $value;
            $insertData[$key][] = 1;
        }
        $insert = \Yii::$app->db->createCommand()->batchInsert(PurchaseCompactItems::tableName(),['compact_number','pur_number','bind'],$insertData)->execute();
        if($insert){
            return ['status'=>true,'message'=>'虚拟合同生成成功','compact_number'=>$compact_number];
        }else{
            return ['status'=>false,'message'=>'虚拟合同生成失败'];
        }
    }

    protected  function exportErrorLog($errorLog){
        $table = [
            '采购单号',
            '合同单号',
            '金额',
            '运费',
            '合计',
            '手续费',
            '付款时间',
            '实付',
            '收款人',
            '收款账号',
            '收款银行',
            '供应商',
            '付款银行',
            '付款账号',
            '备注',
            '错误原因',
            '文件位置'
        ];
        theCsv::export([
            'header' =>$table,
            'data' => $errorLog,
        ]);
    }

    protected function saveNewPayData($value,$pur_number,$compact_number,$payer){
        //合同单号为空
        $source=2;
        if(empty($compact_number)){
            if(count($pur_number)==1){
                $compact_number = PurchaseCompactItems::find()->select('compact_number')->where(['pur_number'=>$pur_number])->andWhere(['bind'=>1])->column();
                if(count(array_unique($compact_number))>1){
                    return ['status'=>false,'message'=>'数据表的采购单号在数据库查到多个合同单号'];
                }
                if($compact_number){
                    $order_no = $compact_number[0];
                    $source=1;
                }else{
                    $source=2;
                    $order_no = $pur_number[0];
                }
            }
            if(count($pur_number)>1){
                $compact_numbers = PurchaseCompactItems::find()->select('compact_number')->where(['in','pur_number',$pur_number])->andWhere(['bind'=>1])->column();
                if(count(array_unique($compact_numbers))>1){
                    return ['status'=>false,'message'=>'数据表的采购单号在数据库查到多个合同单号'];
                }
                $order_no = $compact_numbers[0];
                $source=1;
            }
        }else{
            $order_no = $compact_number;
        }
        $orderSupplierInfo = PurchaseOrder::find()
            ->select('supplier_code,supplier_name,account_type,pay_type')
            ->where(['pur_number'=>$pur_number[0]])->asArray()->one();
        $model = new PurchaseOrderPay();
        $model->pur_number  = $order_no;
        $model->pay_status  = 5;
        $model->requisition_number = CommonServices::getNumber('PP');
        $model->supplier_code = $orderSupplierInfo['supplier_code'];
        $model->settlement_method = $orderSupplierInfo['account_type'];
        $model->pay_name = '采购费用';
        $model->pay_price = $value[7];
        $model->create_notice = '财务修复';
        $model->applicant     = 1;
        $model->auditor       = 1;
        $model->approver      =  1;
        $model->application_time      =  $value[6];
        $model->review_time      =  $value[6];
        $model->processing_time      = $value[6];
        $model->pay_type      = 3;
        $model->currency      = 'RMB';
        $model->payer      = $payer;
        $model->payer_time      = $value[6];
        $model->source      = $source;
        $model->real_pay_price      = $value[7];
        $payInfo = $this->getPayInfo($value[12]);
        if(empty($payInfo)){
            return ['status'=>false,'message'=>'付款银行信息异常'];
        }
        $is_kevin = $this->getOrderIsKevin($pur_number);
        if(!$is_kevin['status']){
            return ['status'=>false,'message'=>'kevin判断异常'];
        }
        $model->pay_account      = $payInfo['id'];
        $model->pay_number      = $payInfo['account_number'];
        $model->k3_account      = $payInfo['k3_bank_account'];
        $model->pay_branch_bank      = $payInfo['branch'];
        $model->is_kevin   = $is_kevin['is_kevin'];
        if($model->save()==false){
            return ['status'=>false,'message'=>implode(',',$model->getFirstErrors())];
        }
        $insertData = $this->arrayKeyValue($model->attributes);
        $this->saveLog('success_log.txt','新增付款数据','',$insertData,'新增数据为：');
        return ['status'=>true,'message'=>'新增数据成功'];
    }

    protected function getOrderIsKevin($pur_number){
        $productSku = PurchaseOrderItems::find()->select('sku')->where(['in','pur_number',$pur_number])->column();
        $productSkuName = ProductDescription::find()
            ->select('title')
            ->where(['in','sku',$productSku])
            ->andWhere(['language_code'=>'Chinese'])->column();
        if(empty($productSkuName)){
            return ['status'=>true,'is_kevin'=>0];
        }
        $is_kevin=[];
        foreach ($productSkuName as $name){
            if(strtolower(substr($name,-2))=='-k'){
                $is_kevin[] = 1;
            }else{
                $is_kevin[] = 0;
            }
        }
        if(empty($is_kevin)){
            return ['status'=>true,'is_kevin'=>0];
        }
        if(count(array_unique($is_kevin))>1){
            return ['status'=>false,'is_kevin'=>0];
        }
        if(count(array_unique($is_kevin))==1){
            return ['status'=>true,'is_kevin'=>$is_kevin[0]];
        }
    }

    protected function getPayInfo($payBank){
        $payInfo=[];
        if($payBank=='上海富友'){
            $payInfo = BankCardManagement::find()->where(['id'=>138])->asArray()->one();
        }elseif($payBank=='花旗银行'){
            $payInfo = BankCardManagement::find()->where(['id'=>69])->asArray()->one();
        }elseif ($payBank=='招商银行东莞分行塘厦支行(范礼林账号）'){
            $payInfo = BankCardManagement::find()->where(['id'=>84])->asArray()->one();
        }elseif ($payBank=='招商银行深圳梅龙支行（刘楚雯）'){
            $payInfo = BankCardManagement::find()->where(['id'=>109])->asArray()->one();
        }elseif ($payBank=='招商银行深圳西丽支行（雷鸣）'){
            $payInfo = BankCardManagement::find()->where(['id'=>107])->asArray()->one();
        }elseif ($payBank=='从锦绣支行支付'){
            $payInfo = BankCardManagement::find()->where(['id'=>60])->asArray()->one();
        }elseif ($payBank=='从北方大厦支付'){
            $payInfo = BankCardManagement::find()->where(['id'=>55])->asArray()->one();
        }
        return $payInfo;
    }

    protected function saveLog($filename,$message,$key=null,$data,$defaultmessage='表格数据为：'){
        @file_put_contents($filename,'第'.$key.'行'.$message.$defaultmessage.implode(',',$data).PHP_EOL,FILE_APPEND);
    }

    protected function deletePayData($payData,$pur_number){
        if(!is_array($pur_number)){
            $pur_number = [$pur_number];
        }
        $delete=0;
        foreach (array_unique($pur_number) as $value){
            //一个采购单的付款数据只在第一次删除
            if(!\Yii::$app->cache->add($value.'delete',date('Y-m-d H:i:s',time()))){
                $delete++;
                break;
            }
        }
        if($delete>0){
            return false;
        }
        foreach ($payData as $v){
            $tranfer = \Yii::$app->db->createCommand()->insert(PurchaseOrderPayBak::tableName(),$v->attributes)->execute();
            if($tranfer){
                $v->delete();
            }else{
                $deleteData = $this->arrayKeyValue($v->attributes);
                $this->saveLog('fail_log.txt','数据备份失败','',$deleteData,'备份数据为：');
            }
        }
        return true;
    }

    protected function arrayKeyValue($array){
        $stringArray=[];
        foreach ($array as $key=>$value){
            $stringArray[]=$key.'=>'.$value;
        }
        return $stringArray;
    }
}