<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\ApiReference\Annotations\ApiMethodInfoExtractor;
use Piwik\Plugins\ApiReference\Specs\PathResolver;

/**
 * This class lets you define a new command. To read more about commands have a look at our Matomo Console guide on
 * https://developer.matomo.org/guides/piwik-on-the-command-line
 *
 * As Matomo Console is based on the Symfony Console you might also want to have a look at
 * https://symfony.com/doc/current/components/console/index.html
 */
class ExtractReportingApiMethodInfo extends ConsoleCommand
{
    /**
     * This method allows you to configure your command. Here you can define the name and description of your command
     * as well as all options and arguments you expect when executing it.
     */
    protected function configure()
    {
        $this->setName('apireference:extract-api-method-info');
        $this->setDescription('Extract the comment block and basic information about methods for the Matomo Reporting API.');
        $this->addRequiredValueOption('plugin', 'p', 'Name of the plugin to inspect');
        $this->addNoValueOption('not-dry-run', null, 'Flag to allow writing to file instead of outputting a dry run.');
    }

    /**
     * Interact with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     */
    protected function doInteract(): void
    {
    }

    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     */
    protected function doInitialize(): void
    {
    }

    /**
     * The actual task is defined in this method. Here you can access any option or argument that was defined on the
     * command line via $this->getInput() and write anything to the console via $this->getOutput().
     * In case anything went wrong during the execution you should throw an exception to make sure the user will get a
     * useful error message and to make sure the command does not exit with the status code 0.
     *
     * Ideally, the actual command is quite short as it acts like a controller. It should only receive the input values,
     * execute the task by calling a method of another class and output any useful information.
     *
     * Execute the command like: ./console apireference:extract-api-method-info --plugin=TagManager --not-dry-run
     */
    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        $plugin = $input->getOption('plugin');
        if (empty($plugin)) {
            throw new \RuntimeException('Please specify a plugin name.');
        }
        $notDryRun = $input->getOption('not-dry-run') ?: false;

        $message = sprintf('<info>Extracting API method info for: %s</info>', $plugin);

        $output->writeln($message);

        $result = (new ApiMethodInfoExtractor())->extractMethodInfo($plugin, $notDryRun);

        if ($notDryRun) {
            $output->writeln('<info>Results written to ' . StaticContainer::get(PathResolver::class)->getAnnotationsDirectory() . '</info>');

            return $result ? self::SUCCESS : self::FAILURE;
        }

        $output->writeln($result);

        return $result ? self::SUCCESS : self::FAILURE;
    }
}
