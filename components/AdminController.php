<?php
namespace callmez\wechat\components;

use Yii;
use yii\filters\AccessControl;
use callmez\wechat\models\Wechat;

/**
 * 微信管理后台控制器基类
 * 后台管理类虚继承此类
 *
 * @package callmez\wechat\components
 */
class AdminController extends BaseController
{
    /**
     * 存储管理微信的session key
     */
    const SESSION_MANAGE_WECHAT_KEY = 'session_manage_wechat';
    /**
     * 默认后台主视图
     * @var string
     */
    public $layout = '@callmez/wechat/views/layouts/main';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // 登录才能操作后台
                        'matchCallback' => function() { // 是否设置应用公众号
                            $controller = Yii::$app->controller;
                            if ($this->getWechat() || implode('/', [$controller->module->id, $controller->id]) == 'wechat/wechat') {
                                return true;
                            }
                            $this->flash('未设置管理公众号, 请先选则需要管理的公众号', 'error', ['/wechat/wechat']);
                            return false;
                        }
                    ]
                ]
            ]
        ];
    }

    /**
     * @var Wechat
     */
    private $_wechat;

    /**
     * 设置当前需要管理的公众号
     * @param Wechat $wechat
     */
    public function setWechat(Wechat $wechat)
    {
        $this->_wechat = $wechat;
    }

    /**
     * 获取当前管理的公众号
     * @return Wechat|null
     * @throws InvalidConfigException
     */
    public function getWechat()
    {
        if ($this->_wechat === null) {
            $wid = Yii::$app->session->get(self::SESSION_MANAGE_WECHAT_KEY);
            if (!$wid || ($wechat = Wechat::findOne($wid)) === null) {
                return false;
            }
            Yii::$app->session->set(self::SESSION_MANAGE_WECHAT_KEY, $wechat->id);
            $this->setWechat($wechat);
        }
        return $this->_wechat;
    }
}