<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessSmsPromoJob implements ShouldQueue
{
    use Queueable;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }


    public function handle()
    {
        $phone = $this->data['phone'] ?? null;
        if (!$phone) {
            logger('Telefon raqam berilmagan');
            return;
        }

        $authClient = new AuthServiceClient();

        $user = $authClient->findUserByPhone($phone);

        if (!$user) {
            // User yo'q, yangi yaratamiz
            $newUserData = [
                'phone' => $phone,
                // boshqa kerakli ma'lumotlar
            ];
            $user = $authClient->createUser($newUserData);
            logger("Yangi user yaratildi: ", $user);
        } else {
            logger("Mavjud user topildi: ", $user);
        }
        logger('Promo code qabul qilindi: ', $this->data);


        // User bilan keyingi logika shu yerda bajariladi
    }
}
