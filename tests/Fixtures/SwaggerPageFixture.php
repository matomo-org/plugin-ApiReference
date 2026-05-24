<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\tests\Fixtures;

use Piwik\Plugins\ApiReference\ApiReference;
use Piwik\Plugins\ApiReference\Specs\PathResolver;
use Piwik\Tests\Framework\Fixture;
use Piwik\Updater;

class SwaggerPageFixture extends Fixture
{
    public $dateTime = '2010-01-03 00:00:00';
    public $idSite = 1;

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->markApiReferenceAsInstalled();
        $this->writeOpenApiSpecFixtures();
    }

    public function tearDown(): void
    {
        $this->removeOpenApiSpecFixtures();
    }

    private function setUpWebsite(): void
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    private function writeOpenApiSpecFixtures(): void
    {
        $resolver = new PathResolver();
        $specDirectory = $resolver->getSpecDirectory();

        if (!is_dir($specDirectory)) {
            mkdir($specDirectory, 0777, true);
        }

        file_put_contents(
            $this->getReferrersSpecPath(),
            json_encode($this->getReferrersSpec(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function markApiReferenceAsInstalled(): void
    {
        (new Updater())->markComponentSuccessfullyUpdated('ApiReference', '5.0.0');
    }

    private function removeOpenApiSpecFixtures(): void
    {
        $specPath = $this->getReferrersSpecPath();

        if (is_file($specPath)) {
            unlink($specPath);
        }
    }

    private function getReferrersSpecPath(): string
    {
        return (new PathResolver())->getSpecFilePath('Referrers', ApiReference::DEFAULT_SPEC_VERSION);
    }

    /**
     * @return array<string, mixed>
     */
    private function getReferrersSpec(): array
    {
        $description = 'Returns example reporting data for referrer types.';

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Reporting API for Referrers plugin',
                'version' => ApiReference::DEFAULT_SPEC_VERSION,
                'description' => 'Fixture-backed OpenAPI data for ApiReference UI screenshot tests.',
            ],
            'tags' => [
                [
                    'name' => 'Referrers',
                    'description' => 'Referrer reporting endpoints.',
                ],
            ],
            'paths' => [
                '/index.php?module=API&method=Referrers.getReferrerType' => [
                    'get' => [
                        'tags' => ['Referrers'],
                        'summary' => 'Get referrer types',
                        'description' => $description,
                        'parameters' => [
                            [
                                'name' => 'idSite',
                                'in' => 'query',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                            ],
                            [
                                'name' => 'period',
                                'in' => 'query',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                            [
                                'name' => 'date',
                                'in' => 'query',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Successful response',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'label' => ['type' => 'string'],
                                                    'nb_visits' => ['type' => 'integer'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/index.php?module=API&method=Referrers.getCampaigns' => [
                    'post' => [
                        'tags' => ['Referrers'],
                        'summary' => 'Get campaigns',
                        'description' => 'Returns example campaign reporting data.',
                        'requestBody' => [
                            'required' => false,
                            'content' => [
                                'application/x-www-form-urlencoded' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'segment' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Successful response',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'label' => ['type' => 'string'],
                                                    'revenue' => ['type' => 'number'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
