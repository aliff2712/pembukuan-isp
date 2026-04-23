<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class Card extends Component
{
    public $title;
    public $icon;
    public $bg;
    public $link;
    public $border;

    public function __construct(
        $title,
        $icon,
        $bg = '',
        $link = null,
        $border = null
    ) {
        $this->title = $title;
        $this->icon = $icon;
        $this->bg = $bg;
        $this->link = $link;
        $this->border = $border;
    }

    public function render()
    {
        return view('components.dashboard.card');
    }
}