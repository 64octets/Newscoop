<?php
/**
 * @package Newscoop
 * @copyright 2012 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\Tools\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console;

require_once WWW_DIR . '/include/campsite_init.php';

/**
 * Update Image Storage Command
 */
class AutopublishCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
            ->setName('newscoop:autopublish')
            ->setDescription('Autopublish pending issues and articles')
            ->setHelp('Modifies the status of issues and articles scheduled for certain actions.');
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        // fill in HTTP_HOST to avoid notices in campsite_constants.php
        $_SERVER['HTTP_HOST'] = '';
        $issueActions = \IssuePublish::DoPendingActions();
        $articleActions = \ArticlePublish::DoPendingActions();

        if ($issueActions > 0 || $articleActions > 0) {
            fopen(WWW_DIR.'/reset_cache', 'w');
        }

        $output->writeln('Published issues: <info>'.count($issueActions).'</info>.');
        $output->writeln('Published articles: <info>'.count($articleActions).'</info>.');
    }
}
