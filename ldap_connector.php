<?php
namespace CandyWrappers;

final class LDAP_Connector {
    
    private $ldap;
    
    public function __construct(string $host, protected string $base_dn, string $user, string $pass){
        $this->ldap = ldap_connect($host);
        ldap_bind($this->ldap, $user, $pass);
    }
    
    public function __destruct(){
        ldap_unbind($this->ldap);
    }
    
    public function search($filter, $OU = null):array {
        $dn = ($OU ? "OU=$OU," : '').$this->base_dn;
        $result = ldap_search($this->ldap, $dn, $filter);
        return ldap_get_entries($this->ldap, $result);
    }
    
    public function username2guid(string $username):string {
        $filter_string = sprintf('(sAMAccountName=%s)', $username);
        $ldap_info = $this->search($filter_string)[0];
        return bin2hex($ldap_info['objectguid'][0]);
    }
    
    public function guid2username(string $guid):string {
        $filter_string = sprintf('(objectGUID=%s)', $this->hex2guid($guid));
        $ldap_info = $this->search($filter_string)[0];
        return $ldap_info['samaccountname'][0];
    }
    
    public function getUserGroups(string $guid):array {
        $return = [];
        $ldap_info = $this->search(sprintf('(objectGUID=%s)', $this->hex2guid($guid)))[0];
        $filter_string = "(member:1.2.840.113556.1.4.1941:=$ldap_info[dn])";
        $groups = $this->search($filter_string);
        array_shift($groups);
        foreach ($groups as $group) {
            array_push($return, bin2hex($group['objectguid'][0]));
        }
        return $return;
    }
    
    protected function hex2guid($guid):string {
        $output = '';        
        for ($i = 0; $i <= strlen($guid)-2; $i = $i+2){
            $output .=  "\\".substr($guid, $i, 2);
        }
        return $output;
    }
    
}