<?php
include '../config.php';
session_start();

$_SESSION['step1'] = $_POST;

if ($_POST['status'] === 'Yes') {
    header('Location: preview.php');
} else {
    header('Location: step-2.php');
}
exit;
    