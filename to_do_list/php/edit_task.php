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

$taskId = "";
$deadlineDate = "";
$content = "";
$taskStatus = "";
$priority = "";
$publicationRange = "";
$managerId = "";

function edit()
{
  global $taskId, $deadlineDate, $content, $taskStatus, $priority, $publicationRange, $managerId, $manager;
  $taskId = $_POST["task_id"];
  $deadlineDate = $_POST["deadline_date"];
  $content = $_POST["content"];
  $taskStatus = $_POST["task_status"];
  $priority = $_POST["priority"];
  $publicationRange = $_POST["publication_range"];
  $managerId = null;
  if (array_key_exists("manager_id", $_POST)) {
    $managerId = $_POST["manager_id"];
  }


  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if (mysqli_connect_error()) {
    die("データベースの接続に失敗しました");
  }

  $stmt = $mysqli->prepare("select U.name from t_todo T inner join users U on T.manager_id = U.id where T.manager_id = ? limit 1");
  $stmt->bind_param("s", $managerId);
  $stmt->execute();
  $result = $stmt->get_result();
  if (!$result) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }
  $data = $result->fetch_assoc();
  $manager = $data['name'] ?? null;
};
if (array_key_exists("edit_button", $_POST)) {
  edit();
}
?>

<?php
require_once('config.php');

function edit_regist()
{

  global $taskId, $deadlineDate, $content, $taskStatus, $priority, $publicationRange, $manager, $errorMessageDeadLineDate,
    $errorMessageContent, $errorMessageTaskStatus, $errorMessagePriority, $errorMessagePublicationRange, $errorMessageManager;

  $taskId = "";
  $deadlineDate = "";
  $content = "";
  $taskStatus = "";
  $priority = "";
  $publicationRange = "";
  $manager = null;

  $taskId = $_POST["task_id"];
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

  $stmt = $mysqli->prepare("UPDATE T_TODO SET deadline_date=?,content=?,task_status=?,priority=?,publication_range=?,manager_id=?,update_timestamp=CURRENT_TIMESTAMP() where task_id=?");
  $stmt->bind_param("sssssii", $deadlineDate, $content, $taskStatus, $priority, $publicationRange, $managerId, $taskId);
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

  <!--更新-->
  <header class="header">
    <p class="header__title">タスク更新</p>
  </header>

  <div class="regist">
    <form class="regist__form" method="post">
      <input type="hidden" name="task_id" value=<?php echo $taskId ?>>
      <div>
        <?php if (!empty($errorMessageDeadLineDate)) : ?>
          <p class="error__message"><?= htmlspecialchars($errorMessageDeadLineDate, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
      </div>
      <div class="regist__form__item">
        <p>締め切り日時：</p>
        <input type="date" name="deadline_date" value=<?php echo $deadlineDate ?>>
      </div>
      <div>
        <?php if (!empty($errorMessageContent)) : ?>
          <p class="error__message"><?= htmlspecialchars($errorMessageContent, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
      </div>
      <div class="regist__form__item">
        <p>内容：</p>
        <input type="text" name="content" value=<?php echo $content ?>>
      </div>
      <div>
        <?php if (!empty($errorMessageTaskStatus)) : ?>
          <p class="error__message"><?= htmlspecialchars($errorMessageTaskStatus, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
      </div>
      <div class="regist__form__item">
        <p>進捗状況：</p>
        <select name="task_status" value=<?php echo $taskStatus ?>>
          <option value="">選択してください</option>
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
      <div>
        <?php if (!empty($errorMessagePriority)) : ?>
          <p class="error__message"><?= htmlspecialchars($errorMessagePriority, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
      </div>
      <div class="regist__form__item">
        <p>優先度：</p>
        <select name="priority" value=<?php echo $priority ?>>
          <option value="">選択してください</option>
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
      <div>
        <?php if (!empty($errorMessagePublicationRange)) : ?>
          <p class="error__message"><?= htmlspecialchars($errorMessagePublicationRange, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
      </div>
      <div class="regist__form__item">
        <p>公開範囲：</p>
        <select name="publication_range" value=<?php echo $publicationRange ?>>
          <option value="">選択してください</option>
          <option <?php if ($_POST['publication_range'] === '公開') {
                    echo ' selected';
                  } ?> value="公開">公開</option>
          <option <?php if ($_POST['publication_range'] === '非公開') {
                    echo ' selected';
                  } ?> value="非公開">非公開(自分のみ)</option>
        </select>
      </div>
      <div>
        <?php if (!empty($errorMessageManager)) : ?>
          <p class="error__message"><?= htmlspecialchars($errorMessageManager, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
      </div>
      <div class="regist__form__item">
        <p>担当者：</p>
        <input list="search-engine" type="text" name="manager" value=<?php echo $manager ?>>
        <datalist id="search-engine">
          <?php foreach ($searchEngineNameList as $row): ?>
            <option value=<?= htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8") ?>></option>
          <?php endforeach; ?>
        </datalist>
      </div>
      <input class="header__button_regist" type="submit" value="更新" name="edit_regist_button">
      <a href="index.php" class="header__button_back">戻る</a>
    </form>

  </div>

</body>

</html>