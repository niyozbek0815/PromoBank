<?php

namespace App\Jobs;

use App\Imports\PromoCodeImport;
use App\Imports\VaucherImport;
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

class ImportVaucherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $createdByUserId;
    protected string $filePath;

    public function __construct( int $createdByUserId, string $filePath)
    {
        $this->createdByUserId = $createdByUserId;
        $this->filePath = $filePath;
    }

    public function handle(): void
    {
        DB::beginTransaction();

        try {
            $importFile = storage_path("app/public/{$this->filePath}");
            Excel::queueImport(new VaucherImport, $importFile);
            $updates = [];
            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error("❌ Promo importda xatolik", [
                'file' => $this->filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Queue tizimi xabardor bo‘lishi uchun
            throw $e;
        }
    }
}
