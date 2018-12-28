<?php

namespace app\api\v1\models;

use Yii;

class DevelopTrack extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%develop_track}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kf_create_time', 'kf_audit_time', 'kf_zhijian_time', 'cg_xiaoshou_time', 'cg_xiaoshou_audit_time', 'cg_suggest_time', 'cg_audit_time', 'cg_shenqing_pay_time', 'cg_caiwu_pay_time', 'wms_daohuo_time', 'wms_zhijian_time', 'wms_ruku_time', 'wms_fahuo_time', 'wms_beihuo_time', 'wms_audit_time', 'wms_jianhuo_time', 'wms_yanhuo_time', 'wms_shangjia_time'], 'safe'],
            [['kf_audit_status', 'kf_zhijian_status', 'cg_xiaoshou_audit_status', 'cg_audit_status', 'cg_caiwu_pay_status', 'wms_daohuo_status', 'jiedian_status'], 'integer'],
            [['sku'], 'string', 'max' => 100],
            [['kf_user', 'kf_audit_user', 'kf_zhijian_user', 'cg_xiaoshou_user', 'cg_xiaoshou_audit_user', 'cg_suggest_user', 'cg_audit_user', 'cg_shenqing_pay_user', 'cg_caiwu_pay_user', 'wms_daohuo_user', 'wms_zhijian_user', 'wms_ruku_user', 'wms_fahuo_user', 'wms_beihuo_user', 'wms_audit_user', 'wms_jianhuo_user'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'kf_create_time' => 'Kf Create Time',
            'kf_user' => 'Kf User',
            'kf_audit_status' => 'Kf Audit Status',
            'kf_audit_time' => 'Kf Audit Time',
            'kf_audit_user' => 'Kf Audit User',
            'kf_zhijian_status' => 'Kf Zhijian Status',
            'kf_zhijian_time' => 'Kf Zhijian Time',
            'kf_zhijian_user' => 'Kf Zhijian User',
            'cg_xiaoshou_time' => 'Cg Xiaoshou Time',
            'cg_xiaoshou_user' => 'Cg Xiaoshou User',
            'cg_xiaoshou_audit_status' => 'Cg Xiaoshou Audit Status',
            'cg_xiaoshou_audit_time' => 'Cg Xiaoshou Audit Time',
            'cg_xiaoshou_audit_user' => 'Cg Xiaoshou Audit User',
            'cg_suggest_time' => 'Cg Suggest Time',
            'cg_suggest_user' => 'Cg Suggest User',
            'cg_audit_status' => 'Cg Audit Status',
            'cg_audit_time' => 'Cg Audit Time',
            'cg_audit_user' => 'Cg Audit User',
            'cg_shenqing_pay_time' => 'Cg Shenqing Pay Time',
            'cg_shenqing_pay_user' => 'Cg Shenqing Pay User',
            'cg_caiwu_pay_status' => 'Cg Caiwu Pay Status',
            'cg_caiwu_pay_time' => 'Cg Caiwu Pay Time',
            'cg_caiwu_pay_user' => 'Cg Caiwu Pay User',
            'wms_daohuo_status' => 'Wms Daohuo Status',
            'wms_daohuo_time' => 'Wms Daohuo Time',
            'wms_daohuo_user' => 'Wms Daohuo User',
            'wms_zhijian_time' => 'Wms Zhijian Time',
            'wms_zhijian_user' => 'Wms Zhijian User',
            'wms_ruku_time' => 'Wms Ruku Time',
            'wms_ruku_user' => 'Wms Ruku User',
            'wms_fahuo_time' => 'Wms Fahuo Time',
            'wms_fahuo_user' => 'Wms Fahuo User',
            'wms_beihuo_time' => 'Wms Beihuo Time',
            'wms_beihuo_user' => 'Wms Beihuo User',
            'wms_audit_time' => 'Wms Audit Time',
            'wms_audit_user' => 'Wms Audit User',
            'wms_jianhuo_time' => 'Wms Jianhuo Time',
            'wms_jianhuo_user' => 'Wms Jianhuo User',
            'wms_yanhuo_time' => 'Wms Yanhuo Time',
            'wms_shangjia_time' => 'Wms Shangjia Time',
            'jiedian_status' => 'Jiedian Status',
        ];
    }
    /**
     * 更新数据
     */
    public static function updateDevelopTrack()
    {
        echo 12345;
    }
    /**
     * 保存旧的数据
     */
    public static function saveOldInfo($old_model)
    {
        $model = new self;
        $model->sku = $old_model->sku;
        $model->kf_create_time = $old_model->kf_create_time;
        $model->kf_user = $old_model->kf_user;
        $model->kf_audit_status = $old_model->kf_audit_status;
        $model->kf_audit_time = $old_model->kf_audit_time;
        $model->kf_audit_user = $old_model->kf_audit_user;
        $model->kf_zhijian_status = $old_model->kf_zhijian_status;
        $model->kf_zhijian_time = $old_model->kf_zhijian_time;
        $model->kf_zhijian_user = $old_model->kf_zhijian_user;
        // $model->cg_xiaoshou_time = $old_model->cg_xiaoshou_time;
        // $model->cg_xiaoshou_user = $old_model->cg_xiaoshou_user;
        // $model->cg_xiaoshou_audit_status = $old_model->cg_xiaoshou_audit_status;
        // $model->cg_xiaoshou_audit_time = $old_model->cg_xiaoshou_audit_time;
        // $model->cg_xiaoshou_audit_user = $old_model->cg_xiaoshou_audit_user;
        // $model->cg_suggest_time = $old_model->cg_suggest_time;
        // $model->cg_suggest_user = $old_model->cg_suggest_user;
        // $model->cg_audit_status = $old_model->cg_audit_status;
        // $model->cg_audit_time = $old_model->cg_audit_time;
        // $model->cg_audit_user = $old_model->cg_audit_user;
        // $model->cg_shenqing_pay_time = $old_model->cg_shenqing_pay_time;
        // $model->cg_shenqing_pay_user = $old_model->cg_shenqing_pay_user;
        // $model->cg_caiwu_audit_time = $old_model->cg_caiwu_audit_time;
        // $model->cg_caiwu_audit_user = $old_model->cg_caiwu_audit_user;
        // $model->cg_caiwu_pay_status = $old_model->cg_caiwu_pay_status;
        // $model->cg_caiwu_pay_time = $old_model->cg_caiwu_pay_time;
        // $model->cg_caiwu_pay_user = $old_model->cg_caiwu_pay_user;
        // $model->wms_daohuo_status = $old_model->wms_daohuo_status;
        // $model->wms_daohuo_time = $old_model->wms_daohuo_time;
        // $model->wms_daohuo_user = $old_model->wms_daohuo_user;
        // $model->wms_zhijian_time = $old_model->wms_zhijian_time;
        // $model->wms_zhijian_user = $old_model->wms_zhijian_user;
        // $model->wms_ruku_time = $old_model->wms_ruku_time;
        // $model->wms_ruku_user = $old_model->wms_ruku_user;
        // $model->wms_fahuo_time = $old_model->wms_fahuo_time;
        // $model->wms_fahuo_user = $old_model->wms_fahuo_user;
        // $model->wms_beihuo_time = $old_model->wms_beihuo_time;
        // $model->wms_beihuo_user = $old_model->wms_beihuo_user;
        // $model->wms_audit_time = $old_model->wms_audit_time;
        // $model->wms_audit_user = $old_model->wms_audit_user;
        // $model->wms_jianhuo_time = $old_model->wms_jianhuo_time;
        // $model->wms_jianhuo_user = $old_model->wms_jianhuo_user;
        // $model->wl_yanhuo_time = $old_model->wl_yanhuo_time;
        // $model->wl_shangjia_time = $old_model->wl_shangjia_time;
        $model->jiedian_status = 0;
        $status = $model->save();
        return $model;
    }
    /**
     * 获取采购节点数据
     */
    public static function getCgInfo($sku)
    {
        //销售建单日期，建单人，销售审核状态，审核时间，审核人
        $xiaoshou_info = PlatformSummary::find()
            ->select(['sku','demand_number','create_time cg_xiaoshou_time','create_id cg_xiaoshou_user','level_audit_status cg_xiaoshou_audit_status','purchase_time cg_xiaoshou_audit_time','buyer cg_xiaoshou_audit_user'])
            ->where(['sku'=>$sku])
            ->asArray()->all();
        return $xiaoshou_info;
        return PlatformSummary::getXiaoshouInfo($sku);
    }
}
