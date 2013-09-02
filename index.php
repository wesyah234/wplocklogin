<?php
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

$howDeep = '../..';
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
$loginPageContents = file_get_contents("$howDeep/wp-login.php");
if (isset($argv[1]) && is_numeric($argv[1])) {
  //echo "I will lock after waiting ".$argv[1]."\n";
  sleep($argv[1]);
  // if 404 not already there, put it there:
  if (strcmp($loginPageContents, $fourohfourPage) === 0) {
   // echo "already locked, do nothing\n ";
  }
  else {
    //echo "not locked, locking\n";
    file_put_contents("$howDeep/wp-login.php", $fourohfourPage);
  }
}
elseif ($_REQUEST['login'] == 1 || $_REQUEST['logout'] == 1 || $_REQUEST['unlockforlogin'] == 1) {
  // copy the good login page to the wp-login.php
  if (strcmp($loginPageContents, $fourohfourPage) === 0) {
    file_put_contents("$howDeep/wp-login.php", file_get_contents($goodLoginPage));
  }
 // else do nothing. as it is already unlocked (ie. first time running)
  //echo "now fire the lock after wait $locktime seconds\n"; 
  if ($_REQUEST['login'] == 1) {
    $locktime = 30;
    exec ("php index.php $locktime  > /dev/null 2>&1 &");
    header("Location:$howDeep/wp-login.php");
  }
  elseif ($_REQUEST['logout'] == 1) {
    $locktime = 30;
    exec ("php index.php $locktime  > /dev/null 2>&1 &");
    header("Location:$howDeep/wp-login.php?action=logout");
  }
  else {

// todo this is not working...
    $locktime = 120;
    exec ("php index.php $locktime  > /dev/null 2>&1 &");
    echo "login page now unlocked for $locktime seconds, click to ";
    echo '<a href="'.$howDeep.'/wp-admin">return to admin</a>';
  }
}
else {
// todo get the site url and add to the title here, so they can bookmark this page
  $servername = $_SERVER['HTTP_HOST'];
  echo "<html><head><title>Secure login and logout for $servername</title></head><body>";
  echo "Another...New version on github you can bookmark this page, then click one of the 2 options to either login or logout</br/>";
  echo "<a href='?login=1'>Click Here to Login</a> ";;
  echo "<a href='?logout=1'>Click Here to Logout</a> ";
  echo "<a href='?unlockforupgrade=1'>Click Here to unlock for an upgrade</a>";
  echo "</body></html>";
// check for and pull updates if the script is updated on github 
  exec ("git pull  > /dev/null 2>&1 &");
}
?>
