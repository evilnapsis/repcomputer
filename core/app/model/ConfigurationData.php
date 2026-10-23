<?php
class ConfigurationData {
	public static $tablename = "configuration";

	public $id, $name, $short, $val ; 

	public function __construct(){
		$this->name = "";
		$this->short = "";
		$this->val = "";
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new ConfigurationData());
	}

	public static function getByShort($short){
		$sql = "select * from ".self::$tablename." where short=\"$short\"";
		$query = Executor::doit($sql);
		return Model::one($query[0],new ConfigurationData());
	}

	public static function updateValByShort($short,$val){
		$sql = "update ".self::$tablename." set val=\"$val\" where short=\"$short\"";		
		Executor::doit($sql);
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename;
		$query = Executor::doit($sql);
		return Model::many($query[0],new ConfigurationData());
	}
}
?>
