<?php
require_once('config.php');
$deadlineDate = "";
$content = "";
$taskStatus = "";
$priority = "";

$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
if (mysqli_connect_error()){
  die("データベースの接続に失敗しました");
}
$sql = "select * from t_todo order by deadline_date";
$result = $mysqli->query($sql);
mysqli_close($mysqli);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/index.css">
</head>
<body>
  <header class="header">
    <p class="header__title">タスク一覧</p>
    <a href="new_regist.php" class="header__button_regist">登録</a>
  </header>
  <div class="main">
    <div class="main__content">
      <ul class="main__content__col">
        <li>締め切り日時</li>
        <li>内容</li>
        <li>進捗状況</li>
        <li>優先度</li>
      </ul>
      <?php foreach($result as $column):?>
        <div class="main__content__area">
          <ul class="main__content__row">
            <li><?php echo $column['deadline_date']?></li>
            <li><?php echo $column['content']?></li>
            <li><?php echo $column['task_status']?></li>
            <li><?php echo $column['priority']?></li>
          </ul>
          <form class="main__content__row" action="new_regist.php" method="post">
            <input type="hidden" value=<?php echo $column['task_id']?> name="task_id">
            <input type="hidden" value=<?php echo $column['deadline_date']?> name="deadline_date">
            <input type="hidden" value=<?php echo $column['content']?> name="content">
            <input type="hidden" value=<?php echo $column['task_status']?> name="task_status">
            <input type="hidden" value=<?php echo $column['priority']?> name="priority">
            <input type="submit" class="main__content__row__button_edit" name="edit_button" value="編集">
          </form>
          <form class="main__content__row" action="delete.php" method="post">
            <input type="hidden" value=<?php echo $column['task_id']?> name="task_id">
            <input type="submit" class="main__content__row__button_delete" name="delete_button" value="削除">
          </form>
        </div>  
      <?php endforeach; ?>
    </div>
  </div>  
</body>
</html>