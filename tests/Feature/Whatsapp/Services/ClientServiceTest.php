<?php

namespace Whatsapp\Services;

use Modules\Whatsapp\Services\ClientService;
use Tests\TestCase;

class ClientServiceTest extends TestCase
{

    public function testE()
    {
        $name = 'fsdfds';
        $multilineString = <<<EOD
This is a multiline string.
It can span across multiple lines.
Variables like $name can be interpolated.
EOD;

        echo $multilineString;
    }

}
