<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Logs;
use app\models\LogsSearch;
use yii\db\Query;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LogsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $countDates = [];
        $countRequestPerDay = [];
        $topUrlPerDay = [];
        $topBrowserPerDay = [];
        $graphXDates = [];
        $graphYcountPerDat = [];
        $graphXpercentOfCountTop3 = [];
        $grafik1 = Logs::find()->select(["FROM_UNIXTIME(date,'%Y-%m-%d') as day, count(*) as total"])->groupBy(["day"])->asArray()->all();
        $grafik2 = Logs::find()->select(["FROM_UNIXTIME(date,'%Y-%m-%d') as day, count(*) as total"])->groupBy(["day"])->asArray()->all();

        // Выполняем запрос к базе данных
        // $query = Logs::find();
        // $topBrowser=[];
        // $masTopBrowsers = $query->select("browser")
        //     ->where($dataProvider->query->where)
        //     ->groupBy("browser")
        //     ->orderBy('count(1) desc')
        //     ->limit(3)
        //     ->asArray()
        //     ->all();
        // foreach($masTopBrowsers as $TopBrowser){
        //     $topBrowser[] = $TopBrowser['browser'];
        // }

        // $query = Logs::find();
        // $masDateTopBrowsers = $query->select(["DATE_FORMAT(date,'%y-%m-%d') date", "count(1) cnt"])
        //     ->where(
        //         [
        //             'browser'=>$topBrowser,
        //         ]
        //     )
        //     ->where($dataProvider->query->where)
        //     ->groupBy(["DATE_FORMAT(date,'%y-%m-%d')"])
        //     ->asArray()
        //     ->all();

        // $query = Logs::find();
        // $subQueryBrowser = (new Query())
        //     ->select('browser')
        //     ->where($dataProvider->query->where)
        //     ->from('logs')
        //     ->groupBy("browser")
        //     ->orderBy('count(1) desc')
        //     ->limit(1);
        // $subQueryurl = (new Query())
        //     ->select('url')
        //     ->where($dataProvider->query->where)
        //     ->from('logs')
        //     ->groupBy("url")
        //     ->orderBy('count(1) desc')
        //     ->limit(1);
        // $masDate = $query->select(["DATE_FORMAT(date,'%y-%m-%d') date", "count(1) cnt",'browser'=>$subQueryBrowser,'url'=>$subQueryurl])
        //     ->where($dataProvider->query->where)
        //     ->groupBy(["DATE_FORMAT(date,'%y-%m-%d')"])
        //     ->orderBy("date")
        //     ->asArray()
        //     ->all();

        return $this->render('index',compact('grafik1','grafik2','countRequestPerDay','topUrlPerDay','topBrowserPerDay','searchModel','dataProvider'));
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
