<?php
class OperationDetailData {
	public static $tablename = "operation_detail";

	public $id;
	public $operation_id;
	public $service_id;
	public $quantity;
	public $price;
	public $discount;

	public function __construct(){
		$this->quantity = 0;
		$this->price = 0;
		$this->discount = 0;
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (operation_id,service_id,quantity,price,discount) ";
		$sql .= "value ($this->operation_id,".($this->service_id?$this->service_id:"NULL").",$this->quantity,$this->price,$this->discount)";
		return Executor::doit($sql);
	}

	public function del(){
		$sql = "delete from ".self::$tablename." where id=$this->id";
		Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new OperationDetailData());
	}

	public static function getAllByOperationId($id){
		$sql = "select * from ".self::$tablename." where operation_id=$id";
		$query = Executor::doit($sql);
		return Model::many($query[0],new OperationDetailData());
	}

	public function getService(){ return $this->service_id ? ServiceData::getById($this->service_id) : null; }
}
?>
