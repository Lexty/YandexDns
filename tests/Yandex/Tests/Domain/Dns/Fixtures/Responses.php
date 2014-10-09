<?php

namespace Yandex\Tests\Domain\Dns\Fixtures;

class Responses
{
    public static function responsesFixtures()
    {
        return array(
            'correctSuccess' => '<?xml version="1.0" encoding="utf-8"?>
<page><domains><domain><name>example.com</name><response><record domain="yourdomain.ru" priority="" ttl="21600" subdomain="www" type="A" id="342432432">127.0.0.1</record></response><nsdelegated/></domain><error>ok</error></domains></page>',
            'correctEmptyData' => '<?xml version="1.0" encoding="utf-8"?>
<page><domains><domain><name>example.com</name></domain><error>ok</error></domains></page>',
            'correctError' => '<?xml version="1.0" encoding="utf-8"?>
<page><domains><domain><name>example.com</name><response><record domain="yourdomain.ru" priority="" ttl="21600" subdomain="www" type="A" id="342432432">127.0.0.1</record></response><nsdelegated/></domain><error>error</error></domains></page>',
            'anotherStructure' => '<?xml version="1.0" encoding="utf-8"?>
<page><domain><domain><name>example.com</name><response><record domain="yourdomain.ru" priority="" ttl="21600" subdomain="www" type="A" id="342432432">127.0.0.1</record></response><nsdelegated/></domain><error>ok</error></domain></page>',
            'invalidXml' => '<?xml version="1.0" encoding="utf-8"?>
<page><domains><domain><name>example.com</name><response><record domain="yourdomain.ru" priority="" ttl="21600" subdomain="www" type="A" id="342432432">127.0.0.1</record></response><nsdelegated/></domain><success>ok</success></domains>',
        );
    }
}