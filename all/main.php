
<?php

session_start();

function checkHit($xVal, $yVal, $rVal)
{
    return ($xVal >= 0 && $yVal >= 0 && $rVal >= sqrt($xVal*$xVal + $yVal*$yVal))
        || ($xVal <= 0 && $yVal <= 0 && $yVal>=-$xVal-$rVal)
        || ($xVal >= 0 && $yVal <= 0 &&  $xVal<=$rVal/2 && $yVal>=-$rVal);
}

function validate($xVal, $yVal, $rVal, $timezone)
{
    return isset($xVal) && isset($yVal) && isset($rVal) && isset($timezone)
        && is_numeric($xVal) && is_numeric($yVal) && is_numeric($rVal) && is_numeric($timezone)
        && $xVal >= -5 && $xVal <= 3 && $yVal >= -5 && $yVal <= 5 && $rVal >= 1 && $rVal <= 3;
}

function getResultArray($xVal, $yVal, $rVal, $timezone)
{
    $results = array();

    foreach ($yVal as $value) {
        $isHit = checkHit($value, $xVal, $rVal);
        date_default_timezone_set('Europe/Moscow');
        $currentTime = date("H:i:s");
        $executionTime = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 7);

        array_push($results, array(
            "x" => $xVal,
            "y" => $value,
            "r" => $rVal,
            "currentTime" => $currentTime,
            "execTime" => $executionTime,
            "isHit" => $isHit
        ));
    }

    return $results;
}

function generateTableWithRows($results)
{
    $html = '';

    foreach ($results as $elem)
        $html .= generateRow($elem);

    return $html;
}

function generateRow($elem)
{
    $isHit = $elem['isHit'] ? 'Yes' : 'No';
    $elemHtml = $elem["isHit"] ? '<tr class="hit-yes">' : '<tr class="hit-no">';
    $elemHtml .= '<td>' . $elem['x'] . '</td>';
    $elemHtml .= '<td>' . $elem['y'] . '</td>';
    $elemHtml .= '<td>' . $elem['r'] . '</td>';
    $elemHtml .= '<td>' . $elem['currentTime'] . '</td>';
    $elemHtml .= '<td>' . $elem['execTime'] . '</td>';
    $elemHtml .= '<td>' . $isHit . '</td>';
    $elemHtml .= '</tr>';

    return $elemHtml;
}

function clear()
{
    $_SESSION['results'] = array();
}

function print_error()
{
    echo "Error: invalid values given.";
}

// $state = $_POST['state'];
$state = isset($_POST['state']) ? $_POST['state'] : '';

if ($state == 1) {
    if (isset($_SESSION['results']))
        foreach (array_reverse($_SESSION['results']) as $element) echo generateTableWithRows($element);
} else if ($state == 2) {
    clear();
} else if ($state == 0) {
    $xVal = @$_POST["x"];
    $yVal = @$_POST["y"];
    $rVal = @$_POST["r"];
    $timezone = @$_POST["timezone"];

    if (validate($xVal, $yVal, $rVal, $timezone)) {
        $yVal = explode(",", $_POST['y']);

        $results = getResultArray($xVal, $yVal, $rVal, $timezone);

        if (!isset($_SESSION['results'])) {
            $_SESSION['results'] = array($results);
        } else {
            array_push($_SESSION['results'], $results);
        }

        foreach (array_reverse($_SESSION['results']) as $element) echo generateTableWithRows($element);
    } else {
        if (!isset($_SESSION['results'])) {
            $_SESSION['results'] = array();
        }
        print_error();
    }
    
} else {
    print_error();
}

?>