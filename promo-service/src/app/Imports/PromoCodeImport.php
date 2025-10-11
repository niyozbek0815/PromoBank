<?php
namespace App\Imports;

use App\Models\PromoCode;
use App\Models\PromoGeneration;
use App\Models\PromotionSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PromoCodeImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected PromoGeneration $generation;
    protected int $promotionId;
    protected ?PromotionSetting $settings;
    protected bool $hasRules;
    protected string $insertedPath;
    protected string $skippedPath;

    protected array $validRows    = [];
    protected array $insertedRows = [];
    protected array $skippedRows  = [];
    protected int $chunkIndex     = 1;
    public function __construct(PromoGeneration $generation, bool $hasRules, string $insertedRelative, string $skippedRelative)
    {
        $this->generation   = $generation;
        $this->promotionId  = $generation->promotion_id;
        $this->hasRules     = $hasRules;
        $this->settings     = $hasRules ? PromotionSetting::where('promotion_id', $this->promotionId)->firstOrFail() : null;
        $this->insertedPath = storage_path('app/public/' . $insertedRelative);
        $this->skippedPath  = storage_path('app/public/' . $skippedRelative);

    }

    public function collection(Collection $rows): void
    {
        Log::info("📥 Processing chunk #{$this->chunkIndex}");

        $this->validRows    = [];
        $this->insertedRows = [];
        $this->skippedRows  = [];

        $seenInFile = [];
        $rawCodes   = [];

        foreach ($rows as $row) {
            $code = trim($row['promocode'] ?? '');

            if ($code === '') {
                $this->reject($code, 'Empty');
                continue;
            }

            if (in_array($code, $seenInFile)) {
                $this->reject($code, 'Duplicate in import file');
                continue;
            }

            if ($this->hasRules && ! $this->passesValidation($code)) {
                continue;
            }

            $seenInFile[] = $code;
            $rawCodes[]   = $code;
        }

        if (empty($rawCodes)) {
            $this->flushToExcel();
            $this->chunkIndex++; // 🔁 Keyingi chunk uchun
            return;
        }

        $existingCodesQuery = PromoCode::query()->whereIn('promocode', $rawCodes);

        if ($this->hasRules) {
            if (! $this->settings->unique_across_all_promotions) {
                $existingCodesQuery->where('promotion_id', $this->promotionId);
            }
        } else {
            $existingCodesQuery->where('promotion_id', $this->promotionId);
        }

        $existingCodes = $existingCodesQuery->pluck('promocode')->toArray();

        $existingMap = array_flip($existingCodes);

        foreach ($rawCodes as $code) {
            if (isset($existingMap[$code])) {
                $this->reject($code, 'Already exists in DB');
                continue;
            }

            $this->validRows[] = [
                'generation_id' => $this->generation->id,
                'promotion_id'  => $this->promotionId,
                'promocode'     => $code,
                'is_used'       => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];

            $this->insertedRows[] = ['promocode' => $code];
        }

        if (! empty($this->validRows)) {
            PromoCode::insert($this->validRows);
        }

        $this->flushToExcel();

        Log::info("✅ Finished chunk #{$this->chunkIndex}");
        $this->chunkIndex++; // 🔁 Keyingi chunk uchun
    }
    public function chunkSize(): int
    {
        return 1000;
    }

    protected function passesValidation(string $code): bool
    {
        // Log::info("Checking code: $code");
        if (strlen($code) !== $this->settings->length) {
            $this->reject($code, 'Length mismatch');
            return false;
        }

        $core = substr(
            $code,
            strlen($this->settings->prefix ?? ''),
            $this->settings->length - strlen($this->settings->prefix ?? '') - strlen($this->settings->suffix ?? '')
        );

        $charset = str_split($this->settings->charset);
        if ($this->settings->exclude_chars) {
            $charset = array_diff($charset, str_split($this->settings->exclude_chars));
        }

        if (strspn($core, implode('', $charset)) !== strlen($core)) {
            $this->reject($code, 'Invalid charset');
            return false;
        }

        return true;
    }

    protected function reject(string $code, string $reason): void
    {
        $this->skippedRows[] = ['promocode' => $code, 'reason' => $reason];
    }

    protected function flushToExcel(): void
    {
        if (! empty($this->insertedRows)) {
            $this->writeExcel($this->insertedPath, $this->insertedRows);
        }

        if (! empty($this->skippedRows)) {
            $this->writeExcel($this->skippedPath, $this->skippedRows);
        }
    }

    protected function writeExcel(string $filePath, array $rows): void
    {
        $exists      = file_exists($filePath);
        $spreadsheet = $exists ? \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath) : new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $startRow    = $sheet->getHighestRow() + 1;

        if (! $exists) {
            $sheet->fromArray(array_keys($rows[0]), null, 'A1');
        }

        foreach ($rows as $i => $row) {
            $sheet->fromArray(array_values($row), null, 'A' . ($startRow + $i));
        }

        if (! is_dir($dir = dirname($filePath))) {
            mkdir($dir, 0755, true);
        }

        (new Xlsx($spreadsheet))->save($filePath);
    }
}
