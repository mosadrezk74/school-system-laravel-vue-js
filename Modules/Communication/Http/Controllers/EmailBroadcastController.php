<?php

namespace Modules\Communication\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Modules\Communication\DTOs\SendEmailBroadcastDTO;
use Modules\Communication\Http\Requests\StoreEmailBroadcastRequest;
use Modules\Communication\Services\AudienceOptionsService;
use Modules\Communication\Services\EmailSenderService;
use Modules\Core\Abstracts\BaseInertiaController;

class EmailBroadcastController extends BaseInertiaController
{
    public function create(AudienceOptionsService $audienceOptions): Response
    {
        $this->authorize('broadcast.create');

        return $this->render('Communication::EmailBroadcast/Create', $audienceOptions->forForm());
    }

    public function store(StoreEmailBroadcastRequest $request, EmailSenderService $emailSender): RedirectResponse
    {
        $this->authorize('broadcast.create');

        $emailSender->send(SendEmailBroadcastDTO::fromRequest($request));

        return $this->toRoute('communication.emailBroadcast.create', [], __('communication::communication.email_queued'));
    }
}
