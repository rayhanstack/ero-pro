<?php

namespace App\Services\Language;

use App\Models\Language;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LanguageService
{
    /**
     * Get paginated languages list.
     */
    public function getPaginatedLanguages(int $perPage = 15): LengthAwarePaginator
    {
        return Language::orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get all active languages.
     */
    public function getActiveLanguages()
    {
        return Language::where('status', 'active')->orderBy('name', 'asc')->get();
    }

    /**
     * Create a new language.
     */
    public function createLanguage(array $data): Language
    {
        return DB::transaction(function () use ($data) {
            return Language::create([
                'name' => $data['name'],
                'code' => strtolower($data['code']),
                'native' => $data['native'] ?? $data['name'],
                'rtl' => ! empty($data['rtl']) ? 1 : 0,
                'status' => $data['status'] ?? 'active',
                'is_default' => 0,
            ]);
        });
    }

    /**
     * Update an existing language.
     */
    public function updateLanguage(Language $language, array $data): Language
    {
        return DB::transaction(function () use ($language, $data) {
            $language->update([
                'name' => $data['name'],
                'code' => strtolower($data['code']),
                'native' => $data['native'] ?? $data['name'],
                'rtl' => ! empty($data['rtl']) ? 1 : 0,
                'status' => $data['status'] ?? $language->status,
            ]);

            return $language;
        });
    }

    /**
     * Set a language as the system default.
     */
    public function setDefault(Language $language): void
    {
        DB::transaction(function () use ($language) {
            Language::query()->update(['is_default' => 0]);
            $language->update(['is_default' => 1, 'status' => 'active']);
        });
    }

    /**
     * Delete a language (preventing deletion if default or active English).
     */
    public function deleteLanguage(Language $language): bool
    {
        if ($language->is_default || $language->code === 'en') {
            throw new \InvalidArgumentException(_trans('common.Default or primary English language cannot be deleted.'));
        }

        return (bool) $language->delete();
    }
}
