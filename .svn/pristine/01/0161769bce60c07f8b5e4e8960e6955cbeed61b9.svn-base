<?php
namespace app\models;

use app\models\base\BaseModel;
use Yii;
use linslin\yii2\curl;
class AlibabaAccount extends BaseModel
{

    // 阿里巴巴开放平台api列表
    public $api_list = [
        'buyerView' => 'param2/1/com.alibaba.trade/alibaba.trade.get.buyerView/',  // 获取订单详情（买家视角）
        'urlGet'    => 'param2/1/com.alibaba.trade/alibaba.alipay.url.get/'         // 获取支付宝支付链接（买家视角）
    ];

    // 阿里巴巴开放平台网关
    public $ali_gateway = 'http://gw.open.1688.com/openapi/';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%alibaba_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'last_update_time', 'modify_user_id', 'expires_in', 'bind_account'], 'integer'],
            [['app_key', 'secret_key','account'], 'required'],
            [['account'], 'string', 'max' => 100],
            [['account','app_key','secret_key'], 'trim'],
            [['access_token', 'refresh_token', 'redirect_uri', 'code'], 'string', 'max' => 255],
            [['app_key', 'secret_key', 'refresh_token_timeout'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'account' => Yii::t('app', '账号'),
            'access_token' => Yii::t('app', '访问令牌'),
            'refresh_token' => Yii::t('app', 'refresh_token'),
            'status' => Yii::t('app', '状态'),
            'last_update_time' => Yii::t('app', '最后更新时间'),
            'modify_user_id' => Yii::t('app', '修改用户ID'),
            'app_key' => Yii::t('app', 'appkey'),
            'secret_key' => Yii::t('app', '签名密钥'),
            'redirect_uri' => Yii::t('app', '返回URL'),
            'code' => Yii::t('app', '临时授权码code'),
            'refresh_token_timeout' => Yii::t('app', 'refreshToken的过期时间'),
            'expires_in' => Yii::t('app', '表示access_token的多小个小时失效'),
            'bind_account' => Yii::t('app', '绑定账户'),
        ];
    }

    // 计算api签名
    public function makeSignature($args)
    {
        $aliParams = array();
        foreach($args['param'] as $key => $val) {
            $aliParams[] = $key . $val;
        }
        sort($aliParams);
        $sign_str = join('', $aliParams);
        $sign_str = $args['apiInfo'] . $sign_str;
        $code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $args['appSecret'], true)));
        return $code_sign;
    }

    // 执行api访问
    public function executeApi($apiInfo, $param)
    {
        $query = http_build_query($param);
        $url = $this->ali_gateway.$apiInfo.'?'.$query;
        $curl = new curl\Curl();
        $s = $curl->post($url);
        $response = json_decode($s, 1);
        return $response;
    }



















}
