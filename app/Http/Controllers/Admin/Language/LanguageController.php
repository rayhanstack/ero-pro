<?php

namespace App\Http\Controllers\Admin\Language;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\StoreLanguageRequest;
use App\Http\Requests\Language\UpdateLanguageRequest;
use App\Models\Language;
use App\Services\Language\LanguageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function __construct(
        protected LanguageService $languageService
    ) {}

    public function index(): View
    {
        $languages = $this->languageService->getPaginatedLanguages();

        return view('admin.languages.index', compact('languages'));
    }

    public function create(): View
    {
        return view('admin.languages.create');
    }

    public function store(StoreLanguageRequest $request): RedirectResponse
    {
        $this->languageService->createLanguage($request->validated());

        return redirect()
            ->route('languages.index')
            ->with('success', _trans('common.Language created successfully.'));
    }

    public function edit(Language $language): View
    {
        return view('admin.languages.edit', compact('language'));
    }

    public function update(UpdateLanguageRequest $request, Language $language): RedirectResponse
    {
        $this->languageService->updateLanguage($language, $request->validated());

        return redirect()
            ->route('languages.index')
            ->with('success', _trans('common.Language updated successfully.'));
    }

    public function setDefault(Language $language): RedirectResponse
    {
        $this->languageService->setDefault($language);

        return redirect()
            ->route('languages.index')
            ->with('success', _trans('common.Default language updated successfully.'));
    }

    public function destroy(Language $language): RedirectResponse
    {
        try {
            $this->languageService->deleteLanguage($language);

            return redirect()
                ->route('languages.index')
                ->with('success', _trans('common.Language deleted successfully.'));
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('languages.index')
                ->with('error', $e->getMessage());
        }
    }
}
