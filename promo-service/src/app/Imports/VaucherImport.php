<?php
namespace App\Imports;

use App\Models\OntvVaucher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;

class VaucherImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{

    use Importable, SkipsErrors;

    public function collection(Collection $rows)
    {
        $seen = [];
        $toInsert = [];

        foreach ($rows as $row) {
            $code = trim($row['vaucher'] ?? '');
            if ($code === '' || isset($seen[$code])) continue;
            $seen[$code] = true;

            $toInsert[] = [
                'code' => $code,
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($toInsert)) {
            $existing = OntvVaucher::whereIn('code', array_column($toInsert, 'code'))->pluck('code')->all();
            $filtered = array_filter($toInsert, fn($item) => !in_array($item['code'], $existing));

            if (!empty($filtered)) {
                OntvVaucher::insert(array_values($filtered));
            }
        }
    }

    public function chunkSize(): int
    {
        return 1000; // har bir chunk 1000 qator
    }
}
