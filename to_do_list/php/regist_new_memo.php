<?php

require_once('config.php');
require_once('common_utils.php');
function registNewMemo()
{
  try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (mysqli_connect_error()) {
      die("データベースの接続に失敗しました");
    }

    $stmt = $mysqli->prepare("INSERT INTO t_memo (task_id, memo) VALUES (?, ?)");
    $stmt->bind_param("ss", $_POST["task_id"], $_POST["new_memo_text"]);
    $result = $stmt->execute();
    if (!$result) {
      die("クエリの実行に失敗しました: " . $mysqli->error);
    }
    $task_id = urlencode($_POST["task_id"]);
    header("Location: show-detail.php?task_id={$task_id}");
  } finally {
    mysqli_close($mysqli);
  }
}

if (array_key_exists("regist_new_memo", $_POST) and !empty($_POST["new_memo_text"])) {
  registNewMemo();
}