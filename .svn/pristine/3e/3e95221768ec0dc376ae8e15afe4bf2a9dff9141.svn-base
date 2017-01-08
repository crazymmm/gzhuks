<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Home;
use Zend\Session\Container;
class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function onBootstrap($e)
    {
        //设置静态适配器
        $sm = $e->getApplication()->getServiceManager();
        $adapter = $sm->get('Zend\Db\Adapter\Adapter');
        \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);
    
        $app = $e->getTarget();
        $app->getEventManager()->attach('dispatch',array($this,'rendercommon'),1000);
    }
    
    /**
     * 访问限制
     * @param unknown $e
     */
    public function rendercommon($e)
    {
    
        $app = $e->getTarget();
        $sm = $app->getServiceManager();
        $adaper = $sm->get('Zend\Db\Adapter\Adapter');
    
        $routeMatch = $e->getRouteMatch();
        $module = $routeMatch->getParam('module');
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $needAccess = true;

        if($userId !="" ||
            $userId != null ||
            $controller == "Home\Controller\IndexController" ||
            "/index/login" == $_SERVER['REQUEST_URI'] ||
            "/index/register" == $_SERVER['REQUEST_URI'] ||
            "/admin/index/register" == $_SERVER['REQUEST_URI'] ||
            "/index/passage" == $_SERVER['REQUEST_URI'] ||
            "/index/course" == $_SERVER['REQUEST_URI'] ||
            "/admin/index/couclassify" == $_SERVER['REQUEST_URI'] ||
            "/admin/index/getCourese" == $_SERVER['REQUEST_URI'] ||
            "/admin/index/getArticle" == $_SERVER['REQUEST_URI'] ||
            '/' == $_SERVER['REQUEST_URI']){
                $needAccess = false;
        }
    
        if($needAccess){
            header("X-Frame-Options: deny");
            header("X-XSS-Protection: 0");
            header("Location: http://" . $_SERVER['HTTP_HOST']);
            exit;
        }
         
    }
}
