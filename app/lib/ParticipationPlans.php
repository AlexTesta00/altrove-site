<?php
declare(strict_types=1);
namespace Altrove;

final class ParticipationPlans {
    public static function all(): array {
        return [
            'essenziale' => ['name'=>'Essenziale', 'price'=>200, 'items'=>['Puntata completa su YouTube', '1 Reel su Instagram'],
                'for'=>'Per raccontarti in video e presentare un primo estratto sui social.',
                'difference'=>'La puntata e un primo momento da condividere.', 'recommended'=>false],
            'completo' => ['name'=>'Completo', 'price'=>250, 'items'=>['Puntata completa su YouTube e Spotify', '1 Reel su Instagram'],
                'for'=>'Consigliato se vuoi affiancare al video anche l’ascolto su Spotify.',
                'difference'=>'+50 € rispetto a Essenziale: include Spotify.', 'recommended'=>true],
            'amplifica' => ['name'=>'Amplifica', 'price'=>350, 'items'=>['Puntata completa su YouTube e Spotify', '3 Reel totali su Instagram'],
                'for'=>'Per raccontare tre momenti della puntata attraverso tre estratti social.',
                'difference'=>'+100 € rispetto a Completo: 2 Reel in più.', 'recommended'=>false],
        ];
    }

    public static function find(mixed $id): ?array {
        return is_string($id) ? (self::all()[$id] ?? null) : null;
    }

    public static function message(mixed $id): string {
        $plan = self::find($id);
        if ($plan === null) throw new \InvalidArgumentException('piano_non_valido');
        return $plan['name'] . ' · ' . $plan['price'] . " € (importo finale)\n"
            . implode("\n", $plan['items'])
            . "\n\nÈ la mia preferenza di collaborazione, da valutare e confermare insieme. La scelta non comporta un pagamento né garantisce la partecipazione.";
    }
}
