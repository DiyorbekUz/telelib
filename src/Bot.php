<?php

declare(strict_types=1);

namespace DiyorbekUz\Telelib;

use DiyorbekUz\Telelib\Exceptions\Error;

/**
 * Class Bot
 *
 * @package DiyorbekUz\Telelib
 * @author DiyorbekDev
 */
class Bot
{
    public const PARSE_MODE_HTML = 'HTML';
    public const PARSE_MODE_MARKDOWN = 'Markdown';
    public const PARSE_MODE_MARKDOWN_V2 = 'MarkdownV2';
    public const PARSE_MODE_DEFAULT = self::PARSE_MODE_HTML;

    public const ACTION_TYPING = 'typing'; // for text messages
    public const ACTION_UPLOAD_PHOTO = 'upload_photo'; // for photos
    public const ACTION_RECORD_VIDEO = 'record_video'; // for videos
    public const ACTION_UPLOAD_VIDEO = 'upload_video'; // for videos
    public const ACTION_RECORD_AUDIO = 'record_audio'; // for audio files
    public const ACTION_UPLOAD_AUDIO = 'upload_audio'; // for audio files
    public const ACTION_UPLOAD_DOCUMENT = 'upload_document'; // for general files
    public const ACTION_FIND_LOCATION = 'find_location'; // for location data
    public const ACTION_RECORD_VIDEO_NOTE = 'record_video_note'; // for video notes
    public const ACTION_UPLOAD_VIDEO_NOTE = 'upload_video_note'; // for video notes

    /**
     * @var string
     */
    protected string $token;

    /**
     * @var Username
     */
    protected Username $username;

    /**
     * @var string
     */
    protected string $default_parse_mode = self::PARSE_MODE_DEFAULT;

    /**
     * Bot constructor.
     *
     * @param string $token
     * @param string $username
     */
    public function __construct(string $token, string $username)
    {
        $this->token = $token;
        $this->username = new Username($username);
    }

    /**
     * @param string $url
     * @param string $text
     * @param string $parse_mode
     * @param bool $filter
     * @return string
     */
    public static function genLink(string $url, string $text, string $parse_mode = self::PARSE_MODE_DEFAULT,
        bool $filter = false): string
    {
        if ($filter) {
            $text = self::filterstring($text, $parse_mode);
        }

        switch ($parse_mode) {
            case self::PARSE_MODE_HTML:
                return "<a href=\"$url\">$text</a>";

            case self::PARSE_MODE_MARKDOWN_V2:
            case self::PARSE_MODE_MARKDOWN:
                return "[$text]($url)";

        }

        throw new Error("Unknown parse mode: $parse_mode");
    }

    /**
     * @param string $text
     * @param string $parse_mode
     * @return string
     */
    public static function filterString(string $text, string $parse_mode = self::PARSE_MODE_DEFAULT): string
    {
        switch ($parse_mode) {
            case self::PARSE_MODE_HTML:
                return str_replace(['<', '>', '&', '"'], ['&lt;', '&gt;', '&amp;', '&quot;'], $text);

            case self::PARSE_MODE_MARKDOWN:
                return str_replace(['_', '*', '`', '['], ['\_', '\*', '\`', '\['], $text);

            case self::PARSE_MODE_MARKDOWN_V2:
                return str_replace(
                    ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+',
                        '-', '=', '|', '{', '}', '.', '!',],
                    ['\_', '\*', '\[', '\]', '\(', '\)', '\~', '\`', '\>', '\#', '\+',
                        '\-', '\=', '\|', '\{', '\}', '\.', '\!',],
                    $text);

        }

