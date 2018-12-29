<?
include_once "comn.php";

	$result1 = fn_sql("SELECT MAX(UPDATE_ID) AS UPDATE_ID FROM TB_TEMP"); // 저장된 맥스값 기준이며, SND_DH가 NULL인건 차후 다시 실행
	$row = mysqli_fetch_array($result1);
	$vMaxUpdateId = $row['UPDATE_ID'];

	$vOffset = $vMaxUpdateId + 1;

echo '{"offset":"'.$vOffset .'"}';

/*
sleep(10);
// getUpdatesBot이 20초마다 실행되기때문에 10초지연 페이지를 만들어서 결론적으로 10초마다 실행되도록 구현
include "getUpdatesBot.php";
*/
?>