<?php	
	if($_SERVER["SERVER_NAME"] == "localhost") {
		require_once($_SERVER["DOCUMENT_ROOT"]."/ivres/beta01/includes/application-top.php");
	} else {
		require_once($_SERVER["DOCUMENT_ROOT"]."projects/ivres/1/includes/application-top.php");
	}

	require_once(SITE_CLASSES_PATH."class.Users.php");
	require_once(SITE_CLASSES_PATH."class.Property.php");
	require_once(SITE_CLASSES_PATH."class.Location.php");
	require_once(SITE_CLASSES_PATH."class.Message.php");

	$locationObj 	= new Location();
	$propertyObj 	= new Property();
	$usersObj 		= new Users();
	$messageObj 	= new Message();

if(isset($_SESSION['ses_user_id']) && $_SESSION['ses_user_id'] !=""){
	$user_id 			= $_SESSION['ses_user_id'];
	$ownerPropertyArr = $propertyObj->fun_getPropertyOwnerArr($user_id);
	if(isset($ownerPropertyArr)&&(count($ownerPropertyArr) > 0)){
		$total_properties = count($ownerPropertyArr);
	} else {
		$total_properties = 0;
	}
	$userInfoArr 		= $usersObj->fun_getUsersInfo($user_id);
	$users_first_name 	= $userInfoArr['user_fname'];
	$users_last_name 	= $userInfoArr['user_lname'];
	$users_email_id 	= $userInfoArr['user_email'];
	$user_full_name 	= $users_first_name." ".$users_last_name;
	$users_password 	= $userInfoArr['user_pass'];
	$country_id 		= $userInfoArr['user_country'];

	$userInboxArr 		= $messageObj->fun_getUserInboxArr($user_id);
	$userOutboxArr 		= $messageObj->fun_getUserOutboxArr($user_id);

}

if($message_id == "" && isset($_GET['msgid']) && $_GET['msgid'] !=""){
	$message_id 		= $_GET['msgid'];
	$messageInfoArr 	= $messageObj->fun_getMessageInfo($message_id);
	$message_subject 	= $messageInfoArr['message_subject'];
	$message_body 		= $messageInfoArr['message_body'];
	$message_received_on= date('F d, Y', strtotime($messageInfoArr['message_created_on']));
	$sender_fname 		= $messageInfoArr['user_fname'];
	$sender_lname 		= $messageInfoArr['user_lname'];
	$sender_full_name 	= $sender_fname." ".$sender_lname;

}
//$messages_total 	= $messageObj->fun_countNewMessageInbox($user_id);
//echo $messageTotal;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $seo_title;?></title>
    <meta name="description" content="<?php echo $seo_description;?>" />
    <meta name="keywords" content="<?php echo $seo_keywords;?>" />
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo tranText('charset'); ?>" />
    <META HTTP-EQUIV="Content-language" CONTENT="<?php echo tranText('lang_iso'); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo SITE_URL; ?>favicon.ico" />
    <link rel="SHORTCUT ICON" href="<?php echo SITE_URL; ?>favicon.ico"/>
    <link href="<?php echo SITE_CSS_INCLUDES_PATH;?>owner.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" language="javascript" src="<?php echo SITE_JS_INCLUDES_PATH;?>common.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo SITE_JS_INCLUDES_PATH;?>dargPop.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo SITE_JS_INCLUDES_PATH;?>dhtmlwindow.js"></script>
	<script language="javascript" type="text/javascript">
        var x, y;
        function show_coords(event){	
            x=event.clientX;
            y=event.clientY;
            x = x-160;
            y = y+4;
            //alert(x);alert(y);
        }
    </script>
</head>
<body>
<!-- Main Wrapper Starts Here -->
<div id="wrapper">
    <!-- Header Include Starts Here -->
    <div id="header" align="center">
        <?php require_once(SITE_INCLUDES_PATH.'header.php'); ?>
    </div>
    <!-- Header Include End Here -->
	<?php require_once(SITE_INCLUDES_PATH.'holidayadvsearch.php'); ?>
	<?php //require_once(SITE_INCLUDES_PATH.'breadcrumb.php'); ?>
    <div id="pg-wrapper" align="center"><h1 class="page-heading"><?php echo $page_title; ?></h1></div>
    <div id="main"><?php echo $page_discription; ?></div>
</div>
<!-- Main Wrapper End Here -->
<!-- Footer Include Starts Here -->
<div id="footer">
    <?php require_once(SITE_INCLUDES_PATH.'footer.php'); ?>
</div>
<!-- Footer Include End Here -->
</body>
</html>
