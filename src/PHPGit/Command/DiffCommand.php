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
     * Returns diffs.
     *
     * ``` php
     * $git = new PHPGit\Git();
     * $git->setRepository('/path/to/repo');
     * $diff = $git->diff();
     * ```
     *
     *
     * ##### Options
     *
     * @param string $revRange [optional] Show only commits in the specified revision range
     * @param string $path     [optional] Show only commits that are enough to explain how the files that match the specified paths came to be
     * @param array  $options  [optional] An array of options {@see LogCommand::setDefaultOptions}
     *
     * @throws GitException
     *
     * @return mixed
     */
    public function __invoke($revRange = '', $path = null, array $options = array())
    {
        $builder = $this->git->getProcessBuilder()
            ->add('diff')
            ->add('--color')
        ;

        if ($revRange) {
            $builder->add($revRange);
        }

        if ($path) {
            $builder->add('--')->add($path);
        }

        echo $builder->getProcess()->getCommandLine();

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
