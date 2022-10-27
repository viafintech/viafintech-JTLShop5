<?php

declare(strict_types=1);

namespace Plugin\s360_barzahlen_shop5;

use JTL\Shop;
use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;

class Bootstrap extends Bootstrapper
{

    private $cFrontendPfad;
    private $cacheArr = [CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE, CACHING_GROUP_LICENSES, CACHING_GROUP_PLUGIN, CACHING_GROUP_BOX];

    public function installed()
    {
        parent::installed();
        // Bug Workaround - JTL-Shop 5.0.0 does not clear cache when a plugin gets installed via the extension store
        Shop::Container()->getCache()->flushTags($this->cacheArr);
    }

    public function updated($oldVersion, $newVersion)
    {
        parent::updated($oldVersion, $newVersion);
        // Bug Workaround - JTL-Shop 5.0.0 does not clear cache when a plugin gets installed via the extension store
        Shop::Container()->getCache()->flushTags($this->cacheArr);
    }

    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);
        $dispatcher->listen(
            'shop.hook.' . \HOOK_SMARTY_OUTPUTFILTER,
            function (array $args) {
                $this->hookSmartyOutputfilter();
            }
        );
    }

    private function hookSmartyOutputfilter()
    {
        $this->cFrontendPfad = $this->getPlugin()->getPaths()->getFrontendPath();

        if (Shop::getPageType() === PAGE_BESTELLABSCHLUSS && !empty($_SESSION["Barzahlen"]->checkout_token)) {
            $this->pageBestellabschluss();
        }
    }

    private function pageBestellabschluss()
    {
        Shop::Smarty()->assign("checkout_token", $_SESSION["Barzahlen"]->checkout_token);
        unset($_SESSION["Barzahlen"]->checkout_token);

        $template = Shop::Smarty()->fetch($this->cFrontendPfad . 'template/checkout_script.tpl');
        pq("body")->append($template);

        $template = Shop::Smarty()->fetch($this->cFrontendPfad . 'template/checkout_button.tpl');
        pq(".order-confirmation-details")->append($template);

        unset($_SESSION["Barzahlen"]);
    }
}
