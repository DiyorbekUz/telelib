<?php

declare(strict_types=1);

namespace DiyorbekUz\Telelib\Types;

use DiyorbekUz\Telelib\Exceptions\Error;

/**
 * This object represents an incoming update.At most one of the optional parameters can be present in any given update.
 *
 * @package DiyorbekUz\Telelib
 * @author Diyorbek
 */
class Update implements TypeInterface
{
    public const ACT_MESSAGE = 'message';
    public const ACT_EDITED_MESSAGE = 'edited_message';
    public const ACT_CHANNEL_POST = 'channel_post';
    public const ACT_EDITED_CHANNEL_POST = 'edited_channel_post';
    public const ACT_INLINE_QUERY = 'inline_query';
    public const ACT_CHOSEN_INLINE_RESULT = 'chosen_inline_result';
    public const ACT_CALLBACK_QUERY = 'callback_query';
    public const ACT_SHIPING_QUERY = 'shipping_query';
    public const ACT_PRE_CHECKOUT_QUERY = 'pre_checkout_query';
    public const ACT_POLL = 'poll';
    public const ACT_POLL_ANSWER = 'poll_answer';


    public int $update_id;

    public ?Message $message = null;

    public ?Message $edited_message = null;

    public ?Message $channel_post = null;

    public ?Message $edited_channel_post = null;

    public ?InlineQuery $inline_query = null;

    public ?ChosenInlineResult $chosen_inline_result = null;

    public ?CallbackQuery $callback_query = null;

    public ?ShippingQuery $shipping_query = null;

    public ?PreCheckoutQuery $pre_checkout_query = null;

    public ?Poll $poll = null;

    public ?PollAnswer $poll_answer = null;

    public function __construct(array $data)
    {
        $this->update_id = $data['update_id'];
        if (isset($data['message'])) {
            $this->message = $data['message'] instanceof Message
                ? $data['message']
                : new Message($data['message']);
        }

        if (isset($data['edited_message'])) {
            $this->edited_message = $data['edited_message'] instanceof Message
                ? $data['edited_message']
                : new Message($data['edited_message']);
        }

        if (isset($data['channel_post'])) {
            $this->channel_post = $data['channel_post'] instanceof Message
                ? $data['channel_post']
                : new Message($data['channel_post']);
        }

        if (isset($data['edited_channel_post'])) {
            $this->edited_channel_post = $data['edited_channel_post'] instanceof Message
                ? $data['edited_channel_post']
                : new Message($data['edited_channel_post']);
        }

        if (isset($data['inline_query'])) {
            $this->inline_query = $data['inline_query'] instanceof InlineQuery
                ? $data['inline_query']
                : new InlineQuery($data['inline_query']);
        }

        if (isset($data['chosen_inline_result'])) {
            $this->chosen_inline_result = $data['chosen_inline_result'] instanceof ChosenInlineResult
                ? $data['chosen_inline_result']
                : new ChosenInlineResult($data['chosen_inline_result']);
        }

        if (isset($data['callback_query'])) {
            $this->callback_query = $data['callback_query'] instanceof CallbackQuery
                ? $data['callback_query']
                : new CallbackQuery($data['callback_query']);
        }

        if (isset($data['shipping_query'])) {
            $this->shipping_query = $data['shipping_query'] instanceof ShippingQuery
                ? $data['shipping_query']
                : new ShippingQuery($data['shipping_query']);
        }

        if (isset($data['pre_checkout_query'])) {
            $this->pre_checkout_query = $data['pre_checkout_query'] instanceof PreCheckoutQuery
                ? $data['pre_checkout_query']
                : new PreCheckoutQuery($data['pre_checkout_query']);
        }

        if (isset($data['poll'])) {
            $this->poll = $data['poll'] instanceof Poll
                ? $data['poll']
                : new Poll($data['poll']);
        }

        if (isset($data['poll_answer'])) {
            $this->poll_answer = $data['poll_answer'] instanceof PollAnswer
                ? $data['poll_answer']
                : new PollAnswer($data['poll_answer']);
        }
    }


    public static function make(int $update_id): self
    {
        return new self([
            'update_id' => $update_id,
        ]);
    }

