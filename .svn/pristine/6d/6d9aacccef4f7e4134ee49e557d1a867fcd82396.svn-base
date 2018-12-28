<?php
namespace app\controllers;

use app\models\Product;
use app\models\Supplier;
use app\models\ProductSupplierChange;
use app\models\ProductSupplierChangeSearch;
use Yii;
use yii\filters\VerbFilter;
use app\models\ChangeLog;
use app\models\SupplierUpdateApply;

/**
 * sku屏蔽申请列表 控制器
 * Class ProductRepackageController
 * @package app\controllers
 */
class ProductSupplierChangeController extends BaseController
{

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

    /**
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel    = new ProductSupplierChangeSearch();
        $params         = Yii::$app->request->queryParams;
        if(empty($params)){// 设置默认时间
            $params['ProductSupplierChangeSearch']['apply_time'] = date('Y-m-d',strtotime('- 3 months')) .' - '.date('Y-m-d');
        }
        $dataProvider   = $searchModel->search($params);
        $data = [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];
        return $this->render('index', $data);
    }


    /**
     * 创建SKU 二次包装记录
     * @return string|\yii\web\Response
     */
    public function actionCreateData()
    {
        $model = new ProductSupplierChangeSearch();

        if (Yii::$app->request->isPost)
        {
            $data                   = Yii::$app->request->post()['ProductSupplierChangeSearch'];
            $skuStr                 = $data['sku'];
            $apply_remark           = $data['apply_remark'];
            $other_apply_reason     = $data['other_apply_reason'];

            if($apply_remark == 100){
                $apply_remark = $other_apply_reason;
            }else{
                $apply_remark = ProductSupplierChangeSearch::getApplyReasonList($apply_remark);
            }

            if(empty($skuStr)){
                Yii::$app->getSession()->setFlash('warning','无数据');
                return $this->redirect(['index']);
            }

            $exists_list = $success_list = $no_sku_list = [];
            $search = array('，'," ","　"," ","\n","\r","\t");
            $replace = array(',',",",",",",",",",",",",");

            $skuStr = str_replace($search,$replace ,$skuStr);

            if(strpos($skuStr,',') !== false){
                $skuArr = explode(',',$skuStr);
                $skuArr = array_unique($skuArr);
                $skuArr = array_diff($skuArr,['']);
            }else{
                $skuArr = array($skuStr);
            }

            foreach($skuArr as $sku){
                if(empty($sku)) continue;// 空的不保存
                $sku = trim($sku);

                $addInfo = [
                    'sku'                   => $sku,
                    'apply_remark'          => $apply_remark
                ];

                $result = ProductSupplierChangeSearch::addChangeInfo($addInfo);
                if($result['status'] == 'success'){
                    $success_list[] = $sku;
                }elseif($result['status'] == 'exists'){
                    $exists_list[] = $sku;
                }elseif($result['status'] == 'none'){
                    $no_sku_list[] = $sku;
                }
            }
            $no_sku_message = empty($no_sku_list)?"":'，不存在的SKU['.implode(',',$no_sku_list).']';

            if(count($success_list) > 0 AND count($exists_list) == 0){
                Yii::$app->getSession()->setFlash('success','恭喜你,添加成功'.$no_sku_message);
            }elseif(count($success_list) > 0 AND count($exists_list) > 0){
                Yii::$app->getSession()->setFlash('success','添加成功，部分SKU已发起屏蔽申请['.implode(',',$exists_list).']'.$no_sku_message);
            }elseif(count($exists_list) > 0){
                Yii::$app->getSession()->setFlash('warning','SKU已发起屏蔽申请['.implode(',',$exists_list).']'.$no_sku_message);
            }else{
                Yii::$app->getSession()->setFlash('error','抱歉，导入失败'.$no_sku_message);
            }

            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('create',['model' =>$model]);
        }
    }

