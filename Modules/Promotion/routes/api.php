<?php

/*
 * Promotions are an admin screen only; there is no API surface for them yet.
 *
 * The generator's default apiResource was removed rather than left in place —
 * it mapped to show/update/destroy methods the controller does not implement,
 * so every one of those routes would have failed at runtime.
 */
