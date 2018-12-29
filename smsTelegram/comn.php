<?
header('Content-Type: text/html; charset=utf-8');

include_once "../include/db_ok.php";

$gvLang = "";
$gvString = array(); // 메시지 담을 글로벌변수
$gvUrl = array ('https://api.telegram.org/bot114864593:AAFoLCPVdfLV5TIu2ZO_IvECAIT7eKUbX-c/'    // 0 test
               ,'https://api.telegram.org/bot164152133:AAF6fmt-vGDcGkjTtWtrbaM6EuhbtfQ4I_U/' ); // 1 real1

# 지정발송
if ( $_REQUEST['lid'] != "" ) $vLid = $_REQUEST['lid'];
if ( $_REQUEST['ver'] != "" ) $vVer = $_REQUEST['ver'];
if ( $_REQUEST['conId'] != "" ) $vConId = $_REQUEST['conId'];
if ( $_REQUEST['dpl'] != "" ) $vDpl = $_REQUEST['dpl']; // 중복확인키
if ( $_REQUEST['mod'] != "" ) $vMod = $_REQUEST['mod']; else $vMod = "sms"; // 메시지종류(sms문자,bat배터리)
if ( $_REQUEST['num'] != "" ) $vNum = $_REQUEST['num']; // 보낸사람
if ( $_REQUEST['myn'] != "" ) $vMyn = $_REQUEST['myn']; // 내전화번호
if ( $_REQUEST['tim'] != "" ) $vTim = $_REQUEST['tim']; // 문자받은시간
if ( strlen($vTim) == 13 ) $vTim = substr($vTim,0,10);


if ( $_REQUEST['nam'] != "" ) $vNam = urldecode($_REQUEST['nam']); // 기기주소록의 보낸사람이름
if ( $_REQUEST['msg'] != "" ) $vMsg = urldecode($_REQUEST['msg']);

if ( $_REQUEST['img'] != "" ) $vImg = $_REQUEST['img'];


//if ( $_REQUEST['cid'] != "" ) $vCid = $_REQUEST['cid'];
if ( $_REQUEST['bid'] != "" ) $vBid = $_REQUEST['bid'];
$vCid = "";
//$vBid = "";
if ( $vConId != "" ) fn_setLCBByConId();

function ms($pCode, $pData = array()) {
  if ( sizeof($gvString) == 0 ) { // 문자가 로딩된적이 없다면
    global $gvLang;
    if ( $gvLang == "" ) $gvLang = "en";
    $filename = "./string/".$gvLang.".php";
	  if ( is_file($filename) == false ) {
      $filename = "./string/ko.php";
    } // 해당 언어팩이 없을시 기본.
    include_once $filename;
  }
  $vString = $gvString[$pCode];
  if ( $vString == null ) $vString = "";

  if ( $pData != null ) {
    for ( $i = 0; $i < sizeof($pData); $i++ ) {
      $vString = str_replace("{".$i."}", $pData[$i], $vString);
    }
  }
  return $vString;
}

function fn_setCDATA($pStr) {
	return ( $pStr == "" )? "" : "<![CDATA[$pStr]]>";
}

function fn_err($pErrCd, $pWhere) {
  logDB("E-".$pErrCd, $vConId.":".$vErrDsc );
  if ( $pWhere == "xml" ) {
    $vReturn = "<A>0</A><F>".fn_setCDATA($vErrCd)."</F><G>".fn_setCDATA($vErrDsc)."</G>";
  } else {
    $vArray = array("ok"=>false, "error_code"=>$pErrCd, "description"=> urlencode($vErrDsc));
    $vReturn = urldecode(json_encode($vArray));
  }
  return $vReturn;
}

function fn_chkConId() {
	global $vLid, $vBid, $vCid, $vConId;
  $row = mysqli_fetch_array(fn_sql("SELECT LID, CID, BID, LANG, USE_YN FROM TB_TL004 A WHERE CON_ID = '$vConId'"));
	if ( $row["LID"] != $vLid ) return false;
	if ( $row["CID"] != $vCid ) return false;
	if ( $row["BID"] != $vBid ) return false;
	if ( $row["USE_YN"] == "N" ) return false;
	return $row;
}

