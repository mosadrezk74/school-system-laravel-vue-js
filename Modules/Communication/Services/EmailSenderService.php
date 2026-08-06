<?php

namespace Modules\Communication\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Communication\DTOs\SendEmailBroadcastDTO;
use Modules\Communication\Mail\BroadcastEmail;
use Throwable;

class EmailSenderService
{
    private const CHUNK_SIZE = 200;

    public function __construct(
        private readonly RecipientResolverService $resolver,
    ) {}

    /**
     * Returns control as soon as the response is flushed; the actual sending
     * runs afterwards so the admin is never left waiting on a broadcast to
     * hundreds of recipients.
     */
    public function send(SendEmailBroadcastDTO $dto): void
    {
        dispatch(fn () => $this->deliver($dto))->afterResponse();
    }

    /**
     * Each recipient gets the copy written in their own language, using the
     * translations already produced at request time.
     */
    public function deliver(SendEmailBroadcastDTO $dto): void
    {
        $this->resolver->resolve($dto->targetType, $dto->targetIds)
            ->whereNotNull('email')
            ->select(['id', 'email', 'language'])
            ->chunkById(self::CHUNK_SIZE, function ($users) use ($dto): void {
                foreach ($users as $user) {
                    $this->sendTo($user->email, $user->language, $dto);
                }
            });
    }

    /**
     * One failed address must not abort the rest of the broadcast.
     */
    private function sendTo(string $email, ?string $locale, SendEmailBroadcastDTO $dto): void
    {
        try {
            Mail::to($email)->locale($locale ?? $dto->fallbackLocale)->send(
                new BroadcastEmail(
                    $dto->subjectFor($locale),
                    $dto->bodyFor($locale),
                )
            );
        } catch (Throwable $e) {
            Log::error('Broadcast email delivery failed.', [
                'email' => $email,
                'exception' => $e,
            ]);
        }
    }
}
