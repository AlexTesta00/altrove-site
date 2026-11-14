<?php
declare(strict_types=1);
namespace Altrove;
final class Validator {
    public static function text(string $value): string {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = \Normalizer::normalize($value, \Normalizer::FORM_C);
        if ($value === false) return '';
        return trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\p{Cf}]/u', '', $value) ?? '');
    }
    public static function validate(array $input, array $origins): array {
        $data = [];
        foreach (['nome', 'cognome', 'telefono', 'esperienza', 'origine', 'veste', 'tema'] as $key) $data[$key] = self::text(is_string($input[$key] ?? null) ? $input[$key] : '');
        $errors = [];
        foreach (['nome', 'cognome'] as $key) if (!preg_match('/^[\p{L}\p{M}][\p{L}\p{M} \'’\-]{1,59}$/uD', $data[$key])) $errors[$key] = "Scrivi il tuo $key usando da due a sessanta lettere, spazi, apostrofi o trattini.";
        $phone = preg_replace('/[\s().-]/u', '', $data['telefono']);
        if (str_starts_with($phone, '00')) $phone = '+' . substr($phone, 2);
        if (!str_starts_with($phone, '+')) $phone = '+39' . $phone;
        if (!preg_match('/^\+[1-9][0-9]{7,14}$/D', $phone)) $errors['telefono'] = 'Scrivi un numero di telefono valido, con il prefisso internazionale se non è italiano.';
        $data['telefono'] = $phone;
        if (mb_strlen($data['esperienza']) < 30 || mb_strlen($data['esperienza']) > 4000) $errors['esperienza'] = 'Raccontami la tua esperienza in almeno trenta caratteri, senza superare il limite indicato.';
        if (($input['consenso'] ?? '') !== '1') $errors['consenso'] = 'Per inviare la candidatura, leggi l’informativa e dai il tuo consenso.';
        if (!in_array($data['origine'], $origins, true)) $data['origine'] = 'candidati';
        if (!in_array($data['veste'], ['esperienza', 'professionista', 'progetto'], true)) $errors['veste'] = 'Scegli in che veste vuoi partecipare.';
        if (!in_array($data['tema'], ['nde', 'medium', 'channeling', 'mentalismo', 'altro'], true)) $errors['tema'] = 'Scegli il tema più vicino alla tua storia.';
        $data['consenso'] = ($input['consenso'] ?? '') === '1';
        return [$data, $errors];
    }
}

