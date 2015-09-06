<?php

use PHPGit\Git;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Tomek Marcinkowski
 * @since 06.09.2015
 */
class LogCommandTest extends BaseTestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var Git
     */
    private $git;

    public function testLogLimit()
    {
        $this->addAndCommitFiles(2);

        $log = $this->git->log(null, null, array('limit' => 1));
        $this->assertEquals(1, count($log));
        $this->assertEquals('test2', $log[0]['title']);
    }

    /**
     * @param $filesCount
     */
    private function addAndCommitFiles($filesCount)
    {
        for ($i = 1; $i <= $filesCount; ++$i) {
            $filePath = $this->directory . '/test' . $i;

            $this->filesystem->dumpFile($filePath, 'foo');
            $this->git->add($filePath);
            $this->git->commit('test' . $i);
        }
    }

    public function testLogWithOptionsOnlyTheOldWay()
    {
        $this->addAndCommitFiles(2);

        $log = $this->git->log(null, null, array('limit' => 1));
        $this->assertNotEmpty($log);
    }

    public function testLogWithOptionsOnlyTheNewWay()
    {
        $this->addAndCommitFiles(2);

        $log = $this->git->log(array('limit' => 1));
        $this->assertNotEmpty($log);
    }

    public function setUp()
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->directory);

        $this->git = new Git();
        $this->git->init($this->directory);
        $this->git->setRepository($this->directory);
    }
}
