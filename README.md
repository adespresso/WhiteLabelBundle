# adespresso/white-label-bundle

This bundle allow to release white label version of the website.

## Install

    php composer.phar require adespresso/white-label-bundle

## Register the bundle in Symfony2

    <?php
    // AppKernel.php

    use Symfony\Component\HttpKernel\Kernel;
    use Symfony\Component\Config\Loader\LoaderInterface;
    
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                ...
                new Ae\WhiteLabelBundle\AeWhiteLabelBundle(),

            );
            ...
        }
    }

## Configuration

This configuration parameters are an example for a website with two different whitelabel, one identified by a different domain, the other one by a parameter in user entity.

    ae_white_label:
        default_website: foo_site
        websites:
            foo_site:
                label: foo labels
                host: foo.domain.com
                method: byHost
                custom_params:
                    customFoo: bar
                priority: 1
            bar_site:
                label: bar labels
                user_param:
                    key: origin
                    value: bar_website
                method: byUserParam
                priority: 2
    

## Twig

The bundle expose different twig functions: whitelabel, website and impersonateUrl.

### Whitelabel

It a conditional statements based on website (in example above foo_site and bar_site). 
It allows to concatenate the conditions with logical operators as AND and OR 
 
 
     {% whitelabel 'foo_site' %}
         <a href="#">Home for Foo site</a>
     {% else %}
         <a href="#">Home for Other sites</a>
     {% endwhitelabel %}
     
 ### Website

Return the website info set in configuration.

    {% set thisWebsite = website() %}
    
### ImpersonateUrl

Build the impersonate url for a specific whitelabel site. The first parameter is the whitelabel site, the second one (is optional) is the url where the impersonate url have to point.

    {% set impersonateUrl = impersonateUrl('foo_site', 'http://domain.com' ) %}


## Copyright

© 2017 AdEspresso, Inc

## License

Apache 2.0 (see [LICENSE](/LICENSE) file or http://www.apache.org/licenses/LICENSE-2.0)
