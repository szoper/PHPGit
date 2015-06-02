<?php

namespace PHPGit\Command;

use PHPGit\Command;
use PHPGit\Exception\GitException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Show commit logs - `git diff`.
 *
 * @author Kazuyuki Hayashi <hayashi@valnur.net>
 */
class DiffCommand extends Command
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
        $builder = $this->git->getProcessBuilder()
            ->add('diff');

        if ($revRange) {
            $builder->add($revRange);
        }

        if ($path) {
            $builder->add('--')->add($path);
        }

        $output = $this->git->run($builder->getProcess());

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
    }
}
