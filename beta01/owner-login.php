<?php
	if($_SERVER["SERVER_NAME"] == "localhost") {
		require_once($_SERVER["DOCUMENT_ROOT"]."/ivres/beta01/includes/application-top.php");
	} else {
		require_once($_SERVER["DOCUMENT_ROOT"]."projects/ivres/1/includes/application-top.php");
	}
	require_once(SITE_CLASSES_PATH."class.Users.php");
	require_once(SITE_CLASSES_PATH."class.UserSetting.php");
	require_once(SITE_CLASSES_PATH."class.CMS.php");
	require_once(SITE_CLASSES_PATH."class.Location.php");
	require_once(SITE_CLASSES_PATH."class.Property.php");
	require_once(SITE_CLASSES_PATH."class.Product.php");
	require_once(SITE_CLASSES_PATH."class.Email.php");
	
	$usersObj 		= new Users();
	$cmsObj         = new Cms();
	$userSettingObj	= new UserSetting();
	$productObj		= new Product();
	$propertyObj 	= new Property();
	$locationObj 	= new Location();

	if(isset($_SESSION['ses_user_id']) && $_SESSION['ses_user_id'] !=""){ // login then redirect to its home page
		redirectURL($_SESSION['ses_user_home']);
	} else {
		$user_id = "";
	}

	// Form submission
	$form_array 	= array();
	$errorMsg 		= 'no';
	
	if($_POST['securityKey']==md5("NEWREGISTRATION")) {
		if(trim($_POST['txtUserFName']) == '') {
			$form_array['txtUserFName'] = 'First Name required';
			$errorMsg = 'yes';
		}
		if(trim($_POST['txtUserLName']) == '') {		
			$form_array['txtUserLName'] = 'Last Name required';
			$errorMsg = 'yes';
		}
		if($_POST['txtUserEmail'] == '') {
			$form_array['txtUserEmail'] = 'Please enter a valid email address';
			$errorMsg = 'yes';
		} else {
			if(preg_match(EMAIL_ID_REG_EXP_PATTERN, $_POST['txtUserEmail'])) {
				// Check if email already exist
				if($usersObj->fun_checkEmailAddress($_POST['txtUserEmail']) === true) {
					$form_array['txtUserEmail'] = 'Email address already exists <strong>Sign in</strong>';
					$errorMsg = 'yes';
				}
			} else {
				$form_array['txtUserEmail'] = 'Please enter a valid email address';
				$errorMsg = 'yes';
			}
		}

		if((trim($_POST['txtUserPasswrd']) == '') || (strlen($_POST['txtUserPasswrd']) < 6)) {
			$form_array['txtUserPasswrd'] = 'Minimum of 6 character password required';
			$errorMsg = 'yes';
		}

		if((trim($_POST['txtConfirmPassword']) == '') || (trim($_POST['txtConfirmPassword']) != trim($_POST['txtUserPasswrd']))){
			$form_array['txtConfirmPassword'] = 'Please confirm your password';
			$errorMsg = 'yes';
		}


		if(isset($_POST['txtIsOwner']) && (trim($_POST['txtIsOwner']) == '1')) {
			if($_POST['txtDOBDay'] == ''){		
				$form_array['txtDOBDay'] = 'Please enter a date';
				$errorMsg = 'yes';
			}
			if($_POST['txtDOBDay'] != '' && $_POST['txtDOBMonth'] == ''){
				$form_array['dobVal'] = 'Please enter a month';
				$errorMsg = 'yes';
			}
			if($_POST['txtDOBDay'] != '' && $_POST['txtDOBMonth'] != '' && $_POST['txtDOBYear'] == ''){
				$form_array['dobVal'] = 'Please enter a year';
				$errorMsg = 'yes';
			}
			if($_POST['txtDOBDay'] != '' && $_POST['txtDOBMonth'] != '' && $_POST['txtDOBYear'] != ''){
				$yyyy	= $_POST['txtDOBYear'];
				$mm		= $_POST['txtDOBMonth'];
				$dd		= $_POST['txtDOBDay'];
				$chkDob = fun_check_date($yyyy, $mm, $dd);
				if($chkDob['code'] == false){
					$form_array['dobVal'] = $chkDob['codemsg'];			
					$errorMsg = 'yes';
				}
			}	
			if($_POST['txtRCountry'] == ''){
				$form_array['txtRCountry'] = 'Please select your country';
				$errorMsg = 'yes';
			}
		}

		if($_SESSION['security_code'] != $_POST['image_vcode']){
			$form_array['image_vcode'] = "Codes must match!";
			$errorMsg = 'yes';
		}

		if(($errorMsg == 'no') && ($errorMsg != 'yes')){		
			// register as owner
			$txtUserFName 	= trim($_POST['txtUserFName']);
			$txtUserLName 	= trim($_POST['txtUserLName']);
			$txtUserEmail 	= trim($_POST['txtUserEmail']);
			$txtUserPasswrd	= trim($_POST['txtUserPasswrd']);
			if(isset($_POST['txtIsOwner']) && (trim($_POST['txtIsOwner']) == '1')) {
				$txtDOBDay 		= trim($_POST['txtDOBDay']);
				$txtDOBMonth 	= trim($_POST['txtDOBMonth']);
				$txtDOBYear 	= trim($_POST['txtDOBYear']);
				$txtAddress1 	= trim($_POST['txtAddress1']);
				$txtAddress2 	= trim($_POST['txtAddress2']);
				$txtTown 		= trim($_POST['txtTown']);
				$txtState 		= trim($_POST['txtState']);
				$txtZip 		= trim($_POST['txtZip']);
				$txtCountry 	= trim($_POST['txtCountry']);
				$txtRCountry 	= trim($_POST['txtRCountry']);
			} else {
				$txtDOBDay 		= "";
				$txtDOBMonth 	= "";
				$txtDOBYear 	= "";
				$txtAddress1 	= "";
				$txtAddress2 	= "";
				$txtTown 		= "";
				$txtState 		= "";
				$txtZip 		= "";
				$txtCountry 	= "";
				$txtRCountry 	= "";
			}
			$txtIsOwner 	= trim($_POST['txtIsOwner']);
			$user_id 		= $usersObj->fun_registerUser($txtUserEmail, $txtUserPasswrd, $txtUserFName, $txtUserLName, $txtUserEmail, $txtDOBDay, $txtDOBMonth, $txtDOBYear, $txtAddress1, $txtAddress2, $txtTown, $txtState, $txtZip, $txtCountry, $txtRCountry, $txtIsOwner);
			if($user_id != "") {
				if($txtIsOwner == "1") {
					// for contact numbers
					$txtContactNumberType 	= $_POST['txtContactNumberType'];
					$txtContactCountry 		= $_POST['txtContactCountry'];
					$txtContactNumber 		= $_POST['txtContactNumber'];
					$usersObj->fun_updateUserContactNumbers($user_id, $txtContactNumberType, $txtContactCountry, $txtContactNumber);				
					// for contact languages
					$txtContactLanguage 	= $_POST['txtContactLanguage'];
					$usersObj->fun_updateUserContactLanguages($user_id, $txtContactLanguage);
				}

				if(isset($_POST['products_id']) && $_POST['products_id'] != "") {
					$products_id 	= $_POST['products_id'];
					$ownerPackage 	= $propertyObj->fun_checkOwnerPackageCredit($user_id, $products_id);
					if($ownerPackage == false) {
						$package_id 		= $productObj->fun_getPackageIdByProductId($products_id);
						$products_price 	= $productObj->fun_getProductPrice($products_id);
						$products_quantity  = 1;
						$propertyObj->fun_addOwnerPackage($user_id, $package_id);
						$propertyObj->fun_addProductUserBasket($user_id, $products_id, $products_quantity, $products_price);
					}
				}

				// For user settings
				if(isset($_POST['txtUserSetting']) && count($_POST['txtUserSetting']) > 0) {
					$usersObj->fun_updateUserSettings($user_id, $_POST['txtUserSetting']);
				}
				$_SESSION['registraton_id'] 	= $user_id;
				$_SESSION['registraton_pass'] 	= $txtUserPasswrd;

				if($usersObj->sendActivationEmailToUserNew($user_id)) {
					redirectURL("registration2.php");
				}
				/*
				if($txtIsOwner == "1") {
					redirectURL("owner-home.php");
				} else {
					redirectURL("home.php");
				}
				*/
			} else {
				redirectURL("owner-login");
			}
		} else {
			$form_array['error_msg'] = "Please submit your form again!";
		}
	}

	$errorMsg 		= "";
	$errorArray 	= array();
	$errorArray['name_error'] 		= '';
	$errorArray['password_error'] 	= '';
	$referpage = SITE_URL;
	if($_POST['securityKey'] == md5("USERLOGIN")){
		if(trim($_POST['user_name']) == ''){
			$errorArray['name_error'] = 'Username required';
		}
		if(trim($_POST['user_password']) == ''){
			$errorArray['password_error'] = 'Password required';
		}
		if(trim($_POST['user_name']) != '' && trim($_POST['user_password']) != ''){
			$userName 		= $_POST['user_name'];
			$userPassword 	= $_POST['user_password'];
			if($usersObj->fun_verifyUsers($userName, $userPassword)){			
				$usersDets = $usersObj->fun_getUsersInfo(0, $userName);
				if($usersDets["user_status"] == "1"){
					$_SESSION['ses_user_id'] 	= $usersDets["user_id"];
					$_SESSION['ses_user_fname'] = $usersDets["user_fname"];
					$_SESSION['ses_user_email'] = $usersDets["user_email"];
					$_SESSION['ses_user_pass'] 	= $usersDets["user_pass"];
					if(isset($usersDets["is_owner"]) && ($usersDets["is_owner"]=="1")){
						$_SESSION['ses_user_home'] = SITE_URL."owner-home";
						if(isset($_POST['products_id']) && $_POST['products_id'] != ""){
							$products_id 	= $_POST['products_id'];
							$ownerPackage 	= $propertyObj->fun_checkOwnerPackageCredit($user_id, $products_id);
							if($ownerPackage == false) {
								$package_id 		= $productObj->fun_getPackageIdByProductId($products_id);
								$products_price 	= $productObj->fun_getProductPrice($products_id);
								$products_quantity  = 1;
								$propertyObj->fun_addOwnerPackage($usersDets["user_id"], $package_id);
								$propertyObj->fun_addProductUserBasket($usersDets["user_id"], $products_id, $products_quantity, $products_price);
								redirectURL(SITE_URL."owner-shopping-cart");
							} else if($ownerPackage == true) { // other selected package active
								//echo "Already an active package exist!";
								//sleep(5);
								redirectURL(SITE_URL."add-a-property");
							}
						} else if(($referpage != "") || ($referpage == "index.php")){
							$referpage = $_SESSION['ses_user_home'];
						} else {
							$referpage = SITE_URL."owner-home";
						}
					} else {
						$_SESSION['ses_user_home'] = SITE_URL."home";
						if(($referpage != "") || ($referpage == "index.php")){
							$referpage = SITE_URL."home";
						}
					}
					redirectURL($referpage);
				}
			} else {
				$errorMsg = "Invalid Username or Password!";
			}
		}
	}

	// Page details
	$page_id  				= 10;
	$pageInfo 				= $cmsObj->fun_getPageInfo($page_id);
    $page_title 			= fun_db_output($pageInfo['page_title']);
    $page_content_title 	= fun_db_output($pageInfo['page_content_title']);
    $page_discription 		= fun_db_output($pageInfo['page_discription']);    $seo_title 				= ($pageInfo['page_seo_title']!="")?$pageInfo['page_seo_title']:$seo_title;
    $seo_keywords 			= ($pageInfo['page_seo_keyword']!="")?$pageInfo['page_seo_keyword']:$seo_keywords;
    $seo_description 		= ($pageInfo['page_seo_discription']!="")?$pageInfo['page_seo_discription']:$seo_description;
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
    <script type="text/javascript" language="javascript" src="<?php echo SITE_JS_INCLUDES_PATH;?>owner.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo SITE_JS_INCLUDES_PATH;?>dargPop.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo SITE_JS_INCLUDES_PATH;?>dhtmlwindow.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo SITE_JS_INCLUDES_PATH;?>pop-up.js"></script>
    <script type="text/javascript" language="javascript">
  		var x, y;
		function show_coords(event){	
			x=event.clientX;
			y=event.clientY;
			x = x-160;
			y = y+4;
		//	alert(x);alert(y);
		}

		function validateLogin(){
			if(document.getElementById("user_name").value == "") {
				document.getElementById("showErrorUserNameId").innerHTML = "Username required";
				document.getElementById("user_name").focus();
				return false;
			}

			if(document.getElementById("user_password").value == "") {
				document.getElementById("showErrorPasswordId").innerHTML = "Password required";
				document.getElementById("user_password").focus();
				return false;
			}

			if(document.getElementById("user_password").value != "") {
				var txt = document.getElementById("user_password").value;
				if(txt.length < 6){
					document.getElementById("showErrorPasswordId").innerHTML = "Minimum of 6 char password required";
					document.getElementById("user_password").focus();
					return false;
				}
			}

			document.frmLogin.submit();
		}

        function cancelLogin() {
            window.location = 'index.php';
        }

	    function showRow(strId){
            var strId = strId;
            document.getElementById(strId).style.display = "block";
        }

        function removeContactNumber(strId) {
            var strNumberId = "txtContactNumber"+strId;
            document.getElementById(strNumberId).value = "";
            document.frmPropertyContacts.submit();
        }
        function addEvent() {
            var strTable = "";
            var ni = document.getElementById('myDiv');
            var numi = document.getElementById('theValue');
            var num = (document.getElementById("theValue").value -1)+ 2;
            numi.value = num;
            var divIdName = "my"+num+"Div";
            var strcontype = "<?php $propertyObj->fun_getPropertyContactNoTypeOptionsList(); ?>";
            var strconnamefav = "<?php $propertyObj->fun_getCountriesISDOptionsList('', " WHERE countries_name IN ('United States', 'United Kingdom') ORDER BY countries_name IN ('United States', 'United Kingdom')"); ?>";
            var strconname = "<?php $propertyObj->fun_getCountriesISDOptionsList(); ?>";
            var newdiv = document.createElement('div');
            newdiv.setAttribute("id",divIdName);
            strTable += "<table cellspacing='0' cellpadding='0'>";
            strTable += "<tr>";
            strTable += "<td colspan='4' height='5'>";
            strTable += "</td>";
            strTable += "</tr>";
            strTable += "<tr>";
            strTable += "<td align='left'>";
            strTable += "<select name='txtContactNumberType[]' class='select94'>";
            strTable += "<option value=''>Select Type</option>";
            strTable += strcontype;
            strTable += "</select>";
            strTable += "<td class='pad-lft10'>";
            strTable += "<select name='txtContactCountry[]' id='txtContactCountryId' class='select128'>";
            strTable += "<option value='' style='font-style:normal; background-color:#D0E0F1;COLOR:#000000' disabled='disabled' selected='selected'>Select Country ...</option>";
            strTable += strconnamefav;
            strTable += "<option value='' style='font-style:normal;background-color:#D0E0F1;COLOR:#000000' disabled='disabled'> ---------------------------------------------- </option>";
            strTable += strconname;
            strTable += "</select>";
            strTable += "</td>";
            strTable += "<td class='pad-lft10'><input type='text' name='txtContactNumber[]' class='txtBox160' maxlength='15' /></td>";
            strTable += "<td class='pad-lft10 pad-top1' valign='middle'><a href=\"javascript:;\" class='delete-contact' onclick=\"removeElement(\'"+divIdName+"\')\">Delete</a></td>";
            strTable += "</tr>";
            strTable += "</table>";
            newdiv.innerHTML = strTable;
            ni.appendChild(newdiv);
        }
        function removeElement(divNum) {
            var d = document.getElementById('myDiv');
            var olddiv = document.getElementById(divNum);
            d.removeChild(olddiv);
        }
        function addEvent1() {
            var strTable1 = "";
            var ni = document.getElementById('myDiv1');
            var numi = document.getElementById('theValue');
            var num = (document.getElementById("theValue").value -1)+ 2;
            numi.value = num;
            var divIdName = "my"+num+"Div";
            var strlang = "<?php $usersObj->fun_getLanguagesOptionsList(); ?>";
            var newdiv = document.createElement('div');
            newdiv.setAttribute("id",divIdName);
            strTable1 += "<table cellspacing='0' cellpadding='0'>";
            strTable1 += "<tr>";
            strTable1 += "<td height='5'>";
            strTable1 += "</td>";
            strTable1 += "</tr>";
            strTable1 += "<tr>";
            strTable1 += "<td align='left'>";
            strTable1 += "<select name='txtContactLanguage[]' class='select230'>";
            strTable1 += "<option value='' style='font-style:normal; background-color:#D0E0F1;COLOR:#000000' disabled='disabled' selected='selected'>Select Language ...</option>";
            strTable1 += strlang;
            strTable1 += "</select>";
            strTable1 += "</td>";
            strTable1 += "<td class='pad-lft10 pad-top1' valign='middle'> <a href=\"javascript:void(0);\" class='delete-language' onclick=\"removeElement1(\'"+divIdName+"\')\">Delete</a></td>";
            strTable1 += "</tr>";
            strTable1 += "</table>";
            newdiv.innerHTML = strTable1;
            ni.appendChild(newdiv);
        }
    
        function removeElement1(divNum) {
            var d = document.getElementById('myDiv1');
            var olddiv = document.getElementById(divNum);
            d.removeChild(olddiv);
        }

		function validateRegister(){
			if(document.getElementById("txtUserFNameId").value == "") {
				document.getElementById("showErrorUserFNameId").innerHTML = "First Name required";
				document.getElementById("txtUserFNameId").focus();
				return false;
			}

			if(document.getElementById("txtUserLNameId").value == "") {
				document.getElementById("showErrorUserLNameId").innerHTML = "Last Name required";
				document.getElementById("txtUserLNameId").focus();
				return false;
			}

			if(document.getElementById("txtUserEmailId").value == "") {
				document.getElementById("showErrorUserEmailId").innerHTML = "Enter valid email address";
				document.getElementById("txtUserEmailId").focus();
				return false;
			} else {
				var emailRegxp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				var txtemail = document.getElementById("txtUserEmailId").value;
				if (emailRegxp.test(txtemail)!= true){
					document.getElementById("showErrorUserEmailId").innerHTML = "Enter valid email address";
					document.getElementById("txtUserEmailId").value = "";
					document.getElementById("txtUserEmailId").focus();
					return false;
				}
			}

			if(document.getElementById("txtUserPasswrdId").value == "") {
				document.getElementById("showErrorPassword").innerHTML = "Password required";
				document.getElementById("txtUserPasswrdId").focus();
				return false;
			}

			if(document.getElementById("txtUserPasswrdId").value != "") {
				var txt = document.getElementById("txtUserPasswrdId").value;
				if(txt.length < 6){
					document.getElementById("showErrorPassword").innerHTML = "Minimum of 6 character password required";
					document.getElementById("txtUserPasswrdId").focus();
					return false;
				}
			}

			if(document.getElementById("txtTermsId").checked == false) {
				document.getElementById("showErrorTerms").innerHTML = "<font style='color:#FF0000;'>Need to check</font>";
				document.getElementById("txtTermsId").focus();
				return false;
			}

			document.frmUserProfile.submit();
		}

		function setUserType(strId) {
			var strOwnerId = "txtOwnerId";
			var strHolidayId = "txtHolidayId";
			var txtIsOwnerId = "txtIsOwnerId";
			switch(strId) {
				case 0:
					if(document.getElementById(strHolidayId).checked == true) {
						document.getElementById(txtIsOwnerId).value = document.getElementById(strHolidayId).value;
						document.getElementById(strOwnerId).checked = "";
						if(document.getElementById("ownerMsgId")) {
							document.getElementById("ownerMsgId").style.display = "none";
						}
						if(document.getElementById("ownerDisplayId")) {
							document.getElementById("ownerDisplayId").style.display = "none";
						}
						document.getElementById("ownerTxtId").style.display = "none";
						document.getElementById("holidayTxtId").style.display = "block";
					} else {
						document.getElementById(txtIsOwnerId).value = document.getElementById(strOwnerId).value;
						document.getElementById(strOwnerId).checked = "checked";
						if(document.getElementById("ownerMsgId")) {
							document.getElementById("ownerMsgId").style.display = "block";
						}
						if(document.getElementById("ownerDisplayId")) {
							document.getElementById("ownerDisplayId").style.display = "block";
						}
						document.getElementById("ownerTxtId").style.display = "block";
						document.getElementById("holidayTxtId").style.display = "none";
					}
				break;
				case 1:
					if(document.getElementById(strOwnerId).checked == true) {
						document.getElementById(txtIsOwnerId).value = document.getElementById(strOwnerId).value;
						document.getElementById(strHolidayId).checked = "";
						if(document.getElementById("ownerMsgId")) {
							document.getElementById("ownerMsgId").style.display = "block";
						}
						if(document.getElementById("ownerDisplayId")) {
							document.getElementById("ownerDisplayId").style.display = "block";
						}
						document.getElementById("ownerTxtId").style.display = "block";
						document.getElementById("holidayTxtId").style.display = "none";
					} else {
						document.getElementById(txtIsOwnerId).value = document.getElementById(strHolidayId).value;
						document.getElementById(strHolidayId).checked = "checked";
						if(document.getElementById("ownerMsgId")) {
							document.getElementById("ownerMsgId").style.display = "none";
						}
						if(document.getElementById("ownerDisplayId")) {
							document.getElementById("ownerDisplayId").style.display = "none";
						}
						document.getElementById("ownerTxtId").style.display = "none";
						document.getElementById("holidayTxtId").style.display = "block";
					}
				break;
			}
		}
    
        function cancelRegistration() {
            window.location = 'index.php';
        }

        function chkblnkTxtError(strFieldId, strErrorFieldId) {
            if(document.getElementById(strFieldId).value != "") {
                document.getElementById(strErrorFieldId).innerHTML = "";
            }
        }
    </script>
