<?php
session_start();
require_once 'connection.php';

$arr['id'] = (string) filter_input(INPUT_GET, 'id');
$arr['status'] = (string) filter_input(INPUT_GET, 'status');

function Redirect($url, $permanent = false) {
    header('Location: ' . $url, true, $permanent ? 301 : 302);
    exit();
}

if ($arr['id'] == "xxxx" || !$arr['id']) {
    echo "error";
    exit;
}

$query = "select partner_id, survey_id, adid from respondants where resp_id= ? ";
$result = $conn->prepare($query);
$result->execute(array($arr['id']));
$result1 = $result->fetchAll(PDO::FETCH_ASSOC);

$arr['survey_id'] = $result1[0]['survey_id'];
$arr['partner_id'] = $result1[0]['partner_id'];
$arr['adid'] = $result1[0]['adid'];

switch ($arr['status']) {
    case "comp":
        $query = "UPDATE survey JOIN respondants
                SET completes = CASE 
                	WHEN respondants.changed IS NULL THEN completes + 1 
                  else 
                    completes
                END,
                  is_live = CASE
                      WHEN completes >= current_target THEN 0
                	ELSE 
                      is_live
                END
                
                WHERE survey.survey_id = :survey_id AND respondants.resp_id = :id ;";
                        $query .= "UPDATE respondants
                SET outcome= CASE
                   WHEN changed IS NULL THEN 'Complete'
                   ELSE 
                     outcome
                END
                WHERE resp_id = :id ;";
        break;
    case "term":
        $query = "UPDATE survey JOIN respondants
            set terminates = case 
            	WHEN respondants.changed IS NULL THEN terminates + 1 
            	else terminates
            END,
            is_live = CASE
            WHEN completes >= current_target THEN 0
            	ELSE is_live
            END
            WHERE survey.survey_id = :survey_id AND respondants.resp_id = :id ;";
                    $query .= "UPDATE respondants
            SET outcome= CASE
               WHEN changed IS NULL THEN 'Terminate'
               ELSE outcome
            END
            WHERE resp_id = :id ;";
                    break;
                case "quot":
                    $query = "UPDATE survey JOIN respondants
            set quota = case 
            	WHEN respondants.changed IS NULL THEN quota + 1 
            	else quota
            END,
            is_live = CASE
            WHEN completes >= current_target THEN 0
            	ELSE is_live
            END
            WHERE survey.survey_id = :survey_id AND respondants.resp_id = :id ;";
                    $query .= "UPDATE respondants
            SET outcome= CASE
               WHEN changed IS NULL THEN 'Quota-Full'
               ELSE outcome
            END
            WHERE resp_id = :id ;";
                    break;
                case "test":
                    $query .= "UPDATE respondants
            SET outcome= CASE
               WHEN changed IS NULL THEN 'Test'
               ELSE outcome
            END
            WHERE resp_id = :id ;";
}

$result2 = $conn->prepare($query);
$result2->bindParam(':survey_id', $arr['survey_id'], PDO::PARAM_INT);
$result2->bindValue(':id', $arr['id'], PDO::PARAM_STR);
$result2->execute();
// print_r($result2->debugDumpParams());

if ($result2) {
    echo "thank you for your time";
    if ($arr['adid'] == 'Partner') {
    switch ($arr['status']) {
        case 'comp' : $redirect = 'comp_redirect';
            break;
        case 'term' : $redirect = 'term_redirect';
            break;
        case 'quot' : $redirect = 'quot_redirect';
    }
    
    $query = 'SELECT '. $redirect .' FROM partner WHERE survey_id = ? ';
    $result = $conn->prepare($query);
    $result->execute(array($arr['survey_id']));
    $row = $result->fetchAll(PDO::FETCH_ASSOC);
    $redirect = $row[0]['comp_redirect'];

    $redirect = str_replace("xxxx", $arr['partner_id'], $redirect);
    Redirect($redirect, false);
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