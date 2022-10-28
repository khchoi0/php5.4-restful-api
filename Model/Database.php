<?php

error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);

class Database
{
    protected $connection = null;
 
    public function __construct()
    {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE_NAME);
         
            if ( mysqli_connect_errno()) {
                throw new Exception("Could not connect to database.");   
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());   
        }           
    }
 
    public function select($query = "" , $params = array())
    {
        try {
            $stmt = $this->executeStatement($query , $params);
            $result = array();
            while(true)
            {
                $fields = $this->bindAll($stmt);
                $row = $this->fetchRowAssoc($stmt, $fields);
                if ($row) {
                    array_push($result, $row);
                } else {
                    break;
                }
            }
            $stmt->close();
 
            return $result;
        } catch(Exception $e) {
            throw New Exception( $e->getMessage() );
        }
        return false;
    }
 
    private function executeStatement($query = "" , $params = array())
    {
        try {
            $stmt = $this->connection->prepare( $query );
 
            if($stmt === false) {
                throw New Exception("Unable to do prepared statement: " . $query);
            }
 
            if( $params ) {
                $stmt->bind_param($params[0], $params[1]);
            }
 
            $stmt->execute();
 
            return $stmt;
        } catch(Exception $e) {
            throw New Exception( $e->getMessage() );
        }   
    }

    private function bindAll($stmt) 
    {
        $meta = $stmt->result_metadata();
        $fields = array();
        $fieldRefs = array();
        
        while ($field = $meta->fetch_field())
        {
            $fields[$field->name] = "";
            $fieldRefs[] = &$fields[$field->name];
        }

        call_user_func_array(array($stmt, 'bind_result'), $fieldRefs);
        $stmt->store_result();
    
        return $fields;
    }

    private function fetchRowAssoc($stmt, &$fields) {
        if ($stmt->fetch()) {
            return $fields;
        }
        return false;
    }
}
