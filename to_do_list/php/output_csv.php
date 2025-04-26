<?php
require_once('common_utils.php');
function outputCsv()
{
  ini_set("display_errors", 0);
  error_reporting(0);
  require_once('config.php');
  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if (mysqli_connect_error()) {
    die("データベースの接続に失敗しました");
  }
  $sql = "select * from t_todo order by task_id";
  $result = $mysqli->query($sql);
  if (!$result) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }
  mysqli_close($mysqli);


  //csv出力のためにheader情報を設定する
  header("Content-Type: text/csv");
  header("Content-Disposition: attachment; filename = to_do_list.csv");

  //php://outputでブラウザに直接出力する
  $output = fopen("php://output", "w");
  fputcsv($output, ["タスクID", "締め切り日付", "内容", "進捗状況", "優先度"]);

  while ($data = $result->fetch_assoc()) {
    fputcsv($output, [
      $data["task_id"],
      $data["deadline_date"],
      $data["content"],
      $data["task_status"],
      $data["priority"]
    ]);
  }

  fclose($output);
  exit();
}

if (isset($_POST["output_csv"])) {
  outputCsv();
}