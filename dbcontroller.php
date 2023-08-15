<?php

// Include the configuration file 
require_once 'config.php'; 

class DBController {
	
	private $conn;

	function __construct() {
		$this->conn = $this->connectDB();
	}
	
	function connectDB() {
		$conn = mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
		if ($conn->connect_errno) {  
			printf("Falló conexión: %s\n", $conn->connect_error);  
			exit();  
		}
		return $conn;
	}
	
	function runQuery($query) {
		$result = mysqli_query($this->conn,$query);
		while($row=mysqli_fetch_assoc($result)) {
			$resultSet[] = $row;
		}		
		if(!empty($resultSet))
			return $resultSet;
	}

	function substractStock($code, $qty) {
		$result = mysqli_query($this->conn,"UPDATE product SET stock = stock - ".$qty." WHERE code = '".$code."'");
		return $result;
	}

	function addStock($code, $qty) {
		$result = mysqli_query($this->conn,"UPDATE product SET stock = stock + ".$qty." WHERE code = '".$code."'");
		return $result;
	}
	
	function numRows($query) {
		$result  = mysqli_query($this->conn,$query);
		$rowcount = mysqli_num_rows($result);
		return $rowcount;	
	}
}
?>