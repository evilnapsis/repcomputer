<?php
class ContactData {
	public static $tablename = "contact";

	public $id;
	public $name;
	public $lastname;
	public $email;
	public $address;
	public $phone;
	public $image;
	public $company;
	public $phone2;
	public $cp;
	public $city;
	public $description;
	public $type; /* 1. cliente, 2. proveedor */
	public $is_active;
	public $created_at;

	public function __construct(){
		$this->name = "";
		$this->lastname = "";
		$this->email = "";
		$this->address = "";
		$this->phone = "";
		$this->image = "";
		$this->company = "";
		$this->phone2 = "";
		$this->cp = "";
		$this->city = "";
		$this->description = "";
		$this->type = 1;
		$this->is_active = 1;
		$this->created_at = "NOW()";
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (name,lastname,email,address,phone,image,company,phone2,cp,city,description,type,is_active,created_at) ";
		$sql .= "value (\"$this->name\",\"$this->lastname\",\"$this->email\",\"$this->address\",\"$this->phone\",\"$this->image\",\"$this->company\",\"$this->phone2\",\"$this->cp\",\"$this->city\",\"$this->description\",$this->type,$this->is_active,$this->created_at)";
		return Executor::doit($sql);
	}

	public function del(){
		$sql = "delete from ".self::$tablename." where id=$this->id";
		Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "delete from ".self::$tablename." where id=$id";
		Executor::doit($sql);
	}

	public function update(){
		$sql = "update ".self::$tablename." set name=\"$this->name\",lastname=\"$this->lastname\",email=\"$this->email\",address=\"$this->address\",phone=\"$this->phone\",company=\"$this->company\",phone2=\"$this->phone2\",cp=\"$this->cp\",city=\"$this->city\",description=\"$this->description\",type=$this->type,is_active=$this->is_active where id=$this->id";
		Executor::doit($sql);
	}

	public static function getById($id){
		if($id=="" || $id==null){ return null; }
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new ContactData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ContactData());
	}

	public static function getAllBy($k,$v){
		$sql = "select * from ".self::$tablename." where $k=\"$v\" order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ContactData());
	}

	public static function getAllClients(){
		return self::getAllBy("type", 1);
	}

	public static function getAllSuppliers(){
		return self::getAllBy("type", 2);
	}

	public static function getLike($q){
		$sql = "select * from ".self::$tablename." where name like '%$q%' or lastname like '%$q%' or email like '%$q%'";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ContactData());
	}
}
?>
