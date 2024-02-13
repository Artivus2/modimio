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
$dataProvider3 = new \yii\data\ArrayDataProvider(['allModels' => $topUrl]);
$dataProvider4 = new \yii\data\ArrayDataProvider(['allModels' => $table]);

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
          ['name' => 'Кол-во', 'data' => new SeriesDataHelper($dataProvider1, ['total'])],
       ]
    ]
 ]);


 var_dump($grafik2);
 echo Highcharts::widget([
    'scripts' => [
        'modules/exporting',
        'themes/grid-light',
    ],
    'options' => [
        'title' => [
            'text' => 'График 2',
        ],
        'xAxis' => [
            'categories' => new SeriesDataHelper($dataProvider2, ['day'])
        ],
        'labels' => [
            'items' => [
                [
                    'html' => 'Доля топ3 браузеров',
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
                'name' => 'Top 3',
                'data' => new SeriesDataHelper($dataProvider2, ['browser','total']),
            ],
            // [
            //     'type' => 'column',
            //     'name' => 'Top 2',
            //     'data' => new SeriesDataHelper($dataProvider2, ['browser','total']),
            // ],
            // [
            //     'type' => 'column',
            //     'name' => 'Top 3',
            //     'data' => new SeriesDataHelper($dataProvider2, ['browser','total']),
            // ],
            [
                'type' => 'spline',
                'name' => 'Average',
                'data' => new SeriesDataHelper($dataProvider2, ['total']),
                // 'marker' => [
                //     'lineWidth' => 2,
                //     'lineColor' => new JsExpression('Highcharts.getOptions().colors[3]'),
                //     'fillColor' => 'white',
                // ],
            ],
            
        ],
    ]
]);
?>
<div>
    <br>
    <h3>Таблица</h3>
    <?php
    $dataProvider = new ArrayDataProvider([
        'allModels' => $table,
        'sort' => [
            'attributes' => ['Дата', 'Число запросов', 'Самый популярный браузер','Самый популярный URL'],
        ],
        'pagination' => [
            'pageSize' => 10,
        ],
    ]);
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['attribute'=>'Дата','value'=>'day'],
            ['attribute'=>'Число запросов','value'=>'cnt'],
            ['attribute'=>'Самый популярный браузер','value'=>'browser'],
            ['attribute'=>'Самый популярный URL','value'=>'url'],
        ],
    ]); ?>
</div>

