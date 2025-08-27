<?php
include '../config.php';
session_start();

$_SESSION['step2'] = $_POST;

if ($_POST['status'] === 'Agreed') {
    header('Location: preview.php');
} else {
    header('Location: step-3.php');
}
exit;
