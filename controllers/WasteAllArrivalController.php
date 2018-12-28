<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/20
 * Time: 19:40
 */

namespace app\controllers;

use app\config\Vhelper;
use app\models\PlatformSummary;
use app\models\ProductTaxRate;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderOrders;
use app\models\PurchaseOrderPayDetail;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderShip;
use app\models\PurchaseOrderTaxes;
use app\models\SampleInspect;
use app\models\SupplierUpdateApply;
use Yii;
use app\models\AlibabaAccount;
use app\models\ArrivalRecord;
use app\models\OperatLog;
use app\models\OverseasWarehouseGoodsTaxRebate;
use app\models\Product;
use app\models\PurchaseDemand;
use app\models\PurchaseDiscount;
use app\models\PurchaseNote;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderReceipt;
use app\models\PurchaseSuggest;
use app\models\PurchaseUser;
use app\models\Stock;
use app\models\StockOwes;
use app\models\SupplierLog;
use app\models\User;
use app\models\Warehouse;
use app\models\WarehouseMin;
use app\models\WarehouseResults;
use app\services\BaseServices;
use app\services\CommonServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierGoodsServices;
use app\services\SupplierServices;
use linslin\yii2\curl\Curl;
use yii\base\ErrorException;
use yii\db\Connection;
use yii\web\HttpException;
use app\models\Supplier;
use app\models\OverseasPurchaseOrderSearch;
use yii\web\NotFoundHttpException;

use yii\web\Controller;
use yii\db\Migration;
use yii\db\Schema;

class WasteAllArrivalController extends BaseController
{
    //可执行人
    public $allowUser = ['刘伟', '王瑞','张凡'];

