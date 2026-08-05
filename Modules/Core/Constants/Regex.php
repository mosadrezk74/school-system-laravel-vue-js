<?php

namespace Modules\Core\Constants;

final class Regex
{
    const PHONE = '/^\+[0-9]{9,15}$/';

    const OTP = '/^[0-9]{5}$/';

    const POSTAL_CODE = '/^[0-9]{4,10}$/';

    const SLUG = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    const PRICE = '/^\d+(\.\d{1,2})?$/';

    const UUID = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
}
