#AttachFileBundle

[![Build Status](https://scrutinizer-ci.com/g/it-blaster/attach-file-bundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/it-blaster/attach-file-bundle/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/it-blaster/attach-file-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/it-blaster/attach-file-bundle/?branch=master)

Вспомогательный бандл для работы с файлами на сайте. Есть возмжность прикреплять несколько файлов к одной сущности. Есть поддержка языковых версий.

## Установка

Добавьте <b>ItBlasterAttachFileBundle</b> в `composer.json`:

```js
{
    "require": {
        "it-blaster/attach-file-bundle": "dev-master"
	},
}
```

Теперь запустите композер, чтобы скачать бандл командой:

``` bash
$ php composer.phar update it-blaster/attach-file-bundle
```

Композер установит бандл в папку проекта `vendor/it-blaster/attach-file-bundle`.

Далее подключите бандл в ядре `AppKernel.php`:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new ItBlaster\AttachFileBundle\ItBlasterAttachFileBundle(),
    );
}
```

## Использование