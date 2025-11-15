<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use App\Telegram\Services\Translator;
use Telegram\Bot\Laravel\Facades\Telegram;

class Refferral
{
    public function __construct(protected Translator $translator)
    {
    }

    /**
     * Referral xabarini yuborish
     */
    public function handle($update): void
    {
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        if (!$chatId)
            return;

        $referralLink = "https://t.me/promo_bank_bot?start={$chatId}";

        $text = $this->translator->get($chatId, 'refferral_text');

        // HTML tags to plain text
        $message = strip_tags(str_replace('::refferral_link', $referralLink, $text));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'disable_web_page_preview' => false,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        [
                            'text' => $this->translator->get($chatId, 'share_referral'),
                            'url' => "https://t.me/share/url?url={$referralLink}&text=" . urlencode($message)
                        ],
                    ],
                ],
            ]),
        ]);
    }

    /**
     * HTML xavfsiz formatlash (fallback)
     */
    protected function telegramSafeHtml(string $html): string
    {
        $html = str_replace(
            ['<h3>', '</h3>', '<h4>', '</h4>'],
            ["\n<b>", "</b>\n", "\n<b>", "</b>\n"],
            $html
        );

        $html = str_replace(['<ul>', '</ul>'], ["\n", "\n"], $html);
        $html = str_replace(['<li>', '</li>'], ["• ", "\n"], $html);
        $html = str_replace(['<p>', '</p>'], ["\n", "\n"], $html);
        $html = str_replace(['<em>', '</em>'], ['<i>', '</i>'], $html);
        $html = str_replace(['<strong>', '</strong>'], ['<b>', '</b>'], $html);

        $html = strip_tags($html, '<b><i><u><s><a><code><pre><blockquote>');
        return trim(preg_replace("/\n{3,}/", "\n\n", $html));
    }
}