function fn_setLCBByConId() {
	global $vConId;
  if ( $vConId == "" ) return;
	global $vLid, $vCid, $vBid;
  $vResult = fn_sql("SELECT LID, CID, BID FROM TB_TL004 WHERE CON_ID = '$vConId' ");
  $row = mysqli_fetch_array($vResult);

	$vLid = $row['LID'];
	$vCid = $row['CID'];
	$vBid = $row['BID'];
}

function fn_setConId($plid, $pcid, $pbid) {
	$vQuery = "INSERT INTO TB_TL004 (LID,CID,BID,CON_ID,FST_INS_DH,LST_UPD_DH
	) VALUES (
	'$plid','$pcid','$pbid',(SELECT IFNULL((SELECT MAX(CON_ID)+1 FROM TB_TL004 A), 1)),NOW(),NOW())
	ON DUPLICATE KEY UPDATE USE_YN = '', LST_UPD_DH = NOW()";
	fn_sql($vQuery);
}

// 커넥션아이디를 리턴한다. 없으면 빈칸.
function fn_getConId($plid, $pcid, $pbid) {
	global $vLid, $vCid, $vBid;
	if ( $plid == "" ) $plid = $vLid;
	if ( $pcid == "" ) $pcid = $vCid;
	if ( $pbid == "" ) $pbid = $vBid;
	$vQuery = "SELECT CON_ID FROM TB_TL004 WHERE LID = '$plid' AND CID = '$pcid' AND BID = '$pbid' AND USE_YN != 'N' ";
  $vResult = fn_sql($vQuery);
  $row = mysqli_fetch_array($vResult);
  $vConId = $row['CON_ID'];

  if ( $vConId == "" && $plid == "0" ) {
    // web에서 보낸 메시지면 커넥션 연결
	  fn_setConId($plid, $pcid, $pbid);
		$vQuery = "SELECT CON_ID FROM TB_TL004 WHERE LID = '$plid' AND CID = '$pcid' AND BID = '$pbid' AND USE_YN != 'N' ";
	  $vResult = fn_sql($vQuery);
	  $row = mysqli_fetch_array($vResult);
	  $vConId = $row['CON_ID'];
  }
  return $vConId;
}



function sendMessage($text, $parameters = array() ) {
	global $gvUrl, $vLid, $vBid, $vCid, $vMod, $vDpl, $vTim;
	global $vConId;
	
  if ( $parameters["method"] == "" ) $parameters["method"] = "sendMessage";
  if ( $parameters["method"] == "sendMessage" ) {
		$vChk = fn_chkConId();

		if ( $vChk === false ) return '{"ok":false,"error_code":223,"description":"[Error]: invalid connection id"}';
		if ( $gvUrl[$vBid] == "" ) return '{"ok":false,"error_code":224,"description":"[Error]: $gvUrl[$vBid] is null"}';

    $parameters["chat_id"] = $vCid;
    $parameters["text"] =  $text;
    if ( $parameters["chat_id"] == "" ) return '{"ok":false,"error_code":220,"description":"[Error]: chat_id is null"}';
    if ( $parameters["text"   ] == "" ) return '{"ok":false,"error_code":221,"description":"[Error]: text is null"}';
    if ( $parameters["disable_web_page_preview"] == "" ) $parameters["disable_web_page_preview"] = true;
    //if ( $parameters["parse_mode"] == "" ) $parameters["parse_mode"] = "HTML";

    // 발송불가 체크 1커넥션여부 2기발송여부 3최근작성여부
    $vQuery = "SELECT ( SELECT SVR_OUT_DH FROM TB_TL005 WHERE CON_ID = '$vConId' AND MODE = '$vMod' AND DPL = '$vDpl' ) AS SVR_OUT_DH
                    , CASE WHEN '$vTim' = '' THEN 'OK'
                           WHEN '$vMod' = 'img' THEN 'OK'
                           WHEN '$vTim' > UNIX_TIMESTAMP() - ( 60*60*24*3 ) THEN 'OK' ELSE 'NO' END TIM_CHK ";
    $row = mysqli_fetch_array(fn_sql($vQuery));

    if ( $row["SVR_OUT_DH"] != '' ) {
      // 기발송. SVR_OUT_DH는 발송시도 TL_COMP_DH는 발송완료
      fn_sql("UPDATE TB_TL005 SET DPL_CNT = DPL_CNT + 1, LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND  MODE = '$vMod' AND DPL = '$vDpl'");
      return '{"ok":false,"error_code":227,"description":"Duplication"}';
    } else if ( $row["TIM_CHK"] == 'NO' ) {
//TODO      return '{"ok":false,"error_code":229,"description":"long time ago msg"}'; // 3일이 지난 메시지. img는 로직상 항상 OK나옴
    }

    $vRplMsgId = $parameters["pOrgRplMsgId"]; // 사용자가 답장으로 선택한 오리지널

		$vQuery = "INSERT INTO TB_TL005 ( CON_ID, MODE, DPL, MSG_IN_DH, SVR_IN_DH, SVR_OUT_DH, RPL_MSG_ID, DPL_CNT, LST_UPD_DH 
		) VALUES (
		 '$vConId', '$vMod', '$vDpl', CASE WHEN '$vTim' = '' THEN NULL ELSE FROM_UNIXTIME('$vTim') END, NOW(), NOW(), '$vRplMsgId', 0, NOW() )
		ON DUPLICATE KEY UPDATE SVR_OUT_DH = NOW(), LST_UPD_DH = NOW()";
		fn_sql($vQuery);
	} else {
		if ( $gvUrl[$vBid] == "" ) return '{"ok":false,"error_code":225,"description":"[Error]: $gvUrl[$vBid] is null"}';
	}
  $vResult = fn_getUrl($gvUrl[$vBid], $parameters);
  $response = json_decode($vResult, true);

	if ( $parameters["method"] == "sendMessage" ) { 
		if ( $response['ok'] == true ) {
			fn_sql("UPDATE TB_TL005 SET TL_COMP_DH = FROM_UNIXTIME('".$response["result"]["date"]."'), MSG_ID = '".$response["result"]["message_id"]."', ERR_CD = '', ERR_DSC = '', LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND MODE = '$vMod' AND DPL = '$vDpl'");
		} else {
			fn_sql("UPDATE TB_TL005 SET TL_COMP_DH = NULL, ERR_CD = '".$response["error_code"]."', ERR_DSC = '".$response['description']."', LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND MODE = '$vMod' AND DPL = '$vDpl'");
		}
	}
