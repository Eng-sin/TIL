<?php
require_once('config.php');
require_once('common_utils.php');

// DB接続とユーザー一覧取得
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
  die("データベースの接続に失敗しました: " . $mysqli->connect_error);
}
$sql = "SELECT * FROM users";
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
$manager = "";

$errorMessageDeadLineDate = "";
$errorMessageContent = "";
$errorMessageTaskStatus = "";
$errorMessagePriority = "";
$errorMessagePublicationRange = "";
$errorMessageManager = "";

function edit()
{
  global $taskId, $deadlineDate, $content, $taskStatus, $priority, $publicationRange, $managerId, $manager;
  $taskId = $_POST["task_id"];
  $deadlineDate = $_POST["deadline_date"];
  $content = $_POST["content"];
  $taskStatus = $_POST["task_status"];
  $priority = $_POST["priority"];
  $publicationRange = $_POST["publication_range"];
  $managerId = $_POST["manager_id"] ?? null;

  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if (mysqli_connect_error()) {
    die("データベースの接続に失敗しました");
  }

  if ($managerId !== null) {
    $stmt = $mysqli->prepare("SELECT U.name FROM t_todo T INNER JOIN users U ON T.manager_id = U.id WHERE T.manager_id = ? LIMIT 1");
    $stmt->bind_param("s", $managerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $manager = $data['name'] ?? '';
  }
}

function edit_regist()
{
  global $taskId, $deadlineDate, $content, $taskStatus, $priority, $publicationRange, $manager,
    $errorMessageDeadLineDate, $errorMessageContent, $errorMessageTaskStatus, $errorMessagePriority,
    $errorMessagePublicationRange, $errorMessageManager;

  $hasError = false;

  $taskId = $_POST["task_id"] ?? "";
  $deadlineDate = $_POST["deadline_date"] ?? "";
  $content = $_POST["content"] ?? "";
  $taskStatus = $_POST["task_status"] ?? "";
  $priority = $_POST["priority"] ?? "";
  $publicationRange = $_POST["publication_range"] ?? "";
  $manager = $_POST["manager"] ?? "";

  if (empty($deadlineDate)) {
    $errorMessageDeadLineDate = "日付を入力してください";
    $hasError = true;
  }
  if (empty($content)) {
    $errorMessageContent = "内容を入力してください";
    $hasError = true;
  }
  if (empty($taskStatus)) {
    $errorMessageTaskStatus = "進捗状況を入力してください";
    $hasError = true;
  }
  if (empty($priority)) {
    $errorMessagePriority = "優先度を選択してください";
    $hasError = true;
  }
  if (empty($publicationRange)) {
    $errorMessagePublicationRange = "公開範囲を選択してください";
    $hasError = true;
  }

  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if (mysqli_connect_error()) {
    die("データベースの接続に失敗しました");
  }

  $managerId = null;
  if (!empty($manager)) {
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $manager);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if (!$result) {
      $errorMessageManager = "そのユーザー名のユーザーは存在しません";
      $hasError = true;
    } else {
      $managerId = $result["id"];
    }
  }

  if ($hasError) {
    return;
  }

  $stmt = $mysqli->prepare("UPDATE T_TODO SET deadline_date=?, content=?, task_status=?, priority=?, publication_range=?, manager_id=?, update_timestamp=CURRENT_TIMESTAMP() WHERE task_id=?");
  $stmt->bind_param("sssssii", $deadlineDate, $content, $taskStatus, $priority, $publicationRange, $managerId, $taskId);
  if (!$stmt->execute()) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }

  mysqli_close($mysqli);
  header('Location: http://localhost:3000/index.php');
  exit();
}

