<?php
namespace Arhframe\Util;

/**
 *
 */
class File
{
    /**
     * @var string
     */
    private $folder;
    /**
     * @var
     */
    private $basename;
    /**
     * @var
     */
    private $filename;
    /**
     * @var
     */
    private $extension;
    /**
     * @var bool
     */
    private $isUrl = false;

    /**
     * @param $file
     */
    function __construct($file)
    {
        if (preg_match('#^http(s){0,1}://#', $file)) {
            $this->isUrl = true;
        }
        $pathinfo = pathinfo($file);
        $this->folder = $pathinfo['dirname'];
        if ($this->folder == '.') {
            $this->folder = '';
        }
        $this->basename = $pathinfo['basename'];
        $this->filename = $pathinfo['filename'];
        $this->extension = $pathinfo['extension'];
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        $folder = $this->folder;
        if (!is_dir($this->folder) && $folder[0] != '/') {
            $folder = '/' . $folder;
        }
        return $folder;
    }

    /**
     * @param $folder
     * @return mixed
     */
    public function setFolder($folder)
    {
        return $this->folder = $folder;
    }

    /**
     * @param $basename
     * @return mixed
     */
    public function setName($basename)
    {
        return $this->basename = $basename;
    }

    /**
     * @return mixed
     */
    public function getBase()
    {
        return $this->filename;
    }

    /**
     * @param $filename
     * @return mixed
     */
    public function setBase($filename)
    {
        return $this->filename = $filename;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param $extension
     * @return mixed
     */
    public function setExtension($extension)
    {
        return $this->extension = $extension;
    }

    /**
     * @return mixed|null|string
     * @throws UtilException
     */
    public function getContent($binaryMode = false)
    {

        if ($this->isUrl) {
            return $this->httpGetContent($this->absolute());
        }
        if (!$this->isFile()) {
            throw new UtilException("File '" . $this->absolute() . "' doesn't exist.");
        }
        if ($binaryMode) {
            $binaryMode = 'b';
        } else {
            $binaryMode = null;
        }
        $handle = fopen($this->absolute(), 'r' . $binaryMode);
        $return = null;
        while (($buffer = fgets($handle)) !== false) {
            $return .= $buffer;
        }
        if (!feof($handle)) {
            throw new UtilException("Error: unexpected fgets() fail\n");
        }
        fclose($handle);
        return $return;
    }

    /**
     * @param $content
     */
    public function setContent($content)
    {
        $this->createFolder();
        file_put_contents($this->absolute(), $content);
    }

    private function httpGetContent($url)
    {
        if (function_exists('curl_exec')) {
            return $this->curlGetContent($url);
        }
        $context = Proxy::createStreamContext();
        if (empty($context)) {
            return file_get_contents($url);
        }
        return file_get_contents($url, false, $context);
    }

    /**
     * @param $url
     * @return mixed
     */
    private function curlGetContent($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $proxy = Proxy::getProxyHttp();
        if (!empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * @return string
     */
    public function absolute()
    {
        $extension = null;
        if (isset($this->extension)) {
            $extension = '.' . $this->extension;
        }
        return $this->folder . '/' . $this->filename . $extension;
    }

    /**
     * @return bool
     */
    public function isFile()
    {
        return is_file($this->absolute());
    }

    /**
     *
     */
    public function createFolder()
    {
        if (is_dir($this->folder)) {
            return;
        }
        mkdir($this->folder, 0777, true);
    }

    /**
     * @throws UtilException
     */
    public function touch()
    {
        if (!$this->isFile()) {
            throw new UtilException("File '" . $this->absolute() . "' doesn't exist.");
        }
        touch($this->absolute());
    }

    /**
     * @throws UtilException
     */
    public function getTime()
    {
        if (!$this->isFile()) {
            throw new UtilException("File '" . $this->absolute() . "' doesn't exist.");
        }
        filemtime($this->absolute());
    }

    /**
     * @return int
     * @throws UtilException
     */
    public function getSize()
    {
        if (!$this->isFile()) {
            throw new UtilException("File '" . $this->absolute() . "' doesn't exist.");
        }
        return filesize($this->absolute());
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return explode('/', $this->absolute());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->absolute();
    }

    /**
     * @throws UtilException
     */
    public function remove()
    {
        if (!$this->isFile()) {
            throw new UtilException("File '" . $this->absolute() . "' doesn't exist.");
        }
        unlink($this->absolute());
    }

    /**
     * @param $regex
     * @return int
     * @throws UtilException
     */
    public function match($regex)
    {
        if (!$this->isFile()) {
            throw new UtilException("File '" . $this->absolute() . "' doesn't exist.");
        }
        return preg_match($regex, $this->getName());
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->basename;
    }

    /**
     * @return bool
     */
    public function isUrl()
    {
        return $this->isUrl;
    }

    /**
     * @param $algo
     * @param bool $rowOutput
     * @return string
     * @throws UtilException
     */
    public function getHash($algo, $rowOutput = false)
    {
        if (!$this->isFile()) {
            throw new UtilException("File '" . $this->absolute() . "' doesn't exist.");
        }
        return hash_file($algo, $this->absolute(), $rowOutput);
    }

    /**
     * @param $sha1ToCheck
     * @return bool
     * @throws UtilException
     */
    public function checksumSha1($sha1ToCheck)
    {
        return $sha1ToCheck == $this->getHash('sha1');
    }

    /**
     * @param $md5ToCheck
     * @return bool
     * @throws UtilException
     */
    public function checksumMd5($md5ToCheck)
    {
        return $md5ToCheck == $this->getHash('md5');
    }
}
