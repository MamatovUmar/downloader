<?php

namespace app\controllers;

use Sabberworm\CSS\Parser;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use linslin\yii2\curl;

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

    public function cssImageParser($file) {
        $css = file_get_contents($file);
        if (!preg_match_all('/url\(([\s])?([\"|\'])?(.*?)([\"|\'])?([\s])?\)/i', $css, $arr)) return array();
        return array_map(function($val) {
            return str_replace('../', '', $val);
        }, $arr[2]);
    }

    public  function actionDownload(){
        set_time_limit(100);
        $url = Yii::$app->request->post('url');



        $curl = new curl\Curl();
        $response = $curl->get($url);

        $dom = \phpQuery::newDocument($response);
        $dir = 'download/' . $dom->find("title")->text() . '/';

        $files = [];
        foreach($dom->find("link") as $key => $value){
            $files[] = pq($value)->attr('href');
        }

        foreach($dom->find("script") as $key => $value){
            $files[] = pq($value)->attr('src');
        }

        foreach($dom->find("img") as $key => $value){
            $files[] = pq($value)->attr('src');
        }
        $files = array_filter($files);
        $images = [];
        foreach ($files as $file){
            if(!filter_var($file, FILTER_VALIDATE_URL)){
                $this->saveFile($dir . $file, $url . $file, LOCK_EX);
                $path_parts = pathinfo($dir . $file);
                if($path_parts['extension'] == 'css'){
                    $images = array_merge($images, $this->cssImageParser($dir . $file));
                }
            }
        }

        $images = array_filter($images);
        foreach ($images as $image){
            if(!filter_var($image, FILTER_VALIDATE_URL)){
                $this->saveFile($dir . $image, $url . $image, LOCK_EX);
            }
        }
        dump($images);

        $index = fopen($dir . "index.html", "w");
        fwrite($index, $response);
        fclose($index);

        exit();
    }

    public function saveFile($fullPath, $content_path, $flags = 0 ){
        try {
            $content = file_get_contents($content_path);
            $parts = explode( '/', $fullPath );
            array_pop( $parts );
            $dir = implode( '/', $parts );

            if( !is_dir( $dir ) )
                mkdir( $dir, 0777, true );

            file_put_contents( $fullPath, $content, $flags );
        }catch (\Exception $e){

        }
    }

}
