<?php

namespace App\Commands;

use App\Services\GithubActionConfig;
use App\Services\GithubApiCommands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BranchName
 *
 * @package App\Services
 */
class BranchNameConvention extends Command
{
    /**
     * @var GithubActionConfig
     */
    protected GithubActionConfig $config;

    /**
     * @var GithubApiCommands
     */
    protected GithubApiCommands $apiCommands;

    /**
     * BranchName constructor.
     *
     * @param GithubActionConfig $config
     * @param GithubApiCommands $apiCommands
     */
    public function __construct(GithubActionConfig $config, GithubApiCommands $apiCommands)
    {
        parent::__construct();

        $this->config = $config;
        $this->apiCommands = $apiCommands;
    }

    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this
            ->setName('branch-naming')
            ->setDescription('Branch naming convention.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (1 === preg_match('/^bugfix\/[0-9]{6}-[a-z-]/', $this->config->headRef()) &&
            $this->config->actor() == 'JurjenFolkertsma'
        ) {
            $message = <<<TXT
Invalid branch name, actor is not allowed to push bugfix branches.

Format should be: type/(six-digit-ticket-number)-short-description
Type is one of the following: feature/styling.
Short description has be lowercase a-z, 0-9 and - only.
TXT;
            return $this->fail($output, $message);
        }
        if (1 !== preg_match('/^(feature|bugfix|styling)\/[0-9]{6}-[a-z-]/', $this->config->headRef())) {
            $message = <<<TXT
Invalid branch name.

Format should be: type/(six-digit-ticket-number)-short-description
Type is one of the following: feature/bugfix/styling.
Short description has be lowercase a-z, 0-9 and - only.
TXT;
            return $this->fail($output, $message);
        }

        return 0;
    }

    /**
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function fail(OutputInterface $output, string $message): int
    {
        $output->writeln($message);

        $this->apiCommands->placeIssueComment($message);
        $this->apiCommands->closePullRequest();

        return 1;
    }
}
