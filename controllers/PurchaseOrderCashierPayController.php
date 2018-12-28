<?php
namespace app\controllers;

use app\models\PurchaseOrderPayUfxfuiou;
use app\models\TablesChangeLog;
use app\models\UfxFuiou;
use app\models\UfxfuiouPayDetail;
use app\models\UfxfuiouRequestLog;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\Json;
use yii\filters\VerbFilter;
use m35\thecsv\theCsv;

use app\models\User;
use app\config\Vhelper;

use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;


use app\models\PurchaseOrder;
use app\models\PurchaseOrdersV2;
use app\models\PurchaseLog;
use app\models\PurchaseNote;

use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPayDetail;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderPaySearch;

use app\models\AlibabaAccount;
use app\models\BankCardManagement;
use app\models\PurchaseOrderPayWater;
use app\models\SupplierPaymentAccount;
use app\models\PurchasePayForm;
use app\models\Template;
use app\models\PurchaseCompact;
use app\models\OrderPayDemandMap;
use app\models\PlatformSummary;
use app\models\PurchaseDemand;
use app\models\PurchaseOrderItems;

class PurchaseOrderCashierPayController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $args = Yii::$app->request->queryParams;
        $searchModel = new PurchaseOrderPaySearch();
        $args['source'] = isset($args['source']) ? (int)$args['source'] : 1;
        if(in_array($args['source'], [1, 2])) {
            $dataProvider = $searchModel->search2($args);
            if(isset($args['order_account'])){
                $searchModel->order_account = $args['order_account'];
            }
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'source' => $searchModel->source
            ]);
        } else {
            throw new \yii\web\NotFoundHttpException;
        }
    }

    // 出纳驳回请款单
    public function actionCashierReject()
    {
        $request = Yii::$app->request;
        if($request->isAjax) {
            $id = $request->post('id');
            $tran = Yii::$app->db->beginTransaction();
            try {
                $pay = PurchaseOrderPay::findOne($id);
                $order = PurchaseOrder::findOne(['pur_number' => $pay->pur_number]);
                $pay->pay_status = 12;
                $pay->payment_notice = $request->post('payment_notice');
                $order->pay_status = 12;
                PurchaseLog::addLog([
                    'pur_number' => $pay->pur_number,
                    'note' => '出纳驳回请款单>'.$id
                ]);
                $pay->save(false);
                $order->save(false);
                
                //海外仓NEW流程
                $demand_numbers = OrderPayDemandMap::find()->select('demand_number')->where(['requisition_number'=>$pay->requisition_number])->column();
                if ($demand_numbers) {
                    PlatformSummary::updateAll(['pay_status'=>12], ['in','demand_number',$demand_numbers]);
                    $message = "请款单出纳【驳回】\r\n驳回备注:{$pay->payment_notice}\r\n请款单:".$pay->requisition_number;
                    PurchaseOrderServices::writelog($demand_numbers, $message);
                }
                
                $tran->commit();
                return json_encode(['error' => 0, 'message' => '操作成功']);
            } catch(\Exception $e) {
                $tran->rollback();
                return json_encode(['error' => 1, 'message' => '操作失败']);
            }
        }
    }

    /**
     * ajax请求获取银行信息
     * @return array|mixed|null|\yii\db\ActiveRecord
     */
    public function actionGetBank()
    {
        $id   = Yii::$app->request->get('id');
        $data = BaseServices::getBankCard($id);
        echo Json::encode($data);
    }
    /**
     * ajax请求获取供应商的支付帐号
     * @return array|mixed|null|\yii\db\ActiveRecord
     */
    public  function  actionGetSupplierPay()
    {
        $id   = Yii::$app->request->get();
        $data = SupplierPaymentAccount::find()->where(['supplier_code'=>$id['code'],'payment_method'=>$id['id']])->one();
        echo Json::encode($data);
    }

    /**
     * 导出数据到 excel 文件
     */
    public function actionExportCvs()
    {
        $start_ttt = time();
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $data = Yii::$app->request->get();

        $start = $data['start'];

        $end = $data['end'];

        $ids = $data['ids'];

        $type = $data['type'];

        $time = strtotime($end)-strtotime($start);

        $days = ceil(($time)/86400); // 时间相隔多少天

        if($days > 5) {
            Yii::$app->getSession()->setFlash('error','最多只能导 5 天的出纳付款单！',true);
            return $this->redirect(['index']);
        }

        $modelsList = array();// 支持多个合同的导出
        $freightList = array();//采购单运费及折扣
        $skus = array();       //获取当前勾选的sku
        $sku_old = array();
        if(isset($data['s'])) {

            $ids = explode(',', $ids);
            /*if(count($ids) > 1) {
                exit('合同的请款信息，目前只能一次导一个合同的');
            }*/
            $pays = PurchaseOrderPay::find()->where(['in', 'id', $ids])->orderBy('id desc')->all();

            foreach($pays as $pay) {

                $pos = PurchaseCompact::getPurNumbers($pay->pur_number);
                $in = '';
                foreach($pos as $p) {
                    $in .= ",'{$p}'";
                }
                $in = substr($in, 1, strlen($in));

                if(empty($in)) continue;//单号为空的跳过
                $sql = "SELECT
                      c.pur_number, c.warehouse_code, c.buyer,c.purchase_type,
                      
                      b.freight, b.discount,
                      
                      d.sku, d.name, d.price, d.ctq, d.items_totalprice,
                      g.supplier_name,
                      purchas_status,
                      arrival_quantity,
                      instock_qty_count
                  FROM `pur_purchase_order` AS c
                  LEFT JOIN `pur_purchase_order_pay_type` AS b ON c.pur_number = b.pur_number
                  LEFT JOIN `pur_purchase_order_items` AS d ON (c.pur_number = d.pur_number AND ctq>0 AND is_cancel=2)
                  
                  LEFT JOIN `pur_supplier` AS g ON c.supplier_code = g.supplier_code
                    
                  LEFT JOIN (
                      SELECT h.sku, h.pur_number, SUM(h.instock_qty_count) AS instock_qty_count, SUM(h.arrival_quantity) AS arrival_quantity
                      FROM pur_warehouse_results AS h
                      GROUP BY h.pur_number, h.sku
                  ) AS i ON d.pur_number = i.pur_number AND d.sku = i.sku
                  
                   WHERE c.pur_number IN ({$in}) and c.purchas_status <> 10";

                $models = Yii::$app->db->createCommand($sql)->queryAll();



                foreach($models as &$value) {
                    $value['contract_number'] = $pay->pur_number;// 合同号
                    $value = array_merge($pay->attributes, $value);
                }

                if($models) $modelsList[] = $models;  
            }


        } else {
          if ($ids && $type == 2) { //勾选导出
            $ids = explode(',',$ids);
            foreach($ids as $id) {
                $model = PurchaseOrderPay::findOne($id); 
                if ($model->orderPayDemandMap) { //新采购单
                    //获取申请单号的运费及优惠
                    $orderPayDemandMap = $model->orderPayDemandMap;
                    $freight = '';
                    $discount = '';
                    foreach ($orderPayDemandMap as $ordermap){
                        $freight += $ordermap['freight'];
                        $discount += $ordermap['discount'];
                        $requisition_number = $ordermap['requisition_number'];
                        $data = ['freight'=>$freight,'discount'=>$discount];
                    }
                    $freightList[$requisition_number] = $data;

                    $sql = "SELECT
                          a.pur_number, a.requisition_number, a.pay_price, a.application_time, a.payer_time, a.pay_status, a.pay_type,
                            
                         # b.freight, b.discount,
                            
                          c.warehouse_code, c.buyer,c.purchase_type,
                            
                          d.sku, d.name, d.price, d.ctq, d.items_totalprice, 
                            
                          g.supplier_name,
                            
                          purchas_status,
                          arrival_quantity,
                          instock_qty_count

                      FROM `pur_purchase_order_pay` AS a
                      
                     # LEFT JOIN `pur_purchase_order_pay_type` AS b ON a.pur_number = b.pur_number #旧采购单表
                     # LEFT JOIN `pur_order_pay_demand_map` AS b ON a.requisition_number = b.requisition_number  #新采购单表
                      
                      LEFT JOIN `pur_purchase_order` AS c ON a.pur_number = c.pur_number 
                      LEFT JOIN `pur_purchase_order_items` AS d ON (a.pur_number = d.pur_number  AND ctq>=0 AND is_cancel=2)
                      
                      LEFT JOIN `pur_supplier` AS g ON a.supplier_code = g.supplier_code
                        
                      LEFT JOIN (
                          SELECT h.sku, h.pur_number, SUM(h.instock_qty_count) AS instock_qty_count, SUM(h.arrival_quantity) AS arrival_quantity
                          FROM pur_warehouse_results AS h
                          GROUP BY h.pur_number, h.sku
                      ) AS i ON d.pur_number = i.pur_number AND d.sku = i.sku";
                    if($type == 2) {

                        $sql .= " WHERE a.id = {$id}";

                    } else {

                        Yii::$app->getSession()->setFlash('error','不支持的导出方式',true);
                        return $this->redirect(['index']);

                    }

                    $sql .= " ORDER BY a.pur_number ASC";

                    $models = Yii::$app->db->createCommand($sql)->queryAll();

                    if($models) {
                        $serializeArrs = array_map('serialize',$models);
                        $uniqueArrs = array_merge(array_filter(array_filter($serializeArrs)));
                        $unserializeArrs = array_map('unserialize',$uniqueArrs);

                        $modelsList[] = $models;
                        //Vhelper::dump($models);
                    }   
                    continue; 
                } 

                if ($model->purchaseOrderPayType) { //旧采购单
                    $sql = "SELECT
                          a.pur_number, 
                          a.requisition_number, 
                          a.pay_price, 
                          a.application_time, 
                          a.payer_time, 
                          a.pay_status, 
                          a.pay_type,   
                          b.freight, 
                          b.discount,
                          c.warehouse_code, 
                          c.buyer,
                          c.purchase_type, 
                          d.sku, 
                          d.name, 
                          d.price, 
                          d.ctq, 
                          d.items_totalprice, 
                          g.supplier_name,  
                          purchas_status,
                          arrival_quantity,
                          instock_qty_count
                          FROM `pur_purchase_order_pay` AS a
                          LEFT JOIN `pur_purchase_order_pay_type` AS b ON a.pur_number = b.pur_number
                          LEFT JOIN `pur_purchase_order` AS c ON a.pur_number = c.pur_number 
                          LEFT JOIN `pur_purchase_order_items` AS d ON (a.pur_number = d.pur_number AND ctq>=0 AND is_cancel=2)
                          LEFT JOIN `pur_supplier` AS g ON a.supplier_code = g.supplier_code
                          LEFT JOIN (
                               SELECT 
                                  h.sku, h.pur_number, SUM(h.instock_qty_count) AS instock_qty_count, SUM(h.arrival_quantity) AS arrival_quantity
                               FROM pur_warehouse_results AS h
                               WHERE h.sku in(
                                     select 
                                        distinct n.sku 
                                     FROM pur_purchase_order_pay m 
                                     inner join pur_purchase_order_items n on m.pur_number=n.pur_number ";
                           if($type == 2){
                               $sql.="where m.id={$id}";
                           }elseif($type == 1){
                               $sql .= " WHERE m.pay_status IN (5) AND m.application_time BETWEEN '{$start}' AND '{$end}'";
                           }else{
                               Yii::$app->getSession()->setFlash('error','不支持的导出方式',true);
                               return $this->redirect(['index']);
                           }

                        $sql.=")
                               GROUP BY h.pur_number, h.sku
                          ) AS i ON d.pur_number = i.pur_number AND d.sku = i.sku";
                    if ($type == 1) {

                        $sql .= " WHERE a.pay_status IN (5) AND a.application_time BETWEEN '{$start}' AND '{$end}'";

                    } elseif($type == 2) {

                        $sql .= " WHERE a.id = {$id}";

                    } else {

                        Yii::$app->getSession()->setFlash('error','不支持的导出方式',true);
                        return $this->redirect(['index']);

                    }

                    $sql .= " ORDER BY a.pur_number ASC";
                    $models = Yii::$app->db->createCommand($sql)->queryAll();
                    $models_count = count($models);

                    if($models && $models_count >= 2) {
                        $serializeArrs = array_map('serialize',$models);
                        $uniqueArrs = array_merge(array_filter(array_filter($serializeArrs)));
                        $unserializeArrs = array_map('unserialize',$uniqueArrs);

                        $modelsList[] = $models;
                        $skus_old[] = array_merge(array_flip(array_flip(array_column($models,'sku'))));
                        foreach ($skus_old as $value) {
                            foreach ($value as $val) {
                                $sku_old[] = $val;
                            }
                        }
                    } else {
                      $modelsList[] = $models;
                    }    
                }
            }
          } else { //时间段导出
            $alreadyPaid = PurchaseOrderPay::$alreadyPaid; //已付款的订单
            $alreadyPaid_str = implode(',', $alreadyPaid);
            $sql = "SELECT
                           a.pur_number, a.requisition_number, a.pay_price, a.application_time, a.payer_time, a.pay_status, a.pay_type,
                             
                           b.freight, b.discount,
                             
                           c.warehouse_code, c.buyer,
                             
                           d.sku, d.name, d.price, d.ctq, d.items_totalprice, 
                             
                           g.supplier_name,
                             
                           purchas_status,
                           arrival_quantity,
                           instock_qty_count
                             
                       FROM `pur_purchase_order_pay` AS a
                       
                       LEFT JOIN `pur_purchase_order_pay_type` AS b ON a.pur_number = b.pur_number
                       
                       LEFT JOIN `pur_purchase_order` AS c ON a.pur_number = c.pur_number 
                       LEFT JOIN `pur_purchase_order_items` AS d ON (a.pur_number = d.pur_number  AND ctq>0 AND is_cancel=2)
                       
                       LEFT JOIN `pur_supplier` AS g ON a.supplier_code = g.supplier_code
                         
                       LEFT JOIN (
                           SELECT h.sku, h.pur_number, SUM(h.instock_qty_count) AS instock_qty_count, SUM(h.arrival_quantity) AS arrival_quantity
                           FROM pur_warehouse_results AS h
                           GROUP BY h.pur_number, h.sku
                       ) AS i ON d.pur_number = i.pur_number AND d.sku = i.sku";

                      if($type == 1) {
                           $sql .= " WHERE c.source=2 AND a.pay_status IN (" . $alreadyPaid_str . ") AND a.application_time BETWEEN '{$start}' AND '{$end}'";
                       } else {
                           Yii::$app->getSession()->setFlash('error','不支持的导出方式',true);
                           return $this->redirect(['index']);
                       }
                       $sql .= " ORDER BY a.pur_number ASC";
                       $models = Yii::$app->db->createCommand($sql)->queryAll();

                       if($models) $modelsList[] = $models;
          }  

        }


        if (empty($modelsList)) {
            Yii::$app->getSession()->setFlash('error','没有你所需要的数据！',true);
            return $this->redirect(['index']);
        }

        if(!isset($data['s']) && $type ==2) {
            //获取导出勾选的sku
            //旧采购单
            $rs = PurchaseOrderPay::find()
                  ->alias('o')
                  ->select('i.sku')
                  ->leftJoin(PurchaseOrderItems::tableName().' i','o.pur_number=i.pur_number')
                  ->where(['in','o.id',$ids])
                  ->asArray()
                  ->all();
            if (!empty($rs)) {
              $sku_old = array_column($rs,'sku');
              $sku_old = array_merge(array_flip(array_flip($sku_old)));
            } else {
              $sku_old = array();
            }
            //新采购单
            foreach ($modelsList as $value) {
                foreach ($value as $k => $v) {
                    $modelsReduce[] = $v;
                } 
            }

            $requisition_number_list = array_column($modelsReduce, 'requisition_number');
            $requisition_number_list = array_merge(array_flip(array_flip($requisition_number_list)));

            if (!empty($requisition_number_list)) {

                $res = PlatformSummary::find()
                       ->alias('p')
                       ->select('p.sku')
                       ->leftJoin(OrderPayDemandMap::tableName().' m','p.demand_number=m.demand_number')
                       ->where(['in','requisition_number',$requisition_number_list])
                       ->asArray()
                       ->all();
                if ($res) {
                  $sku_new = array_column($res,'sku');
                  $sku_new = array_merge(array_flip(array_flip($sku_new)));
                } else {
                  $sku_new = array();
                }
            }
            $skus = array_merge($sku_old,$sku_new);

            if (empty($skus)) {

                Yii::$app->getSession()->setFlash('error','没有你所需要的数据！',true);
                return $this->redirect(['index']);
            }

            //筛选出勾选sku的采购单
            foreach ($modelsList as &$models) {
                foreach ($models as $k1=>&$v1) {
                    //if(!empty($skus) && in_array($v1['sku'],$skus) && $v1['purchase_type'] == 2) continue;
                    if(!empty($skus) && in_array($v1['sku'],$skus)) continue;
                    array_splice($models,$k1,1); 
                }
            }
        }    

        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);

        // 报表头的输出
        $objectPHPExcel->getActiveSheet()->mergeCells("A1:V1"); // 合并单元格
        $objectPHPExcel->getActiveSheet()->setCellValue("A1",'出纳付款');  // 设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle("A1")->getFont()->setSize(24); // 设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle("A1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // 表格头的输出  采购需求建立时间、财务审核时间、财务付款时间
        $a = ['序号','合同号', '采购单号', '采购仓库', '采购员',
            '申请日期', '付款日期', 'SKU', '货品名称', '采购单价',
            '采购数量', '实际到货数量', '实际入库数量', '金额', '运费',
            '优惠', '优惠后', '供应商', '备注', '付款状态', '采购状态', '支付方式'];

        foreach($a as $kk => $vv) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($kk+65) . 3,$vv);
        }

        //设置表头居中
        $objectPHPExcel->getActiveSheet()->getStyle("A3:V3")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $n = 0;
        $shu_mu = 0;

        unset($models);

        // 统计采购单总金额
        $modelsListTotalAmount = array();
        $pur_numbers = array();

        foreach($modelsList as $models_1){
            foreach($models_1 as $key => $value_1){
                $pur_number = $value_1['pur_number'];
                $pur_numbers[] = $value_1['pur_number'];
                if($key == 0){// 一个合同号 多条记录的只求和一条
                    if(isset($modelsListTotalAmount[$pur_number])) continue;
                }


               $items_total_price = isset($value_1['items_totalprice']) ? $value_1['items_totalprice'] : 0;

               $freight = isset($value_1['freight']) ? $value_1['freight'] : 0;
               $discount = isset($value_1['discount']) ? $value_1['discount'] : 0;

               if(isset($modelsListTotalAmount[$pur_number])){
                   $modelsListTotalAmount[$pur_number]['item_total_real_money'] += $items_total_price;
               }else{
                   $modelsListTotalAmount[$pur_number]['item_total_real_money'] = $items_total_price;
                   $modelsListTotalAmount[$pur_number]['freight'] = $freight;
                   $modelsListTotalAmount[$pur_number]['discount'] = $discount;
               }
           }
       }

      $cancelInfo = \app\models\PurchaseOrderCancelSub::getCancelCtq($pur_numbers);
       
        // Vhelper::dump($modelsList);
       $warehouse_code = BaseServices::getWarehouseCode();
       $pay_status = PurchaseOrderServices::getPayStatus();
       $purchase_status = PurchaseOrderServices::getPurchaseStatusText();
       $pay_method = SupplierServices::getDefaultPaymentMethod('10');
       foreach($modelsList as $key=>$models){
           $pur_number_list = array_column($models,'pur_number');
           $pur_number_list = array_unique($pur_number_list);
           $pur_number_list_str = "'".implode("','",$pur_number_list)."'";
           unset($pur_number_list);

           // 获取所有采购单的 申请备注
           $sql_pay_note        = "SELECT `pay`.`pur_number`, `pay`.`applicant`, `pay`.`create_notice`, `user`.`username` 
                    FROM `pur_purchase_order_pay` `pay` 
                    LEFT JOIN `pur_user` `user` ON pay.applicant=user.id 
                    WHERE `pay`.`pur_number` IN ($pur_number_list_str)
                    AND (pay.create_notice!='' AND pay.create_notice IS NOT NULL)";
           $pur_pay_note_list   = Yii::$app->db->createCommand($sql_pay_note)->queryAll();
           $pur_pay_note_list_tmp = [];
           foreach($pur_pay_note_list as $note){
               if(isset($pur_pay_note_list_tmp[$note['pur_number']])){
                   $pur_pay_note_list_tmp[$note['pur_number']] .= "\r\n".$note['username'].':'.$note['create_notice'];
               }else{
                   $pur_pay_note_list_tmp[$note['pur_number']] = $note['username'].':'.$note['create_notice'];
               }
           }
           $pur_pay_note_list = $pur_pay_note_list_tmp;
           unset($pur_pay_note_list_tmp);

           // 采购单备注
           $sql_order_note       = "SELECT `note`.`pur_number`, `note`.`note`, `note`.`create_id`, `user`.`username` 
                    FROM `pur_purchase_note` `note` 
                    LEFT JOIN `pur_user` `user` ON  note.create_id=user.id 
                    WHERE `note`.`pur_number` IN ($pur_number_list_str)
                    AND (note.note!='' AND note.note IS NOT NULL)";
           $pur_order_note_list  = Yii::$app->db->createCommand($sql_order_note)->queryAll();
           $pur_order_note_list_tmp = [];
           foreach($pur_order_note_list as $note){
               if(isset($pur_order_note_list_tmp[$note['pur_number']])){
                   $pur_order_note_list_tmp[$note['pur_number']] .= "\r\n".$note['username'].':'.$note['note'];
               }else{
                   $pur_order_note_list_tmp[$note['pur_number']] = $note['username'].':'.$note['note'];
               }
           }
           $pur_order_note_list = $pur_order_note_list_tmp;
           unset($pur_order_note_list_tmp);

           // 输出明细
           foreach($models as $k => $v) {
              $ctq = !empty($v['ctq']) ? $v['ctq'] : '';
              if (!empty($cancelInfo[$v['pur_number']][$v['sku']]) && $cancelInfo[$v['pur_number']][$v['sku']]==$ctq) {
                continue;
              }
               $pur_number = $v['pur_number'];
               // 明细的输出
               $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+4) ,$n+1);
               $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+4) ,isset($v['contract_number'])?$v['contract_number']:'');
               $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4) ,$v['pur_number']);
               $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+4) ,(!empty($v['warehouse_code'] && isset($warehouse_code[$v['warehouse_code']])) ? $warehouse_code[$v['warehouse_code']]:''));
               $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4) ,(!empty($v['buyer']) ? $v['buyer'] : ''));
               $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4) ,$v['application_time']);
               $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+4) ,$v['payer_time']);
               $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4) ,(!empty($v['sku']) ? $v['sku'] : ''));
               $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+4) ,(!empty($v['name']) ? $v['name'] : ''));
               $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+4) ,(!empty($v['price']) ? $v['price'] : '')) ;
               $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+4) ,(!empty($v['ctq']) ? $v['ctq'] : ''));
               $objectPHPExcel->getActiveSheet()->setCellValue('L'.($n+4) ,($v['arrival_quantity']));
               $objectPHPExcel->getActiveSheet()->setCellValue('M'.($n+4) ,($v['instock_qty_count']));
               $objectPHPExcel->getActiveSheet()->setCellValue('N'.($n+4) ,(!empty($v['items_totalprice']) ? $v['items_totalprice'] : ''));     


               //运费及折扣
               if (isset($freightList[$v['requisition_number']])) {
                   $freight = $freightList[$v['requisition_number']]['freight'];
                   $discount = $freightList[$v['requisition_number']]['discount'];
               } else {
                   $freight = isset($v['freight']) ? $v['freight'] : 0;
                   $discount = isset($v['discount']) ? $v['discount'] : 0;
               }

               $objectPHPExcel->getActiveSheet()->setCellValue('O'.($n+4), $freight);
               $objectPHPExcel->getActiveSheet()->setCellValue('P'.($n+4), $discount);

               $real_money = $modelsListTotalAmount[$pur_number]['item_total_real_money'] + $freight - $discount;

               if (!empty($models[$k+1]['pur_number']) && ($models[$k]['pur_number'] == $models[$k+1]['pur_number'])) {
                   $shu_mu++;
               } else {
                   $objectPHPExcel->getActiveSheet()->mergeCells('O'.($n+4-$shu_mu) . ':' . 'O'.($n+4));
                   $objectPHPExcel->getActiveSheet()->mergeCells('P'.($n+4-$shu_mu) . ':' . 'P'.($n+4));
                   $objectPHPExcel->getActiveSheet()->mergeCells('Q'.($n+4-$shu_mu) . ':' . 'Q'.($n+4));
                   $shu_mu = 0;
               }

               $objectPHPExcel->getActiveSheet()->setCellValue('Q'.($n+4), $real_money);
               $objectPHPExcel->getActiveSheet()->setCellValue('R'.($n+4) ,$v['supplier_name']);

