<?php

namespace Fazy\AsseticConfigBundle\Tests\Assetic\Filter;

use Fazy\AsseticConfigBundle\Assetic\Filter\ConfigFilter;

class ConfigFilterText extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Assetic\Asset\AssetInterface
     */
    private $asset;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    private $parameterBag;

    public function setUp()
    {
        $this->asset = $this->getMock('Assetic\Asset\AssetInterface');
        $this->parameterBag = $this->getMock('Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface');
    }

    public function testFilterDump()
    {
        $this->asset
            ->expects($this->atLeastOnce())
            ->method('getContent')
            ->will($this->returnValue('Test __config__foo_bar__ String'));

        $this->asset
            ->expects($this->once())
            ->method('setContent')
            ->with('Test "foo bar value" String');

        $this->parameterBag
            ->expects($this->once())
            ->method('get')
            ->with('foo_bar')
            ->will($this->returnValue('foo bar value'));

        $configFilter = new ConfigFilter($this->parameterBag);
        $configFilter->filterDump($this->asset);
    }
}
