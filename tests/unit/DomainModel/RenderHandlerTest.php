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

use League\Event\Emitter;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use League\Tactician\CommandBus;
use Mockery as m;
use phpDocumentor\DomainModel\Parser\Documentation;
use phpDocumentor\Infrastructure\FileSystemFactory;
use phpDocumentor\DomainModel\Parser\Version\Number;
use phpDocumentor\DomainModel\Renderer\Template\Action;
use phpDocumentor\DomainModel\Renderer\RenderActionCompleted;
use phpDocumentor\DomainModel\Renderer\RenderingFinished;
use phpDocumentor\DomainModel\Renderer\RenderingStarted;
use phpDocumentor\DomainModel\Renderer\Template;
use phpDocumentor\DomainModel\Renderer\TemplateFactory;

/**
 * @coversDefaultClass phpDocumentor\DomainModel\RenderHandler
 * @covers ::__construct
 * @covers ::<private>
 */
final class RenderHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $templateFactory;

    private $commandBus;

    private $filesystemFactory;

    private $emitter;

    private $handler;

    public function setUp()
    {
        $this->templateFactory = m::mock(TemplateFactory::class);
        $this->commandBus = m::mock(CommandBus::class);
        $this->filesystemFactory = m::mock(FileSystemFactory::class);
        $this->emitter = m::mock(Emitter::class);

        $this->handler = new RenderHandler(
            $this->templateFactory,
            $this->commandBus,
            $this->filesystemFactory,
            $this->emitter
        );
    }

    /**
     * @covers ::__invoke
     */
    public function testIfTemplateActionsAreExecutedWhenRenderingATemplate()
    {
        $documentation = new Documentation(new Number('1.0'));
        $target = '.';
        $templates = ['template'];
        $renderActionCommand = m::mock(Action::class);

        $command = new Render($documentation, $target, $templates);

        $this->emitter->shouldReceive('emit')->once()->with(m::type(RenderActionCompleted::class));
        $this->emitter->shouldReceive('emit')->once()->with(m::type(RenderingStarted::class));
        $this->emitter->shouldReceive('emit')->once()->with(m::type(RenderingFinished::class));

        $this->filesystemFactory->shouldReceive('create')->andReturn(new Filesystem(new MemoryAdapter()));
        $this->templateFactory
            ->shouldReceive('createFromName')
            ->andReturn(new Template('name', [], [$renderActionCommand]));

        $this->commandBus->shouldReceive('handle')->once()->with($renderActionCommand);

        $this->handler->__invoke($command);
    }

    /**
     * @covers ::__invoke
     */
    public function testIfRenderingFailsIfTemplateWasNotFound()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'The template "template" could not be found');

        $documentation = new Documentation(new Number('1.0'));
        $target = '.';
        $templates = ['template'];

        $command = new Render($documentation, $target, $templates);

        $this->emitter->shouldReceive('emit')->never()->with(m::type(RenderActionCompleted::class));
        $this->emitter->shouldReceive('emit')->once()->with(m::type(RenderingStarted::class));
        $this->emitter->shouldReceive('emit')->never()->with(m::type(RenderingFinished::class));

        $this->filesystemFactory->shouldReceive('create')->andReturn(new Filesystem(new MemoryAdapter()));
        $this->templateFactory
            ->shouldReceive('createFromName')
            ->andReturn(null);

        $this->commandBus->shouldReceive('handle')->never();

        $this->handler->__invoke($command);
    }
}
