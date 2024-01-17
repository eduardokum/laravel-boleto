<?php

namespace Eduardokum\LaravelBoleto;

use Illuminate\View\Factory;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\View\FileViewFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Compilers\BladeCompiler;

class Blade
{
    /**
     * Array containing paths where to look for blade files
     * @var array
     */
    public $viewPaths;

    /**
     * Location where to store cached views
     * @var string
     */
    public $cachePath = null;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Factory
     */
    protected $instance;

    /**
     * Initialize class
     * @param array $viewPaths
     * @param string $cachePath
     */
    public function __construct($viewPaths = [], $cachePath = null)
    {
        $this->container = new Container;
        $this->viewPaths = (array) $viewPaths;
        $this->cachePath = $cachePath;
        $this->registerFilesystem();
        $this->registerEngineResolver();

        $this->registerViewFinder();

        $this->instance = $this->registerFactory();
    }

    public function view()
    {
        return $this->instance;
    }

    public function registerFilesystem()
    {
        $this->container->singleton('files', function () {
            return new Filesystem;
        });
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    public function registerEngineResolver()
    {
        $self = $this;

        $this->container->singleton('view.engine.resolver', function ($app) use ($self) {
            $resolver = new EngineResolver;
            $self->registerPhpEngine($resolver);
            $self->registerBladeEngine($resolver);

            return $resolver;
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param EngineResolver $resolver
     * @return void
     */
    public function registerPhpEngine($resolver)
    {
        $resolver->register('php', function () {
            return new PhpEngine;
        });
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param EngineResolver $resolver
     * @return void
     */
    public function registerBladeEngine($resolver)
    {
        $self = $this;
        $app = $this->container;

        $this->container->singleton('blade.compiler', function ($app) use ($self) {
            $cache = $self->cachePath;

            return new BladeCompiler($app['files'], $cache);
        });

        $resolver->register('blade', function () use ($app) {
            return new CompilerEngine($app['blade.compiler']);
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $self = $this;
        $this->container->singleton('view.finder', function ($app) use ($self) {
            $paths = $self->viewPaths;

            return new FileViewFinder($app['files'], $paths);
        });
    }

    /**
     * Register the view environment.
     */
    public function registerFactory()
    {
        $resolver = $this->container['view.engine.resolver'];
        $finder = $this->container['view.finder'];
        $env = new Factory($resolver, $finder, new Dispatcher);
        $env->setContainer($this->container);

        return $env;
    }

    public function getCompiler()
    {
        return $this->container['blade.compiler'];
    }
}
