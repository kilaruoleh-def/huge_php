# Использование Yii в качестве микро-framework'а

Yii можно легко использовать без функций, включенных в базовый и расширенный шаблоны приложений. Другими словами Yii уже является микро-каркасом. Не требуется иметь структуру каталогов предоставляемую этими шаблонами при работе с Yii.

Это особенно удобно, когда Вам не нужен весь пред-установленный шаблонный код, такой как `Assets` или `Views`. Одним из таких случаев является создание JSON API. В следующих разделах будет показано, как это сделать.

## Установка Yii

Создайте каталог для файлов проекта и смените рабочий каталог на этот путь. В примерах используются команды Unix, но аналогичные команды существуют и в Windows.

```bash
mkdir micro-app
cd micro-app
```

> Note: Для продолжения требуется немного знаний о Composer. Если Вы еще не знаете, как использовать Composer, пожалуйста, найдите время, чтобы прочитать [Руководство Composer](https://getcomposer.org/doc/00-intro.md).

Создайте файл `composer.json` в каталоге `micro-app` с помощью Вашего любимого редактора и добавьте следующее:

```json
{
    "require": {
        "yiisoft/yii2": "~2.0.0"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "https://asset-packagist.org"
        }
    ]
}
```

Сохраните файл и запустите команду `composer install`. Это установит framework со всеми его зависимостями.

## Создание структуры проекта

После того как Вы установили фреймворк, пришло время создать [входную точку](structure-entry-scripts.md) приложения. Точка входа - это самый первый файл, который будет выполнен при попытке открыть приложение. По соображениям безопасности рекомендуется поместить файл точки входа в отдельный каталог и сделать каталог корнем веб директории.

Создайте каталог `web` и поместите в него файл `index.php` со следующим содержимым:

```php 
<?php

// закомментируйте следующие две строки при использовании в рабочем режиме
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require __DIR__ . '/../config.php';
(new yii\web\Application($config))->run();
```

Также создайте файл с именем `config.php`, который будет содержать всю конфигурацию приложения:

```php
<?php
return [
    'id' => 'micro-app',
    // basePath (базовый путь) приложения будет каталог `micro-app`
    'basePath' => __DIR__,
    // это пространство имен где приложение будет искать все контроллеры
    'controllerNamespace' => 'micro\controllers',
    // установим псевдоним '@micro', чтобы включить автозагрузку классов из пространства имен 'micro'
    'aliases' => [
        '@micro' => __DIR__,
    ],
];
```

> Info: Несмотря на то, что конфигурация приложения может находиться в файле `index.php` рекомендуется
> содержать её в отдельном файле. Таким образом её можно также использовать и для консольного приложения, как показано ниже.

Теперь Ваш проект готов к наполнению кодом. Вы можете выбрать любую структуру каталогов, соответствующую пространству имен.

## Создание первого контроллера

Создайте каталог `controllers` и добавьте туда файл `SiteController.php`, который является контроллером по умолчанию, он будет обрабатывать запрос без пути.

```php
<?php

namespace micro\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return 'Hello World!';
    }
}
```

Если Вы хотите использовать другое имя для этого контроллера, Вы можете изменить его настроив [[yii\base\Application::$defaultRoute]].
Например, для `DefaultController` будет соответственно `'defaultRoute' => 'default/index'`.

На данный момент структура проекта должна выглядеть так:

```
micro-app/
├── composer.json
├── web/
    └── index.php
└── controllers/
    └── SiteController.php
```

Если Вы еще не настроили веб-сервер, Вы можете взглянуть на [примеры конфигурационных файлов веб-серверов](start-installation.md#configuring-web-servers).
Другой возможностью является использование команды `yii serve`, которая будет использовать встроенный веб-сервер PHP. Вы можете запустить её из каталога `micro-app/` через:

    vendor/bin/yii serve --docroot=./web

При открытии URL приложения в браузере, он теперь должен печатать "Hello World!", который был возвращен из `SiteController::actionIndex()`.

> Info: В нашем примере мы изменили пространство имен по умолчанию приложения с `app` на` micro`, чтобы продемонстрировать
> что Вы не привязаны к этому имени (в случае, если Вы считали, что это так), а затем скорректировали
> [[yii\base\Application::$controllerNamespace|controllers namespace]] и установили правильный псевдоним.

## Создание REST API

Чтобы продемонстрировать использование нашей "микроархитектуры", мы создадим простой REST API для сообщений.

Чтобы у API были данные для работы, нам нужна база данных. Добавим конфигурацию подключения базы данных
к конфигурации приложения:

```php
'components' => [
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'sqlite:@micro/database.sqlite',
    ],
],
```

> Info: Для простоты мы используем базу данных sqlite. Дополнительную информацию см. в [Руководство по базам данных](db-dao.md).

Затем, добавим [миграции базы данных](db-migrations.md) для создания таблицы сообщений.
Убедитесь, что у Вас есть отдельный файл конфигурации, как описано выше, нам это нужно для того, чтобы запустить консольные команды.
Запуск следующих команд создаст файл миграции и применит миграцию к базе данных:

    vendor/bin/yii migrate/create --appconfig=config.php create_post_table --fields="title:string,body:text"
    vendor/bin/yii migrate/up --appconfig=config.php

Создайте каталог `models` и файл` Post.php` в этом каталоге. Это код модели:

```php
<?php

namespace micro\models;

use yii\db\ActiveRecord;

class Post extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{posts}}';
    }
}
```

> Info: Созданная модель наследует класс ActiveRecord и представляет данные из таблицы `posts`.
> Для получения дополнительной информации обратитесь к [active record руководству](db-active-record.md).

Чтобы обслуживать сообщения в нашем API, добавьте `PostController` в` controllers`:

```php
<?php

namespace micro\controllers;

use yii\rest\ActiveController;

class PostController extends ActiveController
{
    public $modelClass = 'micro\models\Post';

    public function behaviors()
    {
        // удаляем rateLimiter, требуется для аутентификации пользователя
        $behaviors = parent::behaviors();
        unset($behaviors['rateLimiter']);
        return $behaviors;
    }
}
```

На этом этапе наш API предоставляет следующие URL-адреса:

- `/index.php?r=post` - список всех сообщений
- `/index.php?r=post/view&id=1` - просмотр сообщения с ID 1
- `/index.php?r=post/create` - создание сообщения
- `/index.php?r=post/update&id=1` - обновление сообщения с ID 1
- `/index.php?r=post/delete&id=1` - удаление сообщения с ID 1

Начиная с этого момента Вы можете посмотреть следующие руководства для дальнейшего развития своего приложения:

- API в настоящий момент принимает только urlencoded данные на вход. Чтобы сделать его настоящим JSON API, Вам
  необходимо настроить [[yii\web\JsonParser]].
- Чтобы сделать URL более дружественным, вам необходимо настроить маршрутизацию.
  См. [Руководство по маршрутизации REST](rest-routing.md) о том, как это сделать.
- Дополнительную информацию см. в разделе [Взгляд в будущее](start-looking-ahead.md).
