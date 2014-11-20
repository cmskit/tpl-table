<?php
require dirname(dirname(__DIR__)) . '/inc/php/session.php';
//
$projectName = $_GET['project'];
$objectName = $_GET['object'];
$objects = isset($_SESSION['TMP__'.$projectName]['objects'])
				? $_SESSION['TMP__'.$projectName]['objects']
				: $_SESSION[$projectName]['objects'];

if(!isset($objects[$objectName]['acl']) || !$acl = $objects[$objectName]['acl']) exit('OK');
echo json_encode($acl);
?>
