<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/12/5
 * Time: 下午10:27
 */

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use Overtrue\Socialite\SocialiteManager;

class GithubController extends BaseController
{
    private $socialite;

    public function onConstruct()
    {
        $this->socialite = new SocialiteManager(['github' => $this->commonConfig->github->toArray()]);
    }

    /**
     * 跳转到github授权页面
     */
    public function authAction()
    {
        $response = $this->socialite->driver('github')->redirect();
        $response->send();
    }

    /**
     * github授权成功回调地址
     */
    public function callbackAction()
    {
        $user = $this->socialite->driver('github')->user();
        var_dump($user);
    }

}