<?php

namespace app\controllers;

use app\models\Landings;
use Throwable;
use Yii;
use yii\base\ErrorException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use linslin\yii2\curl;
use app\models\Parser;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
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
                'class' => VerbFilter::className(),
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
        return $this->render('index');
    }


    public  function actionDownload(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        set_time_limit(Parser::TIME_LIMIT);

        $model = new Parser();
        $landing = new Landings();

        try {
            $model->parseRun();
            $landing->newLanding($model);

            return [
                'status' => true,
                'link' => $model->download_link,
                'errors' => $model->errors
            ];
        }catch (ErrorException $e){
            $landing->parseError($e);

            return [
                'status' => false,
                'message' => $e,
                'errors' => $model->errors
            ];
        }
    }


}
