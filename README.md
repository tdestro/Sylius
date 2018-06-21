mbp1:Sylius anthonyodestro$ git pull https://github.com/Sylius/Sylius.git master
remote: Counting objects: 1932, done.
remote: Compressing objects: 100% (757/757), done.
remote: Total 1932 (delta 1208), reused 1704 (delta 1040), pack-reused 0
Receiving objects: 100% (1932/1932), 467.44 KiB | 3.57 MiB/s, done.
Resolving deltas: 100% (1208/1208), completed with 262 local objects.
From https://github.com/Sylius/Sylius
 * branch                  master     -> FETCH_HEAD
Auto-merging yarn.lock
CONFLICT (content): Merge conflict in yarn.lock
Auto-merging src/Sylius/Component/Core/spec/Resolver/DefaultShippingMethodResolverSpecSpec.php
CONFLICT (modify/delete): src/Sylius/Bundle/ShopBundle/Gulpfile.js deleted in 885110fbe3487383b8b1eefea2a78461e117354f and modified in HEAD. Version HEAD of src/Sylius/Bundle/ShopBundle/Gulpfile.js left in tree.
Removing src/Sylius/Bundle/AdminBundle/Gulpfile.js
Auto-merging package.json
CONFLICT (content): Merge conflict in package.json
Auto-merging easy-coding-standard.yml
Auto-merging composer.json
CONFLICT (content): Merge conflict in composer.json
Auto-merging app/config/parameters.yml.dist
CONFLICT (content): Merge conflict in app/config/parameters.yml.dist
Auto-merging app/config/config.yml
CONFLICT (content): Merge conflict in app/config/config.yml
CONFLICT (modify/delete): Gulpfile.js deleted in 885110fbe3487383b8b1eefea2a78461e117354f and modified in HEAD. Version HEAD of Gulpfile.js left in tree.
Automatic merge failed; fix conflicts and then commit the result.


<h1 align="center">
    <a href="http://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</h1>

Sylius is an Open Source eCommerce Framework on top of [**Symfony**](https://symfony.com). 

The highest quality of code, strong testing culture, built-in Agile (BDD) workflow and exceptional flexibility make it the best solution for application tailored to your business requirements. 
Powerful REST API allows for easy integrations and creating unique customer experience on any device.

We're using full-stack Behavior-Driven-Development, with [phpspec](http://phpspec.net) and [Behat](http://behat.org).

Enjoy being an eCommerce Developer again!

Installation
------------

[Install Sylius](http://docs.sylius.com/en/latest/book/installation/installation.html) with Composer (see [requirements details](http://docs.sylius.com/en/latest/book/installation/requirements.html)).

Alternatively, you can [use our Vagrant setup](http://docs.sylius.com/en/latest/book/installation/vagrant_installation.html).

Documentation
-------------
 
Documentation is available at [docs.sylius.com](http://docs.sylius.com).

Community
---------

[Get Sylius support](http://docs.sylius.com/en/latest/support/index.html) on [Slack](https://sylius.com/slack), [Forum](https://forum.sylius.com/) or [Stack Overflow](https://stackoverflow.com/questions/tagged/sylius).

Stay updated by following our [Twitter](https://twitter.com/Sylius) and [Facebook](https://www.facebook.com/SyliusEcommerce/).

Contributing
------------

Would like to help us and build the most developer-friendly eCommerce platform? Start from reading our [Contributing Guide](http://docs.sylius.com/en/latest/contributing/index.html)!

License
-------

Sylius is completely free and released under the [MIT License](https://github.com/Sylius/Sylius/blob/master/LICENSE).

Authors
-------

Sylius was originally created by [Paweł Jędrzejewski](http://pjedrzejewski.com).
See the list of [contributors from our awesome community](https://github.com/Sylius/Sylius/contributors).
