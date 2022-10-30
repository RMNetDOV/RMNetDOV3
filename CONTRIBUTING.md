# Prispevek k RM-Net - DOV CP
RM-Net - DOV CP je odprtokodni projekt in prispevki skupnosti so zelo dobrodošli. Če želite prispevati, se držite smernic.

Ta dokument je v razvoju in se bo nenehno izboljševal.

# Težave
* Preden odprete novo številko, uporabite funkcijo iskanja, da preverite, ali že ni poročila o napakah/zahteve za funkcije.
* Če poročate o napaki, prosimo, delite svoj OS in različico PHP (CLI).
* Če želite prijaviti več napak ali zahtevati več funkcij, odprite ločeno težavo za vsako od njih.

# Podružnice
* Ustvarite izdajo za vsak prispevek, ki ga želite dati.
* Ne postavljajte več prispevkov v eno vejo in združite zahteve. Vsak prispevek naj ima svojo vejo.
* Za svoj prispevek ne uporabljajte razvojne veje v svojem razcepljenem projektu. Za vsako težavo ustvarite ločeno vejo.
* Poimenujte svojo podružnico, npr. g. `6049-update-the-contributing-doc`, kjer je 6049 številka izdaje.

# Zahteve za združitev
Svoji zahtevi za združitev dajte opis, v katerem je na kratko navedeno, za kaj gre. Zahteve za združitev brez dobrega naslova ali z manjkajočim opisom bodo zamujale, ker je za nas več truda, da preverimo pomen izvedenih sprememb.
Še enkrat: ne postavljajte več stvari v eno zahtevo za združitev. Če na primer odpravite dve težavi, pri čemer ena vpliva na uporabnike apache in ena na poštne uporabnike, uporabite ločene težave in ločene zahteve za spajanje.
V eno zahtevo za združitev lahko združite več vprašanj, če imajo isto specifično temo, npr. g. če imate eno težavo, ki navaja, da manjka jezikovni vnos pri uporabnikih pošte, in drugo težavo, da manjka jezikovni vnos za konfiguracijo strežnika, lahko obe težavi postavite v eno vejo in združite zahtevo. V tem primeru obvezno vključite vse ID-je težav (če jih je več) v opis zahteve za združitev.
* Odprite težavo za napako, ki jo želite popraviti / funkcijo, ki jo želite implementirati
* Ko odprete težavo, potrdite svoje spremembe v svoji veji
* Upoštevajte težavo # pri vsaki objavi
* Posodobite dokumentacijo (Novi razvijalci ne bodo imeli dostopa do tega. Pošljite E-Mail na admin@rm-net.si)
* Dodajte prevode za vsak jezik
* Uporabite kratek naslov
* Napišite jasen opis - na primer, ko posodabljate smernice za prispevanje s težavo #6049: \
  "Posodobitev naših smernic za prispevanje \
  Zapre #6049"
* Zavedajte se, da ne moremo sprejeti zahtev za združitev, ki se ne držijo smernic za kodiranje. Pri tem moramo vztrajati, da bo koda čista in vzdržljiva.

# Nekaj ​​smernic za spletni razvoj s php.
-----------------------------------------------------
* Ne uporabljajte funkcij, ki niso podprte v PHP 5.4, za združljivost z izdajami OS LTS mora RM-Net - DOV CP podpirati PHP 5.4+
* Ne uporabljajte kratkih oznak. Kratka oznaka je `<?` in to povzroča zmedo z `<?xml` -> vedno uporabite `<?php`
* Ne uporabljajte imenskih prostorov
* Imena stolpcev v tabelah baze podatkov in imena tabel baze podatkov so z malimi črkami
* Razredi za vmesnik se nahajajo v interface/lib/classes/ in naloženi s funkcijami $app->uses() ali $app->load().
* Razredi za strežnik se nahajajo v server/lib/classes/ in naloženi s funkcijami $app->uses() ali $app->load().

