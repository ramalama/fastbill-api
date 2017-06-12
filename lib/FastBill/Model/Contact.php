<?php

namespace FastBill\Model;

class Contact extends AbstractModel
{
    protected static $xmlProperties = [
        'CUSTOMER_ID' => 'customerId',
        'ORGANIZATION' => 'organization', // Firmenname [REQUIRED] wenn CUSTOMER_TYPE = business
        'POSITION' => 'position',
        'SALUTATION' => 'salutation',
        'FIRST_NAME' => 'firstName',
        'LAST_NAME' => 'lastName',
        'ADDRESS' => 'address',
        'ADDRESS_2' => 'address2',
        'ZIPCODE' => 'zipcode',
        'CITY' => 'city',
        'COUNTRY_CODE' => 'countryCode',
        'SECONDARY_ADDRESS' => 'secondaryAddress', // Lieferadresse
        'PHONE' => 'phone',
        'PHONE_2' => 'phone2',
        'FAX' => 'fax',
        'MOBILE' => 'mobile',
        'EMAIL' => 'email',
        'VAT_ID' => 'vatId',
        'CURRENCY_CODE' => 'currencyCode',
        'CREATED' => 'created',
        'TAGS' => 'tags',
    ];
}