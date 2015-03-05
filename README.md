AttachFileBundle
====================

[![Build Status](https://scrutinizer-ci.com/g/it-blaster/attach-file-bundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/it-blaster/attach-file-bundle/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/it-blaster/attach-file-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/it-blaster/attach-file-bundle/?branch=master)

Вспомогательный бандл для работы с файлами на сайте. Есть возмжность прикреплять несколько файлов к одной сущности. Есть поддержка языковых версий.

Installation
------------

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

В `app/config/config.yml` необходимо указать путь до класса бихейвора <b>it_blaster_file</b> и подключить js виджета attach_file:

``` bash
 propel:
     ...
     behaviors:
         ...
         it_blaster_file: ItBlaster\AttachFileBundle\Behavior\AttachFileBehavior

sonata_admin:
    assets:
        javascripts:
            - bundles/itblasterattachfile/js/attach_file_admin.js
```

В файл app/config/routing.yml необходимо подключить роутинг-файл бандла:
``` bash
attach_file:
    resource: '@ItBlasterAttachFileBundle/Resources/config/routing.yml'
```

Usage
-----

В файле `schema.yml` подключите бихейвор <b>it_blaster_file</b>
``` xml
    <table name="example">
        <column name="id"           type="integer"  required="true" primaryKey="true" autoIncrement="true" />
        <column name="title"        type="varchar"  required="true" primaryString="true" />
        <column name="image"        type="integer" />

        <behavior name="it_blaster_file" >
            <parameter name="file_columns" value="image" />
        </behavior>
    </table>
```
В параметре <b>file_columns</b> необходимо указать имя поля изображения. В данном примере этим полем является поле <b>image</b>. Если к сущности необходимо прекреплять несколько файлов, названия полей в параметре <b>file_columns</b> нужно указать через запятую, например:
``` xml
    <table name="example">
        <column name="id"           type="integer"  required="true" primaryKey="true" autoIncrement="true" />
        <column name="title"        type="varchar"  required="true" primaryString="true" />
        <column name="logo"         type="integer" />
        <column name="sheet"        type="integer" />

        <behavior name="it_blaster_file" >
            <parameter name="file_columns" value="logo, sheet" />
        </behavior>
    </table>
```
Поля файлов должны иметь тип <b>integer</b>

Use i18n
-------
Если вы используете языковые версии на сайте на основе propel-бихейвора `i18n` и к каждому переводу необходимо прикреплять файл, то вам необходимо в основной таблице (<b>example</b>) указать параметр `i18n`, где будет имя поля файла, и в соответствующей таблице с переводами (<b>example_i18n</b>) укзать параметр `file_columns`, в котором будет то же самое значение поля файла. Пример:
``` xml
    <table name="example">
        <column name="id"           type="integer"  required="true" primaryKey="true" autoIncrement="true" />
        <column name="title"        type="varchar"  required="true" primaryString="true" />
        <column name="logo"         type="integer" />

        <behavior name="it_blaster_file" >
            <parameter name="file_columns" value="logo" />
        </behavior>
    </table>

    <table name="example_i18n">
        <behavior name="it_blaster_file" >
            <parameter name="file_columns" value="logo" />
        </behavior>
    </table>
```


Credits
-------

It-Blaster <it-blaster@yandex.ru>