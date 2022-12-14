ルーティングと URL 生成
=======================

Yii のアプリケーションがリクエストされた URL の処理を開始するときに、最初に実行するステップは URL を解析して [ルート](structure-controllers.md#routes) にすることです。
次に、リクエストを処理するために、このルートを使って、対応する [コントローラ・アクション](structure-controllers.md) のインスタンスが作成されます。
このプロセスの全体が *ルーティング* と呼ばれます。

ルーティングの逆のプロセスが *URL 生成* と呼ばれます。
これは、与えられたルートとそれに結び付けられたクエリ・パラメータから URL を生成するものです。
生成された URL が後でリクエストされたときには、ルーティングのプロセスがその URL を解決して元のルートとクエリ・パラメータに戻すことが出来ます。

ルーティングと URL 生成について主たる役割を果たすのが `urlManager` [アプリケーション・コンポーネント](structure-application-components.md) として登録されている
[[yii\web\UrlManager|URL マネージャ]] です。
[[yii\web\UrlManager|URL マネージャ]] は、入ってくるリクエストをルートとそれに結び付けられたクエリ・パラメータとして解析するための [[yii\web\UrlManager::parseRequest()|parseRequest()]] メソッドと、
与えられたルートとそれに結び付けられたクエリ・パラメータから URL を生成するための [[yii\web\UrlManager::createUrl()|createUrl()]]
メソッドを提供します。

アプリケーション構成情報の `urlManager` コンポーネントを構成することによって、既存のアプリケーション・コードを修正することなく、
任意の URL 形式をアプリケーションに認識させることが出来ます。
例えば、`post/view` アクションのための URL を生成するためには、次のコードを使うことが出来ます。

```php
use yii\helpers\Url;

// Url::to() は UrlManager::createUrl() を呼び出して URL を生成します
$url = Url::to(['post/view', 'id' => 100]);
```

このコードによって生成される URL は、`urlManager` の構成に応じて、下記のどれか (またはその他) の形式になります。
そして、こうして生成された URL が後でリクエストされた場合には、解析されて元のルートとクエリ・パラメータの値に戻されます。

```
/index.php?r=post%2Fview&id=100
/index.php/post/100
/posts/100
```


## URL 形式 <span id="url-formats"></span>

[[yii\web\UrlManager|URL マネージャ]] は二つの URL 形式をサポートします。すなわち、

- デフォルトの URL 形式と、
- 綺麗な URL (プリティ URL) の 形式。

デフォルトの URL 形式は、`r` という [[yii\web\UrlManager::$routeParam|クエリ・パラメータ]] を使用してルートを表し、
通常のクエリ・パラメータを使用してルートに結び付けられたクエリ・パラメータを表します。
例えば、`/index.php?r=post/view&id=100` という URL は、`post/view` というルートと、`id` というクエリ・パラメータが 100 であることを表します。
デフォルトの URL 形式は、[[yii\web\UrlManager|URL マネージャ]] についての構成を何も必要とせず、ウェブ・サーバの設定がどのようなものでも動作します。

綺麗な URL 形式は、エントリ・スクリプトの名前に続く追加のパスを使用して、ルートとそれに結び付けられたクエリ・パラメータを表します。
例えば、`/index.php/post/100` という URL の追加のパスは `/post/100` ですが、
適切な [[yii\web\UrlManager::rules|URL 規則]] があれば、この追加のパスが `post/view` というルートと `id` のクエリ・パラメータ `100` を表すものとすることが出来ます。
綺麗な URL 形式を使用するためには、URL をどのように表現すべきかという実際の要求に従って、
一連の [[yii\web\UrlManager::rules|URL 規則]] を設計する必要があります。

この二つの URL 形式は、[[yii\web\UrlManager|URL マネージャ]] の [[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]] プロパティを ON/OFF することによって、
他のアプリケーション・コードを少しも変えることなく、切り替えることが出来ます。


## ルーティング <span id="routing"></span>

ルーティングは二つのステップを含みます。

- まず、入ってくるリクエストが解析されて、ルートとそれに結び付けられたクエリ・パラメータに分解されます。
- そして、解析されたルートに対応する [コントローラ・アクション](structure-controllers.md#actions)
がリクエストを処理するために生成されます。

デフォルトの URL 形式を使っている場合は、リクエストからルートを解析することは、
`r` という名前の `GET` クエリ・パラメータを取得するだけの簡単なことです。

綺麗な URL 形式を使っている場合は、[[yii\web\UrlManager|URL マネージャ]] が、登録されている [[yii\web\UrlManager::rules|URL 規則]] を調べます。
合致する規則が見つかれば、リクエストをルートに解決することが出来ます。
合致する規則が見つからなかったら、[[yii\web\NotFoundHttpException]] 例外が投げられます。

いったんリクエストからルートが解析されたら、今度はルートによって特定されるコントローラ・アクションを生成する番です。
ルートはその中にあるスラッシュによって複数の部分に分けられます。例えば、`site/index` は `site` と `index` に分割されます。
その各部分は、モジュール、コントローラ、または、アクションを参照する ID です。
アプリケーションは、ルートの最初の部分の ID から始めて、下記のステップを踏んで、
モジュール (もし有れば)、コントローラ、アクションを生成します。

1. アプリケーションをカレント・モジュールとして設定します。
2. カレント・モジュールの [[yii\base\Module::controllerMap|コントローラ・マップ]] が現在の ID を含むかどうかを調べます。
   含んでいる場合は、マップの中で見つかった構成情報に従ってコントローラのオブジェクトが生成されます。
   そして、ステップ 5 に跳んで、ルートの残りの部分を処理します。
3. 現在の ID がカレント・モジュールの [[yii\base\Module::modules|modules]] プロパティのリストに挙げられたモジュールを指すものかどうかを調べます。
   もしそうであれば、モジュールのリストで見つかった構成情報に従ってモジュールが生成されます。
   そして、新しく生成されたモジュールのコンテキストのもとで、ステップ 2 に戻って、ルートの次の部分を処理します。
4. 現在の ID を [コントローラ ID](structure-controllers.md#controller-ids) として扱ってコントローラ・オブジェクトを生成します。
   そしてルートの残りの部分を持って次のステップに進みます。
5. コントローラは、[[yii\base\Controller::actions()|アクション・マップ]] の中に現在の ID があるかどうかを調べます。
   もし有れば、マップの中で見つかった構成情報に従ってアクションを生成します。
   もし無ければ、現在の [アクション ID](structure-controllers.md#action-ids) に対応するアクション・メソッドで定義されるインライン・アクションを生成しようと試みます。

上記のステップの中で、何かエラーが発生すると、[[yii\web\NotFoundHttpException]] が投げられて、
ルーティングのプロセスが失敗したことが示されます。


### デフォルト・ルート <span id="default-route"></span>

リクエストから解析されたルートが空になった場合は、いわゆる *デフォルト・ルート* が代りに使用されることになります。
デフォルトでは、デフォルト・ルートは `site/index` であり、`site` コントローラの `index` アクションを指します。
デフォルト・ルートは、次のように、アプリケーションの構成情報の中でアプリケーションの [[yii\web\Application::defaultRoute|defaultRoute]]
プロパティを構成することによって、カスタマイズすることが出来ます。

```php
[
    // ...
    'defaultRoute' => 'main/index',
];
```

アプリケーションのデフォルト・ルートと同じく、モジュールにもデフォルト・ルートがあります。
従って、例えば、`user` というモジュールがあって、リクエストの解析結果が `user` というルートになった場合、このモジュールの [[yii\base\Module::defaultRoute|defaultRoute]] がコントローラを決定するのに使用されます。
デフォルトでは、このコントローラの名前は `default` となります。
[[yii\base\Module::defaultRoute|defaultRoute]] でアクションが指定されていない場合は、コントローラの [[yii\base\Controller::defaultAction|defaultAction]] プロパティがアクションを決定するのに使用されます。
この例の場合だと、完全なルートは `user/default/index` となります。


### `catchAll` ルート <span id="catchall-route"></span>

たまには、ウェブ・アプリケーションを一時的にメンテナンス・モードにして、全てのリクエストに対して同じ「お知らせ」のページを表示したいことがあるでしょう。
この目的を達する方法はたくさんありますが、最も簡単な方法の一つは、次のように、
アプリケーションの構成情報の中で [[yii\web\Application::catchAll]] プロパティを構成することです。

```php
[
    // ...
    'catchAll' => ['site/offline'],
];
```

上記の構成によって、入ってくる全てのリクエストを処理するために `site/offline` アクションが使われるようになります。

`catchAll` プロパティは配列を取り、最初の要素はルートを指定し、残りの要素 (「名前-値」のペア) は
[アクションのパラメータ](structure-controllers.md#action-parameters) を指定するものでなければなりません。

> Info: このプロパティを有効にすると、開発環境で [デバッグ・ツール・バー](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide-ja/README.md)が
> 動作しなくなります。


## URL を生成する <span id="creating-urls"></span>

Yii は、与えられたルートとそれに結び付けられたクエリ・パラメータからさまざまな URL を生成する
[[yii\helpers\Url::to()]] というヘルパ・メソッドを提供しています。例えば、

```php
use yii\helpers\Url;

// ルートへの URL を生成する: /index.php?r=post%2Findex
echo Url::to(['post/index']);

// パラメータを持つルートへの URL を生成する: /index.php?r=post%2Fview&id=100
echo Url::to(['post/view', 'id' => 100]);

// アンカー付きの URL を生成する: /index.php?r=post%2Fview&id=100#content
echo Url::to(['post/view', 'id' => 100, '#' => 'content']);

// 絶対 URL を生成する: https://www.example.com/index.php?r=post%2Findex
echo Url::to(['post/index'], true);

// https スキームを使って絶対 URL を生成する: https://www.example.com/index.php?r=post%2Findex
echo Url::to(['post/index'], 'https');
```

上記の例では、デフォルトの URL 形式が使われていると仮定していることに注意してください。
綺麗な URL 形式が有効になっている場合は、生成される URL は、使われている [[yii\web\UrlManager::rules|URL 規則]] に従って、異なるものになります。

[[yii\helpers\Url::to()]] メソッドに渡されるルートの意味は、コンテキストに依存します。
ルートは *相対* ルートか *絶対* ルートかのどちらかであり、下記の規則によって正規化されます。

- ルートが空文字列である場合は、現在リクエストされている [[yii\web\Controller::route|ルート]] が使用されます。
- ルートがスラッシュを全く含まない場合は、カレント・コントローラのアクション ID であると見なされて、
   カレント・コントローラの [[\yii\web\Controller::uniqueId|uniqueId]] の値が前置されます。
- ルートが先頭にスラッシュを含まない場合は、カレント・モジュールに対する相対ルートと見なされて、
   カレント・モジュールの [[\yii\base\Module::uniqueId|uniqueId]] の値が前置されます。

バージョン 2.0.2 以降では、[エイリアス](concept-aliases.md) の形式でルートを指定することが出来ます。
その場合は、エイリアスが最初に実際のルートに変換され、
そのルートが上記の規則に従って絶対ルートに変換されます。

例えば、カレント・モジュールが `admin` であり、カレント・コントローラが `post` であると仮定すると、

```php
use yii\helpers\Url;

// 現在リクエストされているルート: /index.php?r=admin%2Fpost%2Findex
echo Url::to(['']);

// アクション ID だけの相対ルート: /index.php?r=admin%2Fpost%2Findex
echo Url::to(['index']);

// 相対ルート: /index.php?r=admin%2Fpost%2Findex
echo Url::to(['post/index']);

// 絶対ルート: /index.php?r=post%2Findex
echo Url::to(['/post/index']);

// "/post/index" と定義されているエイリアス "@posts"を使用: /index.php?r=post%2Findex
echo Url::to(['@posts']);
```

[[yii\helpers\Url::to()]] メソッドは、[[yii\web\UrlManager|URL マネージャ]] の
[[yii\web\UrlManager::createUrl()|createUrl()]] メソッド、および、[[yii\web\UrlManager::createAbsoluteUrl()|createAbsoluteUrl()]]
を呼び出すことによって実装されています。
次に続くいくつかの項では、[[yii\web\UrlManager|URL マネージャ]] を構成して、生成される URL の形式をカスタマイズする方法を説明します。

[[yii\helpers\Url::to()]] メソッドは、特定のルートとの関係を持た**ない** URL の生成もサポートしています。
その場合、最初のパラメータには、配列を渡す代りに文字列を渡さなければなりません。例えば、
 
```php
use yii\helpers\Url;

// 現在リクエストされている URL: /index.php?r=admin%2Fpost%2Findex
echo Url::to();

// エイリアス化された URL: https://example.com
Yii::setAlias('@example', 'https://example.com/');
echo Url::to('@example');

// 絶対 URL: https://example.com/images/logo.gif
echo Url::to('/images/logo.gif', true);
```

`to()` メソッドの他にも、[[yii\helpers\Url]]` ヘルパ・クラスは、便利な URL 生成メソッドをいくつか提供しています。
例えば、

```php
use yii\helpers\Url;

// ホームページの URL: /index.php?r=site%2Findex
echo Url::home();

// ベース URL。アプリケーションがウェブ・ルートのサブ・ディレクトリに配置されているときに便利
echo Url::base();

// 現在リクエストされている URL の canonical URL。
// https://en.wikipedia.org/wiki/Canonical_link_element を参照
echo Url::canonical();

// 現在リクエストされている URL を記憶し、それを後のリクエストの中で呼び戻す。
Url::remember();
echo Url::previous();
```


## 綺麗な URL を使う <span id="using-pretty-urls"></span>

綺麗な URL を使うためには、アプリケーションの構成情報の中で `urlManager` コンポーネントを次のように構成します。

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

[[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]] プロパティは、綺麗な URL 形式の有効/無効を切り替えますので、必須です。
その他のプロパティはオプションですが、上記で示されている構成が最もよく用いられているものです。

* [[yii\web\UrlManager::showScriptName|showScriptName]]: このプロパティは、生成される URL にエントリ・スクリプトを含めるべきかどうかを決定します。
  例えば、このプロパティを `false` にすると、`/index.php/post/100` という URL を生成する代りに、
  `/post/100` という URL を生成することが出来ます。
* [[yii\web\UrlManager::enableStrictParsing|enableStrictParsing]]: このプロパティは、厳密なリクエスト解析を有効にするかどうかを決定します。
  厳密な解析が有効にされた場合、リクエストされた URL が有効なリクエストとして扱われるためには、それが [[yii\web\UrlManager::rules|rules]] の少なくとも一つに合致しなければなりません。
  そうでなければ、[[yii\web\NotFoundHttpException]] が投げられます。
  厳密な解析が無効にされた場合は、リクエストされた URL が [[yii\web\UrlManager::rules|rules]] のどれにも合致しない場合は、
  URL のパス情報の部分がリクエストされたルートとして扱われます。
* [[yii\web\UrlManager::rules|rules]]: このプロパティが URL を解析および生成するための一連の規則を含みます。
  このプロパティが、アプリケーションの固有の要求を満たす形式を持つ URL を生成するために、
  あなたが主として使うプロパティです。

> Note: 生成された URL からエントリ・スクリプト名を隠すためには、[[yii\web\UrlManager::showScriptName|showScriptName]] を `false` に設定するだけでなく、
  ウェブ・サーバを構成して、リクエストされた URL が PHP スクリプトを明示的に指定していない場合でも、
  正しい PHP スクリプトを特定出来るようにする必要があります。
もしあなたが Apache または nginx ウェブ・サーバを使うつもりなら、[インストール](start-installation.md#recommended-apache-configuration)
  のセクションで説明されている推奨設定を参照することが出来ます。


### URL 規則 <span id="url-rules"></span>

URL 規則は [[yii\web\UrlRuleInterface]] を実装するクラス、通常は、[[yii\web\UrlRule]] クラスです。
すべての URL 規則は、URL のパス情報の部分との照合に使われるパターン、ルート、そして、いくつかのクエリ・パラメータから構成されます。
URL 規則は、パターンがリクエストされた URL と合致する場合に、リクエストの解析に使用することが出来ます。
また、URL 規則は、ルートとクエリ・パラメータ名が与えられたものと合致する場合に、URL の生成に使用することが出来ます。

綺麗な URL 形式が有効にされている場合、[[yii\web\UrlManager|URL マネージャ]] は、その [[yii\web\UrlManager::rules|rules]] プロパティに宣言されている URL 規則を使って、
入ってくるリクエストの解析と URL の生成を行います。
具体的に言えば、入ってくるリクエストを解析するためには、[[yii\web\UrlManager|URL マネージャ]] は宣言されている順に規則を調べて、
リクエストされた URL に合致する *最初の* 規則を探します。
そして、その合致する規則を使って URL を解析して、ルートとそれに結び付けられたパラメータを得ます。
同じように、URL を生成するためには、[[yii\web\UrlManager|URL マネージャ]] は、与えられたルートとパラメータに合致する最初の規則を探して、それを使って URL を生成します。

[[yii\web\UrlManager::rules]] は、 [[yii\web\UrlRule::$pattern|パターン]] をキーとし、それに対応する [[yii\web\UrlRule::$route|ルート]] を値とする配列として構成することが出来ます。
「パターン - ルート」のペアが、それぞれ、URL 規則を構成します。
例えば、次の [[yii\web\UrlManager::rules|rules]] の構成は、二つの URL 規則を宣言するものです。
最初の規則は `posts` という URL に合致し、それを `post/index` というルートにマップします。
第二の規則は `post/(\d+)` という正規表現にマッチする URL に合致し、それを `post/view` というルートと `id` という名前のパラメータにマップします。

```php
'rules' => [
    'posts' => 'post/index',
    'post/<id:\d+>' => 'post/view',
]
```

> Info: 規則のパターンは URL のパス情報の部分との照合に使用されます。
  例えば、`/index.php/post/100?source=ad` のパス情報は `post/100` であり (先頭と末尾のスラッシュは無視します)、
  これは `post/(\d+)` というパターンに合致します。

URL 規則は、「パターン - ルート」のペアとして宣言する以外に、構成情報配列として宣言することも出来ます。
構成情報の一つの配列が、それぞれ、一つの URL 規則のオブジェクトを構成するために使われます。
この形式は、URL 規則の他のプロパティを構成したい場合に、よく必要になります。例えば、

```php
'rules' => [
    // ... 他の URL 規則 ...
    [
        'pattern' => 'posts',
        'route' => 'post/index',
        'suffix' => '.json',
    ],
]
```

URL 規則の構成情報で `class` を指定しない場合は、デフォルトとして、[[yii\web\UrlRule]] が使われます。
このクラスが、[[yii\web\UrlManager::$ruleConfig]] で
デフォルト値として定義されています。


### 名前付きパラメータ <span id="named-parameters"></span>

URL 規則は、パターンの中で `<ParamName:RgExp>` の形式で指定される、名前付きクエリ・パラメータと結び付けることが出来ます。
ここで、`ParamName` はパラメータ名を指定し、`RegExp` はパラメータの値との照合に使われるオプションの正規表現を指定するものです。
`RegExp` が指定されていない場合は、
パラメータの値がスラッシュを含まない文字列であるべきことを意味します。

> Note: 正規表現はパラメータの中でのみ使用できます。パターンの残りの部分はプレーンテキストとして解釈されます。

規則が URL の解析に使われるときには、URL の対応する部分に合致した値が、結び付けられたパラメータに入れられます。
そして、そのパラメータは、後に `request` アプリケーション・コンポーネントによって、`$_GET` に入れられて利用できるようになります。
規則が URL の生成に使われるときは、提供されたパラメータの値を受けて、
パラメータが宣言されている所にその値が挿入されます。

名前付きパラメータの動作を説明するためにいくつかの例を挙げましょう。次の三つの URL 規則を宣言したと仮定してください。

```php
'rules' => [
    'posts/<year:\d{4}>/<category>' => 'post/index',
    'posts' => 'post/index',
    'post/<id:\d+>' => 'post/view',
]
```

規則が URL 解析に使われる場合は、

- `/index.php/posts` は、二番目の規則を使って解析され、ルート `post/index` になります。
- `/index.php/posts/2014/php` は、最初の規則を使って解析され、ルートは `post/index`、`year` パラメータの値は 2014、
  そして、`category` パラメータの値は `php` となります。
- `/index.php/post/100` は、三番目の規則を使って解析され、ルートが `post/view`、
  `id` パラメータの値が 100 となります。
- `/index.php/posts/php` は、どのパターンにも合致しないため、[[yii\web\UrlManager::enableStrictParsing]] が `true` の場合は、[[yii\web\NotFoundHttpException]] を引き起こします。
  [[yii\web\UrlManager::enableStrictParsing]] が `false` (これがデフォルト値です) の場合は、パス情報の部分である `posts/php` がルートとして返されることになります。
  こうして解析されたルートに対応するアクションがあればそれが実行され、そうでなければ [[yii\web\NotFoundHttpException]] が投げられます。

規則が URL 生成に使われる場合は、

- `Url::to(['post/index'])` は、二番目の規則を使って、`/index.php/posts` を生成します。
- `Url::to(['post/index', 'year' => 2014, 'category' => 'php'])` は、最初の規則を使って、`/index.php/posts/2014/php` を生成します。
- `Url::to(['post/view', 'id' => 100])` は、三番目の規則を使って、`/index.php/post/100` を生成します。
- `Url::to(['post/view', 'id' => 100, 'source' => 'ad'])` も、三番目の規則を使って、`/index.php/post/100?source=ad` を生成します。
  `source` パラメータは規則の中で指定されていないので、クエリ・パラメータとして、生成される URL に追加されます。
- `Url::to(['post/index', 'category' => 'php'])` は、どの規則も使わずに、`/index.php/post/index?category=php` を生成します。
  どの規則も当てはまらないため、URL は、単純に、ルートをパス情報とし、
  すべてのパラメータをクエリ文字列として追加して生成されます。


### ルートをパラメータ化する <span id="parameterizing-routes"></span>

URL 規則のルートにはパラメータ名を埋め込むことが出来ます。このことによって、URL 規則を複数のルートに合致させることが可能になっています。
例えば、以下の規則は `controller` と `action` というパラメータをルートに埋め込んでいます。

```php
'rules' => [
    '<controller:(post|comment)>/create' => '<controller>/create',
    '<controller:(post|comment)>/<id:\d+>/<action:(update|delete)>' => '<controller>/<action>',
    '<controller:(post|comment)>/<id:\d+>' => '<controller>/view',
    '<controller:(post|comment)>s' => '<controller>/index',
]
```

`/index.php/comment/100/update` という URL の解析には、二番目の規則が適用され、`controller` パラメータには `comment`、`action` パラメータには `update` がセットされます。
こうして、`<controller>/<action>` というルートは、`comment/update` として解決されます。

同じように、`comment/index` というルートの URL を生成するためには、最後の規則が適用されて、`index.php/comments` という URL が生成されます。

> Info: ルートをパラメータ化することによって、URL 規則の数を大幅に減らすことが可能になり、
  [[yii\web\UrlManager|URL マネージャ]] のパフォーマンスを目に見えて改善することが出来ます。

### デフォルトのパラメータ値 <span id="default-parameter-values"></span>

デフォルトでは、規則の中で宣言されたパラメータは必須となります。
リクエストされた URL が特定のパラメータを含まない場合や、特定のパラメータなしで URL を生成する場合には、規則は適用されません。
パラメータのどれかをオプション扱いにしたい場合は、規則の [[yii\web\UrlRule::defaults|defaults]] プロパティを構成することが出来ます。
このプロパティのリストに挙げられたパラメータはオプション扱いとなり、提供されなかった場合は指定された値を取るようになります。

次の規則の宣言においては、`page` と `tag` のパラメータは両方ともオプション扱いで、
提供されなかった場合は、それぞれ、1 と空文字列を取ります。

```php
'rules' => [
    // ... 他の規則 ...
    [
        'pattern' => 'posts/<page:\d+>/<tag>',
        'route' => 'post/index',
        'defaults' => ['page' => 1, 'tag' => ''],
    ],
]
```

上記の規則を以下の URL を解析または生成するために使用することが出来ます。

* `/index.php/posts`: `page` は 1, `tag` は ''.
* `/index.php/posts/2`: `page` は 2, `tag` は ''.
* `/index.php/posts/2/news`: `page` は 2, `tag` は `'news'`.
* `/index.php/posts/news`: `page` は 1, `tag` は `'news'`.

オプション扱いのパラメータを使わなければ、同じ結果を得るために 4 個の規則を作らなければならなかったところです。

> Note: [[yii\web\UrlRule::$pattern|pattern]] がオプション扱いのパラメータとスラッシュだけを含んでいるときは、
  最初のパラメータは、他のパラメータが省略されている場合に限り、省略することが出来ます。


### サーバ名を持つ規則 <span id="rules-with-server-names"></span>

URL 規則のパターンには、ウェブ・サーバ名を含むことが出来ます。
このことが役に立つのは、主として、あなたのアプリケーションがウェブ・サーバ名によって異なる動作をしなければならない場合です。
例えば、次の規則は、`https://admin.example.com/login` という URL を `admin/user/login` のルートとして解析し、`https://www.example.com/login` を `site/login` として解析するものです。

```php
'rules' => [
    'https://admin.example.com/login' => 'admin/user/login',
    'https://www.example.com/login' => 'site/login',
]
```

サーバ名にパラメータを埋め込んで、そこから動的な情報を抽出することも出来ます。
例えば、次の規則は `https://en.example.com/posts` という URL を解析して、`post/index` というルートと `language=en` というパラメータを取得するものです。

```php
'rules' => [
    'http://<language:\w+>.example.com/posts' => 'post/index',
]
```

バージョン 2.0.11 以降は、`http` と `https` の両方に通用する、プロトコル相対パターンを使うことも出来ます。
記法は上記と同じです、ただ、`http:` の部分を省略します。例えば、`'//www.example.com/login' => 'site/login'`。

> Note: サーバ名を持つ規則は、そのパターンに、エントリ・スクリプトのサブフォルダを**含まない**ようにすべきです。
例えば、アプリケーションのエントリ・スクリプトが `https://www.example.com/sandbox/blog/index.php` である場合は、`https://www.example.com/sandbox/blog/posts` ではなく、`https://www.example.com/posts` というパターンを使うべきです。
こうすれば、アプリケーションをどのようなディレクトリに配置しても、URL 規則を変更する必要がなくなります。Yii はアプリケーションのベース URL を自動的に検出します。


### URL 接尾辞 <span id="url-suffixes"></span>

さまざまな目的から URL に接尾辞を追加したいことがあるでしょう。
例えば、静的な HTML ページに見えるように、`.html` を URL に追加したいかも知れません。
また、レスポンスとして期待されているコンテント・タイプを示すために、`.json` を URL に追加したい場合もあるでしょう。
アプリケーションの構成情報で、次のように、[[yii\web\UrlManager::suffix]] プロパティを構成することによって、この目的を達することが出来ます。

```php
[
    // ...
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            // ...
            'suffix' => '.html',
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

上記の構成によって、[[yii\web\UrlManager|URL マネージャ]] は、接尾辞として `.html` の付いた URL を認識し、
また、生成するようになります。

> Tip: URL が全てスラッシュで終るようにするためには、URL 接尾辞として `/` を設定することが出来ます。

> Note: URL 接尾辞を構成すると、リクエストされた URL が接尾辞を持たない場合は、認識できない URL であると見なされるようになります。
  これは、異なる URL 上の重複コンテンツを防止するためのものであり、SEO (検索エンジン最適化) の見地からも推奨されるプラクティスです。

場合によっては、URL によって異なる接尾辞を使いたいことがあるでしょう。
その目的は、個々の URL 規則の [[yii\web\UrlRule::suffix|suffix]] プロパティを構成することによって達成できます。
URL 規則にこのプロパティが設定されている場合は、それが [[yii\web\UrlManager|URL マネージャ]] レベルの接尾辞の設定をオーバーライドします。
例えば、次の構成には、グローバルな接尾辞 `.html` の代りに `.json` を使用するカスタマイズされた URL 規則が含まれています。

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            // ...
            'suffix' => '.html',
            'rules' => [
                // ...
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'suffix' => '.json',
                ],
            ],
        ],
    ],
]
```

### HTTP メソッド <span id="http-methods"></span>

RESTful API を実装するときは、使用されている HTTP メソッドに応じて、同一の URL を異なるルートとして解析することが必要になる場合がよくあります。
これは、規則のパターンにサポートされている HTTP メソッドを前置することによって、簡単に達成することが出来ます。
一つの規則が複数の HTTP メソッドをサポートする場合は、メソッド名をカンマで区切ります。
例えば、次の三つの規則は、`post/<id:\d+>` という同一のパターンを持って、異なる HTTP メソッドをサポートするものです。
`PUT post/100` に対するリクエストは `post/update` と解析され、`GET post/100` に対するリクエストは `post/view` と解析されることになります。

```php
'rules' => [
    'PUT,POST post/<id:\d+>' => 'post/update',
    'DELETE post/<id:\d+>' => 'post/delete',
    'post/<id:\d+>' => 'post/view',
]
```

> Note: URL 規則が HTTP メソッドをパターンに含む場合、指定されたメソッドに `GET` が入っていない限り、その規則は解析目的にだけ使用されます。
  [[yii\web\UrlManager|URL マネージャ]] が URL 生成のために呼ばれたときは、その規則はスキップされます。

> Tip: RESTful API のルーティングを簡単にするために、Yii は特別な URL 規則クラス [[yii\rest\UrlRule]] を提供しています。
  これは非常に効率的なもので、コントローラ ID の自動的な複数形化など、いくつかの素敵な機能をサポートするものです。
  詳細については、RESTful API 開発についての [ルーティング](rest-routing.md) のセクションを参照してください。


### 規則を動的に追加する <span id="adding-rules"></span>

URL 規則は [[yii\web\UrlManager|URL マネージャ]] に動的に追加することが出来ます。
このことは、再配布可能な [モジュール](structure-modules.md) が自分自身の URL 規則を管理する必要がある場合に、しばしば必要になります。
動的に追加された規則がルーティングのプロセスで効果を発揮するためには、その規則をアプリケーションの [ブートストラップ](runtime-bootstrapping.md) の段階で追加しなければなりません。
これは、モジュールにとっては、次のように、[[yii\base\BootstrapInterface]] を実装して、
[[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] メソッドの中で規則を追加しなければならないことを意味します。

```php
public function bootstrap($app)
{
    $app->getUrlManager()->addRules([
        // ここに規則の宣言
    ], false);
}
```

さらに、モジュールが [ブートストラップ](runtime-bootstrapping.md) の過程に関与できるように、
それを [[yii\web\Application::bootstrap]] のリストに挙げなければならないことに注意してください。


### 規則クラスを作成する <span id="creating-rules"></span>

デフォルトの [[yii\web\UrlRule]] クラスはほとんどのプロジェクトに対して十分に柔軟なものであるというのは事実ですが、それでも、自分自身で規則クラスを作る必要があるような状況はあります。
例えば、自動車ディーラーのウェブ・サイトにおいて、`/Manufacturer/Model` のような URL 形式をサポートしたいけれども、
`Manufacturer` と `Model` は、両方とも、データベース・テーブルに保存されている何らかのデータに合致するものでなければならない、というような場合です。
デフォルトの規則クラスは、静的に宣言されるパターンに依拠しているため、ここでは役に立ちません。

この問題を解決するために、次のような URL 規則クラスを作成することが出来ます。

```php
<?php

namespace app\components;

use yii\web\UrlRuleInterface;
use yii\base\BaseObject;

class CarUrlRule extends BaseObject implements UrlRuleInterface
{
    public function createUrl($manager, $route, $params)
    {
        if ($route === 'car/index') {
            if (isset($params['manufacturer'], $params['model'])) {
                return $params['manufacturer'] . '/' . $params['model'];
            } elseif (isset($params['manufacturer'])) {
                return $params['manufacturer'];
            }
        }
        return false; // この規則は適用されない
    }

    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches)) {
            // $matches[1] と $matches[3] をチェックして、
            // データベースの中の製造者とモデルに合致するかどうか調べる
            // 合致すれば、$params['manufacturer'] および/または $params['model']
            // をセットし、['car/index', $params] を返す
        }
        return false; // この規則は適用されない
    }
}
```

そして、[[yii\web\UrlManager::rules]] の構成情報で、新しい規則クラスを使います。

```php
'rules' => [
    // ... 他の規則 ...
    [
        'class' => 'app\components\CarUrlRule', 
        // ... 他のプロパティを構成する ...
    ],
]
```


## URL の正規化 <span id="url-normalization"></span>

バージョン 2.0.10 以降、[[yii\web\UrlManager|UrlManager]] で [[yii\web\UrlNormalizer|UrlNormalizer]] を使って、
同一 URL のバリエーション (例えば、末尾のスラッシュの有無) の問題を処理する出来るようになりました。
技術的には `https://example.com/path` と `https://example.com/path/` は別の URL ですから、これらの両方に同一のコンテントを提供することは SEO ランキングを低下させる可能性があります。
デフォルトでは、URL ノーマライザは、連続したスラッシュを畳み、サフィックスが末尾のスラッシュを持っているかどうかに従って末尾のスラッシュを追加または削除し、
正規化された URL に [恒久的な移動](https://en.wikipedia.org/wiki/HTTP_301) を使ってリダイレクトします。
ノーマライザは、URL マネージャのためにグローバルに構成することも、各規則のために個別に構成することも出来ます。
各規則は、デフォルトでは、URL マネージャのノーマライザを使用します。
[[yii\web\UrlRule::$normalizer|UrlRule::$normalizer]] を `false` にすれば、特定の URL 規則について正規化を無効にすることが出来ます。

次に、[[yii\web\UrlNormalizer|UrlNormalizer]] の構成例を示します。

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'suffix' => '.html',
    'normalizer' => [
        'class' => 'yii\web\UrlNormalizer',
        // デバッグのために、恒久的移動のかわりに一時的リダイレクションを使う
        'action' => UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
    ],
    'rules' => [
        // ... 他の規則 ...
        [
            'pattern' => 'posts',
            'route' => 'post/index',
            'suffix' => '/',
            'normalizer' => false, // この規則では正規化を無効にする
        ],
        [
            'pattern' => 'tags',
            'route' => 'tag/index',
            'normalizer' => [
                // この規則では連続するスラッシュを畳まない
                'collapseSlashes' => false,
            ],
        ],
    ],
]
```

> Note: デフォルトでは [[yii\web\UrlManager::$normalizer|UrlManager::$normalizer]] は無効になっています。
  URL の正規化を有効にするためには、明示的に構成する必要があります。



## パフォーマンスに対する考慮 <span id="performance-consideration"></span>

複雑なウェブ・アプリケーションを開発するときは、リクエストの解析と URL 生成に要する時間を削減するために
URL 規則を最適化することが重要になります。

パラメータ化したルートを使うことによって、URL 規則の数を減らして、パフォーマンスを著しく向上させることが出来ます。

URL を解析または生成するときに、[[yii\web\UrlManager|URL マネージャ]] は、宣言された順序で URL 規則を調べます。
従って、より多く使われる規則がより少なく使われる規則より前に来るように順序を調整することを検討してください。

パターンまたはルートに共通の先頭部分を持つ URL 規則がある場合は、[[yii\web\UrlManager|URL マネージャ]] がそれらをグループ化して効率的に調べることが出来るように、
[[yii\web\GroupUrlRule]] を使うことを検討してください。
あなたのアプリケーションがモジュールによって構成されており、モジュールごとに、モジュール ID を共通の先頭部分とする一群の URL 規則を持っている場合は、通常、このことが当てはまります。
