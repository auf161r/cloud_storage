<?php

namespace Core;

class App
{
    private Router $router;

    /**
     * Конструктор приложения
     */
    public function __construct()
    {
        $this->router = new Router();
        $this->initRoutes();
    }

    /**
     * Инициализация маршрутов приложения
     * 
     * @return void
     */
    private function initRoutes(): void
    {
        $this->router->add('GET', '/', 'HomeController', 'index');
        $this->router->add('GET', '/users/list', 'User', 'list');
        $this->router->add('GET', '/users/get/{id}', 'User', 'get');
        $this->router->add('PUT', '/users/update', 'User', 'update');
        $this->router->add('POST', '/login', 'User', 'login');
        $this->router->add('GET', '/logout', 'User', 'logout');
        $this->router->add('POST', '/reset_password', 'User', 'resetPassword');
        $this->router->add('POST', '/reset-password/{token}', 'User', 'setNewPassword');
        $this->router->add('GET', '/admin/users/list', 'Admin', 'list');
        $this->router->add('GET', '/admin/users/get/{id}', 'Admin', 'get');
        $this->router->add('DELETE', '/admin/users/delete/{id}', 'Admin', 'delete');
        $this->router->add('PUT', '/admin/users/update/{id}', 'Admin', 'update');
        $this->router->add('GET', '/files/list', 'File', 'list');
        $this->router->add('POST', '/files/add', 'File', 'add');
        $this->router->add('POST', '/register', 'User', 'register');
        $this->router->add('GET', '/files/get/{id}', 'File', 'get');
        $this->router->add('PUT', '/files/rename', 'File', 'rename');
        $this->router->add('DELETE', '/files/remove/{id}', 'File', 'remove');
        $this->router->add('POST', '/directories/add', 'File', 'addDirectory');
        $this->router->add('PUT', '/directories/rename', 'File', 'renameDirectory');
        $this->router->add('GET', '/directories/get/{id}', 'File', 'getDirectory');
        $this->router->add('DELETE', '/directories/delete/{id}', 'File', 'deleteDirectory');
        $this->router->add('GET', '/files/share/{id}', 'File', 'shareList');
        $this->router->add('PUT', '/files/share/{id}/{userId}', 'File', 'shareAdd');
        $this->router->add('DELETE', '/files/share/{id}/{userId}', 'File', 'shareRemove');
        $this->router->add('GET', '/user/search/{email}', 'User', 'search');
    }

    /**
     * Получить объект роутера
     * 
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}
