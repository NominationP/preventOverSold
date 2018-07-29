<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "local_common.php";

class MysqlProcess{

	public $db_instance=0;
	public $setTransaction = false;

	function __construct(){

		$this->getMysqlInstance();
		

	}

	function __destruct(){

		/**
		 * if we are use transaction manually ($this->setTrasaction == true)
		 * will commit manually
		 */

		if($this->setTransaction){
			$this->db_instance->autocommit(true);
		}

		$this->db_instance->close();

	}

	function setTransaction($bool){

		/**
		 * if we are use transaction manually ( $bool = true ) ===> set autocommit(false)
		 */

		if($bool){
			$this->db_instance->autocommit(false);
			$this->setTransaction = true;
		}
	}

	function getMysqlInstance(){

		$info_arr = explode(",",MYSQLINFO);

		$ip = $info_arr[0];
		$db_user = $info_arr[1];
		$db_password = $info_arr[2];
		$db_name = $info_arr[3];

		$db=new mysqli;
        // $db->options(MYSQLI_OPECT_TIMEOUT, 1);
        $db->connect($ip,$db_user,$db_password,$db_name);
        $this->db_instance = $db;
	}

	function initialAmount(){

		$sql = "update store set amount = 800 where goods_id = 12345";
		$re = $this->db_instance->query($sql);

		$sql = "select amount from store where goods_id = 12345";
		$count = $this->db_instance->query($sql)->fetch_assoc()['amount'];

		echo date("H:i:s\n");print_r("reset amount = ".$count);



	}

	function version1($transaction=false){

		$this->setTransaction($transaction);

		$userName = strtotime(date("Y-m-d H:i:s"));

		try{

			$sql = "select amount from store where goods_id = 12345";
			$amount = $this->db_instance->query($sql)->fetch_assoc()['amount'];
			// echo date("H:i:s\n");print_r($amount);echo "\n";
			if($amount>=1){
				$sql = "update store set amount = amount-1 where goods_id = 12345";
				$this->db_instance->query($sql);

				$sql = "select amount from store where goods_id = 12345";
				$count = $this->db_instance->query($sql)->fetch_assoc()['amount'];

				file_put_contents("log", date("H:i:s")." ".$userName." bought! Reside: $count\n",FILE_APPEND);

				// echo date("H:i:s\n");print_r(" ".$userName." bought! Reside: $count");echo "\n";
			}else{
				$sql = "select amount from store where goods_id = 12345";
				$count = $this->db_instance->query($sql)->fetch_assoc()['amount'];
				file_put_contents("log", date("H:i:s")." "."saleOut : ".$count."\n",FILE_APPEND);

				// echo date("H:i:s\n");print_r("saleOut : ".$count);echo "\n";die();
			}

		}
		catch(Exception $e) {
		  // echo 'Message: ' .$e->getMessage();

		  if($this->setTransaction){
		  	echo date("H:i:s\n");print_r("rollback transaction manually !\n");
		  	$this->db_instance->rollback();

		  }
		}

		if($this->setTransaction){
			echo date("H:i:s\n");print_r("commit transaction manually !\n");
			$this->db_instance->commit();
		}

	}


	/**
	 * ab  -n 1000 -c 1000 http://XXXXXXX.cn/preventOversold/test.php
	 * not saleOut
	 */
	
	/**
	 * why this could be preventOverSold
	 * 1 mysql innodb transaction level 3 : repeatable read // not this reason
	 * 2 update amount = amount - 1 ==> atomic together 
	 */


	function version2($transaction=true){

		$this->setTransaction($transaction);
		$userName = strtotime(date("Y-m-d H:i:s"));


		try{

			$sql = "update store set amount = amount-1 where goods_id = 12345";
			// echo date("H:i:s\n");print_r($sql);echo "\n";
			$this->db_instance->query($sql);

			$sql = "select amount from store where goods_id = 12345";
			$count = $this->db_instance->query($sql)->fetch_assoc()['amount'];

			if($count<0){
				// file_put_contents("log", date("H:i:s")." "."saleOut : ".$count."\n",FILE_APPEND);
				throw new Exception("Insufficient inventory !");

			}

			if($this->setTransaction){
				echo date("H:i:s\n");print_r("commit transaction manually !\n");
				$this->db_instance->commit();
			}

			// file_put_contents("log", date("H:i:s")." ".$userName." bought! Reside: $count\n",FILE_APPEND);

		}
		catch(Exception $e){

			if($this->setTransaction){
				echo date("H:i:s\n");print_r("rollback transaction manually !\n");
				$this->db_instance->rollback();

			}

		}


		$sql = "select amount from store where goods_id = 12345";
		$count = $this->db_instance->query($sql)->fetch_assoc()['amount'];

		echo date("H:i:s\n");print_r($count);echo "\n";


	}

	/**
	 * ab  -n 1000 -c 1000 http://XXXXXXX.cn/preventOversold/test.php
	 * not saleOut
	 */


	function version3($transaction=false){

		$this->setTransaction($transaction);
		$userName = strtotime(date("Y-m-d H:i:s"));


		try{

			$sql = "update store set amount = amount-1 where goods_id = 12345 and amount >= 1";
			$re = $this->db_instance->query($sql);

			if(mysqli_affected_rows($this->db_instance)==0){
				// file_put_contents("log", date("H:i:s")." "."saleOut : ".$count."\n",FILE_APPEND);
				throw new Exception("Insufficient inventory !");

			}

			if($this->setTransaction){
				echo date("H:i:s\n");print_r("commit transaction manually !\n");
				$this->db_instance->commit();
			}

			// file_put_contents("log", date("H:i:s")." ".$userName." bought! Reside: $count\n",FILE_APPEND);

		}
		catch(Exception $e){

			if($this->setTransaction){
				echo date("H:i:s\n");print_r("rollback transaction manually !\n");
				$this->db_instance->rollback();

			}

		}


		$sql = "select amount from store where goods_id = 12345";
		$count = $this->db_instance->query($sql)->fetch_assoc()['amount'];

		echo date("H:i:s\n");print_r($count);echo "\n";


	}



}


// $mp = new MysqlProcess;
// $mp->initialAmount();
// $mp->version1();
// $mp->version2();



