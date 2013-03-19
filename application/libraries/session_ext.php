<?php
class session_ext
{ 
    function __construct()
    {
        $this->lifeTime = 10;
        $this->time = time();
        ini_set("session.gc_divisor", 1);
        
        //$domain = '.infor96.com';
        //不使用 GET/POST 变量方式
        ini_set('session.use_trans_sid',    1);
        //设置垃圾回收最大生存时间
        ini_set('session.gc_maxlifetime',   $this->lifeTime);
        //使用 COOKIE 保存 SESSION ID 的方式
        ini_set('session.use_cookies',      1);
        ini_set('session.cookie_path',      '/');
        //多主机共享保存 SESSION ID 的 COOKIE
        //ini_set('session.cookie_domain',    $domain);
        session_module_name('user'); 
        session_set_save_handler(array(&$this,"open"), 
                                 array(&$this,"close"), 
                                 array(&$this,"read"), 
                                 array(&$this,"write"), 
                                 array(&$this,"destroy"),
                                 array(&$this,"gc"));  
        session_cache_limiter('private, must-revalidate');                         
        session_start(); 
    }

   function open($savePath, $sessName) { 
       $CI = & get_instance();
       $db = $CI->db;
       $this->_db_ = &$db;
       
       return true; 
   } 
   function close() { 
       //$this->gc(); 
       return true; 
   } 
   function read($sessID) { 
        
        $res = $this->_db_->query("SELECT session_data AS d FROM ws_sessions 
                           WHERE session_id = '$sessID' 
                           AND session_expires > ".$this->time); 
        $data= $res->result_array();
        return $data ? $data[0]['d'] : '';

   }

   function write($sessID,$sessData) 
   { 
       $new_time = $this->time + $this->lifeTime ;
       $sql = "REPLACE INTO ws_sessions VALUES ('$sessID', '$new_time', '$sessData')"; 
       $this->_db_->query($sql); 
       return true;
      
   } 
   function destroy($sessID) {

       $this->_db_->query("DELETE FROM ws_sessions WHERE session_id = '$sessID'");  
       return true; 
   } 
   function gc($maxlifetime = null) { 

       $this->_db_->query("DELETE FROM ws_sessions WHERE session_expires < ".$this->time);
       $this->_db_->query("OPTIMIZE TABLE ws_sessions ");
       return true; 
   } 

}