if (isset($_POST["edit_button"])) {
  edit();
}
if (isset($_POST["edit_regist_button"])) {
  edit_regist();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>タスク更新</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

  <div class="bg-white p-8 rounded-lg shadow-lg w-[800px]">
    <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">タスク更新</h1>
    <form method="post">
      <input type="hidden" name="task_id" value="<?= htmlspecialchars($taskId, ENT_QUOTES, 'UTF-8') ?>">

      <!-- 締め切り日時 -->
      <?php if ($errorMessageDeadLineDate): ?>
        <div class="bg-red-100 text-red-700 p-2 mb-2 rounded"><?= htmlspecialchars($errorMessageDeadLineDate) ?></div>
      <?php endif; ?>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700"><span class="text-red-700">※</span>締め切り日付</label>
        <input type="date" name="deadline_date"
          class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
          value="<?= htmlspecialchars($deadlineDate, ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <!-- 内容 -->
      <?php if ($errorMessageContent): ?>
        <div class="bg-red-100 text-red-700 p-2 mb-2 rounded"><?= htmlspecialchars($errorMessageContent) ?></div>
      <?php endif; ?>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700"><span class="text-red-700">※</span>内容</label>
        <input type="text" name="content"
          class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
          value="<?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <!-- 進捗状況 -->
      <?php if ($errorMessageTaskStatus): ?>
        <div class="bg-red-100 text-red-700 p-2 mb-2 rounded"><?= htmlspecialchars($errorMessageTaskStatus) ?></div>
      <?php endif; ?>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700"><span class="text-red-700">※</span>進捗状況</label>
        <select name="task_status"
          class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
          <option value="">選択してください</option>
          <option value="未着手" <?= $taskStatus === '未着手' ? 'selected' : '' ?>>未着手</option>
          <option value="進行中" <?= $taskStatus === '進行中' ? 'selected' : '' ?>>進行中</option>
          <option value="完了" <?= $taskStatus === '完了' ? 'selected' : '' ?>>完了</option>
        </select>
      </div>

      <!-- 優先度 -->
      <?php if ($errorMessagePriority): ?>
        <div class="bg-red-100 text-red-700 p-2 mb-2 rounded"><?= htmlspecialchars($errorMessagePriority) ?></div>
      <?php endif; ?>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700"><span class="text-red-700">※</span>優先度</label>
        <select name="priority"
          class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
          <option value="">選択してください</option>
          <option value="高" <?= $priority === '高' ? 'selected' : '' ?>>高</option>
          <option value="中" <?= $priority === '中' ? 'selected' : '' ?>>中</option>
          <option value="低" <?= $priority === '低' ? 'selected' : '' ?>>低</option>
        </select>
      </div>

      <!-- 公開範囲 -->
      <?php if ($errorMessagePublicationRange): ?>
        <div class="bg-red-100 text-red-700 p-2 mb-2 rounded"><?= htmlspecialchars($errorMessagePublicationRange) ?></div>
      <?php endif; ?>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700"><span class="text-red-700">※</span>公開範囲</label>
        <select name="publication_range"
          class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
          <option value="">選択してください</option>
          <option value="公開" <?= $publicationRange === '公開' ? 'selected' : '' ?>>公開</option>
          <option value="非公開" <?= $publicationRange === '非公開' ? 'selected' : '' ?>>非公開（自分のみ）</option>
        </select>
      </div>

      <!-- 担当者 -->
      <?php if ($errorMessageManager): ?>
        <div class="bg-red-100 text-red-700 p-2 mb-2 rounded"><?= htmlspecialchars($errorMessageManager) ?></div>
      <?php endif; ?>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700"><span class="text-red-700">※</span>担当者</label>
        <input type="text" name="manager" list="search-engine"
          class="mt-1 w-full px-4 py-2 border border-gray-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
          value="<?= htmlspecialchars($manager, ENT_QUOTES, 'UTF-8') ?>">
        <datalist id="search-engine">
          <?php foreach ($searchEngineNameList as $row): ?>
            <option value="<?= htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8') ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>

      <div class="flex justify-center mt-6">
        <input type="submit" name="edit_regist_button"
          class="px-4 py-2 text-white bg-sky-500 rounded-md shadow hover:bg-sky-600 mr-4" value=" 更新">
        <a href="index.php" class="block px-4 py-2 text-white bg-gray-500 rounded-md shadow hover:bg-gray-600">戻る</a>
      </div>
    </form>
  </div>

</body>

</html>