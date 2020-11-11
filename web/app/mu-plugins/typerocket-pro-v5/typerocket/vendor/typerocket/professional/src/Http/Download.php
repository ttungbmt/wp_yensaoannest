<?php
namespace TypeRocketPro\Http;

class Download
{
    protected $location;
    protected $name;
    protected $headers;
    protected $type = 'attachment';

    /**
     * Download constructor.
     *
     * @param string $location
     */
    public function __construct($location)
    {
        $this->location = $location;
    }

    /**
     * @param string $location
     *
     * @return static
     */
    public static function new($location)
    {
        return new static($location);
    }

    /**
     * @param null|string $name
     * @param array|null $headers
     * @param null|string $type
     *
     * @link https://www.php.net/manual/en/function.readfile.php
     */
    public function send($name = null, ?array $headers = null, $type = null)
    {
        \TypeRocket\Http\Response::getFromContainer()->setDownloadHeaders($this->location, $name, $headers ?? $this->headers, $type ?? $this->type );
        readfile($this->location);
        exit();
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param null|array $headers
     *
     * @return $this
     */
    public function setHeaders(?array $headers)
    {
        $this->headers = $headers;

        return $this;
    }
}