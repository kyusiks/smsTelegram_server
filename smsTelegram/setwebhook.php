<?
  include_once "comn.php";
	$vWebhookUrl = "https://smstelegram-1244.appspot.com/helloworld.php";

	for ( $i = 0; $i < sizeof($gvUrl); $i++ ) {
		$vBid = $i;
    $param = array( 'method' => 'setwebhook', 'url' => $vWebhookUrl."?bid=".$i );
    $vRtnHook = sendMessage('1', $param); // 웹훅세팅

    var_dump($vRtnHook);
    echo "<br><hr>";

    if ( true ) { // 봇 업데이트
      $param = array( 'method' => 'getMe' );
      $RtnHook = sendMessage('1', $param);    
      //{"ok":true,"result":{"id":114864593, "first_name":"\uc559\ub7a9_\ud14c\uc2a4\ud2b8_\ubd07", "username":"anglab_test_bot"}}
      // $vRtnHook['result']['id'] 저장 안하는 이유는 보안상.
      var_dump($RtnHook);
      $vRtnHook = json_decode($RtnHook, true);
      echo "<br><hr>";

	    $vQuery = "INSERT INTO TB_TL003 ( BID, BOT_ID, BOT_NM, USE_YN, HOOK_URL, LST_UPD_DH
) VALUES (
'".$i."', '".$vRtnHook['result']['username']."', '".$vRtnHook['result']['first_name']."', '', '".$vWebhookUrl."?bid=".$i."', NOW()) 
	ON DUPLICATE KEY
	UPDATE BOT_ID = '".$vRtnHook['result']['username']."'
	     , BOT_NM = '".$vRtnHook['result']['first_name']."'
	     , HOOK_URL = '".$vWebhookUrl."?bid=".$i."'
	     , LST_UPD_DH = NOW()";
      fn_sql($vQuery);
    }
	}


	return;

echo "0:".$_SERVER['HTTP_CLIENT_IP'];
echo "<br>1:".$_SERVER['HTTP_X_FORWARDED_FOR'];
echo "<br>2:".$_SERVER['REMOTE_ADDR'];
echo "<br>3:".ip2long($_SERVER['REMOTE_ADDR']);

echo "<br>4:".inet_pton($_SERVER['REMOTE_ADDR']);

echo "<br>5:".$_SERVER['REMOTE_HOST'];

//https://192.168.43.1:8443/index.htm
//https://203.226.206.44:8443/index.htm

function getAddr($inet=false){
    $addr = $_SERVER['REMOTE_ADDR'];
    if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) $addr .=  "/".$_SERVER['HTTP_X_FORWARDED_FOR'];
    else if( isset($_SERVER['HTTP_CLIENT_IP']) ) $addr .= "/".$_SERVER['HTTP_CLIENT_IP'];
 
    if( $inet ){
        $tmp = explode("/", $addr);
        $addr = ip2long($tmp[0]);
        if( isset($tmp[1]) ) $addr .= ".".ip2long($tmp[1]);
    }
    return $addr;
}
echo getAddr();
?>
