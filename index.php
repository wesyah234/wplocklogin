<?php
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// Read the last $num lines from stream $fp
function read_last_lines($fp, $num)
{
    $idx   = 0;

    $lines = array();
    while(($line = fgets($fp)))
    {
        $lines[$idx] = $line;
        $idx = ($idx + 1) % $num;
    }

    $p1 = array_slice($lines,    $idx);
    $p2 = array_slice($lines, 0, $idx);
    $ordered_lines = array_merge($p1, $p2);

    return $ordered_lines;
}

$howDeep = '..';
include $howDeep.'/wp-includes/version.php';
$goodLoginPage = "goodloginpage.$wp_version";
// ensure that we have a saved version of the good login page:
if (!file_exists($goodLoginPage)) {
  // go get it from the wp svn repo:
  exec("wget --no-check-certificate --output-document $goodLoginPage http://core.svn.wordpress.org/tags/$wp_version/wp-login.php");
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
if (isset(\$_REQUEST['action']) && \$_REQUEST['action'] == 'logout') {
  include 'wp-load.php';
  wp_logout();
  wp_safe_redirect('/');
}
else {
  echo '<h1>404 Not Found</h1>';
  echo 'The page that you have requested could not be found.';
}
?>";
// if file already not there, write out the 404 page to it, so rest
// of logic can continue as is
if (!file_exists("$howDeep/wp-login.php")) {
    file_put_contents("$howDeep/wp-login.php", $fourohfourPage);
}
if ($_REQUEST['ajaxunlock'] == 1 || $_REQUEST['login'] == 1 || $_REQUEST['logout'] == 1 || $_REQUEST['unlockforupgrade'] == 1) {
  if ($_REQUEST['login'] == 1) {
    header("Location:$howDeep/wp-admin");
  }
  elseif ($_REQUEST['ajaxunlock'] == 1) {
    ignore_user_abort(true);
    echo "ajax unlock running...";
    $file = fopen('locklogin.log', 'a');
        $loginPageContents = file_get_contents("$howDeep/wp-login.php");
    $seconds = 60;
    fwrite($file, date('r')." login page unlocked for $seconds seconds from IP: ".$_SERVER['REMOTE_ADDR']."\n");
    // copy the good login page to the wp-login.php
    if (strcmp($loginPageContents, $fourohfourPage) === 0) {
      file_put_contents("$howDeep/wp-login.php", file_get_contents($goodLoginPage));
    }
    sleep($seconds);
    fwrite($file, date('r')." login page re-locked from IP: ".$_SERVER['REMOTE_ADDR']."\n");
    file_put_contents("$howDeep/wp-login.php", $fourohfourPage);
    fclose($file);
    echo "ajax unlock done...";
  }
}
else {
// todo get the site url and add to the title here, so they can bookmark this page
  $servername = $_SERVER['HTTP_HOST'];
    echo "</b><br/>";
    echo "<html><head><title>$servername - Unlock login page</title>";

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

    echo "<b>Instructions:</b> Click the unlock button, then click the login link, we'll lock things back up in a little bit<br/>";
    echo "<br/><b>Status</b>: your login page is currently ";
    $loginPageContents = file_get_contents("$howDeep/wp-login.php");
    if (strcmp($loginPageContents, $fourohfourPage) === 0) {
      echo "locked.";
    }
    else {
      echo "unlocked.";
    }
    echo "<br/><br/>";
    $file = fopen('locklogin.log', 'a');
    fwrite($file, date('r')." wplocklogin accessed from IP: ".$_SERVER['REMOTE_ADDR']."\n");
    fclose($file);

    echo '<br/><button type="button" onclick="loadXMLDoc()">Step 1 - Unlock</button><br/>';
    echo "<br/><a href='?login=1'>Step 2 - Login</a><br/><br/> ";

    echo "<br/><br/>History:<br/>";
    $logFile = fopen('locklogin.log', 'r');
    $lines = read_last_lines($logFile, 20);
    fclose($logFile);
        for ($i = count($lines); $i > 0; $i--) {
        echo $lines[$i]."<br/>";
    }


    echo "<br/><br/>To install this script, create a super secret directory under your web root, cd into that directory, and enter this command:<br/>";
    echo " <code>wget --no-check-certificate https://raw.github.com/wesyah234/wplocklogin/master/index.php</code>";
    echo "</body></html>";

  //exec ("git pull  > /dev/null 2>&1 &");
}
?>
