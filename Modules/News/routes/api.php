<?php

/*
 * News is served through Inertia only; there is no API surface for it yet.
 *
 * The generator's default apiResource was removed rather than left in place —
 * it mapped to show/update/destroy signatures the controller does not match,
 * so those routes would have failed at runtime.
 */
