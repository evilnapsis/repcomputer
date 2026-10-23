<?php
/**
 * PaymentData
 *
 * Pagos y abonos aplicados a una orden de servicio. Una orden puede recibir
 * varios pagos parciales; el saldo pendiente es el total de la orden menos la
 * suma de sus pagos.
 */
class PaymentData {
	public static $tablename = "payment";

	/** Formas de pago aceptadas. Cualquier otro valor se guarda como "Otro". */
	public static $methods = array("Efectivo", "Tarjeta", "Transferencia", "Deposito", "Otro");

	public $id;
	public $operation_id;
	public $amount;
	public $method;
	public $reference;
	public $notes;
	public $user_id;
	public $created_at;

	public function __construct(){
		$this->amount = 0;
		$this->method = "Efectivo";
		$this->reference = "";
		$this->notes = "";
		$this->created_at = "NOW()";
	}

	public function add(){
		$con = Database::getCon();
		$method = $con->real_escape_string($this->method);
		$reference = $con->real_escape_string($this->reference);
		$notes = $con->real_escape_string($this->notes);
		$amount = floatval($this->amount);
		$opId = intval($this->operation_id);
		$userId = $this->user_id ? intval($this->user_id) : "NULL";

		$sql = "insert into ".self::$tablename." (operation_id,amount,method,reference,notes,user_id,created_at) ";
		$sql .= "value ($opId,$amount,\"$method\",\"$reference\",\"$notes\",$userId,$this->created_at)";
		return Executor::doit($sql);
	}

	public function del(){
		$sql = "delete from ".self::$tablename." where id=".intval($this->id);
		return Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=".intval($id);
		$query = Executor::doit($sql);
		return Model::one($query[0],new PaymentData());
	}

	public static function getAllByOperationId($id){
		$sql = "select * from ".self::$tablename." where operation_id=".intval($id)." order by created_at asc, id asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new PaymentData());
	}

	public static function getAllByQ($q){
		$sql = "select * from ".self::$tablename." $q";
		$query = Executor::doit($sql);
		return Model::many($query[0],new PaymentData());
	}

	/** Borra de golpe los pagos de una orden (se usa al eliminar la orden). */
	public static function delAllByOperationId($id){
		$sql = "delete from ".self::$tablename." where operation_id=".intval($id);
		return Executor::doit($sql);
	}

	/** Suma de lo cobrado en una orden. */
	public static function getTotalByOperationId($id){
		$sql = "select coalesce(sum(amount),0) as cobrado from ".self::$tablename." where operation_id=".intval($id);
		$query = Executor::doit($sql);
		if(!$query[0]) return 0.0;
		$r = $query[0]->fetch_assoc();
		return floatval($r["cobrado"]);
	}

	/** Mapa [operation_id => total cobrado] para pintar listados sin N+1. */
	public static function getTotalsByOperation(){
		$sql = "select operation_id, coalesce(sum(amount),0) as cobrado from ".self::$tablename." group by operation_id";
		$query = Executor::doit($sql);
		$out = array();
		if($query[0]){
			while($r = $query[0]->fetch_assoc()){
				$out[intval($r["operation_id"])] = floatval($r["cobrado"]);
			}
		}
		return $out;
	}

	public function getOperation(){ return $this->operation_id ? OperationData::getById($this->operation_id) : null; }
	public function getUser(){ return $this->user_id ? UserData::getById($this->user_id) : null; }

	/** Cliente del pago, a traves de la orden. */
	public function getContact(){
		$op = $this->getOperation();
		return $op ? $op->getContact() : null;
	}
}
?>
