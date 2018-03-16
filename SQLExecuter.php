<?php
/**
 * Created by PhpStorm.
 * User: VeroFess
 * Date: 2018/1/26
 * Time: 21:22
 */
namespace Binklac\Basic\SQLExecuter;

use Binklac\Basic\Helper as Helper;

/*
 * About Helper
 *
    static public function IS_SUCCESS($Status) {
        if (!is_array($Status)) {
            return FALSE;
        } elseif (!isset($Status['Status'])){
            return FALSE;
        } elseif (!($Status['Status'] === 'STATUS_SUCCESS')){
            return FALSE;
        } else {
            return TRUE;
        }
    }

    static public function GET_STATUS_DATA_OR_NULL($Status){
        if (!is_array($Status)) {
            return '';
        } elseif (!isset($Status['Status'])){
            return '';
        } elseif (!isset($Status['Data'])){
            return '';
        } elseif (!($Status['Status'] === 'STATUS_SUCCESS')){
            return '';
        } else {
            return $Status['Data'];
        }
    }
 * */

class SQLExecuter{
    const DATA_TYPE_INT_VAL = 1;
    const DATA_TYPE_FLOAT_VAL = 2;
    const DATA_TYPE_BOOL_VAL = 3;
    const DATA_TYPE_STRING_VAL = 4;

    private static $TheInstance;
    private static $IsInit;
    private static $EnableDebug;
    private static $dbh;

    private function __construct(){
        self::$IsInit = FALSE;
        self::$EnableDebug = FALSE;
    }

    public function __clone(){
        trigger_error('Binklac Web Framework 4: Clone is not allowed.',E_USER_ERROR);
    }

    static public function Instance(){
        if(!isset(self::$TheInstance)){
            self::$TheInstance = new self();
        }
        return self::$TheInstance;
    }

    public function SQL_PREPARE_DATA($Name, $Data, $DataType){
        if(!self::$IsInit){
            return array("Status"=>"STATUS_SQL_NEED_INIT", "Data"=>"");
        }

        //检查名称, 无条件信任 $Name 部分，自己做死谁都拦不住 = =
        if(!is_string($Name)){
            return array("Status"=>"STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data"=>"");
        }

        //如果是数字类型
        if ($DataType === self::DATA_TYPE_INT_VAL || $DataType === self::DATA_TYPE_FLOAT_VAL){
            $TrimedData = trim(trim($Data), " \f\n\r\t\v\0\x0B"); //部分版本默认没有 \f, 造成is_numeric绕过，清除两次确保清除干净
            if(is_numeric($TrimedData)){ //清除完了再判断是不是数字
                if($DataType === self::DATA_TYPE_FLOAT_VAL){ //浮点数，特殊处理
                    $CleanData = floatval($Data);//加上引号，防止奇葩错误
                    return array("Status"=>"STATUS_SUCCESS", "Data"=>(array("K"=>$Name, "V"=>$CleanData)));
                } else { //十六进制，八进制之类的进到这里处理
                    $CleanData = intval($Data);
                    return array("Status"=>"STATUS_SUCCESS", "Data"=>(array("K"=>$Name, "V"=>$CleanData)));
                }
            } else {
                return array("Status"=>"STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data"=>"");
            }
        }

        if ($DataType === self::DATA_TYPE_STRING_VAL) { //字符串类型
            return array("Status"=>"STATUS_SUCCESS", "Data"=>(array("K"=>$Name, "V"=>$Data)));
        }

        if ($DataType === self::DATA_TYPE_BOOL_VAL) { //布尔，直接强制转换
            $CleanData = $Data ? 1 : 0;
            return array("Status"=>"STATUS_SUCCESS", "Data"=>(array("K"=>$Name, "V"=>$CleanData)));
        }
        return array("Status"=>"STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data"=>"");
    }

    public function SQL_EASY_BUILD_DATA($SQLRowData){
        if(!self::$IsInit){
            return array("Status"=>"STATUS_SQL_NEED_INIT", "Data"=>"");
        }

        $SQLData = array();

        if(!(count($SQLRowData) > 0)) {
            return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
        }

        foreach ($SQLRowData as $EachRowData){
            if(Helper::IS_SUCCESS($EachRowData)){
                $SQLData[Helper::GET_STATUS_DATA_OR_NULL($EachRowData)["K"]] = Helper::GET_STATUS_DATA_OR_NULL($EachRowData)["V"];
            } else {
                return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
            }
        }

        return array("Status"=>"STATUS_SUCCESS", "Data"=>$SQLData);
    }

