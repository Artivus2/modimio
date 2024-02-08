<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ListView;
use phpnt\chartJS\ChartJs;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Access Logs';
$this->params['breadcrumbs'][] = $this->title;

?>
<h1>Logs</h1>
<?php
$masCnt = array_column($masDate, 'cnt', 'date');
var_dump($masCnt);
//$masCntBrows = array_column($masDateTopBrowsers, 'cnt', 'date');
// $start = $masDate[0]['date'];
// $interval = new DateInterval('P1D');
// $end = strtotime("+1 day", $masDate[0]['date']);
// $period = new DatePeriod($start, $interval, $end, DatePeriod::INCLUDE_END_DATE);

//$start = new dateTime($masDate[0]['date']);
//var_dump($masDate[0]);
//$interval = new DateInterval('P1D');
//$end = new DateTime(end($masDate)['date']. "+1day");
//$period = new DatePeriod($start, $interval, $end);

// При переборе экземпляра DatePeriod в цикле будут отображены все отобранные даты
// периода.
?>