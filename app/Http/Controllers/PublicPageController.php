<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function home(): View
    {
        return view('public.home');
    }

    public function about(): View
    {
        return view('public.about', ['title' => __('About')]);
    }

    public function howItWorks(): View
    {
        return view('public.how-it-works', ['title' => __('How It Works')]);
    }

    public function faq(): View
    {
        return view('public.faq', ['title' => __('FAQ')]);
    }

    public function contact(): View
    {
        return view('public.contact', ['title' => __('Contact')]);
    }

    public function terms(): View
    {
        return view('public.terms', ['title' => __('Terms and Conditions')]);
    }

    public function privacy(): View
    {
        return view('public.privacy', ['title' => __('Privacy Policy')]);
    }

    public function refundPolicy(): View
    {
        return view('public.refund-policy', ['title' => __('Refund and Cancellation Policy')]);
    }

    public function cookiePolicy(): View
    {
        return view('public.cookie-policy', ['title' => __('Cookie Policy')]);
    }
}