    public function SetupConnection($Server, $Database, $UserName, $Password, $EnableDebugMessage = FALSE){
        try{
            self::$dbh = new \PDO("mysql:host=" . $Server . ";dbname=" . $Database . ";charset=utf8", $UserName, $Password);
            if(!self::$dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, FALSE)){
                return array("Status"=>"STATUS_EXCEPTION_COON_DB", "Data"=>"");
            }
        }catch(\PDOException $e){
            return array("Status"=>"STATUS_EXCEPTION_COON_DB", "Data"=>"");
        }
        self::$EnableDebug = is_bool($EnableDebugMessage) ? $EnableDebugMessage : FALSE;
        self::$IsInit = TRUE;
        return array("Status"=>"STATUS_SUCCESS", "Data"=>"");
    }

    public function Select($Table, $QueriesData, $Target = "*"){
        if(!self::$IsInit){
            return array("Status"=>"STATUS_SQL_NEED_INIT", "Data"=>"");
        }

        if(!(is_string($Table) || is_string($Target))){
            return array("Status"=>"STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data"=>"");
        }

        if(!Helper::IS_SUCCESS($QueriesData)) {
            return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
        }

        $SQLCommand = "SELECT " . ($Target === "*" ? $Target : ("`" . $Target . "`")) . " FROM `" . $Table . "` WHERE ";

        $Queries = Helper::GET_STATUS_DATA_OR_NULL($QueriesData);
        $QueriesCount = count($Queries);
        $QueryDataArray = array();

        foreach($Queries as $EachQueryName=>$EachQueryData){
            $SQLCommand = $SQLCommand . $EachQueryName . " =  ?";
            array_push($QueryDataArray, $EachQueryData);
            if(--$QueriesCount){
                $SQLCommand = $SQLCommand . " AND ";
            }
        }

        $sth = self::$dbh->prepare($SQLCommand);
        $sth->execute($QueryDataArray);

        if($sth->rowCount() > 0){
            return array("Status"=>"STATUS_SUCCESS", "Data"=>($sth->fetchAll(\PDO::FETCH_ASSOC)));
        }
        return array("Status"=>"STATUS_SQL_SELECT_EXCEPTION", "Data"=>self::$EnableDebug ? $sth->errorInfo() : "");
    }

    public function Update($Table, $QueriesData, $Targets = FALSE){
        if(!self::$IsInit){
            return array("Status"=>"STATUS_SQL_NEED_INIT", "Data"=>"");
        }

        if(!is_string($Table)){
            return array("Status"=>"STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data"=>"");
        }

        if(!Helper::IS_SUCCESS($QueriesData)) {
            return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
        }

        if(!($Targets === FALSE)){
            if(!((count($Targets) > 0) || is_array($Targets))) {
                return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
            }
        }



        $SQLCommand = "UPDATE " . $Table . " SET ";
        $Queries = Helper::GET_STATUS_DATA_OR_NULL($QueriesData);
        $QueriesCount = count($Queries);
        $QueryDataArray = array();

        foreach($Queries as $EachQueryName=>$EachQueryData){
            $SQLCommand = $SQLCommand . $EachQueryName . " =  ?";
            array_push($QueryDataArray, $EachQueryData);
            if(--$QueriesCount){
                $SQLCommand = $SQLCommand . " , ";
            }
        }

        if(!($Targets === FALSE)){
            $SQLCommand =  $SQLCommand . " WHERE ";
            $TargetsCount = count($Targets);

            foreach($Targets as $EachTargetName=>$EachTargetData){
                $SQLCommand =  $SQLCommand . $EachTargetName . " = ?";
                array_push($QueryDataArray, $EachTargetData);
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
        return array("Status"=>"STATUS_SQL_SELECT_EXCEPTION", "Data"=>self::$EnableDebug ? $sth->errorInfo() : "");
    }

    public function Delete($Table, $TargetsData){
        if(!self::$IsInit){
            return array("Status"=>"STATUS_SQL_NEED_INIT", "Data"=>"");
        }

        if(!is_string($Table)){
            return array("Status"=>"STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data"=>"");
        }

        if(!Helper::IS_SUCCESS($TargetsData)) {
            return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
        }

        $SQLCommand = "DELETE FROM " . $Table . " WHERE ";
        $Targets = Helper::GET_STATUS_DATA_OR_NULL($TargetsData);
        $TargetsCount = count($Targets);
        $QueryDataArray = array();

        foreach($Targets as $EachTargetName=>$EachTargetData){
            $SQLCommand =  $SQLCommand . $EachTargetName . " = ?";
            array_push($QueryDataArray, $EachTargetData);
            if(--$TargetsCount){
                $SQLCommand = $SQLCommand . " AND ";
            }
        }

        $sth = self::$dbh->prepare($SQLCommand);
        $sth->execute($QueryDataArray);
        if($sth->rowCount() > 0){
            return array("Status"=>"STATUS_SUCCESS", "Data"=>"");
        }
        return array("Status"=>"STATUS_SQL_DELETE_EXCEPTION", "Data"=>self::$EnableDebug ? $sth->errorInfo() : "");
    }

    public function Insert($Table, $TargetsData){
        if(!self::$IsInit){
            return array("Status"=>"STATUS_SQL_NEED_INIT", "Data"=>"");
        }

        if(!is_string($Table)){
            return array("Status"=>"STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data"=>"");
        }

        if(!Helper::IS_SUCCESS($TargetsData)) {
            return array("Status" => "STATUS_SQL_EXCEPTION_INVALID_ARGUMENT", "Data" => "");
        }

        $SQLCommand = "INSERT INTO `" . $Table . "` ( ";
        $Targets = Helper::GET_STATUS_DATA_OR_NULL($TargetsData);
        $TargetsCount = count($Targets);
        $QueryDataArray = array();

        foreach($Targets as $EachTargetName=>$EachTargetData){
            $SQLCommand =  $SQLCommand . $EachTargetName;
            array_push($QueryDataArray, $EachTargetData);
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

        $SQLCommand = $SQLCommand . " );";

        $sth = self::$dbh->prepare($SQLCommand);
        $sth->execute($QueryDataArray);
        if($sth->rowCount() > 0){
            return array("Status"=>"STATUS_SUCCESS", "Data"=>"");
        }
        return array("Status"=>"STATUS_SQL_INSERT_EXCEPTION", "Data"=>self::$EnableDebug ? $sth->errorInfo() : "");
    }
}
