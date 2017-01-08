<?php

namespace Model\Base;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class BaseTableModel
{
    protected $adapter = null;
    protected $ServiceLocator = null;

    public function __construct(Adapter $adapter, $table)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    /**
     * @param string $sql
     * @param array $sqlArg sql里面对应的参数
     * @return 如果是SELECT会返回数组，如果INSERT会返回插入完的生成的主键，update/delete返回TRUE/FALSE和MSG
     */
    protected function sqlexec($sql, $sqlArg = array())
    {
       
        try {
			/*数组与字符串转换*/
        	$newArg=array();
        	foreach ($sqlArg as $argKey => $argVal){
        		if(is_array($argVal)){
        			//如果是数组，默认是IN,并对SQL进行替换
        			$valString=implode("','", $argVal);
                    $sql=str_replace("= :$argKey", "in ('".$valString."')",$sql);
        		}else{
        			$newArg[$argKey]=$argVal;
        		}
        	}
        	 
            //echo $sql ;
            //替换/R/T/N
            $sql=str_replace(array("\r\n", "\r", "\n","\t"), " ", $sql);
            //检查冒号与数组数量的对应关系
            if (strstr($sql, 'select') || strstr($sql, 'SELECT'))
            {
            	if(substr_count($sql,':')>count($newArg))
            	{
            		return  array("result"=>false,"msg"=>"SQLEXEC:执行查询操作时，参数sqlArg数组内参数要多于SQL语句参数".$sql);//传入的sqlArg数组的参数数量应该多于SQL语句参数
            	}
            	
            }
            else
            {
            	if(substr_count($sql,':')!=count($newArg))
            	{
            		return  array("result"=>false,"msg"=>"SQLEXEC:执行插入、删除、更新操作时，参数sqlArg数组与SQL语句参数要一致，不能多也不能少".$sql);
            	}
            }
            
            $statement = $this->adapter->createStatement($sql);
            
//             var_dump($statement->getSql());
            
            $result = $statement->execute($newArg);
            preg_match_all('/([a-zA-Z]+)/',$sql,$match);
           	$sql_str_6 =  strtolower($match[0][0]);
            if ($sql_str_6 == 'insert') {
                return array('result' => true, 'msg' => $result->getGeneratedValue());
            }
            
            elseif ($sql_str_6 == 'delete') {
            	return array('result' => true, 'msg' => $result->getAffectedRows());
            }
            
            elseif ($sql_str_6 == 'update') {
            	return array('result' => true, 'msg' => $result->getAffectedRows());
            }

            elseif ($sql_str_6 == 'select') {
                $resultSet = new ResultSet;
                $resultSet->initialize($result);
                return array('result' => true, 'msg' => $resultSet->toArray());
            }
            
            elseif (strstr($sql, 'foreign_key_checks') || strstr($sql, 'FOREIGN_KEY_CHECKS')) {
            	return array('result' => true, 'msg' => '');
            }
            return array('result' => false, 'msg' => "sqlexec 执行出错，相应的SQL：" . $statement->getSql());
        } catch (\Exception $e) {
            return array("result" => false, "msg" => "SQLEXEC:异常" . $e->getMessage() . $sql);
        }
    }
    
    /**
     * 生成sql的部分语句
     * 主要用于判断传入参数是否为空，然后生成对应的语句,减少重复语句
     */
	public function generateSql($arrValue,$sqlkey,$key,$tableName)
	{
		$sql = "";
		if($arrValue=="%%")
		{
			$sql = "($tableName.$sqlkey is null or $tableName.$sqlkey like :$key) ";
		}
		else if($arrValue != null)
		{
			$sql = "($tableName.$sqlkey like :$key) ";
		}
		else
		{
			$sql = "($tableName.$sqlkey is null) ";
		}
		return $sql;
	}
}