//echo $vResult;
  return $vResult;
}

function fn_getUrl($pUrl, $pParam=array(), $pHeader=array()) {
  if ( $pHeader == null ) $pHeader = array("Content-Type: application/json");
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $pUrl);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pParam));
  curl_setopt($ch, CURLOPT_HTTPHEADER, $pHeader);

  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}

function sendGCM($pMethod, $pRid, $pData=array()) {
  if ( $pMethod == "" ) return '{"ok":false,"error_code":222,"description":"no method gcm"}'; 
  if ( $pRid == "" ) return '{"ok":false,"error_code":222,"description":"no rid gcm"}';

	// Replace with the real server API key from Google APIs
	$apiKey = "AIzaSyDw6xz9E6b8PuqEoF8Wm94YKRkuk1x9tCQ"; //구글에서 발급받은 API키값
	$url = "https://android.googleapis.com/gcm/send"; //GCM 전송URL
	
	//$regid = $_REQUEST['regid']; // 디바이스 키값
	//$regid = "APA91bEl3mzKXoKQBGFgyGV5kNirSpKW5Z-mJi6V7r0-14JfZLnFOZlkAA88DwXm6q-CYMx9e_-eCck15dXKW8S-zXDzZi9OsRQ_fV2xIXAsQpfX1WPIM_wpdOjB2KIK3SkIzEUdEMUy";

	$regid = $pRid; // 디바이스 키값

	$registrationIDs = array( $regid );
	$pData["method"] = $pMethod;
	
	// Message to be sent
	// $message = iconv("EUC-KR", "UTF-8", "한글 테스트 TEST!!"); //보낼 메시지

	$fields = array('registration_ids' => $registrationIDs, 'data' => $pData);
	$headers = array('Authorization: key='.$apiKey, 'Content-Type: application/json');
	
  $result = fn_getUrl($url, $fields, $headers);
  return $result;
}








function fn_makeImgId($vConId, $vDpl, $vImg, $vReplyToMessageId) {
  // img219420313876989
  $vNum1 = strlen($vConId);
  $vNum2 = strlen($vReplyToMessageId);
  $vNum3 = strlen($vDpl);

  if ( $vNum1 == 0 || $vNum2 == 0 || $vNum3 == 0 ) return "";

  $vComnImgId = "/img".$vNum1.$vConId.$vNum2.$vReplyToMessageId.$vNum3.$vDpl;

  $vMsg = "";
  $arrImg = explode("|", $vImg);

  foreach ($arrImg as $v1) {
  	if ( $v1 != "" ) $vMsg .= $vComnImgId.$v1."\n";
  }
  
  if ( sizeof($arrImg) > 1 ) { // 모두 받기 (이미지가 2 이상일때)
  	$vMsg .= "\n\n".ms("W1003")." : ".$vComnImgId."ALL";
  }
  
  return $vMsg;
}


