<?php


namespace app\models;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use Yii;
use yii\base\Model;
use linslin\yii2\curl;
use yii\helpers\Url;
use ZipArchive;

class Parser extends Model
{
    const CSS_PARSE_IMG_REGX = '/url\(([\s])?([\"|\'])?(.*?)([\"|\'])?([\s])?\)/i';
    const FOLDER_TO_SAVE = 'download/';
    const TIME_LIMIT = 180;

    public $dom;
    public $save_dir;
    public $zip_path;
    public $download_link;
    public $url;
    public $response;
    public $project_name;

    public function __construct()
    {
        $this->url = Yii::$app->request->post('url');

        $curl = new curl\Curl();
        $this->response = $curl->get($this->url);

        $this->dom = \phpQuery::newDocument($this->response);
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
     * получение списка изображений в css файлах
     *
     * @param $file
     * @return array|array[]|string[]|\string[][]
     */
    public function cssImageParser($file, $get_content = true) {
        if($get_content){
            $content = file_get_contents($file);
        }else{
            $content = $file;
        }
        if (!preg_match_all(self::CSS_PARSE_IMG_REGX, $content, $arr)) return array();

        return array_map(function($val) {
            return str_replace('../', '', $val);
        }, $arr[3]);
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
//            echo $content_path . "<br/>";
        }
    }

    public function getImages(){
        $images = [];
        // get images from inline styles
        foreach ($this->dom->find("[style]") as $item) {
            $images[] = $this->cssImageParser(pq($item)->attr('style'), false)[0] ?? null;
        }

        //  get images from tag img
        foreach($this->dom->find("img") as $value){
            $images[] = pq($value)->attr('src');
        }

        return array_filter($images);
    }

    public function getOtherFiles(){
        $files = [];

        //  get css files
        foreach($this->dom->find("link") as $value){
            $files[] = $file = pq($value)->attr('href');

            $path_parts = pathinfo($this->save_dir . $file);
            if($path_parts['extension'] == 'css'){
                $files = array_merge($files, $this->cssImageParser($this->url . $file));
            }
        }

        //  get js files
        foreach($this->dom->find("script") as $value){
            $files[] = pq($value)->attr('src');
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
     * запуск скачивание файлов лендинга
     */
    public function  parseRun(){

        $files = array_merge($this->getOtherFiles(), $this->getImages());

        //  download files
        foreach ($files as $file){
            if(!filter_var($file, FILTER_VALIDATE_URL)){  // if no cdn
                $this->saveFile($this->save_dir . $file, $this->url . $file, LOCK_EX);
            }
        }

        file_put_contents( $this->save_dir . "index.html", $this->response);

        $this->createZipFile();
        $this->removeTree($this->save_dir);
    }

}