<?php

namespace Fazy\AsseticConfigBundle\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;
use Assetic\Filter\HashableInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ConfigFilter implements FilterInterface, HashableInterface
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var string Regex to use for picking out config items
     */
    protected $configPattern;

    /**
     * @var callable Function to encode the parameter
     */
    protected $encoder;

    /**
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->configPattern = '/__config__(.+?)__/';
        $this->encoder = function($value) {
            if (! is_scalar($value) && ! is_null($value)) {
                throw new Exception\ValueNotSupportedException(
                    'ConfigFilter encoder only supports scalar or null values.'
                );
            }
            return $value;
        };
    }

    /**
     * @param string $configPattern
     */
    public function setConfigPattern($configPattern)
    {
        $this->configPattern = $configPattern;
    }

    /**
     * @param callable $encoder
     */
    public function setEncoder(callable $encoder)
    {
        $this->encoder = $encoder;
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
        $encoder = $this->encoder;

        $content = preg_replace_callback(
            $this->configPattern,
            function($matches) use ($encoder) {
                $parameter = $matches[1];

                if (! $this->parameterBag->has($parameter)) {
                    throw new Exception\ParameterNotFoundException("Parameter not found: '$parameter'.");
                }

                return $encoder($this->parameterBag->get($parameter));
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
        return serialize($this->parameterBag->all());
    }
}
