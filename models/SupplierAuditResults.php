<?php

namespace app\models;

use app\models\base\BaseModel;
use yii\data\ActiveDataProvider;

use Yii;

/**
 * This is the model class for table "pur_supplier_audit_results".
 */
class SupplierAuditResults extends BaseModel
{

    public $audit_time;
    public $start_time;
    public $end_time;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier_audit_results}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supplier_code','audit_user','supplier_code','audit_status','audit_time','start_time','end_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'supplier_code'         => '供应商编码',
            'supplier_name'         => '供应商名称',
            'apply_time'            => '申请时间',
            'audit_status'          => '审核状态',
            'audit_user'            => '审核人',
            'audit_time'            => '审核时间',
            'audit_used'            => '审核时效',
            'res_type'              => '审核类型',
            'res_source'            => '来源',
            'create_time'           => '创建时间',
        ];
    }

    /**
     * 返回指定 状态值对应的 名称
     * @param  string     $status
     * @return array|bool|string
     * @desc 设置则返回状态值对应的 名称或false,未设置则返回 状态列表
     */
    public static function getStatusList($status = null){
        $status_list = [
            '10'  => '审核通过',
            '20' => '驳回',
        ];

        // 不准查询状态值为空的 Name
        if($status !== null AND empty($status)) return false;

        if($status){// 只返回 对应状态值的 Name
            return isset($status_list[$status])?$status_list[$status]:false;
        }

        return $status_list;
    }


    /**
     * 返回指定 状态值对应的 名称
     * @param  string     $type
     * @return array|bool|string
     * @desc 设置则返回状态值对应的 名称或false,未设置则返回 状态列表
     */
    public static function getResTypeList($type = null){
        $type_list = [
            '1' => '新增供应商',
            '2' => '修改资料',
        ];
        // 不准查询状态值为空的 Name
        if($type !== null AND empty($type)) return false;
        if($type){// 只返回 对应值的 Name
            return isset($type_list[$type])?$type_list[$type]:false;
        }
        return $type_list;
    }

    /**
     * 返回指定 状态值对应的 名称
     * @param  string     $type
     * @return array|bool|string
     * @desc 设置则返回状态值对应的 名称或false,未设置则返回 状态列表
     */
    public static function getResSourceList($type = null){
        $type_list = [
            '1' => 'ERP',
            '2' => '采购系统',
        ];
        // 不准查询状态值为空的 Name
        if($type !== null AND empty($type)) return false;
        if($type){// 只返回 对应值的 Name
            return isset($type_list[$type])?$type_list[$type]:false;
        }
        return $type_list;
    }

    /**
     * 获取默认供应商
     * @return mixed
     */
    public function getSupplier(){
        return $this->hasOne(Supplier::className(), ['supplier_code' => 'supplier_code']);
    }

    /**
     * 列表查询
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find();
        $query->alias('t');
        $query->andWhere(['is_show' => 1]);

        $dataProvider = new ActiveDataProvider([
               'query' => $query,
               'pagination' => [
                   'pageSize' => isset($params['per-page']) ? intval($params['per-page']) : 20,
               ],
           ]);

        if (isset($params['sort'])) {
            $dataProvider->setSort([
               'attributes' => [
                   'id' => [
                       'desc' => ['id' => SORT_DESC],
                       'asc' => ['id' => SORT_ASC],
                       'label' => 'id'
                   ],
                   'supplier_code' => [
                       'desc' => ['supplier_code' => SORT_DESC],
                       'asc' => ['supplier_code' => SORT_ASC],
                       'label' => 'supplier_code'
                   ],
                   'audit_user' => [
                       'desc' => ['audit_user' => SORT_DESC],
                       'asc' => ['audit_user' => SORT_ASC],
                       'label' => 'audit_user'
                   ],
                   'audit_date' => [
                       'desc' => ['audit_date' => SORT_DESC],
                       'asc' => ['audit_date' => SORT_ASC],
                       'label' => 'audit_date'
                   ],
                   'audit_used' => [
                       'desc' => ['audit_used' => SORT_DESC],
                       'asc' => ['audit_used' => SORT_ASC],
                       'label' => 'audit_used'
                   ],
                   'res_source' => [
                       'desc' => ['res_source' => SORT_DESC],
                       'asc' => ['res_source' => SORT_ASC],
                       'label' => 'res_source'
                   ],
               ]
           ]);
        } else {
            $query->orderBy('t.audit_date desc');
        }


        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere(['LIKE','t.audit_user',$this->audit_user]);
        $query->andFilterWhere(['=','t.supplier_code',$this->supplier_code]);
        $query->andFilterWhere(['=','t.audit_status',$this->audit_status]);

        if (!empty($this->start_time)) {
            $start_time   = $this->start_time . ' 00:00:00';
            $end_time     = $this->end_time . ' 23:59:59';
            $query->andFilterWhere(['between', 'audit_date', $start_time, $end_time]);
        }

        return $dataProvider;
    }


    /**
     * 新增供应商审核记录
     * @param $data
     * @return bool
     */
    public static function addOneResult($data){

        $model = new self();
        $model->supplier_code   = isset($data['supplier_code'])?$data['supplier_code']:'';
        $model->supplier_name   = isset($data['supplier_name'])?$data['supplier_name']:'';
        $model->apply_time      = isset($data['apply_time'])?$data['apply_time']:date('Y-m-d H:i:s');;
        $model->audit_status    = isset($data['audit_status'])?$data['audit_status']:0;

        $model->related_id      = isset($data['related_id'])?$data['related_id']:0;
        $model->res_type        = isset($data['res_type'])?$data['res_type']:0;
        $model->res_source      = isset($data['res_source'])?$data['res_source']:0;
        $model->create_time     = date('Y-m-d H:i:s');

        $res = $model->save(false);
        return $res;
    }

    /**
     * 更新供应商审核记录
     * @param $data
     * @return bool
     */
    public static function updateOneResult($data){
        $related_id     = $data['related_id'];
        $res_type       = $data['res_type'];

        $supplier_code  = $data['supplier_code'];
        $audit_time     = isset($data['audit_time'])?$data['audit_time']:date('Y-m-d H:i:s');

        $model = self::findOne(['res_type' => $res_type,'related_id' => $related_id]);
        if(empty($model)) return false;
        $model->audit_status    = isset($data['audit_status'])?$data['audit_status']:0;
        $model->audit_user      = Yii::$app->user->identity->username;
        $model->audit_date      = date('Y-m-d H:i:s');
        $model->audit_used      = sprintf("%.2f",(strtotime($audit_time) - strtotime($model->apply_time)) / 3600);// 时效 小时
        $model->is_show         = 1;

        $res = $model->save(false);
        return $res;

    }

}
