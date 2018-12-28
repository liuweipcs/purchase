<?php
namespace app\synchcloud\controllers;
use Yii;
use yii\web\Controller;
use app\config\Vhelper;

use app\synchcloud\models\Supplier;
use app\synchcloud\models\SupplierBuyer;
use app\synchcloud\models\PurchaseOrder;
use app\synchcloud\models\PurchaseOrderPayWater;
use app\synchcloud\models\PurchaseOrderReceiptWater;
use app\synchcloud\models\PurchaseOrderPayType;
use app\synchcloud\models\PurchaseOrderRefundQuantity;
use app\synchcloud\models\PurchaseCompactItems;
use app\synchcloud\models\PurchaseOrderItems;
use app\synchcloud\models\PurchaseWarehouseResults;
use app\synchcloud\models\PurchaseOrderPay;
use app\synchcloud\models\PurchaseOrderPayRestore;
use app\synchcloud\models\PurchaseCompactItemsRestore;
use app\synchcloud\models\PurchaseOrderRestore;
use app\synchcloud\models\PurchaseOrderTaxes;
use app\synchcloud\models\PurchaseOrderSourceRestore;
use app\synchcloud\models\PurchasePaymentRefund;

use app\synchcloud\models\MidPurchaseOrder;
use app\synchcloud\models\MidPurchaseOrderDetail;
use app\synchcloud\models\MidPurchasePayment;
use app\synchcloud\models\MidPurchaseRefund;
use app\synchcloud\models\MidPurchaseIn;
use app\synchcloud\models\MidPurchaseDiscount;
use app\synchcloud\models\MidPurchaseFreight;
use app\synchcloud\models\MidPurchaseReturns;
use app\synchcloud\models\MidPurchasePayRelation;
use app\synchcloud\models\MidPurchaseInDetail;
use app\synchcloud\models\ExcPurchaseOrder;
use app\synchcloud\models\ExcPurchaseOrderDetail;

use app\synchcloud\models\MidSupplier;
use app\synchcloud\models\MidSupplierBuyer;
use app\synchcloud\models\SupplierPaymentAccount;
use app\synchcloud\models\ExcSupplierBank;
use app\synchcloud\models\ExcCityCode;
use app\synchcloud\models\ExcBankCode;
use app\synchcloud\models\app\synchcloud\models;
use app\synchcloud\models\ExcSupplier;

/**
 * PMS同步中间表定时脚本（共13个）
 */
class AutosyncController extends Controller
{
    public $accessIpList;
    
    const CLOUD_SYNC_STATUS_INIT = 0;  //待同步k3
    const CLOUD_SYNC_STATUS_SUCCESS = 1; //同步成功
    const CLOUD_SYNC_STATUS_FAIL = 2; //同步失败
    const CLOUD_SYNC_STATUS_EXIST = 3; //检查到重复

    CONST SYNC_SUCCESS = 1;
    const SYNC_FAILURE = 2;

    public $limits = [
        'returns' => 500,
        'supplier' => 200,
        'order' => 300,
        'payment' => 300,
        'receipt' => 300,
        'ruku' => 300,
        'freight' => 300,
        'discount' => 300,
        'freight_diff' => 300,
        'discount_diff' => 300,
        'compact_items' => 300,
    ];

    public function init()
    {
        parent::init();
        /*if(!YII_DEBUG) {
            $this->accessIpList = require Yii::$app->basePath.'/config/cloudAccessIp.php';
            if(!$this->checkAccess() ) {
                exit('Illegal access,Your current IP or host is not allowed to access this site!');
            }
        }*/
    }

    public function checkAccess()
    {
        $userIP = Yii::$app->request->userIP;
        $allow = false;
        foreach($this->accessIpList as $client_ip) {
            if(strpos($userIP, str_replace('*', '', $client_ip)) !== false ) {
                $allow = true;
                break;
            }
        }
        return $allow;
    }

    /**
     * 数据库连接测试
     * http://test.yibainetwork.com/synchcloud/autosync/db-test
     * http://caigou.yibainetwork.com/synchcloud/autosync/db-test
     */
    public function actionDbTest()
    {
        $mid_cloud = Yii::$app->mid_cloud;
        $cloud_basic = Yii::$app->cloud_basic;
        if($mid_cloud) {
            echo $mid_cloud->dsn;
            echo '<br/>';
        }
        if($cloud_basic) {
            echo $cloud_basic->dsn;
            echo '<br/>';
        }
        echo __DIR__;
        exit;
    }

    /**
     * 同步供应商
     * http://test.yibainetwork.com/synchcloud/autosync/sync-supplier
     * http://caigou.yibainetwork.com/synchcloud/autosync/sync-supplier?limit=1
     */
    public function actionSyncSupplier()
    {
    	exit('closed');
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
        $model = new Supplier();
        $midModel = new MidSupplier();
        $limit = Yii::$app->request->get('limit');
        $limit = $limit ? $limit : $this->limits['supplier'];
        $data = $model->getApplicativeSupplier($limit);
        if(empty($data)) {
            exit('没有数据了');
        }
        $success = [];
        $transaction = $model->getDb()->beginTransaction();
        try {
            $success = $midModel->saveRows($data);
            if($success) {
                echo 'SUCCESS:'.count($success);
                $transaction->commit();
            } else {
                echo 'Failure';
                $transaction->rollBack();
            }
        } catch(\Exception $e) {
            $transaction->rollBack();
            Vhelper::dump($e->getMessage());
        }
        if(!empty($success)) {
            $model::updateAll(['is_push_to_k3cloud' => self::SYNC_SUCCESS], ['in', 'id', $success]);
        }
    }
    
