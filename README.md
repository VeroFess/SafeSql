# SafeSql

 Prevent PHP Mysql ingect, you can view useage in my repository "EasyPHPReg"
 
 some example:
 
  insert:
  
		if($this->sql->SafeInsert("Users",array(
											array("email",strtolower($email)),
											array("password",strtolower(md5($password))),
											array("token",$token),
											array("exp_time",time()+60*60*12),
											array("reg_time",time()),
											array("active",0)
											)) == false){
			return "err_sql_insert";
		}

			
	delete:
		
		$this->sql->SafeDelete("Users",array(array('email',$email)));
		
	update:
		
		if($this->sql->SafeUpdate("Users",array(array("token",""),array("exp_time",0)),array(array('token',$token)))){
			return true;
		}
		
	select:
	
		if($this->sql->SafeSelect("Users",array(array('email',$email))) != false){
			if($this->sql->SafeSelect("Users",array(array('email',$email)),"exp_time")[0]["exp_time"] < time()){
				$this->sql->SafeDelete("Users",array(array('token',$token)));
			}else{
				return "err_already_use";
			}
		}
