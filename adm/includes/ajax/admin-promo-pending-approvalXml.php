<?php
session_start();
header('Content-Type: text/xml');
header("Cache-Control: no-cache, must-revalidate");
//A date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

require_once("../../includes/common.php");
require_once("../../includes/database-table.php");
require_once("../../includes/classes/class.DB.php");
require_once("../../includes/functions/general.php");

$dbObj = new DB();
$dbObj->fun_db_connect();

$strXml ="";
$strXmlContent ="";
if(isset($_GET['promoid']) && $_GET['promoid'] !=""){
	$promo_id	= $_GET['promoid'];
	$mode		= $_GET['mode']; //approve
	$cur_unixtime	= time ();
	if(isset($_SESSION['ses_admin_id']) && $_SESSION['ses_admin_id'] !=""){
		$cur_user_id 	= $_SESSION['ses_admin_id'];
	} else if(isset($_SESSION['ses_modarator_id']) && $_SESSION['ses_modarator_id'] !="") {
		$cur_user_id 	= $_SESSION['ses_modarator_id'];
	} else {
		$cur_user_id 	= "";
	}
	switch($mode){
		case 'approve':
			$active 		= 1;
			$strUpdateQuery = "UPDATE ".TABLE_PROMOS." SET active='$active' WHERE promo_id='".$promo_id."'";
			if($dbObj->mySqlSafeQuery($strUpdateQuery) == true){
				$strXmlContent .="<promo>";
				$strXmlContent .="<promostatus>Approved</promostatus>\n";
				$strXmlContent .="</promo>";
			}
		break;
		case 'decline':
			$active 		= 0;
			$strUpdateQuery = "UPDATE ".TABLE_PROMOS." SET active='$active' WHERE promo_id='".$promo_id."'";
			if($dbObj->mySqlSafeQuery($strUpdateQuery) == true){
				$strXmlContent .="<promo>";
				$strXmlContent .="<promostatus>Declined</promostatus>\n";
				$strXmlContent .="</promo>";
			}
		break;
		case 'suspend':
			$active 		= 0;
			$strUpdateQuery = "UPDATE ".TABLE_PROMOS." SET active='$active' WHERE promo_id='".$promo_id."'";
			if($dbObj->mySqlSafeQuery($strUpdateQuery) == true){
				$strXmlContent .="<promo>";
				$strXmlContent .="<promostatus>Suspended</promostatus>\n";
				$strXmlContent .="</promo>";
			}
		break;
	}
} else {
	$strXmlContent .="<promo>";
	$strXmlContent .="<promostatus>Error.</promostatus>\n";
	$strXmlContent .="</promo>";
}

$strXml ="";
$strXml .='<?xml version="1.0" encoding="ISO-8859-1"?><promos>';
$strXml .=$strXmlContent;
$strXml .='</promos>';
echo $strXml;
?>
