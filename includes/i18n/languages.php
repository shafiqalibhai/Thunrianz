<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

	static $cc = array (0=>array('value'=>'en','name'=>'English'),1=>array('value'=>'ad','name'=>'Andorra'),2=>array('value'=>'ae','name'=>'United Arab Emirates'),3=>array('value'=>'af','name'=>'Afghanistan'),4=>array('value'=>'ag','name'=>'Antigua And Barbuda'),5=>array('value'=>'ai','name'=>'Anguilla'),6=>array('value'=>'al','name'=>'Albania'),7=>array('value'=>'am','name'=>'Armenia'),8=>array('value'=>'an','name'=>'Netherlands Antilles'),9=>array('value'=>'ao','name'=>'Angola'),10=>array('value'=>'ar','name'=>'Argentina'),11=>array('value'=>'as','name'=>'American Samoa'),12=>array('value'=>'at','name'=>'Austria'),13=>array('value'=>'au','name'=>'Australia'),14=>array('value'=>'aw','name'=>'Aruba'),15=>array('value'=>'ax','name'=>'Åland Islands'),16=>array('value'=>'az','name'=>'Azerbaijan'),17=>array('value'=>'ba','name'=>'Bosnia And Herzegovina'),18=>array('value'=>'bb','name'=>'Barbados'),19=>array('value'=>'bd','name'=>'Bangladesh'),20=>array('value'=>'be','name'=>'Belgium'),21=>array('value'=>'bf','name'=>'Burkina Faso'),22=>array('value'=>'bg','name'=>'Bulgaria'),23=>array('value'=>'bh','name'=>'Bahrain'),24=>array('value'=>'bi','name'=>'Burundi'),25=>array('value'=>'bj','name'=>'Benin'),26=>array('value'=>'bm','name'=>'Bermuda'),27=>array('value'=>'bn','name'=>'Brunei Darussalam'),28=>array('value'=>'bo','name'=>'Bolivia'),29=>array('value'=>'br','name'=>'Brazil'),30=>array('value'=>'bs','name'=>'Bahamas'),31=>array('value'=>'bt','name'=>'Bhutan'),32=>array('value'=>'bw','name'=>'Botswana'),33=>array('value'=>'by','name'=>'Belarus'),34=>array('value'=>'bz','name'=>'Belize'),35=>array('value'=>'ca','name'=>'Canada'),36=>array('value'=>'cc','name'=>'Cocos (Keeling) Islands'),37=>array('value'=>'cd','name'=>'Congo, The Democratic Republic Of The'),38=>array('value'=>'cf','name'=>'Central African Republic'),39=>array('value'=>'cg','name'=>'Congo'),40=>array('value'=>'ch','name'=>'Switzerland'),41=>array('value'=>'ci','name'=>'Côte D\'ivoire'),42=>array('value'=>'ck','name'=>'Cook Islands'),43=>array('value'=>'cl','name'=>'Chile'),44=>array('value'=>'cm','name'=>'Cameroon'),45=>array('value'=>'cn','name'=>'China'),46=>array('value'=>'co','name'=>'Colombia'),47=>array('value'=>'cr','name'=>'Costa Rica'),48=>array('value'=>'cs','name'=>'Serbia And Montenegro'),49=>array('value'=>'cu','name'=>'Cuba'),50=>array('value'=>'cv','name'=>'Cape Verde'),51=>array('value'=>'cx','name'=>'Christmas Island'),52=>array('value'=>'cy','name'=>'Cyprus'),53=>array('value'=>'cz','name'=>'Czech Republic'),54=>array('value'=>'de','name'=>'Germany'),55=>array('value'=>'dj','name'=>'Djibouti'),56=>array('value'=>'dk','name'=>'Denmark'),57=>array('value'=>'dm','name'=>'Dominica'),58=>array('value'=>'do','name'=>'Dominican Republic'),59=>array('value'=>'dz','name'=>'Algeria'),60=>array('value'=>'ec','name'=>'Ecuador'),61=>array('value'=>'ee','name'=>'Estonia'),62=>array('value'=>'eg','name'=>'Egypt'),63=>array('value'=>'er','name'=>'Eritrea'),64=>array('value'=>'es','name'=>'Spain'),65=>array('value'=>'et','name'=>'Ethiopia'),66=>array('value'=>'fi','name'=>'Finland'),67=>array('value'=>'fj','name'=>'Fiji'),68=>array('value'=>'fk','name'=>'Falkland Islands (Malvinas)'),69=>array('value'=>'fm','name'=>'Micronesia, Federated States Of'),70=>array('value'=>'fo','name'=>'Faroe Islands'),71=>array('value'=>'fr','name'=>'France'),72=>array('value'=>'ga','name'=>'Gabon'),73=>array('value'=>'gb','name'=>'United Kingdom'),74=>array('value'=>'gd','name'=>'Grenada'),75=>array('value'=>'ge','name'=>'Georgia'),76=>array('value'=>'gf','name'=>'French Guiana'),77=>array('value'=>'gh','name'=>'Ghana'),78=>array('value'=>'gi','name'=>'Gibraltar'),79=>array('value'=>'gl','name'=>'Greenland'),80=>array('value'=>'gm','name'=>'Gambia'),81=>array('value'=>'gn','name'=>'Guinea'),82=>array('value'=>'gp','name'=>'Guadeloupe'),83=>array('value'=>'gq','name'=>'Equatorial Guinea'),84=>array('value'=>'gr','name'=>'Greece'),85=>array('value'=>'gt','name'=>'Guatemala'),86=>array('value'=>'gu','name'=>'Guam'),87=>array('value'=>'gw','name'=>'Guinea-Bissau'),88=>array('value'=>'gy','name'=>'Guyana'),89=>array('value'=>'hk','name'=>'Hong Kong'),90=>array('value'=>'hn','name'=>'Honduras'),91=>array('value'=>'hr','name'=>'Croatia'),92=>array('value'=>'ht','name'=>'Haiti'),93=>array('value'=>'hu','name'=>'Hungary'),94=>array('value'=>'id','name'=>'Indonesia'),95=>array('value'=>'ie','name'=>'Ireland'),96=>array('value'=>'il','name'=>'Israel'),97=>array('value'=>'in','name'=>'India'),98=>array('value'=>'io','name'=>'British Indian Ocean Territory'),99=>array('value'=>'iq','name'=>'Iraq'),100=>array('value'=>'ir','name'=>'Iran, Islamic Republic Of'),101=>array('value'=>'is','name'=>'Iceland'),102=>array('value'=>'it','name'=>'Italy'),103=>array('value'=>'jm','name'=>'Jamaica'),104=>array('value'=>'jo','name'=>'Jordan'),105=>array('value'=>'jp','name'=>'Japan'),106=>array('value'=>'ke','name'=>'Kenya'),107=>array('value'=>'kg','name'=>'Kyrgyzstan'),108=>array('value'=>'kh','name'=>'Cambodia'),109=>array('value'=>'ki','name'=>'Kiribati'),110=>array('value'=>'km','name'=>'Comoros'),111=>array('value'=>'kn','name'=>'Saint Kitts And Nevis'),112=>array('value'=>'kp','name'=>'Korea, Democratic People\'s Republic Of'),113=>array('value'=>'kr','name'=>'Korea, Republic Of'),114=>array('value'=>'kw','name'=>'Kuwait'),115=>array('value'=>'ky','name'=>'Cayman Islands'),116=>array('value'=>'kz','name'=>'Kazakhstan'),117=>array('value'=>'la','name'=>'Lao People\'s Democratic Republic'),118=>array('value'=>'lb','name'=>'Lebanon'),119=>array('value'=>'lc','name'=>'Saint Lucia'),120=>array('value'=>'li','name'=>'Liechtenstein'),121=>array('value'=>'lk','name'=>'Sri Lanka'),122=>array('value'=>'lr','name'=>'Liberia'),123=>array('value'=>'ls','name'=>'Lesotho'),124=>array('value'=>'lt','name'=>'Lithuania'),125=>array('value'=>'lu','name'=>'Luxembourg'),126=>array('value'=>'lv','name'=>'Latvia'),127=>array('value'=>'ly','name'=>'Libyan Arab Jamahiriya'),128=>array('value'=>'ma','name'=>'Morocco'),129=>array('value'=>'mc','name'=>'Monaco'),130=>array('value'=>'md','name'=>'Moldova, Republic Of'),131=>array('value'=>'mg','name'=>'Madagascar'),132=>array('value'=>'mh','name'=>'Marshall Islands'),133=>array('value'=>'mk','name'=>'Macedonia, The Former Yugoslav Republic Of'),134=>array('value'=>'ml','name'=>'Mali'),135=>array('value'=>'mm','name'=>'Myanmar'),136=>array('value'=>'mn','name'=>'Mongolia'),137=>array('value'=>'mo','name'=>'Macao'),138=>array('value'=>'mp','name'=>'Northern Mariana Islands'),139=>array('value'=>'mq','name'=>'Martinique'),140=>array('value'=>'mr','name'=>'Mauritania'),141=>array('value'=>'ms','name'=>'Montserrat'),142=>array('value'=>'mt','name'=>'Malta'),143=>array('value'=>'mu','name'=>'Mauritius'),144=>array('value'=>'mv','name'=>'Maldives'),145=>array('value'=>'mw','name'=>'Malawi'),146=>array('value'=>'mx','name'=>'Mexico'),147=>array('value'=>'my','name'=>'Malaysia'),148=>array('value'=>'mz','name'=>'Mozambique'),149=>array('value'=>'na','name'=>'Namibia'),150=>array('value'=>'nc','name'=>'New Caledonia'),151=>array('value'=>'ne','name'=>'Niger'),152=>array('value'=>'nf','name'=>'Norfolk Island'),153=>array('value'=>'ng','name'=>'Nigeria'),154=>array('value'=>'ni','name'=>'Nicaragua'),155=>array('value'=>'nl','name'=>'Netherlands'),156=>array('value'=>'no','name'=>'Norway'),157=>array('value'=>'np','name'=>'Nepal'),158=>array('value'=>'nr','name'=>'Nauru'),159=>array('value'=>'nu','name'=>'Niue'),160=>array('value'=>'nz','name'=>'New Zealand'),161=>array('value'=>'om','name'=>'Oman'),162=>array('value'=>'pa','name'=>'Panama'),163=>array('value'=>'pe','name'=>'Peru'),164=>array('value'=>'pf','name'=>'French Polynesia'),165=>array('value'=>'pg','name'=>'Papua New Guinea'),166=>array('value'=>'ph','name'=>'Philippines'),167=>array('value'=>'pk','name'=>'Pakistan'),168=>array('value'=>'pl','name'=>'Poland'),169=>array('value'=>'pm','name'=>'Saint Pierre And Miquelon'),170=>array('value'=>'pn','name'=>'Pitcairn'),171=>array('value'=>'pr','name'=>'Puerto Rico'),172=>array('value'=>'ps','name'=>'Palestinian Territory, Occupied'),173=>array('value'=>'pt','name'=>'Portugal'),174=>array('value'=>'pw','name'=>'Palau'),175=>array('value'=>'py','name'=>'Paraguay'),176=>array('value'=>'qa','name'=>'Qatar'),177=>array('value'=>'re','name'=>'Réunion'),178=>array('value'=>'ro','name'=>'Romania'),179=>array('value'=>'ru','name'=>'Russian Federation'),180=>array('value'=>'rw','name'=>'Rwanda'),181=>array('value'=>'sa','name'=>'Saudi Arabia'),182=>array('value'=>'sb','name'=>'Solomon Islands'),183=>array('value'=>'sc','name'=>'Seychelles'),184=>array('value'=>'sd','name'=>'Sudan'),185=>array('value'=>'se','name'=>'Sweden'),186=>array('value'=>'sg','name'=>'Singapore'),187=>array('value'=>'sh','name'=>'Saint Helena'),188=>array('value'=>'si','name'=>'Slovenia'),189=>array('value'=>'sk','name'=>'Slovakia'),190=>array('value'=>'sl','name'=>'Sierra Leone'),191=>array('value'=>'sm','name'=>'San Marino'),192=>array('value'=>'sn','name'=>'Senegal'),193=>array('value'=>'so','name'=>'Somalia'),194=>array('value'=>'sr','name'=>'Suriname'),195=>array('value'=>'st','name'=>'Sao Tome And Principe'),196=>array('value'=>'sv','name'=>'El Salvador'),197=>array('value'=>'sy','name'=>'Syrian Arab Republic'),198=>array('value'=>'sz','name'=>'Swaziland'),199=>array('value'=>'tc','name'=>'Turks And Caicos Islands'),200=>array('value'=>'td','name'=>'Chad'),201=>array('value'=>'tg','name'=>'Togo'),202=>array('value'=>'th','name'=>'Thailand'),203=>array('value'=>'tj','name'=>'Tajikistan'),204=>array('value'=>'tk','name'=>'Tokelau'),205=>array('value'=>'tl','name'=>'Timor-Leste'),206=>array('value'=>'tm','name'=>'Turkmenistan'),207=>array('value'=>'tn','name'=>'Tunisia'),208=>array('value'=>'to','name'=>'Tonga'),209=>array('value'=>'tr','name'=>'Turkey'),210=>array('value'=>'tt','name'=>'Trinidad And Tobago'),211=>array('value'=>'tv','name'=>'Tuvalu'),212=>array('value'=>'tw','name'=>'Taiwan, Province Of China'),213=>array('value'=>'tz','name'=>'Tanzania, United Republic Of'),214=>array('value'=>'ua','name'=>'Ukraine'),215=>array('value'=>'ug','name'=>'Uganda'),216=>array('value'=>'um','name'=>'United States Minor Outlying Islands'),217=>array('value'=>'us','name'=>'United States'),218=>array('value'=>'uy','name'=>'Uruguay'),219=>array('value'=>'uz','name'=>'Uzbekistan'),220=>array('value'=>'va','name'=>'Holy See (Vatican City State)'),221=>array('value'=>'vc','name'=>'Saint Vincent And The Grenadines'),222=>array('value'=>'ve','name'=>'Venezuela'),223=>array('value'=>'vg','name'=>'Virgin Islands, British'),224=>array('value'=>'vi','name'=>'Virgin Islands, U.S.'),225=>array('value'=>'vn','name'=>'Viet Nam'),226=>array('value'=>'vu','name'=>'Vanuatu'),227=>array('value'=>'wf','name'=>'Wallis And Futuna'),228=>array('value'=>'ws','name'=>'Samoa'),229=>array('value'=>'ye','name'=>'Yemen'),230=>array('value'=>'yt','name'=>'Mayotte'),231=>array('value'=>'yu','name'=>'Yugoslavia'),232=>array('value'=>'za','name'=>'South Africa'),233=>array('value'=>'zm','name'=>'Zambia'),234=>array('value'=>'zw','name'=>'Zimbabwe'));
?>