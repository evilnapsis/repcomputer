<?php
class OperationData {
	public static $tablename = "operation";

	public $id;
	public $description;
	public $device_id;
	public $contact_id;
	public $user_id;
	public $status_id;
	public $start_date;
	public $end_date;
	public $total;
	public $created_at;

	public function __construct(){
		$this->description = "";
		$this->start_date = "NULL";
		$this->end_date = "NULL";
		$this->total = 0;
		$this->created_at = "NOW()";
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (description,device_id,contact_id,user_id,status_id,start_date,end_date,total,created_at) ";
		$sql .= "value (\"$this->description\",".($this->device_id?$this->device_id:"NULL").",".($this->contact_id?$this->contact_id:"NULL").",$this->user_id,".($this->status_id?$this->status_id:"NULL").",".($this->start_date!="NULL"?"\"$this->start_date\"":"NULL").",".($this->end_date!="NULL"?"\"$this->end_date\"":"NULL").",$this->total,$this->created_at)";
		return Executor::doit($sql);
	}

	public function del(){
		$sql = "delete from ".self::$tablename." where id=$this->id";
		Executor::doit($sql);
	}

	public function update(){
		$sql = "update ".self::$tablename." set description=\"$this->description\",device_id=".($this->device_id?$this->device_id:"NULL").",contact_id=".($this->contact_id?$this->contact_id:"NULL").",user_id=$this->user_id,status_id=".($this->status_id?$this->status_id:"NULL").",start_date=".($this->start_date!="NULL"?"\"$this->start_date\"":"NULL").",end_date=".($this->end_date!="NULL"?"\"$this->end_date\"":"NULL").",total=$this->total where id=$this->id";
		Executor::doit($sql);
	}

	public function update_total(){
		$sql = "update ".self::$tablename." set total=$this->total where id=$this->id";
		Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new OperationData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new OperationData());
	}

	public static function getAllByQ($q){
		$sql = "select * from ".self::$tablename." $q";
		$query = Executor::doit($sql);
		return Model::many($query[0],new OperationData());
	}

	public function getDevice(){ return $this->device_id ? DeviceData::getById($this->device_id) : null; }
	public function getVehicle(){ return $this->getDevice(); }
	public function getContact(){ return $this->contact_id ? ContactData::getById($this->contact_id) : null; }
	public function getUser(){ return $this->user_id ? UserData::getById($this->user_id) : null; }
	public function getStatus(){ return $this->status_id ? StatusData::getById($this->status_id) : null; }
}
?>
