FazyAsseticConfigBundle
=======================

*This is a very new bundle, please report any issues.*

This Symfony 2 bundle allows you to use container parameters within any assets such as JavaScripts and CSS files,
so long as they can be compiled through [Assetic](http://symfony.com/doc/current/cookbook/assetic/asset_management.html).
You will need to be familiar with Assetic before you can use this bundle.

Two Assetic filters are provided: **config** and **config-json**. The only difference is that **config-json**
JSON-encodes the output value.

Usage
-----

Install using [Composer](https://getcomposer.org/):

    composer require fazy/assetic-config-bundle:dev-master

Add the following line to **registerBundles()** in **app/AppKernel.php**:

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Fazy\AsseticConfigBundle\FazyAsseticConfigBundle(),
        );

        // ..
    }

For any assets that you want to use config values in, ensure they are filtered through the **config** or **config-json**
filter as follows in your Twig templates:

    {% javascripts filter="config-json"
        '@MyBundle/Resources/public/js/config.js'
    %}
        <script type="text/javascript" src="{{ asset_url }}"></script>
    {% endjavascripts %}

In this example, only one script is included, **config.js**. It is filtered through the **config-json** filter.

Within the asset file, you can insert any config item using the pattern `__config__<config>__`, for example:

    // app/config/parameters.yml
    my_bundle.foo.bar: Hello12345!

    // MyBundle/Resources/public/js/config.js
    MY_BUNDLE_CONFIG = {
        "foo": {
            "bar": __config__my_bundle.foo.bar__
        },
    };

This should result in the following code being served to the client (the double quotes for the value are a result of
the JSON-encode):

    MY_BUNDLE_CONFIG = {
        "foo": {
            "bar": "Hello12345!"
        },
    };

At the risk of stating the obvious, you should not output any sensitive configuration values in this way!

Configuration
-------------

At this point there are no configuration options, but you can extend the class
**Fazy\AsseticConfigBundle\Assetic\Filter\ConfigFilter**:

    // app/config/parameters.yml
    parameters:
        fazy_assetic_config.filter_class: MyBundle\Assetic\Filter\ConfigFilter

    // MyBundle/Assetic/Filter/ConfigFilter.php
    namespace MyBundle\Assetic\Filter\ConfigFilter;
    use Fazy\AsseticConfigBundle\Assetic\Filter\ConfigFilter as BaseConfigFilter

    class ConfigFilter extends BaseConfigFilter
    {
        // ...
    }

You can also make your own service, perhaps injecting a different output encoder instead of the supplied JsonEncoder.
See **Resources/services.xml** in the bundle.
