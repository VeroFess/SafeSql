<?php
class SqlBase {
    private $dbh;
	
    public function __construct($server,$database,$username,$password) {
		try{
			$this->dbh = new PDO("mysql:host=" . $server . ";dbname=" . $database . ";charset=utf8", $username, $password);
			$this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}catch(PDOException$e){
			die("Error!: ".$e->getMessage()."<br/>"); 
		}
	}
	
	public function SafeSelect($table,$querys,$target = "*"){
		$sql = "SELECT " . $target . " FROM " . $table . " WHERE ";
		$n_query = count($querys);$arr_query = array();
		foreach($querys as $query){
			$sql = $sql . $query[0] . " =  ?";
			array_push($arr_query,$query[1]);
			if(--$n_query){
				$sql = $sql . " AND ";
			}
		}
		$sth = $this->dbh->prepare($sql);
		$sth->execute($arr_query);
		if($sth->rowCount() > 0){
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		}
		return false;
	}
	
	public function SafeUpdate($table,$querys,$targets){
		$sql = "UPDATE " . $table . " SET ";
		$n_query = count($querys);$arr_query = array();
		foreach($querys as $query){
			$sql = $sql . $query[0] . " =  ?";
			array_push($arr_query,$query[1]);
			if(--$n_query){
				$sql = $sql . " , ";
			}
		}
		$sql =  $sql . " WHERE ";
		$n_target = count($targets);
		foreach($targets as $target){
			$sql =  $sql . $target[0] . " = ?";
			array_push($arr_query,$target[1]);
			if(--$n_target){
				$sql = $sql . " AND ";
			}
		}
		$sth = $this->dbh->prepare($sql);
		$sth->execute($arr_query);
		if($sth->rowCount() > 0){
			return true;
		}
		return false;
	}
	
	public function SafeDelete($table,$targets){
		$sql = "DELETE FROM " . $table . " WHERE ";
		$n_target = count($targets);$arr_query = array();
		foreach($targets as $target){
			$sql =  $sql . $target[0] . " = ?";
			array_push($arr_query,$target[1]);
			if(--$n_target){
				$sql = $sql . " AND ";
			}
		}
		$sth = $this->dbh->prepare($sql);
		$sth->execute($arr_query);
		if($sth->rowCount() > 0){
			return true;
		}
		return false;
	}
	
	public function SafeInsert($table,$targets){
		$sql = "INSERT INTO " . $table . " ( ";
		$n_target = count($targets);$arr_query = array();
		foreach($targets as $target){
			$sql =  $sql . $target[0];
			array_push($arr_query,$target[1]);
			if(--$n_target){
				$sql = $sql . " , ";
			}
		}
		$sql = $sql . " ) VALUES (";
		for($i = count($targets);$i > 0;){
			$sql = $sql . " ?";
			if(--$i){
				$sql = $sql . " ,";
			}
		}
		$sql = $sql . " )";
		$sth = $this->dbh->prepare($sql);
		$sth->execute($arr_query);
		if($sth->rowCount() > 0){
			return true;
		}
		return false;
	}
	
    function __destruct(){
        $this->dbh = null;
    }
}
?>