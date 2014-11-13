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

        $parameters = array(
            'foo'       => 'foo value',
            'bar'       => 'bar value',
            'foo.bar'   => 'foo dot bar value',
            'null'      => null,
            'quotes'    => '"aren\'t quotes wonderful"',
            'array'     => array('test' => 'something')
        );

        $this->parameterBag = $this->getMock('Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface');

        $this->parameterBag
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($key) use ($parameters) {
                return $parameters[$key];
            }));

        $this->parameterBag
            ->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($key) use ($parameters) {
                return array_key_exists($key, $parameters);
            }));
    }

    /**
     * @dataProvider filterDumpProvider
     */
    public function testFilterDump($content, $output)
    {
        $this->asset
            ->expects($this->atLeastOnce())
            ->method('getContent')
            ->will($this->returnValue($content));

        $this->asset
            ->expects($this->once())
            ->method('setContent')
            ->with($output);

        $configFilter = new ConfigFilter($this->parameterBag);
        $configFilter->filterDump($this->asset);
    }

    public function testFilterDumpNonScalarValue()
    {
        $this->asset
            ->expects($this->atLeastOnce())
            ->method('getContent')
            ->will($this->returnValue('__config__array__'));

        $this->setExpectedException(
            'Fazy\AsseticConfigBundle\Assetic\Filter\Exception\ValueNotSupportedException',
            'ConfigFilter encoder only supports scalar or null values.'
        );

        $configFilter = new ConfigFilter($this->parameterBag);
        $configFilter->filterDump($this->asset);
    }

    public function testFilterDumpParameterNotFound()
    {
        $this->asset
            ->expects($this->atLeastOnce())
            ->method('getContent')
            ->will($this->returnValue('__config__non_existent_parameter__'));

        $this->setExpectedException(
            'Fazy\AsseticConfigBundle\Assetic\Filter\Exception\ParameterNotFoundException',
            "Parameter not found: 'non_existent_parameter'."
        );

        $configFilter = new ConfigFilter($this->parameterBag);
        $configFilter->filterDump($this->asset);
    }

    /**
     * @dataProvider filterDumpJsonProvider
     */
    public function testFilterDumpWithJsonEncoder($content, $output)
    {
        $this->asset
            ->expects($this->atLeastOnce())
            ->method('getContent')
            ->will($this->returnValue($content));

        $this->asset
            ->expects($this->once())
            ->method('setContent')
            ->with($output);

        $configFilter = new ConfigFilter($this->parameterBag);
        $configFilter->setEncoder(function($value) { return json_encode($value); });
        $configFilter->filterDump($this->asset);
    }

    public function filterDumpProvider()
    {
        return array(
            array('Test __config__foo__ String',                'Test foo value String'),
            array('Foo: __config__foo__, Bar: __config__bar__', 'Foo: foo value, Bar: bar value'),
            array('__config__foo.bar__',                        'foo dot bar value'),
            array('__config__null__',                           ''),
            array('__config__quotes__',                         '"aren\'t quotes wonderful"'),
        );
    }

    public function filterDumpJsonProvider()
    {
        return array(
            array('Test __config__foo__ String',                'Test "foo value" String'),
            array('Foo: __config__foo__, Bar: __config__bar__', 'Foo: "foo value", Bar: "bar value"'),
            array('__config__foo.bar__',                        '"foo dot bar value"'),
            array('__config__null__',                           'null'),
            array('__config__quotes__',                         '"\"aren\'t quotes wonderful\""'),
            array('__config__array__',                          '{"test":"something"}'),
        );
    }
}
