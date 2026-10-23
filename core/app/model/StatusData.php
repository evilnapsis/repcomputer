<?php
class StatusData {
	public static $tablename = "status";

	public $id;
	public $name;
	public $color;

	public function __construct(){
		$this->name = "";
		$this->color = "";
	}

	public static function getById($id){
		if($id=="" || $id==null){ return null; }
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new StatusData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by id asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new StatusData());
	}
}
?>
