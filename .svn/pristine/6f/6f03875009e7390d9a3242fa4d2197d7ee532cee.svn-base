<?php

namespace app\models;

use app\models\base\BaseModel;
use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%tables_change_log}}".
 *
 * @property integer $id
 * @property string $table_name
 * @property string $create_user
 * @property string $create_time
 * @property integer $change_type
 * @property string $change_content
 * @property string $create_ip
 */
class TablesChangeLog extends BaseModel
{
    public static $tableChangeLogEnabled = false;        //是否记录数据表操作日志
    
    const CHANGE_TYPE_INSERT = 1;  //插入
    const CHANGE_TYPE_UPDATE = 2; //修改
    const CHANGE_TYPE_DELETE = 3; //删除

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tables_change_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['table_name', 'create_id', 'create_user', 'change_type', 'change_content', 'create_ip'], 'required'],
            [['create_time'], 'safe'],
            [['change_type'], 'integer'],
            [['change_content'], 'string'],
            [['table_name'], 'string', 'max' => 200],
            [['create_id','create_user', 'create_ip'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'table_name' => '变动的表名称',
            'create_id' => '操作人ID',
            'create_user' => '操作人',
            'create_time' => '操作时间',
            'change_type' => '变动类型(1insert，2update，3delete)',
            'change_content' => '变更内容',
            'create_ip' => '记录ip',
        ];
    }
    public static function get_change_type_data($key='')
    {
        $list = [
             static::CHANGE_TYPE_INSERT => 'insert',
             static::CHANGE_TYPE_UPDATE => 'update',
             static::CHANGE_TYPE_DELETE => 'delete',
        ];
        
        if (!empty($key)) {
            return $list[$key];
        }
        
        return $list;
    }
    /**
     * 根据表操作情景来新增日志
     * @param string $table_name 数据变动的表名称
     * @param int    $change_type 变动类型(1insert，2update，3delete)
     * @param string $change_content 变动的内容描述
     */
    public static function save_tables_change_log_data($table_name,$change_type,$change_content)
    {
        $model = new self();
        $model->table_name = $table_name;
        $model->create_time = date('Y-m-d H:i:s');
        $model->create_user = BaseModel::SYSTEM_USER;
        $model->create_id = 1;
        $user = null;
        if (isset(\Yii::$app->user)) {
            $user = \Yii::$app->user->getIdentity();
            if ($user != null) {
                $model->create_user = $user->username;
                $model->create_id = $user->id;
            } else {
                return false;
            }
        } else {
            return false;
        }

        //系统自动脚本操作的不记录日志
        if ($model->create_user == BaseModel::SYSTEM_USER)
            return true;
        $model->change_type = $change_type;
        $model->change_content = $change_content;
        $model->create_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $model->route = Yii::$app->request->absoluteUrl;

        if (!$model->save()) {
            if (!$model->save(false)) {
                # code...
                throw new \Exception(current(current($model->getErrors()))); 
            }
        }

        return $model->id;
    }

    /**
     * 添加日志
     */
    public static function addLog($data)
    {
        $model = new self();
        $model->table_name = $data['table_name']; //变动的表名称
        $model->create_id = Yii::$app->user->id;
        $model->create_user = Yii::$app->user->identity->username;
        $model->create_time = date('Y-m-d H:i:s', time());
        $model->change_type = $data['change_type']; //变动类型(1insert，2update，3delete)
        $model->change_content = $data['change_content']; //变更内容
        $model->create_ip = Yii::$app->request->userIP;
        $model->route = Yii::$app->request->absoluteUrl;
        $status = $model->save(false);
        return $status;
    }
    
    public static function addLogByModel($tableModel, $is_delete = 0)
    {
        $model = new self();
        $model->table_name = $tableModel::tableName();
        $model->create_id = Yii::$app->user->id;
        $model->create_user = Yii::$app->user->identity->username;
        $model->create_time = date('Y-m-d H:i:s', time());
        if ($is_delete == 1) {
            $model->change_type = 3;
        } else {
            $model->change_type = $tableModel->getIsNewRecord() ? 1 : 2;
        }
        $model->change_content = $tableModel->getDirtyAttributes(); //变更内容
        $model->create_ip = Yii::$app->request->userIP;
        $model->route = Yii::$app->request->absoluteUrl;
        $model->save(false);
    }

    /**
    $tran = Yii::$app->db->beginTransaction();
    try {

    } catch (Exception $e) {
    $tran->rollBack();
    return $this->render(Yii::$app->request->referrer);
    }
     */

    /**
     * 修改时：判断修改前后的内容
//表修改日志-更新
$change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
$change_data = [
'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
'change_type' => '2', //变动类型(1insert，2update，3delete)
'change_content' => $change_content, //变更内容
//'change_content' => "update:pur_number:{$updateArray},purchas_status:=>4", //变更内容
];
TablesChangeLog::addLog($change_data);
     */
    public static function updateCompare($new, $old)
    {
        $id = !empty($old['id']) ? $old['id'] : '';
        $data = 'update:id:' . $id . ',';
        foreach ($old as $nk => $nv) {
            if ( $nv !== $new[$nk]) {
                $data .= "$nk:$nv=>$new[$nk],";
            }
        }
        return $data;
    }
    /**
     * 新增数据处理
//表修改日志-新增
$change_content = "insert:新增id值为{$model->id}的记录";
$change_data = [
'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
'change_type' => '1', //变动类型(1insert，2update，3delete)
'change_content' => $change_content, //变更内容
];
TablesChangeLog::addLog($change_data);
     */
    public static function insertCompare($insert_info)
    {
        $data = 'insert:';
        foreach ($insert_info as $k => $v) {
            $data .= "$k:$v,";
        }
        return $data;
    }
    /**
     * 删除数据处理
//表修改日志-删除
$change_content = "delete:删除id值为{$model->id}的记录";
$change_data = [
'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
'change_type' => '3', //变动类型(1insert，2update，3delete)
'change_content' => $change_content, //变更内容
];
TablesChangeLog::addLog($change_data);
     */
    public static function deleteCompare()
    {
        return true;
    }
}
