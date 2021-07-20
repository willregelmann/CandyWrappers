<?php
namespace CandyWrappers;

class Database_Connector {
    
    protected $pdo, $type;
    
    public function __construct(string $host, object $resource) {
        $this->type = $resource->type ?? 'mysql';
        $connection_str = match($this->type) {
            'sqlsrv' => ":Server=$host".(isset($resource->port) ? ",$resource->port" : '').(isset($resource->dbname) ? ";Database=$resource->dbname" : ''),
            'odbc' => ":$host",
            default => ":host=$host".(isset($resource->port) ? ";port=$resource->port" : '').(isset($resource->dbname) ? ";dbname=$resource->dbname" : '')
        };
        if ($resource->type == 'odbc') {
            putenv("ODBCINI=/etc/odbc.ini");
            putenv("ODBCINST=/etc/odbcinst.ini");
        }
        $this->pdo = new \PDO($this->type.$connection_str, $resource->user, $resource->pass);
    }
    
    public function __destruct() {
        $this->pdo = null;
    }
    
    public function query(string $query, ...$params):array|bool {
        try {
            $sth = $this->pdo->prepare($query);
            $sth->execute($params);
            return $sth->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    public function update($query, ...$params):bool {
        try {
            $sth = $this->pdo->prepare($query);
            $sth->execute($params);
            return $sth->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    public function last_insert_id(){
        return $this->pdo->lastInsertId();
    }
    
    public function error_info(){
        return $this->pdo->errorInfo();
    }
        
}