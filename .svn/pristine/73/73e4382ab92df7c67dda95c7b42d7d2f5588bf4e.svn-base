<?php

namespace app\modules\manage\models;

use app\config\Vhelper;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "pur_supplier_permission".
 *
 * @property integer $id
 * @property string $module
 * @property string $controller
 * @property string $action
 * @property string $permission_name
 * @property integer $type
 * @property integer $parent_id
 * @property integer $is_show
 * @property integer $order_num
 * @property integer $icon
 */
class SupplierPermission extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_permission';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'parent_id'], 'integer'],
            [['module', 'controller', 'action','icon'], 'string', 'max' => 50],
            [['permission_name'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'module' => 'Module',
            'controller' => 'Controller',
            'action' => 'Action',
            'permission_name' => 'Permission Name',
            'type' => 'Type',
            'parent_id' => 'Parent ID',
            'is_show' => '是否左侧显示',
            'order_num' => '排序',
            'icon' => '图标',
        ];
    }

    public static function getPermission(){
        return [0=>'顶级菜单']+ArrayHelper::map(self::find()->select('id,permission_name')->where(['in','type',[1,2]])->asArray()->all(),'id','permission_name');
    }

    public static function savePermission($saveModel,$datas){
        try {
            $model = self::find()
                ->where(['module' => $datas['module'],
                    'controller' => $datas['controller'],
                    'action' => $datas['action'],
                    'type' => $datas['type'],
                    'permission_name' => $datas['permission_name'],
                ])->one();
            if (!empty($model)&&($saveModel->isNewRecord||$saveModel->id!=$model->id)) {
                throw new Exception('当前权限已经存在');
            }
            $saveModel->module = $datas['module'];
            $saveModel->controller = $datas['controller'];
            $saveModel->action = $datas['action'];
            $saveModel->permission_name = $datas['permission_name'];
            $saveModel->parent_id = $datas['parent_id'];
            $saveModel->is_show = $datas['is_show'];
            $saveModel->order_num = $datas['order_num'];
            $saveModel->type = $datas['type'];
            if ($saveModel->save()==false){
                throw new Exception($saveModel->isNewRecord ? '权限添加失败' :'权限编辑失败');
            }
            $response = ['status'=>'success','message'=>$saveModel->isNewRecord ? '权限添加成功' :'权限编辑成功'];
        }catch (Exception $e){
            $response = ['status'=>'error','message'=>$saveModel->isNewRecord ? '权限添加失败' :'权限编辑失败'];
        }
        return $response;
    }

    public static function getTreeDatas(){
        $datas = [];
        $firstsLevel = self::find()->select('id,parent_id,permission_name,type')->where(['parent_id'=>0])->orderBy('order_num ASC')->asArray()->all();
        foreach ($firstsLevel as $key=>$value){
            $datas[$key] = ['id'=>$value['id'],'permission_name'=>$value['permission_name'],'type'=>$value['type']];
            $secondLevel = self::find()->select('id,parent_id,permission_name,type')->where(['parent_id'=>$value['id']])->orderBy('order_num ASC')->asArray()->all();
            foreach ($secondLevel as $k=>$v){
                $thirdLevel = self::find()->select('id,parent_id,permission_name,type')->where(['parent_id'=>$v['id']])->orderBy('order_num ASC')->asArray()->all();
                $datas[$key]['items'][$k] = ['id'=>$v['id'],'permission_name'=>$v['permission_name'],'type'=>$v['type'],'items'=>$thirdLevel];
            }
        }
        return $datas;
    }
    public function search($params){
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        // $query->where(['sku'=>'JM00042']);
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }
}
