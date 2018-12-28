<?php
namespace app\controllers;
use Yii;
use app\config\Vhelper;
use app\models\Template;

class TemplateController extends BaseController
{

    public function actionIndex()
    {
        $model = new Template();
        $tpls = $model::find()->all();
        return $this->render('index', ['model' => $model, 'tpls' => $tpls]);
    }

    // 模板配置
    public function actionSetting()
    {
        $request = Yii::$app->request;
        if($request->post()) {
            $model = new Template();
            $model->attributes = $request->post();
            if(!$model->validate()) {
                foreach($model->errors as $v) {
                    echo implode(',', $v)."<br/>";
                }
                exit;
            }
            if($model->save()) {
                Yii::$app->getSession()->setFlash('success','恭喜你，保存成功');
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                Yii::$app->getSession()->setFlash('error','对不起，保存失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {

            // 抓取默认模板信息
            $tplDir = Yii::getAlias('@template');

            $tpls = glob($tplDir.'/*.php');

            $files = [];
            foreach($tpls as $filename) {
                $files[] = basename($filename, '.php');
            }
            return $this->render('setting', ['files' => $files]);
        }
    }

    public function actionGetTpl($code)
    {
        if(!$code) {
            return '模板编码错误';
        }
        $filePath = Yii::getAlias('@template');
        $filePath .= '/'.$code.'.php';
        return file_get_contents($filePath);
    }







}