<?
include_once "comn.php";
//sendGCM("", $arr); // 푸시알림 전송

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
	
	$fields = array( 'registration_ids' => $registrationIDs, 'data' => $pData );
	$headers = array( 'Authorization: key=' . $apiKey, 'Content-Type: application/json' );
	
	// Open connection
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $fields));
	$result = curl_exec($ch);
	curl_close($ch);

	echo $result;
	//print_r($result);
	//var_dump($result);
  return $result;
}


/*
{"multicast_id":6039459666172689590,"success":1,"failure":0,"canonical_ids":0,"results":[{"message_id":"0:1458658437678538%53da76ecf9fd7ecd"}]}

03-17 00:34:47.424: D/Rece11ived:(21286): Bundle[mParcelledData.dataSize=196]
03-17 00:34:47.424: D/TAG(21286): from 174732147215 (java.lang.String)
03-17 00:34:47.424: D/TAG(21286): message 한글 테스트 TEST!! (java.lang.String)
03-17 00:34:47.424: D/TAG(21286): collapse_key do_not_collapse (java.lang.String)
03-17 00:34:47.444: I/icelancer(21286): Working... 1/5 @ 952974042
03-17 00:34:47.444: I/Raaeceived:(21286): Bundle[{from=174732147215, message=한글 테스트 TEST!!, android.support.content.wakelockid=2, collapse_key=do_not_collapse}]

*/
?>
