<?php

declare(strict_types=1);

namespace DiyorbekUz\Telelib;

use DiyorbekUz\Telelib\Exceptions\Error;

/**
 * Class Username
 *
 * @package DiyorbekUz\Telelib
 * @author DiyorbekDev
 */
class Username
{
    public const URL_FORMAT_T_ME = 'https://t.me/%s';
    public const URL_FORMAT_T_DO_RU = 'https://t-do.ru/%s';
    public const URL_FORMAT_TLGG_RU = 'https://tlgg.ru/%s';
    public const URL_FORMAT_TELEG_RUN = 'https://teleg.run/%s';
    public const URL_FORMAT_TELE_CLICK = 'https://tele.click/%s';
    public const URL_FORMAT_DEFAULT = self::URL_FORMAT_T_ME;

    protected string $username;

    public function __construct(string $username)
    {
        $username = ltrim($username, '@');
        if (!self::checkUsername($username)) {
            throw new Error("Incorrect username: $username");
        }
        $this->username = $username;
    }

    public static function checkUsername(string $username): bool
    {
        return strpos($username, '__') === false &&
            preg_match('/^([a-zA-Z])(\w{4,31})$/', $username);
//            && preg_match('/_$/', $username) === false;
    }


    public function getLower(): string
    {
        return strtolower($this->username);
    }

    public function getUpper(): string
    {
        return strtoupper($this->username);
    }

    public function getShort(): string
    {
        return $this->username;
    }

    public function getFull(): string
    {
        return '@' . $this->username;
    }

    public function getUrl(string $url_format = self::URL_FORMAT_DEFAULT): string
    {
        return sprintf($url_format, $this->username);
    }
}
