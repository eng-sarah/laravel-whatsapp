<?php

namespace MissaelAnda\Whatsapp;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MissaelAnda\Whatsapp\Events\SendingMessage;
use MissaelAnda\Whatsapp\Exceptions\MessageRequestException;
use MissaelAnda\Whatsapp\Exceptions\PhoneNumberNameNotFound;
use MissaelAnda\Whatsapp\Messages\WhatsappMessage;

class Templates
{
    public const WHATSAPP_API_URL = 'https://graph.facebook.com/v{{VERSION}}';
    public const WHATSAPP_MESSAGE_API = 'message_templates';
    public const WHATSAPP_API_VERSION = '21.0';

    public function __construct(
        protected readonly ?string $numberId,
        protected readonly ?string $token,
    ) {
        // 
    }

    public function client(string $numberId, string $token): static
    {
        if (empty($numberId)) {
            throw new \Exception('Invalid number ID provided.');
        }

        if (empty($token)) {
            throw new \Exception('Invalid token provided.');
        }

        return new static($numberId, $token);
    }

    public function numberId(string $numberId): static
    {
        if (empty($numberId)) {
            throw new \Exception('Invalid number ID provided.');
        }

        return new static($numberId, $this->token);
    }

    public function token(string $token): static
    {
        if (empty($token)) {
            throw new \Exception('Invalid token provided.');
        }

        return new static($this->numberId, $token);
    }

    public function numberName(string $name): static
    {
        $phone = Config::get("whatsapp.phones.$name");

        if ($phone === null) {
            throw new PhoneNumberNameNotFound($name);
        }

        return $this->numberId($phone, $this->token);
    }

    public function defaultNumber(): static
    {
        return $this->numberId(Config::get('whatsapp.default_number_id'));
    }
       /**
     * @return MessageResponse|array<MessageResponse|MessageRequestException>
     * 
     * @throws MessageRequestException
     */
    public function send(string|array $phones, WhatsappMessage $message): MessageResponse|array
    {
        if (empty($this->numberId)) {
            throw new \Exception('The number id is required.');
        }

        SendingMessage::dispatch($this->numberId, (array)$phones, $message);

        if (is_string($phones) || count($phones) === 1) {
            return $this->sendMessage(Arr::wrap($phones)[0], $message);
        }
    }

    protected function sendMessage(string $phone): MessageResponse
    {
        $response = $this->sendRequest($this->buildApiEndpoint('message_templates'), 'get');

        return MessageResponse::build($response);
    }


    protected function request(): PendingRequest
    {
        return Http::acceptJson()->withToken($this->token);
    }

    protected function buildApiEndpoint(string $for, bool $withNumberId = true): string
    {
        return Str::of(static::WHATSAPP_API_URL)
            ->replace('{{VERSION}}', static::WHATSAPP_API_VERSION)
            ->when($withNumberId, fn ($str) => $str->append('/', $this->numberId))
            ->append('/', $for);
    }

    protected function sendRequest(string $url, string $method): Response
    {
        /** @var Response */
        $response = $this->request()->{strtolower($method)}($url);

        if (!$response->successful()) {
            throw new MessageRequestException($response);
        }

        return $response;
    }
}