    /**
     * 首页
     */
    public function actionIndex()
    {

//        header("location:http://www.purchase.com/waste-all-arrival/index");
        $username = Yii::$app->user->identity->username;
        if (in_array($username, $this->allowUser)) return $this->render('index', []);
        Yii::$app->getSession()->setFlash('warning','功能已禁用，如有问题，请联系产品经理-汤庆，商讨优化功能');
        return $this->redirect(Yii::$app->request->referrer);
    }
    /**
     * ============================  查看数据   ============================
     */
    public function actionSelectData()
    {
        $db=Yii::$app->getDb();
        $session = Yii::$app->session;

        if (Yii::$app->request->isPost) {
            $select_data_01 = trim(\Yii::$app->request->post('select_data_01'));
            $selected_01 = trim(\Yii::$app->request->post('selected_01'));

            $select_data_02 = trim(\Yii::$app->request->post('select_data_02'));
            $selected_02 = trim(\Yii::$app->request->post('selected_02'));
            $table_name = $session->get('table_name');

            if ($selected_01 != '') {
                $where = "{$select_data_01}='{$selected_01}' ";
            } else {
                return json_encode(['error' => 1, 'message' => [['color'=>'red','msg'=>'第一个字段的值不能为空']]]);
            }

            if ($selected_02 !='') {
                $where .= "and {$select_data_02}='{$selected_02}' ";
            }
            try {
                $sql = "select * from {$table_name} WHERE {$where}";
                $res = Yii::$app->db->createCommand($sql)->queryAll();
                $json_rows = json_encode($res);
                $data[] = ['color'=>'green','msg'=>$json_rows];
                return json_encode(['error' => 1, 'message'=>$data]);

            } catch (\Exception $e) {
                $data[] = ['color'=>'red','msg'=>'******* 最终结果：查询失败！！ ********'. date('H:i:s',time())];
                return json_encode(['error' => 1, 'message'=>$data]);
            }
        } else if (Yii::$app->request->isAjax) {
            $table_name = trim(\Yii::$app->request->get('table_name'));

            $fields= $db->getSchema()->getTableSchema($table_name)->columns;//获取指定表中所有字段名
            foreach ($fields as $k => $v) {
                $fields[$k] = $v->comment;
            }
//            $fields = array_keys($fields);
            return json_encode($fields);
        } else {
            /*$mir = new Migration();
            $sch = new \yii\db\mysql\Schema;
            $tableName='shang';
            $table=$db->createCommand("SHOW TABLES LIKE '".$tableName."'")->queryAll();
            if($table==null)        {
                echo '1';
            }else{
                echo '2';
            }
             $mir->createTable('shang', [
                 'id' => 'pk',
                 'title' => $sch::TYPE_STRING . ' NOT NULL',
                 'content' => $sch::TYPE_TEXT,
             ]);
             $tables=$db->getSchema()->getTableSchemas(); //获取所有表的所有字段*/

            $tables=$db->getSchema()->getTableNames();//获取表名
            $table_name = trim(\Yii::$app->request->get('table_name'));
            if (empty($table_name)) {
                $table_name = 'pur_purchase_order';
            }
            $fields = [];
//            $tables=$db->getSchema()->getTableSchemas(); //获取所有数据库中的所有表的所有字段
//            $tables=$db->getSchema()->getTableSchemas('yb_purchase'); //获取yb_purchase数据库中的所有表的所有字段
            $fields= $db->getSchema()->getTableSchema($table_name)->columns;//获取指定表中所有字段名

            foreach ($fields as $k => $v) {
                $fields[$k] = $v->comment;
            }

            $session->set('table_name',$table_name);
            return $this->renderPartial('_form-select-data',[
                'table_name' => $table_name,
                'tables' => $tables,
                'fields' => $fields,
            ]);
        }

    }
    /**
     * ============================  作废单被入库  处理   ============================
     */
    /**作废单被入库  处理
     * @param string $waste  作废单号
     * @param string $all_arrival  全到货单号
     */
    public function actionWasteAllArrival()
    {
        $waste = trim(\Yii::$app->request->post('waste'));
        $all_arrival = trim(\Yii::$app->request->post('all_arrival'));
        if (empty($waste) || empty($all_arrival)) {
            return json_encode(['error' => 1, 'message' => [['color'=>'red','msg'=>'作废的单号不能为空'],['color'=>'red','msg'=>'全到货的单号不能为空']]]);
        }

        //新增到货记录
//        $data = $arrival_status = $this->createArrivalRecord($waste,$all_arrival,$data=[]);

        $data = [];
        $transaction=\Yii::$app->db->beginTransaction();
        try {
            //修改订单状态
            $data = $order_status = $this->updatePurchaseStatus($waste, $all_arrival,$data);

            //新增入库结果
            $data = $warehouse_status = $this->createWarehouseResults($waste,$all_arrival,$data);

            //新增到货记录
            $data = $arrival_status = $this->createArrivalRecord($waste,$all_arrival,$data);

            foreach ($data as $v) {
                if (!empty($v['status'])  && $v['status'] === 1) {
                    $data[] = ['color'=>'red','msg'=>'******* 最终结果：作废单被入错库处理失败！！*********' . date('H:i:s',time())];
                    return json_encode(['error' => 1, 'message' => $data]);
                }
            }
            $transaction->commit();
            //保存到日志
            $log['type']=14;
            $log['pid']= null;
            $log['pur_number']=$waste . ' ' . $all_arrival;
            $log['module']='作废单被入错库';
            $log['content']='作废单号--'. $waste . '===========全到货单号--' . $all_arrival . '========相关表：pur_arrival_record，pur_warehouse_results';
            Vhelper::setOperatLog($log);

            $data[] = ['color'=>'green','msg'=>'******* 最终结果：作废单被入错库处理成功！！*******' . date('H:i:s',time())];
        } catch(Exception $e) {
            $data = [['color'=>'red','msg'=>'******* 最终结果：作废单被入错库处理失败！！********' . date('H:i:s',time())]];
            $transaction->rollBack();
        }
        return json_encode(['error' => 1, 'message' => $data]);
    }
    /**
     * 修改：订单状态
     */
    public function updatePurchaseStatus($waste,$all_arrival,$data)
    {
        //作废
        $waste_order_model = PurchaseOrder::find()->where(['pur_number' => $waste])->one();
        if (!empty($waste_order_model)) {
            $waste_order_model->purchas_status = 10;
            $waste_order_res = $waste_order_model->save();
            if (!empty($waste_order_res)) {
                $waste_msg = $waste . '--作废成功(pur_purchase_order)';
                $data[] = ['color'=>'green','msg'=> $waste_msg];
            } else {
                $waste_msg = $waste . '--订单状态未修改(pur_purchase_order)';
                $data[] = ['color'=>'red','msg'=> $waste_msg];
            }
        } else {
            $data[] = ['color'=>'red','msg'=> $waste . '--作废单号--错误（pur_purchase_order）','status'=>1];
            return $data;
        }

        //全到货
        $all_arrival_order_model = PurchaseOrder::find()->where(['pur_number' => $all_arrival])->one();
        if (!empty($all_arrival_order_model)) {
            $all_arrival_order_model->purchas_status = 6;
            $all_arrival_order_res = $all_arrival_order_model->save();

            if (!empty($all_arrival_order_res)) {
                $all_arrival_msg = $all_arrival . '--全到货成功(pur_purchase_order)';
                $data[] = ['color'=>'green','msg'=> $all_arrival_msg];

            } else {
                $all_arrival_msg = $all_arrival . '--订单状态未修改(pur_purchase_order)';
                $data[] = ['color'=>'red','msg'=> $all_arrival_msg];
            }
        } else {
            $data[] = ['color'=>'red','msg'=> $all_arrival . '--全到货单号--错误（pur_purchase_order）','status'=>1];
            return $data;
        }
        return $data;
    }
    /**
     * 新增：入库结果
     */
    public function createWarehouseResults($waste,$all_arrival,$data)
    {
        //方法一：
        $waste_warehouse_model = WarehouseResults::find()->where(['pur_number' => $waste])->asArray()->all();
        if (!empty($waste_warehouse_model)) {
            foreach ($waste_warehouse_model as $v) {
                $exists = WarehouseResults::find()->where(['pur_number' => $all_arrival])->andWhere(['sku' => $v['sku']])->exists();
                if ($exists) {
                    $all_arrival_msg = $all_arrival . '--入库结果,不能重复增加(pur_warehouse_results)' . $v['sku'];
                    $data[] = ['color'=>'dark','msg'=> $all_arrival_msg];
                } else {
                    $all_arrival_warehouse_model= new WarehouseResults();
                    $all_arrival_warehouse_model->pur_number          = $all_arrival;
                    $all_arrival_warehouse_model->sku                 = $v['sku'];
                    $all_arrival_warehouse_model->express_no          = !empty($v['express_no'])?$v['express_no']:null;
                    $all_arrival_warehouse_model->purchase_quantity   = !empty($v['purchase_quantity'])?$v['purchase_quantity']:null;
                    $all_arrival_warehouse_model->arrival_quantity    = !empty($v['arrival_quantity'])?$v['arrival_quantity']:null;
                    $all_arrival_warehouse_model->nogoods             = !empty($v['nogoods'])?$v['nogoods']:null;
                    $all_arrival_warehouse_model->have_sent_quantity  = !empty($v['have_sent_quantity']) ? $v['have_sent_quantity']: null;
                    $all_arrival_warehouse_model->instock_qty_count   = !empty($v['instock_qty_count'])?$v['instock_qty_count']:null;
                    $all_arrival_warehouse_model->receipt_number      = !empty($v['receipt_number'])?$v['receipt_number']:null;
                    $all_arrival_warehouse_model->create_time         = !empty($v['create_time'])?$v['create_time']:null;
                    $all_arrival_warehouse_model->update_time         = !empty($v['update_time'])?$v['update_time']:null;
                    $all_arrival_warehouse_model->check_qty           = !empty($v['check_qty'])?$v['check_qty']:null;
                    $all_arrival_warehouse_model->check_type          = !empty($v['check_type'])?$v['check_type']:null;
                    $all_arrival_warehouse_model->instock_user        = !empty($v['instock_user'])?$v['instock_user']:null;
                    $all_arrival_warehouse_model->instock_date        = !empty($v['instock_date'])?$v['instock_date']:null;
                    $all_arrival_warehouse_model->receipt_id          = !empty($v['receipt_id'])?$v['receipt_id']:null;
                    $all_arrival_warehouse_model->save(false);

                    $all_arrival_msg = $all_arrival . '--入库结果,新增成功(pur_warehouse_results)' . $v['sku'];
                    $data[] = ['color'=>'green','msg'=> $all_arrival_msg];
                }
            }
        } else {
            $msg = $waste . '--入库结果表中没有该单的数据(pur_warehouse_results)';
            $data[] = ['color'=>'red','msg'=> $msg,'status'=>1];
            return $data;
        }

        //方法二：还待完善！！！
        /*if (!empty($waste_warehouse_model)) {
            foreach ($waste_warehouse_model as $v) {
                $exists = WarehouseResults::find()->where(['pur_number' => $all_arrival])->andWhere(['sku' => $v['sku']])->exists();
                $v['pur_number'] = $all_arrival;
                if ($exists) {
//                    return '入库结果 -- 已修改';
//                    $model=new WarehouseResults();
//                    $status = $model->updateAll($v,['sku'=>$v['sku'],'pur_number'=>$all_arrival]);
                } else {
                    $models = new WarehouseResults();
                    $models->setAttributes($v);
                    $all_arrival_warehouse_res = $models->save();
                }
            }
        }*/

        return $data;
    }
    /**
     * 新增：采购到货记录
     */
    public function createArrivalRecord($waste,$all_arrival,$data)
    {
        $waste_record_model = ArrivalRecord::find()->where(['purchase_order_no' => $waste])->asArray()->all();
        $all_arrival_record_res = false;
        if (!empty($waste_record_model)) {
            foreach ($waste_record_model as $v) {
                $exists = ArrivalRecord::find()->where(['purchase_order_no' => $all_arrival])->andWhere(['sku' => $v['sku']])->exists();
                if ($exists) {

                } else {
                    $all_arrival_record_model= new ArrivalRecord;
                    $all_arrival_record_model->purchase_order_no = $all_arrival;
                    $all_arrival_record_model->sku               = $v['sku'];
                    $all_arrival_record_model->name              = $v['name'];
                    $all_arrival_record_model->delivery_qty      = $v['delivery_qty'];
                    $all_arrival_record_model->delivery_time     = $v['delivery_time'];
                    $all_arrival_record_model->delivery_user     = $v['delivery_user'];
                    $all_arrival_record_model->cdate             = $v['cdate'];
                    $all_arrival_record_model->express_no        = $v['express_no'];
                    $all_arrival_record_model->check_type        = $v['check_type'];
                    $all_arrival_record_model->bad_products_qty  = $v['bad_products_qty'];
                    $all_arrival_record_model->check_time        = $v['check_time'];
                    $all_arrival_record_model->check_user        = $v['check_user'];
                    $all_arrival_record_model->qc_id             = $v['qc_id'];
                    $all_arrival_record_model->note              = $v['note'];
                    $all_arrival_record_res = $all_arrival_record_model->save(false);
                }
            }
        } else {
            $msg = $waste . '--采购到货记录中没有该单的数据(pur_arrival_record)';
            $data[] = ['color'=>'red','msg'=> $msg,'status'=>1];
            return $data;
        }
        //方法二：
        /*if (!empty($waste_record_model)) {
            foreach ($waste_record_model as $v) {
                $exists = ArrivalRecord::find()->where(['purchase_order_no' => $all_arrival])->andWhere(['sku' => $v['sku']])->exists();
                $v['purchase_order_no'] = $all_arrival;
                if ($exists) {
//                    return '入库结果 -- 已修改';
//                    $model=new WarehouseResults();
//                    $status = $model->updateAll($v,['sku'=>$v['sku'],'pur_number'=>$all_arrival]);
                } else {
                    $models = new ArrivalRecord();
                    $models->setAttributes($v);
                    $all_arrival_record_res = $models->save();
                }
            }
        }*/
        if ($all_arrival_record_res == true) {
            $all_arrival_msg = $all_arrival . '--采购到货记录,新增成功(pur_arrival_record)';
            $data[] = ['color'=>'green','msg'=> $all_arrival_msg];
        } else if ($all_arrival_record_res == false) {
            $all_arrival_msg = $all_arrival . '--采购到货记录,不能重复增加(pur_arrival_record)';
            $data[] = ['color'=>'dark','msg'=> $all_arrival_msg];
        }

        return $data;
    }
    /**
     * 查看：订单详情
     */
    public function actionSelectWasteAllArrival()
    {
        if (Yii::$app->request->isPost) {
            //订单详情表
            $pur_number = trim(\Yii::$app->request->post('pur_number'));
            $purchase_order_info = $this->selectPurchaseOrderInfo($pur_number);
            return json_encode(['error' => 1, 'purchase_order'=>$purchase_order_info]);
        } else {
            return $this->renderPartial('_form-waste-all-arrival',[]);
        }

    }
    /**
     * ============================  海外仓-采购建议-供应商：新建采购计划单报错问题 ============================
     */
    public function actionUpdateIsPurchase()
    {
        $demand_number = trim(\Yii::$app->request->post('supplier_name'));
        $sku = trim(\Yii::$app->request->post('sku'));

        if (empty($demand_number) || empty($sku)) {
            return json_encode(['error' => 1, 'message' => [['color'=>'red','msg'=>'作废的单号不能为空'],['color'=>'red','msg'=>'全到货的单号不能为空']]]);
        }

        $data= [];

        $get_suggest_model = PurchaseSuggest::find()
            ->select(['id','demand_number'])
            ->where(['demand_number' => $demand_number])
            ->andWhere(['sku' => $sku])
            ->andWhere(['purchase_type' => 4])
            ->andWhere(['is_purchase'=>'Y'])
            ->andWhere(['not', ['demand_number' => null]])
            ->asArray()->all();

        //如果采购建议中能找到
        if (!empty($get_suggest_model)) {
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                foreach ($get_suggest_model as $v) {
                    $get_demand_model = PurchaseDemand::find()
                        ->select('pur_number','demand_number')
                        ->where(['demand_number' => $v['demand_number']])
                        ->andWhere(['not', ['pur_number' => null]])
                        ->one();

                    //如果在需求表中有
                    if (!empty($get_demand_model)) {
                        $status = PurchaseSuggest::updateAll(['is_purchase'=>'N'],['id'=>$v['id']]);
                    } else {
                        $data[] = ['color'=>'red','msg'=>'对应采购建议的ID：' . $v['id'] .' 需求表中没有对应的需求单号'];
                    }
                }
                $transaction->commit();
                //保存到日志
                $log['type']=15;
                $log['pid']= null;
                $log['pur_number']=$sku;
                $log['module']='海外仓-采购建议-供应商：新建采购计划单报错问题';
                $log['content']='将 pur_purchase_suggest中： 需求单号（' . $demand_number . '）-- sku（' . $sku . '）的is_purchase改为"N"';
                Vhelper::setOperatLog($log);

                $data[] = ['color'=>'green','msg'=>'******* 最终结果：执行成功！！********' . date('H:i:s',time())];
                return json_encode(['error' => 1, 'message' => $data]);
            } catch(Exception $e) {
                $transaction->rollBack();
            }
        } else {
            $data[] = ['color'=>'red','msg'=>'你输入的供应商名称或sku错误'];
        }
        $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
        return json_encode(['error' => 1, 'message' => $data]);
    }
    public function actionSelectIsPurchase()
    {
        if (Yii::$app->request->isPost) {
            $demand_number = trim(\Yii::$app->request->post('supplier_name'));
            $suggest = $this->selectPurchaseSuggest(null,$demand_number,4,'Y');
            return json_encode(['error' => 1, 'suggest'=>$suggest]);
        } else {
            return $this->renderPartial('_form-update-is-purchase');
        }

    }
    /**
     * ============================  修改采购单中的供应商名称  ============================
     */
    public function actionUpdateSupplier()
    {
        //selectPurchaseOrderPay
        $pur_number = trim(\Yii::$app->request->post('pur_number'));
        $supplier_name = trim(\Yii::$app->request->post('supplier_name'));
        $supplier_code = trim(\Yii::$app->request->post('supplier_code'));

        if (empty($pur_number) || (empty($supplier_name) && empty($supplier_code)) ) {
            return json_encode(['error' => 1, 'message' => [['color'=>'red','msg'=>'采购单单号不能为空'],['color'=>'red','msg'=>'供应商名或供应商编码不能为空']]]);
        }

        $data= [];
        $transaction=\Yii::$app->db->beginTransaction();
        try {
            //供应商  一个供应商的中文名对应多个供应商编码？？
            $supplier_data = $this->getSupplierNameCode($supplier_name,$supplier_code,$data);
            if (!empty($supplier_data[0]['status']) && $supplier_data[0]['status'] ===1) {
                $supplier_name = $supplier_data[0]['supplier_name'];
                $supplier_code = $supplier_data[0]['supplier_code'];
                $data[] = ['color'=>'green','msg'=> $supplier_name . '--' . $supplier_code];
            } else {
                $data = $supplier_data;
                $data[] = ['color'=>'red','msg'=>'******* 最终结果：供应商修改失败！！********'. date('H:i:s',time())];
                return json_encode(['error' => 1, 'message' => $data]);
            }

            //采购单--对应的供应商
            $purchase_order_info = $this->selectPurchaseOrderInfo($pur_number);
            if (!empty($purchase_order_info[1]['supplier_code'])) {
                $order_supplier_code_old = $purchase_order_info[1]['supplier_code'];
            } else {
                $order_supplier_code_old = '';
            }

            if (!empty($purchase_order_info[1]['supplier_name'])) {
                $order_supplier_name_old = $purchase_order_info[1]['supplier_name'];
            } else {
                $order_supplier_name_old = '';
            }
            //采购单支付表-对应的供应商
            $purchase_order_pay_info = $this->selectPurchaseOrderPayInfo($pur_number);
            if (!empty($purchase_order_pay_info[1]['supplier_code'])) {
                $order_pay_supplier_code_old = $purchase_order_pay_info[1]['supplier_code'];
            } else {
                $order_pay_supplier_code_old = '';
            }

            //采购单
            $order_data = $this->updateOrderSupplierNameCode($pur_number,$supplier_name,$supplier_code,$data);

            foreach ($order_data as $ov) {
                if (!empty($ov['status']) && $ov['status'] ===1) {
                    $data[] = ['color'=>'red','msg'=>'******* 最终结果：供应商修改失败！！********' . date('H:i:s',time())];
                    return json_encode(['error' => 1, 'message' => $order_data]);
                }
                $data = $order_data;
            }

            //财务单
            $data = $this->updateOrderPaySupplierCode($pur_number,$supplier_code,$data);
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：供应商修改成功！！*******' . date('H:i:s',time())];
            $transaction->commit();
            //保存到日志
            $log['type']=16;
            $log['pid']= null;
            $log['pur_number']=$pur_number;
            $log['module']='修改采购单中的供应商名称';
            $log['content']='原：pur_purchase_order（supplier_code：'.$order_supplier_code_old.'--supplier_name：' . $order_supplier_name_old . '）--pur_purchase_order（supplier_code：' . $order_pay_supplier_code_old .'）' . '===========修改后：供应商名称（'.$supplier_name.'）' . '供应商编码（'.$supplier_code .'）===相关表：pur_purchase_order,pur_purchase_order_pay';
            Vhelper::setOperatLog($log);

        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：供应商修改失败！！********' . date('H:i:s',time())];
            $transaction->rollBack();
        }
        return json_encode(['error' => 1, 'message' => $data]);
    }
    /**
     *供应商  一个供应商的中文名对应多个供应商编码
     */
    public function getSupplierNameCode($supplier_name,$supplier_code,$data)
    {
        $get_supplier_model = Supplier::find()->select(['id','supplier_name','supplier_code'])->where(['=','status','1']);
        if (!empty($supplier_code)) {
            $get_supplier_model = $get_supplier_model->andWhere(['supplier_code' => $supplier_code])->orderBy('id desc')->asArray()->all();
        } else {
            $get_supplier_model = $get_supplier_model->andWhere(['supplier_name' => $supplier_name])->orderBy('id desc')->asArray()->all();
        }

        if (!empty($get_supplier_model)) {
            if (count($get_supplier_model) === 1) {
                $supplier_name = $get_supplier_model[0]['supplier_name'];
                $supplier_code = $get_supplier_model[0]['supplier_code'];
                if (empty($supplier_name) || empty($supplier_code)) {
                    $data[] = ['color'=>'red','msg'=>'供应商表中找不到对应的 【供应商名】或【供应商编码】'];
                    return $data;
                } else {
                    $data[] = ['supplier_name'=>$supplier_name,'supplier_code'=>$supplier_code,'status'=>1];
                    return $data;
                }
            } else {
                $data[] = ['color'=>'red','msg'=>'供应商表中找到多个供应商编码,请选择一个正确的供应商编码'];
                foreach ($get_supplier_model as $sv) {
                    $data[] = ['color'=>'red','msg'=>'供应商名：'. $sv['supplier_name'] . '--供应商编码：' . $sv['supplier_code']];
                }
                return $data;
            }
            if (empty($supplier_name) || empty($supplier_code)) {
                $data[] = ['color'=>'red','msg'=>'供应商表中找不到对应的 【供应商名】或【供应商编码】'];
                return $data;
            }
        } else {
            $data[] = ['color'=>'red','msg'=>'你输入的 【供应商名--'. $supplier_name .'】或【供应商编码--'. $supplier_code .'】 有误  或  【供应商已停用】'];
            return $data;
        }
    }
    /**
     * 采购单：修改采购单的供应商名和编码
     */
    public  function updateOrderSupplierNameCode($pur_number,$supplier_name,$supplier_code,$data)
    {
        $get_order_model = PurchaseOrder::find()
            ->select(['id','supplier_name','supplier_code'])
            ->where(['pur_number' => $pur_number])
            ->one();

        //如果采购单中能找到
        if (!empty($get_order_model)) {
            $order_status = PurchaseOrder::updateAll(['supplier_name'=>$supplier_name,'supplier_code'=>$supplier_code],['id'=>$get_order_model->id]);
            $data[] = ['color'=>'green','msg'=>$pur_number . '--采购单(pur_purchase_order)--修改供应商成功！！'];
        } else {
            $data[] = ['color'=>'red','msg'=>$pur_number . '--你输入的采购单单号有误','status'=>1];
        }
        return $data;
    }
    /**
     * 财务单：修改供应商编码
     */
    public function updateOrderPaySupplierCode($pur_number,$supplier_code,$data)
    {
        $get_order_pay_model = PurchaseOrderPay::find()
            ->select(['id','supplier_code'])
            ->where(['pur_number' => $pur_number])
            ->all();
        //如果财务单中能找到
        if (!empty($get_order_pay_model)) {
            $pay_status = PurchaseOrderPay::updateAll(['supplier_code'=>$supplier_code],['pur_number'=>$pur_number]);
            if (!empty($pay_status)) {
                $data[] = ['color'=>'green','msg'=>$pur_number . '--采购单支付表(pur_purchase_order_pay)--修改供应商成功！！'];
            }
        }
        return $data;
    }
    /**
     * 查看：订单状态
     */
    public function actionSelectSupplier()
    {
        if (Yii::$app->request->isPost) {
            //订单详情表
            $pur_number = trim(\Yii::$app->request->post('pur_number'));
            $purchase_order_info = $this->selectPurchaseOrderInfo($pur_number);

            //采购单支付表
            $purchase_order_pay_info = $this->selectPurchaseOrderPay($pur_number);

            //根据订单中的供应商编码，查询供应商
            if (!empty($purchase_order_info[1]['supplier_code'])) {
                $supplier_code = $purchase_order_info[1]['supplier_code'];
                $purchase_order_code = $this->selectSupplier($supplier_code);
            } else {
                $purchase_order_code = '';
            }

            return json_encode(['error' => 1, 'purchase_order'=>$purchase_order_info,'purchase_order_pay'=>$purchase_order_pay_info, 'purchase_order_code'=>$purchase_order_code]);
        } else {
            return $this->renderPartial('_form-update-supplier');
        }

    }
    /**
     * ======== 温馨小提示 ========
     */
    public function actionPrompt()
    {
        return $this->renderPartial('_form-prompt');
    }
    /**
     * ======== 相应的任务，找对应的对接人 ========
     */
    public function actionPickUp()
    {
        return $this->renderPartial('_form-pick-up');
    }
    /**
     * ============================  修改采购单状态 ============================
     */
    public function actionUpdatePurchasStatus()
    {
        $pur_number = trim(\Yii::$app->request->post('pur_number'));
        $purchas_status_new = trim(\Yii::$app->request->post('purchas_status'));
        $refund_status_new = trim(\Yii::$app->request->post('refund_status'));
        $buyer_new = trim(\Yii::$app->request->post('buyer'));

        if (empty($purchas_status_new) && empty($refund_status_new) && empty($buyer_new)) {
            $data[] = ['color'=>'red','msg'=>'请选择采购员或采购单或退款状态'];
            $data[] = ['color'=>'red','msg'=>'********  最终结果：修改失败！！  **********'];
            return json_encode(['error' => 1, 'message' => $data]);
        }

        $purchase_order_info = PurchaseOrder::find()->where(['pur_number'=>$pur_number])->asArray()->one();

        if (empty($purchase_order_info)) {
            $data[] = ['color'=>'red','msg'=>'采购单有误'];
            $data[] = ['color'=>'red','msg'=>'********  最终结果：修改失败！！  **********'];
            return json_encode(['error' => 1, 'message' => $data]);
        }

        $purchas_status_old = !empty($purchase_order_info['purchas_status']) ? $purchase_order_info['purchas_status'] : '';
        $refund_status_old = !empty($purchase_order_info['refund_status']) ? $purchase_order_info['refund_status'] : '';
        $buyer_old = !empty($purchase_order_info['buyer']) ? $purchase_order_info['buyer'] : '';

        $data = [];
        $transaction=\Yii::$app->db->beginTransaction();
        try {

            $data = $this->updateOrderPurchasStatus($pur_number,$purchas_status_new,$refund_status_new,$buyer_new,$data);

            if (!empty($buyer_new)) {
                $user_id = User::find()->select('id')->where(['username'=>$buyer_new])->scalar();
                PurchaseOrderPay::updateAll(['applicant'=>$user_id], ['pur_number'=>$pur_number]);
            }

            if (!empty($data[0]['status']) && $data[0]['status'] === 1) {
                $data[] = ['color'=>'red','msg'=>'********  最终结果：修改失败！！  **********'];
                return json_encode(['error' => 1, 'message' => $data]);
            }
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：修改成功！！ ********'];
            $transaction->commit();

            //保存到日志
            $log['type']=17;
            $log['pid']= null;
            $log['pur_number']=$pur_number;
            $log['module']='修改采购单状态';
            $log['content']="旧purchas_status={$purchas_status_old}，refund_status={$refund_status_old},buyer={$buyer_old}===========新：purchas_status={$purchas_status_new}，refund_status={$refund_status_new},buyer={$buyer_new}===相关表：pur_purchase_order";
            Vhelper::setOperatLog($log);

        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'********  最终结果：修改失败！！  **********'];
            $transaction->rollBack();
        }

        return json_encode(['error' => 1, 'message' => $data]);
    }
    public function updateOrderPurchasStatus($pur_number,$purchas_status_new,$refund_status_new,$buyer_new,$data)
    {
        $get_order_model = PurchaseOrder::find()
            ->select(['id','purchas_status','refund_status','buyer'])
            ->where(['pur_number' => $pur_number])
            ->one();

        //如果采购表中存在
        if (!empty($purchas_status_new)) {
            $where['purchas_status'] = $purchas_status_new;
        }
        if (!empty($refund_status_new)) {
            $where['refund_status'] = $refund_status_new;
        }
        if (!empty($buyer_new)) {
            $where['buyer'] = $buyer_new;
        }
        $order_status = PurchaseOrder::updateAll($where,['id'=>$get_order_model->id]);
        if (!empty($order_status)) {
            $data[] = ['color'=>'green','msg'=>'采购单表(pur_purchase_order)--修改成功！！'];
        } else {
            $data[] = ['color'=>'red','msg'=>'采购单表(pur_purchase_order)--修改失败！！','status'=>1];
        }
        return $data;
    }
    public function actionSelectPurchasStatus()
    {
        if (Yii::$app->request->isPost) {
            //订单详情表
            $pur_number = trim(\Yii::$app->request->post('pur_number'));
            $purchase_order_info = $this->selectPurchaseOrderInfo($pur_number);

            //根据订单中的供应商编码，查询供应商
            if (!empty($purchase_order_info[1]['supplier_code'])) {
                $supplier_code = $purchase_order_info[1]['supplier_code'];
                $purchase_order_code = $this->selectSupplier($supplier_code);
            } else {
                $purchase_order_code = '';
            }

            return json_encode(['error' => 1, 'purchase_order'=>$purchase_order_info,'purchase_order_code'=>$purchase_order_code]);
        } else {
            return $this->renderPartial('_form-update-purchas-status');
        }

    }
    /**
     * ============================ 修改采购付款状态 ============================
     */
    public function actionUpdatePayStatus()
    {
        $data = [];
        $pur_number = trim(\Yii::$app->request->post('pur_number'));
        $pay_status_old = trim(\Yii::$app->request->post('pay_status_old'));
        $pay_status_new = trim(\Yii::$app->request->post('pay_status_new'));

        $transaction=\Yii::$app->db->beginTransaction();
        try {
            $data = $this->updatePurchaseOrderPayStatus($pur_number,$pay_status_old,$pay_status_new,$data);
            $data = $this->updatePurchasePayPayStatus($pur_number,$pay_status_old,$pay_status_new,$data);

            if (!empty($data[0]['status']) && $data[0]['status'] === 1) {
                $data[] = ['color'=>'red','msg'=>'********  最终结果：修改失败！！  **********' . date('H:i:s',time())];
                return json_encode(['error' => 1, 'message' => $data]);
            }

            $data[] = ['color'=>'green','msg'=>'******* 最终结果：修改成功！！ ********' . date('H:i:s',time())];
            $transaction->commit();
            //保存到日志
            $log['type']=18;
            $log['pid']= null;
            $log['pur_number']=$pur_number;
            $log['module']='财务-【付款】状态';
            $log['content']='原：pay_status：'.$pay_status_old . '===========修改后：pay_status：' . $pay_status_new .'===相关表：pur_purchase_order,pur_purchase_order_pay';
            Vhelper::setOperatLog($log);

        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'********  最终结果：修改失败！！  **********' . date('H:i:s',time())];
            $transaction->rollBack();
        }
        return json_encode(['error' => 1, 'message' => $data]);
    }
    public function updatePurchaseOrderPayStatus($pur_number,$pay_status_old,$pay_status_new,$data)
    {
        $get_order_model = PurchaseOrder::find()
            ->select(['id','pay_status'])
            ->where(['pur_number' => $pur_number])
            ->andWhere(['pay_status' => $pay_status_old])
            ->one();

        //如果采购表中存在
        if (!empty($get_order_model)) {
            $order_status = PurchaseOrder::updateAll(['pay_status'=>$pay_status_new],['id'=>$get_order_model->id]);
            if (!empty($order_status)) {
                $data[] = ['color'=>'green','msg'=>'采购单表(pur_purchase_order)--修改采购付款状态成功！！'];
                return $data;
            }
        } else {
            $data[] = ['color'=>'red','msg'=>'采购单号 或  旧的采购付款状态错误','status'=>1];
            return $data;
        }
    }
    public function updatePurchasePayPayStatus($pur_number,$pay_status_old,$pay_status_new,$data)
    {
        $get_order_pay_model = PurchaseOrderPay::find()
            ->select(['id','pay_status'])
            ->where(['pur_number' => $pur_number])
//            ->andWhere(['pay_status' => $pay_status_old])
            ->one();

        //如果采购表中存在
        if (!empty($get_order_pay_model)) {
            $order_pay_status = PurchaseOrderPay::updateAll(['pay_status'=>$pay_status_new],['id'=>$get_order_pay_model->id]);
            if (!empty($order_pay_status)) {
                $data[] = ['color'=>'green','msg'=>'采购单支付表(pur_purchase_order_pay)--修改采购付款状态成功！！'];
            }
        }
        return $data;
    }
    public function actionSelectPurchaseOrderPayStatus()
    {
        if (Yii::$app->request->isPost) {
            //订单详情表
            $pur_number = trim(\Yii::$app->request->post('pur_number'));
            $purchase_order_info = $this->selectPurchaseOrderInfo($pur_number);

            //采购单支付表
            $purchase_order_pay_info = $this->selectPurchaseOrderPayInfo($pur_number);
            return json_encode(['error' => 1, 'purchase_order'=>$purchase_order_info,'purchase_order_pay'=>$purchase_order_pay_info]);
        } else {
            return $this->renderPartial('_form-update-pay-status');
        }

    }
    /**
     * ============================  采购单收款表：修改收款状态  ============================
     */
    public function actionUpdatePurchaseOrderReceiptPayStatus(){
        $data = [];
        $pur_number = trim(\Yii::$app->request->post('pur_number'));
        $pay_status_old = trim(\Yii::$app->request->post('pay_status_old'));
        $pay_status_new = trim(\Yii::$app->request->post('pay_status_new'));

        $transaction=\Yii::$app->db->beginTransaction();
        try {
            $data = $this->updatePurchaseOrderReceiptPayStatus($pur_number,$pay_status_old,$pay_status_new,$data);
            foreach ($data as $v) {
                if (!empty($v['status']) && $v['status']==1) {
                    $data[] = ['color'=>'red','msg'=>'********  最终结果：修改失败！！  **********' . date('H:i:s',time())];
                    return json_encode(['error' => 1, 'message' => $data]);
                }
            }
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：修改成功！！ ********' . date('H:i:s',time())];
            $transaction->commit();
            //保存到日志
            $log['type']=19;
            $log['pid']= null;
            $log['pur_number']=$pur_number;
            $log['module']='财务-【收款】状态';
            $log['content']='原：pay_status：'.$pay_status_old . '=======修改后：pay_status：' . $pay_status_new .'===相关表：pur_purchase_order_receipt';
            Vhelper::setOperatLog($log);
        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'********  最终结果：修改失败！！  **********' . date('H:i:s',time())];
            $transaction->rollBack();
        }
        return json_encode(['error' => 1, 'message' => $data]);
    }
    public function updatePurchaseOrderReceiptPayStatus($pur_number,$pay_status_old,$pay_status_new,$data)
    {
        $get_order_receipt_model = PurchaseOrderReceipt::find()
            ->select(['id','pay_status'])
            ->where(['pur_number' => $pur_number])
            ->andWhere(['pay_status' => $pay_status_old])
            ->one();

        //采购单支付表
        if (!empty($get_order_receipt_model)) {
            $get_order_receipt_status = PurchaseOrderReceipt::updateAll(['pay_status'=>$pay_status_new],['id'=>$get_order_receipt_model->id]);
            $data[] = ['color'=>'green','msg'=>'采购单支付表(pur_purchase_order_receipt)--修改采购付款状态成功！！'];
        } else {
            $data[] = ['color'=>'green','msg'=>'采购单支付表(pur_purchase_order_receipt)--采购单不存在！！','status'=>1];
        }
        return $data;
    }
    public function actionSelectPurchaseOrderReceiptPayStatus()
    {
        if (Yii::$app->request->isPost) {
            $pur_number = trim(\Yii::$app->request->post('pur_number'));
            $purchase_order_receipt_info = $this->selectPurchaseOrderReceipt($pur_number);
            return json_encode(['error' => 1, 'order_receipt'=>$purchase_order_receipt_info]);
        } else {
            return $this->renderPartial('_form-update-purchase-order-receipt-pay-status');
        }

    }
    /**
     * ============================  查看在途库存 ============================
     */
    public function actionSelectStock()
    {
        if (Yii::$app->request->isPost == false) {
            return $this->renderPartial('_form-select-stock');
        }
        $sku = trim(\Yii::$app->request->post('sku'));

        $suggest = $this->selectPurchaseSuggest($sku);
        $stock = $this->selectStock($sku);
        $stock_owes = $this->selectStockOwes($sku);
        return json_encode(['error' => 1, 'suggest' => $suggest, 'message' => $stock,'stock_owes'=>$stock_owes]);
    }
    /**
     * ============================  修改采购建议中的 产品状态??优化显示  ============================
     */
    public function actionSelectSuggestProductStatus()
    {
        if (Yii::$app->request->isPost) {
            $sku = trim(\Yii::$app->request->post('sku'));
            $warehouse_name = trim(\Yii::$app->request->post('warehouse_name'));
            //产品列表
            $product = $this->selectPurchaseProduct($sku);

            //采购建议
            $suggest = $this->selectPurchaseSuggest($sku,null,null,null,$warehouse_name);
            return json_encode(['error' => 1, 'product'=>$product,'suggest'=>$suggest]);
        } else {
            return $this->renderPartial('_form-update-suggest-product-status');
        }

    }
    public function actionUpdateSuggestProductStatus()
    {
        $data = [];
        $sku = trim(\Yii::$app->request->post('sku'));
        $product_status = trim(\Yii::$app->request->post('product_status'));
        $warehouse_name = trim(\Yii::$app->request->post('warehouse_name'));

        $query = PurchaseSuggest::find()->select(['id','product_status'])->where(['sku'=>$sku]);
        if (!empty($warehouse_name)) {
            $query = $query->andWhere(['warehouse_name'=>$warehouse_name]);
        }
        $suggest_info = $query->asArray()->all();

//        $suggest_info = PurchaseSuggest::find()->select(['id','product_status'])->where(['sku'=>$sku])->asArray()->all();

        if (empty($suggest_info)) {
            $data[] = ['color'=>'red','msg'=>'sku 或 仓库名称有误','status'=>1];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：修改失败！！ ********'. date('H:i:s',time())];
        } else if (count($suggest_info) == 1) {
            $purchase_suggest_status = PurchaseSuggest::updateAll(['product_status'=>$product_status],['id'=>$suggest_info[0]['id']]);

            if (!empty($purchase_suggest_status)) {
                //保存到日志
                $log['type']=20;
                $log['pid']= null;
                $log['pur_number']=$sku;
                $log['module']='修改--产品状态';
                $log['content']='原：product_status：'.$suggest_info[0]['product_status'] . '=======修改后：product_status：' . $product_status .'===相关表：pur_purchase_suggest';
                Vhelper::setOperatLog($log);

                $data[] = ['color'=>'green','msg'=>'******* 最终结果：修改成功！！ ********'. date('H:i:s',time())];
            } else {
                $data[] = ['color'=>'red','msg'=>'******* 最终结果：修改失败！！ ********'. date('H:i:s',time())];
            }
        } else {
            $data[] = ['color'=>'red','msg'=>'这个sku有多个采购建议，请填写仓库','status'=>1];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：修改失败！！ ********' . date('H:i:s',time())];
        }
        return json_encode(['error' => 1, 'message'=>$data]);
    }
    /**
     * ============================  修改关税(修改是否退税)  ============================
     */
    public function actionSelectPurchaseOrderIsDrawback() {
        if (Yii::$app->request->isPost) {
            $pur_number = trim(\Yii::$app->request->post('pur_number'));
            $sku = trim(\Yii::$app->request->post('sku'));
            $purchase_order_info = $this->selectPurchaseOrderInfo($pur_number);
            $purchase_order_pay_info = $this->selectPurchaseOrderPay($pur_number);
            $purchase_order_items_info = $this->selectPurchaseOrderItems($pur_number);
            if (!empty($purchase_order_items_info[1])) {
                $skus = array_column($purchase_order_items_info[1],'sku');
                $taxs_sku = !empty($sku) ? $sku : $skus;
                $product_tax_rate_info = $this->selectPurchaseOrderTaxes($pur_number,$taxs_sku);
            } else {
                $product_tax_rate_info = [];
            }
            return json_encode(['error' => 1, 'purchase_order'=>$purchase_order_info, 'purchase_order_pay'=>$purchase_order_pay_info, 'product_tax_rate'=>$product_tax_rate_info]);
        } else {
            return $this->renderPartial('_form-update-order-is-drawback');
        }

    }
    public function actionUpdatePurchaseOrderIsDrawback() {
        $data = [];
        $pur_numbers = trim(\Yii::$app->request->post('pur_number'));
        $is_drawback = trim(\Yii::$app->request->post('is_drawback'));
        $ticketed_point = trim(\Yii::$app->request->post('ticketed_point'));
        $is_push = trim(\Yii::$app->request->post('is_push'));
        $account_type = trim(\Yii::$app->request->post('account_type'));
        $pay_type = trim(\Yii::$app->request->post('pay_type'));
        $sku = trim(\Yii::$app->request->post('sku'));
        $arr_pur_number = explode(',',$pur_numbers);

        foreach ($arr_pur_number as $k=>$pur_number) {
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $purchase_order_info = PurchaseOrder::find()
                    ->select(['id','pur_number','is_drawback','is_push','account_type','pay_type'])
                    ->where(['pur_number'=>$pur_number])
                    ->asArray()
                    ->one();
                $is_drawback_old = !empty($purchase_order_info['is_drawback']) ? $purchase_order_info['is_drawback'] :'';
                $is_push_old = !empty($purchase_order_info['is_push']) ? $purchase_order_info['is_push'] :'';
                $account_type_old = !empty($purchase_order_info['account_type']) ? $purchase_order_info['account_type'] :'';
                $pay_type_old = !empty($purchase_order_info['pay_type']) ? $purchase_order_info['pay_type'] :'';

                $purchase_order_items_info = $this->selectPurchaseOrderItems($pur_number);
                if (!empty($purchase_order_items_info[1])) {
                    $skus = array_column($purchase_order_items_info[1],'sku');
                    $product_tax_rate_info = PurchaseOrderTaxes::find()
                        ->select(['pur_number','sku','taxes'])
                        ->where(['in','pur_number', $pur_number])
                        ->andWhere(['in','sku',!empty($sku) ? $sku : $skus])
                        ->asArray()
                        ->all();
                } else {
                    $skus = null;
                    $product_tax_rate_info = [];
                }
                $product_tax_rate_old = json_encode($product_tax_rate_info);
                $product_tax_rate_new = json_encode($skus);

                if (!empty($purchase_order_info)) {

                    if (!empty($is_drawback)) {
                        $update['is_drawback'] = $is_drawback;
                    }
                    if (!empty($is_push) || $is_push=='0') {
                        $update['is_push'] = $is_push;
                    }
                    if (!empty($account_type)) {
                        $update['account_type'] = $account_type;
                        PurchaseOrderPay::updateAll(['settlement_method'=>$account_type],['pur_number'=>$pur_number]);
                    }
                    if (!empty($pay_type)) {
                        $update['pay_type'] = $pay_type;
                        PurchaseOrderPay::updateAll(['pay_type'=>$pay_type],['pur_number'=>$pur_number]);
                    }
                    if (!empty($update)) {
                        PurchaseOrder::updateAll($update,['id'=>$purchase_order_info['id']]);
                    }


                    if (!empty($sku)&& (!empty($ticketed_point) || $ticketed_point=='0')) {
                        $data['taxes'][] = [
                            'sku' => $sku,
                            'pur_number' => $pur_number,
                            'taxes' => $ticketed_point,
                        ];
                        $purchase_order_taxes_model = new PurchaseOrderTaxes();
                        $purchase_order_taxes_model->saveTax($data);
                        //PurchaseOrderTaxes::updateAll(['taxes' => $ticketed_point],['sku'=>$sku,'pur_number'=>$pur_number]);
                    } elseif (!empty($skus)&& (!empty($ticketed_point) || $ticketed_point=='0')) {
                        foreach ($skus as $v) {
                            $data['taxes'][] = [
                                'sku' => $v,
                                'pur_number' => $pur_number,
                                'taxes' => $ticketed_point,
                            ];
                            $purchase_order_taxes_model = new PurchaseOrderTaxes();
                            $purchase_order_taxes_model->saveTax($data);
//                                PurchaseOrderTaxes::updateAll(['taxes' => $ticketed_point],['sku'=>$v,'pur_number'=>$pur_number]);
                        }
                    }

                    $transaction->commit();

                    //保存到日志
                    $log['type']=21;
                    $log['pid']= null;
                    $log['pur_number']=$pur_number;
                    $log['module']='采购单：修改关税和结算和支付方式';
                    $log['content']="旧：is_drawback={$is_drawback_old}，is_push={$is_push_old}，account_type={$account_type_old}，pay_type={$pay_type_old},product_tax_rate={$product_tax_rate_old}=======新：is_drawback={$is_drawback}，is_push={$is_push}，account_type={$account_type}，pay_type={$pay_type},skus={$product_tax_rate_new},taxes={$ticketed_point}===相关表：pur_purchase_order";
                    Vhelper::setOperatLog($log);

                    $data[] = ['color'=>'green','msg'=>$pur_number];
                    $data[] = ['color'=>'green','msg'=>'******* 最终结果：修改成功！！ ********' . date('H:i:s',time())];

                } else {
                    $data[] = ['color'=>'green','msg'=>$pur_number . '该订单不存在'];
                    $data[] = ['color'=>'red','msg'=>'********  最终结果：修改失败！！  **********' . date('H:i:s',time())];
                }
            } catch(Exception $e) {
                $data[] = ['color'=>'red','msg'=>'********  最终结果：修改失败！！  **********' . date('H:i:s',time())];
                $transaction->rollBack();
            }
        }

        return json_encode(['error' => 1, 'message'=>$data]);
    }
    /**
     * ============================ 拉取erp的sku?? ============================
     */
    /**
     * 将erp中的sku推送给中间件
     */
    public function actionPullErpSku(){
        $sku = trim(\Yii::$app->request->post('sku'));
        if(empty($sku)) {
            return '请传入sku--post形式';
        }
        /*//创建一个新cURL资源
        $ch = curl_init();
        // 设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, "http://120.24.249.36/services/products/product/productsall/sku/".$sku);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 抓取URL并把它传递给浏览器
        $data = curl_exec($ch);
        //关闭cURL资源，并且释放系统资源
        curl_close($ch);*/

        $sku_url     = Yii::$app->params['ERP_URL'].'/services/products/product/productsall/sku/' . $sku;
        $pull_v = $this->curl($sku_url);
        $data = ['color'=>'red','msg'=>$pull_v . date('Y-m-d H:i:s',time())];
        return json_encode(['error' => 1, 'message'=>$data]);
    }
    /**
     *拉取中间件的sku到仓库
     */
    public function actionPullErpSkuCenter(){
        $sku = trim(\Yii::$app->request->post('sku'));
        if(empty($sku)) {
            return '请传入sku--post形式';
        }

        //创建一个新cURL资源
        $ch = curl_init();
        // 设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, "http://dc.yibainetwork.com/index.php/products/insertProductToMongoBysku?sku=".$sku);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 抓取URL并把它传递给浏览器
        $data = curl_exec($ch);
        //关闭cURL资源，并且释放系统资源
        curl_close($ch);

        /*$sku_url     = 'http://dc.yibainetwork.com/index.php/products/insertProductToMongoBysku';
        $data = ['sku'=>$sku];
        $pull_v = $this->curl($sku_url,$data);
        if (empty($pull_v) || ($pull_v!=='success')) {
            $data = ['color'=>'red','msg'=>$pull_v];
            return json_encode(['error' => 1, 'message'=>$data]);
            return $pull_v;
        }
        return json_encode(['a'=>$pull_v]);*/
    }
    /**
     * 将仓库的sku推送给采购系统
     */
    public function actionPushErpSku(){
        // $sku = trim(\Yii::$app->request->post('sku'));
        $sku = trim(\Yii::$app->request->get('sku'));
        if(empty($sku)) {
            return '请传入sku--post形式';
        }
        $push_sku_url     = 'http://dc.yibainetwork.com/index.php/products/pushProductToPurchase';
        $push_v = $this->curl($push_sku_url);
        /*if (empty($push_v) || ($push_v !== '采购模块产品推送处理成功')) {
            $data = ['color'=>'red','msg'=>$push_v];
            return json_encode(['error' => 1, 'message'=>$data]);
        }*/
        $data = ['color'=>'red','msg'=>$push_v . date('Y-m-d H:i:s',time())];
        return json_encode(['error' => 1, 'message'=>$data]);

//        $response=['color' => 'green', 'msg'=>'************拉取 sku 成功--' . $sku . '**************'];
//        return json_encode(['error' => 1, 'message'=>$response]);

    }
    public function actionSelectPullErpSku()
    {
        if (Yii::$app->request->isPost) {
            $sku = trim(\Yii::$app->request->post('sku'));
            //产品列表
            $product = $this->selectPurchaseProduct($sku);

            return json_encode(['error' => 1, 'product'=>$product]);
        } else {
            return $this->renderPartial('_form-pull-erp-sku');
        }
    }
    /**
     * ============================ 拉取物流信息?? ============================
     */
    public function actionPullLogistics()
    {
        $username = trim(\Yii::$app->request->post('bind_account'));
        if (empty($username)) {
            return '请传入-阿里账号绑定用户的ID--post形式';
        }

        if (empty($username)) {
            return json_encode(['error' => 500, 'msg'=>'请输入用户名']);
        }
        $user_info = $this->getUser(null,$username);
        if (empty($user_info)) {
            return json_encode(['error' => 500, 'msg'=>'没有该用户--' . $username]);
        } else {
            if (!empty($user_info[0]['id'])) {
                $bind_account = $user_info[0]['id'];
            } else {
                return json_encode(['error' => 500, 'msg'=>'没有该用户--' . $username]);
            }
        }

        //创建一个新cURL资源
        $ch = curl_init();
        // 设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, "http://caigou.yibainetwork.com/v1/alibaba/get-logistics?account=".$bind_account);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 抓取URL并把它传递给浏览器
        $data = curl_exec($ch);
        //关闭cURL资源，并且释放系统资源
        curl_close($ch);

        /*$logistics_url     = 'http://caigou.yibainetwork.com/v1/alibaba/get-logistics';
        $data = ['account'=>$bind_account];
        $pull_logistics = $this->curl($logistics_url,$data);
//        $data = json_decode($pull_logistics,TRUE);
        return $pull_logistics;*/
    }
    public function actionSelectPullLogistics()
    {
        if (Yii::$app->request->isPost) {
            $account = trim(\Yii::$app->request->post('account'));
            $username = trim(\Yii::$app->request->post('bind_account'));

            if (!empty($username)) {
                $user_info = $this->getUser(null,$username);
                if (empty($user_info)) {
                    return json_encode(['error' => 500, 'msg'=>'没有该用户--' . $username]);
                }
            }

            $bind_account = !empty($user_info[0]['id']) ? $user_info[0]['id'] : null;
            $alibaba_account = $this->selectAlibabaAccount($account,$bind_account);

            if (!empty($alibaba_account) && !empty($alibaba_account[1])) {
                foreach ($alibaba_account[1] as $k=>$v) {
                    $user_info = $this->getUser($v['bind_account']);
                    $alibaba_account[1][$k]['username'] = !empty($user_info[0]['username']) ? $user_info[0]['username'] : '未知用户';
                }
            }
            return json_encode(['error' => 1, 'alibaba_account'=>$alibaba_account]);
        } else {
            return $this->renderPartial('_form-pull-logistics');
        }
    }
    /**
     * ============================ 修改：采购单仓库 ============================
     */
    public function actionUpdatePurchaseOrderWarehouseCode()
    {
        $pur_number = trim(\Yii::$app->request->post('pur_number'));
        $warehouse_code_new = trim(\Yii::$app->request->post('warehouse_code'));
        $transit_warehouse_new = trim(\Yii::$app->request->post('transit_warehouse'));
        if (empty($warehouse_code_new) && empty($transit_warehouse_new)) {
            $data[] = ['color'=>'red','msg'=>'请选择仓或中转仓'];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：修改失败！！ ********'. date('H:i:s',time())];
            return json_encode(['error' => 1, 'message'=>$data]);
        }

        $get_order_info = PurchaseOrder::find()->where(['pur_number'=>$pur_number])->asArray()->one();
        $warehouse_code_old = !empty($get_order_info['warehouse_code'])?$get_order_info['warehouse_code'] : '';
        $transit_warehouse_old = !empty($get_order_info['transit_warehouse'])?$get_order_info['transit_warehouse'] : '';

        if (!empty($get_order_info)) {
            if (!empty($warehouse_code_new)) {
                $update['warehouse_code'] = $warehouse_code_new;
            }
            if (!empty($transit_warehouse_new)) {
                $update['transit_warehouse'] = $transit_warehouse_new;
            }

            $status = PurchaseOrder::updateAll($update,['id'=>$get_order_info['id']]);

            //保存到日志
            $log['type']=22;
            $log['pid']= null;
            $log['pur_number']=$pur_number;
            $log['module']='修改：采购单仓库';
            $log['content']="旧：warehouse_code={$warehouse_code_old}，transit_warehouse={$transit_warehouse_old}=======新：warehouse_code={$warehouse_code_new}，transit_warehouse={$transit_warehouse_new}===相关表：pur_purchase_order";
            Vhelper::setOperatLog($log);

            $data[] = ['color'=>'green','msg'=>'******* 最终结果：修改成功！！ ********'. date('H:i:s',time())];
        } else {
            $data[] = ['color'=>'red','msg'=>'旧的--仓库错误'];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：修改失败！！ ********'. date('H:i:s',time())];
        }
        return json_encode(['error' => 1, 'message'=>$data]);
    }
    public function actionSelectPurchaseOrderWarehouseCode()
    {
        if (Yii::$app->request->isPost) {
            $pur_number = trim(\Yii::$app->request->post('pur_number'));

            $purchase_order_info = $this->selectPurchaseOrderInfo($pur_number);
            return json_encode(['error' => 1, 'purchase_order'=>$purchase_order_info]);
        } else {
            return $this->renderPartial('_form-update-purchase-order-warehouse-code');
        }

    }
    /**
     * ============================ 删除数据 ============================
     */
    public function actionDeleteData()
    {
        if (Yii::$app->request->isPost == false) {
            return $this->renderPartial('_form-delete-data');
        }
        $data_table = trim(\Yii::$app->request->post('data_table'));
        $id = trim(\Yii::$app->request->post('id'));
        // vd($id);


        try {
            $sql= 'SELECT * FROM ' . $data_table . ' WHERE id in(' . $id . ')';
            $select_rows=\Yii::$app->db->createCommand($sql)->queryAll();
            if (empty($select_rows)) {
                $data[] = ['color'=>'red','msg'=>'数据表或id有误'];
                $data[] = ['color'=>'red','msg'=>'******* 最终结果：修改失败！！ ********'. date('H:i:s',time())];
                return json_encode(['error' => 1, 'message'=>$data]);
            }

            foreach ($select_rows as $key => $rows) {
                if (!empty($rows)) {
                    //unset($rows['id']);
                    $json_rows = json_encode($rows);
                } else {
                    $data[] = ['color'=>'red','msg'=>'数据表或id有误'];
                    $data[] = ['color'=>'red','msg'=>'******* 最终结果：修改失败！！ ********'. date('H:i:s',time())];
                    return json_encode(['error' => 1, 'message'=>$data]);
                }

                //删除
                $status = \Yii::$app->db->createCommand()
                    ->delete($data_table, 'id = ' . $rows['id'])
                    ->execute();

                if (!empty($status)) {
                    //保存到日志
                    $log['type']=23;
                    $log['pid']= null;
                    $log['pur_number']=$data_table;
                    $log['module']='删除数据，table(' . $data_table .'),id(' . $rows['id'] . ')';
                    $log['content']=$json_rows;
                    Vhelper::setOperatLog($log);

                    $return_id = \Yii::$app->db->getLastInsertID();

                    $data[] = ['color'=>'green','msg'=>'删除表：' . $data_table . '<br />返回日志中的：ID=' . $return_id . '<br />' . $json_rows];
                    $data[] = ['color'=>'green','msg'=>'******* 最终结果：修改成功！！ ********'. date('H:i:s',time())];

                } else {
                    $data[] = ['color'=>'red','msg'=>'******* 最终结果：不能重复删除！！ ********'. date('H:i:s',time())];
                    return json_encode(['red' => 1, 'message'=>$data]);
                }
            }

            return json_encode(['error' => 1, 'message'=>$data]);
        } catch (\Exception $e) {
            $data[] = ['color'=>'red','msg'=>'数据表或id有误'];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：修改失败！！ ********'. date('H:i:s',time())];
            return json_encode(['error' => 1, 'message'=>$data]);
        }
    }
    /**
     * ============================ 还原数据：被删除的数据 ============================
     */
    public function actionRestoreData()
    {
        //接收表名和id
        $data_table = trim(\Yii::$app->request->post('data_table'));
        $id = trim(\Yii::$app->request->post('id'));

        //根据日志，查到表名和相应的数据
        $operat_log = OperatLog::find()->where(['id'=>$id,'type'=>23])->asArray()->one();
        if (empty($operat_log)) {
            $data[] = ['color'=>'red','msg'=>'你输入的id有误'];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：修改失败！！ ********'. date('H:i:s',time())];
            return json_encode(['error' => 1, 'message'=>$data]);
        }

        $content = json_decode($operat_log['content']);
        $arr = (array)$content;

        $operat_log_table = $operat_log['pur_number'];

        if ($data_table != $operat_log_table) {
            $data[] = ['color'=>'red','msg'=>'你输入的表名有误'];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：修改失败！！ ********'. date('H:i:s',time())];
            return json_encode(['error' => 1, 'message'=>$data]);
        }

        $status = \Yii::$app->db->createCommand()->insert($operat_log_table, $arr)->execute();
        $return_id = \Yii::$app->db->getLastInsertID();
        if (!empty($status)) {
            //保存到日志
            $log['type']=24;
            $log['pid']= null;
            $log['pur_number'] = $return_id;
            $log['module']='还原数据，table(' . $data_table .'),id(' . $return_id . ')';
            $log['content']=$operat_log['content'];
            Vhelper::setOperatLog($log);

            $data[] = ['color'=>'green','msg'=>'插入表：' .$operat_log_table .'<br />返回此表：ID=' . $return_id . '<br />' . $operat_log['content']];
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：修改成功！！ ********'. date('H:i:s',time())];
            return json_encode(['error' => 1, 'message'=>$data]);
        } else {
            $data[] = ['color'=>'red','msg'=>'数据已还原'];
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：修改成功！！ ********'. date('H:i:s',time())];
            return json_encode(['error' => 1, 'message'=>$data]);
        }
    }
    /**
     * ============================  修改运费或优惠金额和采购数量   ============================
     */
    public function actionUpdateShipDiscountPrice()
    {
        $pur_number = trim(\Yii::$app->request->post('pur_number'));
        $freight = trim(\Yii::$app->request->post('freight'));
        $discount_price = trim(\Yii::$app->request->post('discount_price'));

        //查询到以前的记录
        $purchase_order_ship_info = $this->selectPurchaseOrderShip($pur_number);
        $purchase_discount_info = $this->selectPurchaseDiscount($pur_number);
        $purchase_order_pay_detail_info = $this->selectPurchaseOrderPayDetail($pur_number);
        $purchase_order_pay_type_info = $this->selectPurchaseOrderPayType($pur_number);

        //获取到以前的运费
        $new_freight = $freight;
        $old_freight = !empty($purchase_order_ship_info[1]) ? $purchase_order_ship_info[1]['freight'] : null;
        $pay_detail_freight = !empty($purchase_order_pay_detail_info[1]) ? $purchase_order_pay_detail_info[1]['freight'] : null;
        $pay_type_freight = !empty($purchase_order_pay_type_info[1]) ? $purchase_order_pay_type_info[1]['freight'] : null;

        //获取到以前的优惠
        $new_discount_price = $discount_price;
        $old_discount_price = !empty($purchase_discount_info[1]) ? $purchase_discount_info[1]['discount_price'] : null;
        $pay_detail_discount = !empty($purchase_order_pay_detail_info[1]) ? $purchase_order_pay_detail_info[1]['discount'] : null;
        $pay_type_discount = !empty($purchase_order_pay_type_info[1]) ? $purchase_order_pay_type_info[1]['discount'] : null;

        $total_price = !empty($purchase_discount_info[1]) ? $purchase_discount_info[1]['total_price'] : null;

        $transaction=\Yii::$app->db->beginTransaction();
        try {
            if ($freight !== '') {
                $purchase_order_ship_status = PurchaseOrderShip::updateAll(['freight'=>$freight],['pur_number'=>$pur_number]);
                $purchase_order_pay_type_status = PurchaseOrderPayType::updateAll(['freight'=>$freight],['pur_number'=>$pur_number]);
                $purchase_order_pay_type_status = PurchaseOrderPayDetail::updateAll(['freight'=>$freight],['pur_number'=>$pur_number]);
            }

            if ($discount_price !== '') {
                $purchase_discount_status = PurchaseDiscount::updateAll(['discount_price'=>$discount_price],['pur_number'=>$pur_number]);
                $purchase_order_ship_status = PurchaseOrderPayType::updateAll(['discount'=>$discount_price],['pur_number'=>$pur_number]);
                $purchase_order_ship_status = PurchaseOrderPayDetail::updateAll(['discount'=>$discount_price],['pur_number'=>$pur_number]);
            }

            //优惠后总金额：采购总金额+运费-优惠金额
            $new_freight_a = ($new_freight !== '') ? ($new_freight - $old_freight) : 0;
            $new_discount_price_a = ($new_discount_price !== '') ? ($new_discount_price - $old_discount_price) : 0;
            $total_price = $total_price + $new_freight_a - $new_discount_price_a;
            PurchaseDiscount::updateAll(['total_price'=>$total_price],['pur_number'=>$pur_number]);

            //保存到日志
            $log['type']=25;
            $log['pid']= null;
            $log['pur_number']=$pur_number;
            $log['module']='修改运费或优惠金额';
            $log['content']="表【字段（旧）】：pur_purchase_order_pay_type【freight({$pay_type_freight})discount({$pay_type_discount})】pur_purchase_order_ship（freight={$old_freight}）pur_purchase_discount（discount_price={$old_discount_price}）pur_purchase_order_pay_detail【freight({$pay_detail_freight})discount({$pay_detail_discount})】=======新的：freight={$new_freight} -- discount_price={$new_discount_price}";
            Vhelper::setOperatLog($log);

            $transaction->commit();
            $data[] = ['color'=>'green','msg'=>'修改成功--' . $pur_number];
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：执行成功！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
            $transaction->rollBack();
        }
    }
    public function actionSelectShipDiscountPrice()
    {

        if (Yii::$app->request->isPost) {
            $pur_number = trim(\Yii::$app->request->post('pur_number'));
            $purchase_order_ship_info = $this->selectPurchaseOrderShip($pur_number);
            $purchase_discount_info = $this->selectPurchaseDiscount($pur_number);
            $purchase_order_pay_type_info = $this->selectPurchaseOrderPayType($pur_number);
            $purchase_order_pay_detail_info = $this->selectPurchaseOrderPayDetail($pur_number);

            return json_encode(['error' => 1, 'purchase_order_ship_info'=>$purchase_order_ship_info,'purchase_discount_info'=>$purchase_discount_info,'purchase_order_pay_type_info' => $purchase_order_pay_type_info,'purchase_order_pay_detail_info' => $purchase_order_pay_detail_info]);
        } else {
            return $this->renderPartial('_form-update-ship-discount-price');
        }
    }
    /**
     * ============================  修改单价   ============================
     */
    public function actionUpdateOrderItemsPrice()
    {
        $pur_number = trim(\Yii::$app->request->post('pur_number'));
        $sku = trim(\Yii::$app->request->post('sku'));
        $new_price = trim(\Yii::$app->request->post('price'));
        $new_ctq = trim(\Yii::$app->request->post('ctq'));
        $new_name = trim(\Yii::$app->request->post('name'));

        //查询到以前的记录
        $purchase_order_items_info = $this->selectPurchaseOrderItems($pur_number,$sku);
        if (count($purchase_order_items_info[1]) == 1) {
            $old_price = $purchase_order_items_info[1][0]['price'];
            $old_ctq = !empty($purchase_order_items_info[1][0]['ctq']) ? $purchase_order_items_info[1][0]['ctq'] : '';
            $old_name = !empty($purchase_order_items_info[1][0]['name']) ? $purchase_order_items_info[1][0]['name'] : '';
        } else {
            $data[] = ['color'=>'red','msg'=>'数据不唯一' . date('H:i:s',time())];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        }
        if (empty($new_price) && empty($new_ctq) && empty($new_name)) {
            $data[] = ['color'=>'red','msg'=>'请填入单价或数量或名称' . date('H:i:s',time())];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        }

        $transaction=\Yii::$app->db->beginTransaction();
        try {
            if ($new_ctq !=='') {
                $update_ctq = $new_ctq;
            } else {
                $update_ctq = $old_ctq;
            }
            if ($new_price !== '') {
                $update_price = $new_price;
            } else {
                $update_price = $old_price;
            }

            if ($new_name !== '') {
                $update['name'] = $new_name;
                PurchaseOrderItems::updateAll(['name'=> $new_name],['pur_number'=>$pur_number,'sku' => $sku]);
            }
            $items_totalprice = $update_price * $update_ctq;
            PurchaseOrderItems::updateAll(['price'=>$update_price,'ctq'=> $update_ctq,'items_totalprice'=> $items_totalprice],['pur_number'=>$pur_number,'sku' => $sku]);

            //保存到日志
            $log['type']=26;
            $log['pid']= null;
            $log['pur_number']=$pur_number . '--' . $sku;
            $log['module']='修改订单单价';
            $log['content']="旧：price={$old_price},ctq={$old_ctq},name={$old_name}=======新：price={$new_price},ctq={$new_ctq},name={$new_name}【{$pur_number} -- {$sku}】===相关表：pur_purchase_order_items";
            Vhelper::setOperatLog($log);

            $transaction->commit();
            $data[] = ['color'=>'green','msg'=>'修改成功--' . $pur_number . '--' . $sku];
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：执行成功！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
            $transaction->rollBack();
        }
    }
    public function actionSelectOrderItemsPrice()
    {

        if (Yii::$app->request->isPost) {
            $pur_number = trim(\Yii::$app->request->post('pur_number'));
            $sku = trim(\Yii::$app->request->post('sku'));
            $purchase_order_items_info = $this->selectPurchaseOrderItems($pur_number,$sku);

            return json_encode(['error' => 1, 'purchase_order_items_info'=>$purchase_order_items_info]);
        } else {
            return $this->renderPartial('_form-update-order-items-price');
        }
    }
    /**
     * ============================  修改--入库信息   ============================
     */
    public function actionSelectWarehouseResults()
    {
        if (Yii::$app->request->isPost) {
            $pur_number = trim(\Yii::$app->request->post('pur_number'));
            $sku = trim(\Yii::$app->request->post('sku'));
            $warehouse_results_info = $this->selectWarehouseResults($pur_number,$sku);

            return json_encode(['error' => 1,'purchase_warehouse_results_info'=>$warehouse_results_info]);
        } else {
            return $this->renderPartial('_form-update-warehouse-results');
        }
    }
    public function actionUpdateWarehouseResults()
    {
        $pur_number = trim(\Yii::$app->request->post('pur_number'));
        $sku = trim(\Yii::$app->request->post('sku'));
        $purchase_quantity = trim(\Yii::$app->request->post('purchase_quantity'));
        $arrival_quantity = trim(\Yii::$app->request->post('arrival_quantity'));
        $nogoods = trim(\Yii::$app->request->post('nogoods'));
        $have_sent_quantity = trim(\Yii::$app->request->post('have_sent_quantity'));
        $instock_qty_count = trim(\Yii::$app->request->post('instock_qty_count'));
        $instock_user = trim(\Yii::$app->request->post('instock_user'));
        $instock_date = trim(\Yii::$app->request->post('instock_date'));

//查询到以前的记录
        $warehouse_results_info = $this->selectWarehouseResults($pur_number,$sku);
        if (count($warehouse_results_info[1]) == 1) {
            $old_purchase_quantity = !empty($warehouse_results_info[1][0]['purchase_quantity']) ? $warehouse_results_info[1][0]['purchase_quantity'] : '';
            $old_arrival_quantity = !empty($warehouse_results_info[1][0]['arrival_quantity']) ? $warehouse_results_info[1][0]['arrival_quantity'] : '';
            $old_nogoods = !empty($warehouse_results_info[1][0]['nogoods']) ? $warehouse_results_info[1][0]['nogoods'] : '';
            $old_have_sent_quantity = !empty($warehouse_results_info[1][0]['have_sent_quantity']) ? $warehouse_results_info[1][0]['have_sent_quantity'] : '';
            $old_instock_qty_count = !empty($warehouse_results_info[1][0]['instock_qty_count']) ? $warehouse_results_info[1][0]['instock_qty_count'] : '';
            $old_instock_user = !empty($warehouse_results_info[1][0]['instock_user']) ? $warehouse_results_info[1][0]['instock_user'] : '';
            $old_instock_date = !empty($warehouse_results_info[1][0]['instock_date']) ? $warehouse_results_info[1][0]['instock_date'] : '';
        } else {
            $data[] = ['color'=>'red','msg'=>'数据不唯一' . date('H:i:s',time())];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        }
        if (($purchase_quantity==null) && ($arrival_quantity==null) && ($have_sent_quantity==null) && ($have_sent_quantity==null) && ($have_sent_quantity==null) && ($instock_qty_count==null) && empty($instock_user) && empty($instock_date) && ($nogoods==null)) {
            $data[] = ['color'=>'red','msg'=>'请填写修改内容' . date('H:i:s',time())];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        }
        $transaction=\Yii::$app->db->beginTransaction();
        try {
            if ($purchase_quantity!=null) {
                $update['purchase_quantity'] = $purchase_quantity;
            }
            if ($arrival_quantity!=null) {
                $update['arrival_quantity'] = $arrival_quantity;
            }
            if ($nogoods != null) {
                $update['nogoods'] = $nogoods;
            }
            if ($have_sent_quantity !=null) {
                $update['have_sent_quantity'] = $have_sent_quantity;
            }
            if ($instock_qty_count !=null) {
                $update['instock_qty_count'] = $instock_qty_count;
            }
            if (!empty($instock_user)) {
                $update['instock_user'] = $instock_user;
            }
            if (!empty($instock_date)) {
                $update['instock_date'] = $instock_date;
            }

            WarehouseResults::updateAll($update,['pur_number'=>$pur_number,'sku' => $sku]);

            //保存到日志
            $log['type']=27;
            $log['pid']= null;
            $log['pur_number']=$pur_number . '--' . $sku;
            $log['module']='修改入库信息';
            $log['content']="旧：purchase_quantity={$old_purchase_quantity},arrival_quantity={$old_arrival_quantity},nogoods={$old_nogoods}，have_sent_quantity={$old_have_sent_quantity},instock_qty_count={$old_instock_qty_count},instock_user={$old_instock_user}，instock_date={$old_instock_date}=======新：purchase_quantity={$purchase_quantity},arrival_quantity={$arrival_quantity},nogoods={$nogoods}，have_sent_quantity={$have_sent_quantity},instock_qty_count={$instock_qty_count},instock_user={$instock_user}，instock_date={$instock_date}【{$pur_number} -- {$sku}】===相关表：pur_warehouse_results";
            Vhelper::setOperatLog($log);

            $transaction->commit();
            $data[] = ['color'=>'green','msg'=>'修改成功--' . $pur_number . '--' . $sku];
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：执行成功！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
            $transaction->rollBack();
        }

    }
    /**
     * ============================  修改--采购到货记录   ============================
     */
    public function actionSelectArrivalRecord()
    {
        if (Yii::$app->request->isPost) {
            $id = trim(\Yii::$app->request->post('id'));
            $pur_number = trim(\Yii::$app->request->post('pur_number'));
            $sku = trim(\Yii::$app->request->post('sku'));
            $qc_id = trim(\Yii::$app->request->post('qc_id'));

            $arrival_record_info = $this->selectArrivalRecord($id,$pur_number,$sku,$qc_id);

            return json_encode(['error' => 1,'arrival_record_info'=>$arrival_record_info]);
        } else {
            return $this->renderPartial('_form-update-arrival-record');
        }
    }
    public function actionUpdateArrivalRecord()
    {
        $new_json = json_encode(\Yii::$app->request->post());
        $id = trim(\Yii::$app->request->post('id'));
        $pur_number = trim(\Yii::$app->request->post('purchase_order_no'));
        $sku = trim(\Yii::$app->request->post('sku'));
        $qc_id = trim(\Yii::$app->request->post('qc_id'));
        $new_sku = trim(\Yii::$app->request->post('new_sku'));
        $name = trim(\Yii::$app->request->post('name'));
        $delivery_qty = trim(\Yii::$app->request->post('delivery_qty'));
        $delivery_time = trim(\Yii::$app->request->post('delivery_time'));
        $delivery_user = trim(\Yii::$app->request->post('delivery_user'));
        $check_type = trim(\Yii::$app->request->post('check_type'));
        $bad_products_qty = trim(\Yii::$app->request->post('bad_products_qty'));
        $check_time = trim(\Yii::$app->request->post('check_time'));
        $check_user = trim(\Yii::$app->request->post('check_user'));


        //查询到以前的记录
        $arrival_record_info = $this->selectArrivalRecord($id,$pur_number,$sku,$qc_id);
        if (count($arrival_record_info[1]) == 1) {
            $old_json = json_encode($arrival_record_info[1][0]);
        } else {
            $data[] = ['color'=>'red','msg'=>'数据不唯一' . date('H:i:s',time())];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        }

        if (($qc_id==null) && empty($new_sku) && empty($name) && ($delivery_qty==null) && empty($delivery_time) && empty($delivery_user) && empty($check_type) && ($bad_products_qty==null) && empty($check_time) && empty($check_user)) {
            $data[] = ['color'=>'red','msg'=>'请填写修改内容' . date('H:i:s',time())];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        }

        $transaction=\Yii::$app->db->beginTransaction();
        try {
            if ($qc_id!=null) {
                $update['qc_id'] = $qc_id;
            }
            if (!empty($new_sku)) {
                $update['sku'] = $new_sku;
            }
            if (!empty($name)) {
                $update['name'] = $name;
            }
            if ($delivery_qty!=null) {
                $update['delivery_qty'] = $delivery_qty;
            }
            if (!empty($delivery_time)) {
                $update['delivery_time'] = $delivery_time;
            }
            if (!empty($delivery_user)) {
                $update['delivery_user'] = $delivery_user;
            }
            if (!empty($check_type)) {
                $update['check_type'] = $check_type;
            }
            if ($bad_products_qty!=null) {
                $update['bad_products_qty'] = $bad_products_qty;
            }
            if (!empty($check_time)) {
                $update['check_time'] = $check_time;
            }
            if (!empty($check_user)) {
                $update['check_user'] = $check_user;
            }


            if (!empty($id)) {
                $where['id']  = $id;
            }
            if (!empty($pur_number)) {
                $where['purchase_order_no']  = $pur_number;
            }
            if (!empty($sku)) {
                $where['sku']  = $sku;
            }
            if (!empty($qc_id)) {
                $where['qc_id']  = $qc_id;
            }

            ArrivalRecord::updateAll($update,$where);

            //保存到日志
            $log['type']=28;
            $log['pid']= null;
            $log['pur_number']=$pur_number . '--' . $sku;
            $log['module']='修改采购到货记录';
            $log['content']="旧：{$old_json}=======新：{$new_json}===相关表：pur_arrival_record";
            Vhelper::setOperatLog($log);

            $transaction->commit();
            $data[] = ['color'=>'green','msg'=>'修改成功--' . $pur_number . '--' . $sku];
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：执行成功！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
            $transaction->rollBack();
        }

    }
    /**
     * ============================  修改派单号   ============================
     */
    public function actionUpdateOrderNumber()
    {
        $pur_number = trim(\Yii::$app->request->post('pur_number'));
        $order_number_new = trim(\Yii::$app->request->post('order_number'));

        if (empty($order_number_new)) {
            $data[] = ['color'=>'red','msg'=>'请填写派单号' . date('H:i:s',time())];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        }


        //查询到以前的记录
        $purchase_order_orders_info = $this->selectPurchaseOrderOrders($pur_number);
        $purchase_order_pay_type_info = $this->selectPurchaseOrderPayType($pur_number);

        //获取到以前的派单号
        $order_number_old = !empty($purchase_order_orders_info[1]['order_number']) ? $purchase_order_orders_info[1]['order_number'] : null;
        $platform_order_number_old = !empty($purchase_order_pay_type_info[1]['platform_order_number']) ? $purchase_order_pay_type_info[1]['platform_order_number'] : null;

        $transaction=\Yii::$app->db->beginTransaction();
        try {
            $purchase_order_orders_status = PurchaseOrderOrders::updateAll(['order_number'=>$order_number_new],['pur_number'=>$pur_number]);
            $purchase_order_pay_type_status = PurchaseOrderPayType::updateAll(['platform_order_number'=>$order_number_new],['pur_number'=>$pur_number]);

            //保存到日志
            $log['type']=29;
            $log['pid']= null;
            $log['pur_number']=$pur_number;
            $log['module']='修改派单号';
            $log['content']="旧：order_number={$order_number_old}，platform_order_number={$platform_order_number_old}=======新：order_number={$order_number_new}===相关表：pur_purchase_order_orders,pur_purchase_order_pay_type";
            Vhelper::setOperatLog($log);

            $transaction->commit();
            $data[] = ['color'=>'green','msg'=>'修改成功--' . $pur_number];
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：执行成功！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
            $transaction->rollBack();
        }
    }
    public function actionSelectOrderNumber()
    {

        if (Yii::$app->request->isPost) {
            $pur_number = trim(\Yii::$app->request->post('pur_number'));
            $purchase_order_orders_info = $this->selectPurchaseOrderOrders($pur_number);
            $purchase_order_pay_type_info = $this->selectPurchaseOrderPayType($pur_number);

            return json_encode(['error' => 1,'purchase_order_orders_info'=>$purchase_order_orders_info,'purchase_order_pay_type_info' => $purchase_order_pay_type_info]);
        } else {
            return $this->renderPartial('_form-update-order-number');
        }
    }
    /**
     * ============================  修改--采购需求   ============================
     */
    public function actionSelectPlatformSummary()
    {
        if (Yii::$app->request->isPost) {
            $sku = trim(\Yii::$app->request->post('sku'));
            $demand_number = trim(\Yii::$app->request->post('demand_number'));
            $platform_summary_info = $this->selectPlatformSummary($sku,$demand_number);

            return json_encode(['error' => 1,'platform_summary_info'=>$platform_summary_info]);
        } else {
            return $this->renderPartial('_form-update-platform-summary');
        }
    }
    public function actionUpdatePlatformSummary()
    {
        $sku_old = trim(\Yii::$app->request->post('sku_old'));
        $demand_number = trim(\Yii::$app->request->post('demand_number'));
        $platform_number_old = PlatformSummary::find()->where(['sku'=>$sku_old,'demand_number'=>$demand_number])->asArray()->one();
        if (empty($platform_number_old)) {
            $data[] = ['color'=>'red','msg'=>'数据未找到-----' . date('H:i:s',time())];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        }
        $new_info = [];
        $post_info = \Yii::$app->request->post();
        unset($post_info['sku_old']);
        foreach ($post_info as $k=>$v) {
            $value = trim($v);
            if (!empty($value) || $value=='0') {
                $new_info[$k] = $v;
            }
        }
        $platform_number_old = json_encode($platform_number_old);
        $platform_number_new = json_encode($new_info);

        $where = ['sku'=>$sku_old,'demand_number'=>$demand_number];
        $transaction=\Yii::$app->db->beginTransaction();
        try {
            $status = PlatformSummary::updateAll($new_info,$where);
            if ($status == 0) {
                $data[] = ['color'=>'red','msg'=>'数据未被修改-----' . date('H:i:s',time())];
                $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
                return json_encode(['error' => 1, 'message' => $data]);
            }
            //保存到日志
            $log['type']=40;
            $log['pid']= null;
            $log['pur_number']=$sku_old . '--' .  $demand_number;
            $log['module']='修改派单号';
            $log['content']="旧：{$platform_number_old}=======新：{$platform_number_new}===相关表：pur_platform_summary";
            Vhelper::setOperatLog($log);

            $transaction->commit();
            $data[] = ['color'=>'green','msg'=>'修改成功--' . $sku_old];
            $data[] = ['color'=>'green','msg'=>'修改成功--' . $demand_number];
            $data[] = ['color'=>'green','msg'=>$platform_number_new];
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：执行成功！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
            $transaction->rollBack();
        }
    }
    /**
     * ============================  修改--供应商整合   ============================
     */
    public function actionSelectSupplierUpdateApply(){
        if (Yii::$app->request->isPost) {
            $sku = trim(\Yii::$app->request->post('sku'));
            $id = trim(\Yii::$app->request->post('id'));
            $supplier_update_apply_info = $this->selectSupplierUpdateApply($sku,$id);

            return json_encode(['error' => 1,'supplier_update_apply_info'=>$supplier_update_apply_info]);
        } else {
            return $this->renderPartial('_form-update-supplier-update-apply');
        }
    }
    public function actionUpdateSupplierUpdateApply()
    {
        $sku = trim(\Yii::$app->request->post('sku'));
        $id = trim(\Yii::$app->request->post('id'));

        $supplier_update_apply_old = SupplierUpdateApply::find()->where(['id'=>$id])->asArray()->one();
        if (empty($supplier_update_apply_old)) {
            $data[] = ['color'=>'red','msg'=>'数据未找到-----' . date('H:i:s',time())];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        }

        $new_info = [];
        $post_info = \Yii::$app->request->post();
        foreach ($post_info as $k=>$v) {
            $value = trim($v);
            if (!empty($value) || $value=='0') {
                $new_info[$k] = $v;
            }
        }
//        $supplier_update_apply_old = json_encode($supplier_update_apply_old);
        $supplier_update_apply_new = json_encode($new_info);

        $where = ['id'=>$id];
        $transaction=\Yii::$app->db->beginTransaction();
        try {
            $status = SupplierUpdateApply::updateAll($new_info,$where);
            if ($status == 0) {
                $data[] = ['color'=>'red','msg'=>'数据未被修改-----' . date('H:i:s',time())];
                $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
                return json_encode(['error' => 1, 'message' => $data]);
            }
            //保存到日志
            $logData = ['data_table'=>'pur_supplier_update_apply','old' => $supplier_update_apply_old, 'new' => $new_info];
            OperatLog::AddLog([
                'type' => 41,
                'content' => json_encode($logData),
                'module' => '供应商整合',
                'pur_number' => $sku,
                'pid' => null
            ]);
            $transaction->commit();
            $data[] = ['color'=>'green','msg'=>'修改成功--' . $sku];
            $data[] = ['color'=>'green','msg'=>$supplier_update_apply_new];
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：执行成功！！********' . date('H:i:s',time())];
        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            $transaction->rollBack();
        }
        return json_encode(['error' => 1, 'message' => $data]);

    }
    /**
     * ============================  修改--样品检验   ============================
     */
    public function actionSelectSampleInspect(){
        if (Yii::$app->request->isPost) {
            $sku = trim(\Yii::$app->request->post('sku'));
            $id = trim(\Yii::$app->request->post('id'));
            $sample_inspect_info = $this->selectSampleInspect($sku,$id);

            return json_encode(['error' => 1,'sample_inspect_info'=>$sample_inspect_info]);
        } else {
            return $this->renderPartial('_form-update-sample-inspect');
        }
    }
    public function actionUpdateSampleInspect()
    {
        $sku = trim(\Yii::$app->request->post('sku'));
        $id = trim(\Yii::$app->request->post('id'));

        $sample_inspect_old = SampleInspect::find()->where(['id'=>$id])->asArray()->one();
        if (empty($sample_inspect_old)) {
            $data[] = ['color'=>'red','msg'=>'数据未找到-----' . date('H:i:s',time())];
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            return json_encode(['error' => 1, 'message' => $data]);
        }

        $transaction=\Yii::$app->db->beginTransaction();
        try {
            $new_info = [];
            $post_info = \Yii::$app->request->post();
            foreach ($post_info as $k=>$v) {
                $value = trim($v);
                if (!empty($value) || $value=='0') {
                    $new_info[$k] = $v;
                }
            }
            $where = ['id'=>$id];
            $status = SampleInspect::updateAll($new_info,$where);

            if ($status == 0) {
                $data[] = ['color'=>'red','msg'=>'数据未被修改-----' . date('H:i:s',time())];
                $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
                return json_encode(['error' => 1, 'message' => $data]);
            }
            //保存到日志
            $sample_inspect_new = json_encode($new_info);
            $logData = ['data_table'=>'pur_sample_inspect','old' => $sample_inspect_old, 'new' => $new_info];
            OperatLog::AddLog([
                'type' => 42,
                'content' => json_encode($logData),
                'module' => '供应商整合',
                'pur_number' => $sku,
                'pid' => null
            ]);
            $transaction->commit();
            $data[] = ['color'=>'green','msg'=>'修改成功--' . $sku];
            $data[] = ['color'=>'green','msg'=>$sample_inspect_new];
            $data[] = ['color'=>'green','msg'=>'******* 最终结果：执行成功！！********' . date('H:i:s',time())];
        } catch(Exception $e) {
            $data[] = ['color'=>'red','msg'=>'******* 最终结果：执行失败！！********' . date('H:i:s',time())];
            $transaction->rollBack();
        }
        return json_encode(['error' => 1, 'message' => $data]);

    }
    /**
     * ============================  修改数据   ============================
     */
    public function actionUpdateData()
    {
        $db=Yii::$app->getDb();
        $session = Yii::$app->session;

        if (Yii::$app->request->isPost) {
            $where_selected_01 = trim(\Yii::$app->request->post('where_selected_01'));
            $where_01 = trim(\Yii::$app->request->post('where_01'));

            $where_selected_02 = trim(\Yii::$app->request->post('where_selected_02'));
            $where_02 = trim(\Yii::$app->request->post('where_02'));

            $field_selected_01 = trim(\Yii::$app->request->post('field_selected_01'));
            $field_01 = trim(\Yii::$app->request->post('field_01'));

            $table_name = $session->get('update_table_name');

            //修改的条件
            if ($where_01 != '') {
                $where = "{$where_selected_01}='{$where_01}' ";
            } else {
                return json_encode(['error' => 1, 'message' => [['color'=>'red','msg'=>'【修改的条件01】的值不能为空']]]);
            }

            if ($where_02 !='') {
                $where .= "and {where_selected_02}='{$where_02}' ";
            }

            //被修改的数据
            if ($field_01 != '') {
                $field = "{$field_selected_01}='{$field_01}' ";
            } else {
                return json_encode(['error' => 1, 'message' => [['color'=>'red','msg'=>'【修改的数据01】的值不能为空']]]);
            }

            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $select_sql = "select * from {$table_name} WHERE {$where}";
                $res = Yii::$app->db->createCommand($select_sql)->queryAll();
                if (empty($res)) {
                    return json_encode(['error' => 1, 'message' => [['color'=>'red','msg'=>'数据不存在']]]);
                }
                $json_old = json_encode($res);

                $update_sql = "UPDATE {$table_name} SET {$field} WHERE {$where}";
                $status = Yii::$app->db->createCommand($update_sql)->execute();


                $select_sql = "select * from {$table_name} WHERE {$where}";
                $res = Yii::$app->db->createCommand($select_sql)->queryAll();

                $json_new = json_encode($res);
                //保存到日志
                $logData = ['data_table'=>$table_name,'old' => $json_old, 'new' => $json_new];
                OperatLog::AddLog([
                    'type' => 43,
                    'content' => json_encode($logData),
                    'module' => '修改数据',
                    'pur_number' => null,
                    'pid' => null
                ]);

                $transaction->commit();

                $data[] = ['color'=>'green','msg'=>$json_old];
                return json_encode(['error' => 1, 'message'=>$data]);
            } catch (\Exception $e) {
                $transaction->rollBack();

                $data[] = ['color'=>'red','msg'=>'******* 最终结果：修改失败！！ ********'. date('H:i:s',time())];
                return json_encode(['error' => 1, 'message'=> $data]);
            }
        } else if (Yii::$app->request->isAjax) {
            $table_name = trim(\Yii::$app->request->get('table_name'));

            $fields= $db->getSchema()->getTableSchema($table_name)->columns;//获取指定表中所有字段名
            foreach ($fields as $k => $v) {
                $fields[$k] = $v->comment;
            }
//            $fields = array_keys($fields);
            return json_encode($fields);
        } else {
            /*$mir = new Migration();
            $sch = new \yii\db\mysql\Schema;
            $tableName='shang';
            $table=$db->createCommand("SHOW TABLES LIKE '".$tableName."'")->queryAll();
            if($table==null)        {
                echo '1';
            }else{
                echo '2';
            }
             $mir->createTable('shang', [
                 'id' => 'pk',
                 'title' => $sch::TYPE_STRING . ' NOT NULL',
                 'content' => $sch::TYPE_TEXT,
             ]);
             $tables=$db->getSchema()->getTableSchemas(); //获取所有表的所有字段*/

            $tables=$db->getSchema()->getTableNames();//获取表名
            $table_name = trim(\Yii::$app->request->get('table_name'));
            if (empty($table_name)) {
                $table_name = 'pur_purchase_order';
            }
            $fields = [];
//            $tables=$db->getSchema()->getTableSchemas(); //获取所有数据库中的所有表的所有字段
//            $tables=$db->getSchema()->getTableSchemas('yb_purchase'); //获取yb_purchase数据库中的所有表的所有字段
            $fields= $db->getSchema()->getTableSchema($table_name)->columns;//获取指定表中所有字段名

            foreach ($fields as $k => $v) {
                $fields[$k] = $v->comment;
            }

            $session->set('update_table_name',$table_name);
            return $this->renderPartial('_form-update-data',[
                'table_name' => $table_name,
                'tables' => $tables,
                'fields' => $fields,
            ]);
        }
    }
    /**
     * ==============================================================================  获取 =========================================================================================================
     */
    public function getUser($id=null, $username=null)
    {
        $where = [];
        if (!empty($id)) {
            $where['id'] = $id;
        }
        if (!empty($username)) {
            $where['username'] = $username;
        }
        $user_info = User::find()->where($where)->asArray()->all();
        return $user_info;
    }
    /**
     * ==============================================================================  查看详情 =========================================================================================================
     */
    /**
     * ======== 订单主表 ========
     */
    public function selectPurchaseOrderInfo($pur_number) {
        $purchase_order=[];
        $purchase_order_info = PurchaseOrder::find()
//            ->select($select)
            ->where(['pur_number'=>$pur_number])
            ->asArray()
            ->one();

        if (!empty($purchase_order_info)) {
            $purchase_order[] = ['pur_number'=>'PO号', 'is_drawback'=>'是否退税', 'warehouse_code'=>'仓库编码','supplier_code'=>'供应商编码','supplier_name'=>'供应商名称','created_at'=>'创建时间','buyer'=>'采购员','purchas_status'=>'采购单状态','audit_time'=>'审核时间','pay_type'=>'支付方式','account_type'=>'结算方式','is_push'=>'是否推送','pay_status'=>'付款状态','refund_status'=>'退款状态','transit_warehouse'=>'中转仓'];
            $purchase_order_info['is_drawback'] = $purchase_order_info['is_drawback'] ==1 ?'不退':'退';
            $purchase_order_info['is_push'] = $purchase_order_info['is_push'] ==1 ? '推送': ($purchase_order_info['is_push']==2 ? '不推送' : '未推送');
            $purchase_order_info['purchas_status'] = !empty($purchase_order_info['purchas_status']) ? PurchaseOrderServices::getPurchaseStatus($purchase_order_info['purchas_status']) : '';
            $purchase_order_info['pay_status'] = !empty($purchase_order_info['pay_status']) ? PurchaseOrderServices::getPayStatusType($purchase_order_info['pay_status']):'';
            $purchase_order_info['warehouse_code'] = !empty($purchase_order_info['warehouse_code']) ? BaseServices::getWarehouseCode($purchase_order_info['warehouse_code']):'';
            $purchase_order_info['transit_warehouse'] = !empty($purchase_order_info['transit_warehouse']) ? BaseServices::getWarehouseCode($purchase_order_info['transit_warehouse']):'';
            $purchase_order_info['account_type'] = !empty($purchase_order_info['account_type']) ? SupplierServices::getSettlementMethod($purchase_order_info['account_type']) : '';
            $purchase_order_info['pay_type'] = !empty($purchase_order_info['pay_type']) ? SupplierServices::getDefaultPaymentMethod($purchase_order_info['pay_type']) : '';
            $purchase_order_info['refund_status'] = !empty($purchase_order_info['refund_status']) ? PurchaseOrderServices::getReceiptStatusCss($purchase_order_info['refund_status']) : '';

            $purchase_order[] = $purchase_order_info;
        }
        return $purchase_order;
    }
    /**
     * ======== 采购单支付表 ========
     */
    public function selectPurchaseOrderPayInfo($pur_number){
        $purchase_order_pay=[];
        $purchase_order_pay_info = PurchaseOrderPay::find()
//            ->select(['id','pur_number','is_drawback','is_push','buyer'])
            ->where(['pur_number'=>$pur_number])
            ->asArray()
            ->one();

        if (!empty($purchase_order_pay_info)) {
            $purchase_order_pay[] = ['pur_number'=>'PO号', 'pay_status'=>'付款状态','requisition_number'=>'申请单号','supplier_code'=>'供应商编码','settlement_method'=>'结算方式','pay_name'=>'名称','pay_price'=>'金额','create_notice'=>'创建备注','applicant'=>'申请人','auditor'=>'审核人','approver'=>'审批人','application_time'=>'申请时间','review_time'=>'审核时间','processing_time'=>'审批时间','pay_type'=>'支付方式（从供应商拉取）','currency'=>'币种','review_notice'=>'审核备注','cost_types'=>'费用类型','payer'=>'付款人','payer_time'=>'付款时间','payment_cycle'=>'支付周期','payment_notice'=>'付款备注'];
            $purchase_order_pay_info['pay_status'] = PurchaseOrderServices::getPayStatus($purchase_order_pay_info['pay_status']);
            $purchase_order_pay[] = $purchase_order_pay_info;
        }
        return $purchase_order_pay;
    }
    /**
     * ======== 采购建议表 ========
     */
    public function selectPurchaseSuggest($sku=null,$demand_number=null,$purchase_type=null,$is_purchase=null,$warehouse_name=null)
    {
        $suggest=[];
        $select = ['warehouse_code','warehouse_name','sku','name','supplier_code','supplier_name','buyer','buyer_id','replenish_type','qty','price','currency','payment_method','supplier_settlement','ship_method','is_purchase','created_at','creator','product_category_id','category_cn_name','on_way_stock','available_stock','stock','left_stock','sales_avg','type','safe_delivery','transit_code','purchase_type','demand_number','state','product_status','lack_total','qty_13'];

        /*$query = PurchaseSuggest::find()
            ->select($select)
//            ->andWhere(['purchase_type' => 4])
//            ->andWhere(['is_purchase'=>'Y'])
            ->where(['not', ['demand_number' => null]]);
        if (!empty($sku)) {
            $query = $query->andWhere(['sku' => $sku]);
        }
        if (!empty($supplier_name)) {
            $query = $query->andWhere(['supplier_name' => $supplier_name]);
        }
        if (!empty($purchase_type)) {
            $query = $query->andWhere(['purchase_type' => $purchase_type]);
        }
        if (!empty($is_purchase)) {
            $query = $query->andWhere(['is_purchase' => $is_purchase]);
        }
        if (!empty($warehouse_name)) {
            $query = $query->andWhere(['warehouse_name' => $warehouse_name]);
        }*/

        $query = PurchaseSuggest::find()->select($select);
        if (!empty($sku)) {
            $where['sku'] = $sku;
        }
        if (!empty($demand_number)) {
            $where['demand_number'] = $demand_number;
        }
        if (!empty($purchase_type)) {
            $where['purchase_type'] = $purchase_type;
        }
        if (!empty($is_purchase)) {
            $where['is_purchase'] = $is_purchase;
        }
        if (!empty($warehouse_name)) {
            $where['warehouse_name'] = $warehouse_name;
        }
        /*if (!empty($demand_number)) {
            $where['demand_number'] = 'not null';
        }*/
        $get_suggest_model = $query->where($where)->andWhere(['not', ['demand_number' => null]])->asArray()->all();

        if (!empty($get_suggest_model)) {
            $suggest[] = ['warehouse_code'=>'仓库编码', 'warehouse_name'=>'仓库名称','sku'=>'产品SKU','name'=>'产品名称','supplier_code'=>'供应商编码','supplier_name'=>'供应商名称','buyer'=>'采购员','buyer_id'=>'采购员ID','replenish_type'=>'补货类型','qty'=>'建议数据量','price'=>'单价','currency'=>'币种','payment_method'=>'支付方式','supplier_settlement'=>'供应商结算方式','ship_method'=>'运输方式','is_purchase'=>'是否生成采购建议','created_at'=>'创建时间','creator'=>'创建人','product_category_id'=>'分类ID','category_cn_name'=>'分类中文名','on_way_stock'=>'在途数量','available_stock'=>'可用数量','stock'=>'实际数量','left_stock'=>'欠货数量','type'=>'销量走势','safe_delivery'=>'安全交期','transit_code'=>'中转仓','purchase_type'=>'采购类型','demand_number'=>'需求单号','state'=>'处理状态','product_status'=>'产品状态'];

            foreach ($get_suggest_model as $sk=>$sv) {
                $get_suggest_model[$sk]['is_purchase'] = ($sv['is_purchase']=='N') ? '未生成' : '已生成';
                $get_suggest_model[$sk]['state'] = PurchaseOrderServices::getProcesStatus()[$sv['state']];
                $get_suggest_model[$sk]['product_status'] = isset($sv['product_status'])?SupplierGoodsServices::getProductStatus($sv['product_status']):"未知";
            }
            $suggest[] = $get_suggest_model;
        }
        return $suggest;
    }
    /**
     * ======== 采购单支付表 ========
     */
    public function selectPurchaseOrderReceipt($pur_number=null)
    {
        $order_receipt = [];
        $get_order_receipt_model = PurchaseOrderReceipt::find()
            ->where(['pur_number' => $pur_number])
            ->asArray()
            ->one();

        //采购单支付表
        if (!empty($get_order_receipt_model)) {
            $order_receipt[] = ['pur_number'=>'PO号', 'pay_status'=>'收款状态','pay_price'=>'金额','step'=>'退款方式'];
            $get_order_receipt_model['pay_status'] = PurchaseOrderServices::getReceiptStatus($get_order_receipt_model['pay_status']);
            $get_order_receipt_model['step'] = $get_order_receipt_model['step']==3?'部分退款':($get_order_receipt_model['step']==4?'全额退款':''); //步骤：3部分退款，4全额退款
            $order_receipt[] = $get_order_receipt_model;
        }
        return $order_receipt;
    }
    /**
     * ======== 产品表 ========
     */
    public function selectPurchaseProduct($sku=null){
        $product = [];
        $product_info = Product::find()->where(['sku'=>$sku])->asArray()->all();
        if (!empty($product_info)) {
            $product[] = ['sku'=>'sku', 'product_status'=>'产品状态','create_id'=>'开发人员','create_time'=>'开发时间','product_type'=>'是否捆绑','supplier_name'=>'供应商名'];
            foreach ($product_info as $pk=>$pv) {
                $product_info[$pk]['product_status'] = SupplierGoodsServices::getProductStatus($pv['product_status']);
                $product_info[$pk]['product_type'] = $pv['product_type'] ==1 ?'普通':'捆绑';
            }
            $product[] = $product_info;
        }
        return $product;
    }
    /**
     * ======== 库存记录 ========
     */
    public function selectStock($sku=null)
    {
        $stock = [];

        $stock_info = Stock::find()
            ->where(['sku'=>$sku])
            ->asArray()->all();

        if (!empty($stock_info)) {
            $stock[] = ['sku'=>'sku', 'stock'=>'实际数量','on_way_stock'=>'在途数量','available_stock'=>'可用数量','warehouse_code'=>'仓库编码','created_at'=>'创建时间','update_at'=>'更新时间','left_stock'=>'欠货数量','is_suggest'=>'是否生成采购建议'];
            foreach ($stock_info as $sk=>$sv) {
                $stock_info[$sk]['sku'] = $sv['sku'];
            }
            $stock[] = $stock_info;
        }
        return $stock;
    }
    /**
     * ======== 库存记录 ========
     */
    public function selectStockOwes($sku=null)
    {
        $stock_owes = [];
        $select = ['warehouse_code','sku','left_stock','status','statistics_date'];
        $stock_owes_info = StockOwes::find()
            ->select($select)
            ->where(['sku'=>$sku])
            ->asArray()->all();
        if (!empty($stock_owes_info)) {
            $stock_owes[] = ['sku'=>'sku', 'warehouse_code'=>'仓库编码','left_stock'=>'欠货数量','status'=>'状态','statistics_date'=>'欠货时间'];
            foreach ($stock_owes_info as $sk=>$sv) {
                $stock_owes_info[$sk]['status'] = $sv['status'];
            }
            $stock_owes[] = $stock_owes_info;
        }
        return $stock_owes;
    }
    /**
     * ======== curl ========
     */
    public function curl($url,$data=null)
    {
        $curl = new Curl();
        $res = $curl->setPostParams($data)->post($url);
        return $res;
    }
    /**
     * ======== 1688账号管理表 ========
     */
    public function selectAlibabaAccount($account=null,$bind_account=null)
    {
        $alibaba_account = [];

        if (!empty($account)) {
            $where['account'] = $account;
        }
        if (!empty($bind_account)) {
            $where['bind_account'] = $bind_account;
        }
        $alibaba_account_info = AlibabaAccount::find()->where($where)->asArray()->all();
        if (!empty($alibaba_account_info)) {
            $alibaba_account[] = ['account'=>'账号', 'status'=>'状态','bind_account'=>'绑定账户','username'=>'用户名'];
            $alibaba_account[] = $alibaba_account_info;
        }
        return $alibaba_account;
    }
    /**
     * ======== 供应商表 ========
     */
    public function selectSupplier($supplier_code=null)
    {
        $supplier = [];
        if (!empty($supplier_code)) {
            $where['supplier_code'] = $supplier_code;
        }
        $supplier_info = Supplier::find()->where($where)->asArray()->all();
        if (!empty($supplier_info)) {
            $supplier[] = ['supplier_code'=>'供应商代码','buyer'=>'采购员','merchandiser'=>'跟单员','main_category'=>'主营品类','supplier_name'=>'供应商名','supplier_type'=>'供应商类型','supplier_settlement'=>'供应商结算方式','payment_method'=>'支付方式','create_time'=>'创建时间','update_time'=>'修改时间','create_id'=>'创建人ID','update_id'=>'修改人ID','status'=>'状态','esupplier_name'=>'供应商英文名','is_push'=>'是否推送','payment_cycle'=>'支付周期类型'];
            foreach ($supplier_info as $sk=>$sv) {
                $supplier_info[$sk]['status'] = SupplierServices::getStatus($sv['status']);
                $supplier_info[$sk]['supplier_type'] = SupplierServices::getSupplierType($sv['supplier_type']);
                $supplier_info[$sk]['payment_cycle'] = SupplierServices::getPaymentCycle($sv['payment_cycle']);
                $supplier_info[$sk]['payment_method'] = SupplierServices::getDefaultPaymentMethod($sv['payment_method']);
                $supplier_info[$sk]['supplier_settlement'] = SupplierServices::getSettlementMethod($sv['supplier_settlement']);
            }
            $supplier[] = $supplier_info;
        }
        return $supplier;
    }
    /**
     * ======== 采购单-优惠表 ========
     */
    public function selectPurchaseDiscount($pur_number) {
        $purchase_discount=[];
        $purchase_discount_info = PurchaseDiscount::find()
//            ->select($select)
            ->where(['pur_number'=>$pur_number])
            ->asArray()
            ->one();

        if (!empty($purchase_discount_info)) {
            $purchase_discount[] = ['pur_number'=>'PO号','buyer'=>'采购员','discount_price'=>'优惠金额','total_price'=>'优惠后总金额'];
            $purchase_discount[] = $purchase_discount_info;
        }
        return $purchase_discount;
    }
    /**
     * ======== 采购单- 子表 ========
     */
    public function selectPurchaseOrderPayDetail($pur_number) {
        $purchase_order_pay_detail=[];
        $purchase_order_pay_detail_info = PurchaseOrderPayDetail::find()
//            ->select($select)
            ->where(['pur_number'=>$pur_number])
            ->asArray()
            ->one();

        if (!empty($purchase_order_pay_detail_info)) {
            $purchase_order_pay_detail[] = ['pur_number'=>'PO号','requisition_number'=>'申请单号','freight'=>'运费','discount'=>'优惠额','sku_list'=>'sku明细'];
            $purchase_order_pay_detail[] = $purchase_order_pay_detail_info;
        }
        return $purchase_order_pay_detail;
    }
    /**
     * ======== 采购单- 费用表 ========
     */
    public function selectPurchaseOrderPayType($pur_number) {
        $purchase_order_pay_type=[];
        $purchase_order_pay_type_info = PurchaseOrderPayType::find()
//            ->select($select)
            ->where(['pur_number'=>$pur_number])
            ->asArray()
            ->one();

        if (!empty($purchase_order_pay_type_info)) {
            $purchase_order_pay_type[] = ['pur_number'=>'PO号','request_type'=>'请款类型','freight'=>'运费','discount'=>'优惠额','freight_formula_mode'=>'运费计算方式','platform_order_number'=>'拍单号','pay_number'=>'支付单号','purchase_acccount'=>'网络采购对应平台的账号','is_request'=>'是否已请求过1688'];
            $purchase_order_pay_type[] = $purchase_order_pay_type_info;
        }
        return $purchase_order_pay_type;
    }
    /**
     * ======== 采购单- 派单号表 ========
     */
    public function selectPurchaseOrderOrders($pur_number)
    {
        $purchase_order_orders=[];
        $purchase_order_orders_info = PurchaseOrderOrders::find()
//            ->select($select)
            ->where(['pur_number'=>$pur_number])
            ->asArray()
            ->one();

        if (!empty($purchase_order_orders_info)) {
            $purchase_order_orders[] = ['pur_number'=>'PO号','order_number'=>'派单号','is_request'=>'是否已请求过1688','create_id'=>'当前账户','e_order_number'=>'订单号-对比用'];
            $purchase_order_orders[] = $purchase_order_orders_info;
        }
        return $purchase_order_orders;
    }
    /**
     * ======== 采购单物流信息表 ========
     */
    public function selectPurchaseOrderShip($pur_number) {
        $purchase_order_ship=[];
        $purchase_order_ship_info = PurchaseOrderShip::find()
//            ->select($select)
            ->where(['pur_number'=>$pur_number])
            ->asArray()
            ->one();

        if (!empty($purchase_order_ship_info)) {
            $purchase_order_ship[] = ['pur_number'=>'采购单号','express_no'=>'快递号','freight'=>'运费'];
            $purchase_order_ship[] = $purchase_order_ship_info;
        }
        return $purchase_order_ship;
    }
    /**
     * ======== 采购订单详表 ========
     */
    public function selectPurchaseOrderItems($pur_number=null,$sku=null) {
        $purchase_order_items=[];

        if (!empty($pur_number)) {
            $where['pur_number']  = $pur_number;
        }
        if (!empty($sku)) {
            $where['sku']  = $sku;
        }

        $purchase_order_items_info = PurchaseOrderItems::find()
            ->where($where)
            ->asArray()
            ->all();

        if (!empty($purchase_order_items_info)) {
            $purchase_order_items[] = ['pur_number'=>'采购单号','sku'=>'产品SKU','name'=>'产品名称','qty'=>'预期数量','price'=>'单价','ctq'=>'确认数量','rqy'=>'收货数量','cty'=>'上架数量','items_totalprice'=>'单条sku的总金额'];
            $purchase_order_items[] = $purchase_order_items_info;
        }
        return $purchase_order_items;
    }
    /**
     * ======== 入库结果表 ========
     */
    public function selectWarehouseResults($pur_number=null,$sku=null) {
        $warehouse_results=[];

        if (!empty($pur_number)) {
            $where['pur_number']  = $pur_number;
        }
        if (!empty($sku)) {
            $where['sku']  = $sku;
        }

        $warehouse_results_info = WarehouseResults::find()
            ->where($where)
            ->asArray()
            ->all();

        if (!empty($warehouse_results_info)) {
            $warehouse_results[] = ['pur_number'=>'采购单号','sku'=>'产品SKU','purchase_quantity'=>'采购数量','arrival_quantity'=>'到货数量','nogoods'=>'不良品数量','have_sent_quantity'=>'赠送数量','instock_qty_count'=>'上架数量','instock_user'=>'入库人','instock_date'=>'入库时间'];
            $warehouse_results[] = $warehouse_results_info;
        }
        return $warehouse_results;
    }
    /**
     * ======== 采购到货记录表 ========
     */
    public function selectArrivalRecord($id=null,$pur_number=null,$sku=null,$qc_id=null) {
        $arrival_record=[];

        if (!empty($id)) {
            $where['id']  = $id;
        }
        if (!empty($pur_number)) {
            $where['purchase_order_no']  = $pur_number;
        }
        if (!empty($sku)) {
            $where['sku']  = $sku;
        }
        if (!empty($qc_id)) {
            $where['qc_id']  = $qc_id;
        }

        $arrival_record_info = ArrivalRecord::find()
            ->where($where)
            ->asArray()
            ->all();

        if (!empty($arrival_record_info)) {
            $arrival_record[] = ['id'=>'ID','purchase_order_no'=>'采购单号','sku'=>'产品SKU','name'=>'产品名称','delivery_qty'=>'到货数量','delivery_time'=>'收货时间','delivery_user'=>'收货人','check_type'=>'品检类型','bad_products_qty'=>'次品数量','check_time'=>'品检时间','check_user'=>'品检人','qc_id'=>'数据的唯一标识','note'=>'备注'];
            $arrival_record[] = $arrival_record_info;
        }
        return $arrival_record;
    }
    /**
     * ======== 采购需求表 ========
     */
    public function selectPlatformSummary($sku=null,$demand_number=null)
    {
        $platform_summary=[];

        if (!empty($sku)) {
            $where['sku']  = $sku;
        }
        if (!empty($demand_number)) {
            $where['demand_number']  = $demand_number;
        }

        $platform_summary_info = PlatformSummary::find()
            ->where($where)
            ->asArray()
            ->all();

        if (!empty($platform_summary_info)) {
            $platform_summary[] = ['sku'=>'SKU','platform_number'=>'平台号','product_name'=>'产品名','purchase_quantity'=>'采购数量','purchase_warehouse'=>'采购仓','transit_warehouse'=>'中转仓','is_transit'=>'是否中转','create_id'=>'创建人','level_audit_status'=>'审核状态','demand_number'=>'需求单号','agree_user'=>'同意人','product_category'=>'产品类别','supplier_code'=>'供应商code','buyer'=>'采购员','supplier_name'=>'供应名称'];
            foreach ($platform_summary_info as $sk=>$sv) {
                $platform_summary_info[$sk]['purchase_warehouse'] = BaseServices::getWarehouseCode($sv['purchase_warehouse']);
                $platform_summary_info[$sk]['transit_warehouse'] = !empty($sv['transit_warehouse']) ? BaseServices::getWarehouseCode($sv['transit_warehouse']) : null;
                $platform_summary_info[$sk]['is_transit'] = ($sv['is_transit']==1) ? '否' : '是';
                $platform_summary_info[$sk]['level_audit_status'] = Yii::$app->params['demand'][$sv['level_audit_status']];
                $platform_summary_info[$sk]['supplier_name'] = BaseServices::getSupplierName($sv['supplier_code']);
            }
            $platform_summary[] = $platform_summary_info;
        }
        return $platform_summary;
    }
    /**
     * ======== 供应商整合表 ========
     */
    public function selectSupplierUpdateApply($sku=null,$id=null)
    {
        $supplier_update_apply=[];

        if (!empty($sku)) {
            $where['sku']  = $sku;
        }
        if (!empty($id)) {
            $where['id']  = $id;
        }

        $supplier_update_apply_info = SupplierUpdateApply::find()
            ->where($where)
            ->asArray()
            ->all();

        if (!empty($supplier_update_apply_info)) {
            $supplier_update_apply[] = ['id'=>'ID','sku'=>'SKU','old_quotes_id'=>'旧供应商报价','new_quotes_id'=>'新供应商报价','new_supplier_code'=>'新供应商名称','new_product_num'=>'新供应商绑定sku数量','create_user_id'=>'申请人id','create_user_name'=>'申请人名称','status'=>'审核状态','create_time'=>'申请时间','update_time'=>'审核时间','update_user_name'=>'审核人','type'=>'申请类型','is_sample'=>'是否拿样','integrat_status'=>'整合状态','integrat_user_name'=>'整合确认人','new_purchase_link'=>'新采购链接'];
            foreach ($supplier_update_apply_info as $sk=>$sv) {
                $supplier_update_apply_info[$sk]['status'] = SupplierServices::getApplyStatus($sv['status']);
                $supplier_update_apply_info[$sk]['integrat_status'] = !empty($sv['integrat_status']) ? SupplierServices::getIntegratStatus($sv['integrat_status']) : null;
                $supplier_update_apply_info[$sk]['is_sample'] = !empty($sv['is_sample']) ? SupplierServices::getSampleStatus($sv['is_sample']) : '';
            }
            $supplier_update_apply[] = $supplier_update_apply_info;
        }
        return $supplier_update_apply;
    }
    /**
     * ======== 供应商整合表 ========
     */
    public function selectSampleInspect($sku=null,$id=null)
    {
        $sample_inspect=[];

        if (!empty($sku)) {
            $where['sku']  = $sku;
        }
        if (!empty($id)) {
            $where['id']  = $id;
        }

        $sample_inspect_info = SampleInspect::find()
            ->where($where)
            ->asArray()
            ->all();

        if (!empty($sample_inspect_info)) {
            $sample_inspect[] = ['id'=>'ID','sku'=>'SKU','supply_send_name'=>'供应链发出人','supply_chain_send'=>'供应链是否发出','quality_take_name'=>'品控收货人','quality_control_take_time'=>'品控收货时间','quality_control_take'=>'品控是否收货','quality_send_name'=>'品控发出人','quality_control_send'=>'品控是否发回','supply_take_name'=>'供应链收货人','supply_chain_take'=>'供应链是否收货','confirm_time'=>'质检结果确认时间','qc_result'=>'质检结果','confirm_user_name'=>'质检结果确认人','pur_number'=>'PO号'];
            foreach ($sample_inspect_info as $sk=>$sv) {
                $sample_inspect_info[$sk]['qc_result'] = !empty($sv['qc_result']) ? SupplierServices::getSampleResultStatus($sv['qc_result']) : '';
//                $sample_inspect_info[$sk]['integrat_status'] = !empty($sv['integrat_status']) ? SupplierServices::getIntegratStatus($sv['integrat_status']) : null;
//                $sample_inspect_info[$sk]['is_sample'] = !empty($sv['is_sample']) ? SupplierServices::getSampleStatus($sv['is_sample']) : '';
            }
            $sample_inspect[] = $sample_inspect_info;
        }
        return $sample_inspect;
    }
    /**
     * ======== 请款单表 ========
     */
    public function selectPurchaseOrderPay($pur_number){
        $purchase_order_pay=[];
        $purchase_order_pay_info = PurchaseOrderPay::find()
//            ->select(['id','pur_number','is_drawback','is_push','buyer'])
            ->where(['pur_number'=>$pur_number])
            ->asArray()
            ->all();

        if (!empty($purchase_order_pay_info)) {
            $purchase_order_pay[] = ['pur_number'=>'PO号', 'pay_status'=>'付款状态','requisition_number'=>'申请单号','supplier_code'=>'供应商编码','settlement_method'=>'结算方式','pay_name'=>'名称','pay_price'=>'金额','create_notice'=>'创建备注','applicant'=>'申请人','auditor'=>'审核人','approver'=>'审批人','application_time'=>'申请时间','review_time'=>'审核时间','processing_time'=>'审批时间','pay_type'=>'支付方式（从供应商拉取）','currency'=>'币种','review_notice'=>'审核备注','cost_types'=>'费用类型','payer'=>'付款人','payer_time'=>'付款时间','payment_cycle'=>'支付周期','payment_notice'=>'付款备注'];
            foreach ($purchase_order_pay_info as $k => $v) {
                $purchase_order_pay_info[$k]['pay_status'] = PurchaseOrderServices::getPayStatus($v['pay_status']);
                $purchase_order_pay_info[$k]['pay_type'] = !empty($v['pay_type']) ? SupplierServices::getDefaultPaymentMethod($v['pay_type']) : '';
                $purchase_order_pay_info[$k]['settlement_method'] = !empty($v['settlement_method']) ? SupplierServices::getSettlementMethod($v['settlement_method']) : '';
            }
            $purchase_order_pay[] = $purchase_order_pay_info;
        }
        return $purchase_order_pay;
    }
    /**
     * ======== 产品税率表 ========
     */
    public function selectProductTaxRate($skus=null) {
        $product_tax_rate=[];

        $product_tax_rate_info = ProductTaxRate::find()
            ->where(['in','sku', $skus])
            ->asArray()
            ->all();

        if (!empty($product_tax_rate_info)) {
            $product_tax_rate[] = ['id'=>'ID', 'sku'=>'SKU','tax_rate'=>'出口退税税率','ticketed_point'=>'采购开票点（税点）','update_user'=>'修改税点人（最新的）','create_time'=>'开发时间','update_time'=>'修改时间'];
            foreach ($product_tax_rate_info as $k => $v) {

            }
            $product_tax_rate[] = $product_tax_rate_info;
        }
        return $product_tax_rate;
    }
    /**
     * ======== 产品税率表 -- 通用 ========
     */
    public function selectPurchaseOrderTaxes($pur_number=null,$sku=null) {
        $purchase_order_taxes=[];
        $purchase_order_taxes_info = PurchaseOrderTaxes::find();
        if (!empty($pur_number)) {
            $where['pur_number']  = $pur_number;
        }

        $purchase_order_taxes_info->where($where);
        if (!empty($sku)) {
            $purchase_order_taxes_info->andWhere(['in', 'sku',$sku]);
        }

        $purchase_order_taxes_info = $purchase_order_taxes_info->asArray()->all();

        if (!empty($purchase_order_taxes_info)) {
            $purchase_order_taxes[] = ['id'=>'ID','pur_number'=>'采购单号', 'sku'=>'SKU','is_taxes'=>'是否含税','taxes'=>'税率','create_id'=>'创建人','create_time'=>'创建时间'];
            foreach ($purchase_order_taxes_info as $k => $v) {

            }
            $purchase_order_taxes[] = $purchase_order_taxes_info;
        }
        return $purchase_order_taxes;
    }

}