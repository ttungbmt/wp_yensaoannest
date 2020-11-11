<?php
namespace TypeRocketPro\Utility\Drives;

use TypeRocketPro\Http\Download;
use TypeRocket\Utility\File;

class UploadsDrive extends Drive
{
    /**
     * @param string $file
     * @param string $content
     *
     * @return bool
     */
    public function create($file, $content) : bool
    {
        $file = $this->localPath($file);
        return File::new($file)->create($content)->exists();
    }

    /**
     * @param string $file
     * @param string $content
     *
     * @return bool
     */
    public function append($file, $content) : bool
    {
        $file = $this->localPath($file);
        return File::new($file)->append($content)->exists();
    }

    /**
     * @param string $file
     * @param string $content
     *
     * @return bool
     */
    public function replace($file, $content) : bool
    {
        $file = $this->localPath($file);
        return File::new($file)->replace($content)->exists();
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function delete($file) : bool
    {
        $file = $this->localPath($file);
        return File::new($file)->remove();
    }

    /**
     * @param string $file
     *
     * @return false|mixed|string|null
     */
    public function get($file)
    {
        $file = $this->localPath($file);
        return File::new($file)->read();
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function exists($file): bool
    {
        $file = $this->localPath($file);
        return File::new($file)->exists();
    }

    /**
     * @param string $file
     *
     * @return array|mixed|string|null
     */
    public function path($file)
    {
        return $this->localPath($file);
    }

    /**
     * @param string $file
     * @param string|null $name
     * @param array|null $headers
     * @param string|null $type
     *
     * @return Download
     */
    public function download($file, $name = null, ?array $headers = null, $type = null): Download
    {
        $file = $this->localPath($file);
        return Download::new($file)->setName($name)->setHeaders($headers)->setType($type);
    }

    /**
     * @param string $file
     *
     * @return false|int|mixed
     */
    public function size($file)
    {
        $file = $this->localPath($file);
        return File::new($file)->size();
    }

    /**
     * @param string $file
     *
     * @return false|int|mixed
     */
    public function lastModified($file)
    {
        $file = $this->localPath($file);
        return File::new($file)->lastModified();
    }

    /**
     * Storage File Path
     *
     * @param string|null $path storage file path
     *
     * @return array|mixed|null
     */
    protected function localPath($path = null) {
        return WP_CONTENT_DIR. '/uploads' . ( $path ? '/' . ltrim($path, '/') : '' );
    }
}