<?php
class ServiceData {
	public static $tablename = "service";

	public $id;
	public $name;
	public $description;
	public $price;
	public $is_active;

	public function __construct(){
		$this->name = "";
		$this->description = "";
		$this->price = 0;
		$this->is_active = 1;
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (name,description,price,is_active) ";
		$sql .= "value (\"$this->name\",\"$this->description\",\"$this->price\",$this->is_active)";
		return Executor::doit($sql);
	}

	public function del(){
		$sql = "delete from ".self::$tablename." where id=$this->id";
		Executor::doit($sql);
	}

	public function update(){
		$sql = "update ".self::$tablename." set name=\"$this->name\",description=\"$this->description\",price=\"$this->price\",is_active=$this->is_active where id=$this->id";
		Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new ServiceData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ServiceData());
	}
}
?>
