<?php

namespace PHPGit\Command;

use PHPGit\Command;
use PHPGit\Exception\GitException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Show commit logs - `git log`.
 *
 * @author Kazuyuki Hayashi <hayashi@valnur.net>
 */
class LogCommand extends Command
{
    /**
     * Returns the commit logs.
     *
     * ``` php
     * $git = new PHPGit\Git();
     * $git->setRepository('/path/to/repo');
     * $logs = $git->log(array('limit' => 10));
     * ```
     *
     * ##### Output Example
     *
     * ``` php
     * [
     *     0 => [
     *         'hash'  => '1a821f3f8483747fd045eb1f5a31c3cc3063b02b',
     *         'name'  => 'John Doe',
     *         'email' => 'john@example.com',
     *         'date'  => 'Fri Jan 17 16:32:49 2014 +0900',
     *         'title' => 'Initial Commit'
     *     ],
     *     1 => [
     *         //...
     *     ]
     * ]
     * ```
     *
     * ##### Options
     *
     * - **limit** (_integer_) Limits the number of commits to show
     * - **skip**  (_integer_) Skip number commits before starting to show the commit output
     *
     * @param string $revRange [optional] Show only commits in the specified revision range
     * @param string $path     [optional] Show only commits that are enough to explain how the files that match the specified paths came to be
     * @param array  $options  [optional] An array of options {@see LogCommand::setDefaultOptions}
     *
     * @throws GitException
     *
     * @return array
     */
    public function __invoke($revRange = '', $path = null, array $options = array())
    {
        if (1 === func_num_args()) {
            $options  = func_get_arg(0);
            $revRange = null;
            $path     = null;
        }

        $commits = array();
        $options = $this->resolve($options);

        $builder = $this->git->getProcessBuilder()
            ->add('log');

        if (!is_null($options['since'])) {
            $builder->add('--since='.$options['since']);
        }

        if (!is_null($options['search'])) {
            $builder
                ->add('--grep='.$options['search'])
                ->add('-i');
        }

        $builder
            ->add('-n')->add($options['limit'])
            ->add('--skip='.$options['skip'])
            ->add('--format=%h||%aN||%aE||%aD||%s');

        if ($revRange) {
            $builder->add($revRange);
        }

        if ($path) {
            $builder->add('--')->add($path);
        }

        $output = $this->git->run($builder->getProcess());
        $lines = $this->split($output);

        foreach ($lines as $line) {
            list($hash, $name, $email, $date, $title) = preg_split('/\|\|/', $line, -1, PREG_SPLIT_NO_EMPTY);
            $commits[] = array(
                'hash' => $hash,
                'name' => $name,
                'email' => $email,
                'date' => $date,
                'title' => $title,
            );
        }

        return $commits;
    }

    /**
     * @param string $revRange
     * @param null $path
     * @param bool $status
     *
     * @return array
     *
     * @throws GitException
     */
    public function changed($revRange = '', $path = null, $status = false)
    {
        $builder = $this->git->getProcessBuilder()
            ->add('log');

        if ($status) {
            $builder->add('--name-status');
        } else {
            $builder->add('--name-only');
        }

        $builder
            ->add('--format=');

        if ($revRange) {
            $builder->add($revRange);
        }

        if ($path) {
            $builder->add('--')->add($path);
        }

        $output = $this->git->run($builder->getProcess());
        $lines = $this->split($output);

        if ($status) {
            $commits = array();

            foreach ($lines as $line) {
                list($status, $filename) = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
                $commits[] = array(
                    'status' => $status,
                    'filename' => $filename,
                );
            }

            return $commits;
        } else {
            return $lines;
        }
    }

    /**
     * {@inheritdoc}
     *
     * - **limit** (_integer_) Limits the number of commits to show
     * - **skip**  (_integer_) Skip number commits before starting to show the commit output
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('limit', 10)
            ->setDefault('since', null)
            ->setDefault('search', null)
            ->setDefault('skip', 0);
    }
}
