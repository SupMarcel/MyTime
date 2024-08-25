<?php

// app/Router/RouterFactory.php

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;
        
           // Přidání trasy pro WorkerCalendarPresenter
        $router->addRoute('worker/calendar', 'Worker:WorkerCalendar:default');
        $router->addRoute('worker-calendar/addData', 'Worker:WorkerCalendar:addData');
        $router->addRoute('worker-calendar/clearData', 'Worker:WorkerCalendar:clearData');

        $router->addRoute('', 'Common:HomePage:default');

        // Admin registration routes
        $router->addRoute('admin/register', [
            'module' => 'Admin',
            'presenter' => 'AdminRegistration',
            'action' => 'signUp'
        ]);
        $router->addRoute('admin/sign-in', [
            'module' => 'Admin',
            'presenter' => 'AdminRegistration',
            'action' => 'signIn'
        ]);

        // Chief registration routes
        $router->addRoute('chief/register', [
            'module' => 'Chief',
            'presenter' => 'ChiefRegistration',
            'action' => 'signUp'
        ]);
        $router->addRoute('chief/sign-in', [
            'module' => 'Chief',
            'presenter' => 'ChiefRegistration',
            'action' => 'signIn'
        ]);

        // Worker registration routes
        $router->addRoute('worker/register', [
            'module' => 'Worker',
            'presenter' => 'WorkerRegistration',
            'action' => 'signUp'
        ]);
        $router->addRoute('worker/sign-in', [
            'module' => 'Worker',
            'presenter' => 'WorkerRegistration',
            'action' => 'signIn'
        ]);

        // Client registration routes
        $router->addRoute('client/register', [
            'module' => 'Client',
            'presenter' => 'ClientRegistration',
            'action' => 'signUp'
        ]);
        $router->addRoute('client/sign-in', [
            'module' => 'Client',
            'presenter' => 'ClientRegistration',
            'action' => 'signIn'
        ]);

        // General routes
        $router->addRoute('<module>/<presenter>/<action>', [
            'module' => 'Common',
            'presenter' => 'HomePage',
            'action' => 'default'
        ]);

        return $router;
    }
}
