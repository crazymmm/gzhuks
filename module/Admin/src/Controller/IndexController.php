<?php
namespace Admin\Controller;

use Zend\Db\Adapter\Adapter;
use Model\Base\BaseController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use Admin\Model\User;
use Zend\Crypt\Password\Bcrypt;
use phpDocumentor\Reflection\Types\This;

class IndexController extends BaseController
{
    const ROUTE_INDEX  = 'admin/index';
    
    /**
     * 后台主页左侧数据
     * {@inheritDoc}
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        try {
            $user_session = new Container('user');
            $userId = $user_session->userId;
            if(!isset($userId)){
                return new JsonModel(array("result"=>false,'msg'=>"请先登录"));
            }
            /*获取用户头像等信息*/
            $userModel = new User($this->getDbAdapter());
            $userRet = $userModel->selectUserById($userId);
            if(!$userRet['result'] || count($userRet['msg']) == 0){
                return new JsonModel(array("result"=>false,'msg'=>"用户不存在"));
            }
            
            return array(
                'user'=>substr($userRet["msg"][0]['username'], 0, strpos($userRet["msg"][0]['username'], '@')),
                'role'=>$userRet["msg"][0]['flag'] == 1 ?"管理员":"普通用户",
                'flag'=>$userRet["msg"][0]['flag'],
                'headpic' =>isset($userRet["msg"][0]['headpic'])?$userRet["msg"][0]['headpic']:"/data/avatarResource/common/default.jpg"
            );
            
        }catch (\Exception $e) {
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/500.html");
            $response->setStatusCode(302);
            return $response;//重定向到500页面
        }
    }
    
    public function homeAction(){
        try {
            $userModel = new User($this->getDbAdapter());
            $picret = $userModel->getFrontPic();
            if(!$picret['result']){
                $response = $this->getResponse();
                $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
                $response->setStatusCode(302);
                return $response;//重定向到404页面
            }
            $view = new ViewModel(array('pic'=>$picret['msg']));
            return $view;
            
        }catch (\Exception $e) {
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/500.html");
            $response->setStatusCode(302);
            return $response;//重定向到500页面
        }
    }
    
    
    /**
     * 注册界面
     * @return \Zend\View\Model\ViewModel
     */
    public function registerAction()
    {
        /*查看用户是否登录*/
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $userName = $user_session->userName;
        if(isset($userId)){
            header("X-Frame-Options: deny");  
            header("X-XSS-Protection: 0");  
            header("Location: http://" . $_SERVER['HTTP_HOST']);
            exit;
        }
        else{
            /* 实例化模型 */
            $view = new ViewModel();
            return $view;
        }
    }
    public function logoutAction(){
        try {
            $user_session = new Container('user');
            $user_session->getManager()->getStorage()->clear('user');
            unset($_SESSION['user']);
            header("X-Frame-Options: deny");
            header("X-XSS-Protection: 0");
            header("Location: http://" . $_SERVER['HTTP_HOST']);
            exit;
        } catch (\Exception $e) {
            return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
        }
    }
    /**
     * 课程列表
     * @return \Zend\View\Model\ViewModel|\Zend\View\Model\JsonModel
     */
    public function coursemgrAction(){
        try {
            return new ViewModel();
        } catch (\Exception $e) {
            return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
        }
    }
    
    /**
     * 首页轮播
     * @return \Zend\View\Model\ViewModel|\Zend\View\Model\JsonModel
     */
    public function frontpicAction(){
        try {
            return new ViewModel();
        } catch (\Exception $e) {
            return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
        } 
    }
    
    /**
     * 网站设置
     */
    public function settingAction(){
        try {
            $userModel = new User($this->getDbAdapter());
            $ret = $userModel->getWebSetting();
            if(!$ret['result']){
                $response = $this->getResponse();
                $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
                $response->setStatusCode(302);
                return $response;//重定向到404页面
            }
            if(count($ret['msg']) == 0){
                $ret['msg'][0] = array(
                    'web_logo'=>"/logo.jpg",
                    "web_title"=>"课程设计",
                    "web_keyword"=>"",
                    "web_keycontent"=>"",
                    "web_num"=>""
                );
            }
            return new ViewModel(array("common"=>$ret['msg'][0] ));
        } catch (\Exception $e) {
            return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
        }
    }
    
  /**
  * 查看课程列表
  */
   public function checkcourseAction(){
       try {
          $user_session = new Container('user');
          $userId = $user_session->userId;
          $email = $user_session->userId;
          $user = new User($this->getDbAdapter());
          $userRet = $user->selectAuthority($userId);
          if($userRet['result']){
              if(count($userRet['msg']) == 0){
                  return new JsonModel(array('result'=>false,'msg'=>" 用户不存在"));
              }
              if($userRet['msg'][0]["flag"]==1){
                  $courseRet = $user->selectCourse('all');
              }else{
                  $courseRet = $user->selectCourse($userId);
              }
              if(!$courseRet['result']){
                  return new JsonModel(array('result'=>false,'msg'=>"操作失败,请刷新页面重试ERR000"));
              }
              $courseRet['total'] = count($courseRet['msg']);
              foreach ($courseRet['msg'] as $key=> $value){
                  $courseRet['msg'][$key]['check'] = false;
              }
              $userRet = $user->selectAllCourseCl();
              if(!$userRet['result']){
                  return new JsonModel(array('result'=>false,'msg'=>"操作失败,请刷新页面重试ERR001"));
              }
              $courseRet['coucl'] = $userRet['msg'];
              return new JsonModel($courseRet);
          }else{
              return new JsonModel(array("result" => false, "msg" =>"获取数据操作失败，请刷新页面重试"));
          }
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }
       
   }
