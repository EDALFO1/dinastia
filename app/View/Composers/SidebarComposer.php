<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Services\ModuloService;

class SidebarComposer
{
    public function __construct(private ModuloService $service) {}

    public function compose(View $view): void
    {
        $view->with('modulosPermitidos', $this->service->modulosActivos());
    }
}
