<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dao
 * @author luxilang
 */
class MY_Model extends CI_Model
{
    var $id = null;
    var $data = array();
    var $_table;
    var $primaryKey = 'id';
    var $fields = array();
    var $__insertID = null;
    var $__numRows = null;
    var $__affectedRows = null;
    var $returnArray = TRUE;
    var $debug = FALSE;
    var $db_str = "";
	var $queries = array();
    function __construct()
    {
        parent::__construct();
        log_message('debug', "Extended Model Class Initialized");
    }
	
    function loadTable($table,$db='',$fields = array())
    {
        if ($this->debug) log_message('debug', "Loading model table: $table");
 		
        $this->_table  = $table;
        $this->db_str = $db;

		if (!empty($db))
		{
			$this->db = $this->load->database($db, TRUE, TRUE);
		}
		else
		{
			
			if (empty($this->db))
			{
				$this->load->database($db);
			}
			$CI =& get_instance();
			if (isset($CI->db))
			{
				unset($this->db);
				$this->db = clone $CI->db;
			}
			else
			{
				$CI->load->language('db');
				show_error(lang('db_unable_to_connect'));
			}
		}

        //$this->fields = (!empty($fields)) ? $fields : $this->db->list_fields($table);
        if ($this->debug)
        {
            log_message('debug', "Successfully Loaded model table: $table");
        }

    }
    function findAllIn($name, $names_arr)
    {
        $this->db->where_in($name, $names_arr);//WHERE username IN ('Frank', 'Todd', 'James')
        $query = $this->db->get($this->_table);
        $this->__numRows = $query->num_rows();
    	if ($this->debug)
		{
			$this->queries[] = $this->db->last_query();
		}
        return ($this->returnArray) ? $query->result_array() : $query->result();
    }
    
    function findAll($conditions = NULL, $fields = '*', $order = NULL, $start = 0, $limit = NULL,$group = NULL,$like=NULL)
    {
        if ($conditions != NULL)
        {
            if(is_array($conditions))
            {
                $this->db->where($conditions);
            }
            else
            {
                $this->db->where($conditions, NULL, FALSE);
            }
        }
        if ($like != NULL)
        {
            if(is_array($like))
            {
                $this->db->like($like);
            }
        }
        if ($fields != NULL)
        {
            $this->db->select($fields);
        }
 
        if ($order != NULL)
        {
            $this->db->orderby($order);
        }
 
        if ($limit != NULL)
        {
            $this->db->limit($limit, $start);
        }
 		if ($group != NULL) 
 		{
            $this->db->group_by($group);
 		}
 		
        $query = $this->db->get($this->_table);
        $this->__numRows = $query->num_rows();
        
    	if ($this->debug)
		{
			$this->queries[] = $this->db->last_query();
		}
        
        return ($this->returnArray) ? $query->result_array() : $query->result();
    }
 
    function find($conditions = NULL, $fields = '*', $order = NULL)
    {
        $data = $this->findAll($conditions, $fields, $order, 0, 1);
 
        if ($data)
        {
            return $data[0];
        }
        else
        {
            return false;
        }
    }
 
