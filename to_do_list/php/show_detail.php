<?php

require('vendor/autoload.php');
require_once('config.php');
require_once('common_utils.php');

$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
$parsedown->setBreaksEnabled(true);

$editMemoFlg = false;
$editMemoId = "";

function showDetail()
{
  global $deadlineDate, $content, $taskStatus, $priority, $row, $columns, $createUserName;

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
  if ($row['user_id'] != $_SESSION['userid'] && $row['publication_range'] == '非公開') {
    header("Location: index.php");
    exit();
  }

  $stmt = $mysqli->prepare("SELECT U.name AS name FROM t_todo T INNER JOIN users U ON T.user_id = U.id WHERE task_id = ?");
  $stmt->bind_param("i", $taskId);
  $stmt->execute();
  $result = $stmt->get_result();
  if (!$result) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }
  $createUserName = $result->fetch_assoc();


  $stmt = $mysqli->prepare("SELECT U.name AS name, M.update_timestamp AS update_timestamp, M.memo AS memo, M.memo_id AS memo_id
    FROM t_memo M
    INNER JOIN t_todo T ON M.task_id = T.task_id
    INNER JOIN users U ON M.user_id = U.id
    WHERE M.task_id = ?");
  $stmt->bind_param("i", $taskId);
  $stmt->execute();
  $result = $stmt->get_result();
  if (!$result) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }
  $columns = $result->fetch_all(MYSQLI_ASSOC);
  mysqli_close($mysqli);
}

function editModeChange()
{
  global $editMemoFlg, $editMemo, $editMemoId;
  $editMemoFlg = true;
  $editMemo = $_POST["memo"];
  $editMemoId = $_POST["memo_id"];
}

if (array_key_exists("change_mode_edit_memo_button", $_POST) and !empty($_POST["memo_id"])) {
  editModeChange();
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
  <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
  <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
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
    <div class="detail__item">
      <p class="detail__item__title">作成者</p>
      <p class="detail__item__explain"><?php echo htmlspecialchars($createUserName['name'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  </div>

  <p class="memo__title">メモ一覧</p>
  <?php if (!empty($columns)): ?>
    <div class="memo">
      <div class="memo__items">
        <?php foreach ($columns as $memo): ?>
          <div class="memo__item memo__item__explain">
            <div class="memo__item__content">
              <p class="memo__item__user__name">
                <?= "ユーザー名：" . htmlspecialchars($memo['name'], ENT_QUOTES, "UTF-8") ?></p>
              <p class="memo__item__create__timestamp">
                <?php echo date("Y年m月d日 H時i分s秒", strtotime($memo['update_timestamp'])); ?></p>
              <div class="memo__item__explain"><?php echo $parsedown->text($memo['memo']); ?>
              </div>
            </div>

            <div class="memo__items__operation">
              <?php if (!$editMemoFlg): ?>
                <form class="memo__item__change__mode__edit"
                  action="show_detail.php?task_id=<?= urlencode($_GET["task_id"]) ?>" method="post">
                  <input type="hidden" value="<?php echo htmlspecialchars($memo['memo'], ENT_QUOTES, 'UTF-8') ?>" name="memo">
                  <input type="hidden" value="<?php echo htmlspecialchars($memo['memo_id'], ENT_QUOTES, 'UTF-8') ?>"
                    name="memo_id">
                  <input type="hidden" name="task_id" value="<?= htmlspecialchars($_GET["task_id"], ENT_QUOTES, "UTF-8") ?>">
                  <button type="submit" class="change__mode__button__edit__memo"
                    name="change_mode_edit_memo_button">編集</button>
                </form>
              <?php endif; ?>


              <form class="memo__item__delete" action="delete_memo.php" method="post">
                <input type="hidden" value="<?php echo htmlspecialchars($memo['memo_id'], ENT_QUOTES, 'UTF-8') ?>"
                  name="memo_id">
                <input type="hidden" name="task_id" value="<?= htmlspecialchars($_GET["task_id"], ENT_QUOTES, "UTF-8") ?>">
                <button type="submit" class="button__delete__memo" name="delete_memo_button"><i class="fas fa-trash"
                    aria-hidden="true"></i></button>
              </form>
            </div>
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

  <?php if (!$editMemoFlg): ?>
    <form class="regist__new__memo" action="regist_new_memo.php" method="post">
      <textarea class="new_memo_text" name="new_memo_text" id="new_memo_text"></textarea>
      <script>
        const easyMDE = new EasyMDE({
          element: document.getElementById("new_memo_text"),
        });
      </script>
      <input type="hidden" name="task_id" value="<?= htmlspecialchars($_GET["task_id"], ENT_QUOTES, "UTF-8") ?>">
      <button name="regist_new_memo">メモを追加</button>
    </form>
  <?php elseif ($editMemoFlg): ?>
    <form class="edit__memo" action="edit_memo.php" method="post">
      <textarea class="edit_memo_text" name="edit_memo_text"
        id="edit_memo_text"><?php echo htmlspecialchars($editMemo, ENT_QUOTES, 'UTF-8') ?></textarea>
      <script>
        const easyMDE = new EasyMDE({
          element: document.getElementById("edit_memo_text"),
        });
      </script>
      <input type="hidden" value="<?php echo htmlspecialchars($editMemoId, ENT_QUOTES, 'UTF-8') ?>" name="edit_memo_id">
      <input type="hidden" name="task_id" value="<?= htmlspecialchars($_GET["task_id"], ENT_QUOTES, "UTF-8") ?>">
      <button class="edit__memo__button" name="edit_memo">メモを更新</button>
    </form>
  <?php endif; ?>
</body>

</html>