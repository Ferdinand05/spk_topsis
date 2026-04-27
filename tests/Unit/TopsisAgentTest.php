<?php

namespace Tests\Unit;

use App\Ai\Agents\TopsisAgent;
use PHPUnit\Framework\TestCase;

class TopsisAgentTest extends TestCase
{
    public function test_it_builds_a_conclusion_prompt_from_topsis_data(): void
    {
        $agent = new TopsisAgent;

        $prompt = $agent->buildConclusionPrompt(
            calculation: ['id' => 1, 'name' => 'Seleksi Karyawan'],
            criteria: [
                ['id' => 10, 'code' => 'C1', 'name' => 'Pengalaman', 'weight' => 0.4, 'type' => 'benefit'],
            ],
            alternatives: [
                ['id' => 20, 'code' => 'A1', 'name' => 'Andi'],
            ],
            results: [
                ['rank' => 1, 'id' => 20, 'name' => 'Andi', 'score' => 0.9123, 'd_plus' => 0.1, 'd_minus' => 0.8],
            ],
            matrices: [
                'normalized' => [[1]],
            ],
        );

        $this->assertStringContainsString('Seleksi Karyawan', $prompt);
        $this->assertStringContainsString('"code": "C1"', $prompt);
        $this->assertStringContainsString('"name": "Andi"', $prompt);
        $this->assertStringContainsString('"score": 0.9123', $prompt);
        $this->assertStringContainsString('Jangan mengarang data baru', $prompt);
    }
}
