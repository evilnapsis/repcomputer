<?php
class DeviceTypeData {
	public static $tablename = "device_type";

	public $id;
	public $name;
	public $description;
	public $is_active;
	public $created_at;

	public function __construct(){
		$this->name = "";
		$this->description = "";
		$this->is_active = 1;
		$this->created_at = "NOW()";
	}

	public function add(){
		$name = addslashes($this->name);
		$description = addslashes($this->description);
		$isActive = intval($this->is_active);
		$sql = "insert into ".self::$tablename." (name,description,is_active,created_at) ";
		$sql .= "value (\"$name\",\"$description\",$isActive,$this->created_at)";
		return Executor::doit($sql);
	}

	public function del(){
		$id = intval($this->id);
		$sql = "delete from ".self::$tablename." where id=$id";
		Executor::doit($sql);
	}

	public function update(){
		$id = intval($this->id);
		$name = addslashes($this->name);
		$description = addslashes($this->description);
		$isActive = intval($this->is_active);
		$sql = "update ".self::$tablename." set name=\"$name\",description=\"$description\",is_active=$isActive where id=$id";
		Executor::doit($sql);
	}

	public static function getById($id){
		if($id=="" || $id==null){ return null; }
		$id = intval($id);
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new DeviceTypeData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new DeviceTypeData());
	}

	public static function getActive(){
		$sql = "select * from ".self::$tablename." where is_active=1 order by name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new DeviceTypeData());
	}

	public function countDevices(){
		$id = intval($this->id);
		$name = addslashes($this->name);
		$sql = "select count(*) as total from device where device_type_id=$id or type=\"$name\"";
		$query = Executor::doit($sql);
		$row = $query[0]->fetch_assoc();
		return intval($row['total'] ?? 0);
	}
}
?>