<?php

namespace app\controllers;


use app\config\Vhelper;
use app\models\SupplierContactInformation;
use app\models\SupplierImages;
use app\models\SupplierPaymentAccount;
use app\services\BaseServices;
use app\services\CommonServices;
use app\services\SupplierServices;
use Yii;
use app\models\Supplier;
use app\models\SupplierSearch;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\UploadedFile;


/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class SupplierKpiController extends BaseController
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
     * Lists all Stockin models.
     * @return mixed
     */
    public function actionIndex()
    {

        $searchModel = new SupplierSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 查看详情
     * Displays a single Stockin model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model= Supplier::find()->where(['id'=>$id])->with(['pay','contact','img'])->one();

        return $this->render('view', [
            'p1' =>[],
            'p2'  =>[],
            'model' => $model,
        ]);
    }

    /**
     * 图片异步上传
     */
    public function actionAsyncImage ()
    {

        if (Yii::$app->request->isPost) {
            $name= 'SupplierImages[image_url]';
            Vhelper::ImageAsynUpolad($name);

        }
    }
    /**
     * 供应商创建
     * Creates a new Supplier model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model        = new Supplier();

        $model_pay    = new SupplierPaymentAccount();
        $model_contact= new SupplierContactInformation();
        $model_img    = new SupplierImages();
        $p1 = $p2 = [];
        if (Yii::$app->request->isPost) {
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                        //入主
                        $data = $model->saveSupplier(Yii::$app->request->Post());
                        //附图表
                        $model_img->saveSupplierImg(Yii::$app->request->Post(),$data);
                        //支付方式
                        $model_pay->saveSupplierPay(Yii::$app->request->Post(),$data);
                        //联系方式
                        $model_contact->saveSupplierContact(Yii::$app->request->Post(),$data);
                        $transaction->commit();
                        Yii::$app->getSession()->setFlash('success', '恭喜你,添加成功',true);
                        return $this->redirect(['index']);

            } catch (Exception $e) {

                $transaction->rollBack();
            }

        }
        return $this->render('create', [
                'model' => $model,
                'model_pay' => $model_pay,
                'model_contact' => $model_contact,
                'model_img' => $model_img,
                'p1' =>$p1,
                'p2' =>$p2,
        ]);

    }

    /**
     * 修改操作
     * Updates an existing Supplier model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        //联表查询
        $model         = Supplier::find()->where(['id'=>$id])->with(['pay','contact','img'])->one();
        $p1            = $p2 = [];

        //开启事务
        $transaction=\Yii::$app->db->beginTransaction();
        try {
                //加载主表并验证保存
                if ($model->load(Yii::$app->request->post()) && $model->validate())
                {
                    if ($model->save())
                    {
                        $id = $model->attributes['id'];
                    }
                    //保存支付
//                    $S =Vhelper::changeData(Yii::$app->request->Post()['SupplierPaymentAccount']);
//                    Vhelper::dump(Yii::$app->request->Post()['SupplierPaymentAccount']);
//                    Vhelper::dump($S);
                    if(!empty(Yii::$app->request->Post()['SupplierPaymentAccount']))
                    {
                        foreach (Yii::$app->request->Post()['SupplierPaymentAccount'] as $k => $v)
                        {
                            if (!empty($v))
                            {
                                foreach ($v as $d => $c) {
                                    $model->pay[$k]->setAttribute($d, $c);
                                    $model->pay[$k]->save(false);
                                }
                            }
                        }
                    }

                    //保存联系
                    if(!empty(Yii::$app->request->Post()['SupplierContactInformation']))
                    {
                        foreach (Yii::$app->request->Post()['SupplierContactInformation'] as $k => $v)
                        {
                            if (!empty($v))
                            {
                                foreach ($v as $d => $c)
                                {
                                    $model->contact[$k]->setAttribute($d, $c);
                                    $model->contact[$k]->save(false);
                                }
                            }
                        }
                    }
                    //批量插入图片
                    if(!empty(Yii::$app->request->Post()['SupplierImages']))
                    {
                        foreach (Yii::$app->request->Post()['SupplierImages'] as $k => $v) {
                            if (!empty($v))
                            {
                                foreach ($v as $d => $c)
                                {
                                    Yii::$app->db->createCommand()->batchInsert(SupplierImages::tableName(), ['supplier_id', 'image_url'], [[$id, $c]])->execute();
                                }
                            }
                        }
                    }
                    //提交事务
                    $transaction->commit();
                    Yii::$app->getSession()->setFlash('success', '恭喜你！更新成功', true);


                    return $this->redirect(['index']);

                }
        } catch (Exception $e) {
            //回滚
            $transaction->rollBack();
        }

            return $this->render('update', [
                'model' => $model,
                //'p1'    =>$p1,
                'p2'    =>$p2,

        ]);
    }

    /**
     * Finds the Stockin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Stockin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Supplier::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    /**
     * 删除上传到临时目录的图片
     * @return string
     */
    public function actionDeletePic()
    {
        $error = '';
        if (Yii::$app->request->isPost) {
            $uploadpath = 'Uploads/' . date('Ymd') . '/';  //上传路径
            // 图片保存在本地的路径：images/Uploads/当天日期/文件名，默认放置在basic/web/下
            $dir = '/images/' . $uploadpath;
            $filename = yii::$app->request->post("filename");
            $filename = $dir . $filename;
            if (file_exists(Yii::getAlias('@app') . '/web' . $filename)) {
                unlink(Yii::getAlias('@app') . '/web' . $filename);
            }

        }
        echo json_encode($error);
    }

    /**
     * 批量修改供应商属性
     * @return string|\yii\web\Response
     */
    public  function actionAllEditSupplierAttr()
    {
        $model        = new Supplier();
        if (Yii::$app->request->isPost)
        {
            $result = $model->saveSupplierOne(Yii::$app->request->post());
            Yii::$app->getSession()->setFlash('success','恭喜你!修改成功');
            return $this->redirect(['index']);
        } else {
            $id = Yii::$app->request->get('id');
            return $this->renderAjax('attributes', [
                'model' => $model,
                'id' =>$id,
            ]);
        }
    }
    /**ajax进行验证
     * @return array
     */
    public function actionValidateForm () {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new Supplier();
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }
    /**
     * 三级联动
     * Function output the site that you selected.
     * @param int $pid
     * @param int $typeid
     */
    public function actionSites($pid, $typeid = 0)
    {

        $model = BaseServices::getCityList($pid);

        if($typeid == 1){$aa="--请选择市--";}else if($typeid == 2 && $model){$aa="--请选择区--";}

        echo Html::tag('option',$aa, ['value'=>'empty']) ;

        foreach($model as $value=>$name)
        {
            echo Html::tag('option',Html::encode($name),array('value'=>$value));
        }
    }

    /**
     * 搜索供应商
     * @param $q
     * @return array
     */
    public function actionSearchSupplier($q = null, $id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {

            $data = Supplier::find()
                ->select('supplier_code as id,supplier_name AS text')
                ->andFilterWhere(['like', 'supplier_name', $q])
                ->distinct()
                ->limit(50)
                ->asArray()
                ->all();

            $out['results'] = array_values($data);
        }elseif ($id > 0) {
                $out['results'] = ['id' => $id, 'text' => Supplier::find($id)->supplier_name];
        }
        return $out;
    }

    /**
     * 上传供应商
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionExSupplier()
    {
        $model = new Supplier();
        if (Yii::$app->request->isPost)
        {
            $model->file_execl = UploadedFile::getInstance($model, 'file_execl');

            if($model->file_execl->getExtension()!='csv')
            {
                Yii::$app->getSession()->setFlash('error',"格式不正确",true);
                return $this->redirect(['index']);
            }
            $data              = $model->upload();

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }
                $num = count($datas);
                for ($c = 0; $c < $num; $c++) {

                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');

                }

                $Name[$line_number][] = Yii::$app->user->id;
                $Name[$line_number][] = time();
                $line_number++;
            }



            foreach($Name as $v)
            {
                $transaction=\Yii::$app->db->beginTransaction();
                $models                      = new Supplier();
                $models_con                  = new SupplierContactInformation();
                try {

                    $models->supplier_name       = $v['0'];
                    $models->supplier_code       = CommonServices::getNumber('QS');
                    $models->supplier_address    = $v['1'];
                    $models->buyer               = $v['6'];
                    $models->create_time         = $v['7'];
                    $models->status              = 1;
                    $models->supplier_settlement = 1;
                    $models->payment_method      = 1;
                    $models->is_taxpayer         = !empty($v['4'])?$v['4']:0;
                    $models->taxrate             = !empty($v['5'])?$v['5']:'';
                    $models->save();
                    $models_con->contact_person = $v['2'];
                    $models_con->contact_number = $v['3'];
                    $models_con->supplier_id    = $models->id;
                    $models_con->supplier_code  = $models->supplier_code;
                    $statu = $models_con->save();
                    //提交事务
                    $transaction->commit();
                } catch (Exception $e) {
                    //回滚
                    $transaction->rollBack();
                }

            }
            fclose($file);
            if ($statu) {
                Yii::$app->getSession()->setFlash('success',"恭喜你，导入成功！",true);
                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('error','恭喜你，导入失败了！请联系管理员',true);
                return $this->redirect(['index']);
            }

        } else {
            return $this->renderAjax('addfile', ['model' => $model]);
        }
    }

}
