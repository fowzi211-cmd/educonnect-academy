<?php

namespace App\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Certificates extends Component
{
    public function render()
    {
        return view('livewire.student.certificates', [
            'certificates' => Auth::user()->certificates()->with('course')->latest('issued_at')->get(),
        ]);
    }
}
