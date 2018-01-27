# SafeSql

Example Here
 `````
require_once "BasicClasses/Helper.php";
require_once "BasicClasses/SQLExecuter.php";

use Binklac\Basic\SQLExecuter\SQLExecuter as SQLExecuter;
use Binklac\Basic\Helper as Helper;

$Status = array("Status"=>"STATUS_NO_ACTION", "Data"=>"");
$Status = SQLExecuter::Instance()->SetupConnection(
    "localhost",
    "somedb",
    "root",
    "********",
    TRUE
);

if(Helper::IS_SUCCESS($Status)){
    $Status = SQLExecuter::Instance()->Insert("buginfo", SQLExecuter::Instance()->SQL_EASY_BUILD_DATA(array(
        SQLExecuter::Instance()->SQL_PREPARE_DATA("modid", "TEST", SQLExecuter::DATA_TYPE_STRING_VAL),
        SQLExecuter::Instance()->SQL_PREPARE_DATA("title", "TEST", SQLExecuter::DATA_TYPE_STRING_VAL),
        SQLExecuter::Instance()->SQL_PREPARE_DATA("data", "TEST", SQLExecuter::DATA_TYPE_STRING_VAL),
        SQLExecuter::Instance()->SQL_PREPARE_DATA("status", 1, SQLExecuter::DATA_TYPE_INT_VAL),
        SQLExecuter::Instance()->SQL_PREPARE_DATA("createtime", time(), SQLExecuter::DATA_TYPE_INT_VAL)
    )));
}

if(Helper::IS_SUCCESS($Status)){
    $Status = SQLExecuter::Instance()->Update("buginfo", SQLExecuter::Instance()->SQL_EASY_BUILD_DATA(array(
        SQLExecuter::Instance()->SQL_PREPARE_DATA("modid", "TEST2", SQLExecuter::DATA_TYPE_STRING_VAL)
    )));
}

if(Helper::IS_SUCCESS($Status)){
    $Status = SQLExecuter::Instance()->Select("buginfo", SQLExecuter::Instance()->SQL_EASY_BUILD_DATA(array(
        SQLExecuter::Instance()->SQL_PREPARE_DATA("modid", "TEST2", SQLExecuter::DATA_TYPE_STRING_VAL)
    )));

    var_dump($Status);
    /*
     *  OutPut
         array (size=2)
          'Status' => string 'STATUS_SUCCESS' (length=14)
          'Data' => 
            array (size=1)
              0 => 
                array (size=5)
                  'modid' => string ''TEST2'' (length=7)
                  'title' => string ''TEST'' (length=6)
                  'data' => string ''TEST'' (length=6)
                  'status' => int 1
                  'createtime' => int 1517097228
     */
}

if(Helper::IS_SUCCESS($Status)){
    $Status = SQLExecuter::Instance()->Delete("buginfo", SQLExecuter::Instance()->SQL_EASY_BUILD_DATA(array(
        SQLExecuter::Instance()->SQL_PREPARE_DATA("modid", "TEST2", SQLExecuter::DATA_TYPE_STRING_VAL)
    )));
}

`````
