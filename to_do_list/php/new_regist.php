<?php
require_once('config.php');
require_once('common_utils.php');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
  die("データベースの接続に失敗しました: " . $mysqli->connect_error);
}
$sql = "select * from users";
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$searchEngineNameListResults = $stmt->get_result();
if (!$searchEngineNameListResults) {
  die("クエリの実行に失敗しました: " . $mysqli->error);
}
$searchEngineNameList = [];
while ($row = $searchEngineNameListResults->fetch_assoc()) {
  $searchEngineNameList[] = $row;
}

function regist()
{

  global $errorMessageDeadLineDate, $errorMessageContent, $errorMessageTaskStatus, $errorMessagePriority, $errorMessagePublicationRange, $errorMessageManager;

  $deadlineDate = "";
  $content = "";
  $taskStatus = "";
  $priority = "";
  $publicationRange = "";
  $manager = "";

  $deadlineDate = $_POST["deadline_date"];
  $content = $_POST["content"];
  $taskStatus = $_POST["task_status"];
  $priority = $_POST["priority"];
  $publicationRange = $_POST["publication_range"];

  if (!array_key_exists('deadline_date', $_POST) || empty($_POST["deadline_date"])) {
    $errorMessageDeadLineDate = "日付を入力してください";
    return $errorMessageDeadLineDate;
  }

  if (!array_key_exists('content', $_POST) || empty($_POST["content"])) {
    $errorMessageContent = "内容を入力してください";
    return $errorMessageContent;
  }

  if ($_POST['task_status'] == '') {
    $errorMessageTaskStatus = "進捗状況を入力してください";
    return $errorMessageTaskStatus;
  }

  if ($_POST["priority"] == '') {
    $errorMessagePriority = "優先度を選択してください";
    return $errorMessagePriority;
  }

  if ($_POST["publication_range"] == '') {
    $errorMessagePublicationRange = "公開範囲を選択してください";
    return $errorMessagePublicationRange;
  }

  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if (mysqli_connect_error()) {
    die("データベースの接続に失敗しました");
  }

  if (array_key_exists('manager', $_POST) && !empty($_POST["manager"])) {
    $manager = $_POST["manager"];
    $stmt = $mysqli->prepare("select * from users where name = ? limit 1");
    $stmt->bind_param("s", $manager);
    $stmt->execute();
    $result = $stmt->get_result();
    $result = $result->fetch_assoc();

    if ($result == null) {
      $errorMessageManager = "そのユーザー名のユーザーは存在しません";
      return $errorMessageManager;
    }
  }

  $managerId = $result["id"];

  $stmt = $mysqli->prepare("INSERT INTO t_todo(deadline_date,content,task_status,priority,user_id,publication_range,manager_id) values(?,?,?,?,?,?,?)");
  $stmt->bind_param("sssssss", $deadlineDate, $content, $taskStatus, $priority, $_SESSION["userid"], $publicationRange, $managerId);
  $result = $stmt->execute();
  if (!$result) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }
  mysqli_close($mysqli);
  header('Location: http://localhost:3000/index.php');
  exit();
};
if (array_key_exists("regist_button", $_POST)) {
  $errorMessage = regist();
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

  <header class="header">
    <p class="header__title">タスク登録</p>
  </header>

  <div class="regist">
    <form class="regist__form" method="post">
      <?php if (!empty($errorMessageDeadLineDate)) : ?>
        <p class="error__message"><?= htmlspecialchars($errorMessageDeadLineDate, ENT_QUOTES, "UTF-8") ?></p>
      <?php endif; ?>
      <div class="regist__form__item">
        <p>締め切り日付：</p>
        <input type="date" name="deadline_date">
      </div>
      <?php if (!empty($errorMessageContent)) : ?>
        <p class="error__message"><?= htmlspecialchars($errorMessageContent, ENT_QUOTES, "UTF-8") ?></p>
      <?php endif; ?>
      <div class="regist__form__item">
        <p>内容：</p>
        <input type="text" name="content">
      </div>
      <?php if (!empty($errorMessageTaskStatus)) : ?>
        <p class="error__message"><?= htmlspecialchars($errorMessageTaskStatus, ENT_QUOTES, "UTF-8") ?></p>
      <?php endif; ?>
      <div class="regist__form__item">
        <p>進捗状況：</p>
        <select name="task_status">
          <option value="">選択してください</option>
          <option value="未着手">未着手</option>
          <option value="進行中">進行中</option>
          <option value="完了">完了</option>
        </select>
      </div>
      <?php if (!empty($errorMessagePriority)) : ?>
        <p class="error__message"><?= htmlspecialchars($errorMessagePriority, ENT_QUOTES, "UTF-8") ?></p>
      <?php endif; ?>
      <div class="regist__form__item">
        <p>優先度：</p>
        <select name="priority">
          <option value="">選択してください</option>
          <option value="高">高</option>
          <option value="中">中</option>
          <option value="低">低</option>
        </select>
      </div>
      <?php if (!empty($errorMessagePublicationRange)) : ?>
        <p class="error__message"><?= htmlspecialchars($errorMessagePublicationRange, ENT_QUOTES, "UTF-8") ?></p>
      <?php endif; ?>
      <div class="regist__form__item">
        <p>公開範囲：</p>
        <select name="publication_range">
          <option value="">選択してください</option>
          <option value="公開">公開</option>
          <option value="非公開">非公開(自分のみ)</option>
        </select>
      </div>
      <div>
        <?php if (!empty($errorMessageManager)) : ?>
          <p class="error__message"><?= htmlspecialchars($errorMessageManager, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
      </div>

      <div class="regist__form__item">
        <p>担当者：</p>
        <input list="search-engine" type="text" name="manager">
        <datalist id="search-engine">
          <?php foreach ($searchEngineNameList as $row): ?>
            <option value=<?= htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8") ?>></option>
          <?php endforeach; ?>
        </datalist>
      </div>
      <input class="header__button_regist" type="submit" value="登録" name="regist_button">
      <a href="index.php" class="header__button_back">戻る</a>
    </form>
  </div>
</body>

</html>