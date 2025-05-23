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
  global $deadlineDate, $content, $taskStatus, $priority, $row, $columns, $createUserName, $managerName;

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

  $stmt = $mysqli->prepare("SELECT U.name AS name FROM t_todo T INNER JOIN users U ON T.manager_id = U.id WHERE task_id = ?");
  $stmt->bind_param("i", $taskId);
  $stmt->execute();
  $result = $stmt->get_result();
  if (!$result) {
    die("クエリの実行に失敗しました: " . $mysqli->error);
  }
  $data = $result->fetch_assoc();
  $managerName = $data['name'] ?? null;


  $stmt = $mysqli->prepare("SELECT U.id AS user_id, U.name AS name,M.create_timestamp AS create_timestamp, M.update_timestamp AS update_timestamp, M.memo AS memo, M.memo_id AS memo_id
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
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>







  <div class="bg-blue-500 min-h-screen flex justify-center items-center">
    <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2
              bg-white rounded-lg shadow-lg w-[calc(100vw-4rem)] h-[calc(100vh-4rem)] p-8">
      <div
        class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gray-100 border border-gray-300 rounded-lg shadow-lg w-[calc(100vw-8rem)] h-[calc(100vh-8rem)] flex">
        <div class="bg-sky-500 rounded-lg shadow-lg h-full p-2  w-1/6">
          <div class="border-b border-sky-400 pb-2">
            <?php if (isset($_SESSION["username"])) : ?>
              <p class="text-sm font-medium text-white">ユーザーID</p>
              <p class="text-white bg-sky-500 font-bold">
                <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, "UTF-8") ?></p>
            <?php endif; ?>
          </div>
          <div class="border-b border-sky-400 py-2">
            <a href="index.php" class="text-sm font-medium text-white transition hover:opacity-30">タスク一覧</a>
          </div>
          <div class="border-b border-sky-400 py-2">
            <a href="new_regist.php" class="text-sm font-medium text-white transition hover:opacity-30">タスクの追加</a>
          </div>
          <div class="border-b border-sky-400 py-2">
            <form action=" output_csv.php" method="post">
              <button type="submit" class="text-sm font-medium text-white transition hover:opacity-30"
                name="output_csv">csvエクスポート
              </button>
            </form>
          </div>
          <div class="border-b border-sky-400 py-2">
            <form action="logout.php" method="post">
              <button class="text-sm font-medium text-white transition hover:opacity-30"
                name="logout_button">ログアウト</button>
            </form>
          </div>
        </div>
        <div class="p-8 flex flex-col h-full w-full overflow-auto">
          <p class="text-2xl font-bold mb-6 text-gray-800">タスク詳細</p>
          <div class="bg-white border-b border-gray-100 px-2 py-4 grid grid-cols-1 gap-4 items-center">
            <p>締め切り日付</p>
            <p><?php echo htmlspecialchars($row['deadline_date'], ENT_QUOTES, 'UTF-8') ?>
            </p>
          </div>
          <div class="bg-white border-b border-gray-100 px-2 py-4 grid grid-cols-1 gap-4 items-center">
            <p>内容</p>
            <p><?php echo htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8') ?>
            </p>
          </div>
          <div class="bg-white border-b border-gray-100 px-2 py-4 grid grid-cols-1 gap-4 items-center">
            <p>進捗状況</p>
            <p><?php echo htmlspecialchars($row['task_status'], ENT_QUOTES, 'UTF-8') ?>
            </p>
          </div>
          <div class="bg-white border-b border-gray-100 px-2 py-4 grid grid-cols-1 gap-4 items-center">
            <p>優先度</p>
            <p><?php echo htmlspecialchars($row['priority'], ENT_QUOTES, 'UTF-8') ?>
            </p>
          </div>
          <div class="bg-white border-b border-gray-100 px-2 py-4 grid grid-cols-1 gap-4 items-center">
            <p>作成者</p>
            <p><?php echo htmlspecialchars($createUserName['name'], ENT_QUOTES, 'UTF-8') ?>
            </p>
          </div>
          <div class="bg-white border-b border-gray-100 px-2 py-4 grid grid-cols-1 gap-4 items-center">
            <p>担当者</p>
            <?php if (is_array($managerName) && $managerName['name'] != null): ?>
              <p><?php echo htmlspecialchars($managerName['name'], ENT_QUOTES, 'UTF-8') ?>
              <?php endif; ?>
              </p>
          </div>

          <p class="text-2xl font-bold mb-6 text-gray-800 pt-8">メモ一覧</p>
          <?php if (!empty($columns)): ?>
            <div>
              <div>
                <?php foreach ($columns as $memo): ?>
                  <div class="memo__item memo__item__explain">
                    <div class="memo__item__content">
                      <p class="memo__item__user__name">
                        <?= "ユーザー名：" . htmlspecialchars($memo['name'], ENT_QUOTES, "UTF-8") ?></p>
                      <p class="memo__item__create__timestamp">
                        <?php echo date("Y年m月d日 H時i分s秒", strtotime($memo['update_timestamp'])); ?>
                        <?php if ($memo['update_timestamp'] != $memo['create_timestamp']) : ?>
                          <span class="is__edit__memo">（編集済み）</span>
                        <?php endif; ?>
                      </p>
                      <div class="memo__item__explain"><?php echo $parsedown->text($memo['memo']); ?>
                      </div>
                    </div>

                    <?php if ($_SESSION['userid'] == $memo['user_id']): ?>
                      <div class="memo__items__operation">
                        <?php if (!$editMemoFlg): ?>
                          <form class="memo__item__change__mode__edit"
                            action="show_detail.php?task_id=<?= urlencode($_GET["task_id"]) ?>" method="post">
                            <input type="hidden" value="<?php echo htmlspecialchars($memo['memo'], ENT_QUOTES, 'UTF-8') ?>"
                              name="memo">
                            <input type="hidden" value="<?php echo htmlspecialchars($memo['memo_id'], ENT_QUOTES, 'UTF-8') ?>"
                              name="memo_id">
                            <input type="hidden" name="task_id"
                              value="<?= htmlspecialchars($_GET["task_id"], ENT_QUOTES, "UTF-8") ?>">
                            <button type="submit" class="change__mode__button__edit__memo"
                              name="change_mode_edit_memo_button">編集</button>
                          </form>
                        <?php endif; ?>

                        <form class="memo__item__delete" action="delete_memo.php" method="post">
                          <input type="hidden" value="<?php echo htmlspecialchars($memo['memo_id'], ENT_QUOTES, 'UTF-8') ?>"
                            name="memo_id">
                          <input type="hidden" name="task_id"
                            value="<?= htmlspecialchars($_GET["task_id"], ENT_QUOTES, "UTF-8") ?>">
                          <button type="submit" class="button__delete__memo" name="delete_memo_button"><i class="fas fa-trash"
                              aria-hidden="true"></i></button>
                        </form>
                      </div>
                    <?php endif; ?>
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
              <input type="hidden" value="<?php echo htmlspecialchars($editMemoId, ENT_QUOTES, 'UTF-8') ?>"
                name="edit_memo_id">
              <input type="hidden" name="task_id" value="<?= htmlspecialchars($_GET["task_id"], ENT_QUOTES, "UTF-8") ?>">
              <button class="edit__memo__button" name="edit_memo">メモを更新</button>
            </form>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</body>

</html>