<?php

namespace Backstage\Listeners;

use Backstage\Events\FormSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ExecuteFormActions implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FormSubmitted $event): void
    {
        //
        if (! $event->formSubmission?->form?->formActions) {
            return;
        }

        foreach ($event->formSubmission->form->formActions as $action) {
            $action->execute($event->formSubmission);
        }
    }
}
