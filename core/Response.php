<?php

namespace Core;

class Response
{
    private string $data = '';
    private array $headers = [];
    private int $statusCode = 200;

    /**
     * Установить тело ответа
     * 
     * @param mixed $data Данные для отправки
     * @return self
     */
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Установить заголовок ответа
     * 
     * @param string $name Название заголовка
     * @param string $value Значение заголовка
     * @return self
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Установить HTTP-статус код
     * 
     * @param int $code HTTP-статус код
     * @return self
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Отправить ответ клиенту
     * 
     * @return void
     */
    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->data;
    }

    /**
     * Установить JSON-ответ
     * 
     * @param mixed $data Данные для кодирования в JSON
     * @return self
     */
    public function json($data): self
    {
        $this->setHeader('Content-Type', 'application/json');
        $this->data = json_encode($data);
        return $this;
    }
}
