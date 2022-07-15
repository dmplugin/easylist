<?php
/**
 * @package EasyList
 */
namespace EasyList;

use Exception;
use PDO;
use PDOException;
use EasyList\Exceptions\EasyListException;

class ListConnection
{
    public $host;
    public $username;
    public $password;
    public $database;
    public $protocol;
    public $conn;
    public $port;
    public $dsn_service;
    public $dsn_protocol;
    public $ids_server;
    public $connString;
    
    public function __construct(){
        
        $path = realpath(dirname(__FILE__)) . '/../config/EasyListConfig.php';
        require($path);
        
        if(isset($configPath)){
            if(file_exists($configPath)){
                require($configPath);
            } else {
                throw new EasyListException("Please configure DB at '{$configPath}'. Refer sample file at plugin's 'config' folder.");
            }
            
            $this->host        = $db['host'];
            $this->username    = $db['username'];
            $this->password    = $db['password'];
            $this->database    = $db['database'];
            $this->protocol    = trim(strtoupper($db['protocol']));
            $this->port        = (!empty($db['port'])) ? $db['port'] : null;
            $this->dsn_service = (!empty($db['dsn_service'])) ? $db['dsn_service'] : null;
            $this->dsn_protocol= (!empty($db['dsn_protocol'])) ? $db['dsn_protocol'] : null;
            $this->ids_server  = (!empty($db['ids_server'])) ? $db['ids_server'] : null;
            $this->connString  = trim($db['connection_string']);
        } else {
            throw new EasyListException("Configuration file 'config/EasyListConfig.php' is missing in EasyList root directory");
        }
    }
    
    public function setConnection(){
        
        try {

            if($this->connString != ""){
                $this->conn = new PDO ($this->connString,"$this->username","$this->password");
            } else {           
                switch($this->protocol){
                    case 'MYSQL':
                        $this->conn = new PDO("mysql:host=$this->host;dbname=$this->database", $this->username, $this->password);
                        break;
                    case 'SQLSRV':
                        //$this->conn = new PDO( "sqlsrv:Server=$this->host;Database=$this->database", $this->username, $this->password);
                        $this->conn = new PDO ("dblib:host=$this->host:$this->port;dbname=$this->database","$this->username","$this->password"); 
                        break;
                    case 'ORACLE':
                        $this->conn = new PDO( "oci:dbname=$this->database", $this->username, $this->password);//newly added
                        break;
                    case 'POSTGRESQL':
                        $this->conn = new PDO("pgsql:host=$this->host;dbname=$this->database", $this->username, $this->password);// newly added
                        break;
                    case 'SYBASE':
                        $this->conn = new PDO ("dblib:host=$this->host:$this->port;dbname=$this->database","$this->username","$this->password"); // newly added
                        //port = 10060;
                        break;
                    case 'INFORMIX':
                        $this->conn = new PDO("informix:host=$this->host; service=$this->dsn_service;database=$this->database; server=$this->ids_server; protocol=$this->dsn_protocol;EnableScrollableCursors=1", "$this->username ", "$this->password"); // newly added
                        break;
                    default:
                        $this->conn = null;
                        break;
                }
            }
            
            // set the PDO error mode to exception
            if($this->conn){
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $this->conn;
            }else{
                throw new EasyListException("Unable to connect due to wrong credentials");
            }
            
        } catch(PDOException $e) {
            throw new EasyListException("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getProtocol(){
        return $this->protocol;
    }
    
    
}
