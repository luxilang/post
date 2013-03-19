<?php 
class root_cl extends  MY_Controller 
{
    function index() 
    {
        echo time().'---<br />';
        $_SESSION['不是吧']=array('luxilang','你好');
        print_r($_SESSION);
    }
    function luxilang() 
    {
        print_r($_SESSION);
        exit;
        $_SESSION = array();
        if(isset($_COOKIE[session_name()])){
            setcookie(session_name(),'',time()-3600);
        }
        //使用内置session_destroy()函数调用撤销会话
        session_destroy();

    }
}

?>