    public static function checkAction(string $action): bool
    {
        return $action === self::ACT_MESSAGE ||
            $action === self::ACT_EDITED_MESSAGE ||
            $action === self::ACT_CHANNEL_POST ||
            $action === self::ACT_EDITED_CHANNEL_POST ||
            $action === self::ACT_INLINE_QUERY ||
            $action === self::ACT_CHOSEN_INLINE_RESULT ||
            $action === self::ACT_CALLBACK_QUERY ||
            $action === self::ACT_SHIPING_QUERY ||
            $action === self::ACT_PRE_CHECKOUT_QUERY ||
            $action === self::ACT_POLL ||
            $action === self::ACT_POLL_ANSWER;
    }


    public function getUser(): ?User
    {
        switch ($this->getAction()) {
            case self::ACT_PRE_CHECKOUT_QUERY:
                return $this->pre_checkout_query->from;
            case self::ACT_SHIPING_QUERY:
                return $this->shipping_query->from;
            case self::ACT_CALLBACK_QUERY:
                return $this->callback_query->from;
            case self::ACT_CHOSEN_INLINE_RESULT:
                return $this->chosen_inline_result->from;
            case self::ACT_INLINE_QUERY:
                return $this->inline_query->from;
            case self::ACT_EDITED_CHANNEL_POST:
                return $this->edited_channel_post->from;
            case self::ACT_CHANNEL_POST:
                return $this->channel_post->from;
            case self::ACT_EDITED_MESSAGE:
                return $this->edited_message->from;
            case self::ACT_MESSAGE:
                return $this->message->from;
            case self::ACT_POLL:
                return null;
            case self::ACT_POLL_ANSWER:
                return $this->poll_answer->user;
        }

        throw new Error("Unknown action: {$this->getAction()}");
    }

    public function getAction(): string
    {
        switch (true) {
            case $this->message !== null:
                return self::ACT_MESSAGE;
            case $this->edited_message !== null:
                return self::ACT_EDITED_MESSAGE;
            case $this->channel_post !== null:
                return self::ACT_CHANNEL_POST;
            case $this->edited_channel_post !== null:
                return self::ACT_EDITED_CHANNEL_POST;
            case $this->inline_query !== null:
                return self::ACT_INLINE_QUERY;
            case $this->chosen_inline_result !== null:
                return self::ACT_CHOSEN_INLINE_RESULT;
            case $this->callback_query !== null:
                return self::ACT_CALLBACK_QUERY;
            case $this->shipping_query !== null:
                return self::ACT_SHIPING_QUERY;
            case $this->pre_checkout_query !== null:
                return self::ACT_PRE_CHECKOUT_QUERY;
            case $this->poll !== null:
                return self::ACT_POLL;
            case $this->poll_answer !== null:
                return self::ACT_POLL_ANSWER;
        }

        throw new Error('Unknown action');
    }

    public function getChat(): ?Chat
    {
        switch ($this->getAction()) {
            case self::ACT_EDITED_CHANNEL_POST:
                return $this->edited_channel_post->chat;
            case self::ACT_CHANNEL_POST:
                return $this->channel_post->chat;
            case self::ACT_EDITED_MESSAGE:
                return $this->edited_message->chat;
            case self::ACT_MESSAGE:
                return $this->message->chat;
            case self::ACT_INLINE_QUERY:
            case self::ACT_POLL:
            case self::ACT_POLL_ANSWER:
            case self::ACT_PRE_CHECKOUT_QUERY:
            case self::ACT_SHIPING_QUERY:
            case self::ACT_CALLBACK_QUERY:
            case self::ACT_CHOSEN_INLINE_RESULT:
                return null;
        }

        throw new Error("Unknown action: {$this->getAction()}");
    }

    public function getStartCommand(): ?string
    {
        if ($this->isStart()) {
            return trim(mb_substr($this->message->text, $this->message->entities[0]->length));
        }
        return null;
    }


    public function isStart(): bool
    {
        return $this->message !== null && isset($this->message->entities[0])
            && $this->message->entities[0]->getType() === MessageEntity::TYPE_BOT_COMMAND
            && mb_strpos($this->message->text, '/start') === 0;
    }

    public function getRequestArray(): array
    {
        return [
            'update_id' => $this->update_id,
            'message' => $this->message,
            'edited_message' => $this->edited_message,
            'channel_post' => $this->channel_post,
            'edited_channel_post' => $this->edited_channel_post,
            'inline_query' => $this->inline_query,
            'chosen_inline_result' => $this->chosen_inline_result,
            'callback_query' => $this->callback_query,
            'shipping_query' => $this->shipping_query,
            'pre_checkout_query' => $this->pre_checkout_query,
            'poll' => $this->poll,
            'poll_answer' => $this->poll_answer,
        ];
    }
}
