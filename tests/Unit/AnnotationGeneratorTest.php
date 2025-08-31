<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\OpenApiDocs\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\API\DocumentationGenerator;
use Piwik\Plugins\OpenApiDocs\Annotations\AnnotationGenerator;

/**
 * @group OpenApiDocs
 * @group OpenApiDocs_Unit
 * @group OpenApiDocs_AnnotationGeneratorTest
 */
class AnnotationGeneratorTest extends TestCase
{
    /**
     * @var AnnotationGenerator
     */
    private $annotationGenerator;

    public function setUp(): void
    {
        parent::setUp();

        $this->annotationGenerator = new AnnotationGenerator(new DocumentationGenerator());
    }

    /**
     * @dataProvider getTestDataForGetOpenApiTypeFromPhpType
     *
     * @param string $type
     * @param string $expected
     * @return void
     */
    public function testGetOpenApiTypeFromPhpType(string $type, string $expected): void
    {
        $this->assertEquals($expected, $this->annotationGenerator->getOpenApiTypeFromPhpType($type));
    }

    /**
     * @return iterable<string, string}>
     */
    public function getTestDataForGetOpenApiTypeFromPhpType(): iterable
    {
        yield 'should be string for empty' => ['', 'string'];
        yield 'should be string for unknown' => ['unknown', 'string'];
        yield 'should be string for abc123' => ['abc123', 'string'];
        yield 'should be array for array' => ['array', 'array'];
        yield 'should be array for []' => ['[]', 'array'];
        yield 'should be array for int[]' => ['int[]', 'array'];
        yield 'should be array for string[]' => ['string[]', 'array'];
        yield 'should be array for bool[]' => ['bool[]', 'array'];
        yield 'should be array for float[]' => ['float[]', 'array'];
        yield 'should be array for double[]' => ['double[]', 'array'];
        yield 'should be integer for int' => ['int', 'integer'];
        yield 'should be integer for integer' => ['integer', 'integer'];
        yield 'should be boolean for bool' => ['bool', 'boolean'];
        yield 'should be boolean for boolean' => ['boolean', 'boolean'];
        yield 'should be number for float' => ['float', 'number'];
        yield 'should be number for double' => ['double', 'number'];
    }
}