function fn_makeMsg($vMod, $vNum, $vNam, $vMyn, $vTim, $vDpl, $vMsg) {
//$vMailBox = json_decode('"\ud83d\udcec"'); // 우체통 이모지
//$vSMS = json_decode('"\ud83d\udce9"'); // 문자 이모지
//$vClock = json_decode('"\u23f0"'); // 지난시계
//$vClock = json_decode('"\u23f2"'); // 지난시계
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
//\ud83d\uddbc 액자1
//\ud83c\udfd4 액자2
//\ud83d\udc8e 다이아몬드
//\ud83d\udcbe 디스켓
//\ud83d\udce5 파일함
//\ud83d\udcce 클립

	
	$vDownArrow = json_decode('"\ud83d\udd3b"'); // 아래 화살표 이모지
	$vEmoji = json_decode('"\ud83d\udcf2"'); // 내 핸드폰 이모지
	$vClock = json_decode('"\u23f1"'); // 지난시계
	
	// mod별 이모지
	if ( $vMod == "sms" ) { // 문자수신
		$vIcon = json_decode('"\ud83d\udce9"'); // 편지모양
	} else if ( $vMod == "mms" ) { // 문자수신
		$vIcon = json_decode('"\ud83d\udce9"'); // 편지모양
	} else if ( $vMod == "img" ) { // mms의 이미지
		$vIcon = json_decode('"\ud83d\uddbc"'); // mms의 이미지
		$vIcon = json_decode('"\ud83c\udfd4"'); // mms의 이미지
	} else if ( $vMod == "bat" ) { // 배터리상태
		$vIcon = json_decode('"\ud83d\udd0b"'); // 배터리모양
	} else if ( $vMod == "cha" ) { // 충전상태
		$vIcon = json_decode('"\u26a1\ufe0f"'); // 번개모양
	} else if ( $vMod == "mis" ) { // 부재중전화
		$vIcon = json_decode('"\ud83d\udcf5"'); // 전화금지이모지
	} else {
		$vIcon = json_decode('"\ud83d\udcec"'); // 우체통모양
	}

	/**
	  * 보낸사람 ks20160218
	  * 번호o 이름o : 이름(번호)
	  * 번호o 이름x : 번호
	  * 번호x 이름o : 이름
	  * 번호x 이름x : ALARM
	  */
  $who = "";
  if ( $vNam != "" && $vNum != "" ) $who = $vNam."(".$vNum.")";
  else if ( $vNam == "" && $vNum != "" ) $who = $vNum;
  else if ( $vNam != "" && $vNum == "" ) $who = $vNam;
  else if ( $vNam == "" && $vNum == "" ) $who = "ALARM";
	/** 보낸사람끝 */

  /**
   * 시간표시제도
   * 1. 발생 3분 내외면 시간을 표시하지 않는다(텔레그램 시간으로 확인)
   * 2. 발생 60분 이내면 [몇분전] 으로 표시한다.
   * 3. 발생 60분 이후면 월-일 시:분 으로 표시한다.
   */
  if ( $vTim != 0 ) {

    $vPastSecond = mktime() - $vTim; // 문자발송부터 텔레그램 전송까지 걸린 시간(초)

    if ( $vPastSecond < 3 * 60 ) { // 3분내 수신건은 현재 시간으로 인정
    	$vTim = ""; //$vTim = "(now)";
    } else if ( $vPastSecond < 60 * 60 ) { // 60분 이내의 건은 몇분전.으로 표기.
    	//$vTim = "(".floor($vPastSecond / 60)."mins ago)";
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
	//$line ="\n<b>----=============----</b>\n"; 
	$line ="\n\n"; 


	//$tail=$line."<a href='http://naver.com'>NAVER</a> | <a href='http://daum.net'>DAUM</a> | <a href='http://nate.com'>NATE</a>";
	//$vMsg = $vMailBox."From ".$vNum.$vDownArrow."\nTo ".$vMyn." ".$vTim."\n\n".$vMsg;

	$vReturn = $vIcon." ".$who." (".$vDpl.")".$vDownArrow.$line.$vMsg.$line;
  if ( $vMyn != '' ) $vReturn .= $vEmoji." ".$vMyn." ";
  $vReturn .= $vTim;
	//$vReturn .= $tail.$line;
	
  return $vReturn;
  
}


/*

# 기본환경설정
define('TOKEN_KEY','114864593:AAFoLCPVdfLV5TIu2ZO_IvECAIT7eKUbX-c/');
define('BASE_URL', 'https://api.telegram.org/bot'.TOKEN_KEY);

//리얼에서 사용할 SmsTelegramBot
//define('TOKEN_KEY',' 164152133:AAF6fmt-vGDcGkjTtWtrbaM6EuhbtfQ4I_U ');
 {"ok":true,"result":{"id":164152133,"first_name":"SmsTelegramBot","username":"SmsTelegramBot"}}
{"ok":true,"result":{"id":114864593,"first_name":"\uc559\ub7a9_\ud14c\uc2a4\ud2b8_\ubd07","username":"anglab_test_bot"}}

{"ok":true,"result":[{"update_id":46572659, "message":{"message_id":190,"from":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131"},"chat":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131","type":"private"},"date":1457657439,"text":"??"}}]}
*/

/*

	//curl_close($handle);

	//return $response;

  if ( $response === false ) {
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
    
    echo "1";
    //{"ok":true,"result":{"message_id":3038,"from":,"chat":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131","type":"private"},"date":1458055773,"reply_to_message":{"message_id":2871,"from":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131"},"chat":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131","type":"private"},"date":1457622244,"text":"daum.net"},"text":"*daum.net*"}}
		if ( $parameters["method"] == "sendMessage" ) {
			echo "2";

			if ( $response['ok'] == true ) {

				fn_sql("UPDATE TB_TL005 SET TL_COMP_DH = FROM_UNIXTIME('".$response["result"]["date"]."'), MSG_ID = '".$response["result"]["message_id"]."', ERR_CD = '', ERR_DSC = '' LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND MODE = '$vMod' AND DPL = '$vDpl'");
			} else {
				echo "UPDATE TB_TL005 SET TL_COMP_DH = NULL, ERR_CD = '".$response["error_code"]."', ERR_DSC = '".$response['description']."', LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND MODE = '$vMod' AND DPL = '$vDpl'";
				fn_sql("UPDATE TB_TL005 SET TL_COMP_DH = NULL, ERR_CD = '".$response["error_code"]."', ERR_DSC = '".$response['description']."', LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND MODE = '$vMod' AND DPL = '$vDpl'");
			}
			
		} // 발송 완료 저장

    return false;
  } else {
    $response = json_decode($response, true);
    echo "1";
    //{"ok":true,"result":{"message_id":3038,"from":,"chat":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131","type":"private"},"date":1458055773,"reply_to_message":{"message_id":2871,"from":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131"},"chat":{"id":159284966,"first_name":"\uc774\ub984","last_name":"\uc131","type":"private"},"date":1457622244,"text":"daum.net"},"text":"*daum.net*"}}
		if ( $parameters["method"] == "sendMessage" ) {
			echo "2";
			if ( $response['ok'] == true ) {
				fn_sql("UPDATE TB_TL005 SET TL_COMP_DH = FROM_UNIXTIME('".$response["result"]["date"]."'), MSG_ID = '".$response["result"]["message_id"]."', ERR_CD = '', ERR_DSC = '' LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND MODE = '$vMod' AND DPL = '$vDpl'");
			} else {
				echo "UPDATE TB_TL005 SET TL_COMP_DH = NULL, ERR_CD = '".$response["error_code"]."', ERR_DSC = '".$response['description']."', LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND MODE = '$vMod' AND DPL = '$vDpl'";
				fn_sql("UPDATE TB_TL005 SET TL_COMP_DH = NULL, ERR_CD = '".$response["error_code"]."', ERR_DSC = '".$response['description']."', LST_UPD_DH = NOW() WHERE CON_ID = '$vConId' AND MODE = '$vMod' AND DPL = '$vDpl'");
			}
			
		} // 발송 완료 저장

    //if (isset($response['description'])) {
    //  error_log("Request was successfull: {$response['description']}\n");
    //}
    ///$response = $response['result'];
  }
  */
?>