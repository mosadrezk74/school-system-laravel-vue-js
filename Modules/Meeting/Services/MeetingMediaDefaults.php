<?php

namespace Modules\Meeting\Services;

use Modules\Setting\Services\SettingsService;

/**
 * How a room opens: muted or not, and whether the prejoin screen is shown.
 *
 * These are school-wide settings rather than a consequence of the signed-in
 * role. Tying "start unmuted" to being a moderator meant a teacher's camera
 * switched itself on the moment the page loaded, with no chance to check
 * themselves first — so the defaults below are deliberately conservative and
 * the prejoin screen stays on unless a school turns it off.
 */
final readonly class MeetingMediaDefaults
{
    private const KEY_AUDIO_MUTED = 'meeting_start_with_audio_muted';

    private const KEY_VIDEO_MUTED = 'meeting_start_with_video_muted';

    private const KEY_SHOW_PREJOIN = 'meeting_show_prejoin';

    public function __construct(private SettingsService $settings) {}

    public function startWithAudioMuted(): bool
    {
        return $this->flag(self::KEY_AUDIO_MUTED, true);
    }

    public function startWithVideoMuted(): bool
    {
        return $this->flag(self::KEY_VIDEO_MUTED, true);
    }

    public function showPrejoin(): bool
    {
        return $this->flag(self::KEY_SHOW_PREJOIN, true);
    }

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'start_with_audio_muted' => $this->startWithAudioMuted(),
            'start_with_video_muted' => $this->startWithVideoMuted(),
            'show_prejoin' => $this->showPrejoin(),
        ];
    }

    private function flag(string $key, bool $default): bool
    {
        $value = $this->settings->value($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
