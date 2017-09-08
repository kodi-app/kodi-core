# KodiApp

A KodiApp egy általános célú PHP keretrendszer webes alkalmazások fejlesztéséhez, amely erősen támogatja a kód 
újrahasznosítást.

## KodiApp-Core

A KodiApp-Core komponens a KodiApp alap komponense, amely minden esetben szükséges egy alkalmazás futtatásához és 
tartalmazza az összes funkcionalitást, amely szükséges egy alapszintű web alkalmazás futtatásához. Emellett támogatja 
már korábban elkészített szolgáltatások és modulok beillesztését az alkalmazásba így biztosítva az 
újrafelhasználhatóságot.

### KodiApp-Core telepítése 
Az alkalmazás telepítése composer-en keresztül lehetséges:

```bash
composer require kodi-app/kodi-core
```

### KodiApp-Core indítása 

A KodiApp indításához az Application singleton példány run metódusát kell meghívni, amely paraméterben kapja meg a
futtatandó alkalmazás aktuális konfigurációját egy monolitikus tömbben, ami minden beállítást tartalmaz.

**index.php**
```php
require 'vendor/autoload.php';

use KodiCore\Core\KodiConf;

$application = \KodiCore\Application::getInstance();
$application->run([
    KodiConf::ENVIRONMENT => [
        "mode"                  => KodiConf::ENV_DEVELOPMENT,
        "controller_namespace"  => "KodiTest\\Controller\\"
    ],
    KodiConf::ROUTES => [
        "handleIndex" => [
            "method"    =>  "GET",
            "url"       =>  "/",
            "handler"   =>  "TestController::test",
        ],
    ]
]);
```
**TestController.php**
```php
namespace KodiTest\Controller;

use KodiCore\Application;

class TestController
{
    /**
     * @return string
     */
    public function test() {
        return "Hello world!";
    }
}
```


### KodiApp-Core Configuration

A KodiApp egy tömbben várja az összes beállítást, amelyre szükség van az alkalmazás futtatásához. Az alábbiakban a konfigurációs paramétereket fogjuk bemutatni.

#### Environment 
A környezeti változókba azokat a beállításokat érdemes elhelyezni, amelyek az alkalmazás során konstans értékek és a futtatás során több ponton is szükségesek, például: mode,timezone,loglevel, stb...

```php
$application = \KodiCore\Application::getInstance();
$application->run([
    // ...
    KodiConf::ENVIRONMENT => [
        "mode"                  => KodiConf::ENV_DEVELOPMENT,
        "controller_namespace"  => "KodiTest\\Controller\\",
        "timezone"              => "Europe/London"
    ],
    // ...
]);
```
Kötelező mezők:
- _mode_: Az alkalmazás development vagy production módban van-e. (KodiConf::ENV_DEVELOPMENT | KodiConf::ENV_DEVELOPMENT)
- _controller_namespace_: A névtér neve, amely tartalmazza az összes Controller osztályt, amelyek a különböző kérések kezeléséért felelnek. A kérések (url-ek) és Controllerek összerendeléséről részletesen a ROUTES alfejezetben olvashatsz

A környezeti változókat a következő metódushíváson keresztül bárhol el lehet érni:
```php
$timezone = Application::getEnv("timezone")
```
(A `mode` változó értékét az `Application::getEnvMode()` híváson keresztül is el lehet érni.)

#### Hooks

A Hook-ok olyan elemei (kódrészletei) egy alkalmazásnak, amelyeknek minden kérés kezelésénél le kell futniuk. Jó példa erre egy SecurityHook, amely illetéktelen lekérdezést érzékelve meg tudja szakítani egy kérés feldolgozását. A Hook-ok lefutási sorrendjük megegyezik azzal a sorrenddel, amit a konfigurációs tömbben beállítottunk. 

```php
$application->run([
    // ...
    KodiConf::HOOKS => [
        \KodiCore\Hook\DummyHook::class,
        [
            "class_name" => \KodiCore\Hook\RedirectHook::class,
            "parameters" => [
                "redirect_url"  => "http://google.com"
            ]
        ],
        // ...
    ],
    // ...
]);
```
A Hook-okat a KodiConf::HOOKS kulcs alatt kell beállítani. Egy Hook-ra szimplán az osztály nevével is lehet hivatkozni (pl.: DummyHook) vagy ha szükséges paraméterek átadása, akkor a `class_name` és `parameters` páros segítségével lehet beállítani (pl.: RedirectHook).

