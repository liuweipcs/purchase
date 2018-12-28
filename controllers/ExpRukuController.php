<?php
namespace app\controllers;
use app\models\PurchaseOrder;
use app\models\TablesChangeLog;
use Yii;
use app\config\Vhelper;
use yii\web\Controller;
use app\models\PurchaseWarehouseAbnormal;
use app\models\PurchaseWarehouseAbnormalSearch;
use app\models\Address;
use app\models\InformMessage;
use yii\data\Pagination;
use linslin\yii2\curl;
use yii\helpers\Json;
class ExpRukuController extends Controller
{

    public function actionIndex()
    {
        $searchModel = new PurchaseWarehouseAbnormalSearch();
        $args = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($args, 1);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionHandler()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $data = $request->post();
            if($data['handler_type'] == 1) {
                $model = \yii\base\DynamicModel::validateData($data, [
                    ['return_province', 'required', 'message' => '省份不能为空'],
                    ['return_city', 'required', 'message' => '城市不能为空'],
                    ['return_address', 'required', 'message' => '详细地址不能为空'],
                    ['return_linkman', 'required', 'message' => '联系人不能为空'],
                    ['return_phone', 'required', 'message' => '联系人电话不能为空'],
                ]);
                if($model->hasErrors()) {
                    $errors = $model->errors;
                    echo '错误提示：';
                    foreach($errors as $v) {
                        echo "<p>{$v[0]}</p>";
                    }
                    exit;
                }
            }
            $data['is_handler'] = 1;
            if($data['return_province'] > 0) {
                $obj = new Address();
                $data['return_province'] = $obj->getProvinceName($data['return_province']);
            }
            $tran = Yii::$app->db->beginTransaction();
            try {
                $res = PurchaseWarehouseAbnormal::updateRow($data);
                $tran->commit();
            } catch(\Exception $e) {
                $tran->rollBack();
            }
            if($res) {
                Yii::$app->getSession()->setFlash('success',"恭喜你，操作成功！",true);
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                Yii::$app->getSession()->setFlash('error',"对不起，操作失败！",true);
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $address = new Address();
            $get = $request->get();
            if(isset($get['pid']) && $get['pid'] > 0) {
                $cityList = $address->getCityByPid($get['pid']);
                return json_encode($cityList);
            }
            $pro = $address->getProvinceList();
            $model = PurchaseWarehouseAbnormal::findOne(['defective_id' => $get['defective_id']]);
            if(!$model) {
                return '没有查到数据';
            }
            return $this->renderAjax('handler', [
                'model' => $model,
                'pro' => $pro
            ]);
        }
    }

    public function actionView()
    {
        $request = Yii::$app->request;
        $defective_id = $request->get('defective_id');
        $model = PurchaseWarehouseAbnormal::findOne(['defective_id' => $defective_id]);
        if(!$model) {
            return '当前异常还未做处理';
        }
        return $this->renderAjax('view', ['model' => $model]);
    }

