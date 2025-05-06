<?php
require_once('config.php');
require_once('common_utils.php');
function regist()
{
  $deadlineDate = "";
  $content = "";
  $taskStatus = "";
  $priority = "";
  $publicationRange = "";

  $deadlineDate = $_POST["deadline_date"];
  $content = $_POST["content"];
  $taskStatus = $_POST["task_status"];
  $priority = $_POST["priority"];
  $publicationRange = $_POST["publication_range"];

  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if (mysqli_connect_error()) {
    die("データベースの接続に失敗しました");
  }
  $stmt = $mysqli->prepare("INSERT INTO t_todo(deadline_date,content,task_status,priority,user_id,publication_range) values(?,?,?,?,?,?)");
  $stmt->bind_param("ssssss", $deadlineDate, $content, $taskStatus, $priority, $_SESSION["userid"], $publicationRange);
  $result = $stmt->execute();
  if (!$result) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }
  mysqli_close($mysqli);
  header('Location: http://localhost:3000/index.php');
  exit();
};
if (array_key_exists("regist_button", $_POST)) {
  regist();
}
?>

<?php
require_once('config.php');
$taskId = "";
$deadlineDate = "";
$content = "";
$taskStatus = "";
$priority = "";
$publicationRange = "";

function edit()
{
  global $taskId, $deadlineDate, $content, $taskStatus, $priority, $publicationRange;
  $taskId = $_POST["task_id"];
  $deadlineDate = $_POST["deadline_date"];
  $content = $_POST["content"];
  $taskStatus = $_POST["task_status"];
  $priority = $_POST["priority"];
  $publicationRange = $_POST["publication_range"];
};
if (array_key_exists("edit_button", $_POST)) {
  edit();
}
?>

<?php
require_once('config.php');

function edit_regist()
{
  $taskId = "";
  $deadlineDate = "";
  $content = "";
  $taskStatus = "";
  $priority = "";
  $publicationRange = "";

  $taskId = $_POST["task_id"];
  $deadlineDate = $_POST["deadline_date"];
  $content = $_POST["content"];
  $taskStatus = $_POST["task_status"];
  $priority = $_POST["priority"];
  $publicationRange = $_POST["publication_range"];

  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if (mysqli_connect_error()) {
    die("データベースの接続に失敗しました");
  }
  $stmt = $mysqli->prepare("UPDATE T_TODO SET deadline_date=?,content=?,task_status=?,priority=?,publication_range=? where task_id=?");
  $stmt->bind_param("sssssi", $deadlineDate, $content, $taskStatus, $priority, $publicationRange, $taskId);
  $result = $stmt->execute();
  if (!$result) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }

  mysqli_close($mysqli);
  header('Location: http://localhost:3000/index.php');
  exit();
};
if (array_key_exists("edit_regist_button", $_POST)) {
  edit_regist();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/new_regist.css">
</head>

<body>

  <?php if ($taskId == null) : ?>
    <header class="header">
      <p class="header__title">タスク登録</p>
    </header>

    <div class="regist">
      <form class="regist__form" method="post">
        <div class="regist__form__item">
          <p>締め切り日付：</p>
          <input type="date" name="deadline_date">
        </div>
        <div class="regist__form__item">
          <p>内容：</p>
          <input type="text" name="content">
        </div>
        <div class="regist__form__item">
          <p>進捗状況：</p>
          <select name="task_status">
            <option>選択してください</option>
            <option value="未着手">未着手</option>
            <option value="進行中">進行中</option>
            <option value="完了">完了</option>
          </select>
        </div>
        <div class="regist__form__item">
          <p>優先度：</p>
          <select name="priority">
            <option>選択してください</option>
            <option value="高">高</option>
            <option value="中">中</option>
            <option value="低">低</option>
          </select>
        </div>
        <div class="regist__form__item">
          <p>公開範囲：</p>
          <select name="publication_range">
            <option>選択してください</option>
            <option value="公開">公開</option>
            <option value="非公開">非公開(自分のみ)</option>
          </select>
        </div>
        <input class="header__button_regist" type="submit" value="登録" name="regist_button">
        <a href="index.php" class="header__button_back">戻る</a>
      </form>

    </div>







  <?php else: ?>
    <!--更新-->
    <header class="header">
      <p class="header__title">タスク更新</p>
    </header>

    <div class="regist">
      <form class="regist__form" method="post">
        <input type="hidden" name="task_id" value=<?php echo $taskId ?>>
        <div class="regist__form__item">
          <p>締め切り日時：</p>
          <input type="date" name="deadline_date" value=<?php echo $deadlineDate ?>>
        </div>
        <div class="regist__form__item">
          <p>内容：</p>
          <input type="text" name="content" value=<?php echo $content ?>>
        </div>
        <div class="regist__form__item">
          <p>進捗状況：</p>
          <select name="task_status" value=<?php echo $taskStatus ?>>
            <option>選択してください</option>
            <option <?php if ($_POST['task_status'] === '未着手') {
                      echo ' selected';
                    } ?> value="未着手">未着手</option>
            <option <?php if ($_POST['task_status'] === '進行中') {
                      echo ' selected';
                    } ?> value="進行中">進行中</option>
            <option <?php if ($_POST['task_status'] === '完了') {
                      echo ' selected';
                    } ?> value="完了">完了</option>
          </select>
        </div>
        <div class="regist__form__item">
          <p>優先度：</p>
          <select name="priority" value=<?php echo $priority ?>>
            <option>選択してください</option>
            <option <?php if ($_POST['priority'] === '高') {
                      echo ' selected';
                    } ?> value="高">高</option>
            <option <?php if ($_POST['priority'] === '中') {
                      echo ' selected';
                    } ?> value="中">中</option>
            <option <?php if ($_POST['priority'] === '低') {
                      echo ' selected';
                    } ?> value="低">低</option>
          </select>
        </div>
        <div class="regist__form__item">
          <p>公開範囲：</p>
          <select name="publication_range" value=<?php echo $publicationRange ?>>
            <option>選択してください</option>
            <option <?php if ($_POST['publication_range'] === '公開') {
                      echo ' selected';
                    } ?> value="公開">公開</option>
            <option <?php if ($_POST['publication_range'] === '非公開') {
                      echo ' selected';
                    } ?> value="非公開">非公開(自分のみ)</option>
          </select>
        </div>
        <input class="header__button_regist" type="submit" value="更新" name="edit_regist_button">
        <a href="index.php" class="header__button_back">戻る</a>
      </form>

    </div>
  <?php endif; ?>

</body>

</html>