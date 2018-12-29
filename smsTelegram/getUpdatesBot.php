<?
include_once "comn.php";

//header('Content-Type: text/html; charset=utf-8');

# CURL Function
function GetCurl($url, $parameters = array()) {
  // 서버로 전송 및 결과 반환
  $rest = curl_init();
  curl_setopt($rest, CURLOPT_URL, $url);
  curl_setopt($rest, CURLOPT_POST, false);
  curl_setopt($rest, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($rest, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($rest, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  $Result = curl_exec($rest);
  curl_close($rest);
  return $Result;
}

function fn_getUpdatesList($pOffset) {
  if ( $pOffset == '' ) {
		$result1 = fn_sql("SELECT MAX(UPDATE_ID) AS UPDATE_ID FROM TB_TEMP"); // 저장된 맥스값 기준이며, SND_DH가 NULL인건 차후 다시 실행
		$row = mysqli_fetch_array($result1);
		$vMaxUpdateId = $row['UPDATE_ID'];
		$pOffset = $vMaxUpdateId + 1;
  }
  return $pOffset;
}

$vOffset = $_GET['offset'];
$start_time = array_sum(explode(' ', microtime()));
$before_end_time = $start_time;


//echo '{"s":'.sizeof($Result['result']).',"r":[';
for ( $k = 0; $k < 20; $k++ ) {
	$vLimit  = '100';
	$vOffset = fn_getUpdatesList($vOffset);
	$vResult = GetCurl(BASE_URL."getUpdates?offset=".$vOffset."&limit=".$vLimit );
  $Result  = json_decode($vResult, true);

	if (isset($Result["ok"]) ) {
		if ( $Result == true ) {
			if ( sizeof( $Result['result'] ) > 0 ) {
      $vOffset = ""; // 업데이트를 위해 널값
				for ( $i = 0; $i < sizeof($Result['result']); $i++ ) {
     			$vResult2 = GetCurl("http://anglab.dothome.co.kr/smsTelegram/webhooksBot.php", $Result['result'][$i]);
       		//echo $vResult2;
				}
			} else {
				// 대화가 없다
			}
		} else {
			//옵션에러
			exit();
		}
	} else {
		//통신에러
		exit();
	}

	$end_time = array_sum(explode(' ', microtime()));
	if ( $end_time - $start_time > 14 ) break; // 20초를 넘기지 않도록
	if ( $end_time - $before_end_time < 1 ) sleep(1); // 이전 실행시간과의 차이가 1초 이내면 sleep 1초
	//echo "TIME : ". ( $end_time - $start_time )."/". ( $end_time - $before_end_time )."<br>";
	$before_end_time = $end_time;
}
//echo "]}";



?>
