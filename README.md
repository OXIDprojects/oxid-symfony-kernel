# OxidKernel

## Installation

```
composer req sioweb/oxid-kernel
```

## Beispiel

Das Modul [sioweb/oxid-api](https://github.com/Sioweb/OxidApi) basiert auf diesem OxidKernel und fügt eine REST-API hinzu, mit der Daten aus Oxid abgegriffen werden können. OxidKernel wird automatisch installiert, wenn `composer req sioweb/oxid-api` ausgeführt wird.

## Symfony-Bundles & Routen in Oxid

Um Bundles automatisch für Oxid zu registrieren, muss folgende `Extra`-Eigenschaft in der `composer.json` des Bundles hinterlegt werden. Damit müssen Benutzer das Bundle nicht erst von Hand in eine Datei schreiben.

```
"extra": {
    "oxid-kernel-plugin": "Your\\Namespace\\OxidKernel\\Plugin"
},
```

Bitte `Your\\Namespace` durch den eigenen Namespace ersetzen. Die Verzeichnis-Struktur sollte wie folgt aussehen:

- ROOT/
    - src/
        - OxidKernel
            Plugin.php
        - Resources
            - config*
                - [routing|listener|services].yml
            - oxid
                - Module
                    Article.php
                - views/
                - metadata.php
    - composer.json
    - README.md
    