### Vdolbine

Zamiki so vedno narejeni z zavihki. **Ne** uporabljajte presledkov.
Priporočljivo je, da IDE nastavite za prikaz zavihkov s širino 4 presledkov.

### Imena spremenljivk in metod / funkcij

Metode in funkcije morajo biti vedno napisane s kamelo začetnico. Spremenljivke in lastnosti naj bodo vedno napisane z malimi črkami.

**Pravilno:**
```php
class MyClass {
    private $issue_list = [];

    private function getMyValue() {

    }
}
```

**Napačno:**
```php
class my_class {
    private $IssueList = [];

    private function get_my_value() {

    }
}
```

### Bloki

#### Zavit oklepaj

Odprti zavit oklepaj mora biti vedno v isti vrstici kot prejšnji pogoj. Od zaključnega oklepaja so ločeni z enim presledkom.
Zaključni zavit oklepaj je vedno v ločeni vrstici za zadnjim stavkom v bloku. Edina izjema je blok do-while, kjer je logika obrnjena.

Zavite oklepaje je **vedno** treba uporabiti. Ne izpustite jih, tudi če je v ustreznem bloku le en stavek.

**Pravilno:**
```php
if($variable === true) {

}

while($condition) {

}

do {

} while($condition);
```

**Napačno:**
```php
if($variable === true){

}

if($variable === true)
{

}

if($variable === true)
   $x = 'no braces';

while($condition) { }
```

#### Kratek slog

Dovoljena je uporaba kratkega sloga pogojnih dodelitev, vendar ne sme vplivati ​​na berljivost, npr. g. ne bodo ugnezdeni.

**Dovoljeno:**
```php
$a = 0;
if($condition === true) {
    $a = 1;
}

$a = ($condition === true ? 1 : 0);
```

**Nedovoljeno:**
```php
$x = ($condition === true ? ($further == 'foo' ? true : false) : true);
```


#### Presledki in oklepaji

Pravila za uporabo prostorov so:
- brez presledka za `if`/`while` itd. in naslednjim začetnim oklepajem
- en presledek za zapiranjem oklepaja in pred odpiranjem zavitega oklepaja
- brez presledkov na koncu vrstice
- brez presledkov za odpiranjem oklepaja in pred zapiranjem oklepaja
- en presledek pred in za primerjalniki

**Pravilno:**
```php
if($variable === $condition) {

}

while(($condition !== false || $condition2 === true) && $n <= 15) {
    $n++;
}
```

**Napačno:**
```php
if ($variable===$condition) {

}

while(($condition!==false||$condition2===true))&&$n<=15){

}
```

#### Nove vrstice znotraj pogojev

Pogoje lahko razdelite v ločene vrstice, če to pozitivno vpliva na berljivost.

```php
if($condition === true && ($state === 'completed' || $state === 'pending') && ($processed_by !== null || $process_time < time())) {

}
```
lahko zapišemo tudi kot
```php
if($condition === true
    && ($state === 'completed' || $state === 'pending')
    && ($processed_by !== null || $process_time < time())
    ) {

}
```
Tega se ne sme zlorabljati, npr. g. naslednje ni dovoljeno:

```php
if($a == 1
    || $b == 2) {

    }
```

### Nizi

#### Kratka sintaksa

Prosimo, **uporabite** sintakso kratke matrike. Opustili smo staro sintakso matrike.

**Pravilno**:
```php
$var = [];

$var2 = [
    'conf' => [
        'setting1' => 'value1'
    ]
];
```

**Napačno:**
```php
$var = array();

$var2 = array(
    'conf' => array(
        'setting1' => 'value1'
    )
);
```

#### Presledki in nove vrstice

Pri definiranju prazne matrike morata biti oba oklepaja v isti vrstici. Pri definiranju matrike z vrednostmi je slog odvisen od vrednosti, ki jih boste dodelili.

