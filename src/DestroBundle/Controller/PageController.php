<?php
namespace DestroBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PageController extends Controller
{
    public function aboutAction(){
        return $this->render('DestroBundle:Pages:about.html.twig');
    }

    public function termsAction(){
        return $this->render('DestroBundle:Pages:terms.html.twig');
    }

    public function privacyAction(){
        return $this->render('DestroBundle:Pages:privacy.html.twig');
    }

    public function faqAction(){
        return $this->render('DestroBundle:Pages:faq.html.twig');
    }

    public function shippingAction(){
        return $this->render('DestroBundle:Pages:shipping.html.twig');
    }

    public function returnsAction(){
        return $this->render('DestroBundle:Pages:returns.html.twig');
    }
}
