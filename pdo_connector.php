<?php
namespace CandyWrappers;

class PDO_Connector {
    
    protected $pdo;
    
    public function __construct(string $host, protected string $type, ?string $user = null, ?string $pass = null, ?string $dbname = null, ?int $port = null) {
        $connection_str = match($this->type) {
            'sqlsrv' => ":Server=$host".(isset($port) ? ",$port" : '').(isset($dbname) ? ";Database=$dbname" : ''),
            'odbc' => ":$host",
            default => ":host=$host".(isset($port) ? ";port=$port" : '').(isset($dbname) ? ";dbname=$dbname" : '')
        };
        if ($type == 'odbc') {
            putenv("ODBCINI=/etc/odbc.ini");
            putenv("ODBCINST=/etc/odbcinst.ini");
        }
        $this->pdo = new \PDO($type.$connection_str, $user, $pass);
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