//               $where = ['pur_number'=>$pur_number];
//               $pay_note = \app\models\PurchaseOrderPay::getCreateNotice($where,3);
//               $pay_note = is_string($pay_note)?$pay_note:json_encode($pay_note);// 强制转成字符串
//               $order_note = !empty($v['pur_number']) ? PurchaseNote::getNote($v['pur_number']) : '';
//               $order_note = is_string($order_note)?$order_note:json_encode($order_note);// 强制转成字符串

               $pay_note    = isset($pur_pay_note_list[$pur_number])?$pur_pay_note_list[$pur_number]:'';
               $order_note  = isset($pur_order_note_list[$pur_number])?$pur_order_note_list[$pur_number]:'';

               $objectPHPExcel->getActiveSheet()->setCellValue('S'.($n+4) ,$order_note . $pay_note);
               $objectPHPExcel->getActiveSheet()->setCellValue('T'.($n+4) ,!empty($v['pay_status']) ? $pay_status[$v['pay_status']] : '');
               $objectPHPExcel->getActiveSheet()->setCellValue('U'.($n+4) ,!empty($v['purchas_status']) ? $purchase_status[$v['purchas_status']] : '');
               $objectPHPExcel->getActiveSheet()->setCellValue('V'.($n+4) ,!empty($v['pay_type']) ? $pay_method[$v['pay_type']] : '');

               $n = $n +1;   
           }   
           // 设置样式
           $objectPHPExcel->getActiveSheet()->getStyle('C2:N'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
           $objectPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
       }

       ob_end_clean();
       ob_start();

       header("Content-type: application/vnd.ms-excel;charset=UTF-8");
       header('Content-Type: application/vnd.ms-excel');
       header('Content-Disposition:attachment;filename="'.'出纳付款表-'.date("Y年m月j日").'.xls"');

       $objWriter = \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
       $objWriter->save('php://output');

       die;       

    }

    /*
     * 1688在线付款
     * 获取1688订单数据，访问1688收银台进行付款
     * @author WangWei
     * @date 2018-04-09 18:00:00
     * @desc 每次只能批量支付同一个申请人的单
     */
    public function actionOnlinePayment()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $account = new AlibabaAccount();
            $model = $account::findOne(['bind_account' => $post['applicant']]);

            !empty($model) or exit('申请人没有绑定开发者账户，请绑定后重试');

            $orderIdList = [];
            foreach($post['payment'] as $val) {
                $orderIdList[] = $val['order_number'];
            }
            $long = implode(',', $orderIdList);
            if(!$long) {

                exit('没有可支付的单号');

            }
            $param = [
                'orderIdList' => "[".$long."]",
                'access_token' => $model->access_token,
            ];
            $apiInfo = $account->api_list['urlGet'].$model->app_key;
            $args = [
                'param' => $param,
                'apiInfo' => $apiInfo,
                'appSecret' => $model->secret_key
            ];
            $param['_aop_signature'] = $account->makeSignature($args);
            $response = $account->executeApi($apiInfo, $param);
            if(isset($response['payUrl'])) {
                Yii::$app->response->redirect($response['payUrl'], 301)->send();
            } else {
                Vhelper::dump($response);
            }
        } else {
            $get = $request->get();
            $ids = $get['ids'];
            if(!$ids) {
                exit('参数错误');
            }
            $reg = "/^[0-9,]+$/";
            if(!preg_match($reg, $ids)) {
                exit('参数格式错误');
            }
            $ids = explode(',', $ids);
            $newIds = [];
            if(isset($_COOKIE['not-pay'])) {
                $notPay = $_COOKIE['not-pay'];
                $not = explode(',', $notPay);
                foreach($ids as $id) {
                    if(!in_array($id, $not)) {
                        $newIds[] = $id;
                    }
                }
            } else {
                $newIds = $ids;
            }

            $models = PurchaseOrderPay::find()->where(['in', 'id', $newIds])->all();

            !empty($models) or exit('没有找到数据');

            $applicants = [];
            $list = [];

            // 拍单号验证
            $reg_order = "/^[0-9]{10,25}$/";

            // 组建本地数据
            foreach($models as $m) {
                $applicants[] = $m->applicant;
                $subData      = $m->attributes;

                $order_number = !empty($m->purchaseOrderPayType) ? $m->purchaseOrderPayType->platform_order_number : '';

                if(!$order_number) {
                    $order_number = !empty($m->orderOrders) ? $m->orderOrders->order_number : '';
                }

                // 拍单号存在空格
                $order_number = trim($order_number);

                // 踢出拍单号异常的数据
                if(!preg_match($reg_order, $order_number)) {
                    continue;
                }

                $subData['order_number'] = $order_number;


                if( (stripos($m->pur_number,'FBA') !== false) AND $m->pay_category == 30 ){// FBA 批量付款的请款单 运费、优惠金额读取订单
                    $freight = !empty($m->purchaseOrderPayType) ? $m->purchaseOrderPayType->freight : 0;

                    $discount = !empty($m->purchaseOrderPayType) ? $m->purchaseOrderPayType->discount : 0;

                    $subData['order_freight'] = $freight;

                    $subData['order_discount'] = $discount;
                }else{
                    // 修改 运费、优惠计算 方法 @author Jolon @date 2018-10-13 13:32
                    $freight = $discount = 0;
                    $orderPayDemandMap = $m->orderPayDemandMap;
                    foreach($orderPayDemandMap as $ordermap){// 参照 PurchaseOrderPay::getNewPrice()
                        $freight  += $ordermap['freight'];
                        $discount += $ordermap['discount'];
                    }
                    $subData['order_freight']  = $freight;
                    $subData['order_discount'] = $discount;
                }

                $supplier     = $m->supplier;              // 供应商信息




                $orderAccount = $m->purchaseOrderAccount;  // 订单网采账号
                $subData['supplier_name'] = $supplier ? $supplier->supplier_name : '';
                if($orderAccount) {
                    $subData['buyer_account'] = $orderAccount->account ? $orderAccount->account : '未设置';
                } else {
                    $subData['buyer_account'] = '未设置';
                }



                $list[] = $subData;
            }

            if(isset($get['debug'])) {
                Vhelper::dump($list);
            }


            $res = Vhelper::isSameData($applicants);
            $res or exit('只能批量支付同一个申请人的请款数据');
            $list or exit('没有可支付的单，可能是这些单的拍单号异常');

            // 拉取1688数据
            $account = new AlibabaAccount();
            $applicant = $applicants[0]; // 申请人
            $model = $account::findOne(['bind_account' => $applicant]);
            !empty($model) or exit('申请人没有绑定开发者账户，请绑定后重试');
            foreach($list as &$val) {
                $param = [
                    'webSite' => '1688',
                    'orderId' => $val['order_number'],
                    'access_token' => $model->access_token,
                    'includeFields' => 'OrderInvoice' // 只获取订单的发票信息
                ];
                $apiInfo = $account->api_list['buyerView'].$model->app_key;
                $args = [
                    'param' => $param,
                    'apiInfo' => $apiInfo,
                    'appSecret' => $model->secret_key
                ];
                $param['_aop_signature'] = $account->makeSignature($args);
                $response = $account->executeApi($apiInfo, $param);
                if(isset($response['result']) && isset($response['result']['baseInfo'])) {
                    $val['alibaba']['result'] = $response['result']['baseInfo'];
                } else {
                    $val['alibaba'] = '错误：'.json_encode($response);
                }
            }
            return $this->render('batch-payment', ['list' => $list, 'applicant' => $applicant]);
        }

    }

    /*
     * 1688超级卖家在线付款
     * 获取1688订单数据，访问1688收银台进行付款
     * @author WangWei
     * @date 2018-07-04 10:00:00
     * @desc 可以同时支付所有子账号的单
     */
    public function actionSuperOnlinePayment()
      {
          $request = Yii::$app->request;

          // 拉取1688数据
          $account = new AlibabaAccount();
          $model = $account::findOne(['account' => 'yibaisuperbuyers']);

          if(empty($model))
              exit('超级买家账号，不存在');

          if($request->isPost) {
              $post = $request->post();
              $orderIdList = [];
              foreach($post['payment'] as $val) {
                  $orderIdList[] = $val['order_number'];
              }
              $long = implode(',', $orderIdList);
              if(!$long) {
                  exit('没有可支付的单号');
              }
              $param = [
                  'orderIdList' => "[".$long."]",
                  'access_token' => $model->access_token,
              ];
              $apiInfo = $account->api_list['urlGet'].$model->app_key;
              $args = [
                  'param' => $param,
                  'apiInfo' => $apiInfo,
                  'appSecret' => $model->secret_key
              ];
              $param['_aop_signature'] = $account->makeSignature($args);
              $response = $account->executeApi($apiInfo, $param);
              if(isset($response['payUrl'])) {
                  Yii::$app->response->redirect($response['payUrl'], 301)->send();
              } else {
                  echo '<pre>';
                  print_r($response);
                  exit;
              }
          } else {

              $uid = Yii::$app->user->identity->id;

              $qx = \app\models\AlibabaZzh::find()->where(['user' => $uid, 'level' => 0])->one();
              if(empty($qx)) {
                  exit('你不是出纳，请联系出纳负责人开通');
              }

              $payable_users = \app\models\AlibabaZzh::getPayableIds($qx->id);

              if(!$payable_users) {
                  exit('你没有支付权限，请联系出纳负责人开通');
              }

              $get = $request->get();
              $ids = $get['ids'];
              if(!$ids) {
                  exit('参数错误');
              }
              $reg = "/^[0-9,]+$/";
              if(!preg_match($reg, $ids)) {
                  exit('参数格式错误');
              }
              $ids = explode(',', $ids);
              $newIds = [];
              if(isset($_COOKIE['not-pay'])) {
                  $notPay = $_COOKIE['not-pay'];
                  $not = explode(',', $notPay);
                  foreach($ids as $id) {
                      if(!in_array($id, $not)) {
                          $newIds[] = $id;
                      }
                  }
              } else {
                  $newIds = $ids;
              }

              $models = PurchaseOrderPay::find()->where(['in', 'id', $newIds])->all();

              !empty($models) or exit('没有找到数据');

              $list = [];

              // 拍单号验证
              $reg_order = "/^[0-9]{10,25}$/";

              // 组建本地数据
              foreach($models as $m) {

                  if(isset($get['debug'])) {
                      echo $m->applicant;
                      echo '<br/>';
                  }

                  // 踢出不在流中的数据
                  if(!in_array($m->applicant, $payable_users)) {
                      continue;
                  }

                  $subData = $m->attributes;
                  $payType = $m->purchaseOrderPayType;
                  $order_number = !empty($payType) ? $payType->platform_order_number : '';
                  $order_account = !empty($payType) ? $payType->purchase_acccount : '';

                  // 踢出非超级买家下的拍单
                  if($order_account !== 'yibaisuperbuyers') {
                      continue;
                  }
                  $subData['buyer_account'] = $order_account;

                  // 拍单号存在空格
                  $order_number = trim($order_number);

                  // 踢出拍单号异常的数据
                  if(!preg_match($reg_order, $order_number)) {
                      continue;
                  }
                  $subData['order_number'] = $order_number;
                  /* 注释该查询方式 @author Jolon @date 2018-11-05 11:32
                  $freight = !empty($payType) ? $payType->freight : 0;
                  $discount = !empty($payType) ? $payType->discount : 0;
                  $subData['order_freight'] = $freight;
                  $subData['order_discount'] = $discount;
                  */
                  // 修改 运费、优惠计算 方法 @author Jolon @date 2018-11-05 11:32
                  $price_list = PurchaseOrderPay::getPrice($m,false,2,true);// 直接返回显示的金额的值
                  $subData['order_freight']   = $price_list['freight'];
                  $subData['order_discount']  = $price_list['discount'];

                  $supplier = $m->supplier; // 供应商信息
                  $subData['supplier_name'] = $supplier ? $supplier->supplier_name : '';
                  $list[] = $subData;
              }

              if(isset($get['debug'])) {
                  Vhelper::dump($payable_users);
                  Vhelper::dump($list);
              }

              $list or exit('没有可支付的单，可能是这些单的拍单号异常');

              foreach($list as &$val) {
                  $param = [
                      'webSite' => '1688',
                      'orderId' => $val['order_number'],
                      'access_token' => $model->access_token,
                      'includeFields' => 'OrderInvoice' // 只获取订单的发票信息
                  ];
                  $apiInfo = $account->api_list['buyerView'].$model->app_key;
                  $args = [
                      'param' => $param,
                      'apiInfo' => $apiInfo,
                      'appSecret' => $model->secret_key
                  ];
                  $param['_aop_signature'] = $account->makeSignature($args);
                  $response = $account->executeApi($apiInfo, $param);
                  if(isset($response['result']) && isset($response['result']['baseInfo'])) {
                      $val['alibaba']['result'] = $response['result']['baseInfo'];
                  } else {
                      $val['alibaba'] = '错误：'.json_encode($response);
                  }
              }
              return $this->render('batch-payment', ['list' => $list, 'applicant' => 0]);
          }
      }      

    /******************合同流程相关的 开始*********************/
    // 单个付款操作
    public function actionView()
    {
        $request = Yii::$app->request;

        if($request->isPost) {
            $post = $request->post();
            if(isset($post['source']) && $post['source'] == 1) {
                $this->executeCompactPayment($post);
            } else {
                $data = $post['PurchaseOrderPay'];
                $this->executeNetworkPayment($data);
            }
        } else {
            $id = $request->get('id');
            $model = PurchaseOrderPay::findOne($id);
            $renderData = [];

            if(!$model) {
                exit('数据不存在');
            }
            if($model->source == 1 && preg_match('/HT/', $model->pur_number)) {

                $renderData['compact'] = PurchaseCompact::find()->where(['compact_number' => $model->pur_number])->one();
                $renderData['model'] = $model;
                $renderData['bank'] = BankCardManagement::find()->where(['status' => 1])->asArray()->all();
                $renderData['sk_bank'] = SupplierPaymentAccount::find()->where(['supplier_code' => $model->supplier_code, 'status' => 1])->one();

                $select_value = [];
                foreach ($renderData['bank'] as $bk => $bv) {
                    if ($renderData['compact']['is_drawback'] == 1 && $bv['id'] == 138) {
                        # 不退税的，我司付款信息中，账号简称默认为“上海富友”
                        $select_value = $bv;
                        unset($renderData['bank'][$bk]);
                        break;
                    } elseif ($renderData['compact']['is_drawback'] == 2 && $bv['id'] == 60) {
                        # 退税的，默认为“中国银行锦绣支行（尾号6681）”
                        $select_value = $bv;
                        unset($renderData['bank'][$bk]);
                        break;
                    }
                }
                array_unshift($renderData['bank'],$select_value);

                if($model->pay_category !== 10) {
                    $form = PurchasePayForm::find()->where(['pay_id' => $id])->one();
                    if(empty($form)) {
                        exit('采购员没有填写付款申请书');
                    }
                    $tpl = Template::findOne($form->tpl_id);
                    $renderData['form'] = $form;
                    $renderData['tplPath'] = $tpl->style_code;
                }
                return $this->render('compact-payment', $renderData);
            } else {
                $model = PurchaseOrderPay::find()->joinWith(['purchaseOrder', 'supplier'])->where(['pur_purchase_order_pay.id' => $id])->one();
                $itemsInfo = PurchaseOrderItems::find()->alias('poi')->joinWith('purchaseOrderTaxes')->where(['poi.pur_number'=>$model->pur_number])->asArray()->all();
                $taxes = array_column(array_column($itemsInfo, 'purchaseOrderTaxes'), 'taxes');


                //网采单退税的锦绣支行，不退税的上海富友
                // if($model->purchaseOrder['is_drawback']==2){
                //     $condition = ['status'=>1,'id'=>60];
                // }else{
                //     $condition = ['status' => 1,'id'=>54];
                // }
                if ( !empty($taxes) && !in_array(0.00,$taxes) && !in_array(0,$taxes) && !in_array(null,$taxes)) {
                    # 含税不为零
                    $is_taxes = true;
                } else if ( empty($taxes) || in_array(0.00,$taxes) || in_array(0,$taxes) || in_array(null,$taxes) ) {
                    # 存在含税为零的
                    $is_taxes = false;
                }

                if ($model->pay_type == 3 && $is_taxes==true ) {
                    //支付方式“银行卡转账”,开票点为“非0”时(存在含税信息、且含税信息不为零)
                    //默认“中国银行锦绣支行”
                    $condition = ['status' => 1,'id'=>60];
                } elseif ($model->pay_type == 3) {
                    //支付方式“银行卡转账”,开票点为0时,默认“上海富友”
                    $condition = ['status'=>1,'id'=>138];
                } elseif ($model->pay_type==2) {
                    # 支付方式“支付宝”,款账户信息默认“我司支付宝账号yibaisuperbuyers”
                    $condition = ['status'=>1,'id'=>143];
                }
                
                $bank  = BankCardManagement::find()->where($condition)->one();
                return $this->renderAjax('view', [
                    'model' => $model,
                    'bank'  => $bank,
                ]);
            }
        }
    }

    //出纳付款批量驳回
    public function actionBatchReject(){
        if (Yii::$app->request->isPost&&Yii::$app->request->isAjax)
        {
            $ids = Yii::$app->request->getBodyParam('ids');
            $payment_notice  = Yii::$app->request->getBodyParam('payment_notice');
            $source = Yii::$app->request->getBodyParam('source');

            $res = ['status'=>1,'msg'=>''];
            if($source == 1 && !empty($ids)) {
                // 驳回流程
                foreach ($ids as $id){
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $model = PurchaseOrderPay::findOne($id);
                        if($model && $model->pay_status !== 4) {
                            $transaction->rollback();
                            $res['status'] = 0;
                            $res['msg'] = $model->requisition_number.':本次支付的请款单不在待支付状态,不能驳回';
                            die(json_encode($res));
                        }
                        $pos = PurchaseCompact::getPurNumbers($model->pur_number);
                        if(empty($pos)) {
                            $tran->rollback();
                            $res['status'] = 0;
                            $res['msg'] = $model->requisition_number.':没有找到和订单的绑定关系';
                            die(json_encode($res));
                        }

                        $model->pay_status = 12;
                        $model->payment_notice = strip_tags($payment_notice);
                        $log = [
                            'pur_number' => $model->pur_number,
                            'note'       => $model->payer_time.' 在 '.$model->payer_time.' 驳回了请款单',
                        ];
                        PurchaseLog::addLog($log);
                        //新版海外仓-查看日志
                        if (!empty($model->orderPayDemandMap)) {
                            $demand_maps = $model->orderPayDemandMap;
                            foreach ($demand_maps as $mv) {
                                $message = "请款单出纳【驳回】\r\n驳回备注:{$model->payment_notice}\r\n请款单:".$model->requisition_number;
                                PurchaseOrderServices::writelog($mv['demand_number'], $message);
                            }
                        }
                        //海外仓NEW流程
                        $demand_numbers = OrderPayDemandMap::find()->select('demand_number')->where(['requisition_number'=>$model->requisition_number])->column();
                        if ($demand_numbers) {
                            PlatformSummary::updateAll(['pay_status'=>12], ['in','demand_number',$demand_numbers]);
                        }

                        PurchaseOrder::updateAll(['pay_status' => 12], ['pur_number' => $pos]);
                        $purordersv2 = PurchaseOrdersV2::findOne(['pur_number' => $pos]);
                        if($purordersv2) {
                            PurchaseOrdersV2::updateAll(['pay_status' => 12], ['pur_number' => $pos]);
                        }

                        //海外仓NEW流程
                        $demand_numbers = OrderPayDemandMap::find()->select('demand_number')->where(['requisition_number'=>$model->requisition_number])->column();
                        if ($demand_numbers) {
                            PlatformSummary::updateAll(['pay_status'=>12], ['in','demand_number',$demand_numbers]);
                        }

                        $model->save(false);
                        $transaction->commit();
                    } catch (Exception $e) {
                        $transaction->rollBack();
                        $res['status'] = 0;
                        $res['msg'] = '对不起,批量驳回失败';
                        die(json_encode($res));
                    }
                }
                $res['msg'] = '恭喜你,批量驳回成功';
                die(json_encode($res));
            } else {
                foreach ($ids as $id){
                    $tran = Yii::$app->db->beginTransaction();
                    try {
                        $pay = PurchaseOrderPay::findOne($id);
                        if($pay && $pay->pay_status !== 4) {
                            $tran->rollback();
                            $res['status'] = 0;
                            $res['msg'] = $pay->requisition_number.':本次支付的请款单不在待支付状态,不能驳回';
                            die(json_encode($res));
                        }

                        $order = PurchaseOrder::findOne(['pur_number' => $pay->pur_number]);

                        $pay->pay_status = 12;
                        $pay->payment_notice = strip_tags($payment_notice);
                        $order->pay_status = 12;

                        PurchaseLog::addLog([
                            'pur_number' => $pay->pur_number,
                            'note' => '出纳驳回请款单>'.$id
                        ]);
                        //新版海外仓-查看日志
                        if (!empty($pay->orderPayDemandMap)) {
                            $demand_maps = $pay->orderPayDemandMap;
                            foreach ($demand_maps as $mv) {
                                $message = "请款单出纳【驳回】\r\n驳回备注:{$pay->payment_notice}\r\n请款单:".$pay->requisition_number;
                                PurchaseOrderServices::writelog($mv['demand_number'], $message);
                            }
                        }
                        //海外仓NEW流程
                        $demand_numbers = OrderPayDemandMap::find()->select('demand_number')->where(['requisition_number'=>$pay->requisition_number])->column();
                        if ($demand_numbers) {
                            PlatformSummary::updateAll(['pay_status'=>12], ['in','demand_number',$demand_numbers]);
                        }

                        $pay->save(false);
                        $order->save(false);
                        $tran->commit();
                    } catch(\Exception $e) {
                        $tran->rollback();
                        $res['status'] = 0;
                        $res['msg'] = '对不起,批量驳回失败';
                        die(json_encode($res));
                    }
                }
                $res['msg'] = '恭喜你,批量驳回成功';
                die(json_encode($res));
            }
        }
    }

    // 上传回执单
    public function actionUploadReceipt()
    {
        if(Yii::$app->request->isPost) {
            $result = Vhelper::UploadIamgeAnsy('PurchaseOrderPay[images]', 'images[]');
            if($result) {
                return Json::encode($result);
            } else {
                return Json::encode(['error' => '上传出错了，请联系IT处理']);
            }
        }
    }
    /******************合同流程相关的 结束*********************/


    /*************** 付款相关流程（共4种付款入口） *******************/
    /**
     * 批量申请付款
     */
    public function actionBulkPayment()
    {
        Yii::$app->response->format = 'raw';
        $ids = Yii::$app->request->get('id');
        $model = PurchaseOrderPay::find()
            ->where(['in', 'pur_purchase_order_pay.id', $ids])
            ->andWhere(['pay_status' => [4, 6]])
            ->asArray()
            ->all();
        $pur_numbers = array_column($model,'pur_number');
        $is_drawbacks = PurchaseOrder::find()->select('is_drawback')->where(['pur_number'=>$pur_numbers])->distinct()->column();
        if(count($is_drawbacks)>1){
            Yii::$app->end('选中的采购单有多个退税属性，不可同时操作批量付款');
        }
        $pay_types  = PurchaseOrder::find()->select('pay_type')->where(['pur_number'=>$pur_numbers])->distinct()->column();
        if(count($pay_types)>1){
            Yii::$app->end('选中的采购单有多个支付方式，不可同时操作批量付款');
        }
        if($is_drawbacks[0]==2){
            $bank = BankCardManagement::find()->where(['id'=>78])->one();
        }else{
            $bank  = BankCardManagement::find()->one();
        }
        $models = new PurchaseOrderPay;
        if(Yii::$app->request->isPost) {
            $datas = Yii::$app->request->post()['PurchaseOrderPays'];
            $data  = Yii::$app->request->post()['PurchaseOrderPay'];
            $datas = Vhelper::changeData($datas);
            $pay_time_all = Yii::$app->request->post('pay_time_all');

            $transaction=\Yii::$app->db->beginTransaction();
            try {
                // 确认付款
                $first_note = '';
                $bankInfo = BankCardManagement::find()->where(['id'=>$data['pay_types']])->one();
                foreach($datas as $k=>$v) {
                    $model  = PurchaseOrderPay::findOne($v['id']);
                    $v['branch']               = !empty($bankInfo) ? $bankInfo->branch : '';
                    $v['pay_types']            = !empty($bankInfo) ? $bankInfo->id :0;
                    $v['account_abbreviation'] = !empty($bankInfo) ? $bankInfo->account_abbreviation :'';
                    $v['account_holder']       = !empty($bankInfo) ? $bankInfo->account_holder :'';

                    $model->pay_status         = $data['pay_status'] == 5 ? 5 : 6;
                    $model->payer              = Yii::$app->user->id;
                    $model->payer_time         = empty($pay_time_all)?date('Y-m-d H:i:s', time()):$pay_time_all;
                    $model->payment_notice     = $v['create_notice'];
                    $model->pay_account        = !empty($bankInfo) ? $bankInfo->id :0;
                    $model->pay_number         = !empty($bankInfo) ? $bankInfo->account_number : '';
                    $model->k3_account         = !empty($bankInfo) ? $bankInfo->k3_bank_account : '';
                    $model->pay_branch_bank    = !empty($bankInfo) ? $bankInfo->branch : '';
                    if($k == 0 && !empty($v['create_notice'])){
                        $first_note = $v['create_notice'];
                    }
                    //添加采购单日志
                    $s = [
                        'pur_number' => $v['pur_number'],
                        'note'       => '批量付款',
                    ];
                    PurchaseLog::addLog($s);
                    PurchaseOrder::updateAll(['pay_status' => $data['pay_status'] == 5 ? 5 : 6], ['pur_number' => $v['pur_number']]);
                    $model->save(false);
                    PurchaseOrderPayWater::saveOne($v);
                    //海外仓NEW流程
                    PlatformSummary::overseasPayUpdateDemandPaystatus($model->requisition_number, $model->payment_notice);
                    
                    //增加采购备注
                    $model_note = new PurchaseNote();
                    $model_note->pur_number = $v['pur_number'];
                    if($k>0 && empty($v['create_notice'])){
                        $model_note->note = $first_note;
                    }else{
                        $model_note->note = $v['create_notice'];
                    }
                    $model_note->create_time = date('Y-m-d H:i:s');
                    $model_note->create_id = Yii::$app->user->identity->id;
                    if(preg_match('/^PO/',$v['pur_number'])){
                        $model_note->purchase_type = 1;
                    }else if(preg_match('/^ABD/',$v['pur_number'])){
                        $model_note->purchase_type = 2;
                    }else if(preg_match('/^FBA/',$v['pur_number'])){
                        $model_note->purchase_type = 3;
                    }
                    $res = $model_note->save(false);
                    if($res){
                        //表修改日志-新增
                        $change_content = "insert:新增id值为{$model_note->id}的记录";
                        $change_data = [
                            'table_name' => 'pur_purchase_note', //变动的表名称
                            'change_type' => '1', //变动类型(1insert，2update，3delete)
                            'change_content' => $change_content, //变更内容
                        ];
                        TablesChangeLog::addLog($change_data);
                    }
                }
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你,付款成功');
                return $this->redirect(Yii::$app->request->referrer);
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','对不起，出错了');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
        if(!$model) {
            return '已付款过了不能重复付款';
        } else {
            return $this->renderAjax('bulk-payment', [
                'model' => $model,
                'models' => $models,
                'bank'  =>$bank,
            ]);
        }
    }

    /**
     * @title 1688在线付款后的确认付款操作
     * @author WangWei
     * @date 2018-04-13 14:00
     */
    public function actionAffirmPayment()
    {
        $request = Yii::$app->request;
        if($request->isAjax) {
            $ids = $request->post('ids');
            if(!$ids) {
                return json_encode([
                    'error' => 1,
                    'message' => '参数错误'
                ]);
            }
            $models = PurchaseOrderPay::find()->where(['in', 'id', $ids])->all();
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach($models as $mod) {
                    if(in_array($mod->pay_status, [5, 6])) {
                        continue;
                    }
                    $mod->pay_status = 5;
                    $mod->payer = Yii::$app->user->id;
                    $mod->payer_time = date('Y-m-d H:i:s', time());
                    $mod->payment_notice = '1688批量在线支付3.0';
                    $s = [
                        'pur_number' => $mod->pur_number,
                        'note' => '1688批量在线支付3.0>' . $mod->id,
                    ];
                    $payType = $mod->purchaseOrderPayType;
                    $order_account = !empty($payType) ? $payType->purchase_acccount : '';
                    $payWater = [
                        'pur_number' => $mod->pur_number,
                        'supplier_code' => $mod->supplier_code,
                        'pay_price' => $mod->pay_price,
                        'currency' => $mod->currency,
                        'account_abbreviation' => trim($order_account)
                    ];
                    PurchaseLog::addLog($s);
                    PurchaseOrder::updateAll(['pay_status' => 5], ['pur_number' => $mod->pur_number]);
                    PurchaseOrderPayWater::saveOneForAli($payWater);
                    $mod->save(false);
                }
                $transaction->commit();
                return json_encode([
                    'error' => 0,
                    'message' => '恭喜你，付款成功'
                ]);
            } catch (Exception $e) {
                $transaction->rollBack();
                return json_encode([
                    'error' => 1,
                    'message' => '对不起，出错了'
                ]);
            }
        }
    }

    /**
     * @title 合同单付款执行
     * @author WangWei
     * @date 2018-05-13 14:00
     */
    public function executeCompactPayment($data)
    {

        // 错误消息
        $errors = [];
        $model = PurchaseOrderPay::findOne($data['id']);
        if($model->pay_status !== 4) {
            $errors[] = [
                'type' => '状态错误',
                'message' => '本次支付的请款单不在待支付状态'
            ];
        }
        $pos = PurchaseCompact::getPurNumbers($model->pur_number);
        if(empty($pos)) {
            $errors[] = [
                'type' => '数据错误',
                'message' => "{$model->pur_number}，没有找到和订单的绑定关系"
            ];
        }
        $status = $data['status'];
        $compact = PurchaseCompact::find()->select('real_money')->where(['compact_number' => $model->pur_number])->one();
        if($status == 5) {
            // 付款流程
            $real_money = $compact->real_money;
            $has_pay = PurchaseOrderPay::getOrderPaidMoney($model->pur_number);
            $has_pay = $has_pay+$model->pay_price;
            $pay_status = $status;
            if(bccomp($real_money, $has_pay, 3) !== 0) {
                $pay_status = 6; // 部分付款
            }
            if(!empty($errors)) {
                Vhelper::dump($errors);
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $bankId = isset($data['PayWater']['bank_id']) ? $data['PayWater']['bank_id'] :0;
                $bankInfo = BankCardManagement::find()->select('id,branch,account_number,k3_bank_account')->where(['id'=>$bankId])->asArray()->one();
                $model->pay_status     = 5;
                $model->payer          = Yii::$app->user->id;
                $model->payer_time     = $data['payer_time'];
                $model->payment_notice = $data['payment_notice'];
                $model->real_pay_price = $data['real_pay_price'];
                $model->pay_account    = !empty($bankInfo)&&!empty($bankInfo['id']) ?  $bankInfo['id'] :0;
                $model->pay_number    = !empty($bankInfo)&&!empty($bankInfo['account_number']) ?  $bankInfo['account_number'] :'';
                $model->k3_account    = !empty($bankInfo)&&!empty($bankInfo['k3_bank_account']) ?  $bankInfo['k3_bank_account'] :'';
                $model->pay_branch_bank    = !empty($bankInfo)&&!empty($bankInfo['branch']) ?  $bankInfo['branch'] :'';
                if(!empty($data['images'])) {
                    $model->images = Json::encode($data['images']);
                }
                $log = [
                    'pur_number' => $model->pur_number,
                    'note'       => $model->payer_time.' 在 '.$model->payer_time.' 确认了付款',
                ];
                PurchaseLog::addLog($log);
                PurchaseOrder::updateAll(['pay_status' => $pay_status], ['pur_number' => $pos]);
                $model->save(false);
                PurchaseOrderPayWater::saveOneForCompact($data);
                
                //海外仓NEW流程
                PlatformSummary::overseasPayUpdateDemandPaystatus($model->requisition_number, $model->payment_notice);
                
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你，付款成功');
                return $this->redirect(['index']);
            } catch (Exception $e) {
                $transaction->rollBack();
                exit('error');
            }
        } elseif($status == 12) {
            // 驳回流程
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->pay_status = 12;
                $model->payment_notice = $data['payment_notice'];
                $log = [
                    'pur_number' => $model->pur_number,
                    'note'       => $model->payer_time.' 在 '.$model->payer_time.' 驳回了请款单',
                ];
                PurchaseLog::addLog($log);
                PurchaseOrder::updateAll(['pay_status' => 12], ['pur_number' => $pos]);
                $purordersv2 = PurchaseOrdersV2::findOne(['pur_number' => $pos]);
                if($purordersv2) {
                    PurchaseOrdersV2::updateAll(['pay_status' => 12], ['pur_number' => $pos]);
                }
                $model->save(false);
                //海外仓NEW流程
                $demand_numbers = OrderPayDemandMap::find()->select('demand_number')->where(['requisition_number'=>$model->requisition_number])->column();
                if ($demand_numbers) {
                    PlatformSummary::updateAll(['pay_status'=>12], ['in','demand_number',$demand_numbers]);
                    $message = "请款单出纳【驳回】\r\n驳回备注:{$model->payment_notice}\r\n请款单:".$model->requisition_number;
                    PurchaseOrderServices::writelog($demand_numbers, $message);
                }
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你，驳回成功');
                return $this->redirect(['index']);
            } catch (Exception $e) {
                $transaction->rollBack();
                exit('error');
            }
        } else {
            $errors[] = [
                'type' => '系统错误',
                'message' => '未知的操作类型'
            ];
        }
        if(!empty($errors)) {
            echo json_encode($errors);
            exit;
        }
    }

    /**
     * 其它采购单付款执行
     */
    public function executeNetworkPayment($data)
    {
        $id = $data['id'];
        $pay_status = isset($data['pay_status']) ? $data['pay_status'] : 5;
        $model = PurchaseOrderPay::findOne($id);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $bankInfo = BankCardManagement::find()->where(['id'=>$data['pay_type']])->asArray()->one();
            $data['account_abbreviation'] = !empty($bankInfo)&&!empty($bankInfo['account_abbreviation']) ?  $bankInfo['account_abbreviation'] :'';
            $model->pay_status     = $pay_status;
            $model->payer          = Yii::$app->user->id;
            $model->payer_time     = date('Y-m-d H:i:s', time());
            $model->payment_notice = $data['payment_notice'];
            $model->pay_account    = !empty($bankInfo)&&!empty($bankInfo['id']) ?  $bankInfo['id'] :0;
            $model->pay_number    = !empty($bankInfo)&&!empty($bankInfo['account_number']) ?  $bankInfo['account_number'] :'';
            $model->k3_account    = !empty($bankInfo)&&!empty($bankInfo['k3_bank_account']) ?  $bankInfo['k3_bank_account'] :'';
            $model->pay_branch_bank    = !empty($bankInfo)&&!empty($bankInfo['branch']) ?  $bankInfo['branch'] :'';
            $s = [
                'pur_number' => $model->pur_number,
                'note'       => '确认付款',
            ];
            PurchaseLog::addLog($s);
            PurchaseOrder::updateAll(['pay_status' => $pay_status], ['pur_number' => $data['pur_number']]);
            $model->save(false);
            PurchaseOrderPayWater::saveOne($data);
            //海外仓NEW流程
            PlatformSummary::overseasPayUpdateDemandPaystatus($model->requisition_number, $model->payment_notice);
            $transaction->commit();
            Yii::$app->getSession()->setFlash('success','恭喜你,付款成功');
            return $this->redirect(Yii::$app->request->referrer);
        } catch (Exception $e) {
            $transaction->rollBack();
        }
    }

    //富友在线支付;
    public function actionUfxfuiouPay(){
        if(Yii::$app->request->isAjax){
            $ids = Yii::$app->request->getQueryParam('ids','');
            $checkInfo = PurchaseOrderPay::checkPayApplyDatas($ids);
            if($checkInfo['status']=='error'){
                echo $checkInfo['message'];
                Yii::$app->end();
            }
            $payData = PurchaseOrderPay::find()->where(['id'=>$ids])->all();
            $bank = BankCardManagement::find()->where(['id'=>138,'status'=>1])->one();
            if(empty($bank)){
                $bank = BankCardManagement::find()->where(['status'=>1])->one();
            }
            if($checkInfo['source']==2){
                return $this->renderAjax('fuiou_pay',['model'=>$payData,'ids'=>$ids,'is_drawback'=>$checkInfo['is_drawback'],'bank'=>$bank]);
            }elseif($checkInfo['source']==1){
                return $this->renderAjax('fuiou_pay_compact',['model'=>$payData,'ids'=>$ids,'is_drawback'=>$checkInfo['is_drawback'],'bank'=>$bank]);
            }else{
                echo '请款类型异常,请联系技术查看';
                Yii::$app->end();
            }
        }

        if(Yii::$app->request->isPost){
            $fuiouPayDatas = Yii::$app->request->post();
            $fuiouPay = UfxFuiou::bankCardPay($fuiouPayDatas);
            if(isset($fuiouPay['status'])&&$fuiouPay['status']=='success'){
               $result =  PurchaseOrderPay::saveFuiouPayResult($fuiouPayDatas,$fuiouPay);
               Yii::$app->session->setFlash($result['status'],$result['message']);
               return $this->redirect(Yii::$app->request->referrer);
            }else{
                Yii::$app->session->setFlash('warning',isset($fuiouPay['message']) ? $fuiouPay['message']: '付款异常，请联系技术部解决');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
    }

    public function actionGetFuiouPayInfo(){
        if(Yii::$app->request->isAjax&&Yii::$app->request->isGet){
            $requisition_number = Yii::$app->request->getQueryParam('requisition_number','');
            if(empty($requisition_number)){
                Yii::$app->end('确实必要参数');
            }
            $pur_tran_no  = PurchaseOrderPayUfxfuiou::find()->select('pur_tran_num')->where(['requisition_number'=>$requisition_number,'status'=>1])->scalar();
            if(!$pur_tran_no){
                Yii::$app->end('当前付款申请不是富友付款');
            }
            $requisition_numbers = PurchaseOrderPayUfxfuiou::find()->select('requisition_number')->where(['pur_tran_num'=>$pur_tran_no,'status'=>1])->column();
            if(empty($requisition_numbers)){
                Yii::$app->end('富友绑定关系异常');
            }
            $pay_info = PurchaseOrderPay::find()->select('requisition_number,pur_number,pay_status')->where(['requisition_number'=>$requisition_numbers])->asArray()->all();
            if(empty($pay_info)){
                Yii::$app->end('付款信息异常');
            }
            $data = UfxFuiou::getTransferResult($pur_tran_no);
            if(empty($data['status']) || $data['status']=='error'){
                Yii::$app->end('接口请求失败'.$data['response']);
            }else{
                return $this->renderAjax('u-info',['data'=>$data,'pay_info'=>$pay_info,'tran'=>$pur_tran_no]);
            }
        }

        if(Yii::$app->request->isAjax&&Yii::$app->request->isPost){
            $refreshTranNo = Yii::$app->request->getBodyParam('tran_no','');
            if(empty($refreshTranNo)){
                Yii::$app->end('关键参数为空');
            }
            $response =  UfxFuiou::getTransferResult($refreshTranNo);
            $model = new UfxfuiouRequestLog();
            $model->create_time = date('Y-m-d H:i:s',time());
            $model->create_user_name = Yii::$app->user->identity->username;
            $model->request_response = !empty($response['response']) ? $response['response'] :'无回调数据';
            $model->post_params = '手动获取富友付款状态更新请款单状态';
            $model->type        = 3;
            $model->pur_tran_no = $refreshTranNo;
            $model->save();
            if(empty($response['status'])||$response['status']=='error'){
                Yii::$app->end('接口请求失败'.$response['response']);
            }else{
                if(empty($response['responseBody'])||empty($response['responseBody']['rspCode'])||$response['responseBody']['rspCode']!='0000'){
                    $message = empty($response['responseBody']['rspDesc']) ?'接口返回异常':$response['responseBody']['rspDesc'];
                    Yii::$app->end('接口请求失败'.$message);
                }else{
                    if(!empty($response['responseBody']['resultSet']['result']['inOutSt'])&&in_array($response['responseBody']['resultSet']['result']['inOutSt'],['5005','5007'])){
                        $update = $this->updatePayStatus($refreshTranNo,$response['responseBody']['resultSet']['result']);
                        Yii::$app->end($update['message']);
                    }else{
                        Yii::$app->end('富友付款申请未完结不能手动更新系统付款状态,'.$response['responseBody']['resultSet']['result']['inOutSt']);
                    }
                }
            }
        }
    }
    //根据商户流水号，富友请款状态更新请款单号状态
    protected function updatePayStatus($tran_no,$result){
        $requisition_numbers = PurchaseOrderPayUfxfuiou::find()->select('requisition_number')->where(['pur_tran_num'=>$tran_no,'status'=>1])->column();
        if(empty($requisition_numbers)){
            return ['status'=>'error','message'=>'无可更新的付款数据'];
        }
        $tran = Yii::$app->db->beginTransaction();
        try{
            //付款成功
            if(!empty($result['inOutSt'])&&$result['inOutSt']=='5005'){
                UfxfuiouPayDetail::updateAll([
                    'tranfer_result_code'=>empty($result['inOutSt']) ? '' : $result['inOutSt'],
                    'tranfer_result_reason'=>empty($result['reason']) ? '' : $result['reason'],
                    'tranfer_result_money'=>empty($result['amt']) ? '' : $result['amt'],
                    'status'=>5,
                    'pay_status'=>empty($result['inOutSt']) ? '' : $result['inOutSt'],
                    'ufxfuiou_tran_num'=>empty($result['fuiouTransNo']) ? '' : $result['fuiouTransNo'],
                ],['pur_tran_num'=>$tran_no]);
                \app\api\v1\models\PurchaseOrderPay::updateSuccess($requisition_numbers,$tran_no);
                $tran->commit();
                return ['status'=>'success','message'=>'付款成功状态更新成功'];
            }
            //付款失败
            if(!empty($result['inOutSt'])&&$result['inOutSt']=='5007'){
                UfxfuiouPayDetail::updateAll([
                    'tranfer_result_code'=>empty($result['inOutSt']) ? '' : $result['inOutSt'],
                    'tranfer_result_reason'=>empty($result['reason']) ? '' : $result['reason'],
                    'tranfer_result_money'=>empty($result['amt']) ? '' : $result['amt'],
                    'status'=>4,
                    'pay_status'=>empty($result['inOutSt']) ? '' : $result['inOutSt'],
                    'ufxfuiou_tran_num'=>empty($result['fuiouTransNo']) ? '' : $result['fuiouTransNo'],
                ],['pur_tran_num'=>$tran_no]);
                \app\api\v1\models\PurchaseOrderPay::updateFail($requisition_numbers,$tran_no);
                $tran->commit();
                return ['status'=>'success','message'=>'付款失败状态更新成功'];
            }
        }catch (Exception $e){
            $tran->rollBack();
            return ['status'=>'error','message'=>$e->getMessage()];
        }
    }



    
}
