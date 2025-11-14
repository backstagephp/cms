<?php

namespace Backstage\Announcements\Livewire;

use Backstage\Announcements\Models\Announcement as ModelsAnnouncement;
use Filament\Facades\Filament;
use Livewire\Component;

class Announcement extends Component
{
    public ModelsAnnouncement $announcement;

    public string $scope;

    public function mount(ModelsAnnouncement $announcement, $scope)
    {
        $this->announcement = $announcement;

        $this->scope = $scope;

        $this->getColor();
    }

    public function render()
    {
        if ($this->announcement->isDismissedBy()) {
            return '<div></div>';
        }

        return view('backstage/announcements::livewire.announcement');
    }

    public function getColor()
    {
        $styles = \Illuminate\Support\Arr::toCssStyles([
            \Filament\Support\get_color_css_variables($this->announcement->color, shades: [600]),
        ]);

        return $styles;
    }

    public function canMarkAsRead(): bool
    {
        return Filament::auth()->check();
    }

    public function markAsRead()
    {
        if (! $this->announcement->isDismissedBy()) {
            $this->announcement->dismissals()->create([
            ]);
        }
    }
}
