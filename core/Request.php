<?php

namespace Core;

class Request
{
    private string $uri;
    private string $method;
    private array $get;
    private array $post;
    private array $files;
    private array $server;

    /**
     * Конструктор запроса
     * 
     * @param array $server Данные из $_SERVER
     * @param array $get Данные из $_GET
     * @param array $post Данные из $_POST
     * @param array $files Данные из $_FILES
     */
    public function __construct(array $server, array $get, array $post, array $files)
    {
        $this->server = $server;
        $this->get = $get;
        $this->post = $post;
        $this->files = $files;

        $this->uri = parse_url($server['REQUEST_URI'], PHP_URL_PATH);
        $this->method = $server['REQUEST_METHOD'];

        if ($this->method === 'PUT') {
            $this->post = $this->parsePutData();
        }

        if (
            in_array($this->method, ['POST', 'PUT']) &&
            isset($server['CONTENT_TYPE']) &&
            strpos($server['CONTENT_TYPE'], 'application/json') !== false
        ) {

            $input = json_decode(file_get_contents('php://input'), true);
            $this->post = $input ?? [];
        }
    }

    /**
     * Парсинг данных PUT-запроса
     * 
     * @return array Массив с данными PUT-запроса
     */
    private function parsePutData(): array
    {
        $putData = [];
        parse_str(file_get_contents('php://input'), $putData);
        return $putData;
    }

    /**
     * Получить URI запроса без query-строки
     * 
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Получить HTTP-метод запроса
     * 
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Получить GET-параметр
     * 
     * @param string $key Название параметра
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function get(string $key, $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Получить POST-параметр
     * 
     * @param string $key Название параметра
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function post(string $key, $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Получить массив загруженных файлов
     * 
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}
