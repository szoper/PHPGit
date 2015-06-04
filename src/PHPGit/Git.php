<?php

namespace PHPGit;

use PHPGit\Exception\GitException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * PHPGit - A Git wrapper for PHP5.3+
 * ==================================.
 *
 * [![Latest Unstable Version](https://poser.pugx.org/kzykhys/git/v/unstable.png)](https://packagist.org/packages/kzykhys/git)
 * [![Build Status](https://travis-ci.org/kzykhys/PHPGit.png?branch=master)](https://travis-ci.org/kzykhys/PHPGit)
 * [![Coverage Status](https://coveralls.io/repos/kzykhys/PHPGit/badge.png)](https://coveralls.io/r/kzykhys/PHPGit)
 * [![SensioLabsInsight](https://insight.sensiolabs.com/projects/04f10b57-a113-47ad-8dda-9a6dacbb079f/mini.png)](https://insight.sensiolabs.com/projects/04f10b57-a113-47ad-8dda-9a6dacbb079f)
 *
 * Requirements
 * ------------
 *
 * * PHP5.3
 * * Git
 *
 * Installation
 * ------------
 *
 * Update your composer.json and run `composer update`
 *
 * ``` json
 * {
 *     "require": {
 *         "kzykhys/git": "dev-master"
 *     }
 * }
 * ```
 *
 * Basic Usage
 * -----------
 *
 * ``` php
 * <?php
 *
 * require __DIR__ . '/vendor/autoload.php';
 *
 * $git = new PHPGit\Git();
 * $git->clone('https://github.com/kzykhys/PHPGit.git', '/path/to/repo');
 * $git->setRepository('/path/to/repo');
 * $git->remote->add('production', 'git://example.com/your/repo.git');
 * $git->add('README.md');
 * $git->commit('Adds README.md');
 * $git->checkout('release');
 * $git->merge('master');
 * $git->push();
 * $git->push('production', 'release');
 * $git->tag->create('v1.0.1', 'release');
 *
 * foreach ($git->tree('release') as $object) {
 *     if ($object['type'] == 'blob') {
 *         echo $git->show($object['file']);
 *     }
 * }
 * ```
 *
 * @author  Kazuyuki Hayashi <hayashi@valnur.net>
 * @license MIT
 *
 * @method add($file, $options = array())                           Add file contents to the index
 * @method archive($file, $tree = null, $path = null, $options = array()) Create an archive of files from a named tree
 * @method branch($options = array())                               List both remote-tracking branches and local branches
 * @method checkout($branch, $options = array())                    Checkout a branch or paths to the working tree
 * @method clone($repository, $path = null, $options = array())     Clone a repository into a new directory
 * @method commit($message = '', $options = array())                Record changes to the repository
 * @method config($options = array())                               List all variables set in config file
 * @method describe($committish = null, $options = array())         Returns the most recent tag that is reachable from a commit
 * @method diff($revRange = '', $path = null, $options = array())   Returns diffs of specific commits
 * @method fetch($repository, $refspec = null, $options = array())  Fetches named heads or tags from one or more other repositories
 * @method init($path, $options = array())                          Create an empty git repository or reinitialize an existing one
 * @method log($revRange = '', $path = null, $options = array())                    Returns the commit logs
 * @method merge($commit, $message = null, $options = array())      Incorporates changes from the named commits into the current branch
 * @method mv($source, $destination, $options = array())            Move or rename a file, a directory, or a symlink
 * @method pull($repository = null, $refspec = null, $options = array()) Fetch from and merge with another repository or a local branch
 * @method push($repository = null, $refspec = null, $options = array()) Update remote refs along with associated objects
 * @method rebase($upstream = null, $branch = null, $options = array())  Forward-port local commits to the updated upstream head
 * @method remote()                                                 Returns an array of existing remotes
 * @method reset($commit = null, $paths = array())                  Resets the index entries for all <paths> to their state at <commit>
 * @method rm($file, $options = array())                            Remove files from the working tree and from the index
 * @method shortlog($commits = array())                             Summarize 'git log' output
 * @method show($object, $options = array())                        Shows one or more objects (blobs, trees, tags and commits)
 * @method stash()                                                  Save your local modifications to a new stash, and run git reset --hard to revert them
 * @method status($options = array())                               Show the working tree status
 * @method tag()                                                    Returns an array of tags
 * @method tree($branch = 'master', $path = '')                     List the contents of a tree object
 */
class Git
{
    /** @var Command\AddCommand */
    public $add;

    /** @var Command\ArchiveCommand */
    public $archive;

    /** @var Command\BranchCommand */
    public $branch;

    /** @var Command\CatCommand */
    public $cat;

    /** @var Command\CheckoutCommand */
    public $checkout;

    /** @var Command\CloneCommand */
    public $clone;

    /** @var Command\CommitCommand */
    public $commit;

    /** @var Command\ConfigCommand */
    public $config;

    /** @var Command\DescribeCommand */
    public $describe;

    /** @var Command\DiffCommand */
    public $diff;

    /** @var Command\FetchCommand */
    public $fetch;

    /** @var Command\InitCommand */
    public $init;

    /** @var Command\LogCommand */
    public $log;

    /** @var Command\MergeCommand */
    public $merge;

    /** @var Command\MvCommand */
    public $mv;

    /** @var Command\PullCommand */
    public $pull;

    /** @var Command\PushCommand */
    public $push;

    /** @var Command\RebaseCommand */
    public $rebase;

    /** @var Command\RemoteCommand */
    public $remote;

    /** @var Command\ResetCommand */
    public $reset;

    /** @var Command\RmCommand */
    public $rm;

    /** @var Command\ShortlogCommand */
    public $shortlog;

    /** @var Command\ShowCommand */
    public $show;

    /** @var Command\StashCommand */
    public $stash;

    /** @var Command\StatusCommand */
    public $status;

    /** @var Command\TagCommand */
    public $tag;

    /** @var Command\TreeCommand */
    public $tree;

    /** @var string */
    private $bin = 'git';

    /** @var string */
    private $directory = '.';

    /** @var array */
    private $env = array();

    /** @var int */
    private $timeout = 7200;

    /**
     * Initializes sub-commands.
     */
    public function __construct()
    {
        $this->add = new Command\AddCommand($this);
        $this->archive = new Command\ArchiveCommand($this);
        $this->branch = new Command\BranchCommand($this);
        $this->cat = new Command\CatCommand($this);
        $this->checkout = new Command\CheckoutCommand($this);
        $this->clone = new Command\CloneCommand($this);
        $this->commit = new Command\CommitCommand($this);
        $this->config = new Command\ConfigCommand($this);
        $this->describe = new Command\DescribeCommand($this);
        $this->diff = new Command\DiffCommand($this);
        $this->fetch = new Command\FetchCommand($this);
        $this->init = new Command\InitCommand($this);
        $this->log = new Command\LogCommand($this);
        $this->merge = new Command\MergeCommand($this);
        $this->mv = new Command\MvCommand($this);
        $this->pull = new Command\PullCommand($this);
        $this->push = new Command\PushCommand($this);
        $this->rebase = new Command\RebaseCommand($this);
        $this->remote = new Command\RemoteCommand($this);
        $this->reset = new Command\ResetCommand($this);
        $this->rm = new Command\RmCommand($this);
        $this->shortlog = new Command\ShortlogCommand($this);
        $this->show = new Command\ShowCommand($this);
        $this->stash = new Command\StashCommand($this);
        $this->status = new Command\StatusCommand($this);
        $this->tag = new Command\TagCommand($this);
        $this->tree = new Command\TreeCommand($this);
    }

    /**
     * Calls sub-commands.
     *
     * @param string $name      The name of a property
     * @param array  $arguments An array of arguments
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (isset($this->{$name}) && is_callable($this->{$name})) {
            return call_user_func_array($this->{$name}, $arguments);
        }

        throw new \BadMethodCallException(sprintf('Call to undefined method PHPGit\Git::%s()', $name));
    }

    /**
     * Sets the Git binary path.
     *
     * @param string $bin
     *
     * @return Git
     */
    public function setBin($bin)
    {
        $this->bin = $bin;

        return $this;
    }

    /**
     * Sets the Git repository path.
     *
     * @var string
     *
     * @return Git
     */
    public function setRepository($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Returns version number.
     *
     * @return mixed
     */
    public function getVersion()
    {
        $process = $this->getProcessBuilder()
            ->add('--version')
            ->getProcess();

        return $this->run($process);
    }

    /**
     * Returns an instance of ProcessBuilder.
     *
     * @return ProcessBuilder
     */
    public function getProcessBuilder()
    {
        return ProcessBuilder::create()
            ->setTimeout($this->getTimeout())
            ->setPrefix($this->getBin())
            ->setWorkingDirectory($this->directory);
    }

    /**
     * Executes a process.
     *
     * @param Process $process The process to run
     *
     * @throws Exception\GitException
     *
     * @return mixed
     */
    public function run(Process $process)
    {
        $env = array_merge($process->getEnv(), $this->getEnvVars());
        $process->setEnv($env);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new GitException($process->getErrorOutput(), $process->getExitCode(), $process->getCommandLine());
        }

        return $process->getOutput();
    }

    /**
     * Set an alternate private key used to connect to the repository.
     *
     * This method sets the GIT_SSH environment variable to use the wrapper
     * script included with this library. It also sets the custom GIT_SSH_KEY
     * and GIT_SSH_PORT environment variables that are used by the script.
     *
     * @param string      $privateKey
     *                                Path to the private key.
     * @param int         $port
     *                                Port that the SSH server being connected to listens on, defaults to 22.
     * @param string|null $wrapper
     *                                Path the the GIT_SSH wrapper script, defaults to null which uses the
     *                                script included with this library.
     *
     * @return Git
     *
     * @throws GitException
     *                      Thrown when any of the paths cannot be resolved.
     */
    public function setPrivateKey($privateKey, $port = 22, $wrapper = null)
    {
        if (null === $wrapper) {
            if (\Phar::running()) {
                $wrapper = __DIR__.'/../../bin/git-ssh-wrapper.sh';
                $tmp_wrapper = sprintf('%s.sh', tempnam('/tmp', 'wrapper'));
                file_put_contents($tmp_wrapper, file_get_contents($wrapper));
                $wrapper = $tmp_wrapper;
                chmod($wrapper, 0777);
            } else {
                $wrapper = __DIR__.'/../../bin/git-ssh-wrapper.sh';
            }
        }
        if (!$wrapperPath = realpath($wrapper)) {
            throw new GitException('Path to GIT_SSH wrapper script could not be resolved: '.$wrapper);
        }
        if (!$privateKeyPath = realpath($privateKey)) {
            throw new GitException('Path private key could not be resolved: '.$privateKey);
        }

        return $this
            ->setEnvVar('GIT_SSH', $wrapperPath)
            ->setEnvVar('GIT_SSH_KEY', $privateKeyPath)
            ->setEnvVar('GIT_SSH_PORT', (int) $port);
    }

    /**
     * Sets an environment variable that is defined only in the scope of the Git
     * command.
     *
     * @param string $var
     *                      The name of the environment variable, e.g. "HOME", "GIT_SSH".
     * @param string $value
     *
     * @return Git
     */
    public function setEnvVar($var, $value)
    {
        $this->env[$var] = $value;

        return $this;
    }

    /**
     * Unsets an environment variable that is defined only in the scope of the
     * Git command.
     *
     * @param string $var
     *                    The name of the environment variable, e.g. "HOME", "GIT_SSH".
     *
     * @return Git
     */
    public function unsetEnvVar($var)
    {
        unset($this->env[$var]);

        return $this;
    }

    /**
     * Returns an environment variable that is defined only in the scope of the
     * Git command.
     *
     * @param string $var
     *                        The name of the environment variable, e.g. "HOME", "GIT_SSH".
     * @param mixed  $default
     *                        The value returned if the environment variable is not set, defaults to
     *                        null.
     *
     * @return mixed
     */
    public function getEnvVar($var, $default = null)
    {
        return isset($this->env[$var]) ? $this->env[$var] : $default;
    }

    /**
     * Returns the associative array of environment variables that are defined
     * only in the scope of the Git command.
     *
     * @return array
     */
    public function getEnvVars()
    {
        return $this->env;
    }

    /**
     * @param int $timeout
     *
     * @return Git
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return string
     */
    public function getBin()
    {
        return $this->bin;
    }

    public function __destruct()
    {
        if ($wrapper = $this->getEnvVar('GIT_SSH', false)) {
            if (file_exists($wrapper)) {
                unlink($wrapper);
            }
        }
    }
}
