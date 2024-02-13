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
        $grafik1 = Logs::find()->select(["FROM_UNIXTIME(date,'%Y-%m-%d') as day, count(*) as total"])->groupBy(["day"])->asArray()->orderBy('day asc')->all();
        
        // доля от общего числа топ 3 бразура график



        // top 3 браузера
        $query = Logs::find();
        $topBrowser=[];
        $TopBrowsers = $query->select("browser")
            ->where($dataProvider->query->where)
            ->groupBy("browser")
            ->orderBy('count(1) desc')
            ->limit(3)
            ->asArray()
            ->all();

        $grafik2 = Logs::find()->select(["FROM_UNIXTIME(date,'%Y-%m-%d') as day, browser, count(*) as total"])
        ->groupBy(["day","browser"])
        ->asArray()
        ->orderBy('day asc')
        ->where(["browser" => $TopBrowsers])
        ->all();


        foreach($TopBrowsers as $TopBrowser){
            $topBrowser[] = [
                "browser" => $TopBrowser['browser'],
                "data" => Logs::find()
                ->select(["FROM_UNIXTIME(date,'%Y-%m-%d') as day, count(*) as total"])
                ->groupBy(["day"])->asArray()
                ->orderBy('day asc')
                ->where(['browser'=>$TopBrowser])
                ->asArray()
                ->all()

            ];
            
        }

        // top 3  url
        $query = Logs::find();
            $topUrl=[];
            $TopUrls = $query->select("url")
                ->where($dataProvider->query->where)
                ->groupBy("url")
                ->orderBy('count(1) desc')
                ->limit(3)
                ->asArray()
                ->all();
        foreach($TopUrls as $TopUrl){
            $topUrl[] = [
                "url" => $TopUrl['url'],
                "data" => Logs::find()
                ->select(["FROM_UNIXTIME(date,'%Y-%m-%d') as day, count(*) as total"])
                ->groupBy(["day"])->asArray()
                ->orderBy('day asc')
                ->where(['url'=>$TopUrl])
                ->asArray()
                ->all()

            ];
            
        }
        
        $query = Logs::find();
        $subQueryBrowser = (new Query())
            ->select('browser')
            ->where($dataProvider->query->where)
            ->from('logs')
            ->groupBy("browser")
            ->orderBy('count(1) desc')
            ->limit(1);
        $subQueryurl = (new Query())
            ->select('url')
            ->where($dataProvider->query->where)
            ->from('logs')
            ->groupBy("url")
            ->orderBy('count(1) desc')
            ->limit(1);
        $table = $query->select(["FROM_UNIXTIME(date,'%Y-%m-%d') as day", "count(1) cnt",'browser'=>$subQueryBrowser,'url'=>$subQueryurl])
            ->where($dataProvider->query->where)
            ->groupBy(["day"])
            ->orderBy("day")
            ->asArray()
            ->all();

        return $this->render('index',compact('grafik1','grafik2','topBrowser','topUrl','table'));
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
