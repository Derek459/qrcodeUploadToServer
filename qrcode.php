<html>
    <body>
        
 


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
        
        case "GET":
            $sql = "SELECT * FROM $table_name ";
            $id_is_set = isset($_REQUEST["id"]);
            $sql = $sql . ($id_is_set ? "    WHERE id = :id " : "");

            $sql = $sql ."  "."ORDER BY `date`  DESC";


            $pdoStmt = $pdo->prepare($sql);
            if ($id_is_set) {
                $id = $_REQUEST["id"];
                $pdoStmt->bindValue(':id', $id, PDO::PARAM_STR);
            }

            $pdoStmt->execute();
            $result = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);

            // $payload["rows"] = $result;
            $how_many = count($result);
            $index = 0;
            if ($how_many == 0 ) {
                echo( "no blood data<hr />" );
               
            } else {
                echo( "<table border='1' >" );
                echo( "<tr><th>收縮壓</th><th>舒張壓</th><th>上傳時間</th><th>心情</th></tr>" );

                while( $index < $how_many) {
                    $each_row = $result[$index];
                
                    $sys = $each_row["sys"];
                    $dia = $each_row["dia"];
                    $date = $each_row["date"];
                    $mood = $each_row["mood"];
                    echo( "<tr><td>$sys</td><td>$dia</td><td>$date</td><td>$mood</td></tr>" );
                    $index += 1;
                }
                
                echo( "</table  >" );
            }

            // echo json_encode($payload);
            break;
        default:
            exit();
    }


} catch (PDOException $ex) {

    my_error_handler($ex->getCode(), $ex->getMessage(), "table.php", $ex->getLine());
}
?>


</body>
</html>