        throw new Error("Unknown parse mode: {$parse_mode}");
    }

    /**
     * @param string $action
     * @return bool
     */
    public static function checkAction(string $action): bool
    {
        return $action === self::ACTION_TYPING ||
            $action === self::ACTION_UPLOAD_PHOTO ||
            $action === self::ACTION_RECORD_VIDEO ||
            $action === self::ACTION_UPLOAD_VIDEO ||
            $action === self::ACTION_RECORD_AUDIO ||
            $action === self::ACTION_UPLOAD_AUDIO ||
            $action === self::ACTION_UPLOAD_DOCUMENT ||
            $action === self::ACTION_FIND_LOCATION ||
            $action === self::ACTION_RECORD_VIDEO_NOTE ||
            $action === self::ACTION_UPLOAD_VIDEO_NOTE;
    }

    /**
     * @return string
     */
    public function getDefaultParseMode(): string
    {
        return $this->default_parse_mode;
    }

    /**
     * @param string $parse_mode
     * @return $this
     */
    public function setDefaultParseMode(string $parse_mode): self
    {
        if (!self::checkParseMode($parse_mode)) {
            throw new Error("Unknown parse mode: {$parse_mode}");
        }
        $this->default_parse_mode = $parse_mode;
        return $this;
    }

    /**
     * @param string $parse_mode
     * @return bool
     */
    public static function checkParseMode(string $parse_mode): bool
    {
        return $parse_mode === self::PARSE_MODE_HTML ||
            $parse_mode === self::PARSE_MODE_MARKDOWN ||
            $parse_mode === self::PARSE_MODE_MARKDOWN_V2;
    }

    /**
     * Getting bot ID from token
     *
     * @return int
     */
    public function getId(): int
    {
        return (int)substr($this->token, 0, strpos($this->token, ':'));
    }

    /**
     * Getting bot token
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Getting bot username
     *
     * @return Username
     */
    public function getUsername(): Username
    {
        return $this->username;
    }

    /**
     * @param string|null $start_command
     * @param bool|null $start_group
     * @param string $url_format
     * @return string
     */
    public function getBotUrl(string $start_command = null, bool $start_group = null,
        string $url_format = Username::URL_FORMAT_DEFAULT): string
    {
        $get_params = [];

        if ($start_command !== null) {
            $get_params['start'] = $start_command;
        }

        if ($start_group) {
            $get_params['startgroup'] = 'true';
        }

        return $this->username->getUrl($url_format) .
            (empty($get_params) ? '' : '?' . http_build_query($get_params));
    }



    public function getUpdates(int $offset = null, int $limit = null, int $timeout = null,
        array $allowed_updates = null): Requests\GetUpdates
    {
        return new Requests\GetUpdates($this->token, [
            'offset' => $offset,
            'limit' => $limit,
            'timeout' => $timeout,
            'allowed_updates' => $allowed_updates,
        ]);
    }

    public function setWebhook(string $url, string $certificate = null, int $max_connections = null,
        array $allowed_updates = null): Requests\SetWebhook
    {
        return new Requests\SetWebhook($this->token, [
            'url' => $url,
            'certificate' => $certificate,
            'max_connections' => $max_connections,
            'allowed_updates' => $allowed_updates,
        ]);
    }


    public function deleteWebhook(): Requests\DeleteWebhook
    {
        return new Requests\DeleteWebhook($this->token);
    }

    public function getWebhookInfo(): Requests\GetWebhookInfo
    {
        return new Requests\GetWebhookInfo($this->token);
    }

    public function getMe(): Requests\GetMe
    {
        return new Requests\GetMe($this->token);
    }


    public function sendMessage($chat_id, string $text): Requests\SendMessage
    {
        return (new Requests\SendMessage($this->token, [
            'chat_id' => $chat_id,
            'text' => $text,
        ]))->setParseMode($this->default_parse_mode);
    }


    public function forwardMessage($chat_id, $from_chat_id, int $message_id): Requests\ForwardMessage
    {
        return new Requests\ForwardMessage($this->token, [
            'chat_id' => $chat_id,
            'from_chat_id' => $from_chat_id,
            'message_id' => $message_id,
        ]);
    }


    public function sendPhoto($chat_id, string $photo, string $caption = null): Requests\SendPhoto
    {
        return (new Requests\SendPhoto($this->token, [
            'chat_id' => $chat_id,
            'photo' => $photo,
            'caption' => $caption,
        ]))->setParseMode($this->default_parse_mode);
    }

    public function sendAudio($chat_id, string $audio, string $caption = null, int $duration = null,
        string $performer = null, string $title = null, string $thumb = null): Requests\SendAudio
    {
        return (new Requests\SendAudio($this->token, [
            'chat_id' => $chat_id,
            'audio' => $audio,
            'caption' => $caption,
            'duration' => $duration,
            'performer' => $performer,
            'title' => $title,
            'thumb' => $thumb,
        ]))->setParseMode($this->default_parse_mode);
    }

    public function sendDocument($chat_id, string $document, string $caption = null,
        string $thumb = null): Requests\SendDocument
    {
        return (new Requests\SendDocument($this->token, [
            'chat_id' => $chat_id,
            'document' => $document,
            'thumb' => $thumb,
            'caption' => $caption,
        ]))->setParseMode($this->default_parse_mode);
    }

    public function sendVideo($chat_id, string $video, string $caption = null, int $duration = null,
        int $width = null, int $height = null, string $thumb = null,
        bool $supports_streaming = null): Requests\SendVideo
    {
        return (new Requests\SendVideo($this->token, [
            'chat_id' => $chat_id,
            'video' => $video,
            'duration' => $duration,
            'width' => $width,
            'height' => $height,
            'thumb' => $thumb,
            'caption' => $caption,
            'supports_streaming' => $supports_streaming,
        ]))->setParseMode($this->default_parse_mode);
    }

    public function sendAnimation($chat_id, string $animation, string $caption = null, int $duration = null,
        int $width = null, int $height = null, string $thumb = null): Requests\SendAnimation
    {
        return (new Requests\SendAnimation($this->token, [
            'chat_id' => $chat_id,
            'animation' => $animation,
            'duration' => $duration,
            'width' => $width,
            'height' => $height,
            'thumb' => $thumb,
            'caption' => $caption,
        ]))->setParseMode($this->default_parse_mode);
    }

    public function sendVoice($chat_id, string $voice, string $caption = null,
        int $duration = null): Requests\SendVoice
    {
        return (new Requests\SendVoice($this->token, [
            'chat_id' => $chat_id,
            'voice' => $voice,
            'caption' => $caption,
            'duration' => $duration,
        ]))->setParseMode($this->default_parse_mode);
    }

    public function sendVideoNote($chat_id, string $video_note, int $duration = null, int $length = null,
        string $thumb = null): Requests\SendVideoNote
    {
        return new Requests\SendVideoNote($this->token, [
            'chat_id' => $chat_id,
            'video_note' => $video_note,
            'duration' => $duration,
            'length' => $length,
            'thumb' => $thumb,
        ]);
    }

    public function sendMediaGroup($chat_id, array $media): Requests\SendMediaGroup
    {
        return new Requests\SendMediaGroup($this->token, [
            'chat_id' => $chat_id,
            'media' => $media,
        ]);
    }


    public function sendLocation($chat_id, float $latitude, float $longitude,
        int $live_period = null): Requests\SendLocation
    {
        return new Requests\SendLocation($this->token, [
            'chat_id' => $chat_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'live_period' => $live_period,
        ]);
    }


    public function editMessageLiveLocation($chat_id, int $message_id, float $latitude,
        float $longitude): Requests\EditMessageLiveLocation
    {
        return new Requests\EditMessageLiveLocation($this->token, [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    public function editMessageLiveLocationInline(string $inline_message_id, float $latitude,
        float $longitude): Requests\EditMessageLiveLocation
    {
        return new Requests\EditMessageLiveLocation($this->token, [
            'inline_message_id' => $inline_message_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    public function stopMessageLiveLocation($chat_id, int $message_id): Requests\StopMessageLiveLocation
    {
        return new Requests\StopMessageLiveLocation($this->token, [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
        ]);
    }


    public function stopMessageLiveLocationInline(string $inline_message_id): Requests\StopMessageLiveLocation
    {
        return new Requests\StopMessageLiveLocation($this->token, [
            'inline_message_id' => $inline_message_id,
        ]);
    }


    public function sendVenue($chat_id, float $latitude, float $longitude, string $title, string $address,
        string $foursquare_id = null, string $foursquare_type = null): Requests\SendVenue
    {
        return new Requests\SendVenue($this->token, [
            'chat_id' => $chat_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'title' => $title,
            'address' => $address,
            'foursquare_id' => $foursquare_id,
            'foursquare_type' => $foursquare_type,
        ]);
    }


    public function sendContact($chat_id, string $phone_number, string $first_name, string $last_name = null,
        string $vcard = null): Requests\SendContact
    {
        return new Requests\SendContact($this->token, [
            'chat_id' => $chat_id,
            'phone_number' => $phone_number,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'vcard' => $vcard,
        ]);
    }


    public function sendPoll($chat_id, string $question, array $options, bool $is_anonymous = null,
        string $type = null, bool $allows_multiple_answers = null, int $correct_option_id = null,
        string $explanation = null, string $explanation_parse_mode = null, int $open_period = null,
        int $close_date = null, bool $is_closed = null): Requests\SendPoll
    {
        if (!Types\Poll::checkType($type)) {
            throw new Error("Unknown poll type: {$type} (must be self::POLL_TYPE_*");
        }

        return new Requests\SendPoll($this->token, [
            'chat_id' => $chat_id,
            'question' => $question,
            'options' => $options,
            'is_anonymous' => $is_anonymous,
            'allows_multiple_answers' => $allows_multiple_answers,
            'correct_option_id' => $correct_option_id,
            'explanation' => $explanation,
            'explanation_parse_mode' => $explanation_parse_mode,
            'open_period' => $open_period,
            'close_date' => $close_date,
            'is_closed' => $is_closed,
        ]);
    }

    public function sendDice($chat_id, string $emoji = null): Requests\SendDice
    {
        if ($emoji !== null && !Types\Dice::checkEmoji($emoji)) {
            throw new Error("Unknown dice emoji: $emoji");
        }
        return new Requests\SendDice($this->token, [
            'chat_id' => $chat_id,
            'emoji' => $emoji,
        ]);
    }


    public function sendChatAction($chat_id, string $action): Requests\SendChatAction
    {
        return new Requests\SendChatAction($this->token, [
            'chat_id' => $chat_id,
            'action' => $action,
        ]);
    }


    public function getUserProfilePhotos(int $user_id, int $offset = null,
        int $limit = null): Requests\GetUserProfilePhotos
    {
        return new Requests\GetUserProfilePhotos($this->token, [
            'user_id' => $user_id,
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }


    public function getFile(string $file_id): Requests\GetFile
    {
        return new Requests\GetFile($this->token, [
            'file_id' => $file_id,
        ]);
    }

    public function kickChatMember($chat_id, int $user_id, int $until_date = null): Requests\KickChatMember
    {
        return new Requests\KickChatMember($this->token, [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
            'until_date' => $until_date,
        ]);
    }


    public function unbanChatMember($chat_id, int $user_id): Requests\UnbanChatMember
    {
        return new Requests\UnbanChatMember($this->token, [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
        ]);
    }


    public function restrictChatMember($chat_id, int $user_id, Types\ChatPermissions $permissions,
        int $until_date = null): Requests\RestrictChatMember
    {
        return new Requests\RestrictChatMember($this->token, [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
            'permissions' => $permissions,
            'until_date' => $until_date,
        ]);
    }

    public function promoteChatMember($chat_id, int $user_id,
        Types\AdministratorPermissions $permissions): Requests\PromoteChatMember
    {
        return new Requests\PromoteChatMember($this->token, array_merge([
            'chat_id' => $chat_id,
            'user_id' => $user_id,
        ], $permissions->getRequestArray()));
    }


    public function setChatAdministratorCustomTitle($chat_id, int $user_id,
        string $custom_title): Requests\SetChatAdministratorCustomTitle
    {
        return new Requests\SetChatAdministratorCustomTitle($this->token, [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
            'custom_title' => $custom_title,
        ]);
    }


    public function setChatPermissions($chat_id, Types\ChatPermissions $permissions): Requests\SetChatPermissions
    {
        return new Requests\SetChatPermissions($this->token, [
            'chat_id' => $chat_id,
            'permissions' => $permissions,
        ]);
    }


    public function exportChatInviteLink($chat_id): Requests\ExportChatInviteLink
    {
        return new Requests\ExportChatInviteLink($this->token, [
            'chat_id' => $chat_id,
        ]);
    }


    public function setChatPhoto($chat_id, string $photo): Requests\SetChatPhoto
    {
        return new Requests\SetChatPhoto($this->token, [
            'chat_id' => $chat_id,
            'photo' => $photo,
        ]);
    }

    public function deleteChatPhoto($chat_id): Requests\DeleteChatPhoto
    {
        return new Requests\DeleteChatPhoto($this->token, [
            'chat_id' => $chat_id,
        ]);
    }


    public function setChatTitle($chat_id, string $title): Requests\SetChatTitle
    {
        return new Requests\SetChatTitle($this->token, [
            'chat_id' => $chat_id,
            'title' => $title,
        ]);
    }


    public function setChatDescription($chat_id, string $description = null): Requests\SetChatDescription
    {
        return new Requests\SetChatDescription($this->token, [
            'chat_id' => $chat_id,
            'description' => $description,
        ]);
    }


    public function pinChatMessage($chat_id, int $message_id): Requests\PinChatMessage
    {
        return new Requests\PinChatMessage($this->token, [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
        ]);
    }


    public function unpinChatMessage($chat_id): Requests\UnpinChatMessage
    {
        return new Requests\UnpinChatMessage($this->token, [
            'chat_id' => $chat_id,
        ]);
    }


    public function leaveChat($chat_id): Requests\LeaveChat
    {
        return new Requests\LeaveChat($this->token, [
            'chat_id' => $chat_id,
        ]);
    }

    public function getChat($chat_id): Requests\GetChat
    {
        return new Requests\GetChat($this->token, [
            'chat_id' => $chat_id,
        ]);
    }

    public function getChatAdministrators($chat_id): Requests\GetChatAdministrators
    {
        return new Requests\GetChatAdministrators($this->token, [
            'chat_id' => $chat_id,
        ]);
    }

    public function getChatMembersCount($chat_id): Requests\GetChatMembersCount
    {
        return new Requests\GetChatMembersCount($this->token, [
            'chat_id' => $chat_id,
        ]);
    }

    public function getChatMember($chat_id, int $user_id): Requests\GetChatMember
    {
        return new Requests\GetChatMember($this->token, [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
        ]);
    }


    public function setChatStickerSet($chat_id, string $sticker_set_name): Requests\SetChatStickerSet
    {
        return new Requests\SetChatStickerSet($this->token, [
            'chat_id' => $chat_id,
            'sticker_set_name' => $sticker_set_name,
        ]);
    }


    public function deleteChatStickerSet($chat_id): Requests\DeleteChatStickerSet
    {
        return new Requests\DeleteChatStickerSet($this->token, [
            'chat_id' => $chat_id,
        ]);
    }

    public function answerCallbackQuery(string $callback_query_id, string $text = null, bool $show_alert = null,
        string $url = null, int $cache_time = null): Requests\AnswerCallbackQuery
    {
        return new Requests\AnswerCallbackQuery($this->token, [
            'callback_query_id' => $callback_query_id,
            'text' => $text,
            'show_alert' => $show_alert,
            'url' => $url,
            'cache_time' => $cache_time,
        ]);
    }

  
    public function setMyCommands(array $commands): Requests\SetMyCommands
    {
        return new Requests\SetMyCommands($this->token, [
            'commands' => $commands,
        ]);
    }


    public function getMyCommands(): Requests\GetMyCommands
    {
        return new Requests\GetMyCommands($this->token);
    }

    public function editMessageText($chat_id, int $message_id, string $text): Requests\EditMessageText
    {
        return (new Requests\EditMessageText($this->token, [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
        ]))->setParseMode($this->default_parse_mode);
    }

    public function editMessageTextInline(string $inline_message_id, string $text): Requests\EditMessageText
    {
        return (new Requests\EditMessageText($this->token, [
            'inline_message_id' => $inline_message_id,
            'text' => $text,
        ]))->setParseMode($this->default_parse_mode);
    }

    public function editMessageCaption($chat_id, int $message_id, string $caption = null): Requests\EditMessageCaption
    {
        return (new Requests\EditMessageCaption($this->token, [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'caption' => $caption,
        ]))->setParseMode($this->default_parse_mode);
    }


    public function editMessageCaptionInline(string $inline_message_id,
        string $caption = null): Requests\EditMessageCaption
    {
        return (new Requests\EditMessageCaption($this->token, [
            'inline_message_id' => $inline_message_id,
            'caption' => $caption,
        ]))->setParseMode($this->default_parse_mode);
    }


    public function editMessageMedia($chat_id, int $message_id, Types\InputMedia $media): Requests\EditMessageMedia
    {
        return new Requests\EditMessageMedia($this->token, [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'media' => $media,
        ]);
    }


    public function editMessageMediaInline(string $inline_message_id,
        Types\InputMedia $media): Requests\EditMessageMedia
    {
        return new Requests\EditMessageMedia($this->token, [
            'inline_message_id' => $inline_message_id,
            'media' => $media,
        ]);
    }


    public function editMessageReplyMarkup($chat_id, int $message_id): Requests\EditMessageReplyMarkup
    {
        return new Requests\EditMessageReplyMarkup($this->token, [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
        ]);
    }


    public function editMessageReplyMarkupInline(string $inline_message_id): Requests\EditMessageReplyMarkup
    {
        return new Requests\EditMessageReplyMarkup($this->token, [
            'inline_message_id' => $inline_message_id,
        ]);
    }


    public function stopPoll($chat_id, int $message_id): Requests\StopPoll
    {
        return new Requests\StopPoll($this->token, [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
        ]);
    }

    public function deleteMessage($chat_id, int $message_id): Requests\DeleteMessage
    {
        return new Requests\DeleteMessage($this->token, [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
        ]);
    }

    public function sendSticker($chat_id, string $sticker): Requests\SendSticker
    {
        return new Requests\SendSticker($this->token, [
            'chat_id' => $chat_id,
            'sticker' => $sticker,
        ]);
    }

    public function getStickerSet(string $name): Requests\GetStickerSet
    {
        return new Requests\GetStickerSet($this->token, [
            'name' => $name,
        ]);
    }


    public function uploadStickerFile(int $user_id, string $png_sticker): Requests\UploadStickerFile
    {
        return new Requests\UploadStickerFile($this->token, [
            'user_id' => $user_id,
            'png_sticker' => $png_sticker,
        ]);
    }


    public function createNewStickerSet(int $user_id, string $name, string $title, ?string $png_sticker,
        ?string $tgs_sticker, string $emojis, bool $contains_masks = null,
        Types\MaskPosition $mask_position = null): Requests\CreateNewStickerSet
    {
        return new Requests\CreateNewStickerSet($this->token, [
            'user_id' => $user_id,
            'name' => $name,
            'title' => $title,
            'png_sticker' => $png_sticker,
            'tgs_sticker' => $tgs_sticker,
            'emojis' => $emojis,
            'contains_masks' => $contains_masks,
            'mask_position' => $mask_position,
        ]);
    }


    public function addStickerToSet(int $user_id, string $name, ?string $png_sticker, ?string $tgs_sticker,
        string $emojis, Types\MaskPosition $mask_position = null): Requests\AddStickerToSet
    {
        return new Requests\AddStickerToSet($this->token, [
            'user_id' => $user_id,
            'name' => $name,
            'png_sticker' => $png_sticker,
            'tgs_sticker' => $tgs_sticker,
            'emojis' => $emojis,
            'mask_position' => $mask_position,
        ]);
    }

  
    public function setStickerPositionInSet(string $sticker, int $position): Requests\SetStickerPositionInSet
    {
        return new Requests\SetStickerPositionInSet($this->token, [
            'sticker' => $sticker,
            'position' => $position,
        ]);
    }

    public function deleteStickerFromSet(string $sticker): Requests\DeleteStickerFromSet
    {
        return new Requests\DeleteStickerFromSet($this->token, [
            'sticker' => $sticker,
        ]);
    }

    public function setStickerSetThumb(string $name, int $user_id, string $thumb = null): Requests\SetStickerSetThumb
    {
        return new Requests\SetStickerSetThumb($this->token, [
            'name' => $name,
            'user_id' => $user_id,
            'thumb' => $thumb,
        ]);
    }


    public function answerInlineQuery(string $inline_query_id, array $results, int $cache_time = null,
        bool $is_personal = null, string $next_offset = null, string $switch_pm_text = null,
        string $switch_pm_parameter = null): Requests\AnswerInlineQuery
    {
        return new Requests\AnswerInlineQuery($this->token, [
            'inline_query_id' => $inline_query_id,
            'results' => $results,
            'cache_time' => $cache_time,
            'is_personal' => $is_personal,
            'next_offset' => $next_offset,
            'switch_pm_text' => $switch_pm_text,
            'switch_pm_parameter' => $switch_pm_parameter,
        ]);
    }

   
    public function sendInvoice(int $chat_id, string $title, string $description, string $payload,
        string $provider_token, string $start_parameter, string $currency, array $prices,
        string $provider_data = null, string $photo_url = null, int $photo_size = null,
        int $photo_width = null, int $photo_height = null, bool $need_name = null,
        bool $need_phone_number = null, bool $need_email = null, bool $need_shipping_address = null,
        bool $send_phone_number_to_provider = null, bool $send_email_to_provider = null,
        bool $is_flexible = null): Requests\SendInvoice
    {
        return new Requests\SendInvoice($this->token, [
            'chat_id' => $chat_id,
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
            'provider_token' => $provider_token,
            'start_parameter' => $start_parameter,
            'currency' => $currency,
            'prices' => $prices,
            'provider_data' => $provider_data,
            'photo_url' => $photo_url,
            'photo_size' => $photo_size,
            'photo_width' => $photo_width,
            'photo_height' => $photo_height,
            'need_name' => $need_name,
            'need_phone_number' => $need_phone_number,
            'need_email' => $need_email,
            'need_shipping_address' => $need_shipping_address,
            'send_phone_number_to_provider' => $send_phone_number_to_provider,
            'send_email_to_provider' => $send_email_to_provider,
            'is_flexible' => $is_flexible,
        ]);
    }

    
    public function answerShippingQuery(string $shipping_query_id, bool $ok, array $shipping_options = null,
        string $error_message = null): Requests\AnswerShippingQuery
    {
        return new Requests\AnswerShippingQuery($this->token, [
            'shipping_query_id' => $shipping_query_id,
            'ok' => $ok,
            'shipping_options' => $shipping_options,
            'error_message' => $error_message,
        ]);
    }

   
    public function answerPreCheckoutQuery(string $pre_checkout_query_id, bool $ok,
        string $error_message = null): Requests\AnswerPreCheckoutQuery
    {
        return new Requests\AnswerPreCheckoutQuery($this->token, [
            'pre_checkout_query_id' => $pre_checkout_query_id,
            'ok' => $ok,
            'error_message' => $error_message,
        ]);
    }


    public function setPassportDataErrors(int $user_id, array $errors): Requests\SetPassportDataErrors
    {
        return new Requests\SetPassportDataErrors($this->token, [
            'user_id' => $user_id,
            'errors' => $errors,
        ]);
    }

   
    public function sendGame(int $chat_id, string $game_short_name): Requests\SendGame
    {
        return new Requests\SendGame($this->token, [
            'chat_id' => $chat_id,
            'game_short_name' => $game_short_name,
        ]);
    }

   
    public function setGameScore(int $chat_id, int $message_id, int $user_id, int $score, bool $force = null,
        bool $disable_edit_message = null): Requests\SetGameScore
    {
        return new Requests\SetGameScore($this->token, [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'user_id' => $user_id,
            'score' => $score,
            'force' => $force,
            'disable_edit_message' => $disable_edit_message,
        ]);
    }

   
    public function setGameScoreInline(string $inline_message_id, int $user_id, int $score, bool $force = null,
        bool $disable_edit_message = null): Requests\SetGameScore
    {
        return new Requests\SetGameScore($this->token, [
            'inline_message_id' => $inline_message_id,
            'user_id' => $user_id,
            'score' => $score,
            'force' => $force,
            'disable_edit_message' => $disable_edit_message,
        ]);
    }

    
    public function getGameHighScores(int $user_id, int $chat_id, int $message_id): Requests\GetGameHighScores
    {
        return new Requests\GetGameHighScores($this->token, [
            'user_id' => $user_id,
            'chat_id' => $chat_id,
            'message_id' => $message_id,
        ]);
    }

   
    public function getGameHighScoresInline(int $user_id,
        string $inline_message_id): Requests\GetGameHighScores
    {
        return new Requests\GetGameHighScores($this->token, [
            'user_id' => $user_id,
            'inline_message_id' => $inline_message_id,
        ]);
    }
}
