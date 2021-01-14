<?php
require_once 'connection.php';
session_start();
$partner_id =  (string) filter_input(INPUT_GET, 'id');
$survey_map_id = (string) filter_input(INPUT_GET, 'srv');

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

// Dynamic respondent id
$id = "CR-".$survey_map_id."_".getToken(20);  
/* Validate required survey for a partner completed - Start here */
$query = "SELECT com.field_completes_value AS cv, sr.field_total_survey_required_value AS rs  
          FROM node__field_total_survey_required AS sr
          JOIN node__field_completes AS com ON sr.entity_id = com.entity_id
          WHERE sr.entity_id = ? ";
$result = $conn->prepare($query);
$result->execute([$survey_map_id]);
$result1 = $result->fetchAll(PDO::FETCH_ASSOC);

if ($result1[0]['cv'] > $result1[0]['rs']) {
  $survey_link = getRedurectUrl($conn, $survey_map_id);
  $survey_link = str_replace("xxxx", $id, $survey_link);
  Redirect($survey_link, false);
  die;  
}
/* Validate required survey for a partner completed - End here */

function Redirect($url, $permanent = false) {
  header('Location: ' . $url, true, $permanent ? 301 : 302);
  exit();
}

if ($id == "xxxx" || !$id || !$survey_map_id) {
  echo "Invalid Parameters";
  exit;
}

$geopIp = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$_SERVER['REMOTE_ADDR']));
$u_location = $geopIp['geoplugin_city']." ";
$u_location .= $geopIp['geoplugin_regionName']." ";
$u_location .= $geopIp['geoplugin_countryName']." ";
$u_location .= $geopIp['geoplugin_areaCode']." ";

if (empty($u_location) ){
  $geopIp = unserialize(file_get_contents('https://freegeoip.app/json/'.$_SERVER['REMOTE_ADDR']));
  $u_location = $geopIp['city']." ";
  $u_location .= $geopIp['region_name']." ";
  $u_location .= $geopIp['country_name']." ";
  $u_location .= $geopIp['zip_code']." ";
}

if ($survey_map_id) {
    try {
        $ins_data['resp_id'] = $id;
        $ins_data['partner_id'] = $partner_id;
        $ins_data['session_id'] = $sessionid;
        $ins_data['survey_map_id'] = $survey_map_id;
        $ins_data['location'] = $u_location;
        $ins_data['created'] = strtotime('now');
        $ins_data['changed'] = 0;

        $query = "INSERT INTO respondents_term(id, resp_id, partner_id, session_id, survey_id, adid, outcome, location, created, changed) 
        VALUES (NULL, :resp_id, :partner_id, :session_id, :survey_map_id, 'Partner',  'DROPOFF', :location, :created, :changed) ";
        $result = $conn->prepare($query);
        $result->execute($ins_data);

        $survey_link = getSurveyUrl($conn, $survey_map_id);
    } catch (PDOException $e) {
      echo 'Error: ' . $e->getMessage() . '<br />';
    }

    $survey_link = str_replace("xxxx", $id, $survey_link);
    Redirect($survey_link, false);
}


function getSurveyUrl($conn, $survey_map_id) {
  $query = "SELECT sl.field_survey_link_value AS survey_link  
                  FROM node__field_survey_link AS sl
                  JOIN node__field_survey_name as sn ON sl.entity_id = sn.field_survey_name_target_id
                  WHERE sn.entity_id = ? ";
  $result = $conn->prepare($query);
  $result->execute([$survey_map_id]);
  $result1 = $result->fetchAll(PDO::FETCH_ASSOC);
  $survey_link = $result1[0]['survey_link'];
  return $survey_link;
}

function getRedurectUrl($conn, $survey_map_id) {
  $query = "SELECT qf.field_quota_full_redirect_value AS qf_link  
                  FROM node__field_quota_full_redirect AS qf
                  WHERE qf.entity_id = ? ";
  $result = $conn->prepare($query);
  $result->execute([$survey_map_id]);
  $result1 = $result->fetchAll(PDO::FETCH_ASSOC);
  $survey_link = $result1[0]['qf_link'];
  return $survey_link;
}

?>