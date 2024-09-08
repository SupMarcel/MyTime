<?php

// app/Router/RouterFactory.php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    use Nette\StaticClass;

    /**
     * Vytváří a vrací seznam routovacích pravidel pro aplikaci.
     * @return RouteList výsledný router pro aplikaci
     */
    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        // Specifické trasy pro různé moduly a presentery
        $router->addRoute('worker/calendar', 'Worker:WorkerCalendar:default');
        $router->addRoute('worker-calendar/addData', 'Worker:WorkerCalendar:addData');
        $router->addRoute('worker-calendar/clearData', 'Worker:WorkerCalendar:clearData');
                 
        $router->addRoute('admin/register', 'Admin:AdminRegistration:signUp');
        $router->addRoute('admin/sign-in', 'Admin:AdminRegistration:signIn');
        
        $router->addRoute('chief/register', 'Chief:ChiefRegistration:signUp');
        $router->addRoute('chief/sign-in', 'Chief:ChiefRegistration:signIn');
        
        $router->addRoute('worker/register', 'Worker:WorkerRegistration:signUp');
        $router->addRoute('worker/sign-in', 'Worker:WorkerRegistration:signIn');
        
        $router->addRoute('client/register', 'Client:ClientRegistration:signUp');
        $router->addRoute('client/sign-in', 'Client:ClientRegistration:signIn');

        // Přidání univerzální trasy pro všechny moduly, presentery a akce
        $router->addRoute('<module>/<presenter>/<action>[/<id>]', [
            'module' => [
                Route::FILTER_TABLE => [
                    'worker' => 'Worker',
                    'admin' => 'Admin',
                    'chief' => 'Chief',
                    'client' => 'Client',
                ]
            ],
            'presenter' => 'WorkerDashboard',
            'action' => 'default',
        ]);
        
        /* $router->addRoute('worker/<presenter>/<action>', [
            "module" => "Worker",
            "presenter" => "WorkerDashboard",
            "action" => "default" 
        ]); */

        // Root trasa (hlavní stránka)
        $router->addRoute('', 'Common:HomePage:default');

        return $router;
    }
}

