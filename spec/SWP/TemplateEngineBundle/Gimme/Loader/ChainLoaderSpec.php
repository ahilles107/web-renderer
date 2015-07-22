<?php

namespace spec\SWP\TemplateEngineBundle\Gimme\Loader;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SWP\TemplateEngineBundle\Gimme\Loader\ArticleLoader;
use SWP\TemplateEngineBundle\Gimme\Meta\Meta;

define('__APP_DIR__', __DIR__ . '/../../../../../app');

class ChainLoaderSpec extends ObjectBehavior
{
    function let(
        ArticleLoader $articleLoader,
        Meta $meta
    ){
        $articleLoader->isSupported(Argument::exact('article'))->willReturn(true);
        $articleLoader->isSupported(Argument::exact('article2'))->willReturn(false);
        $articleLoader->load(Argument::exact('article'), Argument::type('array'))->willReturn($meta);

    }

    function it_is_initializable()
    {
        $this->shouldHaveType('SWP\TemplateEngineBundle\Gimme\Loader\ChainLoader');
    }

    function it_should_add_new_loader($articleLoader)
    {
        $this->addLoader($articleLoader);
    }

    function it_should_load_meta($articleLoader, $meta)
    {
        $this->addLoader($articleLoader);
        $this->load('article', array())->shouldReturn($meta);
    }

    function it_should_check_if_type_is_supported($articleLoader)
    {
        $this->addLoader($articleLoader);
        $this->isSupported('article')->shouldReturn(true);
        $this->isSupported('article2')->shouldReturn(false);
    }
}
