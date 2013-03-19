<?php
class model_session
{ 
    var $lifeTime;  
    function __construct($db='')
    {
        session_module_name('user'); 
        session_set_save_handler(array(&$this,"open"), 
                                 array(&$this,"close"), 
                                 array(&$this,"read"), 
                                 array(&$this,"write"), 
                                 array(&$this,"destroy"),
                                 array(&$this,"gc")); 
        session_start(); 
    }

   function open($savePath, $sessName) { 
       
       $this->lifeTime = get_cfg_var("session.gc_maxlifetime");
       return true; 
   } 
   function close() { 
       $this->gc(ini_get('session.gc_maxlifetime')); 
       return true; 
   } 
   function read($sessID) { 
        $res = $this->db->query("SELECT session_data AS d FROM ws_sessions 
                           WHERE session_id = '$sessID' 
                           AND session_expires > ".time()); 
        $data= $res->result_array();
        if ($data)
        {
            return $data[0]['d'];
        }
        else
        {
            return ""; 
        }
   } 
   function write($sessID,$sessData) { 
       $newExp = time() + $this->lifeTime; 
       $res = $this->db->query("SELECT * FROM ws_sessions 
                           WHERE session_id = '$sessID'",$this->dbHandle); 
        
       if($res->num_rows()) { 
           $this->db->query("UPDATE ws_sessions 
                         SET session_expires = '$newExp', 
                         session_data = '$sessData' 
                         WHERE session_id = '$sessID'",$this->dbHandle); 

           if($this->db->affected_rows()) 
               return true; 
       }else { 

           $this->db->query("INSERT INTO ws_sessions ( 
                         session_id, 
                         session_expires, 
                         session_data) 
                         VALUES( 
                         '$sessID', 
                         '$newExp', 
                         '$sessData')",$this->dbHandle); 
           if($this->db->affected_rows()) 
               return true; 
       }  
       return false; 
   } 
   function destroy($sessID) { 
       $this->db->query("DELETE FROM ws_sessions WHERE session_id = '$sessID'",$this->dbHandle); 
       if($this->db->affected_rows()) 
           return true;  
       return false; 
   } 
   function gc($sessMaxLifeTime) { 
       $this->db->query("DELETE FROM ws_sessions WHERE session_expires < ".time(),$this->dbHandle); 
       return $this->db->affected_rows(); 
   } 
}