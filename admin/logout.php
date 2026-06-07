<?php
require_once '../includes/auth.php';
destroySession();
header('Location: login.php');
exit;
