<?php

namespace app\Controllers;

use Core\Response;

class HomeController
{
    /**
     * Главная страница API
     * 
     * @return Response JSON-ответ с информацией о состоянии API
     */
    public function index(): Response
    {
        $response = new Response();
        return $response->json(['status' => 'ok', 'message' => 'Api работает']);
    }
}