/**
 * 删除课程
 * @return \Zend\View\Model\JsonModel
 */
   public function deleteCourseinfoAction(){

       try {
           $user_session = new Container('user');
           $userId = $user_session->userId;
           $user = new User($this->getDbAdapter());
           $userRet = $user->selectUserById($userId);
           if($userRet['result']){
               if(count($userRet['msg']) == 0){
                   return new JsonModel(array('result'=>false,'msg'=>" 用户不存在"));
               }
               $userAuthority = $user->selectAuthority($userId);
               if($userAuthority['result']){
               if($userAuthority['msg'][0]['flag']== 0){
                   return new JsonModel(array('result'=>false,'msg'=>"普通用户不能删除课程"));
               }
               else{
                   $c_id=$_POST['cou_c_id'];
                   $courseRet = $user->deleteCouInfo($c_id);
                   if($courseRet['result']){
                       return new JsonModel(array('result'=>true,'msg'=>"删除操作成功"));
                   }
                   else{
                       return new JsonModel(array("result" => false, "msg" =>"获取数据操作失败，请刷新页面重试"));
                   }
                   
               }
               }
               }
               else{
                   return new JsonModel(array("result" => false, "msg" =>"获取数据操作失败，请刷新页面重试"));
               }
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }
        
   }
    
   /**
    * 创建课程
    * @return \Zend\View\Model\JsonModel|\Zend\Stdlib\ResponseInterface
    */
   public function createcourseAction(){
       try {
           if($this->getRequest()->isPost()){
               $user_session = new Container('user');
               $userId = $user_session->userId;
               if(!isset($userId)){
                   return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
               }
           
               /*检查标题,关键字,icp等信息*/
               $request = $this->getRequest();
               $title = $request->getPost('name');
               $time = $request->getPost('time');
               $keycontent = $request->getPost('keycontent');
               $type = $request->getPost('type');
               $arr = array(
                   "title" => $title,
                   "time" => $time,
                   "keycontent" => $keycontent,
                   "type"=>$type,
                   "username"=>$userId
               );
               $userModel = new User($this->getDbAdapter());
               /*将信息插入数据库中*/
               $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
               $ret = $userModel->saveCouInfo($arr);
               if(!$ret['result']){
                   $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                   return new JsonModel(array("result" => false,"msg" => "操作失败:请刷新页面重试ERR001"));
               }
               $arr['id'] = $ret['msg'];
               
               /*检查图像信息*/
               /*保存路径*/
               $savePath =  '/data/courseResource';
               /* 判断目录是否存在，不存在则创建 */
               if (! is_dir($_SERVER['DOCUMENT_ROOT'] . $savePath)) {
                   mkdir($_SERVER['DOCUMENT_ROOT'] . $savePath, 0755, true); // 第三个参数为true即可以创建多极目录
               }
               /*保存logo*/
               if(!empty($_FILES['logofile']['tmp_name'])){
           
                   if ($_FILES['logofile']['size'] > 4194304) {
                       $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                       return new JsonModel(array("result"=>false,'msg'=>"上传的文件体积超过了限制 最大不能超过4M"));
                   }
           
                   $_FILES['logofile']['extension'] = substr(strrchr(strtolower(strrchr($_FILES['logofile']['name'], '.')), '.'), 1);
           
                   if (in_array($_FILES['logofile']['extension'], array('jpg', 'jpeg', 'bmp', 'png', 'swf'))) {
                       $info = getimagesize($_FILES['logofile']['tmp_name']);
                       if (false === $info || ('gif' == strtolower($_FILES['logofile']['extension']) && empty($info['bits']))) {
                           $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                           return new JsonModel(array("result"=>false,'msg'=>"不支持的图像文件ERR001"));
                       }
                   }else{
                       $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                       return new JsonModel(array("result"=>false,'msg'=>"上传的文件格式不正确 仅支持 jpg，bmp，png"));
                   }
           
                   /* 文件名*/
                   $logoname = $_SERVER['DOCUMENT_ROOT'].$savePath . "/" .md5($arr['id']) . "." . $_FILES['logofile']['extension'];
                   /*进行图片保存*/
                   if(!move_uploaded_file($_FILES['logofile']['tmp_name'], $logoname)){
                       /* 保存文件不成功 */
                       $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                       return new JsonModel (array("result" => false, "msg" => "保存图片失败ERR001"));
                   }
                   if(!file_exists($logoname)) {
                       /* 文件不存在 */
                       $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                       return new JsonModel (array("result" => false, "msg" => "保存图片失败ERR002"));
                   }
                   $logosize = $this->resizeImg($logoname, md5($arr['id']).".");
                   $arr['photo'] = $savePath . "/s_" .md5($arr['id']) ."." . $_FILES['logofile']['extension'];
               }else{
                   $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                    return new JsonModel(array("result" => false,"msg" => "操作失败:请选择课程图片"));
               }
               $ret = $userModel->updateCouInfo($arr);
               if(!$ret['result']){
                   $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                   return new JsonModel(array("result" => false,"msg" => "操作失败:请刷新页面重试ERR002"));
               }
               $this->getDbAdapter()->getDriver()->getConnection()->commit();
               return new JsonModel(array("result" => true,"msg" => "创建成功"));
           }else{
               $response = $this->getResponse();
               $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
               $response->setStatusCode(302);
               return $response;//重定向到500页面
           }
           
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }
   }
   /**
    * 修改课程信息
    * @return \Zend\View\Model\JsonModel|\Zend\Stdlib\ResponseInterface
    */
   public function updatecourseAction(){
       try {
           if($this->getRequest()->isPost()){
               $user_session = new Container('user');
               $userId = $user_session->userId;
               if(!isset($userId)){
                   return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
               }
                
               $request = $this->getRequest();
               $title = $request->getPost('edit_name');
               $time = $request->getPost('edit_time');
               $keycontent = $request->getPost('edit_keycontent');
               $type = $request->getPost('edit_type');
               $c_id = $request->getPost('id');
    
               $arr = array(
                   "title" => $title,
                   "time" => $time,
                   "keycontent" => $keycontent,
                   "type"=>$type,
                   "username"=>$userId,
                   "id"=>$c_id
            //       'photo'=>$photo
               );
               $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
               $userModel = new User($this->getDbAdapter());
           
               
               /*检查图像信息*/
               /*保存路径*/
               $savePath =  '/data/courseResource';
               /* 判断目录是否存在，不存在则创建 */
               if (! is_dir($_SERVER['DOCUMENT_ROOT'] . $savePath)) {
                   mkdir($_SERVER['DOCUMENT_ROOT'] . $savePath, 0755, true); // 第三个参数为true即可以创建多极目录
               }
               /*保存logo*/
               if(!empty($_FILES['edit_logofile']['tmp_name'])){
           
                   if ($_FILES['edit_logofile']['size'] > 4194304) {
                       $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                       return new JsonModel(array("result"=>false,'msg'=>"上传的文件体积超过了限制 最大不能超过4M"));
                   }
           
                   $_FILES['edit_logofile']['extension'] = substr(strrchr(strtolower(strrchr($_FILES['edit_logofile']['name'], '.')), '.'), 1);
           
                   if (in_array($_FILES['edit_logofile']['extension'], array('jpg', 'jpeg', 'bmp', 'png', 'swf'))) {
                       $info = getimagesize($_FILES['edit_logofile']['tmp_name']);
                       if (false === $info || ('gif' == strtolower($_FILES['edit_logofile']['extension']) && empty($info['bits']))) {
                           $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                           return new JsonModel(array("result"=>false,'msg'=>"不支持的图像文件ERR001"));
                       }
                   }else{
                       $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                       return new JsonModel(array("result"=>false,'msg'=>"上传的文件格式不正确 仅支持 jpg，bmp，png"));
                   }
           
                   /* 文件名*/
                   $logoname = $_SERVER['DOCUMENT_ROOT'].$savePath . "/" .md5($arr['id']) . "." . $_FILES['edit_logofile']['extension'];
                   /*进行图片保存*/
                   if(!move_uploaded_file($_FILES['edit_logofile']['tmp_name'], $logoname)){
                       /* 保存文件不成功 */
                       $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                       return new JsonModel (array("result" => false, "msg" => "保存图片失败ERR001"));
                   }
                   if(!file_exists($logoname)) {
                       /* 文件不存在 */
                       $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                       return new JsonModel (array("result" => false, "msg" => "保存图片失败ERR002"));
                   }
                   $logosize = $this->resizeImg($logoname, md5($arr['id']).".");
                   $arr['photo'] = $savePath . "/s_" .md5($arr['id']) ."." . $_FILES['edit_logofile']['extension'];
               }else{
                   $courseModel=new User($this->getDbAdapter());
                   $ret=$courseModel->selectCouPhoto($c_id);
                   if(!empty($ret['msg'][0]['photo'])){
                       $arr['photo']=$ret['msg'][0]['photo'];
                   }
                   else{
                   $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                    return new JsonModel(array("result" => false,"msg" => "操作失败:请选择课程图片"));
                   }
               }
               $ret = $userModel->updateCouInfo($arr);
               if(!$ret['result']){
                   $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                   return new JsonModel(array("result" => false,"msg" => "操作失败:请刷新页面重试ERR002"));
               }
               $this->getDbAdapter()->getDriver()->getConnection()->commit();
               return new JsonModel(array("result" => true,"msg" => "修改成功"));
           }else{
               $response = $this->getResponse();
               $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
               $response->setStatusCode(302);
               return $response;//重定向到500页面
           }
            
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }
       
   }
   
   /**
    * 获取课程分类
    * @return \Zend\View\Model\JsonModel|\Zend\View\Model\ViewModel
    */
   public function couclassifyAction(){
       try {
           /*获取一级课程分类*/
           $userModel = new User($this->getDbAdapter());
           $userRet = $userModel->selectCourseCl("");
           if(!$userRet['result']){
               return new JsonModel(array('result'=>false,'msg'=>"操作失败,请刷新页面重试ERR001"));
           }
           foreach ($userRet['msg'] as $key=>$value){
               $userRet['msg'][$key]['color'] = "f3f3f4";
               $userRet['msg'][$key]['textcolor'] = "#676a6c";
              $_ret =  $userModel->selectCourseCl($value['cl_id']);
              if(!$_ret['result']){
                  return new JsonModel(array('result'=>false,'msg'=>"操作失败,请刷新页面重试ERR002"));
              }
              foreach ($_ret['msg'] as $k=>$v){
                  $_ret['msg'][$k]['color'] = "#f3f3f4";
                  $_ret['msg'][$k]['textcolor'] = "#676a6c";
              }
              $userRet['msg'][$key]['childnode'] = $_ret['msg'];
              
           }
           if($this->getRequest()->isPost()){
               return new JsonModel(array('result'=>true,'msg'=>$userRet['msg']));
           }
           return new ViewModel(array('msg'=>$userRet['msg']));
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }
   }
   
   
   /**
    * 获取课程列表
    * @return \Zend\View\Model\JsonModel|\Zend\Stdlib\ResponseInterface
    */
   public function getCoureseAction(){
       try {

           if($this->getRequest()->isPost()){
               $row = $this->getRequest()->getPost('row');
               $col = $this->getRequest()->getPost('col');
               $userModel = new User($this->getDbAdapter());
               $userRet = $userModel->selectallcourse($row,$col);
               if(!$userRet['result']){
                   return new JsonModel($userRet);
               }
               $user_session = new Container('user');
               $userId = $user_session->userId;
               if ($userId==null||$userId==""){
                  foreach ($userRet['msg'] as $K=>$V){
                      $userRet['msg'][$K]['isCheck'] = false;
                  }
               }else{
                   foreach ($userRet['msg'] as $K=>$V){
                       $ret = $userModel->selectmycourseishava($userId,$V['c_id']);
                       if(!$ret['result']){
                           return new JsonModel($ret);
                       }
                       $userRet['msg'][$K]['isCheck'] = count($ret['msg']) != 0;
                   }
               }
               return new JsonModel($userRet);
           }else{
               $response = $this->getResponse();
               $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
               $response->setStatusCode(302);
               return $response;//重定向到500页面
           }
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       } 
   }
   
   /**
    * 新建课程分类
    */
   public function addcouclassifyAction(){
          try {
           $user_session = new Container('user');
           $userId = $user_session->userId;
           if(!isset($userId)){
               return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
           }
           $name = $this->getRequest()->getPost('name');
           if(!isset($name) || $name == ""){
               return new JsonModel(array('result'=>false,'msg'=>"请输入名称"));
           }
           if(!$this->checkMain("name", $name)){
               return new JsonModel(array('result'=>false,'msg'=>"名称含有不支持字符"));
           }
           /*获取一级课程分类*/
           $userModel = new User($this->getDbAdapter());
           $checkRet = $userModel->checkClexist($name);
           if(!$checkRet['result']){
               return new JsonModel(array('result'=>false,'msg'=>"操作失败,刷新页面重试ERR001".$checkRet['msg']));
           }
           if(0 != count($checkRet['msg'])){
               return new JsonModel(array('result'=>false,'msg'=>"操作失败,该名称已被占用"));
           }
           $addRet = $userModel->addCl($name);
           if(!$addRet['result']){
               return new JsonModel(array('result'=>false,'msg'=>"操作失败,刷新页面重试ERR002"));
           }
           return new JsonModel(array('result'=>true,'msg'=>"添加成功"));
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }
   }
   
   /**
    * 保存列表
    */
   public function saveCllistAction(){
       try {
           $user_session = new Container('user');
           $userId = $user_session->userId;
           if(!isset($userId)){
               return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
           }
           $data = $this->getRequest()->getPost('data');
           if(!isset($data) || !is_array($data)){
               return new JsonModel(array('result'=>false,'msg'=>'操作失败,请刷新页面重试ERR001'));
           }
           $userModel = new User($this->getDbAdapter());
           $dataArr = [];
           $l = 0;
           /*组合数据*/
           for($i=0; $i<count($data); $i++){
               $dataArr[$l] = [];
               $dataArr[$l]['parent'] = null;
               $dataArr[$l]['children'] = $data[$i]['id'];
               $l++;
               if(isset($data[$i]["children"])){
                   for($j=0; $j<count($data[$i]["children"]); $j++){
                       $dataArr[$l] = [];
                       $dataArr[$l]['parent'] = $data[$i]['id'];
                       $dataArr[$l]['children'] = $data[$i]["children"][$j]["id"];
                       $l++;
                       if(isset($data[$i]["children"][$j]["children"])){
                           for($k=0; $k<count($data[$i]["children"][$j]["children"]); $k++){
                               $dataArr[$l] = [];
                               $dataArr[$l]['parent'] = $data[$i]["children"][$j]["id"];
                               $dataArr[$l]['children'] = $data[$i]["children"][$j]["children"][$k]["id"];
                               $l++;
                           }
                       }
                   }
               }
           }
           $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
           foreach ($dataArr as $key => $value){
               $_ret = $userModel->saveClList($value);
               if(!$_ret['result']){
                   $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                   return new JsonModel(array('result'=>false,'msg'=>'操作失败,请刷新页面重试ERR002'));
               }
           }
           $this->getDbAdapter()->getDriver()->getConnection()->commit();
           return new JsonModel(array('result'=>true,'msg'=>'保存成功'));
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       } 
   }
   
   /**
    * 保存网址配置
    * @return \Zend\View\Model\JsonModel|\Zend\Stdlib\ResponseInterface
    */
   public function saveWebSettingAction(){
      try {
          $user_session = new Container('user');
          $userId = $user_session->userId;
          if(!isset($userId)){
              return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
          }
          if($this->getRequest()->isPost()){
              $userModel = new User($this->getDbAdapter());
          
              $webModel = new User($this->getDbAdapter());
              $commonArr = $webModel->getWebSetting();
              if(!$commonArr['result']){
                  return new JsonModel(array('result'=>false,'msg'=>"获取配置失败"));
              }
              if(count($commonArr['msg']) == 0){
                  $commonArr['msg'][0] = array(
                      'web_logo'=>"/logo.jpg",
                      "web_title"=>"课程设计",
                      "web_keyword"=>"",
                      "web_keycontent"=>"",
                      "web_num"=>""
                  );
              }
          
              /*先检查之前的logo 和icon 是否有误*/
              if(isset($commonArr['msg'][0]['web_logo'])){
                  if(file_exists($_SERVER['DOCUMENT_ROOT'].$commonArr['msg'][0]['web_logo'])){
                      $info = getimagesize($_SERVER['DOCUMENT_ROOT'].$commonArr['msg'][0]['web_logo']);
                      if (false === $info) {
                          $commonArr['msg'][0]['web_logo'] = "";
                      }
                  }else{
                      $commonArr['msg'][0]['web_logo'] = "";
                  }
              }
          
              /*检查标题,关键字,icp等信息*/
              $request = $this->getRequest();
              $title = $request->getPost('webtitle');
              $keyword = $request->getPost('webkeyword');
              $keycontent = $request->getPost('webkeycontent');
              $icpnum = $request->getPost('webicpnum');
          
              if(!isset($title) || $title == "" || !$this->checkMain("name", $title)){
                  return new jsonModel(array('result'=>false,'msg'=>"网站标题不能为空,且不能包含特殊字符"));
              }
          
              if(mb_strlen($title, 'utf-8') > 15 || mb_strlen($title, 'utf-8') < 1){
                  return new JsonModel(array("result"=>false,'msg'=>"标题在1到15字符以内"));
              }
          
              if(mb_strlen($keyword, 'utf-8') > 100){
                  return new JsonModel(array("result"=>false,'msg'=>"关键字在100字符以内"));
              }
          
              if(mb_strlen($keycontent, 'utf-8') > 100){
                  return new JsonModel(array("result"=>false,'msg'=>"描述在100字符以内"));
              }
          
              if(empty($_FILES['logofile']['tmp_name']) && (!isset($commonArr['msg'][0]['web_logo']) || $commonArr['msg'][0]['web_logo'] == "")){
                  return new JsonModel(array("result"=>false,'msg'=>"请补充logo信息"));
              }
          
          
              $arr = array(
                  "title" => $title,
                  "keyword" => $keyword,
                  "keycontent" => $keycontent,
                  "icpnum" => $icpnum
              );
          
              /*检查图像信息*/
              /*保存网站logo,icon的路径*/
              $savePath =  '/data/webResource';
              /* 判断目录是否存在，不存在则创建 */
              if (! is_dir($_SERVER['DOCUMENT_ROOT'] . $savePath)) {
                  mkdir($_SERVER['DOCUMENT_ROOT'] . $savePath, 0755, true); // 第三个参数为true即可以创建多极目录
              }
              /*保存logo*/
              if(!empty($_FILES['logofile']['tmp_name'])){
          
                  if ($_FILES['logofile']['size'] > 4194304) {
                      return new JsonModel(array("result"=>false,'msg'=>"上传的logo文件体积超过了限制 最大不能超过4M"));
                  }
          
                  $_FILES['logofile']['extension'] = substr(strrchr(strtolower(strrchr($_FILES['logofile']['name'], '.')), '.'), 1);
          
                  if (in_array($_FILES['logofile']['extension'], array('jpg', 'jpeg', 'bmp', 'png', 'swf'))) {
                      $info = getimagesize($_FILES['logofile']['tmp_name']);
                      if (false === $info || ('gif' == strtolower($_FILES['logofile']['extension']) && empty($info['bits']))) {
                          return new JsonModel(array("result"=>false,'msg'=>"不支持的图像文件ERR001"));
                      }
                  }else{
                      return new JsonModel(array("result"=>false,'msg'=>"上传的logo文件格式不正确 仅支持 jpg，bmp，png"));
                  }
          
                  /* 文件名*/
                  $logoname = $_SERVER['DOCUMENT_ROOT'].$savePath . "/" . "logo." . $_FILES['logofile']['extension'];
                  /*进行图片保存*/
                  if(!move_uploaded_file($_FILES['logofile']['tmp_name'], $logoname)){
                      /* 保存文件不成功 */
                      return new JsonModel (array("result" => false, "msg" => "保存网站logo失败ERR001"));
                  }
                  if(!file_exists($logoname)) {
                      /* 文件不存在 */
                      return new JsonModel (array("result" => false, "msg" => "保存网站logo失败ERR002"));
                  }
                  $logosize = $this->resizeImg($logoname, "logo.");
                  $arr['logo'] = $savePath . "/s_"  . "logo." . $_FILES['logofile']['extension'];
              }else{
                  $arr["logo"] = !isset($commonArr['msg'][0]['web_logo']) || $commonArr['msg'][0]['web_logo'] == ""?"/logo.jpg":$commonArr['msg'][0]['web_logo'];
              }
          
              /*将信息更新到网站基础设置表中*/
              $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
              $ret = $webModel->saveWebSetting($arr);
              if(!$ret['result']){
                  $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                  return new JsonModel(array("result" => false,"msg" => "操作失败:请刷新页面重试ERR002"));
              }
              $this->getDbAdapter()->getDriver()->getConnection()->commit();
              return new JsonModel(array('result'=>true,'msg'=>"保存成功"));
          }else{
              $response = $this->getResponse();
              $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
              $response->setStatusCode(302);
              return $response;//重定向到404页面
          }
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }   
   }

   /**
    *  获取轮播图片
    * @return \Zend\View\Model\JsonModel|\Zend\Stdlib\ResponseInterface
    */
   public function getfrontpicAction(){
       try {
           
           if($this->getRequest()->isPost()){
               $user_session = new Container('user');
               $userId = $user_session->userId;
               if(!isset($userId)){
                   return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
               }
               $userModel = new User($this->getDbAdapter());
               $picRet = $userModel->getFrontPic();
               if(!$picRet){
                   return new JsonModel(array('result'=>false,'msg'=>'操作失败,请刷新页面重试ERR001'));
               }
               return new JsonModel($picRet);
           }else{
               $response = $this->getResponse();
               $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
               $response->setStatusCode(302);
               return $response;//重定向到404页面
           }
           
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }  
   }
   
   /**
    * 增加前台图片
    * @return \Zend\View\Model\JsonModel|\Zend\Stdlib\ResponseInterface
    */
   public function addfrontpicAction(){
       try {
           if($this->getRequest()->isPost()){
               $user_session = new Container('user');
               $userId = $user_session->userId;
               if(!isset($userId)){
                   return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
               }

               /* 先判断是否为合法的图片类型 */
               if ($_FILES['avatarfile']['size'] > 4194304) {
                   return new JsonModel(array("result"=>false,'msg'=>"上传的头像文件体积超过了限制 最大不能超过4M"));
               }
               $_FILES['avatarfile']['extension'] = substr(strrchr(strtolower(strrchr($_FILES['avatarfile']['name'], '.')), '.'), 1);
               if (in_array($_FILES['avatarfile']['extension'], array('gif', 'jpg', 'jpeg', 'bmp', 'png'))) {
                   $info = getimagesize($_FILES['avatarfile']['tmp_name']);
                   if (false === $info || ('gif' == strtolower($_FILES['avatarfile']['extension']) && empty($info['bits']))) {
                       return new JsonModel(array("result"=>false,'msg'=>"不支持的图像文件ERR001"));
                   }
               }else{
                   return new JsonModel(array("result"=>false,'msg'=>"不支持的图像文件ERR002"));
               }
               
               /*保存头像的路径*/
               $ava_filenamePath =  '/data/homeResource/image';
               /* 判断目录是否存在，不存在则创建 */
               if (! is_dir($_SERVER['DOCUMENT_ROOT'] . $ava_filenamePath)) {
                   mkdir($_SERVER['DOCUMENT_ROOT'] . $ava_filenamePath, 0755, true); // 第三个参数为true即可以创建多极目录
               }
               /* 文件名*/
               $timestamp = explode(" ", microtime());
               $timestamp = implode($timestamp);
               $filename = $ava_filenamePath . "/" . $timestamp. '.' . $_FILES['avatarfile']['extension'];
                
               if(!move_uploaded_file($_FILES['avatarfile']['tmp_name'],$_SERVER['DOCUMENT_ROOT'].$filename)){
                   /* 保存文件不成功 */
                   return new JsonModel (array("result" => false, "msg" => "保存失败"));
               }
               if(!file_exists($_SERVER['DOCUMENT_ROOT'].$filename)) {
                   /* 文件不存在 */
                   return new JsonModel (array("result" => false, "msg" => "文件不存在"));
               }
               $arr = array('url'=>$filename,'name'=>$_FILES['avatarfile']['name']);
               $userModel = new User($this->getDbAdapter());
               $picRet = $userModel->addFrontPic($arr);
               if(!$picRet){
                   return new JsonModel(array('result'=>false,'msg'=>'操作失败,请刷新页面重试ERR001'));
               }
               return new JsonModel(array('result'=>true,'msg'=>"添加成功"));
           }else{
               $response = $this->getResponse();
               $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
               $response->setStatusCode(302);
               return $response;//重定向到404页面
           }
       
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       } 
   }
   
   /**
    * 删除轮播图片
    * @return \Zend\View\Model\JsonModel|\Zend\Stdlib\ResponseInterface
    */
   public function deleteFrontpicAction(){
       try {
          if ($this->getRequest()->isPost()){
              $user_session = new Container('user');
              $userId = $user_session->userId;
              if(!isset($userId)){
                  return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
              }
              $id = $this->getRequest()->getPost('id');
              $userModel = new User($this->getDbAdapter());
              $delret = $userModel->deleteFrontpic($id);
              if(!$delret['result']){
                  return new JsonModel(array('result'=>false,'msg'=>"操作失败,请刷新页面重试ERR001"));
              }
              return new JsonModel(array('result'=>true,'msg'=>"删除成功"));
          }else{
               $response = $this->getResponse();
               $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
               $response->setStatusCode(302);
               return $response;//重定向到404页面
           }
           
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       } 
   }
   
   /**
    * 文章列表
    * @return \Zend\View\Model\ViewModel|\Zend\View\Model\JsonModel
    */
   public function articlemgrAction(){
       try {
         
           return new ViewModel();
           
        } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }
   }
   /**
    * 获取文章列表
    */
   public function getArticleAction(){
       try {
           if($this->getRequest()->isPost()){
               $id = $this->getRequest()->getPost('id');
               if(isset($id)){
                   if( $id != 1 && $id != 2 && $id != 0){
                       return new JsonModel(array('result'=>false,'msg'=>"操作失败,请刷新页面重试ERR000"));
                   }
               }else{
                   $id = -1;
               }
               $userModel = new User($this->getDbAdapter());
               $ret = $userModel->getArticle($id);
               if(!$ret['result']){
                   return new JsonModel(array('result'=>false,'msg'=>"操作失败,请刷新页面重试ERR001"));
               }
               foreach ($ret['msg'] as $key=>$val){
                   $ret['msg'][$key]['check'] = false;
                   $ret['msg'][$key]['seocontent'] = mb_substr(strip_tags($ret['msg'][$key]['content']), 0,200,"utf-8");
                   $ret['msg'][$key]['keyarr'] = explode(",", $val['keyword']);
                   foreach ($ret['msg'][$key]['keyarr'] as $k =>$val){
                       if($val == ""){
                           array_splice($ret['msg'][$key]['keyarr'], $k, 1);
                       }
                   }
               }
               return new JsonModel($ret);
       
           }else{
               $response = $this->getResponse();
               $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
               $response->setStatusCode(302);
               return $response;//重定向到404页面
           }
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }
   }
   
   /**
    * 添加文章
    */
   public function createArticleAction(){
       try {
            if($this->getRequest()->isPost()){
                $user_session = new Container('user');
                $userId = $user_session->userId;
                if(!isset($userId)){
                    return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
                }

                $content = $this->getRequest()->getPost('content');
                $keyword = $this->getRequest()->getPost('keyword');
                $name = $this->getRequest()->getPost('name');
                $type = $this->getRequest()->getPost('type');
                $arr = array('title'=>$name,"content"=>$content,"type"=>$type,"keyword"=>$keyword,"user"=>$userId);
                $userModel = new User($this->getDbAdapter());
                $delret = $userModel->createArticle($arr);
                if(!$delret['result']){
                    return new JsonModel(array('result'=>false,'msg'=>"操作失败,请刷新页面重试ERR001"));
                }
                return new JsonModel(array('result'=>true,'msg'=>"增加成功"));
                
            }else{
                $response = $this->getResponse();
                $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
                $response->setStatusCode(302);
                return $response;//重定向到404页面
            }
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       } 
   }
   
   /**
    * 删除文章
    */
   public  function  deleteArticleAction(){
       try {
           if($this->getRequest()->isPost()){
               $user_session = new Container('user');
               $userId = $user_session->userId;
               if(!isset($userId)){
                   return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
               }
       
               $id = $this->getRequest()->getPost('id');
       
               $userModel = new User($this->getDbAdapter());
               $delret = $userModel->deleteArticle($id);
               if(!$delret['result']){
                   return new JsonModel(array('result'=>false,'msg'=>"操作失败,请刷新页面重试ERR001"));
               }
               return new JsonModel(array('result'=>true,'msg'=>"删除成功"));
       
           }else{
               $response = $this->getResponse();
               $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
               $response->setStatusCode(302);
               return $response;//重定向到404页面
           }
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }
   }
   
   /**
    * 编辑文章
    * @return \Zend\View\Model\JsonModel|\Zend\Stdlib\ResponseInterface
    */
   public function editArticleAction(){
       try {
           if($this->getRequest()->isPost()){
               $user_session = new Container('user');
               $userId = $user_session->userId;
               if(!isset($userId)){
                   return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
               }
       
               $content = $this->getRequest()->getPost('content');
               $keyword = $this->getRequest()->getPost('keyword');
               $name = $this->getRequest()->getPost('name');
               $type = $this->getRequest()->getPost('type');
               $id = $this->getRequest()->getPost('id');
               $arr = array('title'=>$name,"content"=>$content,"type"=>$type,"keyword"=>$keyword,"id"=>$id);
               $userModel = new User($this->getDbAdapter());
               $delret = $userModel->editArticle($arr);
               if(!$delret['result']){
                   return new JsonModel(array('result'=>false,'msg'=>"操作失败,请刷新页面重试ERR001"));
               }
               return new JsonModel(array('result'=>true,'msg'=>"增加成功"));
       
           }else{
               $response = $this->getResponse();
               $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
               $response->setStatusCode(302);
               return $response;//重定向到404页面
           }
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }
   }
   
   /**
    * 获取用户的已选课列表
    */
   public function getCoureseByUserAction(){
       try {
           if($this->getRequest()->isPost()){
               $user_session = new Container('user');
               $userId = $user_session->userId;
               if(!isset($userId)){
                   return new JsonModel(array("result"=>true,'msg'=>"请先登录"));
               }
                
               $userModel = new User($this->getDbAdapter());
               $delret = $userModel->selectmycourse($userId);
               return new JsonModel($delret);
                
           }else{
               $response = $this->getResponse();
               $response->getHeaders()->addHeaderLine('Location', "../../../../../html/common/404.html");
               $response->setStatusCode(302);
               return $response;//重定向到404页面
           }
       } catch (\Exception $e) {
           return new JsonModel (array("result" => false, "msg" =>"操作失败，请刷新页面重试"));
       }
   }
}