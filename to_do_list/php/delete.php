<?php
require_once('config.php');
function delete(){
  $taskId = "";

  $taskId = $_POST["task_id"];

  $mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
  if (mysqli_connect_error()){
    die("データベースの接続に失敗しました");
  }
  $stmt = $mysqli->prepare("DELETE FROM T_TODO WHERE TASK_ID = ?");
  $stmt->bind_param("i",$taskId);
  $stmt->execute();

  mysqli_close($mysqli);
  header('Location: http://localhost:3000/index.php');
  exit();
};
if(array_key_exists("delete_button",$_POST)){
  delete();
}
?>