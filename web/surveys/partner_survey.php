<?php
require_once 'connection.php';
session_start();
$partner_id =  (string) filter_input(INPUT_GET, 'id');
$survey_id = (string) filter_input(INPUT_GET, 'srv');

$sessionid = session_id();
function getToken($length){
     $token = "";
     $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
     $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
     $codeAlphabet.= "0123456789";
     $max = strlen($codeAlphabet);

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[random_int(0, $max-1)];
    }

    return $token;
}
$id = "CR-".$survey_id."_".getToken(20);  
function Redirect($url, $permanent = false)
    {
    header('Location: ' . $url, true, $permanent ? 301 : 302);
    exit();
    }
if ($id == "xxxx" || !$id || !$survey_id) {
    echo "Invalid Parameters";
    exit;
}
$geopIp = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$_SERVER['REMOTE_ADDR']));
	$u_location = $geopIp['geoplugin_city']." ";
	$u_location .= $geopIp['geoplugin_regionName']." ";
	$u_location .= $geopIp['geoplugin_countryName']." ";
	$u_location .= $geopIp['geoplugin_areaCode']." ";
	if(empty($u_location) ){
		$geopIp = unserialize(file_get_contents('https://freegeoip.app/json/'.$_SERVER['REMOTE_ADDR']));
		$u_location = $geopIp['city']." ";
		$u_location .= $geopIp['region_name']." ";
		$u_location .= $geopIp['country_name']." ";
		$u_location .= $geopIp['zip_code']." ";
	}
if ($survey_id) {
    try {
        $ins_data['id'] = $id;
        $ins_data['partner_id'] = $partner_id;
        $ins_data['sessionid'] = $sessionid;
        $ins_data['survey_id'] = $survey_id;
        $ins_data['u_location'] = $u_location;

        $query = "INSERT into respondants(resp_id, partner_id, session_id, survey_id, adid, outcome, location) values (:id, :partner_id, :sessionid, :survey_id, 'Partner',  'DROPOFF', :u_location); ";
        $conn->prepare($query)->execute($ins_data);

        $query = "select survey_link from survey where survey_id = ? ";
        $result = $conn->prepare($query);
        $result->execute(array($survey_id));
        $result1 = $result->fetchAll(PDO::FETCH_ASSOC);

        $survey_link = $result1[0]['survey_link'];
    } catch (PDOException $e) {
      echo 'Error: ' . $e->getMessage() . '<br />';
    }

    $conn = null;
    $survey_link = str_replace("xxxx", $id, $survey_link);
    Redirect($survey_link, false);
}
?>