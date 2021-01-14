<?php
require_once 'connection.php';

session_start();
$arr['id'] = (string) filter_input(INPUT_GET, 'id');
$arr['status'] = (string) filter_input(INPUT_GET, 'status');

function Redirect ($url, $permanent = false) {
  header('Location: ' . $url, true, $permanent ? 301 : 302);
  exit();
}

if ($arr['id'] == "xxxx" || !$arr['id']) {
  echo "error";
  exit;
}

$query = "select partner_id, survey_id, adid from respondents_term where resp_id= ? ";
$result = $conn->prepare($query);
$result->execute(array($arr['id']));
$result1 = $result->fetchAll(PDO::FETCH_ASSOC);

$arr['survey_id'] = $result1[0]['survey_id'];
$arr['partner_id'] = $result1[0]['partner_id'];
$arr['adid'] = $result1[0]['adid'];

/* Validate required survey for a partner completed - Start here */
$query = "SELECT com.field_completes_value AS cv, sr.field_total_survey_required_value AS rs  
          FROM node__field_total_survey_required AS sr
          JOIN node__field_completes AS com ON sr.entity_id = com.entity_id
          WHERE sr.entity_id = ? ";
$result = $conn->prepare($query);
$result->execute([$arr['survey_id']]);
$result1 = $result->fetchAll(PDO::FETCH_ASSOC);

if ($result1[0]['cv'] > $result1[0]['rs']) {
  $survey_link = getRedurectUrl($conn, $arr['survey_id']);
  // $survey_link = str_replace("xxxx", $id, $survey_link);
  Redirect($survey_link, false);
  die;  
}
/* Validate required survey for a partner completed - End here */

switch ($arr['status']) {
  case "comp":
    $query = "UPDATE respondents_term AS res 
      JOIN node__field_completes AS com ON res.survey_id = com.entity_id
      SET com.field_completes_value = CASE 
        WHEN res.changed IS NULL THEN com.field_completes_value + 1 
        ELSE 
          com.field_completes_value
        END
    WHERE com.entity_id = ? AND res.resp_id = ? ;";

    $query .= "UPDATE respondents_term AS res
      SET res.changed = ?
      SET res.outcome = CASE
        WHEN res.changed IS NULL THEN 'Complete'
        ELSE 
          res.outcome
        END
      WHERE res.resp_id = ? ;";
  break;

  case "term":
    $query = "UPDATE respondents_term AS res      
    JOIN node__field_terminates AS ter ON res.survey_id = ter.entity_id
      set ter.field_terminates_value = case 
        WHEN res.changed IS NULL THEN ter.field_terminates_value + 1 
          else ter.field_terminates_value
        END
      WHERE ter.entity_id = ? AND res.resp_id = ? ;";

    $query .= "UPDATE respondents_term AS res
      SET res.outcome= CASE 
        WHEN res.changed IS NULL THEN 'Terminate'
        ELSE 
          res.outcome
        END
      WHERE res.resp_id = ? ;";
  break;

  case "quot":
    $query = "UPDATE respondents_term AS res
      JOIN node__field_quota AS quo ON res.survey_id = quo.entity_id
      SET quo.field_quota_value = CASE 
        WHEN res.changed IS NULL THEN quo.field_quota_value + 1 
        ELSE 
          quo.field_quota_value
        END
      WHERE quo.entity_id = ? AND res.resp_id = ? ;";

    $query .= "UPDATE respondents_term AS res
      SET res.outcome = CASE
        WHEN res.changed IS NULL THEN 'Quota-Full'
        ELSE 
          res.outcome
        END
      WHERE res.resp_id = :id ;";
  break;

  case "test":
    $query .= "UPDATE respondents_term as res
      SET res.outcome = CASE
      WHEN res.changed IS NULL THEN 'Test'
      ELSE 
        res.outcome
      END
      WHERE res.resp_id = :id ;";
}

try {
$result = $conn->prepare($query);
$result->execute([$arr['survey_id'], $arr['id'], $arr['id']]);
// print_r($result->debugDumpParams());

  if ($result) {
    echo "thank you for your time";
    if ($arr['adid'] == 'Partner') {
      switch ($arr['status']) {
        case 'comp' :
          $table = 'node__field_completes_redirect'; 
          $redirect = 'field_completes_redirect_value';
          break;
        case 'term' : 
          $table = 'node__field_terminates_redirect';
          $redirect = 'field_terminates_redirect_value';
          break;
        case 'quot' : 
          $table = 'node__field_quota_full_redirect';
          $redirect = 'field_quota_full_redirect_value';
      }

      $query = 'SELECT '. $redirect .' FROM '. $table .' WHERE entity_id = ? ';
      $result = $conn->prepare($query);
      $result->execute([$arr['survey_id']]);
      $row = $result->fetchAll(PDO::FETCH_ASSOC);
      $redirect = $row[0][$redirect];

      $redirect = str_replace("xxxx", $arr['partner_id'], $redirect);
      Redirect($redirect, false);
    }
  }

} catch (PDOException $e) {
  echo 'Error: ' . $e->getMessage() . '<br />';
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

session_destroy();
?>

<html>
    <head>
        <title>Members Only Content</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <link rel="stylesheet" href="css/main.css" />
    </head>
    <body class="homepage is-preload">
        <div id="page-wrapper">
            <!-- Header -->
            <section id="header">
                <div class="container">
                    <!-- Logo -->
                    <h1 id="logo"><a href="/">Thank You </a></h1>
                    <header><h2>Your responses have been submitted and are being checked.</h2></header>
                    <a href="/" ><button class="form-button-submit button icon solid fa-envelope" value="Go To Dashboard" /></a>
                    <p>You will receive an email once this process is done.</p>
                    <!-- Nav -->
                </div>
            </section>
        </div>
    </body>
</html>