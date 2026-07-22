<?php

namespace App\Services;

/**
 * Local ISO-3166 flag helper. It deliberately does not call a remote API.
 */
final class CountryFlagService
{
    /** @var array<string, string> */
    private const ISO3_TO_ISO2 = [
        'AFG'=>'AF','ALB'=>'AL','DZA'=>'DZ','AND'=>'AD','AGO'=>'AO','ATG'=>'AG','ARG'=>'AR','ARM'=>'AM','AUS'=>'AU','AUT'=>'AT','AZE'=>'AZ',
        'BHS'=>'BS','BHR'=>'BH','BGD'=>'BD','BRB'=>'BB','BLR'=>'BY','BEL'=>'BE','BLZ'=>'BZ','BEN'=>'BJ','BTN'=>'BT','BOL'=>'BO','BIH'=>'BA','BWA'=>'BW','BRA'=>'BR','BRN'=>'BN','BGR'=>'BG','BFA'=>'BF','BDI'=>'BI',
        'CPV'=>'CV','KHM'=>'KH','CMR'=>'CM','CAN'=>'CA','CAF'=>'CF','TCD'=>'TD','CHL'=>'CL','CHN'=>'CN','COL'=>'CO','COM'=>'KM','COG'=>'CG','COD'=>'CD','CRI'=>'CR','CIV'=>'CI','HRV'=>'HR','CUB'=>'CU','CYP'=>'CY','CZE'=>'CZ',
        'DNK'=>'DK','DJI'=>'DJ','DMA'=>'DM','DOM'=>'DO','ECU'=>'EC','EGY'=>'EG','SLV'=>'SV','GNQ'=>'GQ','ERI'=>'ER','EST'=>'EE','SWZ'=>'SZ','ETH'=>'ET',
        'FJI'=>'FJ','FIN'=>'FI','FRA'=>'FR','GAB'=>'GA','GMB'=>'GM','GEO'=>'GE','DEU'=>'DE','GHA'=>'GH','GRC'=>'GR','GRD'=>'GD','GTM'=>'GT','GIN'=>'GN','GNB'=>'GW','GUY'=>'GY',
        'HTI'=>'HT','HND'=>'HN','HUN'=>'HU','ISL'=>'IS','IND'=>'IN','IDN'=>'ID','IRN'=>'IR','IRQ'=>'IQ','IRL'=>'IE','ISR'=>'IL','ITA'=>'IT','JAM'=>'JM','JPN'=>'JP','JOR'=>'JO',
        'KAZ'=>'KZ','KEN'=>'KE','KIR'=>'KI','PRK'=>'KP','KOR'=>'KR','KWT'=>'KW','KGZ'=>'KG','LAO'=>'LA','LVA'=>'LV','LBN'=>'LB','LSO'=>'LS','LBR'=>'LR','LBY'=>'LY','LIE'=>'LI','LTU'=>'LT','LUX'=>'LU',
        'MDG'=>'MG','MWI'=>'MW','MYS'=>'MY','MDV'=>'MV','MLI'=>'ML','MLT'=>'MT','MHL'=>'MH','MRT'=>'MR','MUS'=>'MU','MEX'=>'MX','FSM'=>'FM','MDA'=>'MD','MCO'=>'MC','MNG'=>'MN','MNE'=>'ME','MAR'=>'MA','MOZ'=>'MZ','MMR'=>'MM',
        'NAM'=>'NA','NRU'=>'NR','NPL'=>'NP','NLD'=>'NL','NZL'=>'NZ','NIC'=>'NI','NER'=>'NE','NGA'=>'NG','MKD'=>'MK','NOR'=>'NO','OMN'=>'OM','PAK'=>'PK','PLW'=>'PW','PAN'=>'PA','PNG'=>'PG','PRY'=>'PY','PER'=>'PE','PHL'=>'PH','POL'=>'PL','PRT'=>'PT','QAT'=>'QA',
        'ROU'=>'RO','RUS'=>'RU','RWA'=>'RW','KNA'=>'KN','LCA'=>'LC','VCT'=>'VC','WSM'=>'WS','SMR'=>'SM','STP'=>'ST','SAU'=>'SA','SEN'=>'SN','SRB'=>'RS','SYC'=>'SC','SLE'=>'SL','SGP'=>'SG','SVK'=>'SK','SVN'=>'SI','SLB'=>'SB','SOM'=>'SO','ZAF'=>'ZA','SSD'=>'SS','ESP'=>'ES','LKA'=>'LK','SDN'=>'SD','SUR'=>'SR','SWE'=>'SE','CHE'=>'CH','SYR'=>'SY',
        'TWN'=>'TW','TJK'=>'TJ','TZA'=>'TZ','THA'=>'TH','TLS'=>'TL','TGO'=>'TG','TON'=>'TO','TTO'=>'TT','TUN'=>'TN','TUR'=>'TR','TKM'=>'TM','TUV'=>'TV','UGA'=>'UG','UKR'=>'UA','ARE'=>'AE','GBR'=>'GB','USA'=>'US','URY'=>'UY','UZB'=>'UZ','VUT'=>'VU','VAT'=>'VA','VEN'=>'VE','VNM'=>'VN','YEM'=>'YE','ZMB'=>'ZM','ZWE'=>'ZW',
        'AIA'=>'AI','ALA'=>'AX','ASM'=>'AS','ATA'=>'AQ','ABW'=>'AW','BLM'=>'BL','BMU'=>'BM','BES'=>'BQ','BVT'=>'BV','CXR'=>'CX','CCK'=>'CC','COK'=>'CK','CUW'=>'CW','FLK'=>'FK','FRO'=>'FO','PYF'=>'PF','ATF'=>'TF','GUF'=>'GF','GLP'=>'GP','GIB'=>'GI','GRL'=>'GL','GUM'=>'GU','HKG'=>'HK','HMD'=>'HM','IMN'=>'IM','JEY'=>'JE','MAC'=>'MO','MTQ'=>'MQ','MSR'=>'MS','NCL'=>'NC','NIU'=>'NU','NFK'=>'NF','MNP'=>'MP','PCN'=>'PN','PRI'=>'PR','REU'=>'RE','SHN'=>'SH','MAF'=>'MF','SPM'=>'PM','SXM'=>'SX','TKL'=>'TK','TCA'=>'TC','VIR'=>'VI','WLF'=>'WF','ESH'=>'EH','IOT'=>'IO','UMI'=>'UM','XKX'=>'XK',
    ];

    public static function emoji(?string $iso3): string
    {
        $iso2 = self::iso2($iso3);
        if (!$iso2) return '🌐';
        return implode('', array_map(static fn (string $letter): string => mb_chr(127397 + ord($letter)), str_split($iso2)));
    }

    public static function iso2(?string $iso3): ?string
    {
        $code = strtoupper(trim((string) $iso3));
        if (strlen($code) === 2) return strtolower($code);
        return isset(self::ISO3_TO_ISO2[$code]) ? strtolower(self::ISO3_TO_ISO2[$code]) : null;
    }
}
