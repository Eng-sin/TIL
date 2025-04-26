<?php
require_once('common_utils.php');
if (array_key_exists("logout_button", $_POST)) {
  session_start();
  session_destroy();
  header("Location: login.php");
}