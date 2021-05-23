<?php


namespace app\models;

use Yii;
use linslin\yii2\curl;

class Parser extends BaseParser
{
    public function __construct()
    {
        $this->url = Yii::$app->request->post('url');
        $page_name = Yii::$app->request->post('file_name');
        dump($this->url);

        $curl = new curl\Curl();
        $this->response = $curl->get($this->url . $page_name);
//       $this->response = file_get_contents($this->url . $page_name);

        $this->dom = \phpQuery::newDocument($this->response);
        $this->dom->find('base')->remove();
        // dump(Html::encode(pq($this->dom)->html()));
//        $this->dom = \phpQuery::newDocument($load_data->find("html:last"));
        $this->generatePathToSave();

        parent::__construct();
    }


    /**
     * получение списка изображений из css файлов
     * @param $file
     * @param $get_content
     * @return array|array[]|string[]|\string[][]
     */
    public function cssParser($file, $get_content = true)
    {
        if ($get_content) {
            $content = file_get_contents($this->url . $file);
        } else {
            $content = $file;
        }
        if (!preg_match_all(self::CSS_PARSE_IMG_REGX, $content, $arr)) return array();

        $result = [];
        foreach ($arr[3] as $val) {
            if (filter_var($file, FILTER_VALIDATE_URL)) {
                continue;
            }
            if (!$get_content) {
                $result[] = $val;
                continue;
            }
            $arr = explode('/', $file);
            if (substr($val, 0, 2) === './') {
                $arr[count($arr) - 1] = str_replace('./', '', $val);
                $result[] = implode('/', $arr);
            } else if (!in_array(substr($val, 0, 1), ['.', '/'])) {
                $arr[count($arr) - 1] = $val;
                $result[] = implode('/', $arr);
            } else if (str_contains($val, '../')) {
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


    public function getImages()
    {
        $images = [];
        // get images from inline styles
        foreach ($this->dom->find("[style]") as $item) {
            $images[] = $this->cssParser(pq($item)->attr('style'), false)[0] ?? null;
        }

        //  get images from tag img
        foreach ($this->dom->find("img") as $value) {
            $images[] = $image = pq($value)->attr('src');
            if (substr($image, 0, 1) === '/' && substr($image, 0, 2) !== '//') {
                pq($value)->attr('src', substr($image, 1));
            }
        }

        return array_filter($images);
    }

    public function getOtherFiles()
    {
        $files = [];

        //  get css files
        foreach ($this->dom->find("link") as $value) {
            $pq = pq($value);

            $files[] = $file = $this->urlReplace($pq->attr('href'));
            if (substr($file, 0, 1) === '/' && substr($file, 0, 2) !== '//') {
                $pq->attr('href', substr($file, 1));
            }

            $path_parts = pathinfo($this->save_dir . $file);
            if ($path_parts['extension'] == 'css') {
                $files = array_merge($files, $this->cssParser($file));
            }
        }

        //  get js files
        foreach ($this->dom->find("script") as $value) {
            $pq = pq($value);
            $files[] = $script = $this->urlReplace($pq->attr('src'));
            if (substr($script, 0, 1) === '/' && substr($script, 0, 2) !== '//') {
                $pq->attr('src', substr($script, 1));
            }
        }

        return array_filter($files);
    }


    /**
     * запуск скачивание файлов лендинга
     */
    public function parseRun()
    {

        $files = array_merge($this->getOtherFiles(), $this->getImages());

        //  download files
        foreach ($files as $file) {
            $file = str_replace($this->url, '', $file);
            if (!filter_var($file, FILTER_VALIDATE_URL)) {  // if no cdn
                $this->saveFile($this->save_dir . $file, $this->url . $file, LOCK_EX);
            }
        }

        file_put_contents($this->save_dir . "index.html", pq($this->dom)->html());

//        $this->createZipFile();
//        $this->removeTree($this->save_dir);
    }

}