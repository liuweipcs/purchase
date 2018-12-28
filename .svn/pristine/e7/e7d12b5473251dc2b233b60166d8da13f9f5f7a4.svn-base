<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\helpers\BaseArrayHelper;

/**
 * This is the model class for table "{{%purchase_user}}".
 *
 * @property string $id
 * @property integer $pur_user_id
 * @property string $pur_user_name
 * @property integer $group_id
 * @property integer $grade
 * @property integer $crate_time
 * @property integer $edit_time
 * @property integer $creator
 * @property integer $editor
 */
class PurchaseUser extends BaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_user_id', 'grade'], 'required'],
            [['pur_user_id', 'group_id', 'type', 'grade', 'crate_time', 'edit_time', 'creator', 'editor'], 'integer'],
            [['pur_user_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pur_user_id' => Yii::t('app', 'Pur User ID'),
            'pur_user_name' => Yii::t('app', '采购用户名'),
            'group_id' => Yii::t('app', '采购小组'),
            'grade' => Yii::t('app', '级别'),
            'crate_time' => Yii::t('app', '创建时间'),
            'edit_time' => Yii::t('app', '修改时间'),
            'creator' => Yii::t('app', '创建人'),
            'editor' => Yii::t('app', '修改人'),
            'type' => Yii::t('app', '用户类型'),
        ];
    }

    /**
     * 采购组和组员同时下拉显示
     * @return array
     */
    public static function getBuyerAndGroup(){
        $pur_group=[
            'g1'=>'国内采购一组',
            'g2'=>'国内采购二组',
            'g3'=>'国内采购三组',
            'g4'=>'国内采购四组',
            'g5'=>'国内采购五组',
        ];
        $allbuery=self::find()->select('pur_user_id,pur_user_name')->asArray()->all();
        return $pur_group+BaseArrayHelper::map($allbuery,'pur_user_id','pur_user_name');
    }

    /**
     * 采购用户等级
     * @param $uid
     * @return string
     */
    public static function getUserGrade($uid){
        $grade=self::findOne(['pur_user_id'=>$uid]);
        $usergrade='';
        if(!empty($grade) && $grade->grade){
            $usergrade=Yii::$app->params['grade'][$grade->grade];
            if(!empty($usergrade)){
                $usergrade="( $usergrade )";
            }
        }
        return $usergrade;
    }

    /**
     * 用户类型
     * @return array
     */
    public static function getUserType(){
        return [0=>'国内采购组',1=>'海外采购组',2=>'FBA采购组'];
    }
    /**
     * 获取用户类型
     */
    public static function getType($uid)
    {
        $user_info=self::find()->where(['pur_user_id'=>$uid])->asArray()->one();
        return isset($user_info['type'])?$user_info['type']:false;
    }

    public static function getUserGradeInt($uid)
    {
        $model = self::findOne(['pur_user_id' => $uid]);
        if($model) {
            $grade = $model->grade ? $model->grade : 0;
        } else {
            $grade = 0;
        }
        return $grade;
    }
    /**
     * 采购小组
     */
    public static function getGroupId($uid=null,$user_name=null)
    {
        $where = [];
        if (!empty($uid)) {
            $where['pur_user_id'] = $uid;
        }
        if (!empty($user_name)) {
            $where['pur_user_name'] = $user_name;
        }
        return self::find()->select('group_id')->where($where)->scalar();
    }
    /**
     * 获取用户级别
     */
    public static function getGrade($uid=null,$user_name=null) 
    {
        $where = [];
        if (!empty($uid)) {
            $where['pur_user_id'] = $uid;
        }
        if (!empty($user_name)) {
            $where['pur_user_name'] = $user_name;
        }

        return self::find()->select('grade')->where($where)->scalar();
    }

}
