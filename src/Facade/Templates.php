<?php

namespace MissaelAnda\Whatsapp\Facade;

use MissaelAnda\Whatsapp\Templates as ConcreteTemplates;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array send(string|array<string> $phones, \MissaelAnda\Whatsapp\WhatsappMessage $message)
 * @method static \MissaelAnda\Whatsapp\Whatsapp client(string $numberId, string $token)
 * @method static \MissaelAnda\Whatsapp\Whatsapp token(string $token)
 * @method static \MissaelAnda\Whatsapp\Whatsapp numberId(string $numberId)
 * @method static \MissaelAnda\Whatsapp\Whatsapp numberName(string $name)
 * @method static \MissaelAnda\Whatsapp\Whatsapp defaultNumber()
 */
class Templates extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'templates';
    }

    public static function from(?string $numberId, ?string $token): ConcreteTemplates
    {
        return new ConcreteTemplates($numberId, $token);
    }
}