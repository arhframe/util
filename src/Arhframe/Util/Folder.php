<?php
namespace Arhframe\Util;

/**
 *
 */
class Folder
{
    /**
     * @var
     */
    private $folder;

    /**
     * @param $folder
     */
    function __construct($folder)
    {
        $this->folder = $folder;
    }

    /**
     * @param $var
     */
    public function append($var)
    {
        $this->folder = $this->folder . '/' . $var;
    }

    /**
     * @param $var
     */
    public function prepend($var)
    {
        $this->folder = $var . '/' . $this->folder;

    }

    /**
     * @param $var
     */
    public function replace($var)
    {
        $array = $this->getArray();
        $array[count($array) - 1] = $var;
        $this->folder = implode('/', $array);

    }

    /**
     * @return array
     */
    public function getArray()
    {
        $folder = $this->folder;
        if (DIRECTORY_SEPARATOR == '\\') {
            $folder = str_replace('\\', '/', $this->folder);
        }
        return explode('/', $folder);
    }

    /**
     *
     */
    public function pop()
    {
        $array = $this->getArray();
        unset($array[count($array) - 1]);
        $this->folder = implode('/', $array);
    }


    public function popReverse()
    {
        $array = $this->getArray();
        if (DIRECTORY_SEPARATOR == '\\' && $array[0][1] == ':') {
            unset($array[1]);
        } else {
            unset($array[0]);
        }
        $this->folder = implode('/', $array);
    }

    /**
     * @throws UtilException
     */
    public function truncate()
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        rmdir($this->absolute());
        $this->create();
    }

    /**
     * @return bool
     */
    public function isFolder()
    {
        return is_dir($this->folder);
    }

    /**
     * @return mixed
     */
    public function absolute()
    {
        return $this->folder;
    }

    /**
     * @param int $perm
     */
    public function create($perm = 0777)
    {
        if (is_dir($this->folder)) {
            return;
        }
        mkdir($this->folder, $perm, true);
    }

    /**
     * @param string $regex
     * @param bool $recursive
     * @throws UtilException
     */
    public function removeFiles($regex = null, $recursive = false)
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        foreach ($this->getFiles($regex, $recursive) as $key => $value) {
            $value->remove();
        }
    }

    /**
     * @param $regex
     * @param bool $recursive
     * @return File[]
     * @throws UtilException
     */
    public function getFiles($regex = null, $recursive = false)
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        $folders = array();
        $iterator = $this->constructIterator($regex, $recursive);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }

            $folders[] = new File($fileInfo->getPathname());
        }
        return $folders;
    }

    /**
     * @param null $regex
     * @param bool|false $recursive
     * @return \DirectoryIterator|\RecursiveDirectoryIterator|\RecursiveIteratorIterator|\RegexIterator
     */
    private function constructIterator($regex = null, $recursive = false)
    {
        if ($recursive) {
            $iterator = new \RecursiveDirectoryIterator($this->folder, \RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new \RecursiveIteratorIterator(
                $iterator,
                \RecursiveIteratorIterator::SELF_FIRST,
                \RecursiveIteratorIterator::CATCH_GET_CHILD);
        } else {
            $iterator = new \DirectoryIterator($this->folder);
        }

        if (!empty($regex)) {
            $iterator = new \RegexIterator($iterator, $regex, \RegexIterator::MATCH);
        }

        return $iterator;
    }

    /**
     * @param string $regex
     * @param bool $recursive
     * @throws UtilException
     */
    public function removeFolders($regex = null, $recursive = false)
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        foreach ($this->getFolders($regex, $recursive) as $key => $value) {
            $value->remove();
        }
    }

    /**
     * @param null $regex
     * @param bool $recursive
     * @return Folder[]
     * @throws UtilException
     */
    public function getFolders($regex = null, $recursive = false)
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        $folders = array();
        $iterator = $this->constructIterator($regex, $recursive);
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isDir()
                || $fileInfo->getFilename() == '.'
                || $fileInfo->getFilename() == '..'
            ) {
                continue;
            }

            $folders[] = new Folder($fileInfo->getPathname());
        }
        return $folders;
    }

    /**
     * @throws UtilException
     */
    public function remove()
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        $files = $this->getFiles(null, true);
        foreach ($files as $file) {
            $file->remove();
        }
        $folders = $this->getFolders(null, true);
        foreach ($folders as $folder) {
            $folder->remove();
        }

        rmdir($this->absolute());
    }

    /**
     * @param $regex
     * @return bool
     * @throws UtilException
     */
    public function match($regex)
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        return preg_match($regex, $this->getName()) === 1;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        $array = $this->getArray();
        return $array[count($array) - 1];
    }

}
