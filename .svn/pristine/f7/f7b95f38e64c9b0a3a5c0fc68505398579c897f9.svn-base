<?php
namespace app\controllers;
use app\config\Vhelper;
use Yii;
use app\models\AlibabaZzh;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use linslin\yii2\curl;

class AlibabaZzhController extends Controller
{

    public function actionIndex()
    {
        $args = Yii::$app->request->queryParams;
        $data = AlibabaZzh::getList($args);
        return $this->render('index', $data);
    }

    public function actionAdd()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $model = new AlibabaZzh();
            $model->account = $post['account'];
            $model->status = 1;
            $model->group_id = 1;
            $model->pid = $post['level'] == 0 ? 0 : $post['pid'];
            $model->level = $post['level'];
            $model->user = $post['user'];
            $model->add_time = date('Y-m-d H:i:s', time());
            $res = $model->save(false);
            if($res) {
                Yii::$app->getSession()->setFlash('success','恭喜你，添加成功');
                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('error','对不起，数据保存失败');
                return $this->redirect(['index']);
            }
        } else {
            return $this->render('add');
        }
    }

    public function actionUpdate()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $model = AlibabaZzh::findOne($post['id']);
            $model->account = $post['account'];
            $model->pid     = $post['level'] == 0 ? 0 : $post['pid'];
            $model->level   = $post['level'];
            $model->user    = $post['user'];
            $res = $model->save(false);
            if($res) {
                Yii::$app->getSession()->setFlash('success','恭喜你，修改成功');
                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('error','对不起，修改失败');
                return $this->redirect(['index']);
            }
        } else {
            $id = $request->get('id');
            $model = AlibabaZzh::findOne($id);
            if(empty($model)) {
                throw new \yii\web\NotFoundHttpException('数据不存在');
                exit;
            }
            return $this->render('update', ['model' => $model]);
        }
    }

    public function actionDelete($id)
    {
        $id = (int)$id;
        if($id <= 0) {
            throw new \yii\web\NotFoundHttpException('参数错误');
            exit;
        }
        $model = AlibabaZzh::findOne($id);
        if(!empty($model)) {
            if($model->level == 0) {
                $r = AlibabaZzh::find()->where(['pid' => $model->id])->all();
                if(!empty($r)) {
                    throw new \yii\web\NotFoundHttpException('这是出纳账户，底下有付款对象，不能直接删除');
                    exit;
                }
            }
            $model->delete();
            Yii::$app->getSession()->setFlash('success','恭喜你，删除成功');
            return $this->redirect(['index']);
        } else {
            throw new \yii\web\NotFoundHttpException('删除失败');
            exit;
        }
    }

}