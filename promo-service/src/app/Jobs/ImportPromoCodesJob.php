<?php

namespace App\Jobs;

use App\Imports\PromoCodeImport;
use App\Models\PromoGeneration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportPromoCodesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $promotionId;
    protected int $createdByUserId;
    protected string $filePath;
    protected bool $settings_rules;

    public function __construct(int $promotionId, int $createdByUserId, string $filePath, bool $settings_rules)
    {
        $this->promotionId = $promotionId;
        $this->createdByUserId = $createdByUserId;
        $this->filePath = $filePath;
        $this->settings_rules = $settings_rules;
    }

    public function handle(): void
    {
        DB::beginTransaction();

        try {
            // 🧾 1. Promo generation yaratish
            $generation = PromoGeneration::create([
                'promotion_id' => $this->promotionId,
                'type' => 'import',
                'created_by_user_id' => $this->createdByUserId,
            ]);

            // 📁 2. Fayl yo‘llarini aniqlash
            $filename = Str::after($this->filePath, 'promo-imports/');
            $storageDir = 'promo-imports/results';
            $insertedPath = "{$storageDir}/inserted/inserted_{$generation->id}_{$filename}.xlsx";
            $skippedPath = "{$storageDir}/skipped/skipped_{$generation->id}_{$filename}.xlsx";
            $importFile = storage_path("app/public/{$this->filePath}");

            // 📦 3. Importni bajarish
            $import = new PromoCodeImport(
              $generation,
               $this->settings_rules,
               $insertedPath,
               $skippedPath
            );

            Excel::import($import, $importFile);

            // 🧩 4. Faqat mavjud fayllarni DB ga update qilish
            $updates = [];

            foreach ([
                'import_result_inserted_file' => $insertedPath,
                'import_result_skipped_file' => $skippedPath,
            ] as $column => $path) {
                if (File::exists(storage_path("app/public/{$path}"))) {
                    $updates[$column] = $path;
                }
            }

            if ($updates) {
                $generation->update($updates);
            }

            DB::commit();

            Log::info("✅ Promo import muvaffaqiyatli yakunlandi", [
                'promotion_id' => $this->promotionId,
                'generation_id' => $generation->id,
                'inserted' => $updates['import_result_inserted_file'] ?? null,
                'skipped' => $updates['import_result_skipped_file'] ?? null,
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error("❌ Promo importda xatolik", [
                'promotion_id' => $this->promotionId,
                'file' => $this->filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Queue tizimi xabardor bo‘lishi uchun
            throw $e;
        }
    }
}
