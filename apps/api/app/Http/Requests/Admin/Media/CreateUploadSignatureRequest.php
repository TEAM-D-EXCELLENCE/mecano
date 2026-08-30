<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Media;

use App\Enums\MediaKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class CreateUploadSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'car_id' => ['required', 'integer', 'exists:cars,id'],
            'kind' => ['required', 'string', Rule::enum(MediaKind::class)],
            'mime' => ['required', 'string'],
            'bytes' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'car_id.required' => 'Le véhicule (car_id) est obligatoire.',
            'car_id.exists' => 'Le véhicule sélectionné n\'existe pas.',
            'kind.required' => 'Le type de média (kind) est obligatoire.',
            'kind.enum' => 'Le type de média doit être "photo" ou "video".',
            'mime.required' => 'Le type MIME est obligatoire.',
            'bytes.required' => 'La taille en octets est obligatoire.',
            'bytes.min' => 'La taille du fichier doit être supérieure à 0 octet.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $kind = $this->input('kind');
                $mime = (string) $this->input('mime');
                $bytes = (int) $this->input('bytes');

                if ($kind === MediaKind::Photo->value) {
                    $allowedMimes = (array) config('media.photos.allowed_mimes', ['image/jpeg', 'image/png', 'image/webp', 'image/heic']);
                    $maxBytes = (int) config('media.photos.max_size_bytes', 15 * 1024 * 1024);

                    if (! in_array($mime, $allowedMimes, true)) {
                        $validator->errors()->add('mime', 'Format d\'image non supporté. Formats acceptés : '.implode(', ', $allowedMimes));
                    }

                    if ($bytes > $maxBytes) {
                        $validator->errors()->add('bytes', 'La taille de l\'image dépasse la limite maximale autorisée (15 Mo).');
                    }
                } elseif ($kind === MediaKind::Video->value) {
                    $allowedMimes = (array) config('media.videos.allowed_mimes', ['video/mp4', 'video/quicktime']);
                    $maxBytes = (int) config('media.videos.max_size_bytes', 200 * 1024 * 1024);

                    if (! in_array($mime, $allowedMimes, true)) {
                        $validator->errors()->add('mime', 'Format de vidéo non supporté. Formats acceptés : '.implode(', ', $allowedMimes));
                    }

                    if ($bytes > $maxBytes) {
                        $validator->errors()->add('bytes', 'La taille de la vidéo dépasse la limite maximale autorisée (200 Mo).');
                    }
                }
            },
        ];
    }
}
