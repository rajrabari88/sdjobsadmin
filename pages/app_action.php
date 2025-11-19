<?php
include __DIR__ . '/../config/db.php';

$id = $_GET['id'];
$status = $_GET['status'];

$conn->query("UPDATE applications SET status='$status' WHERE id=$id");

header("Location: applications_list.php");
exit;
