
<?
//echo($_SERVER['REMOTE_ADDR'].":");
//echo($_SERVER['REMOTE_PORT']);

/*

<!DOCTYPE html>




<html lang="ko" class="">
<head>
<meta charset="utf-8">
<title>Daum &ndash; 모으다 잇다 흔들다</title>
<meta property="og:url" content="http://www.daum.net/">
<meta property="og:type" content="website">
<meta property="og:title" content="모두가 즐거워지는 인터넷 라이프 &ndash; Daum">
<meta property="og:image" content="http://i1.daumcdn.net/svc/image/U03/common_icon/5587C4E4012FCD0001">
<meta property="og:description" content="인터넷 세상의 즐거움을 함께하고 싶은 순간, 다음에서 쉽고 편하게 모두와 나눠보세요.">
<meta name="msapplication-task" content="name=Daum;action-uri=http://www.daum.net/;icon-uri=/favicon.ico">
<meta name="msapplication-task" content="name=미디어다음;action-uri=http://media.daum.net/;icon-uri=/media_favicon.ico">
<meta name="msapplication-task" content="name=tv팟;action-uri=http://tvpot.daum.net;icon-uri=/tvpot_favicon.ico">
<meta name="msapplication-task" content="name=메일;action-uri=http://mail.daum.net;icon-uri=/mail_favicon.ico">

<title>NAVER</title>
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico?6">

<link rel="stylesheet" type="text/css" href="http://s.nm.naver.net/css/w_g160202.css">


<link rel="stylesheet" type="text/css" href="http://s.nm.naver.net/css/e_g150402.css">

<style>
.sb_btns {display:inline-block;} .u_cbox_head {display:none;} #comment_module{padding-top:7px; border-top:0 !important;}


</style>


</head><body></body></html>
$ff="212";


define('BOT_TOKEN', '114864593:AAFoLCPVdfLV5TIu2ZO_IvECAIT7eKUbX-c');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

function apiRequestWebhook($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  header("Content-Type: application/json");
  echo json_encode($parameters);
  return true;
}

function exec_curl_request($handle) {
  $response = curl_exec($handle);

  if ($response === false) {
    $errno = curl_errno($handle);
    $error = curl_error($handle);
    error_log("Curl returned error $errno: $error\n");
    curl_close($handle);
    return false;
  }

  $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
  curl_close($handle);

  if ($http_code >= 500) {
    // do not wat to DDOS server if something goes wrong
    sleep(10);
    return false;
  } else if ($http_code != 200) {
    $response = json_decode($response, true);
    error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
    if ($http_code == 401) {
      throw new Exception('Invalid access token provided');
    }
    return false;
  } else {
    $response = json_decode($response, true);
    if (isset($response['description'])) {
      error_log("Request was successfull: {$response['description']}\n");
    }
    $response = $response['result'];
  }

  return $response;
}

function apiRequest($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  foreach ($parameters as $key => &$val) {
    // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) {
      $val = json_encode($val);
    }
  }
  $url = API_URL.$method.'?'.http_build_query($parameters);

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);

  return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  $handle = curl_init(API_URL);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  return exec_curl_request($handle);
}

function processMessage($message) {
  // process incoming message
  $message_id = $message['message_id'];
  $chat_id = $message['chat']['id'];
  if (isset($message['text'])) {
    // incoming text message
    $text = $message['text'];

    if (strpos($text, "/start") === 0) {
      apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Hello', 'reply_markup' => array(
        'keyboard' => array(array('Hello', 'Hi')),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));
    } else if ($text === "Hello" || $text === "Hi") {
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Nice to meet you'));
    } else if (strpos($text, "/stop") === 0) {
      // stop now
    } else {
      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => 'Cool'));
    }
  } else {
    apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'I understand only text messages'));
  }
}


define('WEBHOOK_URL', 'https://my-site.example.com/secret-path-for-webhooks/');

if (php_sapi_name() == 'cli') {
  // if run from console, set or delete webhook
  apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
  exit;
}


$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
  // receive wrong update, must not happen
  exit;
}

if (isset($update["message"])) {
  processMessage($update["message"]);
}


*/


/*
https://telegram.me/botfather 접속하여 봇아빠를 채팅창에 호출 하고 다음 커맨드를 입력

-------------------------------
봇이름 /newbot
봇실재아이디(끝에 bot을 붙여준다)
/token
/setabouttext
봇정보 안내문구
/setdescription
접속문구
-------------------------------

token이 바로 api 
*/
//header('Content-Type: text/html; charset=utf-8');

