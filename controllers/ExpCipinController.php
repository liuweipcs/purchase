<?php
namespace app\controllers;
use app\models\ExcepReturnInfo;
use Yii;
use app\config\Vhelper;
use yii\web\Controller;
use app\models\PurchaseWarehouseAbnormal;
use app\models\PurchaseWarehouseAbnormalSearch;
use app\models\Address;
class ExpCipinController extends Controller
{

    public function actionIndex()
    {
        $searchModel = new PurchaseWarehouseAbnormalSearch();
        $args = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($args, 2);
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
            try {
                if($data['handler_type'] == 2) {
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
            } catch(\Exception $e) {

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


    public function actionExcepReturnInfo($excep_number){
        $excepInfo = ExcepReturnInfo::find()->where(['excep_number'=>$excep_number])->all();
        return $this->renderAjax('return-info',['excepInfo'=>$excepInfo]);
    }
}