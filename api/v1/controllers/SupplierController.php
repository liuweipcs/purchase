<?php

namespace app\api\v1\controllers;
use app\api\v1\models\ArrivalRecord;
use app\api\v1\models\CostPurchaseNum;
use app\api\v1\models\Product;
use app\api\v1\models\ProductProvider;
use app\api\v1\models\PurchaseOrder;
use app\api\v1\models\PurchaseOrderItems;
use app\api\v1\models\Supplier;
use app\api\v1\models\SupplierKpiCaculte;
use app\api\v1\models\SupplierProposalResult;
use app\api\v1\models\SupplierProposalTemplate;
use app\api\v1\models\SupplierQuotes;
use app\api\v1\models\WarehouseResults;
use app\models\PurchaseCancelQuantity;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderRefundQuantity;
use app\models\SupplierProductLine;
use app\models\SupplierSettlementLog;
use app\models\SupplierUpdateApply;
use app\services\SupplierGoodsServices;
use yii;
use app\config\Vhelper;
use linslin\yii2\curl;
use yii\helpers\Json;
use app\models\SupplierNum;
use yii\helpers\ArrayHelper;
use app\models\SupplierCheck;
use app\models\SupplierCheckSearch;
use app\models\SupplierCheckNote;

/**
 * 供应商
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class SupplierController extends BaseController
{
	
    public $modelClass = 'app\api\v1\models\Supplier';
	static $url = 'https://openapi.tongtool.com/';
	static $token = 'b24fe215-7a7b-4e83-85be-e917d59eef18';
	static $merchantId = '003498';
	/**
     * 推送所有的供应商
     * @return string
     */
    public function actionSupplierAll()
    {
        $curl = new curl\Curl();
        $modelClass = $this->modelClass;
        $limit              = (int)Yii::$app->request->get('limit');
        if ($limit <= 0 || $limit > 1000)
            $limit = 1000;
        $query = $modelClass::find()->joinWith(['pay','contact'])->where(['is_push'=>0])->asarray()->limit($limit)->all();
        try{
                $url =Yii::$app->params['server_ip'].'/index.php/provider/puchaseProviderInsertToMysql';
                $s = $curl->setPostParams([
                    'providerInfo' => Json::encode($query),
                    'token'    => Json::encode(Vhelper::stockAuth()),
                ])->post($url);
                $_result = Json::decode($s);
                if ($_result['success_list'] && !empty($_result['success_list']))
                {
                    foreach ($_result['success_list'] as $v)
                    {
                        $modelClass::updateAll(['is_push' => 1], 'supplier_code = :supplier_code', [':supplier_code' => $v]);
                    }

                } else {
                    exit('数据已推送过去');
                }
        } catch (Exception $e){

            exit('发生了错误');
        }

    }

    /**
     * 推送产品与供应商关系
     */
    public function actionProductSupplier()
    {
        $curl = new curl\Curl();
        $limit              = (int)Yii::$app->request->get('limit');
        if ($limit <= 0 || $limit > 1000)
            $limit = 1000;
        $query = ProductProvider::find()->where(['is_push'=>0])->asarray()->limit($limit)->all();
        $datas =[];
        foreach ($query as $v)
        {
            $data['id']             = $v['id'];
            $data['sku']            = $v['sku'];
            $data['supplier_code']  = $v['supplier_code'];
            $data['is_supplier']    = $v['is_supplier'];
            $data['is_exemption']   = $v['is_exemption'];
            $data['supplier_name']  = Supplier::getSupplierName($v['supplier_code']);
            $datas[] =$data;

        }

        try{
            $url =Yii::$app->params['server_ip'].'/index.php/provider/supplierProductInsertToMysql ';
            $s = $curl->setPostParams([
                'supplierProduct' => Json::encode($datas),
                'token'    => Json::encode(Vhelper::stockAuth()),
            ])->post($url);
            $_result = Json::decode($s);
            if ($_result['success_list'] && !empty($_result['success_list']))
            {
                foreach ($_result['success_list'] as $v)
                {
                    ProductProvider::updateAll(['is_push' => 1], 'id = :id', [':id' => $v]);
                }

            } else {
                exit('数据已推送过去');
            }
        } catch (Exception $e){

            exit('发生了错误');
        }
    }

    /**
     * 接收数据中心过来的供应商信息
     */
    public function actionCreateSupplier()
    {
        $datas  = Yii::$app->request->post()['provider'];
        if(isset($datas) && !empty($datas))
        {
            $datas  = Json::decode($datas);
            $data   = Supplier::FindOnes($datas);
            return $data;
        } else {
            return '没有任何的数据过来！';
        }

    }

    /**
     * 接收通途过来的供应商信息
     */
    public function actionCreateSupplierTongTool()
    {

        set_time_limit(50000);
        $is= SupplierNum::find()->select('num')->where(['type'=>1])->orderBy('id desc')->scalar();

        if(!empty($is))
        {
           $id = $is;
        } else{
            $id = 1;
        }
        //for ($i=$id;$i<=140;$i++) {

            $curl  = new curl\Curl();
            $datas = [
                'token' => 'b24fe215-7a7b-4e83-85be-e917d59eef18',
                'data'  => [
                    'merchantId' => '003498',
                    'pageNo'     => $id,
                ],
            ];
            try {
                $url     = Yii::$app->params['tongtool'] . '/process/resume/openapi/tongtool/suppliersQuery';
                $s       = $curl->setPostParams([
                    'q' => Json::encode($datas),
                ])->post($url);
                //验证json
                $sb = Vhelper::is_json($s);
                if(!$sb)
                {
                    echo '请检查json'."\r\n";
                    exit($s);
                } else {
                    $_result = Json::decode($s);
                    if (!is_array($_result['data']['array'])) {
                        $mod      = new SupplierNum();
                        $mod->num = $id;
                        $mod->type = 1;
                        $mod->time = time();
                        $mod->save(false);
                        exit();
                    } else {
                        Supplier::SaveTongTool($_result['data']['array']);
                        $mod      = new SupplierNum();
                        $mod->num = $id + 1;
                        $mod->type = 1;
                        $mod->time = time();
                        $mod->save(false);
                    }
                }

            } catch (Exception $e) {

                exit('发生了错误');
            }
        //}
    }


    /**
     * 获取供应商报价
     */
    public function  actionSupplierQuotation($ids=1,$times='2016-01-01 00:00:00')
    {
        set_time_limit(50000);
        $is= SupplierNum::find()->select('num,time,end_time')->where(['type'=>2])->orderBy('id desc')->one();

        if(!empty($is))
        {

                $id       = $ids;
                $time     = date('Y-m-d H:i:s',$is->time);
                $end_time = date('Y-m-d H:i:s',$is->end_time);
           /* } else {

                $is->delete();
                exit();
            }*/

        } else {
            $id       = $ids;
            $time     = $times;
            $end_time = date("Y-m-d H:i:s",strtotime("$time   +3   day"));   //日期天数相加函数
        }


        $curl  = new curl\Curl();
        $datas = [
            'token' => 'b24fe215-7a7b-4e83-85be-e917d59eef18',
            'data'  => [
                'pageNo'               => $ids,
                'merchantId'           => '003498',
                'quotedPriceDateBegin' => $time,
                'quotedPriceDateEnd'   => $end_time,
            ],
        ];
        try {
            $url     = Yii::$app->params['tongtool'] . '/process/resume/openapi/tongtool/queryGoodsPrice';
            $s       = $curl->setPostParams([
                'q'  => Json::encode($datas),
            ])->post($url);

            //验证json
            $sb      = Vhelper::is_json($s);
            if(!$sb)
            {
                echo '请检查json'."\r\n";
                exit($s);
            } else {
                $_result = Json::decode($s);
                //Vhelper::dump($_result);
                if (!is_array($_result['data']['array']))
                {
                    $mod            = new SupplierNum();
                    $mod->num       = $id;
                    $mod->type      = 2;
                    $mod->time      = strtotime($time);
                    $mod->end_time  = strtotime($end_time);
                    $mod->save();
                    exit();
                } else {
                    //分页请求
                    if(!empty($_result['data']['array']))
                    {
                        SupplierQuotes::SaveTongTool($_result['data']['array']);
                        $id = $id + 1;
                        $this->actionSupplierQuotation($id,$time);

                    } else{
                        //不是分页请求
                        $mod           = new SupplierNum();
                        $mod->num      = $ids;
                        $mod->type     = 2;
                        $mod->time     = strtotime("$time   +3day");
                        $mod->end_time = strtotime("$end_time   +3day");
                        $mod->save();
                        if(!empty($_result['data']['array']))
                        {
                            SupplierQuotes::SaveTongTool($_result['data']['array']);
                        } else {
                            //SupplierNum::deleteAll('type = :type_id',[ ':type_id' =>2]);
                        }
                    }

                }
            }

        } catch (Exception $e) {

            exit('发生了错误');
        }
    }
	
	
	/**
     * 获取采购建议模板计算结果
     */
    public function  actionProposalResult()
    {
        set_time_limit(50000);
		
        $templateids=SupplierProposalTemplate::find()->select('purchasetemplateid')->where(['suggestiontype'=>'other','done'=>2])->all();
		if(!$templateids){
			$modtemplate  = new SupplierProposalTemplate();
			//同步完所有模板后重新置为未同步
			Yii::$app->db->createCommand()->update($modtemplate->tableName(),['done'=>2],['suggestiontype'=>'other'])->execute();//die;
			$templateids=SupplierProposalTemplate::find()->select('purchasetemplateid')->where(['suggestiontype'=>'other','done'=>2])->all();
							
		}
        $templateids=ArrayHelper::getColumn($templateids,'purchasetemplateid');
		// var_dump($templateids[0]);die;
		
        try {
            // foreach($templateids as $templateid){
                // $is= SupplierNum::find()->select('num,templateid')->where( ['in','templateid',$templateids])->andWhere(['>','num',0])->orderBy('id desc')->one();
				$templateid=$templateids[0];
                $is= SupplierNum::find()->select('num,templateid')->where( ['templateid'=>$templateid])->orderBy('id desc')->one();
				// var_dump($is);die;
				if(!empty($is))
				{
					$id       = $is->num+1;
				} else{
					$id       = 1;
				}
				//请求数据
				$i=0;
				// do{
                $data = array();
                $pageNum=$id+$i;
				$i++;
                // $purchaseTemplateId='6062003498201510280000662712';
                 $purchaseTemplateId=$templateid;
                if($pageNum>0){
                    $data['pageNo']=$pageNum;
                }
                if($purchaseTemplateId){
                    $data['purchaseTemplateId']=$purchaseTemplateId;
                }

                $url = self::$url.'process/resume/openapi/tongtool/queryProposalResult';
                $res=Vhelper::postResult($url,$data);
				
				
				// var_dump($purchaseTemplateId);//die;
				//验证json
				$s=Json::encode($res);
				$sb      = Vhelper::is_json($s);
				if(!$res)
				{
					echo '返回数据为空';
					exit($res);
				}elseif(!$sb){
					echo '请检查json'."\r\n";
					exit($s);
				} else {
				
					// Vhelper::dump($res->data->array);
					$num=count($res->data->array);
					
					//没有数据的模板id
					if($res->ack=='Success'&&$res->data->pageNo==1&&$num==0){
						$mod           = new SupplierNum();
						$mod->num      = 0;
						$mod->type     = 3;
						$mod->templateid  = $templateid;
						$mod->save(false);
						//标记模板id已完成同步 1
						$modtemplate  = new SupplierProposalTemplate();
						Yii::$app->db->createCommand()->update($modtemplate->tableName(),['done'=>1],['purchasetemplateid'=>$templateid])->execute();//die;
					}
					
					if($num>0&&$res->ack=='Success'){
								
						SupplierProposalResult::SaveProposalresult($res);
						$exist= SupplierNum::find()->select('*')->where(['templateid'=>$templateid])->orderBy('id desc')->one();
						if(!empty($exist))
						{
							if($num<100&&$res->ack=='Success'){
								$pageNum=0;
								//标记模板id已完成同步 1
								$modtemplate  = new SupplierProposalTemplate();
			                    Yii::$app->db->createCommand()->update($modtemplate->tableName(),['done'=>1],['purchasetemplateid'=>$templateid])->execute();//die;
							}
							// $exist->templateid=$templateid;
							$exist->num      = $pageNum;
							$exist->update();
						} else{
							if($num<100&&$res->ack=='Success'){
								$pageNum=0;
								//标记模板id已完成同步 1
								$modtemplate  = new SupplierProposalTemplate();
			                    Yii::$app->db->createCommand()->update($modtemplate->tableName(),['done'=>1],['purchasetemplateid'=>$templateid])->execute();//die;
							}
							$mod           = new SupplierNum();
							$mod->num      = $pageNum;
							$mod->type     = 3;
							$mod->templateid  = $templateid;
						   // Vhelper::dump($time);
							$mod->save(false);
						}
						
					}else{
						var_dump($res);
					}
				}
				
                // }while ($num==100&&$i<=30);
            // }
        } catch (Exception $e) {

            var_dump($e->getMessage());
        }
    }
	
	
	/*
	拉取建议模板
	*/
	public function actionProposalTemplate(){
		set_time_limit(50000);
        $is= SupplierNum::find()->select('num')->where(['type'=>4])->orderBy('id desc')->scalar();

        if(!empty($is))
        {
           $id = $is;
        } else{
            $id = 1;
        }
        //for ($i=$id;$i<=140;$i++) {
		$data = array();
		$pageNum=$id;
		if($pageNum>0){
			$data['pageNo']=$pageNum;
		}
		try {
			$url = self::$url.'process/resume/openapi/tongtool/queryProposalTemplate';
			$res=Vhelper::postResult($url,$data);
			$num=count($res->data->array);
			// Vhelper::dump($s);
			$s=Json::encode($res);
			//验证json
			$sb = Vhelper::is_json($s);
			if(!$sb)
			{
				echo '请检查json'."\r\n";
				exit($s);
			} else {
				if ($num>0&&$res->ack=='Success') {
					SupplierProposalTemplate::SaveProposalTemplate($res);
					
					$mod      = new SupplierNum();
					$mod->num = $id + 1;
					if($num<100&&$res->ack=='Success'){
						$mod->num =0;
					}
					$mod->type = 4;
					$mod->save(false);
				} else {
					$mod      = new SupplierNum();
					$mod->num = $id;
					if($num<100&&$res->ack=='Success'){
						$mod->num =0;
					}
					$mod->type = 4;
					$mod->save(false);
					exit();
					
				}
			}

		} catch (Exception $e) {

			exit('发生了错误');
		}
        //}
	}

    /*
     * 获取降价数据采购数量
     */
	public function actionGetCostNum(){
	    set_time_limit(200);
	    //$type 1/统计供应链的申请数据，2、统计非供应链的申请数据
	    //获取价格变化且状态不为4的通过申请
        $begin = Yii::$app->cache->get('circle_begin_cost');
        if(!$begin){
            $begin = 0;
        }
        $query = SupplierUpdateApply::find()
                    ->alias('t')
                    ->andFilterWhere(['t.cost_status'=>0])
                    ->andFilterWhere(['t.status'=>2])
                    ->andFilterWhere(['not in','t.type',[4,5]])
                    ->joinWith('oldQuotes as old')
                    ->joinWith('newQuotes as new')
                    ->andWhere('old.supplierprice <> new.supplierprice')
                    ->orderBy('t.id ASC')
                    ->offset($begin)
                    ->limit(1000);
        $datas = $query->all();
        if(!empty($datas)){
            foreach ($datas as $data){
                //循环一条则设置新的循环偏移量
                $begin++;
                //最后一次更新后五小时无新增数据则缓存过期重新计算
                Yii::$app->cache->set('circle_begin_cost',$begin,18000);
                //开始计算时间供应链取FBA国内仓采购单第一单入库时间和海外仓审核时间最小的一个，其他部门取价格变化时间
                //$cost_begin_time = $type==1 ? self::getFirstTime($data) : $data->update_time;
                //20181024修改所有降本数据开始计算时间都改为改价后第一个满足条件订单审核时间
                $cost_begin_time = self::getFirstTime($data);
                if(!$cost_begin_time){
                    $data->cost_begin_time='';
                    $data->save();
                    CostPurchaseNum::updateAll(['status'=>0],['apply_id'=>$data->id,'sku'=>$data->sku,'status'=>1]);
                    continue;
                }
                //获取分段计算时间
                $countTime1 = self::getPurNumTime($cost_begin_time);
                $countTime2 = !empty($countTime1) ? self::getPurNumTime($countTime1['end_time'],$countTime1['limit']) : [];
                $countTime3 = !empty($countTime2) ? self::getPurNumTime($countTime2['end_time'],$countTime2['limit']) : [];
                $dateArray=[];
                if(!empty($countTime1)){
                    $dateArray[] = date('Y-m-01 00:00:00',strtotime($countTime1['begin_time']));
                }
                if(!empty($countTime2)){
                    $dateArray[] = date('Y-m-01 00:00:00',strtotime($countTime2['begin_time']));
                }
                if(!empty($countTime3)){
                    $dateArray[] = date('Y-m-01 00:00:00',strtotime($countTime3['begin_time']));
                }
                if(!empty($dateArray)){
                    CostPurchaseNum::updateAll(['status'=>0],['and',['apply_id'=>$data->id,'sku'=>$data->sku,'status'=>1],['not in','date',$dateArray]]);
                }else{
                    CostPurchaseNum::updateAll(['status'=>0],['apply_id'=>$data->id,'sku'=>$data->sku,'status'=>1]);
                }
                $numArray = !empty($countTime1) ? self::getPurNum($data,$countTime1,$countTime2,$countTime3) : 0 ;
                CostPurchaseNum::savePurNum($data,$countTime1,$numArray['num1']);
                CostPurchaseNum::savePurNum($data,$countTime2,$numArray['num2']);
                CostPurchaseNum::savePurNum($data,$countTime3,$numArray['num3']);

                $data->cost_begin_time  = $cost_begin_time ;
                //开始时间60天后就不在计算
                $data->cost_status      = date('Y-m-d H:i:s',strtotime("$cost_begin_time+ 60 day")) <date('Y-m-d H:i:s',time()) ? 1 : 0;
                $data->save(false);
            }
        }
        if(count($datas)<1000){
            echo "运行完成";
            Yii::$app->cache->set('circle_begin_cost',0);
        }
        exit('success');
    }

    //获取sku第一单入库或者下单时间
    public static function getFirstTime($data){

        $result = PurchaseOrderItems::find()
            ->alias('t')
            ->select(['ctq'=>'t.ctq','cty'=>'t.cty','price'=>'t.price','base_price'=>'ifnull(t.base_price,0)','cancel_num'=>'ifnull(b.cancel_ctq,0)','cancel_num2'=>'ifnull(refund_qty,0)',
                'pur_number'=>'a.pur_number','audit_time'=>'a.audit_time','buyer'=>'a.buyer','cancel_audit_time'=>'c.audit_time','supplier_name'=>'s.supplier_name'])
            ->leftJoin(PurchaseOrder::tableName().' a','a.pur_number=t.pur_number')
            ->leftJoin(PurchaseOrderCancelSub::tableName().' b','t.pur_number=b.pur_number and t.sku=b.sku')
            ->leftJoin(PurchaseOrderCancel::tableName().' c','c.id = b.cancel_id')
            ->leftJoin(PurchaseOrderRefundQuantity::tableName().' d','t.sku=d.sku and t.pur_number=d.pur_number')
            ->leftJoin(Supplier::tableName().' s','a.supplier_code=s.supplier_code')
            ->andWhere(['or',['and',['t.base_price'=>0],['<=','t.price',$data->newQuotes->supplierprice]],['and',['<>','t.base_price',0],['<=','t.base_price',$data->newQuotes->supplierprice]]])
            ->andWhere(['or',['c.audit_status'=>2],['c.audit_status'=>null]])
            ->andFilterWhere(['t.sku'=>$data->sku])
            ->andFilterWhere(['NOT IN','a.purchas_status',[1,2,4,10]])
            ->andFilterWhere(['>=','a.audit_time',$data->update_time])
            ->orderBy('a.audit_time asc')
            ->asArray()->all();
        if(!empty($result)){
            foreach ($result as $value){
                if(preg_match('/^ABD/',$value['pur_number'])&&!empty($value['cancel_audit_time'])&&strtotime($value['cancel_audit_time'])<=strtotime('2018-11-28 12:00:00')){
                    $cancel = 0;
                }else {
                    $cancel =   $value['cancel_num2'] + $value['cancel_num'];
                }
                if(($value['ctq']-$cancel)<=0){
                    continue;
                }
                return $value['audit_time'];
            }
        }else{
            return false;
        }
        return false;
    }

    //根据开始时间和天数间隔计算采购数据计算时间;
    public static function getPurNumTime($begin_time,$limit=30){
	    if($limit<0){
	        return [];
        }
        $month_big = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        //开始的月份
        $date_month_old = (int)date('m',strtotime($begin_time));
        //下个月的月份
        $year = $date_month_old==12 ? date('Y',strtotime($begin_time)) +1 : date('Y',strtotime($begin_time));
        $date_time_new = strtotime('1 '.$month_big[$date_month_old%12].' '.$year);
        //今天的时间戳
        $date_time_old = strtotime(date('d',strtotime($begin_time)).' '.$month_big[$date_month_old-1].' '.date('Y',strtotime($begin_time)));
        //距下月剩余时间
        //var_dump($limit);
        $time_new = ($date_time_new - $date_time_old)/24/60/60;
        $old_limit=$limit;
        $limit-=$time_new;
        if($limit>0){
            $array['begin_time'] = $begin_time;
            $array['end_time'] = date('Y-m-d H:i:s',$date_time_new);
            $array['limit']    = $limit;
        }else{
            $array['begin_time'] = $begin_time;
            $array['end_time'] = date('Y-m-d 00:00:00',strtotime("$begin_time + $old_limit day"));
            $array['limit']    = $limit;
        }
        return $array;
    }

    //根据时间段获取sku计算数量
    public static function getPurNum($data,$date1,$date2,$date3){
	    if(!empty($date1)){
	        $dateArray[] = strtotime($date1['begin_time']);
	        $dateArray[] = strtotime($date1['end_time']);
        }
        if(!empty($date2)){
            $dateArray[] = strtotime($date2['begin_time']);
            $dateArray[] = strtotime($date2['end_time']);
        }
        if(!empty($date3)){
            $dateArray[] = strtotime($date3['begin_time']);
            $dateArray[] = strtotime($date3['end_time']);
        }
	    if(empty($dateArray)){
	        return ['num1'=>0,'num2'=>0,'num3'=>0];
        }
        $beginTime = date('Y-m-d H:i:s',min($dateArray));
        $endTime   = date('Y-m-d H:i:s',max($dateArray));
        //获取sku采购单确认数量减去取消数量的总数，供应链的只获取海外仓
        //20181024修改所有部门采购数量改为时间段内采购下单数量
        $query = PurchaseOrderItems::find()
                    ->alias('t')
                    ->select(['ctq'=>'t.ctq','cancel_num'=>'ifnull(b.cancel_ctq,0)','cancel_num2'=>'ifnull(refund_qty,0)','pur_number'=>'a.pur_number','audit_time'=>'a.audit_time',
                        'cancel_audit_time'=>'c.audit_time'])
                    ->leftJoin(PurchaseOrder::tableName().' a','a.pur_number=t.pur_number')
                    ->leftJoin(PurchaseOrderCancelSub::tableName().' b','t.pur_number=b.pur_number and t.sku=b.sku')
                    ->leftJoin(PurchaseOrderCancel::tableName().' c','c.id = b.cancel_id')
                    ->leftJoin(PurchaseOrderRefundQuantity::tableName().' d','t.sku=d.sku and t.pur_number=d.pur_number')
                    ->andWhere(['or',['c.audit_status'=>2],['c.audit_status'=>null]])
                    ->andWhere(['or',['and',['t.base_price'=>0],['<=','t.price',$data->newQuotes->supplierprice]],['and',['<>','t.base_price',0],['<=','t.base_price',$data->newQuotes->supplierprice]]])
                    ->andFilterWhere(['t.sku'=>$data->sku])
                    ->andFilterWhere(['NOT IN','a.purchas_status',[1,2,4,10]])
                    ->andFilterWhere(['>=','a.audit_time',$beginTime])
                    ->andFilterWhere(['<','a.audit_time',$endTime]);
        $numData = $query->asArray()->all();
        if(empty($numData)){
            return 0;
        }
        foreach ($numData as $value){
            if(!empty($date1)&&strtotime($value['audit_time'])>=strtotime($date1['begin_time'])&&strtotime($value['audit_time'])<strtotime($date1['end_time'])){
                $k = 'num1';
            }
            if(!empty($date2)&&strtotime($value['audit_time'])>=strtotime($date2['begin_time'])&&strtotime($value['audit_time'])<strtotime($date2['end_time'])){
                $k = 'num2';
            }
            if(!empty($date3)&&strtotime($value['audit_time'])>=strtotime($date3['begin_time'])&&strtotime($value['audit_time'])<strtotime($date3['end_time'])){
                $k = 'num3';
            }
            $cancel[$k][$value['pur_number']]['ctq'] = $value['ctq'];
            if(preg_match('/^ABD/',$value['pur_number'])&&!empty($value['cancel_audit_time'])&&strtotime($value['cancel_audit_time'])<=strtotime('2018-11-28 12:00:00')){
                $cancel[$k][$value['pur_number']]['cancel'] = 0;
            }else {
                $cancel[$k][$value['pur_number']]['cancel'] = isset($cancel[$value['pur_number']]['cancel']) ? $cancel[$value['pur_number']]['cancel'] + $value['cancel_num2'] + $value['cancel_num'] : $value['cancel_num2'] + $value['cancel_num'];
            }
        }
        if(empty($cancel)){
            return 0;
        }
        $num1 = 0;
        $num2 = 0;
        $num3 = 0;
        foreach ($cancel as $key=>$v){
            foreach ($v as $num){
                $$key += ($num['ctq']-$num['cancel']);
            }
        }
        return ['num1'=>$num1,'num2'=>$num2,'num3'=>$num3];
    }

    //王曼需求,禁用合作金额为0,可用sku为0的,临时使用
    public function actionChangeSupplier(){
	    $num = SupplierNum::find()->andWhere(['type'=>80])->orderBy('id DESC')->one();
	    if($num){
	        $pager = $num->num;
        }else{
	        $pager = 0;
        }
        Yii::$app->db->createCommand()->insert(SupplierNum::tableName(),[
            'num'=>$pager+1,
            'type'=>80,
            'time'=>time()
        ])->execute();
        $limit = 2000;
        $supplier = Supplier::find()->andFilterWhere(['status'=>1])->offset($pager*$limit)->limit($limit)->all();
        if(empty($supplier)){
            exit('数据已经跑完');
        }
        foreach ($supplier as $value){
            $money    = Vhelper::getSupplierPurchaseNum($value->supplier_code);
            $skuCount = SupplierUpdateApply::getProduct($value->supplier_code);
            //3月十五之前的且sku为0的禁用
            if(strtotime('2018-03-15 00:00:00')>$value->create_time&&$skuCount==0){
                Supplier::updateAll(['status'=>2],['supplier_code'=>$value->supplier_code]);
            }
            //3月十五之后的合作金额和sku都为0的禁用
            if(strtotime('2018-03-15 00:00:00')<=$value->create_time&&$money==0&&$skuCount==0){
                Supplier::updateAll(['status'=>2],['supplier_code'=>$value->supplier_code]);
            }
        }
    }

    public function actionGetSupplierKpi()
    {
        set_time_limit(0);
        $begin= microtime(time());
        $circleDate=self::getCircleDate();
        if(empty($circleDate)){
            exit();
        }
        foreach ($circleDate as $calculDate){
            $beginTime  = date('Y-m-01 00:00:00',strtotime($calculDate));
            $endTime  = date('Y-m-t 23:59:59',strtotime($calculDate));
            $supplierDatas = Supplier::find()->select('supplier_code')->orderBy('id ASC')->asArray()->all();
            $circleArray = array_chunk(array_column($supplierDatas,'supplier_code'),500);
            Yii::$app->db->createCommand()->delete(SupplierKpiCaculte::tableName(),['month'=>date('Y-m-01',strtotime($calculDate))])->execute();
            foreach ($circleArray  as $data){
                //计算账期数量
                $settlements = SupplierSettlementLog::find()
                                ->select('supplier_code')
                                ->where(['in','supplier_code',$data])
                                ->andWhere(['between','create_time',$beginTime,$endTime])
                                ->andWhere(['is_exec'=>1])
                                ->asArray()
                                ->all();
                $settleData = [];
                foreach ($settlements as $value){
                    $settleData[$value['supplier_code']] = isset($settleData[$value['supplier_code']]) ? $settleData[$value['supplier_code']]+1 : 1;
                }
                unset($settlements);
                $items = PurchaseOrderItems::find()
                            ->alias('t')
                            ->select('o.supplier_code,p.sku,t.price,t.ctq,o.date_eta,a.delivery_time,o.pur_number')
                            ->leftJoin(PurchaseOrder::tableName().' o','t.pur_number=o.pur_number')
                            ->leftJoin(ArrivalRecord::tableName().' a','t.pur_number=a.purchase_order_no AND t.sku=a.sku')
                            ->leftJoin(Product::tableName().' p','p.sku=t.sku')
                            ->where(['in','o.supplier_code',$data])
                            ->andWhere(['in','o.purchas_status',[3,5,6,7,8,9]])
                            ->andWhere(['between','o.created_at',$beginTime,$endTime])
                            ->orderBy('a.delivery_time ASC')
                            ->asArray()
                            ->all();
                $purchaseData=[];
                foreach ($items as $item){
                    $purchaseData[$item['supplier_code']][$item['pur_number']][$item['sku']]['ctq']=$item['ctq'];
                    $purchaseData[$item['supplier_code']][$item['pur_number']][$item['sku']]['price']=$item['price'];
                    $purchaseData[$item['supplier_code']][$item['pur_number']][$item['sku']]['date_eta']=$item['date_eta'];
                    $purchaseData[$item['supplier_code']][$item['pur_number']][$item['sku']]['delivery_time']=$item['delivery_time'];
                }
                $supplierPurchaseData = self::getSupplierPurchase($purchaseData);
                unset($purchaseData);
                $applyDatas= SupplierUpdateApply::find()
                            ->alias('t')
                            ->select(['supplier_code'=>'t.new_supplier_code','old_price'=>'oq.supplierprice','new_price'=>'nq.supplierprice','num'=>'c.purchase_num'])
                            ->leftJoin(CostPurchaseNum::tableName().' c','t.id=c.apply_id')
                            ->leftJoin(SupplierQuotes::tableName().' oq','t.old_quotes_id=oq.id')
                            ->leftJoin(SupplierQuotes::tableName().' nq','t.new_quotes_id=nq.id')
                            ->where(['in','t.new_supplier_code',$data])
                            ->andWhere(['c.date'=>date('Y-m-01 00:00:00',strtotime($calculDate))])
                            ->andWhere(['between','t.update_time',$beginTime,$endTime])
                            ->andWhere(['t.status'=>2])
                            ->asArray()
                            ->all();
                $applyData = self::getApplyData($applyDatas);
                unset($applyDatas);
                $insertData=[];
                foreach ($data as $key=>$isu){
                    $insertData[$key][]=$isu;
                    $insertData[$key][]=date('Y-m-01',strtotime($calculDate));
                    $insertData[$key][]=isset($settleData[$isu]) ? $settleData[$isu] : 0;
                    $insertData[$key][]=isset($supplierPurchaseData[$isu]['purchase_times']) ? $supplierPurchaseData[$isu]['purchase_times'] : 0;
                    $insertData[$key][]=isset($supplierPurchaseData[$isu]['purchase_money']) ? $supplierPurchaseData[$isu]['purchase_money'] : 0;
                    $insertData[$key][]=isset($supplierPurchaseData[$isu]['sku_times']) ? $supplierPurchaseData[$isu]['sku_times'] : 0;
                    $insertData[$key][]=isset($supplierPurchaseData[$isu]['on_time_times']) ? $supplierPurchaseData[$isu]['on_time_times'] : 0;
                    $insertData[$key][]=isset($supplierPurchaseData[$isu]['excep_times']) ? $supplierPurchaseData[$isu]['excep_times'] : 0;
                    $insertData[$key][]=0;
                    $insertData[$key][]=isset($applyData[$isu]['up_money']) ? $applyData[$isu]['up_money'] : 0;
                    $insertData[$key][]=isset($applyData[$isu]['change_times'])&&isset($applyData[$isu]['dowm_num']) ? $applyData[$isu]['dowm_num']/$applyData[$isu]['change_times'] : 0;
                    $insertData[$key][]=isset($applyData[$isu]['change_times'])&&isset($applyData[$isu]['up_num']) ? $applyData[$isu]['up_num']/$applyData[$isu]['change_times'] : 0;
                    $insertData[$key][]=date('Y-m-d H:i:s',time());
                    $insertData[$key][]=isset($applyData[$isu]['down_money']) ? $applyData[$isu]['down_money'] : 0;
                    $insertData[$key][]=!isset($supplierPurchaseData[$isu]['sku_times'])||$supplierPurchaseData[$isu]['sku_times']==0||!isset($supplierPurchaseData[$isu]['on_time_times']) ? 0 : round(($supplierPurchaseData[$isu]['on_time_times']/$supplierPurchaseData[$isu]['sku_times'])*100,2);
                    $insertData[$key][]=!isset($supplierPurchaseData[$isu]['sku_times'])||$supplierPurchaseData[$isu]['sku_times']==0||!isset($supplierPurchaseData[$isu]['excep_times']) ? 0 : round(($supplierPurchaseData[$isu]['excep_times']/$supplierPurchaseData[$isu]['sku_times'])*100,2);
                }
                Yii::$app->db->createCommand()->batchInsert(SupplierKpiCaculte::tableName(),
                    [
                        'supplier_code',
                        'month',
                        'settlement',
                        'purchase_times',
                        'purchase_total',
                        'sku_purchase_times',
                        'sku_punctual_times',
                        'sku_exception_times',
                        'sku_overseas_exception',
                        'sku_up_total',
                        'sku_down_rate',
                        'sku_up_rate',
                        'cacul_date',
                        'sku_down_total',
                        'punctual_rate',
                        'excep_rate'
                    ],$insertData)->execute();
            }
            unset($insertData);
        }
        echo microtime(time())-$begin.PHP_EOL;
    }

    public static function getCircleDate(){
	    $circleDate = [];
	    for($i=0;$i<=4;$i++){
	        if(strtotime("-".$i.'month')<strtotime('2018-01-01')){
	            break;
            }else{
	            $circleDate[] = date('Y-m-01',strtotime("-".$i.'month'));
            }
        }
        return $circleDate;
    }

    public static function getSupplierPurchase($datas){
	    $supplierData = [];
	    foreach ($datas as $key => $data){
	        $calculData = self::getPurchaseData($data);
            $supplierData[$key]['purchase_money'] = isset($calculData['total_money']) ? $calculData['total_money'] : 0;
            $supplierData[$key]['sku_times'] = isset($calculData['sku_times']) ? $calculData['sku_times'] : 0;
            $supplierData[$key]['on_time_times'] = isset($calculData['on_time_times']) ? $calculData['on_time_times'] : 0;
            $supplierData[$key]['excep_times'] = isset($calculData['excep_times']) ? $calculData['excep_times'] : 0;
            $supplierData[$key]['purchase_times'] = count($data);
        }
        return $supplierData;
    }

    public static function getPurchaseData($data){
        $ctqMoney = 0;
        $skuTimes =0;
        $ontimeTimes=0;
        $excepTimes =0;
	    foreach ($data as $value){
            $skuTimes += count($value);
	        foreach ($value as $v){
                $ctqMoney += $v['ctq']*$v['price'];
                if(empty($v['date_eta'])||empty($v['delivery_time'])){
                    continue;
                }
                if(!empty($v['date_eta'])&&!empty($v['delivery_time'])&&strtotime($v['date_eta'])<strtotime($v['delivery_time'])){
                    $excepTimes++;
                }
                if(!empty($v['date_eta'])&&!empty($v['delivery_time'])&&strtotime($v['date_eta'])>strtotime($v['delivery_time'])){
                    $ontimeTimes++;
                }
            }
        }
        return ['total_money'=>$ctqMoney,'sku_times'=>$skuTimes,'on_time_times'=>$ontimeTimes,'excep_times'=>$excepTimes];
    }

    public static function getApplyData($datas){
	    $resultData = [];
	    foreach ($datas as $data){
            if($data['old_price']<$data['new_price']){
                $resultData[$data['supplier_code']]['change_times'] = isset($resultData[$data['supplier_code']]['change_times']) ? $resultData[$data['supplier_code']]['change_times']+1 :1;
                $resultData[$data['supplier_code']]['up_num']   = isset($resultData[$data['supplier_code']]['up_num']) ? $resultData[$data['supplier_code']]['up_num'] +1 : 1;
                $resultData[$data['supplier_code']]['up_money']   =
                        isset($resultData[$data['supplier_code']]['up_money']) ?
                        $resultData[$data['supplier_code']]['up_money'] + (1000*$data['new_price']-1000*$data['old_price'])*$data['num']/1000 :
                        (1000*$data['new_price']-1000*$data['old_price'])*$data['num']/1000;
            }
            if($data['old_price']>$data['new_price']){
                $resultData[$data['supplier_code']]['change_times'] = isset($resultData[$data['supplier_code']]['change_times']) ? $resultData[$data['supplier_code']]['change_times']+1 :1;
                $resultData[$data['supplier_code']]['dowm_num']   = isset($resultData[$data['supplier_code']]['dowm_num']) ? $resultData[$data['supplier_code']]['dowm_num'] +1 : 1;
                $resultData[$data['supplier_code']]['down_money']   =
                    isset($resultData[$data['supplier_code']]['down_money']) ?
                        $resultData[$data['supplier_code']]['down_money'] + (1000*$data['old_price']-1000*$data['new_price'])*$data['num']/1000 :
                        (1000*$data['old_price']-1000*$data['new_price'])*$data['num']/1000;
            }
        }
        return $resultData;
    }


    /*FBA临时接口*/

    public function actionGetSupplierProductLine(){
        set_time_limit(0);
        for ($i=0;$i<=100;$i++){
            $insertData=[];
            $suppliers = Supplier::find()->select('supplier_code')->offset($i*1000)->limit(1000)->asArray()->all();
            if(empty($suppliers)){
                break;
            }
            foreach ($suppliers as $key=>$supplier_code){
                $exist = SupplierProductLine::find()->where(['supplier_code'=>$supplier_code['supplier_code'],'status'=>1])->exists();
                if($exist){
                    continue;
                }
                $defauleSku = ProductProvider::find()->select('sku')->where(['supplier_code'=>$supplier_code['supplier_code'],'is_supplier'=>1])->orderBy('id ASC')->scalar();
                if(!$defauleSku){
                    continue;
                }
                $skuLine = Product::find()->select('product_linelist_id')->where(['sku'=>$defauleSku])->scalar();
                if(!$skuLine){
                    continue;
                }
                $productLine = SupplierGoodsServices::getProductLineFirst($skuLine);
                if(empty($productLine)){
                    continue;
                }
                $insertData[$key][] = $supplier_code['supplier_code'];
                $insertData[$key][] = $productLine;
                $insertData[$key][] = 1;
            }
        if(!empty($insertData)){
            Yii::$app->db->createCommand()->batchInsert(SupplierProductLine::tableName(),['supplier_code','first_product_line','status'],$insertData)->execute();
        }
        unset($insertData);
        }
    }


    /**
     * 计划任务-推送所有采购员信息到erp
     * @return string|\yii\web\Response
     */
    public function actionPushBuyerInfo()
    {   
        set_time_limit(0);//程序执行时间无限制
        ini_set('memory_limit', '512M');

        $offset=(int)\Yii::$app->request->get('offset',0);
        $limit=(int)\Yii::$app->request->get('limit',1000);
        $skus=Product::find()->select('sku,id')->offset($offset)->orderBy('id')->limit($limit)->asArray()->all();
        $skuss=array_column($skus,'sku');
        //海外仓和国内仓的采购员是根据供应商获取的，FBA的是根据产品线获取的
        //sku 采购员
        //1.获取国内采购员信息
        $buyer_china=ProductProvider::find()
                         ->alias('a') 
                         ->select('a.sku,b.buyer')
                         ->leftJoin('pur_supplier_buyer b','a.supplier_code=b.supplier_code')
                         ->where(['=','b.type',1])
                         ->andwhere(['=','b.status',1])
                         ->andwhere(['in','a.sku',$skuss])
                         ->asArray()
                         ->all();
        $buyer_china=array_column($buyer_china, 'buyer','sku');

        //2.获取海外仓采购员信息
        $buyer_oversea=ProductProvider::find()
                             ->alias('a')
                             ->select('a.sku,b.buyer')
                             ->leftJoin('pur_supplier_buyer b','a.supplier_code=b.supplier_code')
                             ->where(['=','b.type',2])
                             ->andwhere(['=','b.status',1])
                             ->andwhere(['in','a.sku',$skuss])
                             ->asArray()
                             ->all();
        $buyer_oversea=array_column($buyer_oversea, 'buyer','sku');

        //3.获取FBA采购员信息
        $buyer_fba=ProductProvider::find()
                             ->alias('a')
                             ->select('a.sku,b.buyer_name')
                             ->leftJoin('pur_supplier_product_line l','a.supplier_code=l.supplier_code')
                             ->leftJoin('pur_purchase_category_bind b','l.first_product_line=b.category_id')
                             ->where(['=','l.status',1])
                             ->andwhere(['in','a.sku',$skuss])
                             ->asArray()
                             ->all();
        $buyer_fba=array_column($buyer_fba, 'buyer_name','sku');

/*        print_r($skus);echo '***';
        print_r($buyer_china);echo '***';
        print_r($buyer_oversea);echo '***';
        print_r($buyer_fba);exit;*/

        $list = [];
        foreach ($skus as $val){
               if(isset($buyer_china[$val['sku']])){
                    $list[$val['sku']][]=$buyer_china[$val['sku']];
               }
               if(isset($buyer_oversea[$val['sku']])){
                    $list[$val['sku']][]=$buyer_oversea[$val['sku']];
               }
               if(isset($buyer_fba[$val['sku']])){
                    $list[$val['sku']][]=$buyer_fba[$val['sku']];
               }
               if(empty($list[$val['sku']])){
                    $list[$val['sku']]=array();
               }
        }


        //Vhelper::dump($list);
        return $list?$list:'没有数据了';

        //Vhelper::dump($skus->createCommand()->getRawSql());
        //Vhelper::dump($skus);

                
    }
    /**
     * 供应商信息整合
     */
    public function actionIntegration()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $supplierInfo = Supplier::find()->select('supplier_code,first_cooperation_time')
            ->where(['status'=>1])
            ->asArray()->all();

        foreach ($supplierInfo as $key => $value) {
            //首次合作时间（月）
            $cooperation_time = !empty($value['first_cooperation_time']) ? ceil((time()-strtotime($value['first_cooperation_time']))/2592000) : 0;

            //最近采购时间
            $purOrder = PurchaseOrder::find()
                ->andFilterWhere(['supplier_code'=>$value['supplier_code']])
                ->andFilterWhere(['NOT IN','purchas_status',[1,2,4,10]])
                ->orderBy('created_at DESC')->one();
            $purchase_time = !empty($purOrder) ? $purOrder->created_at : '';

            //累计合作金额
            $cooperation_price = Vhelper::getSupplierPurchaseNum($value['supplier_code']);

            //有效sku数量
            $sku_num = SupplierUpdateApply::getProduct($value['supplier_code']);

            $model = Supplier::findOne(['supplier_code'=>$value['supplier_code']]);
            $model->cooperation_time = $cooperation_time;
            $model->purchase_time = $purchase_time;
            $model->cooperation_price = $cooperation_price;
            $model->sku_num = $sku_num;
            $status = $model->save();
            if ($status) {
                $data[] = $value['supplier_code'];
            }
        }
        $json = json_encode($data);
        // vd($json);
    }


    /**
     * 推送 供应商验货验厂 数据
     * @return string
     */
    public function actionSupplierCheck()
    {
        $curl   = new curl\Curl();
        $url    = Yii::$app->params['server_ip'] . '/index.php/purchases/purSupplierCheckToMysql';

        // 前一天添加的备注也要推送过去   type:0.普通备注
        $subQuery = (new yii\db\Query())
            ->select('check_id')
            ->from(SupplierCheckNote::tableName())
            ->where(['>=','create_time',date('Y-m-d 00:00:00',strtotime('-2 days'))])
            ->andWhere(['type' => 0])
            ->groupBy('check_id');

        // 查询要推送的  采购单号
        $whereStr1 = "is_push = 0 AND judgment_results !=0 AND judgment_results IS NOT NULL AND pur_number != '' AND pur_number IS NOT NULL";
        $whereStr2 = "judgment_results !=0 AND judgment_results IS NOT NULL AND pur_number != '' AND pur_number IS NOT NULL";
        $query = SupplierCheckSearch::find()
            ->select('id,supplier_code,pur_number,judgment_results,check_code,check_type,group')
            ->where($whereStr1)
            ->orWhere(['and',$whereStr2,['in','id',$subQuery]])
            ->limit(100)
            ->createCommand()
            ->queryAll();

        $supplierCheckList = [];
        foreach($query as $value){
            $id             = $value['id'];

            $checkList['check_id']              = $id;
            $checkList['pur_number']            = '';
            $checkList['judgment_results']      = $value['judgment_results'];// 判定结果  0.待确认,1.合格,2.不合格

            // 供货商验厂-备注（结果评价备注）
            $checkNoteList      = SupplierCheckNote::getAuditNote(['check_id' => $id]);

            $check_note_list_tmp = '';
            if($checkNoteList){
                $check_note_list_tmp = [];
                foreach($checkNoteList as $checkNote){
                    $check_note_list_tmp = $checkNote['role'] . '评价：' . $checkNote['check_note'];
                }
            }
            $checkList['evaluation'] = $check_note_list_tmp;


            // 供货商验厂-备注（正常备注）
            $check_note_list_tmp = '';
            $checkNoteList = SupplierCheckNote::getSupplierCheckNote($id);
            if ($checkNoteList) {
                $check_note_list_tmp = [];
                foreach ($checkNoteList as $checkNote) {
                    $check_note_list_tmp[] = $checkNote['create_user'] . '：' . $checkNote['check_note'];
                }
                $check_note_list_tmp = implode('&',$check_note_list_tmp);
            }
            $checkList['remark'] = $check_note_list_tmp;


            // 判断是否有多个采购单号，有多个采购单号则拆分成单个
            $pur_number     = trim($value['pur_number']);
            if($pur_number AND strpos($pur_number,',') !== false){
                $pur_number_list = explode(',',$pur_number);

                foreach($pur_number_list as $now_pur_number ){
                    $checkList['pur_number']    = trim($now_pur_number);
                    $supplierCheckList[]        = $checkList;
                }
            }else{
                $checkList['pur_number']    = $pur_number;
                $supplierCheckList[]        = $checkList;
            }
        }

        $supplierCheckListTmp = array_column($supplierCheckList,'check_id','pur_number');

        if(empty($supplierCheckList)){
            echo 'Not found results';
            exit;
        }

        try {
            // 执行推送数据
            $s = $curl->setPostParams([
                'purchase_check' => Json::encode($supplierCheckList),
                'token'         => Json::encode(Vhelper::stockAuth()),
            ])->post($url);

            //验证json
            $sb = Vhelper::is_json($s);
            if(!$sb)
            {
                echo '请检查json'."\r\n";
                exit($s);
            } else {
                $date = date('Y-m-d H:i:s');

                // 回写推送结果
                $_result = Json::decode($s);
                if ($_result['success_list'] && !empty($_result['success_list'])) {
                    foreach ($_result['success_list'] as $value_po) {
                        $check_id = isset($supplierCheckListTmp[$value_po])?$supplierCheckListTmp[$value_po]:0;// 根据PO单号找到 Check单号
                        if($check_id){
                            $model_check    = SupplierCheck::findOne(['id' => $check_id]);
                            $model_check->is_push   = 1;// 推送结果
                            $model_check->push_time = $date;// 推送时间
                            $model_check->save();
                        }
                    }

                    exit('推送成功，个数 '.count($supplierCheckList));
                } else {
                    Vhelper::dump($s);
                }
            }
        } catch (\Exception $e) {

            exit('发生了错误');
        }
    }

}
