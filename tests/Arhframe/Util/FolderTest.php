<?php
/**
 * Copyright (C) 2015 Arthur Halet
 *
 * This software is distributed under the terms and conditions of the 'MIT'
 * license which can be found in the file 'LICENSE' in this package distribution
 * or at 'http://opensource.org/licenses/MIT'.
 *
 * Author: Arthur Halet
 * Date: 19/09/15
 */


namespace Arhframe\Util;


class FolderTest extends \PHPUnit_Framework_TestCase
{
    private static $TREE = array(
        '/test1/test1.1/t1.php',
        '/test1/test1.1/t2.php',
        '/test1/test1.1/t3.txt',
        '/test1/t4.txt',
        '/test1/t5.php',
        '/t6.php',
        '/test2/test2.1/t7.php',
        '/test2/test2.1/t8.php',
        '/test2/test2.1/t9.txt',
        '/test2/t10.txt',
        '/test2/t11.php',
        '/t12.txt',
    );
    private static $FOLDER_PATH = '/ahutilst';

    /**
     * @before
     */
    public function before()
    {
        FolderTest::$FOLDER_PATH = sys_get_temp_dir() . FolderTest::$FOLDER_PATH;
        $this->createTree();
    }

    private function createTree()
    {
        foreach (FolderTest::$TREE as $filePath) {
            $file = new File(FolderTest::$FOLDER_PATH . $filePath);
            $file->createFolder();
            $file->touch();

        }
    }

    public function testFolderManipulation()
    {
        $folder = new Folder(FolderTest::$FOLDER_PATH);

        $this->assertEquals(FolderTest::$FOLDER_PATH, $folder->absolute());
        $this->assertEquals('ahutilst', $folder->getName());

        $folder->append('toto');
        $this->assertEquals(FolderTest::$FOLDER_PATH . '/toto', $folder->absolute());
        $folder->pop();
        $this->assertEquals(FolderTest::$FOLDER_PATH, $folder->absolute());

        $folder->prepend('titi');
        $this->assertEquals('titi/' . FolderTest::$FOLDER_PATH, $folder->absolute());
        $folder->popReverse();
        $this->assertEquals(FolderTest::$FOLDER_PATH, $folder->absolute());


        $folderCommonTempName = sys_get_temp_dir() . '/ahutilst';
        $folderTemp = new Folder($folderCommonTempName . '/thisismyfolder');

        $folderTemp->create();
        $this->assertTrue($folderTemp->isFolder());
        $folderTemp->truncate();
        $this->assertTrue($folderTemp->isFolder());
        $folderTemp->replace('heyhey');
        $this->assertEquals($folderCommonTempName . '/heyhey', $folderTemp->absolute());
        $folderTemp->replace('thisismyfolder');
        $folderTemp->remove();
        $this->assertFalse($folderTemp->isFolder());

        $this->assertTrue($folder->match('#ahutilst#i'));
        $this->assertFalse($folder->match('#;lms;%#i'));

        $folders = $folder->getFolders();
        $this->assertCount(2, $folders);

        $folders = $folder->getFolders('#test1#');
        $this->assertCount(1, $folders);

        $folders = $folder->getFolders(null, true);
        $this->assertCount(4, $folders);

        $folders = $folder->getFolders('#test1#', true);
        $this->assertCount(2, $folders);

        $files = $folder->getFiles();
        $this->assertCount(2, $files);

        $files = $folder->getFiles('#t6#');
        $this->assertCount(1, $files);

        $files = $folder->getFiles(null, true);
        $this->assertCount(12, $files);

        $files = $folder->getFiles('#.*\.txt$#', true);
        $this->assertCount(5, $files);

        $folder->removeFiles('#.*\.txt$#');
        $files = $folder->getFiles();
        $this->assertCount(1, $files);

        $folder->removeFiles();
        $files = $folder->getFiles();
        $this->assertCount(0, $files);

        $folder->removeFiles('#.*\.txt$#', true);
        $files = $folder->getFiles(null, true);
        $this->assertCount(6, $files);

        $folder->removeFolders('#test1#');
        $folders = $folder->getFolders();
        $this->assertCount(1, $folders);

        $folder->removeFolders('#test2\.1#', true);
        $folders = $folder->getFolders(null, true);
        $this->assertCount(1, $folders);

        $folder->removeFolders();
        $folders = $folder->getFolders(null, true);
        $this->assertCount(0, $folders);
    }

}
