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
  <!-- <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/new_regist.css"> -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>

  <div class="bg-gray-100 min-h-screen flex items-center justify-center">


    <div class="bg-white p-8 rounded-lg shadow-lg w-[800px]">
      <h1 class="text-2xl font-bold mb-6 text-center font-gray-800">タスク登録</h1>
      <form method="post">
        <?php if (!empty($errorMessageDeadLineDate)) : ?>
          <p class="bg-red-100 text-red-700 p-4 rounded-md text-sm">
            <?= htmlspecialchars($errorMessageDeadLineDate, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
        <div id="deadlineDateError" class="bg-red-100 text-red-700 p-4 rounded-md text-sm"></div>
        <div class="mb-4">
          <label for="deadline_date" class="block text-sm font-medium text-gray-700"><span
              class="text-red-700">※</span>締め切り日付</label>
          <input
            class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            type="date" name="deadline_date" id="deadlineDate">
        </div>
        <?php if (!empty($errorMessageContent)) : ?>
          <p class="bg-red-100 text-red-700 p-4 rounded-md text-sm">
            <?= htmlspecialchars($errorMessageContent, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
        <div id="contentError" class="bg-red-100 text-red-700 p-4 rounded-md text-sm"></div>
        <div class="mb-4">
          <label for="content" class="block text-sm font-medium text-gray-700"><span
              class="text-red-700">※</span>内容</label>
          <input type="text" name="content" id="content"
            class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        </div>
        <?php if (!empty($errorMessageTaskStatus)) : ?>
          <p class="bg-red-100 text-red-700 p-4 rounded-md text-sm">
            <?= htmlspecialchars($errorMessageTaskStatus, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
        <div id="taskStatusError" class="bg-red-100 text-red-700 p-4 rounded-md text-sm"></div>
        <div class="mb-4">
          <label for="task_status" class="block text-sm font-medium text-gray-700"><span
              class="text-red-700">※</span>進捗状況</label>
          <select name="task_status" id="taskStatus"
            class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">選択してください</option>
            <option value="未着手">未着手</option>
            <option value="進行中">進行中</option>
            <option value="完了">完了</option>
          </select>
        </div>
        <?php if (!empty($errorMessagePriority)) : ?>
          <p class="bg-red-100 text-red-700 p-4 rounded-md text-sm">
            <?= htmlspecialchars($errorMessagePriority, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
        <div id="priorityError" class="bg-red-100 text-red-700 p-4 rounded-md text-sm"></div>
        <div class="mb-4">
          <label for="prority" class="block text-sm font-medium text-gray-700"><span
              class="text-red-700">※</span>優先度</label>
          <select name="priority" id="priority"
            class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">選択してください</option>
            <option value="高">高</option>
            <option value="中">中</option>
            <option value="低">低</option>
          </select>
        </div>
        <?php if (!empty($errorMessagePublicationRange)) : ?>
          <p class="bg-red-100 text-red-700 p-4 rounded-md text-sm">
            <?= htmlspecialchars($errorMessagePublicationRange, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
        <div id="publicationRangeError" class="bg-red-100 text-red-700 p-4 rounded-md text-sm"></div>
        <div class="mb-4">
          <label for="publication_range" class="block text-sm font-medium text-gray-700"><span
              class="text-red-700">※</span>公開範囲</label>
          <select name="publication_range" id="publicationRange"
            class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">選択してください</option>
            <option value="公開">公開</option>
            <option value="非公開">非公開(自分のみ)</option>
          </select>
        </div>
        <?php if (!empty($errorMessageManager)) : ?>
          <p class="bg-red-100 text-red-700 p-4 rounded-md text-sm">
            <?= htmlspecialchars($errorMessageManager, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
        <div class="mb-6">
          <label for="manager" class="block text-sm font-medium text-gray-700">担当者</label>
          <input list="search-engine" type="text" name="manager"
            class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
          <datalist id="search-engine">
            <?php foreach ($searchEngineNameList as $row): ?>
              <option value=<?= htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8") ?>></option>
            <?php endforeach; ?>
          </datalist>
        </div>
        <div class="flex justify-center">
          <input type="submit" value="登録" name="regist_button"
            class=" px-4 py-2 text-white bg-sky-500 rounded-md shadow hover:bg-sky-600 mr-4">
          <a href="index.php" class="block px-4 py-2 text-white bg-gray-500 rounded-md shadow hover:bg-gray-600">戻る</a>
        </div>
      </form>
    </div>
  </div>
  <script src="js/validate.js"></script>
</body>

</html>




<div>
  <div class="bbb"></div>
</div>
<div>
  <div>
    <div id="aaa"></div>
  </div>
</div>