##### Seznam vrednosti

Ko definirate matriko s seznamom vrednosti, npr. g. številke ali imena, morajo biti v isti vrstici kot oklepaji brez uporabe novih vrstic, če vrstica ne presega skupnega števila znakov približno 90. Za vsako vejico mora biti en presledek.

##### Ugnezdeno polje

Pri definiranju ugnezdene matrike mora biti samo začetni oklepaj v isti vrstici. Zaključni oklepaj mora biti v ločeni vrstici z zamikom `zavihki * nivo matrike`.

##### Primeri

```php
// empty array
$a = [];

// array with list of values
$array = [4, 3, 76, 12];

// array with long list of values
$array = [
    'This is one entry', 'This is a second one', 'Another one', 'Further entries', 'foo', 'bar', 34, 42, $variable, // newline here for better readability
    'Next entry', 'the last entry'
];

// nested array
$array = [
    'conf' => [
        'level' => 1,
        'settings' => [
            'window' => 'open',
            'door' => 'closed
        ]
    ]
];
```

**Not-to-dos:**
```php
$array=[
];

$array = [
    1,
    4,
    35,
    23,
    345,
    11,
    221,
    'further',
    '...'
];

$array=['conf'=>['settings'=>['window' => 'open', 'door' => 'closed]]];
```

### Nizi

Kadarkoli je mogoče, uporabite enojne narekovaje `'` namesto dvojnih narekovajev `"`. Poskusite ne vdelati spremenljivk v niz. Namesto tega jih povežite.

**Pravilno:**
```php
// simple text
$var = 'This is a text';

// array index
$array['index'] = 'value';

// text with variables
$var = 'This is a text with ' . $value . ' values inside and at the end: ' . $sum_value;

// dynamic array index
$idx = 'index' . $key;
$value = $array[$idx];
```

**Napačno:**
```php
// simple text
$var = "This is a text";

// array index
$array["index"] = 'value';

// text with variables
$var = "This is a text with $value values inside and at the end: {$sum_value}";

// dynamic array index
$value = $array['index' . $key];
$value = $array["index{$key}"];
```

# Kam shraniti nastavitve po meri
## Nastavitve vmesnika
Priporočeno mesto za shranjevanje globalnih nastavitev vmesnika je globalni konfiguracijski sistem v slogu ini
(za nastavitev privzetih vrednosti glejte datoteko system.ini.master v install/tpl/). Datoteka z nastavitvami
se shrani v bazo podatkov RM-Net - DOV CP. Do nastavitev lahko dostopate s funkcijo:
```
$app->uses('ini_parser,getconf');
$interface_settings = $app->getconf->get_global_config('modulename');
```

kjer ime modula ustreza razdelku konfiguracije v datoteki system.ini.master.
Če želite omogočiti urejanje nastavitev pod System > interface config, dodajte novo konfiguracijo
polja v datoteko vmesnik/web/admin/form/system_config.tform.php in ustrezna
datoteko tempalte v podmapi s predlogami skrbniškega modula.

## Nastavitve strežnika
Nastavitve strežnika so shranjene v konfiguracijskem sistemu strežnika v slogu ini (glejte datoteko predloge server.ini.master)
Datoteka z nastavitvami se shrani v bazi podatkov rmnetdov v tabeli strežnika. Nastavitve so lahko
dostopen s funkcijo $app->getconf->get_server_config(....)

Primer dostopa do spletne konfiguracije:

```
$app->uses('ini_parser,getconf');
$web_config = $app->getconf->get_server_config($server_id,'web');
```

# Spoznajte validatorje obrazcev
V interface/lib/classes/tform.inc.php so validatorji obrazcev za lažje preverjanje obrazcev.
Preberite o: REGEX,UNIQUE,NOTEMPTY,ISEMAIL,ISINT,ISPOSITIVE,ISIPV4,ISIPV6,ISIP,CUSTOM