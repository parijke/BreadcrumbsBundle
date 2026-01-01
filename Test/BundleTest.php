<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;
use WhiteOctober\BreadcrumbsBundle\Templating\Helper\BreadcrumbsHelper;
use WhiteOctober\BreadcrumbsBundle\Test\AppKernel;
use WhiteOctober\BreadcrumbsBundle\Twig\Extension\BreadcrumbsExtension;

class BundleTest extends WebTestCase
{
    public function testInitBundle()
    {
        $client = static::createClient();

        $container = $client->getContainer();

        // Test if the service exists
        self::assertTrue($container->has('white_october_breadcrumbs.helper'));

        $service = $container->get('white_october_breadcrumbs.helper');
        self::assertInstanceOf(BreadcrumbsHelper::class, $service);
    }

    public function testRendering()
    {
        $client = static::createClient();

        $container = $client->getContainer();

        /** @var Breadcrumbs $service */
        $service = $this->getContainerForTests()->get(Breadcrumbs::class);
        $service->addItem('foo');

        /** @var BreadcrumbsExtension $breadcrumbsExtension */
        $breadcrumbsExtension = $container->get('white_october_breadcrumbs.twig');

        self::assertStringEqualsStringIgnoringLineEndings(
<<<'EOD'
    <ol id="wo-breadcrumbs" class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <span itemprop="name">foo</span>
                                    <meta itemprop="position" content="1" />

                            </li>
            </ol>

EOD,
            $breadcrumbsExtension->renderBreadcrumbs()
        );
    }

    public function testRenderingTranslationWithParameters()
    {
        $client = static::createClient();

        $container = $client->getContainer();

        /** @var Breadcrumbs $service */
        $service = $this->getContainerForTests()->get(Breadcrumbs::class);
        $service->addItem('foo', '', ['name' => 'John']);

        /** @var BreadcrumbsExtension $breadcrumbsExtension */
        $breadcrumbsExtension = $container->get('white_october_breadcrumbs.twig');

        self::assertStringEqualsStringIgnoringLineEndings(
<<<'EOD'
    <ol id="wo-breadcrumbs" class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <span itemprop="name">foo__{name:John}</span>
                                    <meta itemprop="position" content="1" />

                            </li>
            </ol>

EOD,
            $breadcrumbsExtension->renderBreadcrumbs([
                'viewTemplate' => '@WhiteOctoberBreadcrumbs/microdata.html.twig'
            ])
        );
    }

    public function testRenderingTranslationWithParametersAndTranslationDomain()
    {
        $client = static::createClient();

        $container = $client->getContainer();

        /** @var Breadcrumbs $service */
        $service = $this->getContainerForTests()->get(Breadcrumbs::class);
        $service->addItem('foo');
        $service->addItem('bar', '', ['name' => 'John']);

        /** @var BreadcrumbsExtension $breadcrumbsExtension */
        $breadcrumbsExtension = $container->get('white_october_breadcrumbs.twig');

        self::assertStringEqualsStringIgnoringLineEndings(
            <<<'EOD'
    <ol id="wo-breadcrumbs" class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <span itemprop="name">foo__domain:admin</span>
                                    <meta itemprop="position" content="1" />

                                    <span class='separator'>/</span>
                            </li>
                    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <span itemprop="name">bar__{name:John}__domain:admin</span>
                                    <meta itemprop="position" content="2" />

                            </li>
            </ol>

EOD,
            $breadcrumbsExtension->renderBreadcrumbs([
                'viewTemplate' => '@WhiteOctoberBreadcrumbs/microdata.html.twig',
                'translation_domain' => 'admin',
            ])
        );
    }

    private function getContainerForTests(): ContainerInterface
    {
        if (method_exists(WebTestCase::class, 'getContainer')) {
            return static::getContainer();
        }

        return static::$container;
    }

    public static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
