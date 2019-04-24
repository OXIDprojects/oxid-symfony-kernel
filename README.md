# OxidKernel

## Installation

```
composer req sioweb/oxid-kernel
```

## Beispiel

Das Modul [sioweb/oxid-api](https://github.com/Sioweb/OxidApi) basiert auf diesem OxidKernel und fügt eine REST-API hinzu, mit der Daten aus Oxid abgegriffen werden können. OxidKernel wird automatisch installiert, wenn `composer req sioweb/oxid-api` ausgeführt wird.

## Symfony-Bundles & Routen in Oxid

Um Bundles automatisch für Oxid zu registrieren, muss folgende `Extra`-Eigenschaft in der `composer.json` des Bundles hinterlegt werden. Damit müssen Benutzer das Bundle nicht erst von Hand in eine Datei schreiben. Es ist unbedingt notwendig, der Bundle-Klasse einen uniquen Namen zu geben, daher bitte wie folgt ausschreiben. Das API-Bundle nutzt beispielsweise `SiowebOxidApiBundle.php`.

```
"extra": {
    "oxid-kernel-plugin": "Your\\Namespace\\PluginName\\YourNamespacePluginNameBundle"
}
```

Bitte `Your\\Namespace` durch den eigenen Namespace ersetzen. Die Verzeichnis-Struktur sollte wie folgt aussehen:

- ROOT/
    - src/
        - Kernel/
            - Plugin.php
        - Resources/
            - config*
                - [routing|listener|services].yml
            - oxid
                - Module
                    Article.php
                - views/
                - metadata.php
        - YourNamespacePluginNameBundle.php
    - composer.json
    - README.md
    
In der composer.json kann dann angegeben werden, dass die Daten unter `src/Resources/oxid/` in das Module-Verzeichnis von Oxid installiert werden soll. Alle Namespaces die in den Module-Ordner kopiert werden, sollten als Namespace-Zusatz `Legacy` erhalten. Im API-Bundle lautet der Namespace beispielsweise `Sioweb\Oxid\Api\Legacy\`, gefolgt von der Ordnerstruktur nach PSR-4.

```
"extra": {
    "oxideshop": {
        "blacklist-filter": [
            "documentation/**/*.*"
        ],
        "source-directory": "./src/Resources/oxid",
        "target-directory": "sioweb/Api"
    },
    "oxid-kernel-plugin": "Your\\Namespace\\PluginName\\YourNamespacePluginNameBundle"
},
"autoload": {
    "psr-4": {
        "Your\\Namespace\\PluginName\\Legacy\\": "../../../source/modules/VENDOR NAME/PLUGIN NAME/",
        "Your\\Namespace\\PluginName\\": "src/"
    },
    "exclude-from-classmap": [
        "src/Resources/oxid"
    ]
}
