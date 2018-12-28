<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/8
 * Time: 10:37
 */

namespace app\models;

use app\models\base\BaseModel;


use app\config\Vhelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class PurchaseOrderCancelSearch extends PurchaseOrderCancel
{
    public $start_time;
    public $end_time;

    public function rules()
    {
        return [
          [['pur_number','buyer','create_time', 'audit', 'audit_time','buyer_id','audit_status','cs.sku','por.pay_status','start_time', 'end_time','pay_status'], 'safe'],
        ];
    }
    //添加关联字段到可搜索属性集合
    public function attributes()
    {
        return array_merge(parent::attributes(), ['cs.sku','por.pay_status']);
    }
    public function scenarios()
    {
        return Model::scenarios();
    }
    /**
     * fba审核
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function search($params)
    {
        $query = PurchaseOrderCancel::find();
        $query->alias('c');
        $query->where(['=', 'c.purchase_type', 3]);

        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);

        $query->orderBy('create_time desc');

        //$query->where(['in','c.audit_status',['1','2','3']]);
        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->getAttribute('cs.sku')) {
            $query->joinWith('purchaseOrderCancelSub AS cs');
        }
        if ($this->getAttribute('por.pay_status')) {
            $query->joinWith('purchaseOrderReceipt AS por');
        }

        $query->andFilterWhere([
            'c.buyer' => $this->buyer,
            'c.audit' => $this->audit,
            'c.buyer_id' => $this->buyer_id,
            'c.audit_status' => $this->audit_status,
            'por.pay_status' => $this->getAttribute('por.pay_status'),
        ]);
        $query->andFilterWhere(['like','pur_number',trim($this->pur_number)]);
        $query->andFilterWhere(['like','cs.sku',trim($this->getAttribute('cs.sku'))]);
        $query->andFilterWhere(['between', 'c.create_time',$this->start_time,$this->end_time]);
//        Vhelper::dump($query->createCommand()->getRawSql());

        return $dataProvider;
    }
    /**
     * 海外仓-网采单
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function overseasSearch($params)
    {
        $query = PurchaseOrderCancel::find();
        $query->alias('c');
        $query->where(['in', 'c.purchase_type', [2,4]]);


        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);

        $query->orderBy('create_time desc');

        //$query->where(['in','c.audit_status',['1','2','3']]);
        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->getAttribute('cs.sku')) {
            $query->joinWith('purchaseOrderCancelSub AS cs');
        }
        if ($this->getAttribute('por.pay_status')) {
            $query->joinWith('purchaseOrderReceipt AS por');
        }

        $query->andFilterWhere([
            'c.buyer' => $this->buyer,
            'c.audit' => $this->audit,
            'c.buyer_id' => $this->buyer_id,
            'c.audit_status' => $this->audit_status,
            'por.pay_status' => $this->getAttribute('por.pay_status'),
        ]);
        $query->andFilterWhere(['like','pur_number',trim($this->pur_number)]);
        $query->andFilterWhere(['like','cs.sku',trim($this->getAttribute('cs.sku'))]);
        $query->andFilterWhere(['between', 'c.create_time',$this->start_time,$this->end_time]);
//        Vhelper::dump($query->createCommand()->getRawSql());

        return $dataProvider;
    }
}