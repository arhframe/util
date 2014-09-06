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
        return explode('/', $this->folder);
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

    /**
     * @throws UtilException
     */
    public function popReverse()
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        $array = $this->getArray();
        unset($array[1]);
        $this->folder = implode('/', $array);
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
    public function removeFiles($regex = "#.*#", $recursive = false)
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
    public function getFiles($regex, $recursive = false)
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        if (empty($regex) && !$recursive) {
            return $this->getFilesSimple();
        }
        $newArray = $this->getFilesSimple($regex);
        $files = $newArray;
        if ($recursive) {
            foreach ($files as $file) {
                if ($file instanceof Folder) {
                    $newArray = array_merge($newArray, $file->getFiles($regex, $recursive));
                }

            }
        }
        $finalArray = array();
        foreach ($newArray as $file) {
            if ($file instanceof File) {
                $finalArray[] = $file;
            }
        }
        return $finalArray;
    }

    /**
     * @param null $regex
     * @return File[]
     * @throws UtilException
     */
    public function getFilesSimple($regex = null)
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        $array = glob($this->folder . '/*');
        if (empty($array)) {
            return array();
        }
        $newArray = array();
        foreach ($array as $value) {
            if (is_dir($value)) {
                $file = new Folder($value);
            } else {
                $file = new File($value);
            }
            if ((!empty($regex) && $file->match($regex)) || $file instanceof Folder) {
                $newArray[] = $file;
            } else if (empty($regex)) {
                $newArray[] = $file;
            }
        }
        return $newArray;
    }

    /**
     * @param string $regex
     * @param bool $recursive
     * @throws UtilException
     */
    public function removeFolders($regex = "#.*#", $recursive = false)
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
        if (empty($regex) && !$recursive) {
            return $this->getFoldersSimple();
        }
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        $newArray = $this->getFoldersSimple($regex);
        $folders = $newArray;
        if ($recursive) {
            foreach ($folders as $folder) {
                $newArray = array_merge($newArray, $folder->getFolders($regex, $recursive));
            }
        }
        return $newArray;
    }

    /**
     * @param null $regex
     * @return Folder[]
     * @throws UtilException
     */
    public function getFoldersSimple($regex = null)
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        $array = glob($this->folder . '/*', GLOB_ONLYDIR);
        if (empty($array)) {
            return array();
        }
        $newArray = array();
        foreach ($array as $value) {
            $folder = new Folder($value);
            if (!empty($regex) && $folder->match($regex)) {
                $newArray[] = $folder;
            } else if (empty($regex)) {
                $newArray[] = $folder;
            }
        }
        return $newArray;
    }

    /**
     * @param $regex
     * @return int
     * @throws UtilException
     */
    public function match($regex)
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        return preg_match($regex, $this->getName());
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        $array = $this->getArray();
        return $array[count($array) - 1];
    }

    /**
     * @throws UtilException
     */
    public function remove()
    {
        if (!$this->isFolder()) {
            throw new UtilException("Folder '" . $this->absolute() . "' doesn't exist.");
        }
        rmdir($this->absolute());
    }
}
