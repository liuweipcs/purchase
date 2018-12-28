<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseReceive;
use yii\helpers\ArrayHelper;

/**
 * PurchaseReceiveSearch represents the model behind the search form about `app\models\PurchaseReceive`.
 */
class PurchaseReceiveSearch extends PurchaseReceive
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'qty', 'delivery_qty', 'presented_qty'], 'integer'],
            [['pur_number', 'supplier_code', 'supplier_name', 'buyer', 'sku', 'receive_status', 'receive_type', 'handle_type', 'bearer', 'created_at', 'creator','is_return','currency_code'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$type=1)
    {
        //$fields=['*','sum(qty) as total_qty','sum(delivery_qty) as total_delivery_qty','sum(presented_qty) as total_presented_qty','sum(refund_amount) as total_refund_amount'];
        $fields=['*','qty as total_qty','delivery_qty as total_delivery_qty','presented_qty as total_presented_qty','refund_amount as total_refund_amount'];
        //$query = PurchaseReceive::find()->groupBy(['express_no','pur_number','handle_type'])->select($fields);
        $query = PurchaseReceive::find()->select($fields);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if($type==1){
            $puid= PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
            $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
            if(in_array(Yii::$app->user->id,$ids))
            {

            } else {
                $query->andWhere(['in', 'buyer',Yii::$app->user->identity->username]);
            }
        }else{
            $ids = Yii::$app->authManager->getUserIdsByRole('采购组-国内');
            $idms = Yii::$app->authManager->getUserIdsByRole('采购经理组');
            $username = User::find()->select('username')->andFilterWhere(['in','id',$ids])->asArray()->all();
            $musername = User::find()->select('username')->andFilterWhere(['in','id',$idms])->asArray()->all();
            $usernameArray = array_merge($username,$musername);
            $query->andWhere(['in', 'buyer',array_column($usernameArray,'username')]);
        }
        $query->orderBy('created_at desc');
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'qty' => $this->qty,
            'delivery_qty' => $this->delivery_qty,
            'presented_qty' => $this->presented_qty,
            'created_at' => $this->created_at,
            'is_return' => $this->is_return,
        ]);

        $query->andFilterWhere(['like', 'pur_number', trim($this->pur_number)])

            ->andFilterWhere(['like', 'supplier_name', trim($this->supplier_name)])
            ->andFilterWhere(['like', 'buyer', trim($this->buyer)])
            ->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'receive_status', $this->receive_status])
            ->andFilterWhere(['like', 'receive_type', $this->receive_type])
            ->andFilterWhere(['like', 'handle_type', $this->handle_type])
            ->andFilterWhere(['like', 'bearer', $this->bearer])
            ->andFilterWhere(['like', 'creator', $this->creator]);
        

        return $dataProvider;
    }
}
