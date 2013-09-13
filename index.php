<?php
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

$howDeep = '..';
include $howDeep.'/wp-includes/version.php';
$goodLoginPage = "goodloginpage.$wp_version";
// ensure that we have a saved version of the good login page:
if (!file_exists($goodLoginPage)) {
  // go get it from the wp svn repo:
  exec("wget --output-document $goodLoginPage http://core.svn.wordpress.org/tags/$wp_version/wp-login.php");
}
if (!file_exists($goodLoginPage)) {
  echo "sorry, we were unable to grab the fresh wp-login.php from svn";
  die();
}
$fourohfourPage = "<?php
    header('HTTP/1.0 404 Not Found');
    header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache'); // HTTP/1.0
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    echo \"<h1>404 Not Found</h1>\";
    echo \"The page that you have requested could not be found.\";
    exit();
?>";
// if file already not there, write out the 404 page to it, so rest
// of logic can continue as is
if (!file_exists("$howDeep/wp-login.php")) {
    file_put_contents("$howDeep/wp-login.php", $fourohfourPage);
}
if ($_REQUEST['ajaxunlock'] == 1 || $_REQUEST['login'] == 1 || $_REQUEST['logout'] == 1 || $_REQUEST['unlockforupgrade'] == 1) {
  if ($_REQUEST['login'] == 1) {
    header("Location:$howDeep/wp-login.php");
  }
  elseif ($_REQUEST['ajaxunlock'] == 1) {
    ignore_user_abort(true);
    echo "ajax unlock running...";
    $file = fopen('locklogin.log', 'a');
    $loginPageContents = file_get_contents("$howDeep/wp-login.php");
    fwrite($file, date('r')."login page unlocked \n");
    // copy the good login page to the wp-login.php
    if (strcmp($loginPageContents, $fourohfourPage) === 0) {
      file_put_contents("$howDeep/wp-login.php", file_get_contents($goodLoginPage));
    }
    sleep(15);
    fwrite($file, date('r')."login page locked \n");
    file_put_contents("$howDeep/wp-login.php", $fourohfourPage);
    fclose($file);
    echo "ajax unlock done...";
  }
  elseif ($_REQUEST['logout'] == 1) {
    header("Location:$howDeep/wp-login.php?action=logout");
  }
}
else {
// todo get the site url and add to the title here, so they can bookmark this page
  $servername = $_SERVER['HTTP_HOST'];
  $upgradInProgressFilename = 'upgradeInProgress';
  if (!file_exists($upgradInProgressFilename)) {
    file_put_contents($upgradInProgressFilename, ' empty ');
    if (file_exists($upgradInProgressFilename)) {
      exec("wget --output-document index.php https://raw.github.com/wesyah234/wplocklogin/master/index.php");
      header("Location:index.php");
    }
    else {
      echo "sorry, unable to upgrade cause I can't write the upgradeinp rogress file to disk";
    }
  }
  else {
    echo "===========";
    unlink($upgradInProgressFilename);
    if (file_exists($upgradInProgressFilename)) {
      echo "unable to delete the upgrade in progress file";
    }
    echo "</b><br/>";
    echo "<html><head><title>Secure login and logout for $servername</title>";

    echo "<script>
    function loadXMLDoc()
{
var xmlhttp;
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject(\"Microsoft.XMLHTTP\");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    }
  }
xmlhttp.open(\"GET\",\"index.php?ajaxunlock=1\",true);
xmlhttp.send();
}
</script>";


    echo "</head><body>";

    echo '<button type="button" onclick="loadXMLDoc()">Unlock</button><br/>';
    echo "Version 1.2 you can bookmark this page, then click one of the 2 options to either login or logout</br/>";
    echo "<br/><b>Status</b>: your login page is currently <b>";
    $loginPageContents = file_get_contents("$howDeep/wp-login.php");
    if (strcmp($loginPageContents, $fourohfourPage) === 0) {
      echo "locked.";
    }
    else {
      echo "unlocked.";
    }

    $file = fopen('locklogin.log', 'a');
    fwrite($file, date('r')." wplocklogin accessed \n");
    fclose($file);
    echo "<br/><br/><a href='?login=1'>Click Here to Login</a> ";
    echo "<a href='?logout=1'>Click Here to Logout</a> ";
    echo "</body></html>";

  }
  //exec ("git pull  > /dev/null 2>&1 &");
}
?>
