<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\DevelopTrack;

/**
 * DevelopTrackSearch represents the model behind the search form about `app\models\DevelopTrack`.
 */
class DevelopTrackSearch extends DevelopTrack
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku',], 'safe'],
        ];
       /* return [
            [['id', 'kf_audit_status', 'kf_zhijian_status', 'cg_xiaoshou_audit_status', 'cg_audit_status', 'cg_caiwu_pay_status', 'wms_daohuo_status', 'jiedian_status'], 'integer'],
            [['sku', 'kf_create_time', 'kf_user', 'kf_audit_time', 'kf_audit_user', 'kf_zhijian_time', 'kf_zhijian_user', 'cg_xiaoshou_time', 'cg_xiaoshou_user', 'cg_xiaoshou_audit_time', 'cg_xiaoshou_audit_user', 'cg_suggest_time', 'cg_suggest_user', 'cg_audit_time', 'cg_audit_user', 'cg_shenqing_pay_time', 'cg_shenqing_pay_user', 'cg_caiwu_audit_time', 'cg_caiwu_audit_user', 'cg_caiwu_pay_time', 'cg_caiwu_pay_user', 'wms_daohuo_time', 'wms_daohuo_user', 'wms_zhijian_time', 'wms_zhijian_user', 'wms_ruku_time', 'wms_ruku_user', 'wms_fahuo_time', 'wms_fahuo_user', 'wms_beihuo_time', 'wms_beihuo_user', 'wms_audit_time', 'wms_audit_user', 'wms_jianhuo_time', 'wms_jianhuo_user', 'wl_yanhuo_time', 'wl_shangjia_time', 'pur_number', 'demand_number'], 'safe'],
        ];*/
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
    public function search($sku=null)
    {
        $query = DevelopTrack::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        $query->orderBy('id desc');
        $query->andFilterWhere(['=', 'sku', $sku]);
        return $dataProvider;
    }
}