</head>
<body onmousedown="show_coords(event);">
<!-- Main Wrapper Starts Here -->
<div id="wrapper">
    <!-- Header Include Starts Here -->
    <div id="header" align="center">
        <?php require_once(SITE_INCLUDES_PATH.'header.php'); ?>
    </div>
    <!-- Header Include End Here -->
	<?php require_once(SITE_INCLUDES_PATH.'holidayadvsearch.php'); ?>
	<?php //require_once(SITE_INCLUDES_PATH.'breadcrumb.php'); ?>
    <div id="pg-wrapper" align="center"><h1 class="page-heading">Owner</h1></div>
    <div id="main">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="pad-top10">
            <tr>
                <td width="49%" align="left" valign="top" style="background-color:#efefef; padding:5px 5px 10px 10px;">
                    <?php require_once(SITE_INCLUDES_PATH.'ownerregister.php'); ?>
                </td>
                <td>&nbsp;</td>
                <td width="49%" align="right" valign="top" class="font12" style="background-color:#efefef; padding:5px 5px 10px 10px;">
					<?php require_once(SITE_INCLUDES_PATH.'owner-loginform.php'); ?>
                </td>
            </tr>
        </table>
    </div>
</div>
<!-- Main Wrapper End Here -->
<!-- Footer Include Starts Here -->
<div id="footer">
    <?php require_once(SITE_INCLUDES_PATH.'footer.php'); ?>
</div>
<!-- Footer Include End Here -->
</body>
</html>