    function field($conditions = null, $name, $fields = '*', $order = NULL)
    {
        $data = $this->findAll($conditions, $fields, $order, 0, 1);
 
        if ($data)
        {
            $row = $data[0];
 
            if (isset($row[$name]))
            {
                return $row[$name];
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
 
    }
 
    function findCount($conditions = null,$like = NULL)
    {
        $data = $this->findAll($conditions, 'COUNT(*) AS count', null, 0, 1,NULL,$like);
 
        if ($data)
        {
            return $data[0]['count'];
        }
        else
        {
            return false;
        }
    }
	/**
	 * Returns a key value pair array from database matching given conditions.
	 *
	 * Example use: generateList(null, '', 0. 10, 'id', 'username');
	 * Returns: array('10' => 'emran', '11' => 'hasan')
	 *
	 * @author md emran hasan <emran@rightbrainsolution.com>
	 * @return array a list of key val ue pairs given criteria
	 * @access public
	 */	
    function generateList($conditions = null, $order = 'id ASC', $start = 0, $limit = NULL, $key = null, $value = null)
    {
        $data = $this->findAll($conditions, "$key, $value", $order, $start, $limit);
 
        if ($data)
        {
            foreach ($data as $row)
            {
                $keys[] = ($this->returnArray) ? $row[$key] : $row->$key;
                $vals[] = ($this->returnArray) ? $row[$value] : $row->$value;
            }
 
            if (!empty($keys) && !empty($vals))
            {
                $return = array_combine($keys, $vals);
                return $return;
            }
        }
        else
        {
            return false;
        }
    }
 
	/**
	 * Returns an array of the values of a specific column from database matching given conditions.
	 *
	 * Example use: generateSingleArray(null, 'name');
	 *
	 * @author md emran hasan <emran@rightbrainsolution.com>
	 * @return array a list of key value pairs given criteria
	 * @access public
	 */	
    function generateSingleArray($conditions = null, $field = null, $order = 'id ASC', $start = 0, $limit = NULL)
    {
        $data = $this->findAll($conditions, "$field", $order, $start, $limit);
 
        if ($data)
        {
            foreach ($data as $row)
            {
                $arr[] = ($this->returnArray) ? $row[$field] : $row->$field;
            }
 
            return $arr;
        }
        else
        {
            return false;
        }
    }
 
 
    function create()
    {
        $this->id = false;
        unset ($this->data);
 
        $this->data = array();
        return true;
    }
 
    function read($id = null, $fields = null)
    {
        if ($id != null)
        {
            $this->id = $id;
        }
 
        $id = $this->id;
 
        if ($this->id !== null && $this->id !== false)
        {
            $this->data = $this->find($this->primaryKey . ' = ' . $id, $fields);
            return $this->data;
        }
        else
        {
            return false;
        }
    }
 
    /**
     * 
     * 插入
     * @param array $data
     * @param int $no_insert_id 是否返回插入id
     * @return boolean ***
     * @author luxilang
     */
    function insert($data = null,$no_insert_id = NULL)
    {
        if ($data == null)
        {
            return FALSE;
        }
 
        $this->data = $data;
        //$this->data['create_date'] = date("Y-m-d H:i:s");
        /* 
        foreach ($this->data as $key => $value)
        {
            if (array_search($key, $this->fields) === FALSE)
            {
                unset($this->data[$key]);
            }
        }*/
        
        $rs = $this->db->insert($this->_table, $this->data);

		if ($this->debug)
		{
			$this->queries[] = $this->db->last_query();
		}
		
 		$this->__insertID = $this->db->insert_id();
 		if (!empty($no_insert_id)) 
 		{
 			return $rs;
 		}
 		else
 		{
 			return $this->__insertID;
 		}
    }

	
    function save($data = null, $id = null)
    {
        if ($data)
        {
            $this->data = $data;
        }
        /*
        foreach ($this->data as $key => $value)
        {
            if (array_search($key, $this->fields) === FALSE)
            {
                unset($this->data[$key]);
            }
        }
    	*/
        if ($id != null)
        {
            $this->id = $id;
        }
 
        $id = $this->id;
 
        if ($this->id !== null && $this->id !== false)
        {
            $this->db->where($this->primaryKey, $id);
            $this->db->update($this->_table, $this->data);
        	if ($this->debug)
			{
				$this->queries[] = $this->db->last_query();
			}
            $this->__affectedRows = $this->db->affected_rows();
            return $this->id;
        }
        else
        {
            $this->db->insert($this->_table, $this->data);
        	if ($this->debug)
			{
				$this->queries[] = $this->db->last_query();
			}
            $this->__insertID = $this->db->insert_id();
            return $this->__insertID;
        }
    }
 
 
    function remove($id = null)
    {
        if ($id != null)
        {
            $this->id = $id;
        }
 
        $id = $this->id;
 
        if ($this->id !== null && $this->id !== false)
        {
            if ($this->db->delete($this->_table, array($this->primaryKey => $id)))
            {
                $this->id = null;
                $this->data = array();
            	if ($this->debug)
				{
					$this->queries[] = $this->db->last_query();
				}
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
 	
 
    function query($sql)
    {
		$ret = $this->db->query($sql);
		
		if ($this->debug)
		{
			$this->queries[] = $this->db->last_query();
		}
		
		return $ret;
    }
 
    function lastQuery()
    {
        return $this->db->last_query();
    }
	function debugQueries() 
	{
		$queries = array_reverse($this->queries);
		return $queries;
	}	
 
    function insertString($data)
    {
        return $this->db->insert_string($this->_table, $data);
    }
 
 
    function getID()
    {
        return $this->id;
    }
 
 
    function getInsertID()
    {
        return $this->__insertID;
    }
 
    function getNumRows()
    {
        return $this->__numRows;
    }
 
    function getAffectedRows()
    {
        return $this->db->affected_rows();
    }
 	
	function del($id)
	{
		if (is_array($id)) 
		{
			$ListId_Str = implode(',',$id);
			if (!empty($this->db_str)) 
			{
			    $d = $this->db_str.'.';
			}
			else
			{
			    $d = '';
			    
			}
			$sql = "DELETE FROM  {$d}{$this->_table} WHERE $this->primaryKey in ({$ListId_Str})" ;
			$rs = $this->query($sql);
			if ($this->debug)
    		{
    			$this->queries[] = $this->db->last_query();
    		}
		}
		else
		{
			$rs = $this->remove($id); 
		}
		return $rs;
	}
	function remove_ci($arr)
	{
		$rs = $this->db->delete($this->_table, $arr);
		if ($this->debug)
		{
			$this->queries[] = $this->db->last_query();
		}
		return $rs;
	}

	/**
	 * 
	 * @param array $insertkey 插入key字段
	 * @param array $inserts
	 * 						$insertkey = "province,city,county";
	 * 						foreach ($rs_insert as $k=>$v) 
                            {
                                  $inserts[$k] =  "('{$v['province']}','{$v['city']}','{$v['county']}')";
                            }
	 * 						$inserts//根据实际情况变化数据格式
	 */
	function insert_much_no_repeat($table,$insertkey,$inserts,$insert_no_repeat_key) 
	{
	    $inserts_val= implode(',', $inserts);
        $insertkey_arr = explode(',',$insert_no_repeat_key);
    	foreach ($insertkey_arr as $v) 
    	{
			$insertkey_str_[] = "{$v}=VALUES({$v})"; 
		}
		$insertkey_str_val = implode(',',$insertkey_str_);
		$insert_sql = "insert into $table ($insertkey) values {$inserts_val} ON DUPLICATE KEY UPDATE {$insertkey_str_val}";
        
		$ret = $this->db->query($insert_sql);
		
		if ($this->debug)
		{
			$this->queries[] = $this->db->last_query();
		}
		return $ret;
	}
	/**
	 * 插入多条记录
	 * @param array $insertkey 插入key
	 * @param array $inserts 插入数据 构造插入数据 
	 * 						$insertkey = "province,city,county";
	 * 						foreach ($rs_insert as $k=>$v) 
                            {
                                  $inserts[$k] =  "('{$v['province']}','{$v['city']}','{$v['county']}')";
                            }
	 * 						$inserts//根据实际情况变化数据格式
	 * @param bool $replace 是替换还是插入
	 */
	function insert_replace_much($insertkey,$inserts,$replace = false)
	{
	    $method = $replace?'REPLACE':'INSERT';
		$sql = $method." INTO ".$this->_table."($insertkey) VALUES ".implode(',', $inserts);

		$ret = $this->db->query($sql);
		
		if ($this->debug)
		{
			$this->queries[] = $this->db->last_query();
		}
		
		return $ret;

	}
}

?>