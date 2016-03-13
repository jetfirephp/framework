<?php

namespace JetFire\Framework\Template;

use JetFire\Framework\App;
use JetFire\Framework\Factory\Server;
use JetFire\Framework\System\View;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class DefaultTwigExtension extends Twig_Extension{

    public function getGlobals()
    {
        return array(

        );
    }

    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('dateFr',function($date){
                $Jour = array("Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi","Samedi");
                $Mois = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
                $datefr = $Jour[$date->format("w")]." ".$date->format("d")." ".$Mois[$date->format("n")]." ".$date->format("Y");
                echo $datefr;
            }),
        );
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('token',function($name = ''){
                return generate_token($name);
            }),
            new Twig_SimpleFunction('path',function($path = null,$params = []){
                return View::getInstance()->path($path,$params);
            }),
            new Twig_SimpleFunction('asset',function($value){
                return View::getInstance()->asset($value);
            }),
            new Twig_SimpleFunction('get',function($value){
                return App::getInstance()->get('request')->getQuery()->get($value);
            }),
            new Twig_SimpleFunction('post',function($value){
                return App::getInstance()->get('request')->getPost()->get($value);
            }),
            new Twig_SimpleFunction('session',function($value){
                return App::getInstance()->get('session')->getSession()->get($value);
            }),
            new Twig_SimpleFunction('cookie',function($value){
                return App::getInstance()->get('request')->getCookies()->get($value);
            }),
            new Twig_SimpleFunction('url',function(){
                $server = Server::getInstance();
                return urlencode(
                    'http://'.$server->get('HTTP_HOST').$server->get('REQUEST_URI')
                );
            }),
        );
    }

    public function getName()
    {
        return 'twig_default_extension';
    }

} 