    /**
     * 同步供应商信息到cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-supplier-cloud?limit=1&is_debug=1&supplier_code=AB090901
     **/
    public function actionSynchSupplierCloud()
    {
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',100);
    	$supplier_code = Yii::$app->request->get('supplier_code');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new Supplier();
    	$midModel = new MidSupplier();
    	$supplerPayAcctModel = new SupplierPaymentAccount();
    	$excBankCode = new ExcBankCode();
    
    	$conditions = '';
    	if (!empty($supplier_code)){
    		$supplier_code = explode(',',$supplier_code);
    		$supplier_code = implode("','",$supplier_code);
    		$conditions .= " and a.supplier_code in('$supplier_code')";
    	}
    
    	$need_data = $model->getSyncSupplierCloudData('a.*',$limit,$conditions);
    	 
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    	
    	if (!empty($need_data)){
    		try{
    			$params = $successList = $insertList = $failsList = array();
    			$date = date('Y-m-d H:i:s');
    
    			//组装K3所需要的字段信息
    			foreach ($need_data as $value){
    				if ($midModel->dataExist('supplier_code',$value['supplier_code'])){
    					$successList[] = $value['id'];
    					continue;
    				}
    
    				$retPayAcct = $supplerPayAcctModel->getPaymentAccount('account_name,account,payment_platform_bank',$value['supplier_code']);
    
    				if( !isset($value['supplier_name']) || empty($value['supplier_name']) ){
    					$failsList[Supplier::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    
    				$params[] = array(
    						'id' => $value['id'],
    						'supplier_code' => $value['supplier_code'],
    						'supplier_name' => $this->replaceSpecialChar($value['supplier_name']),
    						'short_name' => (mb_strlen($value['supplier_name']) >= 50) ? '' : $this->replaceSpecialChar($value['supplier_name']),
    						'supplier_settlement_type' => !empty($value['supplier_settlement']) ? $value['supplier_settlement'] : 0,
    						'supplier_type' => $value['supplier_type']?$value['supplier_type']:0, //@todo 7贸易商 8工厂 pms维护，待定
    						'credit_code' => $value['credit_code'] ? $value['credit_code'] : '', // 企业社会信用码
    						//'supplier_bankname' => isset($excBankCodeMap[$retPayAcct['payment_platform_bank']]) && $excBankCodeMap[$retPayAcct['payment_platform_bank']] ? $excBankCodeMap[$retPayAcct['payment_platform_bank']] : '',
    						'supplier_bankname' => $retPayAcct['payment_platform_bank'] && is_numeric($retPayAcct['payment_platform_bank']) ? $retPayAcct['payment_platform_bank'] : '',
    						'supplier_holder' => $retPayAcct['account_name']?$this->replaceSpecialChar($retPayAcct['account_name']):'', // 开户名称
    						'supplier_account' => $retPayAcct['account'] ? $retPayAcct['account'] : '', // 开户帐号
    						'add_time' => !empty($value['create_time']) ? date('Y-m-d H:i:s', $value['create_time']) : $time,
    				);
    			}
    			
    			if(!empty($params)){
    				if ($midModel->batchInsertData( array_keys($params[0]), $params)) {
    					foreach ($params as $value) {
    						$successList[] = $value['id'];
    					}
    				} else {
    					foreach ($params as $value) {
    						if (Yii::$app->db->createCommand()->insert(MidSupplier::tableName(), $value)) {
    							$successList[] = $value['id'];
    						}
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				$model->updateSupplierSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateSupplierSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    		}catch (Exception $e){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    /**
     * 更新供应商信息到cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/update-supplier-cloud?limit=1
     */
    public function actionUpdateSupplierCloud(){
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步100条
    	$limit = Yii::$app->request->get('limit',100);
    	$supplier_code = Yii::$app->request->get('supplier_code');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new Supplier();
    	$supplerPayAcctModel = new SupplierPaymentAccount();
    	$midModel = new MidSupplier();
    
    	$conditions = '';
    	if (!empty($supplier_code)){
    		$supplier_code = explode(',',$supplier_code);
    		$supplier_code = implode("','",$supplier_code);
    		$conditions .= " and supplier_code in('$supplier_code')";
    	}
    
    	$need_data = $model->getUpdateSupplierCloud('a.*',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = array();
    		$date = date('Y-m-d H:i:s');
    
    		try{
    			foreach ($need_data as $value){
    				$retPayAcct = $supplerPayAcctModel->getPaymentAccount('account_name,account,payment_platform_bank',$value['supplier_code']);
    				
    				if( !isset($value['supplier_name']) || empty($value['supplier_name']) ){
    					$failsList[Supplier::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    
    				$params = array(
    						'supplier_code' => $value['supplier_code'],
    						'supplier_name' => $this->replaceSpecialChar($value['supplier_name']),
    						'short_name' => (mb_strlen($value['supplier_name']) >= 50) ? '' : $this->replaceSpecialChar($value['supplier_name']),
    						'supplier_settlement_type' => !empty($value['supplier_settlement']) ? $value['supplier_settlement'] : 0,
    						'supplier_type' => $value['supplier_type']?$value['supplier_type']:0, //@todo 7贸易商 8工厂 pms维护，待定
    						'credit_code' => $value['credit_code'] ? $value['credit_code'] : '', // 企业社会信用码
    						//'supplier_bankname' => isset($excBankCodeMap[$retPayAcct['payment_platform_bank']]) && $excBankCodeMap[$retPayAcct['payment_platform_bank']] ? $excBankCodeMap[$retPayAcct['payment_platform_bank']] : '',
    						'supplier_bankname' => $retPayAcct['payment_platform_bank'] && is_numeric($retPayAcct['payment_platform_bank']) ? $retPayAcct['payment_platform_bank'] : '',
    						'supplier_holder' => $retPayAcct['account_name']?$this->replaceSpecialChar($retPayAcct['account_name']):'', // 开户名称
    						'supplier_account' => $retPayAcct['account'] ? $retPayAcct['account'] : '', // 开户帐号
    						'update_time' => !empty($value['modify_time']) ? $value['modify_time'] : $time,
    				);
    				if($midModel->updateSupplierCloud($params)){
    					$successList[] = $value['id'];
    				}
    			}
    
    			if (count($successList) > 0){
    				//更新分类同步成功
    				$model->updateSupplierSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>更新成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    			
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateSupplierSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $e){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }

    /**
     * 采购订单流程（非合同单）
     * @tables MID采购订单主表 MID采购订单明细表
     * @offline http://test.yibainetwork.com/synchcloud/autosync/sync-order
     * @online http://caigou.yibainetwork.com/synchcloud/autosync/sync-order
     */
    public function actionSyncOrder()
    {
    	exit('CLOSED...');
        $orderObj = new PurchaseOrder();
        $midOrderObj = new MidPurchaseOrder();
        $midOrderDetailObj = new MidPurchaseOrderDetail();
        $limit = Yii::$app->request->get('limit');
        $limit = $limit ? $limit : $this->limits['order'];
        $orders = $orderObj->getData($limit);
        if(!$orders) {
            exit('没有数据了');
        }
        if(!empty($orders['valid'])) {
            $orderItems = $orderObj->GetPurcahseOrderItems($orders['valid']);
            $transaction = $midOrderObj->getDb()->beginTransaction();
            try {
                $success1 = $midOrderObj->saveRows($orders['valid']);
                $success2 = $midOrderDetailObj->saveRows($orderItems);
                if($success1 && $success2) {
                    $transaction->commit();
                    echo "Success:".count($success1);
                } else {
                    echo 'Failure';
                    $transaction->rollBack();
                }
            } catch(\Exception $e) {
                $transaction->rollBack();
                Vhelper::dump($e->getMessage());
            }
        }
        if($success1 && $success2) {
            if(!empty($orders['synced'])) {
                $success1 = array_merge($success1, $orders['synced']);
            }
            $orderObj::updateAll(['is_push_to_k3cloud' => self::SYNC_SUCCESS], ['in', 'id', $success1]);
        }
        if(!empty($orders['mistake'])) {
            $orderObj::updateAll(['is_push_to_k3cloud' => self::SYNC_FAILURE], ['in', 'id', $orders['mistake']]);
        }
    }
    
    /**
     * 同步采购单-非合同 cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-po-cloud?limit=1&is_debug=1&po=AB090901
     **/
    public function actionSynchPoCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    	
    	$model = new PurchaseOrder();
    	$modelDetail = new PurchaseOrderItems();
    	$midOrderModel = new MidPurchaseOrder();
    	$midOrderDetailModel = new MidPurchaseOrderDetail();
    	$modelCompactItems = new PurchaseCompactItems();
    	$modelInstockRlt = new PurchaseWarehouseResults();
    	$modelPoRestore = new PurchaseOrderRestore();
    	$modelPoTaxes = new PurchaseOrderTaxes();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $model->getSyncPoCloudData('a.id,a.buyer,a.pur_number,a.warehouse_code,a.supplier_code,a.created_at,a.is_drawback',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $existList = array();
    		$date = date('Y-m-d H:i:s');
    	
    		$tmpBuyers = [];
    		foreach( $need_data as $value ){
    			$tmpBuyers[] = isset($value['buyer']) && $value['buyer'] ? $value['buyer'] : '';
    		}
    		$purchaserArr = $model->getStaffCodeByUsers( array_unique($tmpBuyers) );
    		
    		$staffCodeMap = array_column($purchaserArr, 'username','user_number');
    		
    		//组装K3所需要的字段信息
    		$transaction = $midOrderModel->getDb()->beginTransaction();
    		$flag = true;
    		try{
    			foreach ($need_data as $value){
    				$checkExist = $midOrderModel->dataExist('purchase_no',$value['pur_number']);
    				if ( isset($checkExist['purchase_no']) && $checkExist['purchase_no'] ){
    					$existList[] = $value['id'];
    					continue;
    				}
    				
    				$staffCode = in_array($value['buyer'], $staffCodeMap) ? array_search($value['buyer'], $staffCodeMap) : '';
    				if ( !$staffCode ){
    					$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    				if ( !$value['warehouse_code'] ){
    					$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_2][] = $value['id'];
    					continue;
    				}
    				if ( !$value['supplier_code'] ){
    					$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_3][] = $value['id'];
    					continue;
    				}
    				if ( !$value['created_at'] ){
    					$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_4][] = $value['id'];
    					continue;
    				}
    				
    				$flagInstock = true;
    				$need_data_detail = $modelDetail->getSyncPoDetailCloudData('*',$value['pur_number']);
    				if ( !$need_data_detail ){
    					$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_5][] = $value['id'];
    					continue;
    				}else{
    					$currInstockData = $modelInstockRlt->getInStockByPo('sku,instock_qty_count',$value['pur_number']);
    					$currInstockSkuMap = array_column($currInstockData, 'instock_qty_count','sku');
    					foreach( $need_data_detail as $v ){
    						if( !$currInstockData || !isset($currInstockSkuMap[$v['sku']]) || !$currInstockSkuMap[$v['sku']] ){
    							$flagInstock = false;
    							break;
    						}
    					}
    					if ( !$flagInstock ){
    						$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_6][] = $value['id'];
    						continue;
    					}
    				}
    				if($value['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
    					$orgId = '101';
    				} elseif($value['is_drawback'] == 2) {
    					$orgId = '102';
    				}
    				
    				if( $value->purchaseOrderPayType ) {
    					$freightPrice = !empty($value->purchaseOrderPayType['freight']) ? $value->purchaseOrderPayType['freight'] : 0;
    				} else {
    					$freightPrice = 0;
    				}
    				//检查是否绑定合同
    				$isCompact = 0;
    				$compactInfo = $modelCompactItems->getCompactBindByPo('',$value['pur_number']);
    				if( isset($compactInfo['compact_number']) && $compactInfo['compact_number'] ){
    					$isCompact = 1;
    				}else{
    					//检查付款方式
    					$payOrderInfo = $model->getPayOrderInfo('pay_type',$value['pur_number']);
    					if( in_array($payOrderInfo['pay_type'], [3,5]) ){ //银行转账、富有
    						$isCompact = 1;
    					}
    				}
    				
    				//检查是否有单独开票点
    				$tmpPoints = [];
    				$tmpPoints = $modelPoRestore->getPurchasePurTicketPoint('sku,price,base_price,pur_ticketed_point',$value['pur_number']);
    				if( $tmpPoints ){
    					$tmpBasePriceMap = array_column($tmpPoints, 'base_price','sku'); //原价
    					$tmpPointMap = array_column($tmpPoints, 'pur_ticketed_point','sku'); //开票点
    					$tmpPriceMap = array_column($tmpPoints, 'price','sku'); //采购价
    				}
    				
    				//检查是否有单独的采购含税 -- 针对海外仓采购单
    				$tmpAbPoints = [];
    				$tmpAbPoints = $modelPoTaxes->getAbPurTicketPointList('sku,taxes',[$value['pur_number']]);
    				if( $tmpAbPoints ){
    					$tmpAbPointMap = array_column($tmpAbPoints, 'taxes','sku'); //开票点
    				}
    				
    				$params = array(
    						'org_id' => $orgId,
    						'hrms_code' => $staffCode,
    						'purchase_no' => $value['pur_number'],
    						'warehouse_code' => $value['warehouse_code'],
    						'supplier_code' => $value['supplier_code'],
    						'freight_price' => $freightPrice,
    						'purchase_date' => $value['created_at'],
    						'is_compact' => $isCompact, //是否合同 1是 0不是
    						//'company_org' => '', //所属机构 1：Kevin ，默认0
    				);
    				
    				if(!empty($params)){
    					$rlt1 = $midOrderModel->batchInsertData( array_keys($params), [$params]);
    				}
    				 
    				$paramDetail = [];
    				foreach( $need_data_detail as $v ){
    					$tmpPointMap[$v['sku']] = isset($tmpPointMap[$v['sku']]) ? $tmpPointMap[$v['sku']] : '';
    					$tmpBasePriceMap[$v['sku']] = isset($tmpBasePriceMap[$v['sku']]) ? $tmpBasePriceMap[$v['sku']] : '';
    					$tmpPriceMap[$v['sku']] = isset($tmpPriceMap[$v['sku']]) ? $tmpPriceMap[$v['sku']] : '';
    					$tmpAbPointMap[$v['sku']] = isset($tmpAbPointMap[$v['sku']]) ? $tmpAbPointMap[$v['sku']] : '';
    					
    					$currTicketPoint = isset($v['pur_ticketed_point'])?$v['pur_ticketed_point']:0;
    					$currPrice = isset($v['price'])?$v['price']:0;
    					$currBasePrice = isset($v['base_price'])?$v['base_price']:0;
    					
    					if( $tmpPointMap[$v['sku']] && $tmpBasePriceMap[$v['sku']] && $tmpPriceMap[$v['sku']] ){
    						$currTicketPoint = $tmpPointMap[$v['sku']]*100;
    						$currPrice = $tmpPriceMap[$v['sku']];
    						$currBasePrice = $tmpBasePriceMap[$v['sku']];
    					}elseif( $tmpAbPointMap[$v['sku']] && $value['is_drawback'] == 2 ){ //有税点，并且采购单退税
    						if( $currPrice == $currBasePrice || empty($currBasePrice) ){ //采购单明细 含税价和不含税价相等 或者 不含税价为0
    							$currBasePrice = $currPrice;
    							$currTicketPoint = $tmpAbPointMap[$v['sku']];
    							$currPrice = round($currPrice*(1+$currTicketPoint*0.01),2);
    						}
    					}
    					
    					$paramDetail[] = array(
    							'purchase_no' => $value['pur_number'],
    							'sku' => $v['sku'],
    							'price' => $currPrice,
    							'qty' => $currInstockSkuMap[$v['sku']],
    							'is_donation' => $v['price'] ? 0 : 1,
    							'ticketed_point' => $currTicketPoint,
    							'base_price' => $currBasePrice,
    					);
    				}
    				
    				if(!empty($paramDetail)){
    					$rlt2 = $midOrderDetailModel->batchInsertData( array_keys($paramDetail[0]), $paramDetail);
    				}
    				$flag = $flag && $rlt1 && $rlt2;
    				if( $rlt1 && $rlt2 ){
    					$successList[] = $value['id'];
    				}
    			}
    			
    			if($flag){
    				$transaction->commit();
    			}else{
    				$transaction->rollBack();
    			}
    		}catch(Exception $ex){
    			$transaction->rollBack();
    		}
    	
    		if (count($successList) > 0){
    			$model->updatePoSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    			echo "<pre>同步成功:<br>";
    			print_r(json_encode($successList));
    			echo "</pre>";
    		}
    		
    		if (count($existList) > 0){
    			$model->updatePoSynchCloud($existList,self::CLOUD_SYNC_STATUS_EXIST,$date);
    			echo "<pre>重复同步订单:<br>";
    			print_r(json_encode($existList));
    			echo "</pre>";
    		}
    	
    		if (count($failsList) > 0){
    			foreach ($failsList as $k => $failsVal){
    				if (!empty($failsVal)){
    					$model->updatePoSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    				}
    			}
    			if ($is_debug){
    				echo "<pre>同步失败:<br>";
    				print_r(json_encode($failsList));
    				echo "</pre>";
    			}
    		}
    		exit('done');
    	}else{
    		exit('data is null');
    	}
    }

    /**
     * 采购订单流程（合同单）
     * @tables MID采购订单主表 MID采购订单明细表
     * @offline http://test.yibainetwork.com/synchcloud/autosync/sync-order-ht
     * @online http://caigou.yibainetwork.com/synchcloud/autosync/sync-order-ht
     */
    public function actionSyncOrderHt()
    {
    	exit('CLOSED...');
        $orderObj = new PurchaseOrder();
        $midOrderObj = new MidPurchaseOrder();
        $midOrderDetailObj = new MidPurchaseOrderDetail();
        $limit = Yii::$app->request->get('limit');
        $limit = $limit ? $limit : $this->limits['order'];
        $orders = $orderObj->getData2($limit);
        if(!$orders) {
            exit('没有数据了');
        }
        if(!empty($orders['valid'])) {
            $orderItems = $orderObj->GetPurcahseOrderItems($orders['valid']);
            $transaction = $midOrderObj->getDb()->beginTransaction();
            try {
                $success1 = $midOrderObj->saveRows($orders['valid']);
                $success2 = $midOrderDetailObj->saveRows($orderItems);
                if($success1 && $success2) {
                    $transaction->commit();
                    echo "Success:".count($success1);
                } else {
                    echo 'Failure';
                    $transaction->rollBack();
                }
            } catch(\Exception $e) {
                $transaction->rollBack();
                Vhelper::dump($e->getMessage());
            }
        }
        if($success1 && $success2) {
            if(!empty($orders['synced'])) {
                $success1 = array_merge($success1, $orders['synced']);
            }
            $orderObj::updateAll(['is_push_to_k3cloud' => self::SYNC_SUCCESS], ['in', 'id', $success1]);
        }
        if(!empty($orders['mistake'])) {
            $orderObj::updateAll(['is_push_to_k3cloud' => self::SYNC_FAILURE], ['in', 'id', $orders['mistake']]);
        }
    }
    
    /**
     * 同步采购单-合同 cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-po-ht-cloud?limit=100&is_debug=1&po=ABD019002
     **/
    public function actionSynchPoHtCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    	 
    	$model = new PurchaseOrder();
    	$modelDetail = new PurchaseOrderItems();
    	$midOrderModel = new MidPurchaseOrder();
    	$midOrderDetailModel = new MidPurchaseOrderDetail();
    	$modelCompactItems = new PurchaseCompactItems();
    	$modelInstockRlt = new PurchaseWarehouseResults();
    	$modelPoRestore = new PurchaseOrderRestore();
    	$modelPoTaxes = new PurchaseOrderTaxes();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $model->getSyncPoHtCloudData('a.id,a.buyer,a.pur_number,a.warehouse_code,a.supplier_code,a.created_at,a.is_drawback',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    	
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $existList = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpBuyers = [];
    		foreach( $need_data as $value ){
    			$tmpBuyers[] = isset($value['buyer']) && $value['buyer'] ? $value['buyer'] : '';
    		}
    		$purchaserArr = $model->getStaffCodeByUsers( array_unique($tmpBuyers) );
    		$staffCodeMap = array_column($purchaserArr, 'username','user_number');
    		 
    		//组装K3所需要的字段信息
    		$transaction = $midOrderModel->getDb()->beginTransaction();
    		try{
    			$flag = true;
    			foreach ($need_data as $value){
    				$checkExist = $midOrderModel->dataExist('purchase_no',$value['pur_number']);
    				if ( isset($checkExist['purchase_no']) && $checkExist['purchase_no'] ){
    					$existList[] = $value['id'];
    					continue;
    				}
    				
    				$staffCode = in_array($value['buyer'], $staffCodeMap) ? array_search($value['buyer'], $staffCodeMap) : '';
    				if ( !$staffCode ){
    					$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    				
    				if ( !$value['warehouse_code'] ){
    					$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_2][] = $value['id'];
    					continue;
    				}
    				
    				if ( !$value['supplier_code'] ){
    					$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_3][] = $value['id'];
    					continue;
    				}
    				
    				if ( !$value['created_at'] ){
    					$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_4][] = $value['id'];
    					continue;
    				}
    				 
    				$flagInstock = true;
    				$need_data_detail = $modelDetail->getSyncPoDetailCloudData('*',$value['pur_number']);
    				if ( !$need_data_detail ){
    					$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_5][] = $value['id'];
    					continue;
    				}else{
    					$currInstockData = $modelInstockRlt->getInStockByPo('sku,instock_qty_count',$value['pur_number']);
    					$currInstockSkuMap = array_column($currInstockData, 'instock_qty_count','sku');
    					foreach( $need_data_detail as $v ){
    						if( !$currInstockData || !isset($currInstockSkuMap[$v['sku']]) || !$currInstockSkuMap[$v['sku']] ){
    							$flagInstock = false;
    							break;
    						}
    					}
    						
    					if ( !$flagInstock ){
    						$failsList[PurchaseOrder::CLOUD_SYNCH_ERROR_6][] = $value['id'];
    						continue;
    					}
    				}
    				
    				if($value['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
    					$orgId = '101';
    				} elseif($value['is_drawback'] == 2) {
    					$orgId = '102';
    				}
    				
    				$freightPrice = 0;
    				/* if( $value->purchaseOrderPayType ) {
    				 $freightPrice = !empty($value->purchaseOrderPayType['freight']) ? $value->purchaseOrderPayType['freight'] : 0;
    				 } else {
    				 $freightPrice = 0;
    				 } */
    				 
    				//检查是否绑定合同
    				$isCompact = 1;
    				/* $compactInfo = $modelCompactItems->getCompactBindByPo('',$value['pur_number']);
    				 if( isset($compactInfo['compact_number']) && $compactInfo['compact_number'] ){
    				 $isCompact = 1;
    				 }else{
    				 //检查付款方式
    				 $payOrderInfo = $model->getPayOrderInfo('pay_type',$value['pur_number']);
    				 if( in_array($payOrderInfo['pay_type'], [3,5]) ){ //银行转账、富有
    				 $isCompact = 1;
    				 }
    				 } */
    				
    				//检查是否有单独开票点
    				$tmpPoints = [];
    				$tmpPoints = $modelPoRestore->getPurchasePurTicketPoint('sku,price,base_price,pur_ticketed_point',$value['pur_number']);
    				$tmpBasePriceMap = array_column($tmpPoints, 'base_price','sku'); //原价
    				$tmpPointMap = array_column($tmpPoints, 'pur_ticketed_point','sku'); //开票点
    				$tmpPriceMap = array_column($tmpPoints, 'price','sku'); //采购价
    				
    				//检查是否有单独的采购含税 -- 针对海外仓采购单
    				$tmpAbPoints = [];
    				$tmpAbPoints = $modelPoTaxes->getAbPurTicketPointList('sku,taxes',[$value['pur_number']]);
    				$tmpAbPointMap = [];
    				if( $tmpAbPoints ){
    					$tmpAbPointMap = array_column($tmpAbPoints, 'taxes','sku'); //开票点
    				}
    				
    				$params = array(
    						'org_id' => $orgId,
    						'hrms_code' => $staffCode,
    						'purchase_no' => $value['pur_number'],
    						'warehouse_code' => $value['warehouse_code'],
    						'supplier_code' => $value['supplier_code'],
    						'freight_price' => $freightPrice,
    						'purchase_date' => $value['created_at'],
    						'is_compact' => $isCompact,
    						//'company_org' => '',
    				);
    				if(!empty($params)){
    					$rlt1 = $midOrderModel->batchInsertData( array_keys($params), [$params]);
    				}
    				
    				$paramDetail = [];
    				foreach( $need_data_detail as $v ){
    					
    					$tmpPointMap[$v['sku']] = isset($tmpPointMap[$v['sku']]) ? $tmpPointMap[$v['sku']] : '';
    					$tmpBasePriceMap[$v['sku']] = isset($tmpBasePriceMap[$v['sku']]) ? $tmpBasePriceMap[$v['sku']] : '';
    					$tmpPriceMap[$v['sku']] = isset($tmpPriceMap[$v['sku']]) ? $tmpPriceMap[$v['sku']] : '';
    					$tmpAbPointMap[$v['sku']] = isset($tmpAbPointMap[$v['sku']]) ? $tmpAbPointMap[$v['sku']] : '';
    					
    					$currTicketPoint = isset($v['pur_ticketed_point'])?$v['pur_ticketed_point']:0;
    					$currPrice = isset($v['price'])?$v['price']:0;
    					$currBasePrice = isset($v['base_price'])?$v['base_price']:0;
    					
    					if( $tmpPointMap[$v['sku']] && $tmpBasePriceMap[$v['sku']] && $tmpPriceMap[$v['sku']] ){
    						$currTicketPoint = $tmpPointMap[$v['sku']]*100;
    						$currPrice = $tmpPriceMap[$v['sku']];
    						$currBasePrice = $tmpBasePriceMap[$v['sku']];
    					}elseif( $tmpAbPointMap[$v['sku']] && $value['is_drawback'] == 2 ){ //有税点，并且采购单退税
    						if( $currPrice == $currBasePrice || empty($currBasePrice) ){ //采购单明细 含税价和不含税价相等 或者 不含税价为0
    							$currBasePrice = $currPrice;
    							$currTicketPoint = $tmpAbPointMap[$v['sku']];
    							$currPrice = round($currPrice*(1+$currTicketPoint*0.01),2);
    						}
    					}
    					
    					$paramDetail[] = array(
    							'purchase_no' => $value['pur_number'],
    							'sku' => $v['sku'],
    							'price' => $currPrice,
    							'qty' => $currInstockSkuMap[$v['sku']],
    							'is_donation' => $v['price'] ? 0 : 1,
    							'ticketed_point' => $currTicketPoint,
    							'base_price' => $currBasePrice,
    					);
    				}
    				if(!empty($paramDetail)){
    					$rlt2 = $midOrderDetailModel->batchInsertData( array_keys($paramDetail[0]), $paramDetail);
    				}
    				$flag = $flag && $rlt1 && $rlt2;
    				if( $rlt1 && $rlt2 ){
    					$successList[] = $value['id'];
    				}
    			}
    			
    			if($flag){
    				$transaction->commit();
    			}else{
    				$transaction->rollBack();
    			}
    		}catch(Exception $ex){
    			$transaction->rollBack();
    		}
    		 
    		if (count($successList) > 0){
    			$model->updatePoSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    			echo "<pre>同步成功:<br>";
    			print_r(json_encode($successList));
    			echo "</pre>";
    		}
    		
    		if (count($existList) > 0){
    			$model->updatePoSynchCloud($existList,self::CLOUD_SYNC_STATUS_EXIST,$date);
    			echo "<pre>重复同步订单:<br>";
    			print_r(json_encode($existList));
    			echo "</pre>";
    		}
    		 
    		if (count($failsList) > 0){
    			foreach ($failsList as $k => $failsVal){
    				if (!empty($failsVal)){
    					$model->updatePoSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    				}
    			}
    			if ($is_debug){
    				echo "<pre>同步失败:<br>";
    				print_r(json_encode($failsList));
    				echo "</pre>";
    			}
    		}
    		exit('done');
    	}else{
    		exit('data is null');
    	}
    }

    /**
     * 采购付款流程
     * @tables MID采购订单付款表
     * @offline http://test.yibainetwork.com/synchcloud/autosync/sync-payment
     * @online http://caigou.yibainetwork.com/synchcloud/autosync/sync-payment
     */
    public function actionSyncPayment()
    {
    	exit('CLOSED...');
        $waterObj = new PurchaseOrderPayWater();
        $midPaymentObj = new MidPurchasePayment();
        $data = $waterObj->getData($this->limits['payment']);
        $success = [];
        if(!empty($data['valid'])) {
            $transaction = $midPaymentObj->getDb()->beginTransaction();
            try {
                $success = $midPaymentObj->saveRows($data['valid']);
                if($success) {
                    $transaction->commit();
                    echo 'Success:'.count($success);
                } else {
                    echo 'Failure';
                    $transaction->rollBack();
                }
            } catch(\Exception $e) {
                $transaction->rollBack();
                Vhelper::dump($e->getMessage());
            }
        }
        if(!empty($success)) {
            $waterObj::updateAll(['is_push_to_k3cloud' => self::SYNC_SUCCESS], ['in', 'id', $success]);
        }
        if(!empty($data['mistake'])) {
            $waterObj::updateAll(['is_push_to_k3cloud' => self::SYNC_FAILURE], ['in', 'id', $data['mistake']]);
        }
    }
    
    /**
     * 同步采购付款流程 cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-payment-cloud?limit=100&is_debug=1&po=PO261015
     **/
    public function actionSynchPaymentCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po'); //采购付款流水的pur_number(采购单号或者合同号)
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new PurchaseOrderPay();
    	$purModel = new PurchaseOrder();
    	$midPaymentModel = new MidPurchasePayment();
    	$modelCompactItems = new PurchaseCompactItems();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $model->getSyncPaymentCloudData('a.id,a.pay_account,a.pur_number,a.applicant,a.payer,a.payment_notice,a.requisition_number,a.supplier_code,a.payer_time,a.pay_price,a.k3_account',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    	
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpUserIds = [];
    		foreach( $need_data as $value ){
    			$tmpUserIds[] = isset($value['applicant']) && $value['applicant'] ? $value['applicant'] : '';
    			$tmpUserIds[] = isset($value['payer']) && $value['payer'] ? $value['payer'] : '';
    		}
    		$usersArr = $purModel->getUserNumberByIds( array_unique($tmpUserIds) );
    		$staffCodeMap = array_column($usersArr, 'user_number','id');
    		 
    		//组装K3所需要的字段信息
    		try{
	    		foreach ($need_data as $value){
	    				$billNo = $value['requisition_number'].'_'.str_pad($value['id'], 10, '0', STR_PAD_LEFT);
	    				if ($midPaymentModel->dataExist('id',$value['pur_number'],$billNo)){
	    					$successList[] = $value['id'];
	    					continue;
	    				}
	    				
	    				if( !isset($value['pay_price']) || !$value['pay_price'] ){
	    					$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_1][] = $value['id'];
	    					continue;
	    				}
	    				
	    				if( !$value['applicant'] || !$staffCodeMap[$value['applicant']] ){
	    					$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_2][] = $value['id'];
	    					continue;
	    				}
	    				
	    				if( !$value['payer'] || !$staffCodeMap[$value['payer']] ){
	    					$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_3][] = $value['id'];
	    					continue;
	    				}
	    					
	    				if( !$value['payer_time'] ){
	    					$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_4][] = $value['id'];
	    					continue;
	    				}
	    				
	    				if(preg_match('/HT/', $value['pur_number'])) {
	    					$cmp = isset($value->purchaseCompact) ? $value->purchaseCompact : null;
	    					if(empty($cmp)) {
	    						$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_5][] = $value['id'];
	    						continue;
	    					}
	    					$isDrawback = $cmp['is_drawback'] ? $cmp['is_drawback'] : 1;
	    				} else {
	    					$purchaseOrder = isset($value->purchaseOrder) ? $value->purchaseOrder : null;
	    					if(empty($purchaseOrder)) {
	    						$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_6][] = $value['id'];
	    						continue;
	    					}
	    					$isDrawback = $purchaseOrder['is_drawback'] ? $purchaseOrder['is_drawback'] : 1;
	    				}
	    				
	    				if($isDrawback == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
	    					$orgId = '101';
	    				} elseif($isDrawback == 2) {
	    					$orgId = '102';
	    				}
	    				
	    				$bank = isset($value->bankAccount)?$value->bankAccount:null;
	    				if( !$bank ){ //检查是否有付款账号数据
	    					$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_7][] = $value['id'];
	    					continue;
	    				}else{
	    					$k3_bank_account = $value['k3_account'] ? $value['k3_account'] : '999.999';
	    				}
	    				
	    				//检查是否绑定合同
	    				$isCompact = 0;
	    				$compactInfo = $modelCompactItems->getPoInfoByCompact('',$value['pur_number']);
	    				if( $compactInfo && sizeof($compactInfo)>0 ){
	    					$isCompact = 1;
	    				}else{
	    					//检查付款方式
	    					$payOrderInfo = $purModel->getPayOrderInfo('pay_type',$value['pur_number']);
	    					if( in_array($payOrderInfo['pay_type'], [3,5]) ){ //银行转账、富有
	    						$isCompact = 1;
	    					}
	    				}
	    				
	    				$params[] = array(
	    						'bill_no' => $billNo,
	    						'provider_id' => $value['supplier_code'],
	    						'dates' => $value['payer_time'], //业务日期
	    						'pay_id' => $k3_bank_account,
	    						'purchase_id' => $staffCodeMap[$value['applicant']],
	    						'payment_user_id' => $staffCodeMap[$value['payer']],
	    						'pay_amount' => $value['pay_price'],
	    						'purchase_no' => $value['pur_number'],
	    						'note' => $value['payment_notice'],
	    						'org_id' => $orgId,
	    						'is_compact' => $isCompact,
	    				);
	    				$insertParams[] = $value['id'];
	    		}
	    		
	    		if(!empty($params)){
	    			if ($midPaymentModel->batchInsertData( array_keys($params[0]), $params)) {
	    				foreach( $insertParams as $v ){
	    					$successList[] = $v;
	    				}
	    			}
	    		}
	    		
	    		if (count($successList) > 0){
	    			$model->updatePoSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
	    			echo "<pre>同步成功:<br>";
	    			print_r(json_encode($successList));
	    			echo "</pre>";
	    		}
	    		
	    		if (count($failsList) > 0){
	    			foreach ($failsList as $k => $failsVal){
	    				if (!empty($failsVal)){
	    					$model->updatePoSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
	    				}
	    			}
	    			if ($is_debug){
	    				echo "<pre>同步失败:<br>";
	    				print_r(json_encode($failsList));
	    				echo "</pre>";
	    			}
	    		}
    		
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    /**
     * 同步采购付款流程 cloud 从财务提供临时表取数据
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-payment-restore-cloud?limit=100&is_debug=1&po=PO261015
     **/
    public function actionSynchPaymentRestoreCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po'); //采购付款流水的pur_number(采购单号或者合同号)
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new PurchaseOrderPayRestore();
    	$purModel = new PurchaseOrder();
    	$midPaymentModel = new MidPurchasePayment();
    	$modelCompactItems = new PurchaseCompactItems();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $model->getSyncPaymentRestoreCloudData('a.id,a.pay_account,a.pur_number,a.applicant,a.payer,a.payment_notice,a.requisition_number,a.supplier_code,a.payer_time,a.pay_price,a.k3_account',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    	 
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpUserIds = [];
    		foreach( $need_data as $value ){
    			$tmpUserIds[] = isset($value['applicant']) && $value['applicant'] ? $value['applicant'] : '';
    			$tmpUserIds[] = isset($value['payer']) && $value['payer'] ? $value['payer'] : '';
    		}
    		$usersArr = $purModel->getUserNumberByIds( array_unique($tmpUserIds) );
    		$staffCodeMap = array_column($usersArr, 'user_number','id');
    		 
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				$billNo = $value['requisition_number'].'_'.str_pad($value['id'], 10,'0',STR_PAD_LEFT);
    				if ($midPaymentModel->dataExist('id',$value['pur_number'],$billNo)){
    					$successList[] = $value['id'];
    					continue;
    				}
    	    
    				if( !isset($value['pay_price']) || !$value['pay_price'] ){
    					$failsList[PurchaseOrderPayRestore::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    	    
    				if( !$value['applicant'] || !$staffCodeMap[$value['applicant']] ){
    					$failsList[PurchaseOrderPayRestore::CLOUD_SYNCH_ERROR_2][] = $value['id'];
    					continue;
    				}
    	    
    				if( !$value['payer'] || !$staffCodeMap[$value['payer']] ){
    					$failsList[PurchaseOrderPayRestore::CLOUD_SYNCH_ERROR_3][] = $value['id'];
    					continue;
    				}
    
    				if( !$value['payer_time'] ){
    					$failsList[PurchaseOrderPayRestore::CLOUD_SYNCH_ERROR_4][] = $value['id'];
    					continue;
    				}
    	    
    				if(preg_match('/HT/', $value['pur_number'])) { //如果是合同单号
    					//先查出真实合同信息
    					$cmp = $model->getCompactData('compact_number,is_drawback',$value['pur_number']);
    					if(!empty($cmp)) { //存在真实合同
    						$isDrawback = $cmp['is_drawback'] ? $cmp['is_drawback'] : 1;
    					}else{ //不存在真实合同，则查虚拟合同，再查采购单
    						$cmpBind = $model->getCompactRestoreBindData('pur_number',$value['pur_number']);
    						$tmpPurNumbers = array_column($cmpBind, 'pur_number'); //虚拟合同关联的采购单
    						$tmpPoArr = $purModel->getPurchaseByPo('pur_number,is_drawback',$tmpPurNumbers);
    						$isDrawback = $tmpPoArr[0]['is_drawback'] ? $tmpPoArr[0]['is_drawback'] : 1;
    					}
    				} else { //采购单号
    					$purchaseOrder = isset($value->purchaseOrder) ? $value->purchaseOrder : null;
    					if(empty($purchaseOrder)) {
    						$failsList[PurchaseOrderPayRestore::CLOUD_SYNCH_ERROR_6][] = $value['id'];
    						continue;
    					}
    					$isDrawback = $purchaseOrder['is_drawback'] ? $purchaseOrder['is_drawback'] : 1;
    				}
    	    
    				if($isDrawback == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
    					$orgId = '101';
    				} elseif($isDrawback == 2) {
    					$orgId = '102';
    				}
    	    
    				$bank = isset($value->bankAccount)?$value->bankAccount:null;
    				if( !$bank ){ //检查是否有付款账号数据
    					$failsList[PurchaseOrderPayRestore::CLOUD_SYNCH_ERROR_7][] = $value['id'];
    					continue;
    				}else{
    					$k3_bank_account = $value['k3_account'] ? $value['k3_account'] : '999.999';
    				}
    	    
    				/* //检查是否绑定合同
    				$isCompact = 0;
    				$compactInfo = $modelCompactItems->getPoInfoByCompact('',$value['pur_number']);
    				if( $compactInfo && sizeof($compactInfo)>0 ){
    					$isCompact = 1;
    				}else{
    					//检查付款方式
    					$payOrderInfo = $purModel->getPayOrderInfo('pay_type',$value['pur_number']);
    					if( in_array($payOrderInfo['pay_type'], [3,5]) ){ //银行转账、富有
    						$isCompact = 1;
    					}
    				} */
    	    
    				$params[] = array(
    						'bill_no' => $billNo,
    						'provider_id' => $value['supplier_code'],
    						'dates' => $value['payer_time'], //业务日期
    						'pay_id' => $k3_bank_account,
    						'purchase_id' => $staffCodeMap[$value['applicant']],
    						'payment_user_id' => $staffCodeMap[$value['payer']],
    						'pay_amount' => $value['pay_price'],
    						'purchase_no' => $value['pur_number'],
    						'note' => isset($value['payment_notice'])?$value['payment_notice'] : 'C',
    						'org_id' => $orgId,
    						'is_compact' => 1,
    				);
    				$insertParams[] = $value['id'];
    			}
    	   
    			if(!empty($params)){
    				if ($midPaymentModel->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    	   
    			if (count($successList) > 0){
    				$model->updatePoSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    	   
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updatePoSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    		exit('done');
    	}else{
    		exit('data is null');
    	}
    }
    

    /**
     * 采购合同与采购单的绑定关系
     * @tables MID采购付款关系表
     * @offline http://test.yibainetwork.com/synchcloud/autosync/sync-compact-link-order
     * @online http://caigou.yibainetwork.com/synchcloud/autosync/sync-compact-link-order
     */
    public function actionSyncCompactLinkOrder()
    {
    	exit('CLOSED...');
        $model = new PurchaseCompactItems();
        $mid = new MidPurchasePayRelation();
        $data = $model->getData($this->limits['compact_items']);
        $transaction = $mid->getDb()->beginTransaction();
        try {
            $success = $mid->saveRows($data);
            if($success) {
                $transaction->commit();
                echo 'Success:'.count($success);
            } else {
                echo 'Failure';
                $transaction->rollBack();
            }
        } catch(\Exception $e) {
            $transaction->rollBack();
            Vhelper::dump($e->getMessage());
        }
        if($success) {
            $model::updateAll(['is_push_to_k3cloud' => self::SYNC_SUCCESS], ['in', 'id', $success]);
        }
    }
    
    /**
     * 同步采购合同与采购单的绑定关系 cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-compact-bind-cloud?limit=1&is_debug=1&po=AB090901
     **/
    public function actionSynchCompactBindCloud()
    {
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$compactNo = Yii::$app->request->get('compact_no');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new PurchaseCompactItems();
        $midRelation = new MidPurchasePayRelation();
    
    	$conditions = '';
    	if (!empty($compactNo)){
    		$compactNo = explode(',',$compactNo);
    		$compactNo = implode("','",$compactNo);
    		$conditions .= " and a.compact_number in('$compactNo')";
    	}
    
    	$need_data = $model->getSyncCompactBindCloudData('a.id,a.pur_number,a.compact_number',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				if ($midRelation->dataExist('id',$value['pur_number'],$value['compact_number'])){
    					$successList[] = $value['id'];
    					continue;
    				}
    	    
    				if ( empty($value['pur_number']) || empty($value['compact_number']) ){
    					$failsList[PurchaseCompactItems::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    	    
    				$params[] = array(
    						'purchase_contract_no' => $value['compact_number'],
    						'purchase_order_no' => $value['pur_number'],
    				);
    				$insertParams[] = $value['id'];
    			}
    			
    			if(!empty($params)){
    				if ($midRelation->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    	   
    			if (count($successList) > 0){
    				$model->updateCompactBindSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    	   
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateCompactBindSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    		exit('done');
    	}else{
    		exit('data is null');
    	}
    }
    
    /**
     * 同步财务临时维护采购合同与采购单的绑定关系 cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-compact-bind-restore-cloud?limit=1&is_debug=1&po=AB090901
     **/
    public function actionSynchCompactBindRestoreCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$compactNo = Yii::$app->request->get('compact_no');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new PurchaseCompactItemsRestore();
    	$midRelation = new MidPurchasePayRelation();
    
    	$conditions = '';
    	if (!empty($compactNo)){
    		$compactNo = explode(',',$compactNo);
    		$compactNo = implode("','",$compactNo);
    		$conditions .= " and a.compact_number in('$compactNo')";
    	}
    
    	$need_data = $model->getSyncCompactBindCloudData('a.id,a.pur_number,a.compact_number',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				if ($midRelation->dataExist('id',$value['pur_number'],$value['compact_number'])){
    					$successList[] = $value['id'];
    					continue;
    				}
    					
    				if ( empty($value['pur_number']) || empty($value['compact_number']) ){
    					$failsList[PurchaseCompactItems::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    					
    				$params[] = array(
    						'purchase_contract_no' => $value['compact_number'],
    						'purchase_order_no' => $value['pur_number'],
    				);
    				$insertParams[] = $value['id'];
    			}
    			 
    			if(!empty($params)){
    				if ($midRelation->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				$model->updateCompactBindSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateCompactBindSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    		exit('done');
    	}else{
    		exit('data is null');
    	}
    }

    /**
     * 采购退款流程
     * @tables MID采购订单收款表
     * @offline http://test.yibainetwork.com/synchcloud/autosync/sync-receipt
     * @online http://caigou.yibainetwork.com/synchcloud/autosync/sync-receipt
     */
    public function actionSyncReceipt()
    {
    	exit('CLOSED...');
        $model = new PurchaseOrderReceiptWater();
        $mid = new MidPurchaseRefund();
        $data = $model->getData($this->limits['receipt']);
        $success = [];
        if(!empty($data['valid'])) {
            $transaction = $mid->getDb()->beginTransaction();
            try {
                $success = $mid->saveRows($data['valid']);
                if($success) {
                    $transaction->commit();
                    echo "Success:".count($success);
                } else {
                    echo 'Failure';
                    $transaction->rollBack();
                }
            } catch(\Exception $e) {
                $transaction->rollBack();
                Vhelper::dump($e->getMessage());
            }
        }
        if(!empty($success)) {
            $model::updateAll(['is_push_to_k3cloud' => self::SYNC_SUCCESS], ['in', 'id', $success]);
        }
        if(!empty($data['mistake'])) {
            $model::updateAll(['is_push_to_k3cloud' => self::SYNC_FAILURE], ['in', 'id', $data['mistake']]);
        }
    }
    
    /**
     * 同步采购退款单 cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-receipt-cloud?limit=1&is_debug=1&po=AB090901
     **/
    public function actionSynchReceiptCloud()
    {
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new PurchaseOrderReceiptWater();
    	$purModel = new PurchaseOrder();
    	$mid = new MidPurchaseRefund();
    	$modelCompactItems = new PurchaseCompactItems();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $model->getSyncReceiptCloudData('a.id,a.pur_number,a.transaction_number,a.supplier_code,a.pay_time,a.create_id,a.price,a.remarks,a.our_account_abbreviation',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpUserIds = [];
    		foreach( $need_data as $value ){
    			$orderReceipt = $value->purchaseOrderReceipt;
    			$tmpUserIds[] = isset($orderReceipt['applicant']) && $orderReceipt['applicant'] ? $orderReceipt['applicant'] : '';
    			$tmpUserIds[] = isset($value['create_id']) && $value['create_id'] ? $value['create_id'] : '';
    		}
    		$usersArr = $purModel->getUserNumberByIds( array_unique($tmpUserIds) );
    		$staffCodeMap = array_column($usersArr, 'user_number','id');
    		 
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				if ($mid->dataExist('id',$value['pur_number'])){
    					$successList[] = $value['id'];
    					continue;
    				}
    					
    				$orderReceipt = isset($value->purchaseOrderReceipt)?$value->purchaseOrderReceipt:null;
    				if ( !$orderReceipt ){
    					$failsList[PurchaseOrderReceiptWater::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    				 
    				if( !$orderReceipt['applicant'] || !$staffCodeMap[$orderReceipt['applicant']] ){
    					$failsList[PurchaseOrderReceiptWater::CLOUD_SYNCH_ERROR_2][] = $value['id'];
    					continue;
    				}
    				 
    				if( !$value['create_id'] || !$staffCodeMap[$value['create_id']] ){
    					$failsList[PurchaseOrderReceiptWater::CLOUD_SYNCH_ERROR_3][] = $value['id'];
    					continue;
    				}
    				
    				if(preg_match('/HT/', $value['pur_number'])) {
    					$cmp = isset($value->purchaseCompact) ? $value->purchaseCompact : null;
    					if(empty($cmp)) {
    						$failsList[PurchaseOrderReceiptWater::CLOUD_SYNCH_ERROR_5][] = $value['id'];
    						continue;
    					}
    					$isDrawback = $cmp['is_drawback'] ? $cmp['is_drawback'] : 1;
    				} else {
    					$purchaseOrder = isset($value->purchaseOrder)?$value->purchaseOrder:null;
    					if(empty($purchaseOrder)) {
    						$failsList[PurchaseOrderReceiptWater::CLOUD_SYNCH_ERROR_6][] = $value['id'];
    						continue;
    					}
    					$isDrawback = $purchaseOrder['is_drawback'] ? $purchaseOrder['is_drawback'] : 1;
    				}
    				 
    				if($isDrawback == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
    					$orgId = '101';
    				} elseif($isDrawback == 2) {
    					$orgId = '102';
    				}
    				 
    				$bank = isset($value->bankAccount)?$value->bankAccount:null;
    				if(!empty($bank)) {
    					$k3_bank_account = $bank['k3_bank_account'] ? $bank['k3_bank_account'] : '999.999';
    				} else {
    					$k3_bank_account = '999.999';
    				}
    				
    				//检查是否绑定合同
    				$isCompact = 0;
    				$compactInfo = $modelCompactItems->getCompactBindByPo('',$value['pur_number']);
    				if( isset($compactInfo['compact_number']) && $compactInfo['compact_number'] ){
    					$isCompact = 1;
    				}else{
    					//检查付款方式
    					$payOrderInfo = $purModel->getPayOrderInfo('pay_type',$value['pur_number']);
    					if( in_array($payOrderInfo['pay_type'], [3,5]) ){ //银行转账、富有
    						$isCompact = 1;
    					}
    				}
    				
    				$params[] = array(
    						'bill_no' => $value['transaction_number'],
    						'provider_id' => $value['supplier_code'],
    						'dates' => $value['pay_time'], //业务日期
    						'pay_id' => $k3_bank_account,
    						'purchase_id' => $staffCodeMap[$orderReceipt['applicant']],
    						'payment_user_id' => $staffCodeMap[$value['create_id']],
    						'refund_money' => $value['price'],
    						'purchase_no' => $value['pur_number'],
    						'note' => $value['remarks'],
    						'org_id' => $orgId,
    						'is_compact' => $isCompact,
    						
    				);
    				$insertParams[] = $value['id'];
    			}
    
    			if(!empty($params)){
    				if ($mid->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				$model->updateReceiptSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateReceiptSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }

    /**
     * 采购入库流程
     * @table MID采购入库主表
     * @offline http://test.yibainetwork.com/synchcloud/autosync/sync-ruku
     * @online http://caigou.yibainetwork.com/synchcloud/autosync/sync-ruku
     */
    public function actionSyncRuku()
    {
    	exit('CLOSED');
        $model = new MidPurchaseIn();
        $data = $model->getData($this->limits['ruku']);
        if(empty($data)) {
            exit('没有数据了');
        }
        $saveData = [];
        foreach($data as $m) {
            $order = PurchaseOrder::find()->where(['pur_number' => $m->purchase_no])->one();
            $arr = [];
            $arr['id'] = $m->id;
            $arr['hrms_code'] = $order->getUserNumber($order->buyer);
            if($arr['hrms_code'] == '') {
                continue;
            }
            $arr['is_drawback'] = $order->is_drawback;
            $saveData[] = $arr;
        }
        if(empty($saveData)) {
            exit('没有数据了');
        }
        $success = $model->saveRows($saveData);
        if($success) {
            echo 'Success:'.count($success);
        } else {
            echo 'Failure';
        }
    }
    
    /**
     * 同步优惠额（只同步大于0的数据）
     * @table MID采购折扣
     * @offline http://test.yibainetwork.com/synchcloud/autosync/sync-discount
     * @online http://caigou.yibainetwork.com/synchcloud/autosync/sync-discount
     */
    public function actionSyncDiscount()
    {
    	exit('CLOSED...');
        $model = new PurchaseOrderPayType();
        $discount = new MidPurchaseDiscount();
        $data = $model->getData($this->limits['discount']);
        if(empty($data)) {
            exit('没有数据了');
        }
        $transaction = $model->getDb()->beginTransaction();
        try {
            $success1 = $discount->saveRows($data);
            $success2 = $model::updateAll(['discount_push_to_k3cloud' => 1], ['in', 'id', $success1]);
            if($success1 && $success2) {
                echo 'SUCCESS:'.count($success1);
                $transaction->commit();
            } else {
                echo 'Failure';
                $transaction->rollBack();
            }
        } catch(\Exception $e) {
            $transaction->rollBack();
            Vhelper::dump($e->getMessage());
        }
    }
    
    /**
     * 同步优惠 cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-discount-cloud?limit=1&is_debug=1&po=AB090901
     **/
    public function actionSynchDiscountCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new PurchaseOrderPayType();
    	$modelPo = new PurchaseOrder();
    	$midDiscount = new MidPurchaseDiscount();
    	$modelCompactItems = new PurchaseCompactItems();
    	$modelCompactItemsRestore = new PurchaseCompactItemsRestore();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $model->getSyncDiscountCloudData('b.id,b.discount,a.pur_number,a.supplier_code,a.buyer,a.created_at,a.is_drawback,b.note',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpBuyers = [];
    		foreach( $need_data as $value ){
    			$tmpBuyers[] = isset($value['buyer']) && $value['buyer'] ? $value['buyer'] : '';
    		}
    		$purchaserArr = $modelPo->getStaffCodeByUsers( array_unique($tmpBuyers) );
    		$staffCodeMap = array_column($purchaserArr, 'username','user_number');
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				$billNo = $value['pur_number'].'_'.str_pad($value['id'], 10, '0', STR_PAD_LEFT);
    				if ($midDiscount->dataExist('id',$value['pur_number'],$billNo)){
    					$successList[] = $value['id'];
    					continue;
    				}
    					
    				$staffCode = in_array($value['buyer'], $staffCodeMap) ? array_search($value['buyer'], $staffCodeMap) : '';
    				if ( !$staffCode ){
    					$failsList[PurchaseOrderPayType::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    				
    				if ( empty($value['created_at']) ){
    					$failsList[PurchaseOrderPayType::CLOUD_SYNCH_ERROR_2][] = $value['id'];
    					continue;
    				}
    
    				if($value['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
    					$orgId = '101';
    				} elseif($value['is_drawback'] == 2) {
    					$orgId = '102';
    				}
    				
    				//检查是否绑定合同
    				$isCompact = 0;
    				$compactInfo = $modelCompactItems->getCompactBindByPo('compact_number',$value['pur_number']);
    				if( isset($compactInfo['compact_number']) && $compactInfo['compact_number'] ){
    					$isCompact = 1;
    				}else{
    					$compactInfoRestore = $modelCompactItemsRestore->getCompactBindByPo('compact_number',$value['pur_number']);
    					if( isset($compactInfoRestore['compact_number']) && $compactInfoRestore['compact_number'] ){
    						$isCompact = 1;
    					}else{
    						//检查付款方式
    						$payOrderInfo = $modelPo->getPayOrderInfo('pay_type',$value['pur_number']);
    						if( in_array($payOrderInfo['pay_type'], [3,5]) ){ //银行转账、富有
    							$isCompact = 1;
    						}
    					}
    				}
    				
    				$params[] = array(
    						'bill_no' => $billNo,
    						'purchase_no' => $value['pur_number'],
    						'provider_id' => $value['supplier_code'],
    						'purchase_id' => $staffCode,
    						'discount' => $value['discount'] ? $value['discount'] : 0,
    						'note' => $value['note'],
    						'dates' => $value['created_at'], //业务日期
    						'org_id' => $orgId,
    						'is_compact' => $isCompact,
    				);
    				$insertParams[] = $value['id'];
    			}
    
    			if(!empty($params)){
    				if ($midDiscount->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				$model->updateDiscountSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateDiscountSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    		exit('done');
    	}else{
    		exit('data is null');
    	}
    }

    /**
     * 优惠额修改后同步差值（同步已经推送到MID的，且修改过优惠额的）
     * @table MID采购运费
     * @offline http://test.yibainetwork.com/synchcloud/autosync/sync-discount-diff
     * @online http://caigou.yibainetwork.com/synchcloud/autosync/sync-discount-diff
     */
    public function actionSyncDiscountDiff()
    {
    	exit('CLOSED...');
        $model = new PurchaseOrderPayType();
        $discount = new MidPurchaseDiscount();
        $data = $model->getData4($this->limits['discount_diff']);
        if(empty($data)) {
            exit('没有数据了');
        }
        $success = [];
        $transaction = $model->getDb()->beginTransaction();
        try {
            $success = $discount->saveRows2($data);
            if($success) {
                $transaction->commit();
                echo 'Success:'.count($success);
            } else {
                echo 'Failure';
                $transaction->rollBack();
            }
        } catch(\Exception $e) {
            $transaction->rollBack();
            Vhelper::dump($e->getMessage());
        }
        if(!empty($success)) {
            $model::updateAll(['discount_push_to_k3cloud' => self::SYNC_SUCCESS, 'is_update_discount' => 0], ['in', 'id', $success]);
        }
    }
    
    /**
     * 同步优惠差值 cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-discount-diff-cloud?limit=1&is_debug=1&po=AB090901
     **/
    public function actionSynchDiscountDiffCloud()
    {
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new PurchaseOrderPayType();
    	$modelPo = new PurchaseOrder();
    	$midDiscount = new MidPurchaseDiscount();
    	$modelCompactItems = new PurchaseCompactItems();
    	$modelCompactItemsRestore = new PurchaseCompactItemsRestore();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $model->getSyncDiscountDiffCloudData('b.id,b.discount,a.pur_number,a.supplier_code,a.buyer,a.created_at,a.is_drawback,b.note',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpBuyers = [];
    		foreach( $need_data as $value ){
    			$tmpBuyers[] = isset($value['buyer']) && $value['buyer'] ? $value['buyer'] : '';
    		}
    		$purchaserArr = $modelPo->getStaffCodeByUsers( array_unique($tmpBuyers) );
    		$staffCodeMap = array_column($purchaserArr, 'username','user_number');
    
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				$synchedDiscount = $midDiscount->getSynchedDiscountByPo($value['pur_number']);
    				$discountDiff = isset($synchedDiscount['sum_discount']) && $synchedDiscount['sum_discount'] ? bcsub($value['discount'], $synchedDiscount['sum_discount'],3) : $value['discount'];
    				if( $discountDiff == 0 ){
    					$successList[] = $value['id'];
    					continue;
    				}
    					
    				$staffCode = in_array($value['buyer'], $staffCodeMap) ? array_search($value['buyer'], $staffCodeMap) : '';
    				if ( !$staffCode ){
    					$failsList[PurchaseOrderPayType::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    
    				if ( empty($value['created_at']) ){
    					$failsList[PurchaseOrderPayType::CLOUD_SYNCH_ERROR_2][] = $value['id'];
    					continue;
    				}
    
    				if($value['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
    					$orgId = '101';
    				} elseif($value['is_drawback'] == 2) {
    					$orgId = '102';
    				}
    				
    				//检查是否绑定合同
    				$isCompact = 0;
    				$compactInfo = $modelCompactItems->getCompactBindByPo('compact_number',$value['pur_number']);
    				if( isset($compactInfo['compact_number']) && $compactInfo['compact_number'] ){
    					$isCompact = 1;
    				}else{
    					$compactInfoRestore = $modelCompactItemsRestore->getCompactBindByPo('compact_number',$value['pur_number']);
    					if( isset($compactInfoRestore['compact_number']) && $compactInfoRestore['compact_number'] ){
    						$isCompact = 1;
    					}else{
    						//检查付款方式
    						$payOrderInfo = $modelPo->getPayOrderInfo('pay_type',$value['pur_number']);
    						if( in_array($payOrderInfo['pay_type'], [3,5]) ){ //银行转账、富有
    							$isCompact = 1;
    						}
    					}
    				}
    				
    				$billNo = $value['pur_number'].'_'.str_pad($value['id'], 10, '0', STR_PAD_LEFT).'_'.substr(time(), -3, 3);
    				
    				$params[] = array(
    						'bill_no' => $billNo,
    						'purchase_no' => $value['pur_number'],
    						'provider_id' => $value['supplier_code'],
    						'purchase_id' => $staffCode,
    						'discount' => $discountDiff,
    						'note' => $value['note'],
    						'dates' => $value['created_at'], //业务日期
    						'org_id' => $orgId,
    						'is_compact' => $isCompact,
    				);
    				$insertParams[] = $value['id'];
    			}
    			
    			if(!empty($params)){
    				if ($midDiscount->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				$model->updateDiscountDiffSynchCloud($successList,0,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateDiscountDiffSynchCloud($failsVal,1,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    		exit('done');
    	}else{
    		exit('data is null');
    	}
    }

    /**
     * 同步运费（只同步大于0的数据，只同步已经同步到MID采购单的数据）
     * @table MID采购运费
     * @offline http://test.yibainetwork.com/synchcloud/autosync/sync-freight
     * @online http://caigou.yibainetwork.com/synchcloud/autosync/sync-freight
     */
    public function actionSyncFreight()
    {
    	exit('CLOSED...');
        $model = new PurchaseOrderPayType();
        $freight = new MidPurchaseFreight();
        $data = $model->getData2($this->limits['freight']);
        if(empty($data)) {
            exit('没有数据了');
        }
        $success = [];
        $transaction = $model->getDb()->beginTransaction();
        try {
            $success = $freight->saveRows($data);
            if($success) {
                $transaction->commit();
                echo 'Success:'.count($success);
            } else {
                echo 'Failure';
                $transaction->rollBack();
            }
        } catch(\Exception $e) {
            $transaction->rollBack();
            Vhelper::dump($e->getMessage());
        }
        if(!empty($success)) {
            $model::updateAll(['freight_push_to_k3cloud' => self::SYNC_SUCCESS], ['in', 'id', $success]);
        }
    }
    
    /**
     * 同步采购运费 cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-freight-cloud?limit=1&is_debug=1&po=AB090901
     **/
    public function actionSynchFreightCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new PurchaseOrderPayType();
    	$modelPo = new PurchaseOrder();
    	$midFreight = new MidPurchaseFreight();
    	$modelCompactItems = new PurchaseCompactItems();
    	$modelCompactItemsRestore = new PurchaseCompactItemsRestore();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $model->getSyncFreightCloudData('b.id,b.freight,a.pur_number,a.supplier_code,a.buyer,a.created_at,a.is_drawback,a.is_push_to_k3cloud',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpBuyers = [];
    		foreach( $need_data as $value ){
    			$tmpBuyers[] = isset($value['buyer']) && $value['buyer'] ? $value['buyer'] : '';
    		}
    		$purchaserArr = $modelPo->getStaffCodeByUsers( array_unique($tmpBuyers) );
    		$staffCodeMap = array_column($purchaserArr, 'username','user_number');
    
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				$billNo = $value['pur_number'].'_'.str_pad($value['id'], 10, '0', STR_PAD_LEFT);
    				if ($midFreight->dataExist('id',$value['pur_number'],$billNo)){
    					$successList[] = $value['id'];
    					continue;
    				}
    					
    				$staffCode = in_array($value['buyer'], $staffCodeMap) ? array_search($value['buyer'], $staffCodeMap) : '';
    				if ( !$staffCode ){
    					$failsList[PurchaseOrderPayType::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    
    				if ( empty($value['created_at']) ){
    					$failsList[PurchaseOrderPayType::CLOUD_SYNCH_ERROR_2][] = $value['id'];
    					continue;
    				}
    
    				if($value['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
    					$orgId = '101';
    				} elseif($value['is_drawback'] == 2) {
    					$orgId = '102';
    				}
    				
    				//检查是否绑定合同
    				$isCompact = 0;
    				$compactInfo = $modelCompactItems->getCompactBindByPo('compact_number',$value['pur_number']);
    				if( isset($compactInfo['compact_number']) && $compactInfo['compact_number'] ){
    					$isCompact = 1;
    				}else{
    					$compactInfoRestore = $modelCompactItemsRestore->getCompactBindByPo('compact_number',$value['pur_number']);
    					if( isset($compactInfoRestore['compact_number']) && $compactInfoRestore['compact_number'] ){
    						$isCompact = 1;
    					}else{
    						//检查付款方式
    						$payOrderInfo = $modelPo->getPayOrderInfo('pay_type',$value['pur_number']);
    						if( in_array($payOrderInfo['pay_type'], [3,5]) ){ //银行转账、富有
    							$isCompact = 1;
    						}
    					}
    				}
    
    				$params[] = array(
    						'bill_no' => $billNo,
    						'purchase_no' => $value['pur_number'],
    						'provider_id' => $value['supplier_code'],
    						'purchase_id' => $staffCode,
    						'shipment' => $value['freight'] ? $value['freight'] : 0,
    						'dates' => $value['created_at'], //业务日期
    						'org_id' => $orgId,
    						'is_compact' => $isCompact,
    				);
    				$insertParams[] = $value['id'];
    			}
    
    			if(!empty($params)){
    				if ($midFreight->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				$model->updateFreightSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateFreightSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }

    /**
     * 运费修改后同步差值（同步已经推送到MID的，且修改过运费的）
     * @table MID采购运费
     * @offline http://test.yibainetwork.com/synchcloud/autosync/sync-freight-diff
     * @online http://caigou.yibainetwork.com/synchcloud/autosync/sync-freight-diff
     */
    public function actionSyncFreightDiff()
    {
    	exit('CLOSED...');
        $model = new PurchaseOrderPayType();
        $freight = new MidPurchaseFreight();
        $data = $model->getData3($this->limits['freight_diff']);
        if(empty($data)) {
            exit('没有数据了');
        }
        $success = $freight->saveRows2($data);
        if(!empty($success)) {
            $model::updateAll(['freight_push_to_k3cloud' => self::SYNC_SUCCESS, 'is_update_freight' => 0], ['in', 'id', $success]);
        }
    }
    
    /**
     * 同步采购运费差值 cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-freight-diff-cloud?limit=1&is_debug=1&po=AB090901
     **/
    public function actionSynchFreightDiffCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new PurchaseOrderPayType();
    	$modelPo = new PurchaseOrder();
    	$midFreight = new MidPurchaseFreight();
    	$modelCompactItems = new PurchaseCompactItems();
    	$modelCompactItemsRestore = new PurchaseCompactItemsRestore();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $model->getSyncFerightDiffCloudData('b.id,b.freight,a.pur_number,a.supplier_code,a.buyer,a.created_at,a.is_drawback',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpBuyers = [];
    		foreach( $need_data as $value ){
    			$tmpBuyers[] = isset($value['buyer']) && $value['buyer'] ? $value['buyer'] : '';
    		}
    		$purchaserArr = $modelPo->getStaffCodeByUsers( array_unique($tmpBuyers) );
    		$staffCodeMap = array_column($purchaserArr, 'username','user_number');
    
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				$synchedFreight = $midFreight->getSynchedFreightByPo($value['pur_number']);
    				$freightDiff = isset($synchedFreight['sum_freight']) && $synchedFreight['sum_freight'] ? bcsub($value['freight'], $synchedFreight['sum_freight'],3) : $value['freight'];
    				if( $freightDiff == 0 ){
    					$successList[] = $value['id'];
    					continue;
    				}
    					
    				$staffCode = in_array($value['buyer'], $staffCodeMap) ? array_search($value['buyer'], $staffCodeMap) : '';
    				if ( !$staffCode ){
    					$failsList[PurchaseOrderPayType::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    
    				if ( empty($value['created_at']) ){
    					$failsList[PurchaseOrderPayType::CLOUD_SYNCH_ERROR_2][] = $value['id'];
    					continue;
    				}
    
    				if($value['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
    					$orgId = '101';
    				} elseif($value['is_drawback'] == 2) {
    					$orgId = '102';
    				}
    				
    				//检查是否绑定合同
    				$isCompact = 0;
    				$compactInfo = $modelCompactItems->getCompactBindByPo('compact_number',$value['pur_number']);
    				if( isset($compactInfo['compact_number']) && $compactInfo['compact_number'] ){
    					$isCompact = 1;
    				}else{
    					$compactInfoRestore = $modelCompactItemsRestore->getCompactBindByPo('compact_number',$value['pur_number']);
    					if( isset($compactInfoRestore['compact_number']) && $compactInfoRestore['compact_number'] ){
    						$isCompact = 1;
    					}else{
    						//检查付款方式
    						$payOrderInfo = $modelPo->getPayOrderInfo('pay_type',$value['pur_number']);
    						if( in_array($payOrderInfo['pay_type'], [3,5]) ){ //银行转账、富有
    							$isCompact = 1;
    						}
    					}
    				}
    
    				$billNo = $value['pur_number'].'_'.str_pad($value['id'], 10, '0', STR_PAD_LEFT).'_'.substr(time(), -3, 3);
    				$params[] = array(
    						'bill_no' => $billNo,
    						'purchase_no' => $value['pur_number'],
    						'provider_id' => $value['supplier_code'],
    						'purchase_id' => $staffCode,
    						'shipment' => $freightDiff,
    						'dates' => $value['created_at'], //业务日期
    						'org_id' => $orgId,
    						'is_compact' => $isCompact,
    				);
    				$insertParams[] = $value['id'];
    			}
    			
    			if(!empty($params)){
    				if ($midFreight->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				$model->updateFreightDiffSynchCloud($successList,0,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateFreightDiffSynchCloud($failsVal,1,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    		exit('done');
    	}else{
    		exit('data is null');
    	}
    }

    /**
     * 同步退料
     * @table MID采购退料单
     * @offline http://test.yibainetwork.com/synchcloud/autosync/sync-returns
     * @online http://caigou.yibainetwork.com/synchcloud/autosync/sync-returns
     */
    public function actionSyncReturns()
    {
    	exit('CLOSED...');
        $model = new PurchaseOrderRefundQuantity();
        $modelReturns = new MidPurchaseReturns();
        $data = $model->getData($this->limits['returns']);
        $success = [];
        if(!empty($data['valid'])) {
            $transaction = $modelReturns->getDb()->beginTransaction();
            try {
                $success = $modelReturns->saveRows($data['valid']);
                if($success) {
                    $transaction->commit();
                    echo 'Success:'.count($success);
                } else {
                    echo 'Failure';
                    $transaction->rollBack();
                }
            } catch(\Exception $e) {
                $transaction->rollBack();
                Vhelper::dump($e->getMessage());
            }
        }
        if(!empty($success)) {
            $model::updateAll(['is_push_to_k3cloud' => self::SYNC_SUCCESS], ['in', 'id', $success]);
        }
        if(!empty($data['mistake'])) {
            $model::updateAll(['is_push_to_k3cloud' => self::SYNC_FAILURE], ['in', 'id', $data['mistake']]);
        }
    }
    
    /**
     * 同步采购退料 cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-return-cloud?limit=1&is_debug=1&po=AB090901
     **/
    public function actionSynchReturnCloud()
    {
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new PurchaseOrderRefundQuantity();
    	$purModel = new PurchaseOrder();
    	$midReturns = new MidPurchaseReturns();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $model->getSyncReturnCloudData('a.pur_number,a.sku,a.id,a.created_at,a.price,a.refund_qty,a.creator,a.requisition_number',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpBuyers = [];
    		foreach( $need_data as $value ){
    			$tmpBuyers[] = isset($value['creator']) && $value['creator'] ? $value['creator'] : '';
    		}
    		$purchaserArr = $purModel->getStaffCodeByUsers( array_unique($tmpBuyers) );
    		$staffCodeMap = array_column($purchaserArr, 'username','user_number');
    		
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				if ($midReturns->dataExist('id',$value['pur_number'],$value['pur_number'].'-'.$value['id'])){
    					$successList[] = $value['id'];
    					continue;
    				}
    					
    				$orderReceipt = isset($value->purchaseOrderReceipt)?$value->purchaseOrderReceipt:null;
    				
    				$note = isset($orderReceipt['review_notice']) && $orderReceipt['review_notice'] ? $orderReceipt['review_notice'] : '';
    				$staffCode = in_array($value['creator'], $staffCodeMap) ? array_search($value['creator'], $staffCodeMap) : '';
    				if (!$value['creator'] || !$staffCode ){
    					$failsList[PurchaseOrderRefundQuantity::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    				
    				$purchaseOrder = isset($value['purchaseOrder'])?$value['purchaseOrder']:null;
    				if( !$purchaseOrder ){
    					$failsList[PurchaseOrderRefundQuantity::CLOUD_SYNCH_ERROR_2][] = $value['id'];
    					continue;
    				}
    				
    				if( !$purchaseOrder['supplier_code'] ){
    					$failsList[PurchaseOrderRefundQuantity::CLOUD_SYNCH_ERROR_3][] = $value['id'];
    					continue;
    				}
    				
    				if( !$purchaseOrder['warehouse_code'] ){
	    				$failsList[PurchaseOrderRefundQuantity::CLOUD_SYNCH_ERROR_4][] = $value['id'];
	    				continue;
    				}
    
    				$isDrawback = $purchaseOrder['is_drawback'] ? $purchaseOrder['is_drawback'] : 1;
    				if($isDrawback == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
    					$orgId = '101';
    				} elseif($isDrawback == 2) {
    					$orgId = '102';
    				}
    
    				$params[] = array(
    						'bill_no' => $value['pur_number'].'-'.$value['id'],
    						'sku' => $value['sku'],
    						'provider_id' => $purchaseOrder['supplier_code'],
    						'dates' => $value['created_at'], //业务日期
    						'warehouse_code' => $purchaseOrder['warehouse_code'],
    						'purchase_id' => $staffCode,
    						'price' => $value['price'],
    						'purchase_no' => $value['pur_number'],
    						'retreating_qty' => $value['refund_qty'],
    						'note' => $note,
    						'org_id' => $orgId,
    				);
    				$insertParams[] = $value['id'];
    			}
    			
    			if(!empty($params)){
    				if ($midReturns->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				$model->updateReturnSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateReturnSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    
    /**
     * 同步采购员和供应商绑定关系到cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-buyer-map-supplier?limit=1
     **/
    public function actionSynchBuyerMapSupplier()
    {
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',100);
    	$supplier_code = Yii::$app->request->get('supplier_code');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new SupplierBuyer();
    	$cloudModel = new MidSupplierBuyer();
    	
    	$conditions = '';
    	if (!empty($supplier_code)){
    		$supplier_code = explode(',',$supplier_code);
    		$supplier_code = implode("','",$supplier_code);
    		$conditions .= " and supplier_code in('$supplier_code')";
    	}
    
    	$need_data = $model->getSyncBuyerSupplierData('*',$limit,$conditions);
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		try{
    			$params = $successList = $insertList = $failsList = array();
    			$date = date('Y-m-d H:i:s');
    
    			//组装K3所需要的字段信息
    			foreach ($need_data as $value){
    				if ($cloudModel->dataExist('supplier_code',$value['supplier_code'])){
    					$successList[] = $value['supplier_code'];
    					continue;
    				}
    
    				$params[] = array(
    						'supplier_code' => $value['supplier_code'],
    						'supplier_name' => $this->replaceSpecialChar($value['supplier_name']),
    						'type' => $value['type'],
    						'status' => $value['status'],
    						'buyer' => $value['buyer'],
    						'add_time' => $date,
    				);
    			}
    
    			if(!empty($params)){
    				//写入K3汇总表中
    				if ($cloudModel->batchInsertData(array_keys($params[0]), $params)) {
    					foreach ($params as $value) {
    						$successList[] = $value['supplier_code'];
    					}
    				} else {
    					foreach ($params as $value) {
    						if (Yii::$app->db->createCommand()->insert(MidSupplierBuyer::tableName(), $value)) {
    							$successList[] = $value['supplier_code'];
    						}
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				//根据ID更新分类同步成功
    				$model->updateBuyerSupplierMapSynced($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    		}catch (Exception $e){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    /**
     * 更新采购员和供应商绑定关系到cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/update-buyer-map-supplier?limit=1
     */
    public function actionUpdateBuyerMapSupplier(){
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步100条
    	$limit = Yii::$app->request->get('limit',100);
    	$supplier_code = Yii::$app->request->get('supplier_code');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new SupplierBuyer();
    	$cloudModel = new MidSupplierBuyer();
    	
    	$conditions = '';
    	if (!empty($supplier_code)){
    		$supplier_code = explode(',',$supplier_code);
    		$supplier_code = implode("','",$supplier_code);
    		$conditions .= " and supplier_code in('$supplier_code')";
    	}    
    	$need_data = $model->getUpdateBuyerSupplierMap('*',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = array();
    
    		$date = date('Y-m-d H:i:s');
    		try{
    
    			//组装K3所需要的字段信息
    			foreach ($need_data as $value){
    				$params = array(
    						'supplier_code' => $value['supplier_code'],
    						'supplier_name' => $this->replaceSpecialChar($value['supplier_name']),
    						'type' => $value['type'],
    						'status' => $value['status'],
    						'buyer' => $value['buyer'],
    						'update_time' => $date,
    				);
    
    				if($cloudModel->updateBuyerSupplierMap($params)){
    					$successList[] = $value['supplier_code'];
    				}
    			}
    
    			if (count($successList) > 0){
    				//更新分类同步成功
    				$model->updateBuyerSupplierMapSynced($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>更新成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    		}catch (Exception $e){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    /**
     * 同步供应商银行信息到结汇系统
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-bank-exc?limit=1
     **/
    public function actionSynchBankExc()
    {
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',100);
    	$supplier_code = Yii::$app->request->get('supplier_code');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new SupplierPaymentAccount();
    	$excModel = new ExcSupplierBank();
    	//$excCityCode = new ExcCityCode();
    	//$excBankCode = new ExcBankCode();
    	 
    	$conditions = '';
    	if (!empty($supplier_code)){
    		$supplier_code = explode(',',$supplier_code);
    		$supplier_code = implode("','",$supplier_code);
    		$conditions .= " and supplier_code in('$supplier_code')";
    	}
    
    	$need_data = $model->getSyncSupplierBankData('*',$limit,$conditions);
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		try{
    			$params = $successList = $insertList = $failsList = $paramsMap = array();
    			$date = date('Y-m-d H:i:s');
    			
    			/* $tmpCityCode = $tmpBankCode = [];
    			foreach( $need_data as $value ){
    				$tmpCityCode[] = isset($value['city_code']) && $value['city_code'] ? $value['city_code'] : '';
    				$tmpBankCode[] = isset($value['payment_platform_bank']) && $value['payment_platform_bank'] ? $value['payment_platform_bank'] : '';
    			}
    			
    			$excCityCodeList = $excCityCode->getCityCodeData('city,city_code',array_unique($tmpCityCode));
    			$excBankCodeList = $excBankCode->getBankCodeData('bank,bank_code',array_unique($tmpBankCode));
    			
    			$excCityCodeMap = array_column($excCityCodeList, 'city','city_code');
    			$excBankCodeMap = array_column($excBankCodeList, 'bank','bank_code'); */
    			
    			//组装K3所需要的字段信息
    			foreach ($need_data as $value){
    				if ($excModel->dataExist('supplier_code',$value['supplier_code'],$value['account'])){
    					$successList[] = $value['pay_id'];
    					continue;
    				}
    				
    				if( !isset($value['city_code']) || empty($value['city_code']) ){
    					$failsList[SupplierPaymentAccount::EXC_SYNCH_ERROR_1][] = $value['pay_id'];
    					continue;
    				}
    				
    				if( !isset($value['payment_platform_bank']) || empty($value['payment_platform_bank']) ){
    					$failsList[SupplierPaymentAccount::EXC_SYNCH_ERROR_2][] = $value['pay_id'];
    					continue;
    				}
    				
    				if( !isset($value['account_name']) || empty($value['account_name']) ){
    					$failsList[SupplierPaymentAccount::EXC_SYNCH_ERROR_3][] = $value['pay_id'];
    					continue;
    				}
    				
    				if( !isset($value['id_number']) || empty($value['id_number']) ){
    					$failsList[SupplierPaymentAccount::EXC_SYNCH_ERROR_4][] = $value['pay_id'];
    					continue;
    				}
    
    				$params[] = array(
    						'account_purpose' => 2, //转入账户
    						'account_type' => $value['account_type'], //1对公 2对私
    						'account_name' => $value['account_name'], //账号名
    						'country' => '中国', //国家
    						'country_code' => 'CHN', //国家简码
    						'city' => '', //城市名称
    						'city_code' => $value['city_code'], //城市编码：对应结汇系统城市编码
    						'bank_name' => '', //银行名称
    						'bank_code' => $value['payment_platform_bank'], //银行code:pms系统维护，对应结汇系统银行编码
    						'account_id' => $value['id_number'], //开户人证件号
    						'bank_account' => $value['account'], //开户账号
    						'create_time' => $date, //创建时间
    						'is_del' => isset($value['status']) && $value['status'] == 1 ? 0 : 1, //0 未删除 1已删除
    						'create_user' => '', //创建人
    						'currency' => isset($value['currency']) && $value['currency'] ? $value['currency'] : 'CNY', //@todo 币种 pms待维护
    						'supplier_code' => $value['supplier_code'], //pms供应商编码
    				);
    				$paramsMap[] = ['pay_id' => $value['pay_id']];
    			}
    			
    			if(!empty($params)){
    				if ($excModel->batchInsertData( array_keys($params[0]), $params)) {
    					foreach ($paramsMap as $value) {
    						$successList[] = $value['pay_id'];
    					}
    				} else {
    					/* foreach ($params as $value) {
    						if (Yii::$app->db->createCommand()->insert(ExcSupplierBank::tableName(), $value)) {
    							$successList[] = $value['pay_id'];
    						}
    					} */
    				}
    			}
    
    			if (count($successList) > 0){
    				//根据ID更新分类同步成功
    				$model->updateSupplierBankSynchExc($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    			
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateSupplierBankSynchExc($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    		}catch (Exception $e){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    /**
     * 更新供应商银行信息到结汇系统
     * http://www.ebuy_pms.com/synchcloud/autosync/update-bank-exc?limit=1
     */
    public function actionUpdateBankExc(){
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步100条
    	$limit = Yii::$app->request->get('limit',100);
    	$supplier_code = Yii::$app->request->get('supplier_code');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new SupplierPaymentAccount();
    	$excModel = new ExcSupplierBank();
    	//$excCityCode = new ExcCityCode();
    	//$excBankCode = new ExcBankCode();
    	
    	$conditions = '';
    	if (!empty($supplier_code)){
    		$supplier_code = explode(',',$supplier_code);
    		$supplier_code = implode("','",$supplier_code);
    		$conditions .= " and supplier_code in('$supplier_code')";
    	}
    
    	$need_data = $model->getUpdateBankExc('*',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = array();
    		$date = date('Y-m-d H:i:s');
    		
    		/* $tmpCityCode = $tmpBankCode = [];
    		foreach( $need_data as $value ){
    			$tmpCityCode[] = isset($value['city_code']) && $value['city_code'] ? $value['city_code'] : '';
    			$tmpBankCode[] = isset($value['payment_platform_bank']) && $value['payment_platform_bank'] ? $value['payment_platform_bank'] : '';
    		}
    		 
    		$excCityCodeList = $excCityCode->getCityCodeData('city,city_code',array_unique($tmpCityCode));
    		$excBankCodeList = $excBankCode->getBankCodeData('bank,bank_code',array_unique($tmpBankCode));
    		 
    		$excCityCodeMap = array_column($excCityCodeList, 'city','city_code');
    		$excBankCodeMap = array_column($excBankCodeList, 'bank','bank_code'); */
    		
    		try{
    			//组装K3所需要的字段信息
    			foreach ($need_data as $value){
    				if( !isset($value['city_code']) || empty($value['city_code']) ){
    					$failsList[SupplierPaymentAccount::EXC_SYNCH_ERROR_1][] = $value['pay_id'];
    					continue;
    				}
    				
    				if( !isset($value['payment_platform_bank']) || empty($value['payment_platform_bank']) ){
    					$failsList[SupplierPaymentAccount::EXC_SYNCH_ERROR_2][] = $value['pay_id'];
    					continue;
    				}
    				
    				if( !isset($value['account_name']) || empty($value['account_name']) ){
    					$failsList[SupplierPaymentAccount::EXC_SYNCH_ERROR_3][] = $value['pay_id'];
    					continue;
    				}
    				
    				if( !isset($value['id_number']) || empty($value['id_number']) ){
    					$failsList[SupplierPaymentAccount::EXC_SYNCH_ERROR_4][] = $value['pay_id'];
    					continue;
    				}
    				
    				$params = array(
    						'account_purpose' => 2, //转入账户
    						'account_type' => $value['account_type'], //1对公 2对私
    						'account_name' => $value['account_name'],
    						'country' => '中国',
    						'country_code' => 'CHN',
    						'city' => '',
    						'city_code' => $value['city_code'], //@todo 结汇系统校验
    						'bank_name' => '',
    						'bank_code' => $value['payment_platform_bank'], //@todo pms要维护 结汇系统校验
    						'account_id' => $value['id_number'], //开户人证件号
    						'bank_account' => $value['account'], //开户账号
    						'is_del' => isset($value['status']) && $value['status'] == 1 ? 0 : 1, //0 未删除 1已删除
    						'update_time' => empty($value['modify_time']) || $value['modify_time'] == '0000-00-00 00:00:00' ? $date : $value['modify_time'],
    						//'payee' => '', //@todo 收款人？
    						//'update_user' => '',
    						'currency' => isset($value['currency']) && $value['currency'] ? $value['currency'] : 'CNY', //@todo pms待维护
    						'supplier_code' => $value['supplier_code'],
    				);
    				
    				//检查银行账号是否存在
    				$existInfo = $excModel->dataExist('supplier_code',$value['supplier_code'],$value['account']);
    				if( $existInfo ){
    					if($excModel->updateSupplierBankExc($params)){
    						$successList[] = $value['pay_id'];
    					}
    				}else{ //银行账号不存在，但是供应商之前同步过银行信息，则停用之前的银行信息，新增新的银行信息
    					$stopData = ['supplier_code' => $value['supplier_code'],'is_del'=>1,'update_time'=>$date];
    					if($excModel->stopSupplierBankExc($stopData)){ //停用
    						//插入新的银行信息
    						unset($params['update_time']);
    						$params['create_time'] = $date;
    						$params['create_user'] = '';
    						if($excModel->batchInsertData( array_keys($params), [$params])) {
    							$successList[] = $value['pay_id'];
    						}
    						
    					}
    				}
    
    			}
    
    			if (count($successList) > 0){
    				//更新分类同步成功
    				$model->updateSupplierBankSynchExc($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>更新成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    			
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateSupplierBankSynchExc($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $e){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    /**
     * 同步供应商信息到结汇系统
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-supplier-exc?limit=1
     **/
    public function actionSynchSupplierExc()
    {
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',100);
    	$supplier_code = Yii::$app->request->get('supplier_code');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new Supplier();
    	$excModel = new ExcSupplier();
    	$purchaseOrderModel = new PurchaseOrder();
    	$supplerPayAcctModel = new SupplierPaymentAccount();
    
    	$conditions = '';
    	if (!empty($supplier_code)){
    		$supplier_code = explode(',',$supplier_code);
    		$supplier_code = implode("','",$supplier_code);
    		$conditions .= " and a.supplier_code in('$supplier_code')";
    	}
    
    	$need_data = $model->getSyncSupplierExcData('a.*',$limit,$conditions);
    	
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		try{
    			$params = $successList = $insertList = $failsList = array();
    			$date = date('Y-m-d H:i:s');
    			 
    			//组装K3所需要的字段信息
    			foreach ($need_data as $value){
    				if ($excModel->dataExist('supplier_code',$value['supplier_code'])){
    					$successList[] = $value['supplier_code'];
    					continue;
    				}
    				
    				/* $retPo = $purchaseOrderModel->checkPoTypeOfCompact('pur_number',$value['supplier_code']);
    				if( !isset($retPo['pur_number']) || empty($retPo['pur_number']) ){
    					$failsList[Supplier::EXC_SYNCH_ERROR_4][] = $value['supplier_code'];
    					continue;
    				} */
    				
    				//检查付款方式
    				$payOrderInfo = $purchaseOrderModel->getPayOrderInfoBySupplier('pur_number',$value['supplier_code']);
    				if( isset($payOrderInfo['pur_number']) && $payOrderInfo['pur_number'] ){ //银行转账、富有
    					$isCompact = 1;
    				}else{
    					$failsList[Supplier::EXC_SYNCH_ERROR_4][] = $value['supplier_code'];
    					continue;
    				}
    				
    				$retPayAcct = $supplerPayAcctModel->getPaymentAccountExc('account_name,id_number',$value['supplier_code']);
    				
    				if( !isset($retPayAcct['account_name']) || empty($retPayAcct['account_name']) ){
    					$failsList[Supplier::EXC_SYNCH_ERROR_1][] = $value['supplier_code'];
    					continue;
    				}
    				
    				if( !isset($retPayAcct['id_number']) || empty($retPayAcct['id_number']) ){
    					$failsList[Supplier::EXC_SYNCH_ERROR_2][] = $value['supplier_code'];
    					continue;
    				}
    				
    				if( !isset($value['payment_method']) || empty($value['payment_method']) ){
    					$failsList[Supplier::EXC_SYNCH_ERROR_3][] = $value['supplier_code'];
    					continue;
    				}
    				
    				$settlement = $this->getSettlementTypeMap($value['supplier_settlement']);
    				if( !$settlement ){
    					$failsList[Supplier::EXC_SYNCH_ERROR_5][] = $value['supplier_code'];
    					continue;
    				}
    
    				$params[] = array(
    						'supplier_code' => $value['supplier_code'], //pms供应商编码
    						'supplier_name' => $this->replaceSpecialChar($value['supplier_name']), //pms供应商名称
    						'supplier_type' => 1, //供应商类型   1 合同供应商   2 网采供应商
    						'supplier_address' => isset($value['supplier_address']) && $value['supplier_address'] ? $this->replaceSpecialChar($value['supplier_address']) : '', //供应商地址
    						'country_code' => 'CHN', //国家简码
    						//'post_code' => '',
    						//'contacts' => '',
    						//'mobile' => '',
    						//'busines_place_code' => '',//经营场所代码
    						//'company_type_code' => '',//企业类型代码
    						//'busines_property_code' => '',//行业属性代码
    						//'economic_type_code' => '',//经济类型代码
    						'document_type' => 1, //证件类型 1身份证；2组织机构代码  @todo 待确认是否为身份证
    						//'is_bc_pay_rec_company' => '',//是否BC类收付汇企业  0.是 1.否
    						//'is_special_economic_zone_company' => '',//是否特殊经济区内企业  0.是 1.否
    						'applicant' => $retPayAcct['account_name'], //申报人名称
    						'document_no' => $retPayAcct['id_number'], //身份证件号/组织机构代码
    						'payment_method' => $settlement, //支付方式  1-货到付款 2-款到发货 3-周结 4-半月结 5-月结 6-两月结 7-30%订金+70%尾款半月结 8-30%订金+70%尾款月结 9-货到付款(15天付清) 10-30%订金+70%尾款货到付款 11-30%订金+尾款次月15日结
    						'create_user' => '', //创建人
    						'create_time' => isset($value['create_time']) && $value['create_time'] ? date('Y-m-d H:i:s',$value['create_time']) : '0000-00-00 00:00:00', //创建时间
    						'is_del' => $value['status'] == 1?0:1, //是否删除    0未删除  1删除
    						//'corporation' => '',//是否法人  1法人   2非法人
    				);
    			}
    			//print_r($params);exit;
    
    			if(!empty($params)){
    				if ($excModel->batchInsertData( array_keys($params[0]), $params)) {
    					foreach ($params as $value) {
    						$successList[] = $value['supplier_code'];
    					}
    				} else {
    					foreach ($params as $value) {
    					 	if (Yii::$app->db->createCommand()->insert(ExcSupplier::tableName(), $value)) {
	    					 	$successList[] = $value['supplier_code'];
	    					 }
    					 }
    				}
    			}
    
    			if (count($successList) > 0){
    				//根据ID更新分类同步成功
    				$model->updateSupplierSynchExc($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    			 
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateSupplierSynchExc($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    		}catch (Exception $e){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    /**
     * 更新供应商信息到结汇系统
     * http://www.ebuy_pms.com/synchcloud/autosync/update-supplier-exc?limit=1
     */
    public function actionUpdateSupplierExc(){
    	ini_set('memory_limit', '32M');
    	set_time_limit('3600');
    
    	//默认每次同步100条
    	$limit = Yii::$app->request->get('limit',100);
    	$supplier_code = Yii::$app->request->get('supplier_code');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new Supplier();
    	$excModel = new ExcSupplier();
    	$purchaseOrderModel = new PurchaseOrder();
    	$supplerPayAcctModel = new SupplierPaymentAccount();
    
    	$conditions = '';
    	if (!empty($supplier_code)){
    		$supplier_code = explode(',',$supplier_code);
    		$supplier_code = implode("','",$supplier_code);
    		$conditions .= " and supplier_code in('$supplier_code')";
    	}
    
    	$need_data = $model->getUpdateSupplierExc('a.*',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = array();
    		$date = date('Y-m-d H:i:s');
    
    		try{
    			foreach ($need_data as $value){
    				/* $retPo = $purchaseOrderModel->checkPoTypeOfCompact('pur_number',$value['supplier_code']);
    				if( !isset($retPo['pur_number']) || empty($retPo['pur_number']) ){
    					$failsList[Supplier::EXC_SYNCH_ERROR_4][] = $value['supplier_code'];
    					continue;
    				} */
    				
    				//检查付款方式
    				$payOrderInfo = $purchaseOrderModel->getPayOrderInfoBySupplier('pur_number',$value['supplier_code']);
    				if( isset($payOrderInfo['pur_number']) && $payOrderInfo['pur_number'] ){ //银行转账、富有
    					$isCompact = 1;
    				}else{
    					$failsList[Supplier::EXC_SYNCH_ERROR_4][] = $value['supplier_code'];
    					continue;
    				}
    				
    				$retPayAcct = $supplerPayAcctModel->getPaymentAccountExc('account_name,id_number',$value['supplier_code']);
    				
    				if( !isset($retPayAcct['account_name']) || empty($retPayAcct['account_name']) ){
    					$failsList[Supplier::EXC_SYNCH_ERROR_1][] = $value['supplier_code'];
    					continue;
    				}
    				
    				if( !isset($retPayAcct['id_number']) || empty($retPayAcct['id_number']) ){
    					$failsList[Supplier::EXC_SYNCH_ERROR_2][] = $value['supplier_code'];
    					continue;
    				}
    				
    				if( !isset($value['payment_method']) || empty($value['payment_method']) ){
    					$failsList[Supplier::EXC_SYNCH_ERROR_3][] = $value['supplier_code'];
    					continue;
    				}
    				
    				$settlement = $this->getSettlementTypeMap($value['supplier_settlement']);
    				if( !$settlement ){
    					$failsList[Supplier::EXC_SYNCH_ERROR_5][] = $value['supplier_code'];
    					continue;
    				}
    				
    				$params = array(
    						'supplier_code' => $value['supplier_code'],
    						'supplier_name' => $this->replaceSpecialChar($value['supplier_name']),
    						//'supplier_type' => 1, //供应商类型   1 合同供应商   2 网采供应商
    						'supplier_address' => isset($value['supplier_address']) && $value['supplier_address'] ? $this->replaceSpecialChar($value['supplier_address']) : '',
    						//'country_code' => 'CHN',
    						//'post_code' => '',
    						//'contacts' => '',
    						//'mobile' => '',
    						//'busines_place_code' => '',//经营场所代码
    						//'company_type_code' => '',//企业类型代码
    						//'busines_property_code' => '',//行业属性代码
    						//'economic_type_code' => '',//经济类型代码
    						//'document_type' => 1, //证件类型 1身份证；2组织机构代码  @todo 待确认是否为身份证
    						//'is_bc_pay_rec_company' => '',//是否BC类收付汇企业  0.是 1.否
    						//'is_special_economic_zone_company' => '',//是否特殊经济区内企业  0.是 1.否
    						'applicant' => $retPayAcct['account_name'], //申报人名称
    						'document_no' => $retPayAcct['id_number'], //身份证件号/组织机构代码
    						'payment_method' => $settlement, //支付方式  1-货到付款 2-款到发货 3-周结 4-半月结 5-月结 6-两月结 7-30%订金+70%尾款半月结 8-30%订金+70%尾款月结 9-货到付款(15天付清) 10-30%订金+70%尾款货到付款 11-30%订金+尾款次月15日结
    						//'modify_user' => '',
    						'modify_time' => isset($value['modify_time']) && $value['modify_time'] ? $value['modify_time'] : '0000-00-00 00:00:00',
    						'is_del' => $value['status'] == 1?0:1, //是否删除    0未删除  1删除
    						//'corporation' => '',//是否法人  1法人   2非法人
    				);
    				if($excModel->updateSupplierExc($params)){
    					$successList[] = $value['supplier_code'];
    				}
    			}
    
    			if (count($successList) > 0){
    				//更新分类同步成功
    				$model->updateSupplierSynchExc($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>更新成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    			
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$model->updateSupplierSynchExc($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $e){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    
    /**
     * 更新采购入库单的开票点和不含税价到汇总系统 - cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-instock-tax-cloud?limit=100&is_debug=1&po=PO261015
     **/
    public function actionSynchInstockTaxCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',300);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    	 
    	$modelPo = new PurchaseOrder();
    	$modelPoDetail = new PurchaseOrderItems();
    	$midInstockModel = new MidPurchaseIn();
    	$midInDetailModel = new MidPurchaseInDetail();
    	$modelPoRestore = new PurchaseOrderRestore();
    	$modelPoTaxes = new PurchaseOrderTaxes();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.purchase_no in('$po')";
    	}
    
    	$need_data = $midInstockModel->getSyncInStockTaxData('b.id,a.purchase_no,b.sku',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = array();
    		$date = date('Y-m-d H:i:s');
    		
    		$tmpPoArr =  array_column($need_data, 'purchase_no');
    		
    		//查询采购单明细
    		$tmpPoDetailArr = $modelPoDetail->getPoDetailData('a.pur_number,a.sku,a.base_price,a.price,a.pur_ticketed_point,b.is_drawback',array_unique($tmpPoArr));
    		//查询采购单票点明细
    		$tmpPoDetailRestoreArr = $modelPoRestore->getPurTicketPointList( 'pur_number,sku,base_price,price,pur_ticketed_point',array_unique($tmpPoArr) );
    		//查询采购单开票 -- 海外仓
    		$tmpPoDetailAbTaxesArr = $modelPoTaxes->getAbPurTicketPointList( 'pur_number,sku,taxes',array_unique($tmpPoArr) );
    		if($is_debug){
    			echo "<pre>同步参数1.1:<br>";
    			print_r($tmpPoDetailArr);
    			echo "</pre>";
    			 
    			echo "<pre>同步参数1.2:<br>";
    			print_r($tmpPoDetailRestoreArr);
    			echo "</pre>";
    			
    			echo "<pre>同步参数1.3:<br>";
    			print_r($tmpPoDetailAbTaxesArr);
    			echo "</pre>";
    		}
    		
    		$tmpMapArr = [];
    		if($tmpPoDetailArr){
    			foreach( $tmpPoDetailArr as $value ){
    				$tmpMapArr[$value['pur_number']][$value['sku']]['price'] = $value['price'];
    				$tmpMapArr[$value['pur_number']][$value['sku']]['base_price'] = $value['base_price'];
    				$tmpMapArr[$value['pur_number']][$value['sku']]['pur_ticketed_point'] = $value['pur_ticketed_point'];
    				$tmpMapArr[$value['pur_number']]['is_drawback'] = $value['is_drawback'];
    			}
    		}
    		
    		$tmpRestoreMapArr = [];
    		if($tmpPoDetailRestoreArr){
    			foreach( $tmpPoDetailRestoreArr as $value ){
    				$tmpRestoreMapArr[$value['pur_number']][$value['sku']]['price'] = $value['price'];
    				$tmpRestoreMapArr[$value['pur_number']][$value['sku']]['base_price'] = $value['base_price'];
    				$tmpRestoreMapArr[$value['pur_number']][$value['sku']]['pur_ticketed_point'] = $value['pur_ticketed_point'];
    			}
    		}
    		
    		$tmpAbMapArr = [];
    		if($tmpPoDetailAbTaxesArr){
    			foreach( $tmpPoDetailAbTaxesArr as $value ){
    				$tmpAbMapArr[$value['pur_number']][$value['sku']]['pur_ticketed_point'] = $value['taxes'];
    			}
    		}
    		
    		if($is_debug){
    			echo "<pre>同步参数2.1:<br>";
    			print_r($tmpMapArr);
    			echo "</pre>";
    		}
    		
    		if($is_debug){
    			echo "<pre>同步参数2.2:<br>";
    			print_r($tmpRestoreMapArr);
    			echo "</pre>";
    		}
    		
    		if($is_debug){
    			echo "<pre>同步参数2.3:<br>";
    			print_r($tmpAbMapArr);
    			echo "</pre>";
    		}
    		
    		//组装K3所需要的字段信息
    		foreach ($need_data as $value){
    			$updateData = [];
    			try{
    				$currItem = isset($tmpMapArr[$value['purchase_no']][$value['sku']]) ? $tmpMapArr[$value['purchase_no']][$value['sku']] : '';
    				$currItemRestore = isset($tmpRestoreMapArr[$value['purchase_no']][$value['sku']]) ? $tmpRestoreMapArr[$value['purchase_no']][$value['sku']] : '';
    				$currItemAbTaxes = isset($tmpAbMapArr[$value['purchase_no']][$value['sku']]) ? $tmpAbMapArr[$value['purchase_no']][$value['sku']] : '';
    				
    				$currItemAbTaxes['pur_ticketed_point'] = isset($currItemAbTaxes['pur_ticketed_point']) ? $currItemAbTaxes['pur_ticketed_point'] : '';
    				
    				//当前采购单明细开票点
    				$currTicketPoint = isset($currItem['pur_ticketed_point'])?$currItem['pur_ticketed_point']:0;
    				$currPrice = isset($currItem['price'])?$currItem['price']:0;
    				$currBasePrice = isset($currItem['base_price'])?$currItem['base_price']:0;
    				
    				//财务维护的开票点
    				$currTicketPointRestore = isset($currItemRestore['pur_ticketed_point'])?$currItemRestore['pur_ticketed_point']:0;
    				$currPriceRestore = isset($currItemRestore['price'])?$currItemRestore['price']:0;
    				$currBasePriceRestore = isset($currItemRestore['base_price'])?$currItemRestore['base_price']:0;
    				
    				if( $currTicketPointRestore && $currBasePriceRestore && $currPriceRestore ){ //如果存在财务单独维护的开票点
    					$currTicketPoint = $currTicketPointRestore*100;
    					$currPrice = $currPriceRestore;
    					$currBasePrice = $currBasePriceRestore;
    				}elseif( $currItemAbTaxes['pur_ticketed_point'] && $tmpMapArr[$value['purchase_no']]['is_drawback'] == 2 ){ //有税点，并且采购单退税
    					if( $currPrice == $currBasePrice || empty($currBasePrice) ){ //采购单明细 含税价和不含税价相等 或者 不含税价为0
    						$currBasePrice = $currPrice;
    						$currTicketPoint = $currItemAbTaxes['pur_ticketed_point'];
    						$currPrice = round($currPrice*(1+$currTicketPoint*0.01),2);
    					}
    				}
    				
    				if(!empty($tmpMapArr)){
    					$updateData = [
    							'id' => $value['id'],
    							'ticketed_point' => $currTicketPoint,
    							'base_price' => $currBasePrice,
    							'price' => $currPrice,
    					];
    					if($is_debug){
    						echo "<pre>同步参数3:<br>";
    						print_r($updateData);
    						echo "</pre>";
    					}
    					$rlt = $midInDetailModel->saveRows($updateData);
    				}
    			}catch(Exception $ex){
    				echo '<br/> errormsg: '.$ex->getMessage();
    			}
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    /**
     * 更新采购单的开票点和不含税价到汇总系统 - cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-po-tax-cloud?limit=100&is_debug=1&po=PO261015
     **/
    public function actionSynchPoTaxCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',300);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$modelPo = new PurchaseOrder();
    	$modelPoDetail = new PurchaseOrderItems();
    	$modelMIdPoDetail = new MidPurchaseOrderDetail();
    	$modelPoRestore = new PurchaseOrderRestore();
    	$modelPoTaxes = new PurchaseOrderTaxes();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.purchase_no in('$po')";
    	}
    
    	$need_data = $modelMIdPoDetail->getSyncPoTaxData('a.id,a.purchase_no,a.sku',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = array();
    		$date = date('Y-m-d H:i:s');
    
    		$tmpPoArr =  array_column($need_data, 'purchase_no');
    
    		//查询采购单明细
    		$tmpPoDetailArr = $modelPoDetail->getPoDetailData('a.pur_number,a.sku,a.base_price,a.price,a.pur_ticketed_point,b.is_drawback',array_unique($tmpPoArr));
    		//查询采购单票点明细
    		$tmpPoDetailRestoreArr = $modelPoRestore->getPurTicketPointList( 'pur_number,sku,base_price,price,pur_ticketed_point',array_unique($tmpPoArr) );
    		//查询采购单开票 -- 海外仓
    		$tmpPoDetailAbTaxesArr = $modelPoTaxes->getAbPurTicketPointList( 'pur_number,sku,taxes',array_unique($tmpPoArr) );
    		if($is_debug){
    			echo "<pre>同步参数1.1:<br>";
    			print_r($tmpPoDetailArr);
    			echo "</pre>";
    			
    			echo "<pre>同步参数1.2:<br>";
    			print_r($tmpPoDetailRestoreArr);
    			echo "</pre>";
    			
    			echo "<pre>同步参数1.3:<br>";
    			print_r($tmpPoDetailAbTaxesArr);
    			echo "</pre>";
    		}
    
    		$tmpMapArr = [];
    		if($tmpPoDetailArr){
    			foreach( $tmpPoDetailArr as $value ){
    				$tmpMapArr[$value['pur_number']][$value['sku']]['price'] = $value['price'];
    				$tmpMapArr[$value['pur_number']][$value['sku']]['base_price'] = $value['base_price'];
    				$tmpMapArr[$value['pur_number']][$value['sku']]['pur_ticketed_point'] = $value['pur_ticketed_point'];
    				$tmpMapArr[$value['pur_number']]['is_drawback'] = $value['is_drawback'];
    			}
    		}
    		
    		$tmpRestoreMapArr = [];
    		if($tmpPoDetailRestoreArr){
    			foreach( $tmpPoDetailRestoreArr as $value ){
    				$tmpRestoreMapArr[$value['pur_number']][$value['sku']]['price'] = $value['price'];
    				$tmpRestoreMapArr[$value['pur_number']][$value['sku']]['base_price'] = $value['base_price'];
    				$tmpRestoreMapArr[$value['pur_number']][$value['sku']]['pur_ticketed_point'] = $value['pur_ticketed_point'];
    			}
    		}
    		
    		$tmpAbMapArr = [];
    		if($tmpPoDetailAbTaxesArr){
    			foreach( $tmpPoDetailAbTaxesArr as $value ){
    				$tmpAbMapArr[$value['pur_number']][$value['sku']]['pur_ticketed_point'] = $value['taxes'];
    			}
    		}
    
    		if($is_debug){
    			echo "<pre>同步参数2.1:<br>";
    			print_r($tmpMapArr);
    			echo "</pre>";
    		}
    		
    		if($is_debug){
    			echo "<pre>同步参数2.2:<br>";
    			print_r($tmpRestoreMapArr);
    			echo "</pre>";
    		}
    		
    		if($is_debug){
    			echo "<pre>同步参数2.3:<br>";
    			print_r($tmpAbMapArr);
    			echo "</pre>";
    		}
    
    		//组装K3所需要的字段信息
    		foreach ($need_data as $value){
    			$updateData = [];
    			try{
    				$currItem = isset($tmpMapArr[$value['purchase_no']][$value['sku']]) ? $tmpMapArr[$value['purchase_no']][$value['sku']] : '';
    				$currItemRestore = isset($tmpRestoreMapArr[$value['purchase_no']][$value['sku']]) ? $tmpRestoreMapArr[$value['purchase_no']][$value['sku']] : '';
    				$currItemAbTaxes = isset($tmpAbMapArr[$value['purchase_no']][$value['sku']]) ? $tmpAbMapArr[$value['purchase_no']][$value['sku']] : '';
    				
    				$currItemAbTaxes['pur_ticketed_point'] = isset($currItemAbTaxes['pur_ticketed_point'])?$currItemAbTaxes['pur_ticketed_point']:'';
    				//当前采购单明细开票点
    				$currTicketPoint = isset($currItem['pur_ticketed_point'])?$currItem['pur_ticketed_point']:0;
    				$currPrice = isset($currItem['price'])?$currItem['price']:0;
    				$currBasePrice = isset($currItem['base_price'])?$currItem['base_price']:0;
    				
    				//财务维护的开票点
    				$currTicketPointRestore = isset($currItemRestore['pur_ticketed_point'])?$currItemRestore['pur_ticketed_point']:0;
    				$currPriceRestore = isset($currItemRestore['price'])?$currItemRestore['price']:0;
    				$currBasePriceRestore = isset($currItemRestore['base_price'])?$currItemRestore['base_price']:0;
    				
    				if( $currTicketPointRestore && $currBasePriceRestore && $currPriceRestore ){ //如果存在财务单独维护的开票点
    					$currTicketPoint = $currTicketPointRestore * 100;
    					$currPrice = $currPriceRestore;
    					$currBasePrice = $currBasePriceRestore;
    				}elseif( $currItemAbTaxes['pur_ticketed_point'] && $tmpMapArr[$value['purchase_no']]['is_drawback'] == 2 ){ //有税点，并且采购单退税
    					if( $currPrice == $currBasePrice || empty($currBasePrice) ){ //采购单明细 含税价和不含税价相等 或者 不含税价为0
    						$currBasePrice = $currPrice;
    						$currTicketPoint = $currItemAbTaxes['pur_ticketed_point'];
    						$currPrice = round($currPrice*(1+$currTicketPoint*0.01),2);
    					}
    				}
    				
    				if(!empty($tmpMapArr)){
    					$updateData = [
    							'id' => $value['id'],
    							'ticketed_point' => $currTicketPoint,
    							'price' => $currPrice,
    							'base_price' => $currBasePrice,
    					];
    					if($is_debug){
    						echo "<pre>同步参数3:<br>";
    						print_r($updateData);
    						echo "</pre>";
    					}
    					$rlt = $modelMIdPoDetail->updateRows($updateData);
    				}
    			}catch(Exception $ex){
    				echo '<br/> errormsg: '.$ex->getMessage();
    			}
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    
    /**
     * 更新采购入库单的组织id到汇总系统 - cloud
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-instock-org-cloud?limit=100&is_debug=1&po=PO261015
     **/
    public function actionSynchInstockOrgCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',300);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$modelPo = new PurchaseOrder();
    	$midInstockModel = new MidPurchaseIn();
    	$modelPoRestore = new PurchaseOrderRestore();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.purchase_no in('$po')";
    	}
    
    	$need_data = $midInstockModel->getSyncInStockOrgData('a.id,a.purchase_no',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = array();
    		$date = date('Y-m-d H:i:s');
    
    		$tmpPoArr =  array_column($need_data, 'purchase_no');
    
    		//查询采购单
    		$tmpPoArr = $modelPo->getPurchaseByPo( 'pur_number,is_drawback',array_unique($tmpPoArr) );
    		if($is_debug){
    			echo "<pre>同步参数1.1:<br>";
    			print_r($tmpPoArr);
    			echo "</pre>";
    		}
    
    		$tmpMapArr = [];
    		if($tmpPoArr){
    			$tmpMapArr = array_column($tmpPoArr, 'is_drawback','pur_number');
    		}
    
    		if($is_debug){
    			echo "<pre>同步参数2.1:<br>";
    			print_r($tmpMapArr);
    			echo "</pre>";
    		}
    
    		//组装K3所需要的字段信息
    		foreach ($need_data as $value){
    			$updateData = [];
    			try{
    				if(!empty($tmpMapArr)){
    					$isDrawBack = $tmpMapArr[$value['purchase_no']];
    					// 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
    					$orgId = $isDrawBack == 1 ? '101' : $isDrawBack == 2 ? '102' : '';
    					$updateData = [
    							'id' => $value['id'],
    							'org_id' => $orgId,
    					];
    					
    					if($is_debug){
    						echo "<pre>同步参数3:<br>";
    						print_r($updateData);
    						echo "</pre>";
    					}
    					$rlt = $midInstockModel->saveOrgRows($updateData);
    				}
    			}catch(Exception $ex){
    				echo '<br/> errormsg: '.$ex->getMessage();
    			}
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    
    function replaceSpecialChar($strParam){//过滤特殊字符
    	$regex = "/\/|\"|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\s|\.|\/|\;|\'|\`|\=|\\\|\|/";
    	return preg_replace($regex," ",$strParam);
    }
    
    /**
     * @desc 获取采购系统供应商结算方式映射关系 - 结汇系统用
     * 支付方式  1-货到付款 2-款到发货 3-周结 4-半月结 5-月结 6-两月结 7-30%订金+70%尾款半月结 8-30%订金+70%尾款月结 9-货到付款(15天付清) 10-30%订金+70%尾款货到付款 11-30%订金+尾款次月15日结
     * 12:15%订金+85%尾款周结 ;13:20%订金+80%尾款周结 ;14:10%订金+发货前30%尾款+到货后60%尾款月结
     */
    public function getSettlementTypeMap( $pmsSettlement='' ){
    	$excSettlement = '';
    	switch ($pmsSettlement){
    		case 1:
    			$excSettlement = 1;
    			break;
    		case 2:
    			$excSettlement = 2;
    			break;
    		case 7:
    			$excSettlement = 3;
    			break;
    		case 8:
    			$excSettlement = 4;
    			break;
    		case 9:
    			$excSettlement = 5;
    			break;
    		case 6:
    			$excSettlement = 6;
    			break;
    		case 13:
    			$excSettlement = 7;
    			break;
    		case 15:
    			$excSettlement = 8;
    			break;
    		case 16:
    			$excSettlement = 9;
    			break;
    		case 17:
    			$excSettlement = 10;
    			break;
    		case 18:
    			$excSettlement = 11;
    			break;
    		case 11:
    			$excSettlement = 12;
    			break;
    		case 12:
    			$excSettlement = 13;
    			break;
    		case 19:
    			$excSettlement = 14;
    			break;
    	}
    	return $excSettlement;
    }
    
    /**
     * 更新汇总系统-采购单 是否合同、组织【修复】
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-po-compact-org-cloud?limit=100&is_debug=1&po=PO261015
     **/
    public function actionSynchPoCompactOrgCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',300);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$modelPoSource = new PurchaseOrderSourceRestore();
    	$modelMIdPo = new MidPurchaseOrder();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.purchase_no in('$po')";
    	}
    
    	$need_data = $modelMIdPo->getSyncCompactOrgData('a.id,a.purchase_no',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = array();
    		$date = date('Y-m-d H:i:s');
    
    		$tmpPoArr =  array_column($need_data, 'purchase_no');
    
    		//查询采购单修复的合同、组织
    		$tmpPoSourceArr = $modelPoSource->getPoSourceData('a.pur_number,a.source,a.company',array_unique($tmpPoArr));
    		
    		if($is_debug){
    			echo "<pre>同步参数1.1:<br>";
    			print_r($tmpPoSourceArr);
    			echo "</pre>";
    		}
    
    		$tmpMapArr = [];
    		if($tmpPoSourceArr){
    			foreach( $tmpPoSourceArr as $value ){
    				$tmpMapArr[$value['pur_number']]['is_compact'] = isset($value['source']) && $value['source'] == 1 ? 1 : 0;
    				$tmpMapArr[$value['pur_number']]['org_id'] = (isset($value['company']) && $value['company'] == 1) ? '101' : (isset($value['company']) && $value['company'] == 2 ? '102' : '');
    			}
    		}
    
    		if($is_debug){
    			echo "<pre>同步参数2.1:<br>";
    			print_r($tmpMapArr);
    			echo "</pre>";
    		}
    
    		//组装K3所需要的字段信息
    		foreach ($need_data as $value){
    			$updateData = [];
    			try{
    				$currIsCompact = isset($tmpMapArr[$value['purchase_no']]['is_compact']) ? $tmpMapArr[$value['purchase_no']]['is_compact'] : '';
    				$currOrgId = isset($tmpMapArr[$value['purchase_no']]['org_id']) ? $tmpMapArr[$value['purchase_no']]['org_id'] : '';
    
    				if(!empty($tmpMapArr)){
    					$updateData = [
    							'id' => $value['id'],
    							'is_compact' => $currIsCompact,
    							'org_id' => $currOrgId,
    							'is_update' => 1,
    					];
    					if($is_debug){
    						echo "<pre>同步参数3.1:<br>";
    						print_r($updateData);
    						echo "</pre>";
    					}
    					$rlt = $modelMIdPo->updateRows($updateData);
    				}
    			}catch(Exception $ex){
    				echo '<br/> errormsg: '.$ex->getMessage();
    			}
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    
    /**
     * 同步、更新汇总系统-采购优惠 【修复】
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-discount-restore-cloud?limit=100&is_debug=1&po=PO261015
     **/
    public function actionSynchDiscountRestoreCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$modelPoSource = new PurchaseOrderSourceRestore();
    	$modelPo = new PurchaseOrder();
    	$midDiscount = new MidPurchaseDiscount();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    	
    	$need_data = $modelPoSource->getSyncDiscountRestoreCloudData('a.id,a.discount,a.source,a.company,a.pur_number,b.supplier_code,b.buyer,b.created_at',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpBuyers = [];
    		foreach( $need_data as $value ){
    			$tmpBuyers[] = isset($value['buyer']) && $value['buyer'] ? $value['buyer'] : '';
    		}
    		$purchaserArr = $modelPo->getStaffCodeByUsers( array_unique($tmpBuyers) );
    		$staffCodeMap = array_column($purchaserArr, 'username','user_number');
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				$billNo = $value['pur_number'].'_D_'.str_pad($value['id'], 10, '0', STR_PAD_LEFT);
    				
    				$staffCode = in_array($value['buyer'], $staffCodeMap) ? array_search($value['buyer'], $staffCodeMap) : '';
    				if ( !$staffCode ){
    					$failsList[] = $value['id'];
    					continue;
    				}
    				
    				if ( empty($value['created_at']) ){
    					$failsList[] = $value['id'];
    					continue;
    				}
    				
    				if ($midDiscount->dataExist('id',$value['pur_number'],'')){ //存在采购单有优惠，则删除原记录
    					$rltDel = $midDiscount->delByPo($value['pur_number']);
    					if( !$rltDel ){
    						$failsList[] = $value['id'];
    						continue;
    					}
    				}
    				
    				$isCompact = isset($value['source']) && $value['source'] == 1 ? 1 : 0;
    				$orgId = isset($value['company']) && $value['company'] == 1 ? '101' : (isset($value['company']) && $value['company'] == 2 ? '102' : '');
    				
    				$params[] = array(
    						'bill_no' => $billNo,
    						'purchase_no' => $value['pur_number'],
    						'provider_id' => $value['supplier_code'],
    						'purchase_id' => $staffCode,
    						'discount' => $value['discount'] ? $value['discount'] : 0,
    						'note' => 'C',
    						'dates' => $value['created_at'], //业务日期
    						'org_id' => $orgId,
    						'is_compact' => $isCompact,
    						'is_update' => 1,
    				);
    				$insertParams[] = $value['id'];
    			}
    			
    			if(!empty($params)){
    				if ($midDiscount->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				$modelPoSource->updateDiscountSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$modelPoSource->updateDiscountSynchCloud([$failsVal],self::CLOUD_SYNC_STATUS_FAIL);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    		exit('done');
    	}else{
    		exit('data is null');
    	}
    }
    
    
    /**
     * 同步、更新汇总系统-采购运费 【修复】
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-freight-restore-cloud?limit=100&is_debug=1&po=PO261015
     **/
    public function actionSynchFreightRestoreCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$modelPoSource = new PurchaseOrderSourceRestore();
    	$modelPo = new PurchaseOrder();
    	$midFreight = new MidPurchaseFreight();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    	 
    	$need_data = $modelPoSource->getSyncFreightRestoreCloudData('a.id,a.freight,a.source,a.company,a.freight_source,a.pur_number,b.supplier_code,b.buyer,b.created_at',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpBuyers = [];
    		foreach( $need_data as $value ){
    			$tmpBuyers[] = isset($value['buyer']) && $value['buyer'] ? $value['buyer'] : '';
    		}
    		$purchaserArr = $modelPo->getStaffCodeByUsers( array_unique($tmpBuyers) );
    		$staffCodeMap = array_column($purchaserArr, 'username','user_number');
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				$billNo = $value['pur_number'].'_F_'.str_pad($value['id'], 10, '0', STR_PAD_LEFT);
    
    				$staffCode = in_array($value['buyer'], $staffCodeMap) ? array_search($value['buyer'], $staffCodeMap) : '';
    				if ( !$staffCode ){
    					$failsList[] = $value['id'];
    					continue;
    				}
    
    				if ( empty($value['created_at']) ){
    					$failsList[] = $value['id'];
    					continue;
    				}
    
    				if ($midFreight->dataExist('id',$value['pur_number'],'')){ //存在采购单有运费，则删除原记录
    					$rltDel = $midFreight->delByPo($value['pur_number']);
    					if( !$rltDel ){
    						$failsList[] = $value['id'];
    						continue;
    					}
    				}
    
    				$isCompact = isset($value['freight_source']) && $value['freight_source'] == 1 ? 1 : 0;
    				$orgId = isset($value['company']) && $value['company'] == 1 ? '101' : (isset($value['company']) && $value['company'] == 2 ? '102' : '');
    
    				$params[] = array(
    						'bill_no' => $billNo,
    						'purchase_no' => $value['pur_number'],
    						'provider_id' => $value['supplier_code'],
    						'purchase_id' => $staffCode,
    						'shipment' => $value['freight'] ? $value['freight'] : 0,
    						'dates' => $value['created_at'], //业务日期
    						'org_id' => $orgId,
    						'is_compact' => $isCompact,
    						'is_update' => 1,
    				);
    				$insertParams[] = $value['id'];
    			}
    			
    			if(!empty($params)){
    				if ($midFreight->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				$modelPoSource->updateFreightSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$modelPoSource->updateFreightSynchCloud([$failsVal],self::CLOUD_SYNC_STATUS_FAIL);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    		exit('done');
    	}else{
    		exit('data is null');
    	}
    }
    
    /**
     * 同步、更新汇总系统-采购退款单 【修复】
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-refund-cloud?limit=100&is_debug=1&po=PO261015
     **/
    public function actionSynchRefundCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$mid = new MidPurchaseRefund();
    	$modelPoRefund = new PurchasePaymentRefund();
    	$modelPo = new PurchaseOrder();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $modelPoRefund->getSyncReceiptCloudData('a.id,a.purchase_no,a.is_drawback,a.provider_code,a.dates,a.pay_account,a.note,a.refund_money,b.buyer',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $insertParams = array();
    		$date = date('Y-m-d H:i:s');
    		 
    		$tmpBuyers = [];
    		foreach( $need_data as $value ){
    			$tmpBuyers[] = isset($value['buyer']) && $value['buyer'] ? $value['buyer'] : '';
    		}
    		$purchaserArr = $modelPo->getStaffCodeByUsers( array_unique($tmpBuyers) );
    		$staffCodeMap = array_column($purchaserArr, 'username','user_number');
    		 
    		//组装K3所需要的字段信息
    		try{
    			foreach ($need_data as $value){
    				$billNo = $value['purchase_no'].'_R_'.str_pad($value['id'], 10, '0', STR_PAD_LEFT);
    
    				$staffCode = in_array($value['buyer'], $staffCodeMap) ? array_search($value['buyer'], $staffCodeMap) : '';
    				if ( !$staffCode ){
    					$failsList[PurchasePaymentRefund::CLOUD_SYNCH_ERROR_1][] = $value['id'];
    					continue;
    				}
    
    				if ($mid->dataExist('id',$value['purchase_no'],'')){ //存在采购单有运费，则删除原记录
    					$rltDel = $mid->delByPo($value['purchase_no']);
    					if( !$rltDel ){
    						$failsList[PurchasePaymentRefund::CLOUD_SYNCH_ERROR_2][] = $value['id'];
    						continue;
    					}
    				}
    
    				$isCompact = 0;
    				$orgId = isset($value['is_drawback']) && $value['is_drawback'] == 1 ? '101' : (isset($value['is_drawback']) && $value['is_drawback'] == 2 ? '102' : '');
    				
    				$params[] = array(
    						'bill_no' => $billNo,
    						'provider_id' => $value['provider_code'],
    						'dates' => $value['dates'], //业务日期
    						'pay_id' => $value['pay_account'],
    						'purchase_id' => $staffCode,
    						'payment_user_id' => '',
    						'refund_money' => isset($value['refund_money']) && $value['refund_money']?$value['refund_money']:0.00,
    						'purchase_no' => $value['purchase_no'],
    						'note' => isset($value['note']) && $value['note']? $value['note'] : 'C',
    						'org_id' => $orgId,
    						'is_compact' => $isCompact,
    						'is_update' => 1,
    						
    				);
    				$insertParams[] = $value['id'];
    			}
    			
    			if(!empty($params)){
    				if ($mid->batchInsertData( array_keys($params[0]), $params)) {
    					foreach( $insertParams as $v ){
    						$successList[] = $v;
    					}
    				}
    			}
    
    			if (count($successList) > 0){
    				$modelPoRefund->updateReceiptSynchCloud($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    				echo "<pre>同步成功:<br>";
    				print_r(json_encode($successList));
    				echo "</pre>";
    			}
    
    			if (count($failsList) > 0){
    				foreach ($failsList as $k => $failsVal){
    					if (!empty($failsVal)){
    						$modelPoRefund->updateReceiptSynchCloud($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    					}
    				}
    				if ($is_debug){
    					echo "<pre>同步失败:<br>";
    					print_r(json_encode($failsList));
    					echo "</pre>";
    				}
    			}
    
    		}catch (Exception $ex){
    			echo 'Error messages:'.$e->getMessage();
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    
    /**
     * 更新汇总系统-采购付款单 组织、是否合同【修复】
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-pay-compact-org-cloud?limit=100&is_debug=1&po=PO261015
     **/
    public function actionSynchPayCompactOrgCloud()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',300);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$modelPoSource = new PurchaseOrderSourceRestore();
    	$modelCompactItems = new PurchaseCompactItems(); //原合同
    	$modelCompactItemsRestore = new PurchaseCompactItemsRestore(); //财务维护合同
    	$midPaymentModel = new MidPurchasePayment();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    	
    	//1.查出未更新采购付款单组织、合同的采购单
    	//2.合同单：根据采购单查出关联的合同（从PMS原付款申请表、以及财务维护的付款申请表）
    	//3.非合同单：根据采购单号更新mid
    
    	$need_data = $modelPoSource->getSyncPaymentRestoreCloudData('a.id,a.pur_number,a.source,a.company',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = array();
    		$date = date('Y-m-d H:i:s');
    
    		$tmpPoArr =  array_column($need_data, 'pur_number');
    
    		//查询原合同
    		$tmpPoCompactArr = $modelCompactItems->getCompactsBindByPo('compact_number,pur_number',array_unique($tmpPoArr));
    		//查询财务修复的合同
    		$tmpPoCompacRestoretArr = $modelCompactItemsRestore->getCompactsBindByPo('compact_number,pur_number',array_unique($tmpPoArr));
    
    		if($is_debug){
    			echo "<pre>同步参数1.1:<br>";
    			print_r($tmpPoCompactArr);
    			echo "</pre>";
    		}
    		
    		if($is_debug){
    			echo "<pre>同步参数1.2:<br>";
    			print_r($tmpPoCompacRestoretArr);
    			echo "</pre>";
    		}
    
    		$tmpMapArr = [];
    		if($tmpPoCompactArr){
    			$tmpMapArr = array_column($tmpPoCompactArr, 'compact_number', 'pur_number');
    		}
    		
    		$tmpMapRestoreArr = [];
    		if($tmpPoCompacRestoretArr){
    			$tmpMapRestoreArr = array_column($tmpPoCompacRestoretArr, 'compact_number', 'pur_number');
    		}
    
    		if($is_debug){
    			echo "<pre>同步参数2.1:<br>";
    			print_r($tmpMapArr);
    			echo "</pre>";
    		}
    		
    		if($is_debug){
    			echo "<pre>同步参数2.2:<br>";
    			print_r($tmpMapRestoreArr);
    			echo "</pre>";
    		}
    
    		//组装K3所需要的字段信息
    		foreach ($need_data as $value){
    			$updateData = [];
    			try{
    				$needPurchaseNo = [];
    				$needPurchaseNo[] = $value['pur_number'];
    				$currCompact = isset($tmpMapArr[$value['pur_number']]) ? $tmpMapArr[$value['pur_number']] : '';
    				$currCompactRestore = isset($tmpMapRestoreArr[$value['pur_number']]) ? $tmpMapRestoreArr[$value['pur_number']] : '';
    				if( $currCompact ){
    					$needPurchaseNo[] = $currCompact;
    				}
    				if( $currCompactRestore ){
    					$needPurchaseNo[] = $currCompactRestore;
    				}
    				
    				$isCompact = isset($value['source']) && $value['source'] == 1 ? 1 : 0;
    				$orgId = isset($value['company']) && $value['company'] == 1 ? '101' : (isset($value['company']) && $value['company'] == 2 ? '102' : '');
    
    				if(!empty($needPurchaseNo)){
    					echo "<pre>同步参数3.1:<br>";
    					print_r($needPurchaseNo);
    					echo "</pre>";
    					$rlt = $midPaymentModel->updateRows($needPurchaseNo,$orgId,$isCompact);
    					if( $rlt !== false ){
    						$modelPoSource->updatePaymentSynchCloud([$value['id']],self::CLOUD_SYNC_STATUS_SUCCESS);
    					}
    				}
    			}catch(Exception $ex){
    				echo '<br/> errormsg: '.$ex->getMessage();
    			}
    		}
    	}else{
    		exit('data is null');
    	}
    }
    
    /**
     * 同步采购单-合同 到结汇系统
     * http://www.ebuy_pms.com/synchcloud/autosync/synch-po-ht-exc?limit=100&is_debug=1&po=ABD-HT000546
     **/
    public function actionSynchPoHtExc()
    {
    	ini_set('memory_limit', '100M');
    	set_time_limit('3600');
    	
    	//默认每次同步500条
    	$limit = Yii::$app->request->get('limit',500);
    	$po = Yii::$app->request->get('po');
    	$is_debug = Yii::$app->request->get('is_debug');
    
    	$model = new PurchaseOrderPay();
    	$modelPo = new PurchaseOrder();
    	$modelDetail = new PurchaseOrderItems();
    	$modelCompactItems = new PurchaseCompactItems();
    	
    	$excOrderModel = new ExcPurchaseOrder();
    	$excOrderDetailModel = new ExcPurchaseOrderDetail();
    
    	$conditions = '';
    	if (!empty($po)){
    		$po = explode(',',$po);
    		$po = implode("','",$po);
    		$conditions .= " and a.pur_number in('$po')";
    	}
    
    	$need_data = $model->getSyncPoHtExcData('a.pur_number,a.currency,a.pay_type,a.payer_time',$limit,$conditions);
    
    	if($is_debug){
    		echo "<pre>同步参数:<br>";
    		print_r($need_data);
    		echo "</pre>";
    	}
    	
    	if (!empty($need_data)){
    		$params = $successList = $insertList = $failsList = $existList = array();
    		$date = date('Y-m-d H:i:s');
    		
    		//组装K3所需要的字段信息
    		$transaction = $excOrderModel->getDb()->beginTransaction();
    		try{
    			$flag = true;
    			foreach ($need_data as $value){
    				//检查是否合同
    				$isCompact = 0;
    				$poArr = [];
    				if(preg_match('/HT/', $value['pur_number'])) {
    					$cmpBind = $modelCompactItems->getPoInfoByCompact('pur_number,compact_number',$value['pur_number']);
    					if(empty($cmpBind)) {
    						$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_5][] = $value['pur_number'];
    						continue;
    					}
    					$isCompact = 1;
    					//根据绑定的采购单号查询采购单信息
    					$tmpPurNumbers = array_column($cmpBind, 'pur_number'); //合同关联的采购单
    					$poArr = $modelPo->getPurchaseByPo('is_drawback,pur_number,supplier_code,created_at',$tmpPurNumbers);
    				} else {
    					$poArr = $modelPo->getPurchaseByPo('is_drawback,pur_number,supplier_code,created_at',$value['pur_number']);
    					if(empty($poArr)) {
    						$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_6][] = $value['pur_number'];
    						continue;
    					}
    				}
    				
    				//检查付款方式
    				if( in_array($value['pay_type'], [3]) ){ //银行转账
    					$isCompact = 1;
    				}
    				
    				if( !$isCompact ){
    					$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_12][] = $value['pur_number'];
    					continue;
    				}
    				
    				//过滤数据
    				$dataCheck = true;
    				foreach( $poArr as $kk=>$vv ){
    					$checkExist = $excOrderModel->dataExist('purchase_no',$vv['pur_number']);
    					if ( isset($checkExist['purchase_no']) && $checkExist['purchase_no'] ){
    						$existList[] = $value['pur_number'];
    						$dataCheck = false;
    						break;
    					}
    					
    					if ( !$vv['supplier_code'] ){
    						$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_9][] = $value['pur_number'];
    						$dataCheck = false;
    						break;
    					}
    					
    					if ( !$vv['created_at'] ){
    						$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_10][] = $value['pur_number'];
    						$dataCheck = false;
    						break;
    					}
    					
    					//检查采购单明细
    					$need_data_detail = $modelDetail->getSyncPoDetailCloudData('*',$vv['pur_number']);
    					if ( !$need_data_detail ){
    						$failsList[PurchaseOrderPay::CLOUD_SYNCH_ERROR_11][] = $value['pur_number'];
    						$dataCheck = false;
    						break;
    					}
    				}
    				
    				//保存数据
    				if( $dataCheck && $poArr ){
    					foreach( $poArr as $kk=>$vv ){
    						//1.保存采购单主信息
    						//检查采购单明细
    						$need_data_detail = $modelDetail->getSyncPoDetailCloudData('pur_number,sku,price,ctq',$vv['pur_number']);
    						
    						if($vv['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
    							$orgId = '101';
    						} elseif($vv['is_drawback'] == 2) {
    							$orgId = '102';
    						}else{
    							//@todo
    						}
    						$params = [];
    						$params = array(
    								'org_id' => $orgId,
    								'purchase_no' => $vv['pur_number'],
    								'supplier_code' => $vv['supplier_code'],
    								'supplier_type' => 1, //供应商类型   1 合同供应商   2 网采供应商  3 账期供应商
    								'freight_price' => 0.00,
    								'purchase_date' => $vv['created_at'],
    								'is_compact' => $isCompact,
    								'payment_time' => $value['payer_time'],
    								//'company_org' => '',
    						);
    						
    						if($is_debug){
    							echo "<pre>插入参数:<br>";
    							print_r($params);
    							echo "</pre>";
    						}
    						
    						if(!empty($params)){
    							$rlt1 = $excOrderModel->batchInsertData( array_keys($params), [$params]);
    						}
    						
    						//2.保存采购单明细信息
    						$paramDetail = [];
    						foreach( $need_data_detail as $v ){
    							$paramDetail[] = array(
    									'purchase_no' => $v['pur_number'],
    									'sku' => $v['sku'],
    									'price' => $v['price'],
    									'qty' => $v['ctq'],
    							);
    						}
    						
    						if(!empty($paramDetail)){
    							$rlt2 = $excOrderDetailModel->batchInsertData( array_keys($paramDetail[0]), $paramDetail);
    						}
    						
    						$flag = $flag && $rlt1 && $rlt2;
    						if( $rlt1 && $rlt2 ){
    							$successList[] = $value['pur_number'];
    						}
    					}
    				}
    			}
    			
    			if($flag){
    				$transaction->commit();
    			}else{
    				$transaction->rollBack();
    			}
    		}catch(Exception $ex){
    			$transaction->rollBack();
    		}
    		 
    		if (count($successList) > 0){
    			$model->updatePoSynchExc($successList,self::CLOUD_SYNC_STATUS_SUCCESS,$date);
    			echo "<pre>同步成功:<br>";
    			print_r(json_encode($successList));
    			echo "</pre>";
    		}
    
    		if (count($existList) > 0){
    			$model->updatePoSynchExc($existList,self::CLOUD_SYNC_STATUS_EXIST,$date);
    			echo "<pre>重复同步订单:<br>";
    			print_r(json_encode($existList));
    			echo "</pre>";
    		}
    		 
    		if (count($failsList) > 0){
    			foreach ($failsList as $k => $failsVal){
    				if (!empty($failsVal)){
    					$model->updatePoSynchExc($failsVal,self::CLOUD_SYNC_STATUS_FAIL,$date,$k);
    				}
    			}
    			if ($is_debug){
    				echo "<pre>同步失败:<br>";
    				print_r(json_encode($failsList));
    				echo "</pre>";
    			}
    		}
    		exit('done');
    	}else{
    		exit('data is null');
    	}
    }
    

}
