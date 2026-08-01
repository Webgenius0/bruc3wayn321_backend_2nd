<?php

namespace App\Http\Controllers\Web\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function privacyPolicy(): Response
    {
        $page = Page::where('slug', 'privacy-policy')->first();
        $legacyPage = Page::where('slug', 'privacy-and-policy')->first();

        return $this->renderPage(
            filled($page?->content) ? $page : ($legacyPage ?: $page ?: Page::privacyPolicy())
        );
    }

    public function termsAndConditions(): Response
    {
        return $this->renderPage(Page::termsAndConditions());
    }

    public function support(): Response
    {
        $page = Page::firstOrCreate(
            ['slug' => 'support'],
            [
                'title' => 'Your Journey, Supported.',
                'content' => 'Tell us what happened and our support team will help you get back to exploring meaningful places, stories, and routes.',
                'status' => true,
            ]
        );

        abort_unless($page->status, 404);

        return Inertia::render('frontend/support', [
            'page' => [
                'title' => $page->title,
                'content' => $page->content,
            ],
        ]);
    }

    private function renderPage(Page $page): Response
    {
        abort_unless($page->status, 404);

        return Inertia::render('frontend/page', [
            'page' => [
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
            ],
        ]);
    }
}
