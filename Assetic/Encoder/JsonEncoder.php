<?php

namespace Fazy\AsseticConfigBundle\Assetic\Encoder;

class JsonEncoder
{
    public function __invoke($value)
    {
        return json_encode($value);
    }
}
