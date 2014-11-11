<?php

namespace Fazy\AsseticConfigBundle\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;
use Assetic\Filter\HashableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigFilter implements FilterInterface, HashableInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    protected $configPattern;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function filterLoad(AssetInterface $asset)
    {
    }

    /**
     * @inheritdoc
     */
    public function filterDump(AssetInterface $asset)
    {
        $content = $asset->getContent();

        $content = preg_replace_callback(
            '/__config__(.+?)__/', // change to $configPattern
            function($matches) {
                // @todo - make the encoding extensible
                return json_encode($this->container->getParameter($matches[1]));
            },
            $content
        );

        $asset->setContent($content);
    }

    /**
     * @inheritdoc
     */
    public function hash()
    {
        return serialize($this->container->getParameterBag()->all());
    }
}
