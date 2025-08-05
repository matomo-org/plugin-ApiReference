<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs\Commands;

use OpenApi\Annotations\ExternalDocumentation;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use OpenApi\Generator;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Plugin\ConsoleCommand;
use Piwik\SettingsPiwik;
use Piwik\Url;

/**
 * This class lets you define a new command. To read more about commands have a look at our Matomo Console guide on
 * https://developer.matomo.org/guides/piwik-on-the-command-line
 *
 * As Matomo Console is based on the Symfony Console you might also want to have a look at
 * https://symfony.com/doc/current/components/console/index.html
 */
class GenerateDocFile extends ConsoleCommand
{
    /**
     * This method allows you to configure your command. Here you can define the name and description of your command
     * as well as all options and arguments you expect when executing it.
     */
    protected function configure()
    {
        $this->setName('openapidocs:generate-doc-file');
        $this->setDescription('Generate the OpenAPI documentation file for the Matomo APIs.');
        $this->addRequiredValueOption('plugin', null, 'Name of the plugin to document');
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
        // Set the constant for the current instance's URL
        if(!defined('LOCAL_MATOMO_SERVER_URL')) {
            define('LOCAL_MATOMO_SERVER_URL', SettingsPiwik::getPiwikUrl());
        }
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
     * Execute the command like: ./console openapidocs:generate-doc-file --plugin=TagManager
     */
    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        $plugin = $input->getOption('plugin') ?: 'Matomo';

        $message = sprintf('<info>Generating documentation for: %s</info>', $plugin);

        $output->writeln($message);

        $generator = new Generator(StaticContainer::get(LoggerInterface::class));
        $generator->setVersion(OpenApi::DEFAULT_VERSION);
        $openapi = $generator->generate([
            __DIR__ . '/../OpenApiDocs.php',
            __DIR__ . '/../../' . $plugin . '/API.php',
        ]);

        $generatedContent = $openapi->toJson();
        $output->writeln($generatedContent);

        return self::SUCCESS;
    }
}
