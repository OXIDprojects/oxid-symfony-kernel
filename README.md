# OxidKernel

## Alpha-Release

**Hinweis:** Dieses Modul befindet sich in der Entwicklung. Benutz es besser noch nicht ohne Rücksprache.

## Installation

```
composer req sioweb/oxid-kernel
```

## Was kannst du damit tun?

Alles was Symfony bietet, oder zumindest alles, was die Symfony-Version von Oxid bietet. Die meisten Symfony 3 Komponenten sollten funktionieren, versuche aber bitte nicht, die Oxid-Tabellen mit Entities zu nutzen. Das Oxid-Framework sollte weitgehends verwendet werden, um Daten zu verarbeiten oder zu formatieren. Die machen das schon ganz gut. OxidKernel hat erst dann einen Vorteil, wenn es um Komminikation mit anderen Systemen etc. geht, oder du mit Komposer bestehende Bundles hinzufügen möchtest.

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
```

In der Datei `Plugin.php` können Routen und Konfigurationen geladen werden.

### Configuration

Die Config wird weitgehends automatisch eingerichtet. Nach der Installation wird im Root des Shops das Verzeichnis `/kernel/` angelegt. Hier können dann die Symfony-Typischen Configs etc. verwendet werden.

#### parameters.yml & Datenbank?!

Die Datei parameters.yml wird automatisch unter `/kernel/config/parameters.yml` eingerichtet. Sämtliche Einstellungen aus `/source/config.inc.php` werden in lowercase übernommen und gespeichert.

Datenbank-Parameter werden automatisch in Symfonykonforme Namen umgewandelt.

#### Eigene Configs?

Unter `/kernel/config/` kann die Datei `config_prod.yml` angelegt werden. OxidKernel lädt diese dann nach. Später wird vermutlich auch eine `config_dev.yml` möglich sein. Bis auf weiteres, gibt es allerdings nur die 'prod'-Environment.

### Aber was ist mit den Oxid-Routen?

OxidKernel wird den regulären Betrieb nicht stören. Ein Eventlistener prüft zunächst, ob eine Route überhaupt in einer YAML-Datei hinterlegt wurde und ob diese geladen werden kann. Sollte die Route nicht gefunden werden, wird Oxid alles weitere Überlassen. Daher würde ich vorschlagen, dass die Routen sich besser nicht mit den Kategorien in Oxid überschneiden.
