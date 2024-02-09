<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ListView;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use miloschuman\highcharts\Highcharts;
use miloschuman\highcharts\SeriesDataHelper;
use miloschuman\highcharts\Highstock;
use miloschuman\highcharts\GanttChart;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Access Logs';
$this->params['breadcrumbs'][] = $this->title;

?>
<h1>Logs</h1>
<?php

$dataProvider1 = new \yii\data\ArrayDataProvider(['allModels' => $grafik1]);
$dataProvider2 = new \yii\data\ArrayDataProvider(['allModels' => $grafik2]);

echo Highcharts::widget([
    'options' => [
       'title' => ['text' => 'График №1'],
       'xAxis' => [
          'categories' => new SeriesDataHelper($dataProvider1, ['day'])
       ],
       'yAxis' => [
          'title' => ['text' => 'Количество запросов']
       ],
       'series' => [
          ['name' => 'Дата', 'data' => new SeriesDataHelper($dataProvider1, ['total'])],
       ]
    ]
 ]);

 echo Highcharts::widget([
    'scripts' => [
        'modules/exporting',
        'themes/grid-light',
    ],
    'options' => [
        'title' => [
            'text' => 'Combination chart',
        ],
        'xAxis' => [
            'categories' => new SeriesDataHelper($dataProvider2, ['day'])
        ],
        'labels' => [
            'items' => [
                [
                    'html' => 'Total fruit consumption',
                    'style' => [
                        'left' => '50px',
                        'top' => '18px',
                        'color' => new JsExpression('(Highcharts.theme && Highcharts.theme.textColor) || "black"'),
                    ],
                ],
            ],
        ],
        'series' => [
            [
                'type' => 'column',
                'name' => 'Jane',
                'data' => new SeriesDataHelper($dataProvider2, ['total']),
            ],
            [
                'type' => 'column',
                'name' => 'John',
                'data' => new SeriesDataHelper($dataProvider2, ['total']),
            ],
            [
                'type' => 'column',
                'name' => 'Joe',
                'data' => new SeriesDataHelper($dataProvider2, ['total']),
            ],
            [
                'type' => 'spline',
                'name' => 'Average',
                'data' => new SeriesDataHelper($dataProvider2, ['total']),
                'marker' => [
                    'lineWidth' => 2,
                    'lineColor' => new JsExpression('Highcharts.getOptions().colors[3]'),
                    'fillColor' => 'white',
                ],
            ],
            [
                'type' => 'pie',
                'name' => 'Total consumption',
                'data' => [
                    [
                        'name' => 'Jane',
                        'y' => 13,
                        'color' => new JsExpression('Highcharts.getOptions().colors[0]'), // Jane's color
                    ],
                    [
                        'name' => 'John',
                        'y' => 23,
                        'color' => new JsExpression('Highcharts.getOptions().colors[1]'), // John's color
                    ],
                    [
                        'name' => 'Joe',
                        'y' => 19,
                        'color' => new JsExpression('Highcharts.getOptions().colors[2]'), // Joe's color
                    ],
                ],
                'center' => [100, 80],
                'size' => 100,
                'showInLegend' => false,
                'dataLabels' => [
                    'enabled' => false,
                ],
            ],
        ],
    ]
]);


?>
