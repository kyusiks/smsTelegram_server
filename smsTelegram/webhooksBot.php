<?
	//incoming - https://smstelegram-1244.appspot.com/helloworld.php
	// http://anglab.dothome.co.kr/smsTelegram/webhooksBot.php?bid=0&test={"update_id":720711568,"message":{"message_id":2871,"from":{"id":159284966,"first_name":"uc774ub984","last_name":"uc131"},"chat":{"id":159284966,"first_name":"uc774ub984","last_name":"uc131","type":"private"},"date":1457622244,"text":"*daum.net*"}}
	// {"update_id":720711621,"message":{"message_id":3017,"from":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131"},"chat":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131","type":"private"},"date":1458030869,"reply_to_message":{"message_id":3016,"from":{"id":114864593,"first_name":"\uc559\ub7a9_\ud14c\uc2a4\ud2b8_\ubd07","username":"anglab_test_bot"},"chat":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131","type":"private"},"date":1458020922,"text":"\uac00\ub098\ub2e4"},"text":"dddd"}}

	include_once "comn.php";
	
	$content = file_get_contents("php://input");
	if ( !$content ) $content = $_GET["test"]; // 테스트용
	
	$update = json_decode($content, true);
	
  logDB("hook:".$vBid, $update['message']['text'].":".$content );
  //logDB("hook:".$vBid, $content );


	if ( !isset($update["message"]) ) exit;

	// $vBid = 웹훅url에서 던져준다. 꼭. 중요.
  $vLid = "0"; // 서버에서 들어온 훅은 로컬아이디를 0으로 잡는다.
  $vBid = $_REQUEST['bid'];
  $vCid = $update["message"]["chat"]["id"];
  $vMod = "web"; // 모드:웹훅
  $vDpl = $update["update_id"];
  $vTim = $update["message"]["date"];
  $vRplMessageId = $update["message"]["reply_to_message"]["message_id"];
  $vConId = fn_getConId($vLid, $vCid, $vBid);

  $vResult = processMessage($update["message"]);

  $response = json_decode($vResult, true);

  if ( $response["ok"] == true ) {
// "ok":true,"result":{"message_id":3025,"from":{"id":114864593,"first_name":"\uc559\ub7a9_\ud14c\uc2a4\ud2b8_\ubd07","username":"anglab_test_bot"},"chat":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131","type":"private"},"date":1458032551,"reply_to_message":{"message_id":2871,"from":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131"},"chat":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131","type":"private"},"date":1457622244,"text":"daum.net"},"text":"*daum.net*"}}
	} else {
	  logDB("hook err:".$response["ok"], $vBid.":".$vResult);
  }
	//끝


function processMessage($message) {
  if ( isset($message['text']) ) { // incoming text message
  	$message_id = $message['message_id'];
	  if ( isset($message['reply_to_message']) ) {
			$reply_to_message = $message['reply_to_message']['message_id'];
		  $vResult = sendMessage('Nice to meet you'.$reply_to_message, array("reply_to_message_id" => $message_id, "pOrgRplMsgId" => $reply_to_message ) );
	  } else {

	    $text = $message['text'];

	    if ( strpos($text, "/") === 0 ) {
	      $vResult = fn_commandIn($message); // 커맨드처리부

	    } else if ($text === "Hello" || $text === "Hi") {
		  	$vResult = sendMessage('Nice to meet you');

	    } else if (strpos($text, "/stop") === 0) {
	      // stop now
	    } else {
				$vResult = sendMessage($text, array("reply_to_message_id" => $message_id));
	    }
	  }
  } else {
		$vResult = sendMessage('I understand only text messages');
  }
  return $vResult;
}


function fn_commandIn($message) {
  $text = $message['text'];
  $arrCommand = split(" ", $text); // [0]커맨드 [1]아귀먼트1 [2] 아귀먼트2
  $message_id = $message['message_id'];
 
  if ( strpos($text, "/img") === 0 ) {
    include_once "./webhookCommands/img.php";
    //$TF = sendMessage($TF, array("reply_to_message_id" => $message_id) );
    //$TF = fn_cmdInclud();
    // 위의 PHP의 fn_cmdInclud 에서 리턴값을 만든다.
		// $vResult = fn_getUrl("http://anglab.dothome.co.kr/smsTelegram/webhookCommands/img.php");  
		return $TF;
  }
 

	if ( $arrCommand[0] == "/start" ) {
		$vQuery = "INSERT INTO TB_TLG01 ( ID, IMG_SND_LIMIT, LANGUAGE, FST_INS_DH ) VALUES (
'"."', 100, 'EN', FROM_UNIXTIME('".$message['date']."'))
		ON DUPLICATE KEY UPDATE LANGUAGE = LANGUAGE";

//		fn_sql($vQuery);

 		$param = array( 'reply_markup' => array( 'keyboard' => array(array('Hello', 'Hi'))
 		                                       , 'one_time_keyboard' => true
 		                                       , 'resize_keyboard' => true ));
	  $TF = sendMessage("Hello".$lang."yo", $param);
	  
	} else if ( $arrCommand[0] == "/lang" ) {
 		$param = array( 'reply_markup' => array( 'keyboard' => array(array('Hello', 'Hi'))
 		                                       , 'one_time_keyboard' => true
 		                                       , 'resize_keyboard' => true ));
	  $TF = sendMessage("Language : \n...", $param);

	} else if ( strpos($text, "/dn_") === 0 ) { // mms image down
		// TODO callGCM;

	  $TF = sendMessage("working... (a few minute)", $param);
	} else {
 		$param = array( 'reply_markup' => array( 'keyboard' => array(array('Hello', 'Hi'))
 		                                       , 'one_time_keyboard' => true
 		                                       , 'resize_keyboard' => true ));
	  $TF = sendMessage($text, $param);

	} 
  return $TF;


}

?>