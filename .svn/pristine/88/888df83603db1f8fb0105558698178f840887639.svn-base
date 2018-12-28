<?php

namespace app\controllers;

use app\models\TablesChangeLog;
use Yii;
use yii\helpers\Json;
use yii\filters\VerbFilter;
use m35\thecsv\theCsv;

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
     * 导出cvs
     */
    public function actionExportCvs1()
    {
        //以写入追加的方式打开
        $daterangepicker_start = Yii::$app->request->get('daterangepicker_start');
        $daterangepicker_end = Yii::$app->request->get('daterangepicker_end');
        $ids = Yii::$app->request->get('ids');
        $time_id = Yii::$app->request->get('time_id');
//        Vhelper::dump(Yii::$app->request->get());

        $time = strtotime($daterangepicker_end)-strtotime($daterangepicker_start);
        $cha=ceil(($time)/86400); //时间相隔多少天

        if ($cha > 5) {
            Yii::$app->getSession()->setFlash('error','最多只能导 5 天的出纳付款单！！',true);
            return $this->redirect(['index']);
        }

        $sql = "SELECT
                    `pur_purchase_order_pay`.*,`pur_purchase_order`.`warehouse_code`,`pur_purchase_order`.`buyer`,`pur_purchase_order_items`.`sku`,`pur_purchase_order_items`.`name`,`pur_purchase_order_items`.`price`,`pur_purchase_order_items`.`ctq`,`pur_purchase_order_items`.`items_totalprice`,`pur_purchase_order_ship`.`freight`,`pur_purchase_discount`.`discount_price`,`pur_purchase_discount`.`total_price`,`pur_supplier`.`supplier_name`,`purchas_status`,`arrival_quantity`,`instock_qty_count`  
                FROM
                    `pur_purchase_order_pay`
                LEFT JOIN `pur_purchase_order_items` ON `pur_purchase_order_pay`.`pur_number` = `pur_purchase_order_items`.`pur_number`
                LEFT JOIN `pur_purchase_order` ON `pur_purchase_order_pay`.`pur_number` = `pur_purchase_order`.`pur_number`
                LEFT JOIN `pur_supplier` ON `pur_purchase_order_pay`.`supplier_code` = `pur_supplier`.`supplier_code`
                LEFT JOIN `pur_purchase_order_ship` ON `pur_purchase_order_pay`.`pur_number` = `pur_purchase_order_ship`.`pur_number`
                LEFT JOIN `pur_purchase_discount` ON `pur_purchase_order_pay`.`pur_number` = `pur_purchase_discount`.`pur_number`
               LEFT JOIN (
                  SELECT
                    d.sku,
                    d.pur_number,
                    SUM(d.instock_qty_count) AS instock_qty_count,
                    SUM(d.arrival_quantity) AS arrival_quantity
                  FROM
                    pur_warehouse_results AS d
                  GROUP BY
                    d.pur_number,d.sku
                ) AS e ON pur_purchase_order_items.pur_number = e.pur_number AND pur_purchase_order_items.sku=e.sku
                WHERE ";

        if ($time_id == 1) {
            $sql .= "(`pur_purchase_order_pay`.`pay_status` IN (5)) AND `pur_purchase_order_pay`.`application_time` BETWEEN '$daterangepicker_start'
                AND '$daterangepicker_end'";
        } elseif ($time_id == 2) {
            $sql .= "(`pur_purchase_order_pay`.`id` IN ( $ids )) ";
        } else {
            Yii::$app->getSession()->setFlash('error','无效数据',true);
            return $this->redirect(['index']);
        }

        $model = Yii::$app->db->createCommand($sql)->queryAll();
        if (empty($model)) {
            Yii::$app->getSession()->setFlash('error','没有你所需要的数据！！',true);
            return $this->redirect(['index']);
        }



//        $model = Yii::$app->db->createCommand($sql)->getRawSql();
//        Vhelper::dump($model);
        $table = [
            '采购单号',
            '采购仓库',
            '采购员',
            '申请日期',
            '付款日期',
            'SKU',
            '货品名称',
            '采购单价',
            '采购数量',
            '实际到货数量',
            '实际入库数量',
            '金额',
            '运费',
            '优惠',
            '优惠后',
            '供应商',
            '备注',
            '付款状态',
            '采购状态',
            '支付方式',
            //实际到货数量（到货数量）、实际入库数量（上架数量）
        ];
        $table_head = [];
        foreach($model as $k=>$v)
        {
            $table_head[$k][] = $v['pur_number'];
            $table_head[$k][] = !empty($v['warehouse_code']) ? BaseServices::getWarehouseCode($v['warehouse_code']):'';
            $table_head[$k][] = !empty($v['buyer']) ? $v['buyer'] : '';
            $table_head[$k][] = $v['application_time'];
            $table_head[$k][] = $v['payer_time'];
            $table_head[$k][] = !empty($v['sku']) ? $v['sku'] : '';
            $table_head[$k][] = !empty($v['name']) ? $v['name'] : '';
            $table_head[$k][] = !empty($v['price']) ? $v['price'] : '';
            $table_head[$k][] = !empty($v['ctq']) ? $v['ctq'] : '';
            $table_head[$k][] = $v['arrival_quantity'];
            $table_head[$k][] = $v['instock_qty_count'];
            $table_head[$k][] = !empty($v['items_totalprice']) ? $v['items_totalprice'] : '';
            $table_head[$k][] = !empty($v['freight']) ? $v['freight'] : '';
            $table_head[$k][] = !empty($v['discount_price']) ? $v['discount_price'] : '';
            $table_head[$k][] = !empty($v['total_price']) ? $v['total_price'] : '';
            $table_head[$k][] = $v['supplier_name'];
            $table_head[$k][] = !empty($v['pur_number']) ? PurchaseNote::getNote($v['pur_number']) : '';
            $table_head[$k][] = !empty($v['pay_status']) ? strip_tags(PurchaseOrderServices::getPayStatus($v['pay_status'])) : '';
            $table_head[$k][] = !empty($v['purchas_status']) ? strip_tags(PurchaseOrderServices::getPurchaseStatus($v['purchas_status'])) : '';
            $table_head[$k][] = !empty($v['pay_type']) ? SupplierServices::getDefaultPaymentMethod($v['pay_type']) : '';

        }


        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
//            'data' => Vhelper::ThereArrayTwo($table_head),
            'name' => '出纳付款单--' . date('Y-m-d') . '.csv',  //Excel表名
        ]);
        die;
    }

    /**
     * 导出数据到 excel 文件
     */
    public function actionExportCvs()
    {
        set_time_limit(0);

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

        if(isset($data['s'])) {
            $ids = explode(',', $ids);
            if(count($ids) > 1) {
                exit('合同的请款信息，目前只能一次导一个合同的');
            }
            $pays = PurchaseOrderPay::find()->where(['in', 'id', $ids])->all();

            foreach($pays as $pay) {

                $pos = PurchaseCompact::getPurNumbers($pay->pur_number);
                $in = '';
                foreach($pos as $p) {
                    $in .= ",'{$p}'";
                }
                $in = substr($in, 1, strlen($in));

                $sql = "SELECT
                      c.pur_number, c.warehouse_code, c.buyer,
                      
                  
                      b.freight, b.discount,
                      
                      d.sku, d.name, d.price, d.ctq, d.items_totalprice,
                      g.supplier_name,
                      purchas_status,
                      arrival_quantity,
                      instock_qty_count
                  FROM `pur_purchase_order` AS c
                  LEFT JOIN `pur_purchase_order_pay_type` AS b ON c.pur_number = b.pur_number
                  
                  LEFT JOIN `pur_purchase_order_items` AS d ON c.pur_number = d.pur_number
                  
                  LEFT JOIN `pur_supplier` AS g ON c.supplier_code = g.supplier_code
                    
                  LEFT JOIN (
                      SELECT h.sku, h.pur_number, SUM(h.instock_qty_count) AS instock_qty_count, SUM(h.arrival_quantity) AS arrival_quantity
                      FROM pur_warehouse_results AS h
                      GROUP BY h.pur_number, h.sku
                  ) AS i ON d.pur_number = i.pur_number AND d.sku = i.sku
                  
                   WHERE c.pur_number IN ({$in})";

                $models = Yii::$app->db->createCommand($sql)->queryAll();



                foreach($models as &$value) {
                    $value = array_merge($pay->attributes, $value);
                }
            }



        } else {

            $sql = "SELECT
                  a.pur_number, a.pay_price, a.application_time, a.payer_time, a.pay_status, a.pay_type,
                    
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
              LEFT JOIN `pur_purchase_order_items` AS d ON a.pur_number = d.pur_number
              
              LEFT JOIN `pur_supplier` AS g ON a.supplier_code = g.supplier_code
                
              LEFT JOIN (
                  SELECT h.sku, h.pur_number, SUM(h.instock_qty_count) AS instock_qty_count, SUM(h.arrival_quantity) AS arrival_quantity
                  FROM pur_warehouse_results AS h
                  GROUP BY h.pur_number, h.sku
              ) AS i ON d.pur_number = i.pur_number AND d.sku = i.sku";

            if ($type == 1) {

                $sql .= " WHERE a.pay_status IN (5) AND a.application_time BETWEEN '{$start}' AND '{$end}'";

            } elseif($type == 2) {

                $sql .= " WHERE a.id IN ({$ids})";

            } else {

                Yii::$app->getSession()->setFlash('error','不支持的导出方式',true);
                return $this->redirect(['index']);

            }

            $models = Yii::$app->db->createCommand($sql)->queryAll();

        }




/*
        Vhelper::dump($models);*/

        if (empty($models)) {

            Yii::$app->getSession()->setFlash('error','没有你所需要的数据！',true);
            return $this->redirect(['index']);
        }

        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);

        $n = 0;
        $shu_mu = 0;




        // 报表头的输出
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:U1'); // 合并单元格
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        $objectPHPExcel->getActiveSheet()->setCellValue('A1','出纳付款');  // 设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); // 设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);




        // 表格头的输出  采购需求建立时间、财务审核时间、财务付款时间
        $a = ['序号', '采购单号', '采购仓库', '采购员',
            '申请日期', '付款日期', 'SKU', '货品名称', '采购单价',
            '采购数量', '实际到货数量', '实际入库数量', '金额', '运费',
            '优惠', '优惠后', '供应商', '备注', '付款状态', '采购状态', '支付方式'];

        foreach($a as $kk => $vv) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($kk+65) . '3',$vv);
        }












        //设置表头居中
        $objectPHPExcel->getActiveSheet()->getStyle('A3:T3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);






        foreach($models as $k => $v) {






            // 明细的输出
            $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+4) ,$n+1);
            $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+4) ,$v['pur_number']);
            $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4) ,(!empty($v['warehouse_code']) ? BaseServices::getWarehouseCode($v['warehouse_code']):''));
            $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+4) ,(!empty($v['buyer']) ? $v['buyer'] : ''));
            $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4) ,$v['application_time']);
            $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4) ,$v['payer_time']);
            $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+4) ,(!empty($v['sku']) ? $v['sku'] : ''));
            $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4) ,(!empty($v['name']) ? $v['name'] : ''));
            $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+4) ,(!empty($v['price']) ? $v['price'] : '')) ;
            $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+4) ,(!empty($v['ctq']) ? $v['ctq'] : ''));
            $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+4) ,($v['arrival_quantity']));
            $objectPHPExcel->getActiveSheet()->setCellValue('L'.($n+4) ,($v['instock_qty_count']));
            $objectPHPExcel->getActiveSheet()->setCellValue('M'.($n+4) ,(!empty($v['items_totalprice']) ? $v['items_totalprice'] : ''));


            $freight = isset($v['freight']) ? $v['freight'] : 0;
            $discount = isset($v['discount']) ? $v['discount'] : 0;




            $objectPHPExcel->getActiveSheet()->setCellValue('N'.($n+4), $freight);
            $objectPHPExcel->getActiveSheet()->setCellValue('O'.($n+4), $discount);



            $real_money = $v['items_totalprice'] + $freight - $discount;

            if (!empty($models[$k+1]['pur_number']) && ($models[$k]['pur_number'] == $models[$k+1]['pur_number'])) {
                $shu_mu++;


            } else {

                $objectPHPExcel->getActiveSheet()->mergeCells('N'.($n+4-$shu_mu) . ':' . 'N'.($n+4));
                $objectPHPExcel->getActiveSheet()->mergeCells('O'.($n+4-$shu_mu) . ':' . 'O'.($n+4));
                $objectPHPExcel->getActiveSheet()->mergeCells('P'.($n+4-$shu_mu) . ':' . 'P'.($n+4));



                $shu_mu = 0;


            }



            $objectPHPExcel->getActiveSheet()->setCellValue('P'.($n+4), $real_money);









            $objectPHPExcel->getActiveSheet()->setCellValue('Q'.($n+4) ,$v['supplier_name']);
            $objectPHPExcel->getActiveSheet()->setCellValue('R'.($n+4) ,(!empty($v['pur_number']) ? PurchaseNote::getNote($v['pur_number']) : ''));
            $objectPHPExcel->getActiveSheet()->setCellValue('S'.($n+4) ,(!empty($v['pay_status']) ? strip_tags(PurchaseOrderServices::getPayStatus($v['pay_status'])) : ''));
            $objectPHPExcel->getActiveSheet()->setCellValue('T'.($n+4) ,(!empty($v['purchas_status']) ? strip_tags(PurchaseOrderServices::getPurchaseStatus($v['purchas_status'])) : ''));
            $objectPHPExcel->getActiveSheet()->setCellValue('U'.($n+4) ,(!empty($v['pay_type']) ? SupplierServices::getDefaultPaymentMethod($v['pay_type']) : ''));

            $n = $n +1;




        }



        // 设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('B2:M'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);

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


                $freight = !empty($m->purchaseOrderPayType) ? $m->purchaseOrderPayType->freight : 0;

                $discount = !empty($m->purchaseOrderPayType) ? $m->purchaseOrderPayType->discount : 0;

                $subData['order_freight'] = $freight;

                $subData['order_discount'] = $discount;


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
                $freight = !empty($payType) ? $payType->freight : 0;
                $discount = !empty($payType) ? $payType->discount : 0;
                $subData['order_freight'] = $freight;
                $subData['order_discount'] = $discount;
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
                $bank  = BankCardManagement::find()->where(['status' => 1])->one();
                $model = PurchaseOrderPay::find()->joinWith(['purchaseOrder', 'supplier'])->where(['pur_purchase_order_pay.id' => $id])->one();

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
                            $transaction->rollback();
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
                        PurchaseOrder::updateAll(['pay_status' => 12], ['pur_number' => $pos]);
                        $purordersv2 = PurchaseOrdersV2::findOne(['pur_number' => $pos]);
                        if($purordersv2) {
                            PurchaseOrdersV2::updateAll(['pay_status' => 12], ['pur_number' => $pos]);
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
        $bank  = BankCardManagement::find()->one();
        $models = new PurchaseOrderPay;
        if(Yii::$app->request->isPost) {
            $datas = Yii::$app->request->post()['PurchaseOrderPays'];
            $data  = Yii::$app->request->post()['PurchaseOrderPay'];
            $datas = Vhelper::changeData($datas);
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                // 确认付款
                $first_note = '';
                foreach($datas as $k=>$v) {
                    $model  = PurchaseOrderPay::findOne($v['id']);
                    $v['branch']               = $data['branch'];
                    $v['pay_types']            = $data['pay_types'];
                    $v['account_abbreviation'] = $data['account_abbreviation'];
                    $v['account_holder']       = $data['account_holder'];

                    $model->pay_status         = $data['pay_status'] == 5 ? 5 : 6;
                    $model->payer              = Yii::$app->user->id;
                    $model->payer_time         = date('Y-m-d H:i:s', time());
                    $model->payment_notice     = $v['create_notice'];
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
                $model->pay_status     = 5;
                $model->payer          = Yii::$app->user->id;
                $model->payer_time     = date('Y-m-d H:i:s', time());
                $model->payment_notice = $data['payment_notice'];
                $model->real_pay_price = $data['real_pay_price'];
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
            $model->pay_status     = $pay_status;
            $model->payer          = Yii::$app->user->id;
            $model->payer_time     = date('Y-m-d H:i:s', time());
            $model->payment_notice = $data['payment_notice'];
            $s = [
                'pur_number' => $model->pur_number,
                'note'       => '确认付款',
            ];
            PurchaseLog::addLog($s);
            PurchaseOrder::updateAll(['pay_status' => $pay_status], ['pur_number' => $data['pur_number']]);
            $model->save(false);
            PurchaseOrderPayWater::saveOne($data);
            $transaction->commit();
            Yii::$app->getSession()->setFlash('success','恭喜你,付款成功');
            return $this->redirect(Yii::$app->request->referrer);
        } catch (Exception $e) {
            $transaction->rollBack();
        }
    }

    
}
