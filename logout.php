<?php
require_once 'includes/functions.php';
startSession();
session_destroy();
header("Location: /eventflow/login.php");
exit();
