<?php
namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use yii\data\ActiveDataProvider;

class UebExpressReceipt extends BaseModel
{

    public $start_time;
    public $end_time;

    public static function tableName()
    {
        return 'ueb_express_receipt';
    }

    public function rules()
    {
        return [
            [[
                'express_single',
                'relation_order_no',
                'status',
                'add_time',
                'start_time',
                'end_time',
                'weight',
            ], 'safe']

        ];
    }
    public function formName()
    {
        return '';
    }

    public function search($params)
    {
        $query = self::find();
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);
        if(!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'express_single' => trim($this->express_single),
            'relation_order_no' => trim($this->relation_order_no),
            'status' => $this->status,
        ]);

        if($this->add_time) {
            $times = explode(' - ', $this->add_time);
            $query->andFilterWhere(['>', 'add_time', $times[0]]);
            $query->andFilterWhere(['<', 'add_time', $times[1]]);
        }

        $query->orderBy('quality_time desc');

        return $dataProvider;




    }

    /**
     * 获取签收时间
     */
    public static function getUebExpressReceipt($express_no=null)
    {
        $res = self::find()->select(['express_single','add_time'])->where(['express_single'=>$express_no])->orderBy('id desc')->all();
        if (!empty($res)) {
            $data = '';
            foreach ($res as $v) {
                $data .= $v['add_time'] . '<br />';
            }
            return $data;
        } else {
            return '';
        }
    }




}
