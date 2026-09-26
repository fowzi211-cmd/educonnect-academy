<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Faq;
use App\Models\NewsItem;
use App\Models\Page;
use App\Models\User;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'featuredCourses' => Course::published()
                ->with(['category', 'lecturers'])
                ->latest('published_at')
                ->take(6)
                ->get(),
            'stats' => [
                'courses' => Course::published()->count(),
                'lecturers' => User::role('lecturer')->count(),
                'students' => User::role('student')->count(),
                'categories' => Category::where('is_active', true)->count(),
            ],
            'newsItems' => NewsItem::published()->orderBy('position')->get(),
        ]);
    }

    public function about(): View
    {
        return view('public.about', ['page' => $this->pageOrAbort('about')]);
    }

    public function howItWorks(): View
    {
        return view('public.how-it-works', ['page' => $this->pageOrAbort('how-it-works')]);
    }

    public function assessments(): View
    {
        return view('public.assessments', ['title' => __('Assessments')]);
    }

    public function faq(): View
    {
        return view('public.faq', [
            'title' => __('FAQ'),
            'faqs' => Faq::where('is_published', true)->orderBy('position')->get(),
        ]);
    }

    public function contact(): View
    {
        return view('public.contact', ['title' => __('Contact')]);
    }

    public function terms(): View
    {
        return view('public.terms', ['page' => $this->pageOrAbort('terms')]);
    }

    public function privacy(): View
    {
        return view('public.privacy', ['page' => $this->pageOrAbort('privacy')]);
    }

    public function refundPolicy(): View
    {
        return view('public.refund-policy', ['page' => $this->pageOrAbort('refund-policy')]);
    }

    public function cookiePolicy(): View
    {
        return view('public.cookie-policy', ['page' => $this->pageOrAbort('cookie-policy')]);
    }

    protected function pageOrAbort(string $slug): Page
    {
        return Page::where('slug', $slug)->firstOrFail();
    }
}
