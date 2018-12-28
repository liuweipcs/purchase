<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "pur_cost_purchase_num".
 *
 * @property integer $id
 * @property integer $apply_id
 * @property string $date
 * @property integer $purchase_num
 * @property string $create_time
 * @property string $update_time
 * @property string $sku
 */
class CostPurchaseNum extends BaseModel
{
    public $group;
    public $new_supplier_code;
    public $create_user_name;
    public $check_start_time;
    public $check_end_time;
    public $type;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_cost_purchase_num';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'purchase_num'], 'integer'],
            [['sku','type','date','group','new_supplier_code','create_user_name','check_start_time','check_end_time', 'create_time', 'update_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apply_id' => 'Apply ID',
            'date' => 'Date',
            'purchase_num' => 'Purchase Num',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'sku'         => 'SKU',
        ];
    }

    public function getApply(){
        return $this->hasOne(SupplierUpdateApply::className(),['id'=>'apply_id']);
    }

    public function search2($params, $noDataProvider = false)
    {
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,//要多少写多少吧
            ],
        ]);
        $this->load($params);
        $query->alias('t');
        $query->leftJoin(SupplierUpdateApply::tableName().' apply','t.apply_id=apply.id');
        $query->andFilterWhere(['apply.status'=>2]);
        $query->andWhere(['t.status'=>1]);
        $query->andWhere('left(t.date,7)>=left(apply.cost_begin_time,7)');
        $query->andFilterWhere(['not in','apply.type',[4,5]]);
        $query->leftJoin(SupplierQuotes::tableName().' old','old.id=apply.old_quotes_id');
        $query->leftJoin(SupplierQuotes::tableName().' new','new.id=apply.new_quotes_id');
        $query->andWhere('old.supplierprice <> new.supplierprice');
        if(!empty($this->type)&&$this->type==1){
            $query->andWhere('old.supplierprice > new.supplierprice');
        }
        if(!empty($this->type)&&$this->type==2){
            $query->andWhere('old.supplierprice < new.supplierprice');
        }
        $query->andFilterWhere(['t.date'=>$this->date]);
        $query->andFilterWhere(['<>','t.purchase_num',0]);

        if(!empty($this->group)&&$this->group==1){
            $userIds = Yii::$app->authManager->getUserIdsByRole('供应链');
            $query->andFilterWhere(['in','apply.create_user_id',$userIds]);
        }
        if(!empty($this->group)&&$this->group==2){
            $FBAIds = Yii::$app->authManager->getUserIdsByRole('FBA采购组');
            $FBAMIds = Yii::$app->authManager->getUserIdsByRole('FBA采购经理组');
            $userIds = array_merge($FBAIds,$FBAMIds);
            $query->andFilterWhere(['in','apply.create_user_id',$userIds]);
        }
        if(!empty($this->group)&&$this->group==3){
            $GNIds = Yii::$app->authManager->getUserIdsByRole('采购组-国内');
            $GNMIds = Yii::$app->authManager->getUserIdsByRole('采购经理组');
            $userIds = array_merge($GNIds,$GNMIds);
            $query->andFilterWhere(['in','apply.create_user_id',$userIds]);
        }
        if(!empty($this->group)&&$this->group==4){
            $GNIds = Yii::$app->authManager->getUserIdsByRole('采购组-海外');
            $GNMIds = Yii::$app->authManager->getUserIdsByRole('采购经理组');
            $userIds = array_merge($GNIds,$GNMIds);
            $query->andFilterWhere(['in','apply.create_user_id',$userIds]);
        }
        if (!$this->validate()) {
            return $dataProvider;
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'apply.new_supplier_code' => $this->new_supplier_code,
        ]);
        $query->andFilterWhere(['like', 't.sku', trim($this->sku)])
            ->andFilterWhere(['like', 'apply.create_user_name', trim($this->create_user_name)])
            ->andFilterWhere(['between', 'apply.cost_begin_time', $this->check_start_time, $this->check_end_time]);
        //$query->groupBy('t.id');

        \Yii::$app->session->set('CostPurchaseNumData', $params);
        if ($noDataProvider)
            return $query;

//        Vhelper::dump($query->createCommand()->getRawSql());
        return $dataProvider;
    }

}
