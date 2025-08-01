<?php
namespace App\Jobs;

use App\Imports\PromoCodeImport;
use App\Models\PromoGeneration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ImportPromoCodesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $promotionId;
    protected int $createdByUserId;
    protected string $filePath;
    protected bool $settings_rules;

    public function __construct(int $promotionId, int $createdByUserId, string $filePath, bool $settings_rules)
    {
        $this->promotionId     = $promotionId;
        $this->createdByUserId = $createdByUserId;
        $this->filePath        = $filePath;
        $this->settings_rules  = $settings_rules;
    }
public function handle()
{
    Log::info("📥 [PromoImport] Started import", ['path' => $this->filePath]);

    $generation = PromoGeneration::create([
        'promotion_id'       => $this->promotionId,
        'type'               => 'import',
        'created_by_user_id' => $this->createdByUserId,
    ]);

    $filename = Str::after($this->filePath, 'promo-imports/');
    $storageDir = 'promo-imports/results';

    $insertedRelative = "{$storageDir}/inserted/inserted_{$generation->id}_{$filename}.xlsx";
    $skippedRelative  = "{$storageDir}/skipped/skipped_{$generation->id}_{$filename}.xlsx";

    $importFilePath = storage_path('app/public/' . $this->filePath);
    $import = new PromoCodeImport($generation, $this->settings_rules, $insertedRelative, $skippedRelative);

    Excel::import($import, $importFilePath);
    Log::info("📥 [PromoImport] Import completed", ['generation_id' => $generation->id]);

    $updates = [];

    if (File::exists(storage_path('app/public/' . $insertedRelative))) {
        $updates['import_result_inserted_file'] = $insertedRelative;
    }

    if (File::exists(storage_path('app/public/' . $skippedRelative))) {
        $updates['import_result_skipped_file'] = $skippedRelative;
    }

    if (!empty($updates)) {
        $generation->update($updates);
        Log::info("📁 [PromoImport] Result files attached", $updates);
    } else {
        Log::warning("⚠️ [PromoImport] No result files found");
    }

    Log::info("✅ [PromoImport] Finished");
}

}
