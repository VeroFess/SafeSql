<?php
/**
 * Created by PhpStorm.
 * User: VeroFess
 * Date: 2018/1/26
 * Time: 21:22
 */

namespace Binklac\Basic\SQLExecuter;

class SQLExecuter{
    private static $TheInstance;
    private static $dbh;

    private function __construct(){}

    public function __clone(){
        trigger_error('Binklac Web Framework 4: Clone is not allowed.',E_USER_ERROR);
    }

    static public function Instance(){
        if(!isset(self::$TheInstance)){
            self::$TheInstance = new self();
        }
        return self::$TheInstance;
    }

    public function SetupConnection($server,$database,$username,$password){
        try{
            self::$dbh = new PDO("mysql:host=" . $server . ";dbname=" . $database . ";charset=utf8", $username, $password);
            if(!self::$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE)){
                return array("Status"=>"STATUS_EXCEPTION_COON_DB", "Data"=>"");
            }
        }catch(PDOException $e){
            return array("Status"=>"STATUS_EXCEPTION_COON_DB", "Data"=>"");
        }
        return array("Status"=>"STATUS_SUCCESS", "Data"=>"");
    }

    public function Select($Table, $Queries, $Target = "*"){
        if(!(is_string($Table) || is_array($Queries) || is_string($Target))){
            return array("Status"=>"STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data"=>"");
        }

        if(!(count($Queries) > 0)) {
            return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
        }

        $SQLCommand = "SELECT " . self::$dbh->quote($Target) . " FROM " . self::$dbh->quote($Table) . " WHERE ";

        $QueriesCount = count($Queries);
        $QueryDataArray = array();

        foreach($Queries as $EachQueryName=>$EachQueryData){
            $SQLCommand = $SQLCommand . self::$dbh->quote($EachQueryName) . " =  ?";
            array_push($QueryDataArray, self::$dbh->quote($EachQueryData));
            if(--$QueriesCount){
                $SQLCommand = $SQLCommand . " AND ";
            }
        }

        $sth = self::$dbh->prepare($SQLCommand);
        $sth->execute($QueryDataArray);

        if($sth->rowCount() > 0){
            return array("Status"=>"STATUS_SUCCESS", "Data"=>($sth->fetchAll(PDO::FETCH_ASSOC)));
        }
        return array("Status"=>"STATUS_SQL_SELECT_EXCEPTION", "Data"=>"");
    }

    public function Update($Table, $Queries, $Targets = FALSE){
        if(!(is_string($Table) || is_array($Queries))){
            return array("Status"=>"STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data"=>"");
        }

        if(!(count($Queries) > 0)) {
            return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
        }

        if(!($Targets === FALSE)){
            if(!((count($Targets) > 0) || is_array($Targets))) {
                return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
            }
        }



        $SQLCommand = "UPDATE " . self::$dbh->quote($Table) . " SET ";

        $QueriesCount = count($Queries);
        $QueryDataArray = array();

        foreach($Queries as $EachQueryName=>$EachQueryData){
            $SQLCommand = $SQLCommand . self::$dbh->quote($EachQueryName) . " =  ?";
            array_push($QueryDataArray, self::$dbh->quote($EachQueryData));
            if(--$QueriesCount){
                $SQLCommand = $SQLCommand . " , ";
            }
        }

        if(!($Targets === FALSE)){
            $SQLCommand =  $SQLCommand . " WHERE ";
            $TargetsCount = count($Targets);

            foreach($Targets as $EachTargetName=>$EachTargetData){
                $SQLCommand =  $SQLCommand . self::$dbh->quote($EachTargetName) . " = ?";
                array_push($QueryDataArray, self::$dbh->quote($EachTargetData));
                if(--$TargetsCount){
                    $SQLCommand = $SQLCommand . " AND ";
                }
            }
        }

        $sth = self::$dbh->prepare($SQLCommand);
        $sth->execute($QueryDataArray);

        if($sth->rowCount() > 0){
            return array("Status"=>"STATUS_SUCCESS", "Data"=>"");
        }
        return array("Status"=>"STATUS_SQL_SELECT_EXCEPTION", "Data"=>"");
    }

    public function Delete($Table, $Targets){
        if(!(is_string($Table) || is_array($Targets))){
            return array("Status"=>"STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data"=>"");
        }

        if(!(count($Targets) > 0)) {
            return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
        }

        $SQLCommand = "DELETE FROM " . self::$dbh->quote($Table) . " WHERE ";
        $TargetsCount = count($Targets);
        $QueryDataArray = array();

        foreach($Targets as $EachTargetName=>$EachTargetData){
            $SQLCommand =  $SQLCommand . self::$dbh->quote($EachTargetName) . " = ?";
            array_push($QueryDataArray, self::$dbh->quote($EachTargetData));
            if(--$TargetsCount){
                $SQLCommand = $SQLCommand . " AND ";
            }
        }

        $sth = self::$dbh->prepare($SQLCommand);
        $sth->execute($QueryDataArray);
        if($sth->rowCount() > 0){
            return array("Status"=>"STATUS_SUCCESS", "Data"=>"");
        }
        return array("Status"=>"STATUS_SQL_DELETE_EXCEPTION", "Data"=>"");
    }

    public function Insert($Table, $Targets){
        if(!(is_string($Table) || is_array($Targets))){
            return array("Status"=>"STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data"=>"");
        }

        if(!(count($Targets) > 0)) {
            return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
        }

        $SQLCommand = "INSERT INTO " . self::$dbh->quote($Table) . " ( ";
        $TargetsCount = count($Targets);
        $QueryDataArray = array();

        foreach($Targets as $EachTargetName=>$EachTargetData){
            $SQLCommand =  $SQLCommand . self::$dbh->quote($EachTargetName);
            array_push($QueryDataArray, self::$dbh->quote($EachTargetData));
            if(--$TargetsCount){
                $SQLCommand = $SQLCommand . " , ";
            }
        }

        $SQLCommand = $SQLCommand . " ) VALUES (";

        for($i = count($Targets); $i > 0 ;){
            $SQLCommand = $SQLCommand . " ?";
            if(--$i){
                $SQLCommand = $SQLCommand . " ,";
            }
        }

        $SQLCommand = $SQLCommand . " )";
        
        $sth = self::$dbh->prepare($SQLCommand);
        $sth->execute($QueryDataArray);
        if($sth->rowCount() > 0){
            return array("Status"=>"STATUS_SUCCESS", "Data"=>"");
        }
        return array("Status"=>"STATUS_SQL_DELETE_EXCEPTION", "Data"=>"");
    }
}
