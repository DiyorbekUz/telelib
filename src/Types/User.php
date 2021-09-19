<?php

declare(strict_types=1);

namespace DiyorbekUz\Telelib\Types;

use DiyorbekUz\Telelib;
use DiyorbekUz\Telelib\Username;

/**
 * This object represents a Telegram user or bot.
 *
 * @package DiyorbekUz\Telelib
 * @author Diyorbek
 */
class User implements TypeInterface
{
    public const URL_FORMAT = 'tg://user?id=%d';


    public int $id;

    public bool $is_bot;

    public string $first_name;

    public ?string $last_name = null;

    public ?Username $username = null;

    public ?string $language_code = null;

    public ?bool $can_join_groups = null;

    public ?bool $can_read_all_group_messages = null;

    public ?bool $supports_inline_queries = null;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->is_bot = $data['is_bot'];
        $this->first_name = $data['first_name'];

        if (isset($data['last_name'])) {
            $this->last_name = $data['last_name'];
        }

        if (isset($data['username'])) {
            $this->username = new Username($data['username']);
        }

        if (isset($data['language_code'])) {
            $this->language_code = $data['language_code'];
        }

        if (isset($data['can_join_groups'])) {
            $this->can_join_groups = $data['can_join_groups'];
        }

        if (isset($data['can_read_all_group_messages'])) {
            $this->can_read_all_group_messages = $data['can_read_all_group_messages'];
        }

        if (isset($data['supports_inline_queries'])) {
            $this->supports_inline_queries = $data['supports_inline_queries'];
        }
    }

    public static function make(int $id, bool $is_bot, string $first_name): self
    {
        return new self([
            'id' => $id,
            'is_bot' => $is_bot,
            'first_name' => $first_name,
        ]);
    }

  
    public function getUrl(): string
    {
        return self::getUrlWithId($this->id);
    }


    public static function getUrlWithId(int $id): string
    {
        return sprintf(self::URL_FORMAT, $id);
    }


    public function getFullName(bool $with_link = false, bool $use_username = false, string $parse_mode = TelegramBotsApi\Bot::PARSE_MODE_DEFAULT, string $url_format = TelegramBotsApi\Username::URL_FORMAT_DEFAULT): string
    {
        $full_name = $this->first_name;
        if ($this->last_name !== null) {
            $full_name .= ' ' . $this->last_name;
        }

        $full_name = TelegramBotsApi\Bot::filterString($full_name, $parse_mode);

        if ($with_link) {
            $link = $use_username && $this->username !== null ? $this->username->getUrl($url_format) : self::getUrlWithId($this->id);
            return TelegramBotsApi\Bot::genLink($link, $full_name, $parse_mode);
        }

        return $full_name;
    }

    public function getRequestArray(): array
    {
        return [
            'id' => $this->id,
            'is_bot' => $this->is_bot,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username->getShort(),
            'language_code' => $this->language_code,
            'can_join_groups' => $this->can_join_groups,
            'can_read_all_group_messages' => $this->can_read_all_group_messages,
            'supports_inline_queries' => $this->supports_inline_queries,
        ];
    }
}
