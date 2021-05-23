<?php


namespace app\models;


use yii\base\ErrorException;
use yii\base\Model;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use yii\helpers\Url;
use ZipArchive;

class BaseParser extends Model
{
    /**
     * regx for parsing 'url()' in css code
     */
    const CSS_PARSE_IMG_REGX = '/url\(([\s])?([\"|\'])?(.*?)([\"|\'])?([\s])?\)/i';

    /**
     * folder to save parsing files
     */
    const FOLDER_TO_SAVE = 'download/';

    /**
     * max execution time
     */
    const TIME_LIMIT = 0;

    /**
     * @var \phpQueryObject|\QueryTemplatesParse|\QueryTemplatesSource|\QueryTemplatesSourceQuery
     */
    public $dom;

    /**
     * folder to save parsing files
     * @var string
     */
    public $save_dir;

    /**
     * path to parsing_files.zip
     * @var string
     */
    public $zip_path;

    /**
     * link to download parsing_files.zip
     * @var string
     */
    public $download_link;

    /**
     * parsing link that the user entered
     * @var string
     */
    public $url;

    /**
     * parsing link without query params that the user entered
     * @var string
     */
    public $base_url;

    /**
     * result of curl get
     * @var bool|mixed|string|null
     */
    public $response;

    /**
     * name of project
     * @var string
     */
    public $project_name;

    /**
     * list of errors during parsing
     * @var array
     */
    public $errors = [];


    /**
     * convert to zip file parsing folder
     */
    public function createZipFile()
    {
        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($this->zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->save_dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
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
    public function removeTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * удаляет базовый url из строки
     */
    public function urlReplace($str)
    {
        return str_replace($this->url, '', $str);
    }

    /**
     * Сохранение файлов
     * @param $fullPath
     * @param $content_path
     * @param int $flags
     */
    public function saveFile($fullPath, $content_path, $flags = 0)
    {
        try {
            $content = file_get_contents($content_path);
            $parts = explode('/', $fullPath);
            array_pop($parts);
            $dir = implode('/', $parts);

            if (!is_dir($dir))
                mkdir($dir, 0755, true);
            file_put_contents(strtok($fullPath, '?'), $content, $flags);
        } catch (ErrorException $e) {
            $this->errors[] = $content_path;
        }
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
}