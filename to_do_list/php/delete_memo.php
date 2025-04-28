<?php
require_once('config.php');
require_once('common_utils.php');
function deleteMemo()
{

  $memoId = $_POST["memo_id"];

  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if (mysqli_connect_error()) {
    die("データベースの接続に失敗しました");
  }
  $stmt = $mysqli->prepare("DELETE FROM T_MEMO WHERE MEMO_ID = ?");
  $stmt->bind_param("i", $memoId);
  $result = $stmt->execute();
  if (!$result) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }
  mysqli_close($mysqli);
  $taskId = urlencode($_POST["task_id"]);
  header("Location: show_detail.php?task_id={$taskId}");
  exit();
};
if (array_key_exists("delete_memo_button", $_POST)) {
  deleteMemo();
}