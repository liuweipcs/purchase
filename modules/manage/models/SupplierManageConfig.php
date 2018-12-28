<?php

namespace app\modules\manage\models;

use app\models\Supplier;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "pur_supplier_manage_config".
 *
 * @property integer $id
 * @property string $supplier_code
 * @property string $supplier_name
 * @property integer $is_commit_quotes
 * @property string $password
 * @property string $create_time
 * @property string $create_user_name
 * @property string $create_user_ip
 * @property string $update_time
 * @property string $update_user_name
 * @property string $update_user_ip
 * @property string $product_line_limit
 * @property string $supplier_code_limit
 */
class SupplierManageConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_manage_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supplier_code', 'password', 'create_time', 'create_user_name', 'create_user_ip'], 'required'],
            [['is_commit_quotes'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['supplier_code'], 'string', 'max' => 150],
            [['password', 'create_user_ip', 'update_user_ip'], 'string', 'max' => 100],
            [['create_user_name', 'update_user_name','supplier_name'], 'string', 'max' => 200],
            [['product_line_limit'], 'string', 'max' => 2000],
            [['supplier_code_limit'], 'string', 'max' => 5000],
            [['supplier_code'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_code' => 'Supplier Code',
            'is_commit_quotes' => 'Is Commit Quotes',
            'password' => 'Password',
            'create_time' => 'Create Time',
            'create_user_name' => 'Create User Name',
            'create_user_ip' => 'Create User Ip',
            'update_time' => 'Update Time',
            'update_user_name' => 'Update User Name',
            'update_user_ip' => 'Update User Ip',
            'product_line_limit' => 'Product Line Limit',
            'supplier_name' => 'Supplier Name',
            'supplier_code_limit' => 'Supplier Code Limit',
        ];
    }

    public static function saveConfig($datas,$supplier_code){
        try{
            $supplier_name = Supplier::find()->select('supplier_name')->where(['supplier_code'=>$supplier_code])->scalar();
            if(!$supplier_name){
                throw new Exception('供应商不存在');
            }
            $model = self::find()->where(['supplier_code'=>$supplier_code])->one();
            if(empty($model)){
                $model = new self();
            }
            $supplier_code_limit = isset($datas['supplier_code_limit']) ? $datas['supplier_code_limit'] : '';
            $product_line_limit = isset($datas['product_line_limit']) ? $datas['product_line_limit'] : '';
            $model->supplier_code = $supplier_code;
            $model->is_commit_quotes = $model->isNewRecord ? 1 : $model->is_commit_quotes;
            $model->password        = $model->isNewRecord ? md5('Aa1234') : $model->password;
            $model->create_time     = $model->isNewRecord ? date('Y-m-d H:i:s',time()): $model->create_time;
            $model->create_user_name=$model->isNewRecord ? Yii::$app->user->identity->username :$model->create_user_name;
            $model->create_user_ip  =$model->isNewRecord ? Yii::$app->request->userIP :$model->create_user_ip;
            if(!$model->isNewRecord){
                $model->update_time        = date('Y-m-d H:i:s',time());
                $model->update_user_name   = Yii::$app->user->identity->username;
                $model->update_user_ip     = Yii::$app->request->userIP;
            }

            $model->supplier_name = $model->isNewRecord ? $supplier_name : $model->supplier_name;
            $model->supplier_code_limit = empty($supplier_code_limit) ? '' :implode(',',$supplier_code_limit);
            $model->product_line_limit  = empty($product_line_limit) ? '' : implode(',',$product_line_limit);
            if($model->save()==false){
                throw new Exception('配置失败');
            }
            $response=['status'=>'success','message'=>$supplier_name.'报价配置成功'];
        }catch (Exception $exception){
            $response=['status'=>'error','message'=>$exception->getMessage()];
        }
        return $response;
    }
}
