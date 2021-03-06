<?php
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2015 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\DomainModel;

use Mockery as m;
use phpDocumentor\DomainModel\Parser\Documentation;
use phpDocumentor\DomainModel\Render;
use phpDocumentor\DomainModel\Parser\Version\Number;

/**
 * @coversDefaultClass phpDocumentor\DomainModel\Render
 * @covers ::__construct
 */
final class RenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getDocumentation
     * @covers ::getTarget
     * @covers ::getTemplates
     */
    public function testIfCommandIsProperlyCreatedAndReturnsParameters()
    {
        $documentation = new Documentation(new Number('1.0'));
        $target = '.';
        $templates = ['template'];

        $fixture = new Render($documentation, $target, $templates);

        $this->assertSame($documentation, $fixture->getDocumentation());
        $this->assertSame($target, $fixture->getTarget());
        $this->assertSame($templates, $fixture->getTemplates());
    }
}