    /**
     * 采购经理审核
     * @return string|\yii\web\Response
     */
    public function actionAudit(){
        $model = new ProductSupplierChangeSearch();

        if(Yii::$app->request->isPost){
            $success        = 0;// 操作成功个数
            $data           = Yii::$app->request->post()['ProductSupplierChangeSearch'];
            $ids            = $data['sku'];
            $status         = $data['status'];
            $apply_remark   = $data['apply_remark'];
            if($status == 10 AND empty($apply_remark)){
                Yii::$app->getSession()->setFlash('error','驳回必须填写原因');
                return $this->redirect(['index']);
            }else{
                $ids_arr = explode(',',$ids);
                foreach($ids_arr as $value_id){
                    $model = ProductSupplierChange::findOne(['id' => $value_id]);
                    if($model->status == 1){
                        // 日志信息
                        $old_status = ProductSupplierChange::getStatusList($model->status);// 旧的状态
                        $new_status = ProductSupplierChange::getStatusList($status);// 新状态
                        $content    = '修改状态：从['.$old_status.']改为['.$new_status.']';

                        $model->status   = $status;
                        if($status == 20){
                            $model->deadline = date('Y-m-d H:i:s',time() + ProductSupplierChange::DEAD_LINE);
                        }elseif($status == 70){
                            $model->status_flag = 'PM';// 采购经理驳回
                        }

                        $res = $model->save();
                        if($res){
                            // 保存日志
                            $success ++;
                            $data = [
                                'oper_id'   => $value_id,
                                'oper_type' => 'ProductSupplierChange',
                                'content'   => $content.($status==70?('，驳回原因：'.$apply_remark):'')
                            ];
                            $result = ChangeLog::addLog($data);
                        }
                    }
                }
            }
            if($success){
                Yii::$app->getSession()->setFlash('success','恭喜，审核成功['.$success.']个');
            }else{
                Yii::$app->getSession()->setFlash('warning','抱歉，没有需要审核的数据');
            }
            return $this->redirect(['index']);
        } else {
            $data         = Yii::$app->request->get();
            $ids          = $data['ids'];
            $sku_str      = implode(',',$ids);

            return $this->renderAjax('audit',['model' => $model,'sku' => $sku_str]);
        }
    }

    /**
     * 展示 操作日志
     * @return string
     */
    public function actionShowLog(){
        $data           = Yii::$app->request->get();
        $key_id         = $data['key_id'];
        $list           = ProductSupplierChangeSearch::getLogList($key_id);

        return $this->renderAjax('show-log',['list' => $list]);
    }


    /**
     * 采购经理审核
     */
    public function actionAffirmSupplier(){
        $model = new ProductSupplierChangeSearch();

        if(Yii::$app->request->isPost){
            $success        = 0;// 操作成功个数
            $data           = Yii::$app->request->post()['ProductSupplierChangeSearch'];
            $value_id       = $data['sku'];
            $status         = $data['status'];
            $apply_remark   = $data['apply_remark'];

            if($status == 70 AND empty($apply_remark)){
                Yii::$app->getSession()->setFlash('error','驳回必须填写原因');
                return $this->redirect(['index']);
            }else{
                $model = ProductSupplierChange::findOne(['id' => $value_id]);

                if(empty($model->new_supplier_code)){// 补充完整 供应商编码
                    $model->new_supplier_code = Supplier::find()->select('supplier_code')->where(['supplier_name' => $model->new_supplier_name])->scalar();
                    $model->save(false);
                }

                // 验证供应商是否已经审核通过
                $supplierStatus = Supplier::find()->select('status')->where(['supplier_code' => $model->new_supplier_code])->scalar();
                if(empty($supplierStatus) OR $supplierStatus != 1){
                    Yii::$app->getSession()->setFlash('error','替换供应商必须是正常状态下的供应商');
                    return $this->redirect(['index']);
                }

                $old_status = ProductSupplierChange::getStatusList($model->status);// 旧的状态
                $new_status = ProductSupplierChange::getStatusList($status);// 新状态
                $content    = '修改状态：从['.$old_status.']改为['.$new_status.']';

                if($model->status == 50){
                    $model->status   = $status;
                    if($status == 70){
                        $model->status_flag = 'P';// 采购经理驳回
                    }
                    $model->affirm_user = Yii::$app->user->identity->username;
                    $model->affirm_time = date('Y-m-d H:i:s');

                    $res = $model->save();
                    if($res){
                        // 保存日志
                        $success ++;
                        $data = [
                            'oper_id'   => $value_id,
                            'oper_type' => 'ProductSupplierChange',
                            'content'   => $content.($status==70?('，驳回原因：'.$apply_remark):'')
                        ];
                        ChangeLog::addLog($data);

                        if($status == 60){// 采购确认通过
                            ChangeLog::addLog([ 'oper_id'   => $value_id,'oper_type' => 'ProductSupplierChange','content' => 'SUPPLIER_CHECK_RESULT','is_show' => 2,'update_data' => 'AGREE']);

                            // 修改默认供应商
                            $apply                    = new SupplierUpdateApply();
                            $apply->type              = 1;
                            $apply->sku               = $model->sku;
                            $apply->new_supplier_code = $model->new_supplier_code;
                            $apply->new_quotes_id     = 0;

                            $res = SupplierUpdateApply::saveDefaultSupplier($apply);

                        }else{
                            ChangeLog::addLog([ 'oper_id'   => $value_id,'oper_type' => 'ProductSupplierChange','content' => 'SUPPLIER_CHECK_RESULT','is_show' => 2,'update_data' => 'DISAGREE:'.$apply_remark]);
                        }
                    }
                }
            }
            if($success){
                Yii::$app->getSession()->setFlash('success','恭喜，操作成功['.$success.']个');
            }else{
                Yii::$app->getSession()->setFlash('warning','抱歉，没有需要审核的数据');
            }
            return $this->redirect(['index']);
        } else {
            $data         = Yii::$app->request->get();
            $id           = $data['id'];

            return $this->renderAjax('affirm-supplier',['model' => $model,'sku' => $id]);
        }
    }

