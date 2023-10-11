<?php
function my_error_handler($errno, $errstr, $errfile, $errline)
{

	$error = [];
	$error["error_no"] = $errno;
	$error["error_message"] = $errstr;
	$error["error_file"] = $errfile;
	$error["error_line"] = $errline;

	echo json_encode($error);
	exit();
}

register_shutdown_function('my_error_handler');
set_error_handler('my_error_handler');
error_reporting(0);

$payload = [];
$payload["error_no"] = 0;
try {
	$pdo = new PDO(
		'mysql:host=localhost;dbname=mid40;charset=utf8mb4',
		'root',
		'Derekwind0406@',
		array(
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		)
	);

	if (isset($_REQUEST["table_name"])) {
		$table_name = $_REQUEST["table_name"];
	} else {
		trigger_error("mapd: Cannot find query parameter 'table_name' ", E_USER_ERROR);
	}

	$which_method = $_SERVER['REQUEST_METHOD'];
	switch ($which_method) {
		case "POST":
			$sql = "INSERT INTO $table_name ( ";
			$data = json_decode(file_get_contents('php://input'), true);
			$count = 0;
			foreach ($data as $eachKey => $eachValue) {
				$count += 1;
				if ($count > 1) {
					$sql = $sql . " , `$eachKey` ";
				} else {
					$sql = $sql . "  `$eachKey` ";
				}
			}
			$sql = $sql . "  ) VALUES ( ";
			$count = 0;
			foreach ($data as $eachKey => $eachValue) {
				$count += 1;
				if ($count > 1) {
					$sql = $sql . " , '$eachValue' ";
				} else {
					$sql = $sql . "  '$eachValue' ";
				}
			}
			$sql = $sql . "  )  ";
			$pdoStmt = $pdo->prepare($sql);

			$pdoStmt->execute();
			$id = $pdo->lastInsertId();

			$payload["update_count"] = $pdoStmt->rowCount();
			$payload["id"] = $id;
			$payload["sql"] = $sql;
			$payload["data"] = $data;

			echo json_encode($payload);
			break;

		case "PUT":
			$id_is_set = isset($_REQUEST["id"]);
			if ( $id_is_set ) {
				$id = $_REQUEST["id"];
			} else {
				trigger_error("mapd: Cannot find query parameter 'id' ", E_USER_ERROR);
			}

			$sql = "UPDATE $table_name SET ";
			$data = json_decode(file_get_contents('php://input'), true);
			$count = 0;
			foreach ($data as $eachKey => $eachValue) {
				$count += 1;
				if ($count > 1) {
					$sql = $sql . " , `$eachKey`='$eachValue' ";
				} else {
					$sql = $sql . "  `$eachKey`='$eachValue' ";
				}
			}
			$sql = $sql . "  WHERE id = $id";
			
			$pdoStmt = $pdo->prepare($sql);

			$pdoStmt->execute();
			
			$payload["update_count"] = $pdoStmt->rowCount();
			$payload["sql"] = $sql;
			$payload["data"] = $data;

			echo json_encode($payload);
			break;

		case "DELETE":
			$id_is_set = isset($_REQUEST["id"]);
			if ($id_is_set) {
				$id = $_REQUEST["id"];
			} else {
				trigger_error("mapd: Cannot find query parameter 'id' ", E_USER_ERROR);
			}

			$sql = "DELETE FROM $table_name  ";
			
			$sql = $sql . "  WHERE id = $id";

			$pdoStmt = $pdo->prepare($sql);

			$pdoStmt->execute();

			$payload["update_count"] = $pdoStmt->rowCount();
			$payload["sql"] = $sql;
			
			echo json_encode($payload);
			break;

		case "GET":
			$sql = "SELECT * FROM $table_name ";
			$id_is_set = isset($_REQUEST["id"]);
			$sql = $sql . ($id_is_set ? "    WHERE id = :id " : "");

			$pdoStmt = $pdo->prepare($sql);
			if ($id_is_set) {
				$id = $_REQUEST["id"];
				$pdoStmt->bindValue(':id', $id, PDO::PARAM_STR);
			}

			$pdoStmt->execute();
			$result = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);

			$payload["rows"] = $result;

			echo json_encode($payload);
			break;
		default:
			exit();
	}


} catch (PDOException $ex) {

	my_error_handler($ex->getCode(), $ex->getMessage(), "table.php", $ex->getLine());
}
?>