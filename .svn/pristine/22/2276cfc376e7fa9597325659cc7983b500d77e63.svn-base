<?php
namespace app\models;

use app\models\base\BaseModel;
use yii;
use yii\base\Model;

class ChangeLog extends BaseModel
{
    public function rules()
    {
        return [
            [['oper_id','oper_type'], 'safe'],
        ];
    }

    /**
     * 添加操作日志
     * @param $data
     * @return bool
     *
     * @example
     *      $data = array(
     *          oper_id     => 目标记录ID,
     *          oper_type   => 操作类型(关联模型)
     *          content     => 改变的内容（关键字,支持搜索）
     *          update_data => 改变的内容（详细信息,文本类型）
     *          is_show     => 标记日志类型（1.展示日志,2.非展示日志，默认 1）
     *      )
     */
    public static function addLog($data){
        $model                  = new self();
        $model->oper_id         = isset($data['oper_id'])?$data['oper_id']:'';
        $model->oper_type       = isset($data['oper_type'])?$data['oper_type']:'';
        $model->content         = isset($data['content'])?$data['content']:'';

        $update_data = isset($data['update_data'])?$data['update_data']:'';
        if(!is_string($update_data) AND !is_numeric($update_data)){
            $update_data = json_encode($update_data);
        }

        $model->update_data     = $update_data;
        $model->operator        = isset(Yii::$app->user->identity->username)?Yii::$app->user->identity->username:'';
        $model->operate_time    = date('Y-m-d H:i:s', time());
        $model->is_show         = isset($data['is_show'])?$data['is_show']:1;
        $status = $model->save(false);
        return $status;

    }
}