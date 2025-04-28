<?php
require_once('config.php');
require_once('common_utils.php');
function showDetail()
{
  global $deadlineDate, $content, $taskStatus, $priority, $row, $columns;

  $deadlineDate = "";
  $content = "";
  $taskStatus = "";
  $priority = "";

  if (isset($_GET['task_id'])) {
    $taskId = $_GET['task_id'];
  } else {
    die("タスクIDが指定されていません");
  }

  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if (mysqli_connect_error()) {
    die("データベースの接続に失敗しました");
  }

  $stmt = $mysqli->prepare("SELECT * FROM t_todo WHERE task_id = ?");
  $stmt->bind_param("i", $taskId);
  $stmt->execute();
  $result = $stmt->get_result();
  if (!$result) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }
  $row = $result->fetch_assoc();

  $stmt = $mysqli->prepare("SELECT * FROM t_memo WHERE task_id = ?");
  $stmt->bind_param("i", $taskId);
  $stmt->execute();
  $result = $stmt->get_result();
  if (!$result) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }
  $columns = $result->fetch_all(MYSQLI_ASSOC);
  mysqli_close($mysqli);
}


showDetail();


?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>To Do List</title>
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/detail.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body>
  <header class="header">
    <p class="header__title">タスク詳細</p>
    <a class="back__home" href="index.php">戻る</a>
  </header>
  <div class="detail__items">
    <div class="detail__item">
      <p class="detail__item__title">締め切り日付</p>
      <p class="detail__item__explain"><?php echo htmlspecialchars($row['deadline_date'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="detail__item">
      <p class="detail__item__title">内容</p>
      <p class="detail__item__explain"><?php echo htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="detail__item">
      <p class="detail__item__title">進捗状況</p>
      <p class="detail__item__explain"><?php echo htmlspecialchars($row['task_status'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="detail__item">
      <p class="detail__item__title">優先度</p>
      <p class="detail__item__explain"><?php echo htmlspecialchars($row['priority'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  </div>

  <p class="memo__title">メモ一覧</p>
  <?php if (!empty($columns)): ?>
  <div class="memo">
    <div class="memo__items">
      <?php foreach ($columns as $memo): ?>
      <div class="memo__item">
        <p class="memo__item__explain"><?php echo htmlspecialchars($memo['memo'], ENT_QUOTES, 'UTF-8'); ?></p>
        <form class="memo__item__delete" action="delete_memo.php" method="post">
          <input type="hidden" value="<?php echo htmlspecialchars($memo['memo_id'], ENT_QUOTES, 'UTF-8') ?>"
            name="memo_id">
          <input type="hidden" name="task_id" value="<?= htmlspecialchars($_GET["task_id"], ENT_QUOTES, "UTF-8") ?>">
          <button type="submit" class="button__delete__memo" name="delete_memo_button"><i class="fas fa-trash"
              aria-hidden="true"></i></button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php else: ?>
  <div class="memo__items">
    <div class="memo__item">
      <p class="memo__item__explain">メモはありません。</p>
    </div>
  </div>
  <?php endif; ?>
  <form class="regist__new__memo" action="regist_new_memo.php" method="post">
    <textarea class="new_memo_text" name="new_memo_text"></textarea>
    <input type="hidden" name="task_id" value="<?= htmlspecialchars($_GET["task_id"], ENT_QUOTES, "UTF-8") ?>">
    <button name="regist_new_memo">メモを追加</button>
  </form>
</body>

</html>