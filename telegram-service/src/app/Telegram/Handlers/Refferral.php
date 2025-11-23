<?php

namespace App\Telegram\Handlers;

use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;

class Refferral
{
    public function __construct(
        protected Translator $translator,
        protected SendMessages $sender
    ) {
    }

    public function handle($update): void
    {
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        if (!$chatId)
            return;

        $referralLink = "https://t.me/promo_bank_bot?start={$chatId}";

        $textTemplate = $this->translator->get($chatId, 'refferral_text');
        $text = strip_tags(str_replace('::refferral_link', $referralLink, $textTemplate));

        $shareUrl = "https://t.me/share/url?url={$referralLink}&text=" . urlencode($text);

        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => false,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        [
                            'text' => $this->translator->get($chatId, 'share_referral'),
                            'url' => $shareUrl
                        ]
                    ]
                ]
            ]),
        ]);
    }

    /**
     * HTML -> Telegram safe format (faqat kerak bo‘lsa)
     */
    protected function telegramSafeHtml(string $html): string
    {
        $map = [
            '<h3>' => "\n<b>",
            '</h3>' => "</b>\n",
            '<h4>' => "\n<b>",
            '</h4>' => "</b>\n",
            '<ul>' => "\n",
            '</ul>' => "\n",
            '<li>' => "• ",
            '</li>' => "\n",
            '<p>' => "\n",
            '</p>' => "\n",
            '<em>' => '<i>',
            '</em>' => '</i>',
            '<strong>' => '<b>',
            '</strong>' => '</b>',
        ];
        $html = str_replace(array_keys($map), array_values($map), $html);
        $html = strip_tags($html, '<b><i><u><s><a><code><pre><blockquote>');
        return trim(preg_replace("/\n{3,}/", "\n\n", $html));
    }
}
