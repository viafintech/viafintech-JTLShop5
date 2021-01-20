<?php declare(strict_types = 1);

namespace Plugin\s360_barzahlen_shop5;

use JTL\Shop;
use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;


class Bootstrap extends Bootstrapper {

    private $cFrontendPfad;
    
    public function boot(Dispatcher $dispatcher): void {

        $dispatcher->listen('shop.hook.' . \HOOK_SMARTY_OUTPUTFILTER, function (array $args) {
            $this->hookSmartyOutputfilter();
        });
        
        parent::boot($dispatcher);
    }
    
    private function hookSmartyOutputfilter() {
        $this->cFrontendPfad = $this->getPlugin()->getPaths()->getFrontendPath();   

        if (Shop::getPageType() === PAGE_BESTELLVORGANG && !empty($_SESSION["Barzahlen"]->message)) {
            $this->pageBestellvorgang();
        }
        if (Shop::getPageType() === PAGE_BESTELLABSCHLUSS && !empty($_SESSION["Barzahlen"]->checkout_token)) {
            $this->pageBestellabschluss();
        }
    }
    
    private function pageBestellvorgang() {
        $level = [
            "success" => "success",
            "notice" => "info",
            "warning" => "warning",
            "error" => "danger"
        ];

        $_SESSION["Barzahlen"]->message->level = $level[$_SESSION["Barzahlen"]->message->type];
        Shop::Smarty()->assign("message", (array)$_SESSION["Barzahlen"]->message);
        unset($_SESSION["Barzahlen"]->message);

        $template = Shop::Smarty()->fetch($this->cFrontendPfad . 'template/messages.tpl');
        pq("#fieldset-payment")->prepend($template);
    }

    private function pageBestellabschluss() {
        Shop::Smarty()->assign("checkout_token", $_SESSION["Barzahlen"]->checkout_token);
        unset($_SESSION["Barzahlen"]->checkout_token);

        if ($_SESSION["Barzahlen"]->api->sandbox) {
            Shop::Smarty()->assign("sandbox", $_SESSION["Barzahlen"]->api->sandbox);
        }

        $template = Shop::Smarty()->fetch($this->cFrontendPfad . 'template/checkout_script.tpl');
        pq("body")->append($template);

        $template = Shop::Smarty()->fetch($this->cFrontendPfad . 'template/checkout_button.tpl');
        pq(".order-confirmation-details")->append($template);

        unset($_SESSION["Barzahlen"]);
    }
    
}