    // 计划任务-推送异常处理结果至仓库系统
    public function actionPushHandlerInfo()
    {
        $fields = [
            'defective_id',
            'handler_type',
            'abnormal_type',
            'handler_person',
            'purchase_order_no',
            'handler_time',
            'handler_describe',
            'return_province',
            'return_city',
            'return_address',
            'return_linkman',
            'return_phone',
        ];
        $rows = PurchaseWarehouseAbnormal::find()
            ->select($fields)
            ->where(['is_handler' => 1, 'is_push_to_warehouse' => 0])
            ->limit(10)
            ->asArray()
            ->all();
        if(empty($rows)) {
            return '已经没有数据了';
        }

        $data = [];
        foreach($rows as $row) {
            $data[$row['defective_id']] = $row;
        }
        $curl = new curl\Curl();

        $domain = Yii::$app->params['wms_domain'];

        $url = "{$domain}/Api/Purchase/QualityAbnormal/getQualityAbnormalResult";
        $token = Json::encode(Vhelper::stockAuth());
        $s = $curl->setPostParams([
            'quality_abnormal_result' => Json::encode($data),
            'token' => $token
        ])->post($url);

        echo '接口返回值：'."<br/>";
        var_dump($s);

        try {
            $res = Json::decode($s);
            if(isset($res['error']) && $res['error'] == -1) {

                echo "<pre>\n---------------------------------接口error返回-1 开始---------------------------------\n";
                print_r($res);
                echo "\n---------------------------------接口error返回-1 结束---------------------------------\n";

            }
            if(is_array($res) && !empty($res)) {
                
                $defective_ids = [];
                $failure_list = [];

                foreach($res as $k => $v) {
                    if($v['status'] == 'success') {
                        $defective_ids[] = strval($k);
                    } elseif($v['status'] == 'fail') {
                        $model = PurchaseWarehouseAbnormal::find()->where(['defective_id' => $v['defective_id']])->one();
                        if(!empty($model)) {
                            $model->is_handler = 2; // 处理失败
                            $model->warehouse_handler_result = $v['msgBox'];
                            $model->save(false);
                        }
                        $failure_list[] = [
                            'defective_id' => $v['defective_id'],
                            'msgBox' => $v['msgBox'],
                        ];
                    }
                }

                if(!empty($failure_list)) {
                    echo "<pre>\n---------------------------------仓库返回错误数据 开始---------------------------------\n";
                    print_r($failure_list);
                    echo "\n---------------------------------仓库返回错误数据 结束---------------------------------\n";
                }

                if($defective_ids) {

                    $i = PurchaseWarehouseAbnormal::updateAll(['is_push_to_warehouse' => 1, 'warehouse_handler_result' => '处理结果已推送至仓库系统'], ['in', 'defective_id', $defective_ids]);

                    echo "<pre>\n---------------------------------标记成功 开始---------------------------------\n";
                    print_r($i);
                    echo "\n---------------------------------标记失败 结束---------------------------------\n";

                }
            }

            echo "<pre>\n---------------------------------接口返回值转成数组格式 开始---------------------------------\n";
            print_r($res);
            echo "\n---------------------------------接口返回值转成数组格式 结束---------------------------------\n";

        } catch(\Exception $e) {

            echo "<pre>\n---------------------------------程序错误 开始---------------------------------\n";
            print_r($e->getMessage());
            echo "\n---------------------------------程序错误 结束---------------------------------\n";

        }
    }


    // 入库数量异常处理
    public function actionRukuNumHandler()
    {
        $args = Yii::$app->request->queryParams;
        $model = new InformMessage();

        $model->attributes = $args;

        $query = InformMessage::find();

        $query->andFilterWhere([
            'sku'         => $model->sku,
            'pur_number'  => $model->pur_number,
            'inform_user' => Vhelper::chunkBuyerByNumeric($model->inform_user),
            'status'      => $model->status,
        ]);

        if($model->create_time) {
            $times = explode(' - ', $model->create_time);
            $query->andFilterWhere(['>', 'create_time', $times[0]]);
            $query->andFilterWhere(['<', 'create_time', $times[1]]);
        }

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 20]);

        $list = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        return $this->render('ruku-num-handler', ['list' => $list, 'pager' => $pagination, 'model' => $model]);
    }

    // 跟进
    public function actionRukuNumGj()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $data = $request->post();
            $model = InformMessage::findOne($data['id']);
            $model->gj_note = $data['gj_note'];
            $model->gj_person = Yii::$app->user->identity->username;
            $model->gj_time = date('Y-m-d H:i:s', time());

            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
            $change_data = [
                'table_name' => 'pur_inform_message', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            $tran = Yii::$app->db->beginTransaction();
            try {
                TablesChangeLog::addLog($change_data);
                $res = $model->save(false);
                $tran->commit();
            } catch (Exception $e) {
                $tran->rollBack();
            }
            if($res) {
                Yii::$app->getSession()->setFlash('success',"恭喜你，操作成功！",true);
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                Yii::$app->getSession()->setFlash('error',"对不起，操作失败！",true);
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $id = $request->get('id');
            $model = InformMessage::findOne($id);
            if(empty($model)) {
                return '数据不存在';
            }
            return $this->renderAjax('ruku-num-gj', ['model' => $model]);
        }
    }

    /*
     * 导出异常推送失败的异常信息
     * http://test.yibainetwork.com/exp-ruku/lead-fail-push-exp-data
     * http://caigou.yibainetwork.com/exp-ruku/lead-fail-push-exp-data
     */
    public function actionLeadFailPushExpData()
    {
        echo "<h1>".Yii::$app->db->dsn."</h1>";
        
		$s1 = "update pur_purchase_compact set tpl_id = 3 where compact_number = 'ABD-HT000733'";

        $e = Yii::$app->db->createCommand($s1)->execute();

        Vhelper::dump($e);

        exit;
    }

     // 验证采购单是否存在
    public function actionCheckPurnumber(){
        $data = ['status'=>0,'msg'=>''];
        $pur_numbmer = Yii::$app->request->post('pur_number');
        $is_exists = PurchaseOrder::find()->where(['pur_number'=>$pur_numbmer])->exists();
        if(!$is_exists){
            $data['status'] = 1;
            $data['msg'] = '采购单号不存在，请检查';
        }
        die(json_encode($data));
    }

}