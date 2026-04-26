<?php

namespace App\Filament\Pages;

use App\Ai\Agents\TopsisAgent;
use App\Filament\Widgets\TopsisRankChart;
use App\Models\Alternative;
use App\Models\Calculation;
use App\Models\Criteria;
use App\Models\Result;
use App\Models\Score;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class Topsis extends Page
{
    protected string $view = 'filament.pages.topsis';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calculator;
    protected static ?int $navigationSort = 4;
    protected static string | UnitEnum | null $navigationGroup = 'Perhitungan';
    protected static ?string $navigationLabel = 'TOPSIS';

    public $calculation_id = null;
    public $calculations = [];

    public $criteria = [];
    public $alternatives = [];
    public $scores = [];

    public $normalizedMatrix = [];
    public $weightedMatrix = [];
    public $idealPositive = [];
    public $idealNegative = [];
    public $distancePositive = [];
    public $distanceNegative = [];

    public $results = [];
    public $aiConclusion = null;
    public $disabledHitung = true;
    public function mount(): void
    {
        $this->calculations = Calculation::orderBy('name')->get()->toArray();
    }

    public function updatedCalculationId()
    {
        $this->loadData();
    }

    public function loadData()
    {
        if (!$this->calculation_id) {
            $this->criteria = [];
            $this->alternatives = [];
            $this->scores = [];
            $this->results = [];
            $this->normalizedMatrix = [];
            $this->weightedMatrix = [];
            $this->idealPositive = [];
            $this->idealNegative = [];
            $this->distancePositive = [];
            $this->distanceNegative = [];
            $this->aiConclusion = null;

            return;
        }

        $this->criteria = Criteria::where('calculation_id', $this->calculation_id)->get()->toArray();
        $this->alternatives = Alternative::where('calculation_id', $this->calculation_id)->get()->toArray();

        // load scores
        $scores = Score::where('calculation_id', $this->calculation_id)->get();

        $matrix = [];

        foreach ($scores as $s) {
            $matrix[$s->alternative_id][$s->criteria_id] = $s->value;
        }

        $this->scores = $matrix;
        $this->results = [];
        $this->normalizedMatrix = [];
        $this->weightedMatrix = [];
        $this->idealPositive = [];
        $this->idealNegative = [];
        $this->distancePositive = [];
        $this->distanceNegative = [];
        $this->aiConclusion = null;
    }

    public function hitung()
    {
        $criteria = collect($this->criteria);
        $alternatives = collect($this->alternatives);

        $nCriteria = $criteria->count();
        $nAlt = $alternatives->count();

        // 🔹 1. Normalisasi
        $norm = [];

        for ($j = 0; $j < $nCriteria; $j++) {
            $sum = 0;

            for ($i = 0; $i < $nAlt; $i++) {
                $value = $this->getScore($i, $j);
                $sum += pow($value, 2);
            }

            $sqrt = sqrt($sum);

            for ($i = 0; $i < $nAlt; $i++) {
                $value = $this->getScore($i, $j);
                $norm[$i][$j] = $value / ($sqrt ?: 1);
            }
        }

        // 🔹 2. Normalisasi bobot
        $totalWeight = $criteria->sum('weight');

        $weights = $criteria->map(fn($c) => $c['weight'] / ($totalWeight ?: 1))->values();

        // 🔹 3. Matriks terbobot
        $y = [];

        for ($i = 0; $i < $nAlt; $i++) {
            for ($j = 0; $j < $nCriteria; $j++) {
                $y[$i][$j] = $norm[$i][$j] * $weights[$j];
            }
        }

        // 🔹 4. Solusi ideal
        $idealPos = [];
        $idealNeg = [];

        for ($j = 0; $j < $nCriteria; $j++) {
            $col = array_column($y, $j);

            if ($criteria[$j]['type'] === 'benefit') {
                $idealPos[$j] = max($col);
                $idealNeg[$j] = min($col);
            } else {
                $idealPos[$j] = min($col);
                $idealNeg[$j] = max($col);
            }
        }

        // 🔹 5. Jarak
        $dPos = [];
        $dNeg = [];

        for ($i = 0; $i < $nAlt; $i++) {
            $sumPos = 0;
            $sumNeg = 0;

            for ($j = 0; $j < $nCriteria; $j++) {
                $sumPos += pow($y[$i][$j] - $idealPos[$j], 2);
                $sumNeg += pow($y[$i][$j] - $idealNeg[$j], 2);
            }

            $dPos[$i] = sqrt($sumPos);
            $dNeg[$i] = sqrt($sumNeg);
        }

        // 🔹 6. Nilai preferensi
        $results = [];

        foreach ($alternatives as $i => $alt) {
            $denominator = ($dPos[$i] + $dNeg[$i]) ?: 1;
            $score = $dNeg[$i] / $denominator;

            $results[] = [
                'id' => $alt['id'],
                'name' => $alt['name'],
                'd_plus' => $dPos[$i],
                'd_minus' => $dNeg[$i],
                'score' => $score,
            ];
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        // 🔹 Simpan hasil
        $this->normalizedMatrix = $norm;
        $this->weightedMatrix = $y;
        $this->idealPositive = $idealPos;
        $this->idealNegative = $idealNeg;
        $this->distancePositive = $dPos;
        $this->distanceNegative = $dNeg;
        $this->aiConclusion = null;

        DB::transaction(function () use ($results) {
            Result::where('calculation_id', $this->calculation_id)->delete();

            foreach ($results as $rank => $r) {
                Result::create([
                    'calculation_id' => $this->calculation_id,
                    'alternative_id' => $r['id'],
                    'score' => $r['score'],
                    'rank' => $rank + 1,
                ]);
            }
        });

        $this->results = $results;
    }

    public function generateConclusion()
    {
        if (empty($this->results)) {
            $this->aiConclusion = 'Silakan jalankan perhitungan TOPSIS terlebih dahulu sebelum meminta kesimpulan AI.';

            return;
        }

        $calculation = collect($this->calculations)
            ->firstWhere('id', $this->calculation_id) ?? [];

        $criteria = collect($this->criteria)->map(function (array $crit) {
            return [
                'id' => $crit['id'],
                'code' => $crit['code'],
                'name' => $crit['name'],
                'weight' => $crit['weight'],
                'type' => $crit['type'],
            ];
        })->values()->all();

        $alternatives = collect($this->alternatives)->map(function (array $alt) {
            return [
                'id' => $alt['id'],
                'code' => $alt['code'],
                'name' => $alt['name'],
            ];
        })->values()->all();

        $results = collect($this->results)->values()->map(function (array $result, int $index) {
            return [
                'rank' => $index + 1,
                'id' => $result['id'],
                'name' => $result['name'],
                'score' => $result['score'],
                'd_plus' => $result['d_plus'],
                'd_minus' => $result['d_minus'],
            ];
        })->all();

        $matrices = [
            'normalized' => $this->normalizedMatrix,
            'weighted' => $this->weightedMatrix,
            'ideal_positive' => $this->idealPositive,
            'ideal_negative' => $this->idealNegative,
            'distance_positive' => $this->distancePositive,
            'distance_negative' => $this->distanceNegative,
        ];

        try {
            $response = TopsisAgent::make()
                ->conclude(
                    calculation: $calculation,
                    criteria: $criteria,
                    alternatives: $alternatives,
                    results: $results,
                    matrices: $matrices,
                );

            $this->aiConclusion = (string) $response;
        } catch (\Throwable $e) {
            report($e);

            $this->aiConclusion = 'Gagal menghasilkan kesimpulan AI. Silakan cek kembali konfigurasi Gemini API key dan model yang dipakai.';
        }
    }

    private function getScore($i, $j)
    {
        $altId = $this->alternatives[$i]['id'];
        $critId = $this->criteria[$j]['id'];

        return $this->scores[$altId][$critId] ?? 0;
    }

    public function saveScores()
    {
        DB::transaction(function () {
            foreach ($this->alternatives as $alt) {
                foreach ($this->criteria as $crit) {

                    $value = $this->scores[$alt['id']][$crit['id']] ?? 0;

                    Score::updateOrCreate(
                        [
                            'calculation_id' => $this->calculation_id,
                            'alternative_id' => $alt['id'],
                            'criteria_id' => $crit['id'],
                        ],
                        [
                            'value' => $value
                        ]
                    );
                }
            }
        });

        $this->disabledHitung = false;
    }



    public function getWidgetData(): array
    {
        return [
            'stats' => [
                'calculation_id' => $this->calculation_id,
            ],
        ];
    }

    protected function getFooterWidgets(): array
    {
        if (!$this->calculation_id) {
            return []; // ❌ jangan tampilkan widget
        }

        return [
            TopsisRankChart::make([
                'stats' => [
                    'calculation_id' => $this->calculation_id
                ]
            ])
        ];
    }
}
