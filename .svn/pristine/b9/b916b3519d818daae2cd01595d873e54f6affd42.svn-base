<?php
namespace app\models;

use app\models\base\BaseModel;

use mdm\admin\models\Assignment;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $access_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends BaseModel implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public $grade;
    public $newPassword;
    public $oldPassword;
    public $retypePassword;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            ['email', 'email'],
            [['username', 'password_hash', 'email', 'alias_name', 'user_number'], 'required'],
            [['username', 'user_number'], 'unique'],
            [['username', 'telephone'], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username'      => Yii::t('app', '用户名'),
            'user_number' => Yii::t('app', '员工编码'),
            'alias_name'    => Yii::t('app', '别名'),
            'email'         => Yii::t('app', '电子邮件'),
            'password_hash' => Yii::t('app', '密码'),
            'role'          => Yii::t('app', '所属角色'),
            'status'        => Yii::t('app', '状态'),
            'created_at'    => Yii::t('app', '创建时间'),
            'updated_at'    => Yii::t('app', '更新时间'),
            'access_token'    => Yii::t('app', 'token'),
            'telephone'    => Yii::t('app', '电话'),
        ];
    }
    /**
     * @status
     */
    public static function Status($key = false){
        $array = [
            '10'=> '正常',
            '0'=> '禁用'
        ];
        if ($key === false){
            return $array;
        }else{
            return isset($array[$key]) ? $array[$key] : '';
        }

    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }


    public static function addUsernameRandompassword($username,$user_number){
        $model = new  self;
        $model->username = $username;
        $model->status   = self::STATUS_ACTIVE;
        $model->password_hash = Yii::$app->security->generatePasswordHash(rand(10000,9999));
        $model->auth_key    = Yii::$app->security->generateRandomString();
        $model->role        = 10;
        $model->email       = 'info@yibainetwork.com';
        $model->status      = 10;
        $model->created_at  = time();
        $model->user_number  = trim($user_number);
        $model->alias_name  = 'erp';
        $model->save(false);
//    	self::getDb()->createCommand()->insert(self::tableName(), array(
//    			'username' => $username,
//    			'status' => self::STATUS_ACTIVE,
//    			'password_hash' => Yii::$app->security->generatePasswordHash(rand(10000,9999)),
//    			'auth_key' => Yii::$app->security->generateRandomString(),
//    	))->execute();
        return $model->id;
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'access_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public static function loginByErpToken($username,$type=1){
        //$class = $this->identityClass;
        $identity = self::findByUsername($username);
        if ($identity && Yii::$app->user->login($identity)) {
            return $identity;
        } else {
            return null;
        }
    }

    /**
     * ERP 中嵌套链接访问 采购系统用户权限认证
     * @param $user   采购系统用户名
     * @param $user_number  用户编号
     * @return array
     */
    public static function accessCheckByErpUser($user,$user_number){
        $return = ['code' => 'success','message' => '','user_id' => ''];

        if($user && $user_number){
            if($user == 'admin'){
                $return['code'] = 'error';
                $return['message'] = 'admin无法登录';
            }
            if(!self::loginByErpToken('Erp_Create_Supplier',2)){// 判断用户是否存在
                $return['code'] = 'error';
                $return['message'] = '用户不存在【采购系统】';
            }else{
                $caiGouUser = self::find()->select('id')->where(['user_number'=>$user_number,'status'=>10])->scalar();
                if(!$caiGouUser){
                    self::addUsernameRandompassword($user,$user_number);// 用户不存在的时候添加一个用户
                    $caiGouUser = self::find()->select('id')->where(['user_number'=>$user_number,'status'=>10])->scalar();
                }
                $return['user_id'] = $caiGouUser;
            }
        }else{
            $return['code'] = 'error';
            $return['message'] = '必要参数为空【用户名和员工编号不能为空】';
        }

        return $return;
    }
}
