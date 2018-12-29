<?
// ini_set('max_execution_time', 120);  
  // http://blog.habonyphp.com/entry/php-%EC%B5%9C%EB%8C%80-%EC%8B%A4%ED%96%89-%EC%8B%9C%EA%B0%84%EC%9D%84-%EC%A0%9C%ED%95%9C%ED%95%98%EB%8A%94-settimelimit-%ED%95%A8%EC%88%98#.VtnK0PmLRYJ
// echo ini_get('max_execution_time'); 
 //echo ini_get('safe_mode');
 //return;
curl_request_async("http://anglab.pe.hu/cron01.php", array("a"=>"b"));

function curl_request_async($url, $params, $type='POST')  {  
    foreach ($params as $key => &$val)  
    {  
        if (is_array($val))  
            $val = implode(',', $val);  
        $post_params[] = $key.'='.urlencode($val);  
    }  
    $post_string = implode('&', $post_params);  
  
    $parts=parse_url($url);  
  
    if ($parts['scheme'] == 'http')  
    {  
        $fp = fsockopen($parts['host'], isset($parts['port'])?$parts['port']:80, $errno, $errstr, 30);  
    }  
    else if ($parts['scheme'] == 'https')  
    {  
        $fp = fsockopen("ssl://" . $parts['host'], isset($parts['port'])?$parts['port']:443, $errno, $errstr, 30);  
    }  
  
    // Data goes in the path for a GET request  
    if('GET' == $type)  
        $parts['path'] .= '?'.$post_string;  
  
    $out = "$type ".$parts['path']." HTTP/1.1\r\n";  
    $out.= "Host: ".$parts['host']."\r\n";  
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";  
    $out.= "Content-Length: ".strlen($post_string)."\r\n";  
    $out.= "Connection: Close\r\n\r\n";  
    // Data goes in the request body for a POST request  
    if ('POST' == $type && isset($post_string))  
        $out.= $post_string;  
  
    fwrite($fp, $out);  
    fclose($fp);  
}  
?>