# 기본환경설정
define('TOKEN_KEY','114864593:AAFoLCPVdfLV5TIu2ZO_IvECAIT7eKUbX-c');
define('BASE_URL', 'https://api.telegram.org/bot'.TOKEN_KEY);






/*
 echo "file upload program<br />";
 echo "select the file<br />";
 ?>
<form method="post" action="<?=BASE_URL?>/sendPhoto" enctype="multipart/form-data">
<input type="file" size=100 name="photo"><hr>
<input type="text" value="159284966" name="chat_id">
<input type="submit" value="send">
</form>
<?
return;
*/


# CURL Function
function GetCurl($url, $data=array()) {
    // 서버로 전송 및 결과 반환
    $rest = curl_init();
    curl_setopt($rest, CURLOPT_URL, $url);
    curl_setopt($rest, CURLOPT_POST, false);
    curl_setopt($rest, CURLOPT_RETURNTRANSFER, true);
    
    $Result = curl_exec($rest);
    curl_close($rest);
    return json_decode($Result, true);
}

function fn_setCDATA($pStr) {
	return ( $pStr == "" )? "" : "<![CDATA[$pStr]]>";
}


if($_POST['type'] == 'room') {

    # 채팅룸 추출
    $Room = GetCurl(BASE_URL.'/getUpdates?limit=100');
    $Room_id = array();
    foreach($Room['result'] as $k=>$v) {
    
        $Room_id[] = $v['message']['chat']['id'];
    }
    $Room_id = array_unique($Room_id);
    $Room_id = array_values($Room_id);
    
    var_dump($Room_id);
    echo '<hr>';
    var_dump($Room);
} else {

    # 지정발송
    if($_POST['room_id']) $Room_id = $_POST['room_id'];
    //else $Room_id = array('52227374','119732868', '84094887');
    

    if($_POST['mod']) $vMod = $_POST['mod']; else $vMod = "sms"; // 메시지종류(sms문자,bat배터리)
    if($_POST['num']) $vNum = $_POST['num']; // 보낸사람
    if($_POST['nam']) $vNam = $_POST['nam']; // 기기주소록의 보낸사람이름
    if($_POST['myn']) $vMyn = $_POST['myn']; // 내전화번호
    if($_POST['tim']) $vTim = $_POST['tim']; // 문자받은시간
    if($_POST['msg']) $vMsg = $_POST['msg']; else $vMsg = date('Y-m-d H:i:s');

		//$vMailBox = json_decode('"\ud83d\udcec"'); // 우체통 이모지
		//$vSMS = json_decode('"\ud83d\udce9"'); // 문자 이모지
		$vDownArrow = json_decode('"\ud83d\udd3b"'); // 아래 화살표 이모지
		
		// mod별 이모지
		if ( $vMod == "sms" ) { // 문자수신
			$vIcon = json_decode('"\ud83d\udce9"'); // 편지모양
		} else if ( $vMod == "mms" ) { // 문자수신
			$vIcon = json_decode('"\ud83d\udce9"'); // 편지모양
		} else if ( $vMod == "bat" ) { // 배터리상태
			$vIcon = json_decode('"\ud83d\udd0b"'); // 배터리모양
		} else if ( $vMod == "cha" ) { // 충전상태
			$vIcon = json_decode('"\u26a1\ufe0f"'); // 번개모양
		} else if ( $vMod == "mis" ) { // 부재중전화
			$vIcon = json_decode('"\ud83d\udcf5"'); // 전화금지이모지
		} else {
			$vIcon = json_decode('"\ud83d\udcec"'); // 우체통모양
		}

		$vEmoji = json_decode('"\ud83d\udcf2"'); // 내 핸드폰 이모지

//\ud83d\udcf2 //화살표 핸드폰
//\ud83d\udcf1 //핸드폰
//\u25b6\ufe0f //네모화살표
//\u2611\ufe0f //체크
//\ud83d\udd38 //노란마름모
//\ud83d\udd39 //파란마름모
//\ud83d\udcec 우체통 이모지
//\ud83d\udce9 문자
//\u26a1\ufe0f 충전
//\ud83d\udd0b 배터리
//\ud83d\udcde 전화
//\u260e\ufe0f 전화
//\ud83d\udcf5 전화금지
//\u23f0\u23f1\u23f2 시계1,2,3
		//$vClock = json_decode('"\u23f0"'); // 지난시계
		$vClock = json_decode('"\u23f1"'); // 지난시계
		//$vClock = json_decode('"\u23f2"'); // 지난시계

    $who = "";
    if ( $vNam != "" && $vNum != "" ) $who = $vNam."(".$vNum.")";
    else if ( $vNam == "" && $vNum != "" ) $who = $vNum;
    else if ( $vNam != "" && $vNum == "" ) $who = $vNam;
    else if ( $vNam == "" && $vNum == "" ) $who = "ALARM";

    /**
     * 시간표시제도
     * 1. 발생 3분 내외면 시간을 표시하지 않는다(텔레그램 시간으로 확인)
     * 2. 발생 60분 이내면 [몇분전] 으로 표시한다.
     * 3. 발생 60분 이후면 월-일 시:분 으로 표시한다.
     */
    if ( $vTim != 0 ) {
    	if ( strlen($vTim) == 13 ) $vTim = substr($vTim,0,10);
	    $vPassSecond = mktime() - $vTim; // 문자발송부터 텔레그램 전송까지 걸린 시간(초)

	    if ( $vPassSecond < 3 * 60 ) { // 3분내 수신건은 현재 시간으로 인정
	    	$vTim = ""; //$vTim = "(now)";
	    } else if ( $vPassSecond < 60 * 60 ) { // 60분 이내의 건은 몇분전.으로 표기.
	    	//$vTim = "(".floor($vPassSecond / 60)."mins ago)";
	    	//$vTim = "(".$vClock.date('H:i', $vTim)")";
				$vTim = "(".$vClock.date('H:i', $vTim).")";
	    } else {
				$vTim = "(".$vClock.date('m-d H:i', $vTim).")";
	    }
	  }
	  /** 시간 표시 끝 */

		//$line="\n<pre>..................................................</pre>\n";
		//$line="\n<pre>...................</pre>\n";
		//$line = json_decode('"\u25aa\ufe0f\u25ab\ufe0f"');
		//$line = json_decode('"\u2b1b\ufe0f\u2b1c\ufe0f"');
		//$line = "\n".$line.$line.$line.$line.$line.$line."\n";

		$line ="\n<b>----=============----</b>\n"; 
		$line ="\n\n"; 


		//$tail=$line."<a href='http://naver.com'>NAVER</a> | <a href='http://daum.net'>DAUM</a> | <a href='http://nate.com'>NATE</a>";

		//$vMsg = $vMailBox."From ".$vNum.$vDownArrow."\nTo ".$vMyn." ".$vTim."\n\n".$vMsg;

		$vMsg = $vIcon." ".$who.$vDownArrow.$line.$vMsg.$line;
    if ( $vMyn != '' ) $vMsg .= $vEmoji." ".$vMyn." ";
    $vMsg .= $vTim;
 //  $vMsg .= $tail.$line;

    if ( sizeof($Room_id) > 1) {
        # 발송하기
        foreach($Room_id as $k=>$v) {
            $Result = GetCurl(BASE_URL.'/sendMessage?chat_id='.$v.'&text='.urlencode($vMsg));
            //print_r($Result);
            //echo '<hr>';
        }
    } else if ( is_array($Room_id) === false && $Room_id ) {
        $Result = GetCurl(BASE_URL.'/sendMessage?chat_id='.$Room_id.'&text='.urlencode($vMsg).'&parse_mode=HTML' );
        //$Result = GetCurl(BASE_URL.'/sendPhoto');
        
        /*
        $a = $Result['ok'];
        $b = $Result['result']['message_id'];
        $c = $Result['result']['chat']['first_name'];
        $d = $Result['result']['chat']['last_name'];
        $e = $Result['result']['chat']['type'];
        $f = $Result['result']['date'];
        echo $a."<br>".$b."<br>".$c."<br>".$d."<br>".$e."<br>".$f."<br>";
        */
        echo "<A>".$Result['ok']."</A>";
        echo "<B>".fn_setCDATA($Result['result']['chat']['first_name'])."</B>";
        echo "<C>".fn_setCDATA($Result['result']['chat']['last_name'])."</C>";
        echo "<D>".$Result['result']['chat']['type']."</D>";
        echo "<E>".$Result['result']['date']."</E>";
        
        
/*
						
{
"ok":true,"result":
{
 "message_id":338
,"from":{"id":114864593,"first_name":"\uc559\ub7a9_\ud14c\uc2a4\ud2b8_\ubd07","username":"anglab_test_bot"}

,"chat":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131","type":"private"}
,"date":1454073710
,"text":"\ud83d\udcecFrom Alex(15772111)\ud83d\udd3b\nTo (01-01 AM09:00)\n\n[\uceec\uccd0\ub79c\ub4dc] \ubcf8\uc778\uc778\uc99d\ubc88\ud638\ub294 388757\uc785\ub2c8\ub2e4. \uc815\ud655\ud788 \uc785\ub825\ud574\uc8fc\uc138\uc694."
}}
*/


				//echo("<br>".$vTitle[$i]."/".$vThumb[$i]."/".$vLinkCode[$i]."/".$vWebUpdDh[$i]."/".$vSellYn[$i]."/".$vUseYn[$i]);
        //print_r($Result);
        //echo '<hr>';
    }
}
/*
http://anglab.dothome.co.kr/toontaku/test.php?room_id=159284966&msg=%5B%EC%BB%AC%EC%B3%90%EB%9E%9C%EB%93%9C%5D+%EB%B3%B8%EC%9D%B8%EC%9D%B8%EC%A6%9D%EB%B2%88%ED%98%B8%EB%8A%94+388757%EC%9E%85%EB%8B%88%EB%8B%A4.+%EC%A0%95%ED%99%95%ED%9E%88+%EC%9E%85%EB%A0%A5%ED%95%B4%EC%A3%BC%EC%84%B8%EC%9A%94.%0A&num=15772111&nam=Alex
http://anglab.dothome.co.kr/toontaku/test.php?room_id=159284966&msg=%EC%9D%B4%EB%B2%88%EB%8B%AC+1%EC%9D%BC%EC%97%90%EC%84%9C+%ED%98%84%EC%9E%AC%EA%B9%8C%EC%A7%80+%EC%82%AC%EC%9A%A9%ED%95%98%EC%8B%A0+%EC%9A%94%EA%B8%88%EC%9D%80+2%2C810%EC%9B%90+%EC%9E%85%EB%8B%88%EB%8B%A4.+%EC%83%81%EC%84%B8%ED%99%95%EC%9D%B8%ED%86%B5%ED%99%94%28%EB%AC%B4%EB%A3%8C%29%0A&num=115&nam=%EC%A0%84%ED%99%94%EB%B9%84&myn=01199100496&tim=1453651221000
http://anglab.dothome.co.kr/toontaku/test.php?room_id=159284966&msg=%5B%EC%9D%B8%EC%A6%9D%EB%B2%88%ED%98%B8%3A%5B893009%5D%5D+NICE+ID+%EB%B3%B8%EC%9D%B8%ED%99%95%EC%9D%B8%EC%9D%84+%EC%9C%84%ED%95%9C+%EC%9D%B8%EC%A6%9D%EB%B2%88%ED%98%B8%EB%A5%BC+%EC%9E%85%EB%A0%A5%ED%95%B4+%EC%A3%BC%EC%84%B8%EC%9A%94.%0A&num=16001522&nam=&myn=01029243012&tim=1454126619000
http://anglab.dothome.co.kr/toontaku/test.php?room_id=159284966&msg=%5BWeb%EB%B0%9C%EC%8B%A0%5D%0A%EC%A3%BC%EC%8B%9D%ED%9A%8C%EC%82%AC+%EC%8B%A0%ED%95%9C%EC%9D%80%ED%96%89%2F%EC%84%9C%EC%9A%B8%EB%B3%B4%EC%A6%9D%EB%B3%B4%ED%97%98+%EC%A3%BC%EC%8B%9D%ED%9A%8C%EC%82%AC+%EA%B7%80%EC%A4%91+%0A++%0A%EB%B3%B8%EC%9D%B8%EC%9D%80+%EA%B0%9C%EC%9D%B8%EC%A0%95%EB%B3%B4%EB%B3%B4%ED%98%B8%EB%B2%95+%EC%A0%9C15%EC%A1%B0%2C%EC%A0%9C17%EC%A1%B0%2C%EC%A0%9C22%EC%A1%B0%2C%EC%A0%9C24%EC%A1%B0%2C+%EC%8B%A0%EC%9A%A9%EC%A0%95%EB%B3%B4%EC%9D%98+%EC%9D%B4%EC%9A%A9+%EB%B0%8F+%EB%B3%B4%ED%98%B8%EC%97%90+%EA%B4%80%ED%95%9C+%EB%B2%95%EB%A5%A0+%EC%A0%9C32%EC%A1%B0%2C33%EC%A1%B0%2C34%EC%A1%B0%EC%97%90+%EB%94%B0%EB%9D%BC+%EA%B7%80%EC%82%AC%EA%B0%80+%EC%95%84%EB%9E%98%EC%99%80+%EA%B0%99%EC%9D%80+%EB%82%B4%EC%9A%A9%EC%9C%BC%EB%A1%9C+%EB%B3%B8%EC%9D%B8%EC%9D%98+%EA%B0%9C%EC%9D%B8%28%EC%8B%A0%EC%9A%A9%29%EC%A0%95%EB%B3%B4%EB%A5%BC+%EC%88%98%EC%A7%91%C2%B7%EC%9D%B4%EC%9A%A9%C2%B7%EC%A0%9C%EA%B3%B5%ED%95%98%EB%8A%94+%EA%B2%83%EC%97%90+%EB%8F%99%EC%9D%98%ED%95%A9%EB%8B%88%EB%8B%A4.+%0A++%0A%5B%EB%8B%B9%EC%82%AC%EC%9D%98+%EA%B3%A0%EC%A7%80%EB%82%B4%EC%9A%A9%5D+%0A%E2%96%A0+%EA%B7%80%ED%95%98%28%EB%8F%99%EC%9D%98%EC%9D%B8%29%EC%9D%98+%EA%B3%A0%EC%9C%A0%EC%8B%9D%EB%B3%84%EB%B2%88%ED%98%B8%2C+%EC%A3%BC%EC%86%8C+%2C%EC%A0%84%ED%99%94%EB%B2%88%ED%98%B8%2C+%EC%97%B0%EC%86%8C%EB%93%9D+%EB%93%B1+%EA%B0%9C%EC%9D%B8%28%EC%8B%A0%EC%9A%A9%29%EC%A0%95%EB%B3%B4%EB%8A%94+%EB%8B%B9%EC%82%AC%28%E3%88%9C%EC%8B%A0%ED%95%9C%EC%9D%80%ED%96%89%2C+%EC%84%9C%EC%9A%B8%EB%B3%B4%EC%A6%9D%EB%B3%B4%ED%97%98%E3%88%9C%29%EC%99%80%EC%9D%98+%28%EA%B8%88%EC%9C%B5%29%EA%B1%B0%EB%9E%98+%5B%EC%8B%A0%ED%95%9C%EC%9D%80%ED%96%89+My-Car%EB%8C%80%EC%B6%9C%5D%EC%99%80+%EA%B4%80%EB%A0%A8%ED%95%98%EC%97%AC+%EA%B7%80%ED%95%98%EC%9D%98+%EA%B0%9C%EC%9D%B8%28%EC%8B%A0%EC%9A%A9%29%EC%A0%95%EB%B3%B4%EB%A5%BC+%EC%A1%B0%ED%9A%8C%ED%95%98%EA%B8%B0+%EC%9C%84%ED%95%9C+%EB%AA%A9%EC%A0%81%EC%9C%BC%EB%A1%9C+%EC%88%98%EC%A7%91%C2%B7%EC%9D%B4%EC%9A%A9%EB%90%98%EB%A9%B0+%EC%84%9C%EC%9A%B8%EC%8B%A0%EC%9A%A9%ED%8F%89%EA%B0%80%EC%A0%95%EB%B3%B4%E3%88%9C%2C%EC%BD%94%EB%A6%AC%EC%95%84%ED%81%AC%EB%A0%88%EB%94%A7%EB%B7%B0%EB%A1%9C%E3%88%9C%2CNICE%EC%8B%A0%EC%9A%A9%ED%8F%89%EA%B0%80%EC%A0%95%EB%B3%B4%E3%88%9C%2C%ED%86%B5%EC%8B%A0%ED%9A%8C%EC%82%AC%2C%EC%A0%84%EA%B5%AD%EC%9D%80%ED%96%89%EC%97%B0%ED%95%A9%ED%9A%8C%2C%EC%97%AC%EC%8B%A0%EA%B8%88%EC%9C%B5%ED%98%91%ED%9A%8C%2C%EA%B5%AD%EB%82%B4%EC%99%B8%EC%9E%AC%EB%B3%B4%ED%97%98%EC%82%AC+%EB%93%B1%EC%97%90+%EC%A0%9C%EA%B3%B5%EB%90%98%EC%96%B4+%EA%B7%80%ED%95%98%EC%9D%98+%EA%B0%9C%EC%9D%B8%EC%8B%9D%EB%B3%84%EC%A0%95%EB%B3%B4%2C%EC%8B%A0%EC%9A%A9%EA%B1%B0%EB%9E%98%EC%A0%95%EB%B3%B4%2C%EC%8B%A0%EC%9A%A9%EB%8A%A5%EB%A0%A5%EC%A0%95%EB%B3%B4%2C%EC%8B%A0%EC%9A%A9%EB%8F%84%ED%8C%90%EB%8B%A8%EC%A0%95%EB%B3%B4+%EB%93%B1%EC%9D%98+%EC%A1%B0%ED%9A%8C%EC%97%90+%ED%99%9C%EC%9A%A9%EB%90%A9%EB%8B%88%EB%8B%A4.+%0A%E2%96%A0+%EB%8B%B9%EC%82%AC%EC%9D%98+%EC%A1%B0%ED%9A%8C%EA%B2%B0%EA%B3%BC+%EA%B7%80%ED%95%98%EC%99%80%EC%9D%98+%28%EA%B8%88%EC%9C%B5%29%EA%B1%B0%EB%9E%98%EA%B0%80+%EA%B0%9C%EC%8B%9C%EB%90%98%EB%8A%94+%EA%B2%BD%EC%9A%B0%EC%97%90%EB%8A%94+%ED%95%B4%EB%8B%B9%28%EA%B8%88%EC%9C%B5%29%EA%B1%B0%EB%9E%98+%EC%A2%85%EB%A3%8C%EC%9D%BC%EA%B9%8C%EC%A7%80+%EC%A0%9C%EA%B3%B5%C2%B7%EC%A1%B0%ED%9A%8C+%EB%8F%99%EC%9D%98%EC%9D%98+%ED%9A%A8%EB%A0%A5%EC%9D%B4+%EC%A7%80%EC%86%8D%EB%90%A9%EB%8B%88%EB%8B%A4.+%0A%E2%96%A0+%EA%B7%80%ED%95%98%EB%8A%94+%EA%B0%9C%EC%9D%B8%28%EC%8B%A0%EC%9A%A9%29%EC%A0%95%EB%B3%B4%EC%9D%98+%EC%84%A0%ED%83%9D%EC%A0%81%EC%9D%B8+%EC%88%98%EC%A7%91%C2%B7%EC%9D%B4%EC%9A%A9%C2%B7%EC%A0%9C%EA%B3%B5%EC%97%90+%EB%8C%80%ED%95%9C+%EB%8F%99%EC%9D%98%EB%A5%BC+%EA%B1%B0%EB%B6%80%ED%95%A0+%EC%88%98+%EC%9E%88%EC%9C%BC%EB%82%98%2C+%EA%B0%9C%EC%9D%B8%28%EC%8B%A0%EC%9A%A9%29%EC%A0%95%EB%B3%B4%EC%9D%98+%ED%95%84%EC%88%98%EC%A0%81+%EC%82%AC%ED%95%AD%EC%97%90+%EB%8C%80%ED%95%98%EC%97%AC+%EC%88%98%EC%A7%91%C2%B7%EC%9D%B4%EC%9A%A9%C2%B7%EC%A0%9C%EA%B3%B5%C2%B7%EC%A1%B0%ED%9A%8C%EC%97%90+%EA%B4%80%ED%95%B4+%EB%8F%99%EC%9D%98%ED%95%98%EC%A7%80+%EC%95%8A%EC%9C%BC%EC%8B%A4+%EA%B2%BD%EC%9
*/
/*
	TB_WT001 웹툰 정보
	TB_WT002 사이트 설정
	TB_WT003 웹툰당 회차정보
	TB_WT004 시스템 설정
	TB_WT005 내가보는웹툰 목록 저장
	TB_WT006 사용자 정보
	TB_LOG01 디버깅로그

  트래픽 감소를 위해 컬럼 단축
	A	OK
	B	RT_FST_NM
	C	RT_LST_NM
	D	RT_TYPE
	E	TRN_DH
	=----
	F	LST_UPD_DH / 이 컬럼은 LST_UPD_DH - '$vLstUpdDh' AS LST_UPD_DH 이렇게 보낸다. 트래픽 감소를 위해.(로컬에서 vParam값을 더할것이다.)
	G	MAX_NO
	H	NAME
	I	SEL_MODE
	J	SET_CONT
	K	SET_ID
	L	SET_VALUE
	M	SITE
	N	SORT
	O	THUMB_COMN
	P	THUMB_NAIL
	Q	USE_YN
	R	CNT
	S	ARTIST 작가명 ks20151125
	T	SELL_YN 유료여부 ks20151125
	U	ORG_UPD_DH 웹서버의 업데이트 일자 ks20151125
*/

?>
