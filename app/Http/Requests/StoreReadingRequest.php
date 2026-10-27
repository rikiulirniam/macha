<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:300'],
            'tags' => ['required', 'array', 'min:1', 'max:3'],
            'tags.*' => ['exists:tags,id'],
            'status' => ['required', 'in:draft,published'],
            'slides' => ['required', 'array', 'min:1', 'max:8'],
            'slides.*.type' => ['required', 'in:text,quote,image,audio'],
            'slides.*.bg_color' => ['required', 'in:paper,teal,kilat,amber'],
            'slides.*.body' => ['nullable', 'string', 'max:2000'],
            'slides.*.source' => ['nullable', 'string', 'max:150'],
            'slides.*.image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'slides.*.audio' => ['nullable', 'file', 'mimes:mp3,m4a,ogg', 'max:5120'],
            'has_quiz' => ['nullable', 'boolean'],
            'quiz_question' => ['required_if:has_quiz,1', 'nullable', 'string', 'max:300'],
            'quiz_options' => ['required_if:has_quiz,1', 'nullable', 'array', 'min:2', 'max:4'],
            'quiz_options.*' => ['required_if:has_quiz,1', 'nullable', 'string', 'max:150'],
            'quiz_correct_index' => ['required_if:has_quiz,1', 'nullable', 'integer', 'min:0', 'max:3'],
            'quiz_explanation' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $slides = $this->input('slides', []);
            $totalWords = 0;

            foreach ($slides as $slide) {
                if (! empty($slide['body'])) {
                    $totalWords += str_word_count(strip_tags($slide['body']));
                }
            }

            if ($totalWords > 1000) {
                $validator->errors()->add('slides', "Total kata dalam seluruh slide ({$totalWords} kata) melebihi batas maksimal 1000 kata.");
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul bacaan wajib diisi.',
            'title.max' => 'Judul bacaan maksimal 150 karakter.',
            'tags.required' => 'Pilih minimal 1 topik tag.',
            'tags.min' => 'Pilih minimal 1 topik tag.',
            'tags.max' => 'Pilih maksimal 3 topik tag.',
            'slides.required' => 'Bacaan harus memiliki minimal 1 slide.',
            'slides.min' => 'Bacaan harus memiliki minimal 1 slide.',
            'slides.max' => 'Bacaan maksimal memiliki 8 slide.',
            'slides.*.image.max' => 'Ukuran gambar maksimal 2 MB.',
            'slides.*.image.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'slides.*.audio.max' => 'Ukuran audio maksimal 5 MB.',
            'slides.*.audio.mimes' => 'Format audio harus mp3, m4a, atau ogg.',
            'quiz_question.required_if' => 'Pertanyaan kuis wajib diisi jika kuis diaktifkan.',
            'quiz_options.required_if' => 'Pilihan jawaban kuis wajib diisi (minimal 2 opsi).',
            'quiz_options.min' => 'Kuis membutuhkan minimal 2 pilihan jawaban.',
            'quiz_options.max' => 'Kuis maksimal memiliki 4 pilihan jawaban.',
        ];
    }
}
