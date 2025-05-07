<?php

require_once('config.php');
require_once('common_utils.php');
function updateMemo()
{
  try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (mysqli_connect_error()) {
      die("データベースの接続に失敗しました");
    }

    $stmt = $mysqli->prepare("UPDATE t_memo set memo = ? ,update_timestamp = CURRENT_TIMESTAMP() where memo_id = ?");
    $stmt->bind_param("si", $_POST["edit_memo_text"], $_POST['edit_memo_id']);
    $result = $stmt->execute();
    if (!$result) {
      die("クエリの実行に失敗しました: " . $mysqli->error);
    }
    $task_id = urlencode($_POST["task_id"]);
    header("Location: show_detail.php?task_id={$task_id}");
  } finally {
    mysqli_close($mysqli);
  }
}

if (array_key_exists("edit_memo", $_POST) and !empty($_POST["edit_memo_text"])) {
  updateMemo();
}