    /**
     * 删除 申请记录
     */
    public function actionBatchDelete(){
        $data         = Yii::$app->request->get();
        $ids          = $data['ids'];

        foreach($ids as $id){
            $model  = ProductSupplierChange::findOne($id);
            if($model){
                $content = '删除记录:'.$model->sku;
                $update_data = json_encode($model->attributes);

                $data = [
                    'oper_id'       => $model->id,
                    'oper_type'     => 'ProductSupplierChange',
                    'content'       => $content,
                    'update_data'   => $update_data
                ];
                $res    = $model->delete();
                if($res){
                    $result = ChangeLog::addLog($data);
                }
            }
        }

        Yii::$app->getSession()->setFlash('success','恭喜你,删除成功');
        return $this->redirect(Yii::$app->request->referrer);
    }


    /**
     * 导出CSV
     * @throws \yii\web\HttpException
     */
    public function actionExportCsv()
    {
        set_time_limit(0);
        $table = [
            '申请时间',
            '审核时间',
            'SKU',
            '品类',
            '采购申请人',
            '产品名称',
            '开发审核人 ',
            '申请备注',
            '最后状态'
        ];
        $searchModel    = new ProductSupplierChangeSearch();
        $params         = Yii::$app->request->queryParams;
        if(isset($params['ids']) AND $params['ids']){
            $ids        = explode(',',$params['ids']);
            $data_list  = ProductSupplierChangeSearch::find()->where(['in','id',$ids])->asArray()->all();
        }else{
            $params         = \Yii::$app->session->get('ProductSupplierChangeSearch');
            $dataProvider   = $searchModel->search($params);
            $query          = $dataProvider->query;
            $data_list      = $query->asArray()->all();
        }

        $status_list        = ProductSupplierChangeSearch::getStatusList();
        $status_flag_list   = ProductSupplierChangeSearch::getStatusFlagList();
        $table_data_list = [];
        foreach($data_list as $k => $v){
            $productInfo = Product::findOne(['sku' => $v['sku']]);

            if(isset($productInfo->product_linelist_id) and $productInfo->product_linelist_id){// 产品线
                $product_linelist_id = \app\services\BaseServices::getProductLine($productInfo->product_linelist_id);
            }else{
                $product_linelist_id = '';
            }
            // 状态
            $status   = isset($status_list[$v['status']])?$status_list[$v['status']]:'';
            if($v['status'] == 70){
                $status   .= isset($status_flag_list[$v['status_flag']])?'('.$status_flag_list[$v['status_flag']].')':'';
            }

            $table_data_list[$k][] = $v['apply_time'];
            $table_data_list[$k][] = !empty($v['erp_oper_time'])?$v['erp_oper_time']:'';
            $table_data_list[$k][] = $v['sku'];
            $table_data_list[$k][] = $product_linelist_id;
            $table_data_list[$k][] = $v['apply_user'];
            $table_data_list[$k][] = isset($productInfo->desc)?$productInfo->desc->title:'';
            $table_data_list[$k][] = !empty($v['erp_oper_user'])?'操作人：'.$v['erp_oper_user']:'';
            $table_data_list[$k][] = $v['apply_remark'];
            $table_data_list[$k][] = $status;

        }

        \m35\thecsv\theCsv::export([
            'name' => "sku屏蔽申请列表.csv",
            'header' => $table,
            'data'   => $table_data_list,
        ]);

    }


    /**
     * 从操作日志里面取出 审核时间
     * @param $change_id
     * @return mixed|string
     */
    public static function getCheckTime($change_id){
        $check_time = '';
        $list  = ProductSupplierChangeSearch::getLogList($change_id);
        if($list){
            foreach($list as $value){
                $content = str_replace(['[',']'],['',''],$value['content']);
                if(preg_match("/(修改状态：从待采购经理审核改为)(待开发审核|已结束)/",$content)){
                    $check_time = $value['operate_time'];
                    break;
                }
            }
        }

        return $check_time;
    }
}
