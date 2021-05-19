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
use yii\helpers\Html;

require_once "simple_html_dom.php";

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
        $patterns = [
            '/<(base)[^>]*>/i'
        ];

        
        // $html = file_get_html('https://woodland-mebel.ru/');
        // foreach($html->find('base') as $element)
        //     echo $element->href . '<br>';
        // dump($file->find('base'));
        // dump(Html::encode($file));
        // $file = Html::encode(file_get_contents('https://woodland-mebel.ru/'));


        // $file = '<!-- End Google Tag Manager -->

        // <base href="//woodland-mebel.ru">fgdf
        // <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">';


        // $p = '/<(base)[^>]*>/i';
        // dump(preg_replace($p, '', $file));




        return $this->render('index');
    }


    public  function actionDownload(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        set_time_limit(Parser::TIME_LIMIT);

        $model = new Parser();
        $landing = new Landings();
        // try {
            $model->parseRun();
            $landing->newLanding($model);

            return [
                'status' => true,
                'link' => $model->download_link,
                'errors' => $model->errors
            ];
        // }catch (ErrorException $e){
        //     $landing->parseError($e);

        //     return [
        //         'status' => false,
        //         'message' => $e,
        //         'errors' => $model->errors
        //     ];
        // }
    }


}
