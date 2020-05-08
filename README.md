### OxidKernel

#### Hinweis Oxid 6.2

Der Kernel kann nicht für Oxid 6.2 genutzt werden, da die Symfony-Versionen sich gegenseitig ausschließen. Voraussichtlich, kann der Kernel ab 6.3 problemfrei eingesetzt werden.

#### Dokumentation

Die Dokumentation wird im Github-Wiki geschrieben: https://github.com/OXIDprojects/oxid-symfony-kernel/wiki

#### Liste der Module die den Kernel verwenden

- [Oxid Module Installer (In Entwicklung)](https://github.com/OXIDprojects/oxid-module-installer)
- [Sioweb/OxidApi (Demomodul)](https://github.com/Sioweb/OxidApi)

#### Installation

Falls nicht schon gesehen, installiere [Composer](https://getcomposer.org/download/) auf deinen Rechner. Öffne die Konsole im Root von Oxid und führe folgenden Befehl aus:

```sh
composer require oxid-community/symfony-kernel
```
