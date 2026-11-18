<?php

namespace App\Http\Requests;

use App\Enums\Cel;
use App\Enums\Szint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Az egylépéses regisztrációs kérdőív validációja (cél, szint, eszközök, panaszok).
 *
 * Az egészségügyi hozzájárulás (GDPR 9. cikk) csak akkor kötelező, ha a felhasználó
 * ténylegesen jelölt be diagnosztizált állapotot vagy orvosi engedélyhez kötött kérdést —
 * ezt a withValidator() ellenőrzi, mert ez a két mező egymástól függ.
 */
class RegisztraciosKerdoivRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:180', 'unique:felhasznalo,email'],
            'jelszo' => ['required', 'min:8', 'confirmed'],
            'nev' => ['required', 'string', 'max:100'],
            'cel' => ['required', Rule::in(Cel::ertekek())],
            'szint' => ['required', Rule::in(Szint::ertekek())],
            'testalkat' => ['nullable', Rule::in(['EKTOMORF', 'MEZOMORF', 'ENDOMORF'])],
            'nem' => ['nullable', Rule::in(['ferfi', 'no', 'egyeb'])],
            'szuletesi_ev' => ['nullable', 'integer', 'between:1920,' . now()->year],
            'magassag_cm' => ['nullable', 'integer', 'between:100,250'],
            'testsuly_kg' => ['nullable', 'numeric', 'between:30,300'],
            'heti_edzesszam' => ['required', 'integer', 'between:1,6'],
            'otthon_edz' => ['sometimes', 'boolean'],
            'diagnozis_panaszok' => ['array'],
            'diagnozis_panaszok.*' => ['integer', Rule::exists('panasz_tipus', 'panasz_tipus_id')->where('eredet', 'diagnozis')],
            'orvosi_panaszok' => ['array'],
            'orvosi_panaszok.*' => ['integer', Rule::exists('panasz_tipus', 'panasz_tipus_id')->where('eredet', 'orvosi_engedely')],
            'egeszseg_hozzajarulas' => ['sometimes', 'boolean'],
            'gdpr_hozzajarulas' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Ezzel az e-mail címmel már van fiók.',
            'gdpr_hozzajarulas.accepted' => 'Az adatkezelési hozzájárulás elfogadása kötelező.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $vanPanasz = $this->input('diagnozis_panaszok', []) !== [] || $this->input('orvosi_panaszok', []) !== [];

            if ($vanPanasz && ! $this->boolean('egeszseg_hozzajarulas')) {
                $v->errors()->add(
                    'egeszseg_hozzajarulas',
                    'A megadott egészségügyi adatok rögzítéséhez külön hozzájárulás szükséges.',
                );
            }
        });
    }
}
