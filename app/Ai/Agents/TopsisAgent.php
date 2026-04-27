<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Responses\AgentResponse;
use Stringable;


#[Provider(Lab::Gemini)]
#[Temperature(0.3)]
#[Timeout(120)]
class TopsisAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return "Anda adalah asisten Sistem Penunjang Keputusan dengan metode TOPSIS, tiap jawaban yang anda berikan adalah sebuah kebenaran dan referensi dari studi literatur, tugas anda adalah memberikan kesimpulan
        dari hasil perhitungan TOPSIS yang diberikan.";
    }

    /**
     * Build a prompt that contains TOPSIS calculation data in a safe JSON payload.
     */
    public function buildConclusionPrompt(
        array $calculation,
        array $criteria,
        array $alternatives,
        array $results,
        array $matrices = [],
    ): string {
        $payload = [
            'calculation' => $calculation,
            'criteria' => $criteria,
            'alternatives' => $alternatives,
            'results' => $results,
            'matrices' => $matrices,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}';

        return <<<PROMPT
        Gunakan data hasil TOPSIS berikut untuk membuat kesimpulan.

        Data:
        {$json}

        Instruksi:
        1. Fokus hanya pada interpretasi hasil, bukan perhitungan.
        2. Jangan menyebutkan ulang seluruh ranking.
        3. Sebutkan:
        - Alternatif terbaik (ranking 1)
        - Alasan umum mengapa alternatif tersebut unggul
        4. Singgung secara singkat pengaruh kriteria:
        - benefit (nilai tinggi lebih baik)
        - cost (nilai rendah lebih baik)
        5. Berikan 1 alternatif cadangan terbaik (ranking 2) sebagai rekomendasi pengganti.
        6. Gunakan bahasa sederhana, jelas, dan tidak bertele-tele (maksimal 3 paragraf pendek).
        7. Gunakan data yang tersedia saja (dilarang mengarang atau mengubah angka).
        8. Jangan menampilkan rumus atau proses perhitungan.
        9. Tarik kesimpulan secara mendalam dan berikan saran jika perlu

        Format output WAJIB:

        **Alternatif Terbaik**
        <isi>

        **Analisis Singkat**
        <isi>

        **Rekomendasi Alternatif**
        <isi>

        Larangan:
        - Jangan mengulang daftar ranking
        - Jangan membuat paragraf panjang
        - Jangan keluar dari format di atas
        PROMPT;
    }

    /**
     * Ask Gemini to write a conclusion from TOPSIS data.
     */
    public function conclude(
        array $calculation,
        array $criteria,
        array $alternatives,
        array $results,
        array $matrices = [],
    ): AgentResponse {
        return $this->prompt(
            $this->buildConclusionPrompt($calculation, $criteria, $alternatives, $results, $matrices),
            provider: Lab::Gemini,
        );
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
}
