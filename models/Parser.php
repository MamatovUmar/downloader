<?php


namespace app\models;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use Yii;
use yii\base\Model;
use linslin\yii2\curl;
use yii\helpers\Html;
use yii\helpers\Url;
use ZipArchive;

class Parser extends Model
{
    const CSS_PARSE_IMG_REGX = '/url\(([\s])?([\"|\'])?(.*?)([\"|\'])?([\s])?\)/i';
    const FOLDER_TO_SAVE = 'download/';
    const TIME_LIMIT = 0;

    public $dom;
    public $save_dir;
    public $zip_path;
    public $download_link;
    public $url;
    public $response;
    public $project_name;
    public $errors = [];

    public function __construct()
    {
        $this->url = Yii::$app->request->post('url');
        $page_name = Yii::$app->request->post('file_name');

        // $curl = new curl\Curl();
        // $this->response = $curl->get($this->url . $page_name);
       $this->response = file_get_contents($this->url . $page_name);

        $this->dom = \phpQuery::newDocument($this->response);
        $this->dom->find('base')->remove();
        // dump(Html::encode(pq($this->dom)->html()));
//        $this->dom = \phpQuery::newDocument($load_data->find("html:last"));
        $this->generatePathToSave();

        parent::__construct();
    }

    /***
     * генерация пути для сохранение файлов
     */
    public function generatePathToSave(){
        $this->project_name = createAlias($this->dom->find("title")->text()) . '_' . date("Y-m-d__H_i_s");

        $this->save_dir = PROJECT_DIR . '/' . self::FOLDER_TO_SAVE . $this->project_name . '/';
        $this->zip_path = PROJECT_DIR . '/' . self::FOLDER_TO_SAVE . $this->project_name . '.zip';
        $this->download_link = Url::base(true) . '/' . self::FOLDER_TO_SAVE . $this->project_name . '.zip';

        mkdir( $this->save_dir, 0755, true );
    }

    /**
     * получение списка изображений из css файлов
     *
     * @param $file
     * @return array|array[]|string[]|\string[][]
     */
    public function cssParser($file, $get_content = true) {
        if($get_content){
            $content = file_get_contents($this->url . $file);
        }else{
            $content = $file;
        }
        if (!preg_match_all(self::CSS_PARSE_IMG_REGX, $content, $arr)) return array();

        $result = [];
        foreach ($arr[3] as $val) {
            if(filter_var($file, FILTER_VALIDATE_URL)){
               continue;
            }
            if(!$get_content){
                $result[] = $val;
                continue;
            }
                $arr = explode('/', $file);
            if(substr($val, 0, 2) === './') {
                $arr[count($arr) - 1] = str_replace('./', '', $val);
                $result[] = implode('/', $arr);
            }else if(!in_array(substr($val, 0, 1), ['.', '/'])){
                $arr[count($arr) - 1] = $val;
                $result[] = implode('/', $arr);
            }else if(str_contains($val, '../')){
                $dot_count = substr_count($val, "../");
                $new_file = str_replace('../', '', $val);

                $arr = explode('/', $file);
                array_splice($arr, -($dot_count + 1));
                $arr[] = $new_file;
                $result[] = implode('/', $arr);
            }
        }
        return $result;
    }

    /**
     * Сохранение файлов
     * @param $fullPath
     * @param $content_path
     * @param int $flags
     */
    public function saveFile($fullPath, $content_path, $flags = 0 ){
        try {
            $content = file_get_contents($content_path);
            $parts = explode( '/', $fullPath );
            array_pop( $parts );
            $dir = implode( '/', $parts );

            if( !is_dir( $dir ) )
                mkdir( $dir, 0755, true );
            file_put_contents( strtok($fullPath, '?'), $content, $flags );
        }catch (Throwable $e){
            $this->errors[] = $content_path;
        }
    }

    public function getImages(){
        $images = [];
        // get images from inline styles
        foreach ($this->dom->find("[style]") as $item) {
            $images[] = $this->cssParser(pq($item)->attr('style'), false)[0] ?? null;
        }

        //  get images from tag img
        foreach($this->dom->find("img") as $value){
            $images[] = $image = pq($value)->attr('src');
            if(substr($image, 0, 1) === '/' && substr($image, 0, 2) !== '//'){
                pq($value)->attr('src', substr($image, 1));
            }
        }

        return array_filter($images);
    }

    public function getOtherFiles(){
        $files = [];

        //  get css files
        foreach($this->dom->find("link") as $value){
            $pq = pq($value);

            $files[] = $file = $this->urlReplace($pq->attr('href'));
            if(substr($file, 0, 1) === '/' && substr($file, 0, 2) !== '//'){
                $pq->attr('href', substr($file, 1));
            }

            $path_parts = pathinfo($this->save_dir . $file);
            if($path_parts['extension'] == 'css'){
                $files = array_merge($files, $this->cssParser($file));
            }
        }

        //  get js files
        foreach($this->dom->find("script") as $value){
            $pq = pq($value);
            $files[] = $script = $this->urlReplace($pq->attr('src'));
            if(substr($script, 0, 1) === '/' && substr($script, 0, 2) !== '//'){
                $pq->attr('src', substr($script, 1));
            }
        }

        return array_filter($files);
    }

    public function createZipFile(){

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($this->zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->save_dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($this->save_dir));

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }

    /**
     * удалить папку с файлами
     * @param $dir
     * @return bool
     */
    public function removeTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * удаляет базовый url из строки
     */
    public function urlReplace($str){
        return str_replace($this->url, '', $str);
    }


    /**
     * запуск скачивание файлов лендинга
     */
    public function  parseRun(){

        $files = array_merge($this->getOtherFiles(), $this->getImages());

        //  download files
        foreach ($files as $file){
            $file = str_replace($this->url, '', $file);
            if(!filter_var($file, FILTER_VALIDATE_URL)){  // if no cdn
                $this->saveFile($this->save_dir . $file, $this->url . $file, LOCK_EX);
            }
        }

        file_put_contents( $this->save_dir . "index.html", pq($this->dom)->html());

        $this->createZipFile();
        $this->removeTree($this->save_dir);
    }

}