<?php
/**
 * Copyright (C) 2015 Arthur Halet
 *
 * This software is distributed under the terms and conditions of the 'MIT'
 * license which can be found in the file 'LICENSE' in this package distribution
 * or at 'http://opensource.org/licenses/MIT'.
 *
 * Author: Arthur Halet
 * Date: 17/09/15
 */


namespace Arhframe\Util;


class FileTest extends \PHPUnit_Framework_TestCase
{
    public static $FILENAME = "/ahutil/myfile.txt";
    public static $CONTENT = "mycontent";
    public static $SHA1 = "d3142687e449788fd5f97a6b469c284f65bf8243";
    public static $MD5 = "c8afdb36c52cf4727836669019e69222";
    public static $URL = "http://motherfuckingwebsite.com/index.html";

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        FileTest::$FILENAME = sys_get_temp_dir() . FileTest::$FILENAME;
    }

    public function testFileManipulation()
    {
        $filename = FileTest::$FILENAME;
        $pathinfoFilename = pathinfo($filename);
        $content = FileTest::$CONTENT;

        $file = new File($filename);

        $this->assertEquals($pathinfoFilename['basename'], $file->getName());
        $this->assertEquals($pathinfoFilename['extension'], $file->getExtension());
        $this->assertEquals($pathinfoFilename['filename'], $file->getBase());
        $this->assertEquals($pathinfoFilename['basename'], $file->getName());
        $this->assertEquals($pathinfoFilename['dirname'], $file->getFolder());
        $this->assertInstanceOf('Arhframe\\Util\\Folder', $file->getFolderObject());
        $this->assertEquals($filename, $file->absolute());
        $this->assertEquals(explode('/', $filename), $file->getArray());
        $this->assertFalse($file->isFile());
        $this->assertFalse($file->isUrl());

        try {
            $file->touch();
        } catch (UtilException $e) {
            $this->assertTrue(true);
        }

        $file->createFolder();
        $time = time();
        try {
            $file->touch();
        } catch (UtilException $e) {
            $this->assertTrue(false, $e->getMessage());
        }
        $this->assertTrue($file->isFile());
        $this->assertEquals($time, $file->getTime());
        $this->assertEquals(0, $file->getSize());

        $file->setContent($content);
        $this->assertEquals($content, $file->getContent());
        $this->assertGreaterThan(0, $file->getSize());
        $this->assertEquals($content, $file->getContent(true));

        $this->assertTrue($file->checksumSha1(FileTest::$SHA1));
        $this->assertTrue($file->checksumMd5(FileTest::$MD5));
        $this->assertFalse($file->checksumMd5('pout'));

        $this->assertTrue($file->match("#^.*\.txt$#"));
        $this->assertFalse($file->match('#^k$#'));

        $file->remove();
        $this->assertFalse($file->isFile());

    }

    public function testManipulateHttpFile()
    {
        $file = new File(FileTest::$URL);
        $this->assertTrue($file->isUrl());
        $haveContent = empty($file->getContent());
        $this->assertFalse($haveContent);
    }

    /**
     * @after
     */
    public function after()
    {
        @unlink(FileTest::$FILENAME);
    }
}