Jelenleg támogatott Hook-ok:
- _RedirectHook_: A paraméterben kapott `redirect_url`-re irányítja át a felhasználót
- _HttpsRedirectHook_: Kikényszeríti a HTTPS protokoll használatát a felhasználó böngészőjétől

Ha saját Hook-ot szeretnél készíteni, akkor erről részeletesen a Saját Hook készítése alfejezetben olvashatsz.

#### Services
A szolgálatások (Services) olyan komponensei az alkalmazásnak, amelyekre nincs mindig szükség így nem kell őket minden alkalommal betölteni, viszont jól elhatárolható feladattal és felelőséggel rendelkeznek. Ilyen például egy adatbázis kapcsolat felépítése vagy a html tartalom renderelése.
Egy Service-re szimplán az osztály nevével is lehet hivatkozni vagy ha szükséges paraméterek átadása, akkor a `class_name` és `parameters` páros segítségével lehet beállítani (pl.: TwigServiceProvider).

```php
$application->run([
    // ...
    KodiConf::SERVICES => [
        [
            "class_name" => TwigServiceProvider::class,
            "parameters" => [
                Twig::TWIG_PATH => "/src/KodiTest/View",
                Twig::PAGE_TEMPLATE_PATH => "/frame/frame.twig"
            ]

        ]
    ],
    // ...
]);
```

Jelenleg támogatott Service-ek:
- _TwigServiceProvider_: Html tartalmat renderelő szolgáltatás
- _PandabaseServiceProvider_: Pandabase ORM szolgáltatás


A különböző szolgáltatásokhoz legkönnyebben a következő metódushíváson keresztül lehet hozzáférni:
```php
/** @var Twig $twig **/
$twig = Application::get("twig")
```

Ha saját Service-t szeretnél készíteni, akkor erről részeletesen a Saját Service készítése alfejezetben olvashatsz.

#### Modules

// TODO

#### Routes

A Routes paraméter alatt kell az alkalmazás által kiszolgált url-ket definiálni a következő struktúrában:

```php
    "urlIdentifier" => [
        "method"    =>  "GET", // HTTP method (GET|POST|PUT|DELETE)
        "url"       =>  "/",
        "handler"   =>  "[ControllerClassName]::[methodName]",
    ]
```
Példa az index kiszolgálására:
```php
$application->run([
    // ...
    KodiConf::ROUTES => [
        "handleIndex" => [
            "method"    =>  "GET",
            "url"       =>  "/",
            "handler"   =>  "TestController::test",
        ],
        "getUser" => [
            "method"    =>  "GET",
            "url"       =>  "/user/{user_id:[0-9]+}",
            "handler"   =>  "UserController::getUser",
        ],
        // ...
    ]
    // ...
]);
```

Az url paraméter esetén lehetséges különböző paraméterek definiálása (pl.: user_id). Ezeket a kérést kezelő Controller megfelelő metódusa egy asszociatív tömbben kap meg. Az url paramétereknél `:`-tal elválasztva megadható reguláris kifejezés is, amelynek teljesülnie kell, hogy meghívódjon a handlerben megadott metódus! A url-k beállításáról részletesebben [itt tudsz](https://github.com/nikic/FastRoute#defining-routes) olvasni.

#### Router

Egy Router feladata, hogy a beérkező kéréseket a megfelelő Controller megfelelő metódusához irányítsa. 
A KodiApp-ban alapméretezetten ezt a funkcionalitást a SimpleRouter osztály valósítja meg, emiatt, ha nem szeretnénk lecserélni a SimpleRouter által biztosított funkcionalitást a KodiConf::ROUTER paraméter elhagyható.

```php
$application->run([
    // ...
    KodiConf::ROUTER => [
        "class_name" => \KodiCore\Core\Router\SimpleRouter::class,
        "parameters" => []
    ],
    // ...
]);
```