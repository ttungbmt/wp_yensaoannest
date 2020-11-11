<?php
namespace TypeRocketPro\Extensions;

use TypeRocket\Core\Config;
use TypeRocket\Models\Model;
use TypeRocket\Utility\Helper;
use TypeRocket\Utility\Str;
use TypeRocketPro\Utility\Http;
use TypeRocketPro\Utility\Storage;

class StaticPageGenerator
{
    protected $drive;
    protected $folder;
    protected $ignore;

    /**
     * You can use this extension to generate static file for
     * your web server to access. The side effects will
     * vary depending on your setup.
     *
     * For example in Nginx you might have the following
     * when using the default root storage drive:
     *
     * location / {
     *    try_files /tr_static$request_uri.html /tr_static$request_uri/index.html $uri $uri/ /index.php?$query_string;
     * }
     *
     */
    public function __construct()
    {
        add_action('typerocket_controller_update', function($controller, $model, $user) {
            $class = get_class($model);
            $generator_key =  $class.':'.$model->getID();

            if(!method_exists($model, 'permalink')) {
                return;
            }

            $models = Config::get('static.models', [
                Helper::modelClass('Post', false),
                Helper::modelClass('Page', false),
            ]);

            $models = apply_filters('typerocket_static_page_generator_models', $models);

            $options = array_map(function($class) {
               return ltrim($class, '\\');
            }, $models);

            if(in_array($class, $options)) {
                $this->cache($model, $generator_key);
            }
        }, 10, 3);
    }

    /**
     * @param Model $model
     * @param string $generator_key
     */
    public function cache(Model $model, string $generator_key)
    {
        $this->drive = Config::get('static.drive', 'root');
        $this->folder = Config::get('static.folder', 'tr_static');
        $this->ignore = Config::get('static.ignore', []);

        $file_name = $this->folder . Str::replaceFirst(site_url(), '', $model->permalink());
        $file1 = rtrim($file_name, '/') . '/index.html';
        $file2 = rtrim($file_name, '/') . '.html';
        $ignore = $file2 === $this->folder . '.html';

        $this->delete($file1);
        $ignore ?: $this->delete($file2);

        if(!in_array($generator_key, $this->ignore)) {
            $content = Http::get($model->permalink())->headers(['X-No-Cache: Yes'])->exec()->body();
            $content = apply_filters('typerocket_static_page_generator_content', $content);

            $this->replace($file1, $content);
            $ignore ?: $this->replace($file2, $content);
        }
    }

    /**
     * @param string $file file path relative to drive
     * @param string $content content to cache
     */
    public function replace(string $file,string $content)
    {
        Storage::driver($this->drive)->replace($file, $content);
    }

    /**
     * @param string $file file path relative to drive
     */
    public function delete(string $file)
    {
        Storage::driver($this->drive)->delete($file);
    }
}