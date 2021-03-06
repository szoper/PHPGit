<?php

namespace PHPGit\Command;

use PHPGit\Command;

/**
 * List the contents of a tree object - `git ls-tree`.
 *
 * @author Kazuyuki Hayashi <hayashi@valnur.net>
 */
class TreeCommand extends Command
{
    /**
     * Returns the contents of a tree object.
     *
     * ``` php
     * $git = new PHPGit\Git();
     * $git->clone('https://github.com/kzykhys/PHPGit.git', '/path/to/repo');
     * $git->setRepository('/path/to/repo');
     * $tree = $git->tree('master');
     * ```
     *
     * ##### Output Example
     *
     * ``` php
     * [
     *     ['mode' => '100644', 'type' => 'blob', 'hash' => '1f100ce9855b66111d34b9807e47a73a9e7359f3', 'file' => '.gitignore', 'sort' => '2:.gitignore'],
     *     ['mode' => '100644', 'type' => 'blob', 'hash' => 'e0bfe494537037451b09c32636c8c2c9795c05c0', 'file' => '.travis.yml', 'sort' => '2:.travis.yml'],
     *     ['mode' => '040000', 'type' => 'tree', 'hash' => '8d5438e79f77cd72de80c49a413f4edde1f3e291', 'file' => 'bin', 'sort' => '1:.bin'],
     * ]
     * ```
     *
     * @param string $branch The commit
     * @param string $path   The path
     *
     * @return array
     */
    public function __invoke($branch = 'master', $path = '')
    {
        $objects = array();
        $builder = $this->git->getProcessBuilder();
        $process = $builder->add('ls-tree')->add($branch.':'.$path)->getProcess();
        $output = $this->git->run($process);
        $lines = $this->split($output);

        $types = array(
            'submodule' => 0,
            'tree' => 1,
            'blob' => 2,
        );

        foreach ($lines as $line) {
            list($meta, $file) = explode("\t", $line);
            list($mode, $type, $hash) = explode(' ', $meta);

            $objects[] = array(
                'sort' => sprintf('%d:%s', $types[$type], $file),
                'mode' => $mode,
                'type' => $type,
                'hash' => $hash,
                'file' => $file,
            );
        }

        return $objects;
    }

    /**
     * @param string $from
     * @param null $to
     * @param bool $status
     * @param string $filter
     *
     * @return array
     *
     * @throws \PHPGit\Exception\GitException
     */
    public function diff($from = 'HEAD', $to = null, $status = false, $filter = 'ACRMT')
    {
        $diff = array();

        $builder = $this->git->getProcessBuilder();
        $builder
            ->add('diff-tree')
            ->add('-r');

        if ($status) {
            $builder->add('--name-status');
        } else {
            $builder->add('--name-only');
        }

        $builder->add($from);

        if (!is_null($to)) {
            $builder->add($to);
        }

        if (!empty($filter)) {
            $builder->add('--diff-filter='.$filter);
        }

        $output = $this->git->run($builder->getProcess());
        $lines = $this->split($output);

        if ($status) {
            foreach ($lines as $line) {
                list($status, $filename) = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
                $diff[] = array(
                    'status' => $status,
                    'filename' => $filename,
                );
            }

            return $diff;
        }

        return $lines;
    }
}
