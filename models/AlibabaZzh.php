<?php
namespace app\models;

use app\models\base\BaseModel;
use Yii;
use linslin\yii2\curl;
use yii\data\Pagination;
class AlibabaZzh extends BaseModel
{


    public static $status_to_txt = [
        1 => '<span class="label label-success">使用中</span>',
        2 => '<span class="label label-danger">禁用</span>',
        3 => '<span class="label label-warning">冻结</span>',
    ];

    public static $level_to_txt = [
        0 => '<span class="label label-success">出纳</span>',
        1 => '<span class="label label-info">非出纳</span>',
    ];

    public static $payer;

    public static function tableName()
    {
        return '{{%alibaba_zzh}}';
    }

    public function formName()
    {
        return '';
    }

    public static function getList($param)
    {
        $model = new self();
        $query = AlibabaZzh::find()
            ->from('pur_alibaba_zzh as a')
            ->select(['a.*', 'b.username'])
            ->leftJoin('pur_user as b', 'a.user = b.id');

        if(isset($param['account']) && trim($param['account']) !== '') {
            $query->andWhere(['account' => trim($param['account'])]);
            $model->account = trim($param['account']);
        }

        if(isset($param['user']) && $param['user'] !== '') {
            $query->andWhere(['user' => $param['user']]);
            $model->user = $param['user'];
        }

        if(isset($param['pid']) && $param['pid'] !== '') {
            $query->andWhere(['pid' => $param['pid']]);
            $model->pid = $param['pid'];
        }

        if(isset($param['level']) && in_array($param['level'], [0, 1])) {
            $query->andWhere(['level' => $param['level']]);
            $model->level = $param['level'];
        }

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count]);
        $data = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        return [
            'model' => $model,
            'data' => $data,
            'pagination' => $pagination
        ];
    }

    // 获取付款人
    public static function getPayer()
    {
        $payer = self::find()
            ->select(['pur_alibaba_zzh.id', 'pur_alibaba_zzh.account', 'pur_user.username'])
            ->leftJoin('pur_user', 'pur_alibaba_zzh.user=pur_user.id')
            ->where('pur_alibaba_zzh.level=0')
            ->asArray()
            ->all();
        $data = [];
        foreach($payer as $v) {
            $data[$v['id']] = $v['username'];
        }
        self::$payer = $data;
        return $data;
    }

    // 获取出纳可支付用户
    public static function getPayableIds($id)
    {
        $ids = self::find()->select('user')->where(['pid' => $id])->column();
        return !empty($ids) ? $ids : false;
    }








}
