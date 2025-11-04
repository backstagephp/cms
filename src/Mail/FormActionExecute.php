<?php

namespace Backstage\Mail;

use Backstage\Models\FormAction;
use Backstage\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormActionExecute extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public FormAction $action, public FormSubmission $formSubmission)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = $this->formSubmission->value($this->action->config['from_email'] ?? null) ?? $this->action->config['from_email'] ?? config('mail.from.address');
        $fromName = $this->formSubmission->value($this->action->config['from_name'] ?? null) ?? $this->action->config['from_name'] ?? config('mail.from.name');

        $subject = $this->action->config['subject'] ?? __(':form submitted', ['form' => $this->formSubmission->form?->name ?? $this->formSubmission->form_slug]);

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->action->config['template'] ?? 'backstage::mails.form.action',
            with: [
                'form' => $this->action->form,
                'action' => $this->action,
                'submission' => $this->formSubmission->load('values.field'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        foreach ($this->formSubmission->values as $value) {
            if ($value->field->field_type == 'file-upload') {
                $file = json_decode($value->value, true) ?? $value->value;
                if (isset($file['path'])) {
                    $attachments[] = Attachment::fromStorage($file['path']);
                } else {
                    $attachments[] = Attachment::fromStorage($value->value);
                }
            }
        }

        return $attachments;
    }
}
