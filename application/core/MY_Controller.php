<?php
class MY_Controller extends  CI_Controller
{
    function __construct() 
    {
        parent::__construct();
        $this->load->library("session_ext");
        //$this->init_global();
    }
    function init_config() 
    {
        $CI = & get_instance();
        $this->load->model("model_config");
        $this->model_config->config_cache();
        $CI->app_config->siteurl = getsiteurl();
    }
    function init_global() 
    {
        $CI = & get_instance();
        $mtime = explode(' ', microtime());
        $CI->app_global->timestamp = $mtime[1];
        $CI->app_global->supe_starttime = $CI->app_global->timestamp + $mtime[0];
    }

}