<?php
include '../../config.php';
session_start();

$_SESSION['step3'] = $_POST;

header('Location: preview.php');
exit;
