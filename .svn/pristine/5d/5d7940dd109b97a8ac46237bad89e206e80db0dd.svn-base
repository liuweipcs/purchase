<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\Product;
use app\models\SkuSingleTacticMainContent;
use app\models\TablesChangeLog;
use app\models\Warehouse;
use Yii;
use app\models\SkuSingleTacticMain;
use app\models\SkuSingleTacticMainSearch;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SkuSingleTacticMainController implements the CRUD actions for SkuSingleTacticMain model.
 */
class SkuSingleTacticMainController extends BaseController
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

    /**
     * 首页
     */
    public function actionIndex()
    {
        $searchModel = new SkuSingleTacticMainSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 查看详情
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * 创建
     */
    public function actionCreate()
    {
        $model = new SkuSingleTacticMain();
        $contentmodel = new SkuSingleTacticMainContent();

        if ($model->load(Yii::$app->request->post())) {
            $tran = Yii::$app->db->beginTransaction();
            try {
                $skumodel=$model;
                $skumodel->sku=$model->sku;
                $skumodel->warehouse=$model->warehouse;
                $skumodel->date_start=$model->date_start;
                $skumodel->date_end=$model->date_end;
                $skumodel->status=$model->status;
                $skumodel->user=Yii::$app->user->identity->username;
                $skumodel->create_date=date('Y-m-d H:i:s',time());
                $skusave=$skumodel->save();

                //表修改日志-新增
                $change_content = "insert:新增id值为{$skumodel->id}的记录";
                $change_data = [
                    'table_name' => 'pur_sku_single_tactic_main', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                if($skusave){
                    $contentmodel->supply_days=$model->supply_days;
                    $contentmodel->minimum_safe_stock_days=$model->minimum_safe_stock_days;
                    $contentmodel->single_tactic_main_id=$skumodel->attributes['id'];
                    $contentmodel->days_safe_transfer=$skumodel->days_safe_transfer;
                    $contentmodel->status=$model->status;
                    $contsave=$contentmodel->save();

                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$contentmodel->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_sku_single_tactic_main_content', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);


                    if($contsave){
                        //写入日志
                        $day = 0 . '->' . $model->sku . ';';
                        $day .= 0 . '->' . $model->warehouse . ';';
                        $msg             = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '对id' . $skumodel->attributes['id'] . '=====' . $day . '进行了创建';
                        $data['type']    = 10;
                        $data['pid']     = $skumodel->attributes['id'];
                        $data['module']  = '单独sku补货';
                        $data['content'] = $msg;
                        Vhelper::setOperatLog($data);
                        Yii::$app->session->setFlash('success','恭喜你,创建成功');
                        $tran->commit();
                        return $this->redirect(['index']);
                    }else{
                        Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
                    }
                }
                $tran->commit();
                return Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
            } catch (Exception $e) {
                $tran->rollBack();
                return Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 修改
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $contentmodel=SkuSingleTacticMainContent::findOne(['single_tactic_main_id'=>$id]);


        if ($model->load(Yii::$app->request->post()))
        {

            $skumodel=$model;

            $skumodel->sku=$model->sku;
            $skumodel->warehouse=$model->warehouse;
            $skumodel->date_start=$model->date_start;
            $skumodel->date_end=$model->date_end;
            $skumodel->status=$model->status;

            //写入日志
            $day = $model->oldAttributes['sku'] . '->' . Yii::$app->request->post()['SkuSingleTacticMain']['sku']. ';';
            $day .= $model->oldAttributes['warehouse'] . '->' . Yii::$app->request->post()['SkuSingleTacticMain']['warehouse'] . ';';
            $msg             = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '对id' . $skumodel->attributes['id'] . '=====' . $day . '进行了更新';
            $data['type']    = 10;
            $data['pid']     = $skumodel->attributes['id'];
            $data['module']  = '单独sku补货';
            $data['content'] = $msg;

            $tran = Yii::$app->db->beginTransaction();
            try {
                Vhelper::setOperatLog($data);

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($skumodel->attributes, $skumodel->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_sku_single_tactic_main', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $skusave=$skumodel->save();

                if($skusave){
                    $contentmodel->supply_days=$model->supply_days;
                    $contentmodel->minimum_safe_stock_days=$model->minimum_safe_stock_days;
                    $contentmodel->days_safe_transfer=$model->days_safe_transfer;
                    $contentmodel->status=$model->status;

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($contentmodel->attributes, $contentmodel->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_sku_single_tactic_main_content', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $contsave=$contentmodel->save();

                    if($contsave){

                        Yii::$app->session->setFlash('success','恭喜你,操作成功');
                        $tran->commit();
                        return $this->redirect(['index']);
                    }else{
                        Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
                    }
                }
                Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
                $tran->commit();
            } catch(Exception $e) {
                $tran->rollBack();
                Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
                return $this->redirect(['index']);
            }

        } else {
            $model->supply_days=$contentmodel->supply_days;
            $model->minimum_safe_stock_days=$contentmodel->minimum_safe_stock_days;
            $model->days_safe_transfer=$contentmodel->days_safe_transfer;

            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 删除
     */
    public function actionDelete($id)
    {

        $this->findModel($id)->delete();

        //表修改日志-删除
        $change_content = "delete:删除id值为{$id}的记录";
        $change_data = [
            'table_name' => 'pur_sku_single_tactic_main', //变动的表名称
            'change_type' => '3', //变动类型(1insert，2update，3delete)
            'change_content' => $change_content, //变更内容
        ];

        $tran = Yii::$app->db->beginTransaction();
        try{
            TablesChangeLog::addLog($change_data);

            //表修改日志-删除
            $change_content = "delete:删除single_tactic_main_id值为{$id}的记录";
            $change_data = [
                'table_name' => 'pur_sku_single_tactic_main_content', //变动的表名称
                'change_type' => '3', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);

            SkuSingleTacticMainContent::findOne(['single_tactic_main_id'=>$id])->delete();
            $tran->commit();
            Yii::$app->session->setFlash('success','恭喜你,操作成功');
        }catch(Exception $e) {
            Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
            $tran->rollBack();
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * 批量删除
     */
    public function actionDeleteBatch()
    {
        $ids_arr = Yii::$app->request->post('ids');
        $ids_str = implode(',', $ids_arr);
        $status = SkuSingleTacticMain::deleteAll(['in','id',$ids_arr]);
        //表修改日志-删除
        $change_content = "delete:删除single_tactic_main_id值为{$ids_str}的记录";
        $change_data = [
            'table_name' => 'pur_sku_single_tactic_main_content', //变动的表名称
            'change_type' => '3', //变动类型(1insert，2update，3delete)
            'change_content' => $change_content, //变更内容
        ];
        TablesChangeLog::addLog($change_data);
        if ($status) {
            Yii::$app->session->setFlash('success','批量删除成功');
        } else {
            Yii::$app->session->setFlash('error','批量删除失败');
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    protected function findModel($id)
    {
        if (($model = SkuSingleTacticMain::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    /**
     * sku补货策略导入
     * @return string|\yii\web\Response
     */
    public function actionReplenishmentStrategyImport(){
        $model = new SkuSingleTacticMain();
        if (Yii::$app->request->isPost && $_FILES)
        {
            $extension=pathinfo($_FILES['SkuSingleTacticMain']['name']['file_execl'], PATHINFO_EXTENSION);

            $filessize=$_FILES['SkuSingleTacticMain']['size']['file_execl']/1024/1024;
            $filessize=round($filessize,2);

            if($filessize>10)
            {
                Yii::$app->getSession()->setFlash('warning',"文件大小不能超过 10M，当前大小： $filessize M",true);
                return $this->redirect(['index']);
            }


            if($extension!='csv')
            {
                Yii::$app->getSession()->setFlash('warning',"格式不正确,只接受 .csv 格式的文件",true);
                return $this->redirect(['index']);
            }
            $name= 'SkuSingleTacticMain[file_execl]';
            $data = Vhelper::upload($name);

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            $s=0;

            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }
                $sku_sku = mb_convert_encoding(trim($datas[0]),'utf-8','gbk');
                $sku=Product::find()->where(['sku'=>$sku_sku])->asArray()->one()['sku'];

                if(!$sku){
                    Yii::$app->getSession()->setFlash('warning',"该sku采购系统不存在{$sku}",true);
                    return $this->redirect(['index']);
                    $s++;
                    continue;
                }
                $num = count($datas);
                for ($c = 0; $c < $num; $c++) {
                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');
                }
                if(!empty($Name[$line_number][1]) && !is_numeric($Name[$line_number][1])){
                    $Name[$line_number][1] =strtoupper($Name[$line_number][1]);
                }

                //仓库
                if(!empty($Name[$line_number][1]) && !is_numeric($Name[$line_number][1])){

                    $purchase_warehouse = Warehouse::find()->select('id,warehouse_code,warehouse_name')->where(['use_status'=>1,'warehouse_name'=>trim($Name[$line_number][1])])->asArray()->one()['warehouse_code'];

                    $arr_warehouse = array(
                        'SZ_AA',
                    );
                    if (empty($purchase_warehouse)) {
                        Yii::$app->getSession()->setFlash('error',$Name[$line_number][1] . ' -- 采购仓有误或为停运状态',true);
                        return $this->redirect(['index']);
                    } elseif (!in_array($purchase_warehouse,$arr_warehouse)) {
                        Yii::$app->getSession()->setFlash('error','仓库只能为：易佰东莞仓库',true);
                        return $this->redirect(['index']);
                    }
                    $Name[$line_number][1] = $purchase_warehouse;
                } else {

                    Yii::$app->getSession()->setFlash('error','仓库不能为空--'.$sku_sku,true);
                    return $this->redirect(['index']);
                }
                //生效时间
                $date_start = date('Y-m-d H:i:s', strtotime($Name[$line_number][2]));
                $Name[$line_number][2] = $date_start;

                //结束时间
                $date_end = date('Y-m-d H:i:s', strtotime($Name[$line_number][3]));
                $Name[$line_number][3] = $date_end;
                $Name[$line_number][6] = trim($Name[$line_number][6]) == '否' ? 0 : 1;
                $Name[$line_number][] = Yii::$app->user->identity->username;

                if (!strtotime($Name[$line_number][2]) || !strtotime($Name[$line_number][3])) { //strtotime转换不对，日期格式显然不对。
                    Yii::$app->getSession()->setFlash('error','时间格式不对--'.$sku_sku,true);
                    return $this->redirect(['index']);
                }

                //数据一次性入库
                $transaction=\Yii::$app->db->beginTransaction();
                try{
                    //保存
                    $skumodel = new SkuSingleTacticMain();
                    $skumodel->sku=$Name[$line_number][0];
                    $skumodel->warehouse=$Name[$line_number][1];
                    $skumodel->date_start=$Name[$line_number][2];
                    $skumodel->date_end=$Name[$line_number][3];
                    $skumodel->status=$Name[$line_number][6];
                    $skumodel->user=Yii::$app->user->identity->username;
                    $skumodel->create_date=date('Y-m-d H:i:s',time());
                    $skusave=$skumodel->save(false);

                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$skumodel->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_sku_single_tactic_main', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);

                    if($skusave){
                        $contentmodel = new SkuSingleTacticMainContent();
                        $contentmodel->supply_days=$Name[$line_number][4];
                        $contentmodel->minimum_safe_stock_days=$Name[$line_number][5];
                        $contentmodel->single_tactic_main_id=$skumodel->attributes['id'];
                        $contentmodel->days_safe_transfer=$Name[$line_number][7];
                        $contentmodel->status=$Name[$line_number][6];
                        $contsave=$contentmodel->save(false);

                        //表修改日志-新增
                        $change_content = "insert:新增id值为{$contentmodel->id}的记录";
                        $change_data = [
                            'table_name' => 'pur_sku_single_tactic_main_content', //变动的表名称
                            'change_type' => '1', //变动类型(1insert，2update，3delete)
                            'change_content' => $change_content, //变更内容
                        ];
                        TablesChangeLog::addLog($change_data);

                        if($contsave){
                            //写入日志
                            $data = [];
                            $day = 0 . '->' . $Name[$line_number][0] . ';';
                            $day .= 0 . '->' . $Name[$line_number][1] . ';';
                            $msg             = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '对id' . $skumodel->attributes['id'] . '=====' . $day . '进行了创建';
                            $data['type']    = 10;
                            $data['pid']     = $skumodel->attributes['id'];
                            $data['module']  = '单独sku补货';
                            $data['content'] = $msg;
                            Vhelper::setOperatLog($data);
                        }else{
                            Yii::$app->session->setFlash('error',"导入失败，数据{$Name[$line_number][0]} 有问题，修改后重新导入");
                            return $this->redirect(['index']);
                        }
                    }
                    $transaction->commit();
                }catch (Exception $e){
                    $transaction->rollBack();
                    Yii::$app->getSession()->setFlash('error','恭喜你，导入失败了！请联系管理员',true);
                    return $this->redirect(['index']);
                }
                $line_number++;
            }

            fclose($file);

            $dir=Yii::getAlias('@app') .'/web/files/' . date('Ymd');
            if (file_exists($dir)){
                FileHelper::removeDirectory($dir);
            }

                Yii::$app->getSession()->setFlash('success',"恭喜你，导入成功！",true);
                return $this->redirect(['index']);

        } else {
            return $this->renderAjax('import', ['model' => $model]);
        }
    }
}
