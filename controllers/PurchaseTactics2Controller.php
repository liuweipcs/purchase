<?php
namespace app\controllers;

use Yii;
use yii\base\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\PurchaseTactics;
use app\models\PurchaseTacticsDailySales;
use app\models\PurchaseTacticsSuggest;
use app\models\PurchaseTacticsWarehouse;
use app\models\PurchaseTacticsSearch;
use app\models\Warehouse;

/**
 * @desc 补货策略
 * @author Jolon
 * @date 2018-10-09 11:43
 * PurchaseTacticsController implements the CRUD actions for Product model.
 */
class PurchaseTactics2Controller extends BaseController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['purchasing-advice', 'getsuggestnumbers'],
                'rules' => [
                    [
                        'actions' => ['purchasing-advice', 'getsuggestnumbers'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseTacticsSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @desc 查看日志
     * @author Jimmy
     * @date 2017-04-14 16:23:11
     */
    public function actionViewLog()
    {
        $map=[];
        $map['sku']=Yii::$app->request->get('sku');
        $map['warehouse_code']=Yii::$app->request->get('warehouse_code');
        $searchModel = new PurchaseTacticsSearch();
        $dataProvider = $searchModel->search(['SkuStatisticsLogSearch'=>$map]);
        return $this->renderAjax('view-log', ['searchModel' => $searchModel,'dataProvider'=>$dataProvider]);
    }


    /**
     * 创建 备货策略
     * @return string|\yii\web\Response
     */
    public function actionCreateData()
    {
        // 获取仓库列表
        $warehouseList = PurchaseTacticsSearch::getWarehouseList();

        if (Yii::$app->request->isPost)
        {
            $data           = Yii::$app->request->post()['PurchaseTacticsSearch'];
            $id             = intval($data['id']);
            $transaction    = Yii::$app->db->beginTransaction();

            try{
                // 新增或更新 备货策略
                if($id){
                    $model = PurchaseTactics::find()->where("id='$id'")->one();
                }else{
                    $model = new PurchaseTactics;
                }
                $model->tactics_name                    = $data['tactics_name'];
                $model->single_price                    = $data['single_price'];
                $model->inventory_holdings              = $data['inventory_holdings'];
                $model->reserved_max                    = $data['reserved_max'];
                $model->sales_sd_value_range            = $data['sales_sd_value_range'];
                $model->lead_time_value_range           = $data['lead_time_value_range'];
                $model->weight_avg_period_value_range   = $data['weight_avg_period_value_range'];

                if($id){
                    $model->updateor    = Yii::$app->user->id;
                    $model->update_at   = date('Y-m-d H:i:s');
                }else{
                    $model->creator     = Yii::$app->user->id;
                    $model->created_at  = date('Y-m-d H:i:s');
                }

                $res = $model->save();
                if($res){

                    // 销量平均值天数权值保存
                    $daily_sales_value  = $data['daily_sales_value'];
                    $daily_sales_day    = $data['daily_sales_day'];

                    $total_value = 0;

                    PurchaseTacticsDailySales::deleteAll("tactics_id=:t_id",[':t_id' => $model->id ]);// 删除旧的适用仓库列表
                    foreach($daily_sales_value as $key => $value){
                        if(empty($value) AND empty($daily_sales_day[$key])) continue;

                        $daily_model                = new PurchaseTacticsDailySales();
                        $daily_model->tactics_id    = $model->id;
                        $daily_model->day_value     = $daily_sales_day[$key];
                        $daily_model->day_sales     = $value;

                        $res = $daily_model->save();
                        $total_value += $value;

                        if($res){

                        }else{
                            $error = current(current($daily_model->getErrors()));
                            throw new Exception("补货策略-销量平均值天数权值保存失败[$error]");
                        }
                    }

                    if ($total_value != 1) {// 验证比值总和比值，加起来必须为1
                        echo json_encode(['status' => 'error', 'message' => '比值加起来必须为 1']);
                        Yii::$app->end();
                    }

                    $percent_start_list             = $data['percent_start'];
                    $percent_end_list               = $data['percent_end'];
                    $stockup_days_list              = $data['stockup_days'];
                    $service_coefficient_list       = $data['service_coefficient'];
                    $incr_days                      = $data['incr_days'];
                    $maximum_list                   = $data['maximum'];
                    $minimum_list                   = $data['minimum'];

                    // 保存 采购建议逻辑 信息
                    PurchaseTacticsSuggest::deleteAll("tactics_id=:t_id",[':t_id' => $model->id ]);// 删除旧的采购建议逻辑
                    foreach($percent_start_list as $type => $percent_start){

                        foreach($percent_start as  $key => $value){
                            $suggest_model              = new PurchaseTacticsSuggest();
                            $suggest_model->tactics_id  = $model->id;

                            // 采购建议逻辑类型
                            if($type == 'type1'){
                                $suggest_model->type = 1;
                            }elseif($type == 'type2'){
                                $suggest_model->type = 2;
                            }else{
                                $suggest_model->type = 3;
                            }
                            $suggest_model->percent_start       = $value;
                            $suggest_model->percent_end         = $percent_end_list[$type][$key];
                            $suggest_model->stockup_days        = $stockup_days_list[$type][$key];
                            $suggest_model->service_coefficient = $service_coefficient_list[$type][$key];
                            $suggest_model->incr_days           = $incr_days[$type][$key];
                            $suggest_model->maximum             = $maximum_list[$type][$key];
                            $suggest_model->minimum             = $minimum_list[$type][$key];

                            $res = $suggest_model->save();
                            if($res){

                            }else{
                                $error = current(current($suggest_model->getErrors()));
                                throw new Exception("补货策略-采购建议逻辑保存失败[$error]");
                            }
                        }
                    }


                    // 保存 适用仓库 信息
                    $warehouse_list = $data['warehouse_list'];

                    $warehouse_list_tmp = [];
                    foreach($warehouse_list as $key => $value){
                        if(empty($value)) continue;
                        $warehouse_list_tmp[] = $value[0];
                    }
                    $warehouse_list = $warehouse_list_tmp;
                    PurchaseTacticsWarehouse::deleteAll("tactics_id=:t_id",[':t_id' => $model->id ]);// 删除旧的适用仓库列表

                    // 保存 适用仓库 信息
                    foreach ($warehouse_list as $ware_code){
                        $ware_model                 = new PurchaseTacticsWarehouse();
                        $ware_model->tactics_id     = $model->id;
                        $ware_model->warehouse_code = $ware_code;
                        $ware_model->warehouse_name = $warehouseList[$ware_code];

                        $res = $ware_model->save();
                        if($res){

                        }else{
                            $error = current(current($ware_model->getErrors()));
                            throw new Exception("补货策略-适用仓库保存失败[$error]");
                        }
                    }

                }else{
                    $error = current(current($model->getErrors()));
                    throw new Exception("补货策略保存失败[$error]");
                }

                $transaction->commit();

                Yii::$app->getSession()->setFlash('success','恭喜你,保存成功');
                return $this->redirect(Yii::$app->request->referrer);
            }catch (Exception $e){
                $transaction->rollBack();
                //echo $e->getMessage();exit;
                Yii::$app->getSession()->setFlash('error',$e->getMessage(),false);
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $data  = Yii::$app->request->get();
            $id    = isset($data['id'])?$data['id']:0;

            if($id){
                $model = PurchaseTacticsSearch::find()->where("id='$id'")->one();
            }else{
                $model = new PurchaseTacticsSearch();
            }

            return $this->renderAjax('create',['model' =>$model,'warehouseList' => $warehouseList]);
        }
    }


    /**
     * 查看 备货策略
     * @return string|\yii\web\Response
     */
    public function actionView()
    {
        $data       = Yii::$app->request->get();
        $id         = $data['id'];
        $model      = PurchaseTacticsSearch::findOne($id);

        // 获取仓库列表
        $sql = Warehouse::find()->select('id,warehouse_name,warehouse_code')->where('use_status=1')->createCommand()->getRawSql();
        $warehouseList = Yii::$app->db->createCommand($sql)->queryAll();
        $warehouseList = array_column($warehouseList,'warehouse_name','warehouse_code');

        if($model->tactics_type == 1){
            return $this->renderAjax('view',['model' =>$model,'warehouseList' => $warehouseList]);
        }else{
            return $this->renderAjax('view-sku-tactics',['model' =>$model,'warehouseList' => $warehouseList]);
        }

    }


    /**
     * 修改 备货策略 的状态（启用或禁用）
     * @return \yii\web\Response
     */
    public function actionChangeStatus(){
        $data       = Yii::$app->request->get();
        $id         = $data['id'];
        $status     = $data['status'];

        $model      = PurchaseTactics::find()->where("id=:id",[':id' => $id])->one();
        $model->status = $status;
        $res = $model->save();

        if($res){
            Yii::$app->getSession()->setFlash('success','恭喜你,状态修改成功');
            return $this->redirect(Yii::$app->request->referrer);
        }else{
            Yii::$app->getSession()->setFlash('error','抱歉,状态修改失败');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }


    /**
     * 删除 备货策略
     * @return \yii\web\Response
     */
    public function actionBatchDelete(){
        $data       = Yii::$app->request->get();
        $ids        = isset($data['ids'])?$data['ids']:[];

        foreach($ids as $id){

            $model = PurchaseTactics::findOne($id);
            $res = $model->delete();

            if($res){
                PurchaseTacticsDailySales::deleteAll(['tactics_id' => $id]);
                PurchaseTacticsSuggest::deleteAll(['tactics_id' => $id]);
                PurchaseTacticsWarehouse::deleteAll(['tactics_id' => $id]);
            }

        }

        Yii::$app->getSession()->setFlash('success','恭喜你,删除成功');
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * 验证提交的数据
     */
    public function actionCheckTacticsInfo(){
        $data           = Yii::$app->request->get();
        $form_data      = $data['data'];

        $form_data_tmp = [];
        foreach($form_data as $key => $value){
            $key = str_replace('PurchaseTacticsSearch','',$value['name']);
            if(strpos($key,'[]') !== false){
                $key = str_replace('[]','',$key);
                $key = ltrim($key,'[');
                $key = rtrim($key,']');
                if(strpos($key,'][') !== false){
                    $key_arr = explode('][',$key);
                    $form_data_tmp[$key_arr[0]][$key_arr[1]][] = $value['value'];
                }else{
                    $form_data_tmp[$key][] = $value['value'];
                }
            }else{
                $key = ltrim($key,'[');
                $key = rtrim($key,']');
                $form_data_tmp[$key] = $value['value'];
            }
        }


        $error_message = [];
        $percent_total = 0;// 总的百分比
        foreach($form_data_tmp['percent_start'] as $key1 => $percent_start_list){
            foreach($percent_start_list as $key2 => $percent_start){
                if(!empty($percent_start) AND ($percent_start < 0 OR $percent_start > 100) ){
                    $error_message['占比范围'] = '只能是0~100之间的数字';
                }
                $percent_end = $form_data_tmp['percent_end'][$key1][$key2];
                if($percent_start AND $percent_end AND $percent_end <= $percent_start){
                    $error_message['占比范围'] = '起始占比必须小于结束占比';
                }
                $percent_total += $percent_end - $percent_start;
//                // 因为范围两边都是取等号(如 0~50表示：0<= 范围 <=50，51~95表示:51<= 范围 <=95)，所以要加一
//                $percent_total += $percent_end - $percent_start + 1;
//                if(is_string($percent_start) AND intval($percent_start) === 0){// 因为从0~50 50-0等于50，不能加一，要减下来
//                    $percent_total -= 1;
//                }
            }
        }

        foreach($form_data_tmp['percent_end'] as $key1 => $percent_end_list){
            foreach($percent_end_list as $key2 => $percent_end){
                if(!empty($percent_start) AND ($percent_end < 0 OR $percent_end > 100) ){
                    $error_message['占比范围'] = '只能是0~100之间的数字';
                }
            }
        }
        foreach($form_data_tmp['maximum'] as $key1 => $maximum_value_list){
            foreach($maximum_value_list as $key2 => $maximum_value){
                $minimum_value = $form_data_tmp['minimum'][$key1][$key2];
                if($maximum_value < $minimum_value){
                    $error_message['最大最小备货'] = '最大值不能小于最小值';
                }
            }
        }
        if($percent_total != 100){
            $error_message['销量占比总值'] = '销量总占比必须为100% 【总:'.$percent_total.'%】';
        }

        if($error_message){
            $message = '';
            foreach($error_message as $key => $value){
                $message .= "$key : $value <br/>";
            }
            echo json_encode(['status' => 'error', 'message' => $message]);
            Yii::$app->end();
        }

        $tactics_id     = intval($data['tactics_id']);
        $tactics_name   = $data['tactics_name'];
        $model = PurchaseTactics::find()->where("id!='$tactics_id' AND tactics_name='$tactics_name'" )->one();
        if($model){
            echo json_encode(['status' => 'error', 'message' => '策略名称已经存在!']);
        }else{
            echo json_encode(['status' => 'success', 'message' => '']);
        }
        Yii::$app->end();
    }


    /**
     * 创建 SKU备货策略
     * @return string|\yii\web\Response
     */
    public function actionCreateSkuTactics(){
        // 获取仓库列表
        $warehouseList = PurchaseTacticsSearch::getWarehouseList();

        if (Yii::$app->request->isPost)
        {
            $data           = Yii::$app->request->post()['PurchaseTacticsSearch'];
            $id             = intval($data['id']);
            $transaction    = Yii::$app->db->beginTransaction();

            try{
                // 新增或更新 备货策略
                if($id){
                    $model = PurchaseTacticsSearch::find()->where("id='$id'")->one();
                    
                    // 验证sku、仓库补货策略是否存在
                    $warehouse_list = $data['warehouse_list'];
                    $warehouse_list_tmp = [];
                    foreach($warehouse_list as $key => $value){
                        if(empty($value)) continue;
                        $warehouse_list_tmp[] = $value[0];
                    }
                    $warehouse_list = $warehouse_list_tmp;
                    if( PurchaseTactics::checkSkuWarehouseIsExists($model->sku,$warehouse_list,$id) ){// 已经存在的SKU不能重复添加
                        $exists_list['此仓库已创建该SKU策略'][] = $model->sku;
                        Yii::$app->getSession()->setFlash('warning','保存失败【选择的仓库已经存在此SKU策略】',false);
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                }else{
                    $model = new PurchaseTacticsSearch;
                }
                $model->tactics_name                    = '';
                $model->tactics_type                    = 2;
                $model->reserved_max                    = $data['reserved_max'];
                $model->sales_sd_value_range            = $data['sales_sd_value_range'];
                $model->lead_time_value_range           = $data['lead_time_value_range'];
                $model->weight_avg_period_value_range   = $data['weight_avg_period_value_range'];
                $model->start_time                      = $data['start_time'];
                $model->end_time                        = $data['end_time'];

                if($id){
                    $model->updateor    = Yii::$app->user->id;
                    $model->update_at   = date('Y-m-d H:i:s');
                }else{
                    $model->creator     = Yii::$app->user->id;
                    $model->created_at  = date('Y-m-d H:i:s');
                }

                $res = $model->save();
                if($res){

                    // 销量平均值天数权值保存
                    $daily_sales_value  = $data['daily_sales_value'];
                    $daily_sales_day    = $data['daily_sales_day'];

                    $total_value = 0;

                    PurchaseTacticsDailySales::deleteAll("tactics_id=:t_id",[':t_id' => $model->id ]);// 删除旧的适用仓库列表
                    foreach($daily_sales_value as $key => $value){
                        if(empty($value) AND empty($daily_sales_day[$key])) continue;

                        $daily_model                = new PurchaseTacticsDailySales();
                        $daily_model->tactics_id    = $model->id;
                        $daily_model->day_value     = $daily_sales_day[$key];
                        $daily_model->day_sales     = $value;

                        $res = $daily_model->save();
                        $total_value += $value;
                        if($res){

                        }else{
                            $error = current(current($daily_model->getErrors()));
                            throw new Exception("补货策略-销量平均值天数权值保存失败[$error]");
                        }
                    }

                    if ($total_value != 1) {// 验证比值总和比值，加起来必须为1
                        echo json_encode(['status' => 'error', 'message' => '比值加起来必须为 1']);
                        Yii::$app->end();
                    }

                    $type                           = $data['type'];
                    $stockup_days_list              = $data['stockup_days'];
                    $service_coefficient_list       = $data['service_coefficient'];
                    $maximum_list                   = $data['maximum'];
                    $minimum_list                   = $data['minimum'];
                    $incr_days                      = $data['incr_days'];

                    // 保存 采购建议逻辑 信息
                    PurchaseTacticsSuggest::deleteAll("tactics_id=:t_id",[':t_id' => $model->id ]);// 删除旧的采购建议逻辑

                    $suggest_model              = new PurchaseTacticsSuggest();
                    $type_str                   = 'type'.$type;

                    $suggest_model->tactics_id          = $model->id;
                    $suggest_model->type                = $type;// 采购建议逻辑类型
                    $suggest_model->stockup_days        = $stockup_days_list[$type_str][0];
                    $suggest_model->service_coefficient = $service_coefficient_list[$type_str][0];
                    $suggest_model->maximum             = $maximum_list[$type_str][0];
                    $suggest_model->minimum             = $minimum_list[$type_str][0];
                    $suggest_model->incr_days           = $incr_days[$type_str][0];

                    $res = $suggest_model->save();
                    if($res){

                    }else{
                        $error = current(current($suggest_model->getErrors()));
                        throw new Exception("补货策略-采购建议逻辑保存失败[$error]");
                    }


                    // 保存 适用仓库 信息
                    $warehouse_list = $data['warehouse_list'];

                    $warehouse_list_tmp = [];
                    foreach($warehouse_list as $key => $value){
                        if(empty($value)) continue;
                        $warehouse_list_tmp[] = $value[0];
                    }
                    $warehouse_list = $warehouse_list_tmp;
                    PurchaseTacticsWarehouse::deleteAll("tactics_id=:t_id",[':t_id' => $model->id ]);// 删除旧的适用仓库列表

                    // 保存 适用仓库 信息
                    foreach ($warehouse_list as $ware_code){
                        $ware_model                 = new PurchaseTacticsWarehouse();
                        $ware_model->tactics_id     = $model->id;
                        $ware_model->warehouse_code = $ware_code;
                        $ware_model->warehouse_name = $warehouseList[$ware_code];

                        $res = $ware_model->save();
                        if($res){

                        }else{
                            $error = current(current($ware_model->getErrors()));
                            throw new Exception("补货策略-适用仓库保存失败[$error]");
                        }
                    }

                }else{
                    $error = current(current($model->getErrors()));
                    throw new Exception("补货策略保存失败[$error]");
                }

                $transaction->commit();

                Yii::$app->getSession()->setFlash('success','恭喜你,保存成功');
                return $this->redirect(['index','tactics_id' => $model->id,'operator' => 'importSku','PurchaseTacticsSearch[tactics_type]' => $model->tactics_type]);
            }catch (Exception $e){
                $transaction->rollBack();
                //echo $e->getMessage();exit;
                Yii::$app->getSession()->setFlash('error',$e->getMessage(),false);
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $data  = Yii::$app->request->get();
            $id    = isset($data['id'])?$data['id']:0;

            if($id){
                $model = PurchaseTacticsSearch::findOne($id);
            }else{
                $model = new PurchaseTacticsSearch();
            }

            return $this->renderAjax('create-sku-tactics',['model' =>$model,'warehouseList' => $warehouseList]);
        }
    }


    /**
     * 导入 SKU记录
     * @return string|\yii\web\Response
     */
    public function actionImport(){
        set_time_limit(0);
        ini_set('memory_limit','512M');

        $model = new PurchaseTacticsSearch();
        if(Yii::$app->request->isPost AND $_FILES){
            $tactics_id   = Yii::$app->request->getQueryParam('tactics_id');
            $model              = PurchaseTactics::find()->where(['id' => $tactics_id])->one();// 备货策略

            $files      = $_FILES['PurchaseTacticsSearch'];
            $file_name  = $files['name']['file_execl'];
            $tmp_name   = $files['tmp_name']['file_execl'];

            $fileExp = explode('.', $file_name);
            $fileExp = strtolower($fileExp[count($fileExp) - 1]);//文件后缀

            if ($fileExp != 'xls' AND $fileExp != 'xlsx' ) {
                Yii::$app->getSession()->setFlash('error','只能导入EXCEL文件');
                return $this->redirect(Yii::$app->request->referrer);
            }

            if ($fileExp == 'xls') $PHPReader = new \PHPExcel_Reader_Excel5();
            if ($fileExp == 'xlsx') $PHPReader = new \PHPExcel_Reader_Excel2007();

            // 文件保存路径
            $path       = Yii::$app->basePath.'/web/files/product-repackage/';
            $filePath   = $path . date('YmdHis') . '.' . $fileExp;
            $time       = date('Y-m-d H:i:s');

            if (move_uploaded_file($tmp_name, $filePath)) {

                $PHPReader      = $PHPReader->load($filePath);
                $currentSheet   = $PHPReader->getSheet(0);
                $totalRows      = $currentSheet->getHighestRow();

                //设置的上传文件存放路径
                $sheetData  = $currentSheet->toArray(null,true,true,true);

                $exists_list = [];// 已经存在策略的SKU
                $success_list = [];

                if($sheetData){
                    $model_daily_sales  = PurchaseTacticsDailySales::find()->where(['tactics_id' => $tactics_id])->all();
                    $model_suggest      = PurchaseTacticsSuggest::find()->where(['tactics_id' => $tactics_id])->all();
                    $model_warehouse    = PurchaseTacticsWarehouse::find()->where(['tactics_id' => $tactics_id])->all();


                    $warehouse_list     = [];// 当前策略仓库列表
                    foreach($model_warehouse as $warehouse){
                        $warehouse_list[] = $warehouse->warehouse_code;
                    }

                    foreach($sheetData as $key => $data_value){
                        $sku    = trim($data_value['A']);
                        $status = trim($data_value['B']);
                        $status = ($status == '否')?2:1;// 状态(1.启用,2禁用)

                        if($key == 1 OR empty($sku)) continue;

                        if( PurchaseTactics::checkSkuWarehouseIsExists($sku,$warehouse_list) ){// 已经存在的SKU不能重复添加
                            $exists_list['此仓库已创建该SKU策略'][] = $sku;
                            continue;
                        }

                        $success_list[] = $sku;

                        // 保存补货策略数据
                        if(empty($model->sku)){
                            $model->sku = $sku;
                            $model->status = $status;
                            $res = $model->save();
                            continue;
                        }

                        // 复制备货策略数据
                        $new_model = new PurchaseTactics();
                        // 共同属性
                        $new_model->tactics_name = $model->tactics_name;
                        $new_model->tactics_type = $model->tactics_type;
                        $new_model->start_time = $model->start_time;
                        $new_model->end_time = $model->end_time;
                        $new_model->single_price = $model->single_price;
                        $new_model->inventory_holdings = $model->inventory_holdings;
                        $new_model->reserved_max = $model->reserved_max;
                        $new_model->days_15 = $model->days_15;
                        $new_model->days_30 = $model->days_30;
                        $new_model->sales_sd_value_range = $model->sales_sd_value_range;
                        $new_model->lead_time_value_range = $model->lead_time_value_range;
                        $new_model->weight_avg_period_value_range = $model->weight_avg_period_value_range;
                        $new_model->creator = Yii::$app->user->id;
                        $new_model->created_at = $time;

                        // 特有属性
                        $new_model->sku     = $sku;
                        $new_model->status  = $model->status;

                        $res = $new_model->save();

                        // 复制 配置补货策略-销量平均值天数比值
                        foreach($model_daily_sales as $daily_sales){
                            $new_model_daily_sales = new PurchaseTacticsDailySales();

                            $new_model_daily_sales->tactics_id  = $new_model->id;
                            $new_model_daily_sales->day_value   = $daily_sales->day_value;
                            $new_model_daily_sales->day_sales   = $daily_sales->day_sales;

                            $res = $new_model_daily_sales->save();
                        }

                        // 复制 配置补货策略-采购建议逻辑
                        foreach($model_suggest as $suggest){
                            $new_model_suggest = new PurchaseTacticsSuggest();

                            $new_model_suggest->tactics_id              = $new_model->id;

                            $new_model_suggest->type                    = $suggest->type;
                            $new_model_suggest->percent_start           = $suggest->percent_start;
                            $new_model_suggest->percent_end             = $suggest->percent_end;
                            $new_model_suggest->stockup_days            = $suggest->stockup_days;
                            $new_model_suggest->service_coefficient     = $suggest->service_coefficient;
                            $new_model_suggest->incr_days               = $suggest->incr_days;
                            $new_model_suggest->maximum                 = $suggest->maximum;
                            $new_model_suggest->minimum                 = $suggest->minimum;

                            $res = $new_model_suggest->save();
                        }

                        // 复制 配置补货策略-适用仓库列表
                        foreach($model_warehouse as $warehouse){
                            $new_model_warehouse = new PurchaseTacticsWarehouse();

                            $new_model_warehouse->tactics_id            = $new_model->id;

                            $new_model_warehouse->warehouse_name        = $warehouse->warehouse_name;
                            $new_model_warehouse->warehouse_code        = $warehouse->warehouse_code;

                            $res = $new_model_warehouse->save();
                        }
                    }
                }

                $message = "";
                foreach($exists_list as $key => $list){
                    $list = implode(',',$list);
                    $message .= "$key [$list]";
                }

                if(count($success_list) > 0 AND empty($message) ){
                    Yii::$app->getSession()->setFlash('success','恭喜你,添加成功');
                }elseif(count($success_list) > 0 AND $message ){
                    Yii::$app->getSession()->setFlash('warning',"恭喜你,添加成功【 $message 】");
                }else{
                    Yii::$app->getSession()->setFlash('warning',"添加失败【 $message 】");
                }

                $this->redirect(['index','PurchaseTacticsSearch[tactics_type]' => $model->tactics_type]);
            }else{
                Yii::$app->getSession()->setFlash('error','文件上传失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }else{
            $tactics_id   = Yii::$app->request->getQueryParam('tactics_id');

            return $this->renderAjax('import',['model' =>$model,'tactics_id' => $tactics_id]);
        }

    }


}