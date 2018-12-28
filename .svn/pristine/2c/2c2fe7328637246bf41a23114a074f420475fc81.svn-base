<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/10
 * Time: 17:36
 */

namespace app\models;

use app\models\base\BaseModel;
use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\LowerRateStatistics;
use yii\helpers\BaseArrayHelper;


class LowerRateStatisticsSearch extends LowerRateStatistics
{
    public $start_time;
    public $end_time;
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['create_time', 'buyer','buyer_id','start_time','end_time'], 'safe'],
        ];
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    public function search($params)
    {

        $query = LowerRateStatistics::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->andWhere(['not in','buyer',['的确良品store','朱青清','刘彩芬','郭梦清','王开伟','青云','杨静','云扬机械密封有限公司','王丽云','温州飞越模型店','刘秋平','刘思仪','吉祥宠物鸟食用品店','陈娟','刘玲俐','罗谦','王彤','蒋贵洋','周美霞','精诚汽车配件精诚汽配']]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        if(!empty($params)){
            if(!empty($this->buyer_id)){
                if(is_numeric($this->buyer_id)){
                    $query->andFilterWhere(['buyer_id' => $this->buyer_id,]);
                }else{
                    $group=[
                        'g1'=>'1',
                        'g2'=>'2',
                        'g3'=>'3',
                        'g4'=>'4',
                        'g5'=>'5',
                        
                    ];

                    $gid=$group[$this->buyer_id];

                    $puid=PurchaseUser::find()->select('pur_user_id')->where(['group_id'=>$gid])->asArray()->all();
                    $query->andFilterWhere(['in', 'buyer_id', array_values(BaseArrayHelper::map($puid,'pur_user_id','pur_user_id'))]);
                }
            }
        }

        //判断创建时间
        if(!empty($this->create_time))
        {
            $start_time = date('Y-m-d 00:00:00',strtotime($this->start_time));
            $end_time   = date('Y-m-d 23:59:59',strtotime($this->end_time));
            $query->andFilterWhere(['between', 'create_time', $start_time, $end_time]);
//            $query->andFilterWhere(['between', 'create_time', $this->start_time, $this->end_time]);
        } else {
            $start_time = date('Y-m-d 00:00:00',time()-86400);
            $end_time   = date('Y-m-d 23:59:59',time()-86400);
            $query->andFilterWhere(['between', 'create_time', $start_time, $end_time]);
        }
        $query->andFilterWhere(['like', 'buyer', trim($this->buyer)]);

//Vhelper::dump($query->createCommand()->getRawSql());
        return $query;
        return $dataProvider;
    }
}