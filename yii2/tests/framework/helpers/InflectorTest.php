<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\helpers\Inflector;
use yiiunit\TestCase;

/**
 * @group helpers
 */
class InflectorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // destroy application, Helper must work without Yii::$app
        $this->destroyApplication();
    }

    public function testPluralize()
    {
        $testData = [
            'move' => 'moves',
            'foot' => 'feet',
            'child' => 'children',
            'human' => 'humans',
            'man' => 'men',
            'staff' => 'staff',
            'tooth' => 'teeth',
            'person' => 'people',
            'mouse' => 'mice',
            'touch' => 'touches',
            'hash' => 'hashes',
            'shelf' => 'shelves',
            'potato' => 'potatoes',
            'bus' => 'buses',
            'test' => 'tests',
            'car' => 'cars',
            'netherlands' => 'netherlands',
            'currency' => 'currencies',
            'software' => 'software',
            'hardware' => 'hardware',
        ];

        foreach ($testData as $testIn => $testOut) {
            $this->assertEquals($testOut, Inflector::pluralize($testIn));
            $this->assertEquals(ucfirst($testOut), ucfirst(Inflector::pluralize($testIn)));
        }
    }

    public function testSingularize()
    {
        $testData = [
            'moves' => 'move',
            'feet' => 'foot',
            'children' => 'child',
            'humans' => 'human',
            'men' => 'man',
            'staff' => 'staff',
            'teeth' => 'tooth',
            'people' => 'person',
            'mice' => 'mouse',
            'touches' => 'touch',
            'hashes' => 'hash',
            'shelves' => 'shelf',
            'potatoes' => 'potato',
            'buses' => 'bus',
            'tests' => 'test',
            'cars' => 'car',
            'Netherlands' => 'Netherlands',
            'currencies' => 'currency',
            'software' => 'software',
            'hardware' => 'hardware',
        ];
        foreach ($testData as $testIn => $testOut) {
            $this->assertEquals($testOut, Inflector::singularize($testIn));
            $this->assertEquals(ucfirst($testOut), ucfirst(Inflector::singularize($testIn)));
        }
    }

    public function testTitleize()
    {
        $this->assertEquals('Me my self and i', Inflector::titleize('MeMySelfAndI'));
        $this->assertEquals('Me My Self And I', Inflector::titleize('MeMySelfAndI', true));
        $this->assertEquals('?????????? ???????????? ????????????!', Inflector::titleize('??????????????????????????????????!', true));
    }

    public function testCamelize()
    {
        $this->assertEquals('MeMySelfAndI', Inflector::camelize('me my_self-andI'));
        $this->assertEquals('QweQweEwq', Inflector::camelize('qwe qwe^ewq'));
        $this->assertEquals('??????????????????????????????????????????????????????????????', Inflector::camelize('????????????, ???? ?????????? ?????????????????? ???????? ??????????! ????'));
    }

    public function testUnderscore()
    {
        $this->assertEquals('me_my_self_and_i', Inflector::underscore('MeMySelfAndI'));
        $this->assertEquals('????????????_????????_??????????????????', Inflector::underscore('??????????????????????????????????????'));
    }

    public function testCamel2words()
    {
        $this->assertEquals('Camel Case', Inflector::camel2words('camelCase'));
        $this->assertEquals('Camel Case', Inflector::camel2words('CamelCase'));
        $this->assertEquals('Lower Case', Inflector::camel2words('lower_case'));
        $this->assertEquals('Tricky Stuff It Is Testing', Inflector::camel2words(' tricky_stuff.it-is testing... '));
        $this->assertEquals('?? ???? ???????????? ??????!', Inflector::camel2words('????????????????????????!'));
        $this->assertEquals('Test', Inflector::camel2words('TEST'));
        $this->assertEquals('X Foo', Inflector::camel2words('XFoo'));
        $this->assertEquals('Foo Bar Baz', Inflector::camel2words('FooBARBaz'));
        $this->assertEquals('Generate Csrf', Inflector::camel2words('generateCSRF'));
        $this->assertEquals('Generate Csrf Token', Inflector::camel2words('generateCSRFToken'));
        $this->assertEquals('Csrf Token Generator', Inflector::camel2words('CSRFTokenGenerator'));
        $this->assertEquals('Foo Bar', Inflector::camel2words('foo bar'));
        $this->assertEquals('Foo Bar', Inflector::camel2words('foo BAR'));
        $this->assertEquals('Foo Bar', Inflector::camel2words('Foo Bar'));
        $this->assertEquals('Foo Bar', Inflector::camel2words('FOO BAR'));
    }

    public function testCamel2id()
    {
        $this->assertEquals('post-tag', Inflector::camel2id('PostTag'));
        $this->assertEquals('post_tag', Inflector::camel2id('PostTag', '_'));
        $this->assertEquals('????????????_??????', Inflector::camel2id('??????????????????', '_'));

        $this->assertEquals('post-tag', Inflector::camel2id('postTag'));
        $this->assertEquals('post_tag', Inflector::camel2id('postTag', '_'));
        $this->assertEquals('????????????_??????', Inflector::camel2id('??????????????????', '_'));

        $this->assertEquals('foo-ybar', Inflector::camel2id('FooYBar', '-', false));
        $this->assertEquals('foo_ybar', Inflector::camel2id('fooYBar', '_', false));
        $this->assertEquals('??????????_??????_????????????', Inflector::camel2id('????????????????????????????', '_', false));

        $this->assertEquals('foo-y-bar', Inflector::camel2id('FooYBar', '-', true));
        $this->assertEquals('foo_y_bar', Inflector::camel2id('fooYBar', '_', true));
        $this->assertEquals('foo_y_bar', Inflector::camel2id('fooYBar', '_', true));
        $this->assertEquals('??????????_??_????_????????????', Inflector::camel2id('????????????????????????????', '_', true));
    }

    public function testId2camel()
    {
        $this->assertEquals('PostTag', Inflector::id2camel('post-tag'));
        $this->assertEquals('PostTag', Inflector::id2camel('post_tag', '_'));
        $this->assertEquals('????????????????????', Inflector::id2camel('????????????_????????', '_'));

        $this->assertEquals('PostTag', Inflector::id2camel('post-tag'));
        $this->assertEquals('PostTag', Inflector::id2camel('post_tag', '_'));
        $this->assertEquals('????????????????????????????', Inflector::id2camel('??????????_??_????_????????????', '_'));

        $this->assertEquals('ShouldNotBecomeLowercased', Inflector::id2camel('ShouldNotBecomeLowercased', '_'));

        $this->assertEquals('FooYBar', Inflector::id2camel('foo-y-bar'));
        $this->assertEquals('FooYBar', Inflector::id2camel('foo_y_bar', '_'));
    }

    public function testHumanize()
    {
        $this->assertEquals('Me my self and i', Inflector::humanize('me_my_self_and_i'));
        $this->assertEquals('Me My Self And I', Inflector::humanize('me_my_self_and_i', true));
        $this->assertEquals('?????? ?? ???????????? ???? ???????? ??????????', Inflector::humanize('??????_??_????????????_????_????????_??????????'));
    }

    public function testVariablize()
    {
        $this->assertEquals('customerTable', Inflector::variablize('customer_table'));
        $this->assertEquals('????????????Hepimiz', Inflector::variablize('????????????_hepimiz'));
    }

    public function testTableize()
    {
        $this->assertEquals('customer_tables', Inflector::tableize('customerTable'));
    }

    public function testSlugCommons()
    {
        $data = [
            '' => '',
            'hello world 123' => 'hello-world-123',
            'remove.!?[]{}???symbols' => 'removesymbols',
            'minus-sign' => 'minus-sign',
            'mdash???sign' => 'mdash-sign',
            'ndash???sign' => 'ndash-sign',
            '????????????????????????????????' => 'aaaeeeiiiooouuua',
            '??l?? ly?? ????li?? ????li?? l??ikkyy' => 'ala-lyo-aalio-oolia-laikkyy',
        ];

        foreach ($data as $source => $expected) {
            if (extension_loaded('intl')) {
                $this->assertEquals($expected, FallbackInflector::slug($source));
            }
            $this->assertEquals($expected, Inflector::slug($source));
        }
    }

    public function testSlugReplacements()
    {
        $this->assertEquals('dont_replace_replacement', Inflector::slug('dont replace_replacement', '_'));
        $this->assertEquals('remove_trailing_replacements', Inflector::slug('_remove trailing replacements_', '_'));
        $this->assertEquals('remove_excess_replacements', Inflector::slug(' _ _ remove excess _ _ replacements_', '_'));
        $this->assertEquals('thisrepisreprepreplacement', Inflector::slug('this is REP-lacement', 'REP'));
        $this->assertEquals('0_100_kmh', Inflector::slug('0-100 Km/h', '_'));
        $this->assertEquals('testtext', Inflector::slug('test text', ''));
    }

    public function testSlugIntl()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is required.');
        }

        // Some test strings are from https://github.com/bergie/midgardmvc_helper_urlize. Thank you, Henri Bergius!
        $data = [
            // Korean
            '????????????' => 'haedong-geomdo',
            // Hiragana
            '????????????' => 'hiragana',
            // Georgian
            '??????????????????????????????' => 'sakartvelo',
            // Arabic
            '????????????' => 'alrby',
            '??????' => 'rb',
            // Hebrew
            '????????????????' => 'iberiyt',
            // Turkish
            'San??r??m hepimiz ayn?? ??eyi d??????n??yoruz.' => 'sanirim-hepimiz-ayni-seyi-dusunuyoruz',
            // Russian
            '????????????????????????' => 'nedvizimost',
            '????????????????' => 'kontakty',
            // Chinese
            '??????' => 'mei-guo',
            // Estonian
            'J??????r' => 'jaaar',
        ];

        foreach ($data as $source => $expected) {
            $this->assertEquals($expected, Inflector::slug($source));
        }
    }

    public function testTransliterateStrict()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is required.');
        }

        // Some test strings are from https://github.com/bergie/midgardmvc_helper_urlize. Thank you, Henri Bergius!
        $data = [
            // Korean
            '????????????' => 'haedong-geomdo',
            // Hiragana
            '????????????' => 'hiragana',
            // Georgian
            '??????????????????????????????' => 'sakartvelo',
            // Arabic
            '????????????' => 'a??l??rby',
            '??????' => '??rb',
            // Hebrew
            '????????????????' => '??ibe??riyt??',
            // Turkish
            'San??r??m hepimiz ayn?? ??eyi d??????n??yoruz.' => 'San??r??m hepimiz ayn?? s??eyi du??s??u??nu??yoruz.',

            // Russian
            '????????????????????????' => 'nedviz??imost??',
            '????????????????' => 'Kontakty',

            // Ukrainian
            '????????????????????: ??????????, ????????????' => 'Ukrai????ns??ka: g??anok, e??vropa',

            // Serbian
            '????????????: ??, ??, ??!' => 'Srpska: ??, n??, d??!',

            // Spanish
            '??Espa??ol?' => '??Espan??ol?',
            // Chinese
            '??????' => 'me??i guo??',
        ];

        foreach ($data as $source => $expected) {
            $this->assertEquals($expected, Inflector::transliterate($source, Inflector::TRANSLITERATE_STRICT));
        }
    }

    public function testTransliterateMedium()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is required.');
        }

        // Some test strings are from https://github.com/bergie/midgardmvc_helper_urlize. Thank you, Henri Bergius!
        $data = [
            // Korean
            '????????????' => ['haedong-geomdo'],
            // Hiragana
            '????????????' => ['hiragana'],
            // Georgian
            '??????????????????????????????' => ['sakartvelo'],
            // Arabic
            '????????????' => ['al??rby'],
            '??????' => ['??rb'],
            // Hebrew
            '????????????????' => ['\'iberiyt', '??iberiyt'],
            // Turkish
            'San??r??m hepimiz ayn?? ??eyi d??????n??yoruz.' => ['Sanirim hepimiz ayni seyi dusunuyoruz.'],

            // Russian
            '????????????????????????' => ['nedvizimost\'', 'nedvizimost??'],
            '????????????????' => ['Kontakty'],

            // Ukrainian
            '????????????????????: ??????????, ????????????' => ['Ukrains??ka: ganok, evropa', 'Ukrains\'ka: ganok, evropa'],

            // Serbian
            '????????????: ??, ??, ??!' => ['Srpska: d, n, d!'],

            // Spanish
            '??Espa??ol?' => ['??Espanol?', '?Espanol?'],
            // Chinese
            '??????' => ['mei guo'],
        ];

        foreach ($data as $source => $allowed) {
            $this->assertIsOneOf(Inflector::transliterate($source, Inflector::TRANSLITERATE_MEDIUM), $allowed);
        }
    }

    public function testTransliterateLoose()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is required.');
        }

        // Some test strings are from https://github.com/bergie/midgardmvc_helper_urlize. Thank you, Henri Bergius!
        $data = [
            // Korean
            '????????????' => ['haedong-geomdo'],
            // Hiragana
            '????????????' => ['hiragana'],
            // Georgian
            '??????????????????????????????' => ['sakartvelo'],
            // Arabic
            '????????????' => ['alrby'],
            '??????' => ['rb'],
            // Hebrew
            '????????????????' => ['\'iberiyt', 'iberiyt'],
            // Turkish
            'San??r??m hepimiz ayn?? ??eyi d??????n??yoruz.' => ['Sanirim hepimiz ayni seyi dusunuyoruz.'],

            // Russian
            '????????????????????????' => ['nedvizimost\'', 'nedvizimost'],
            '????????????????' => ['Kontakty'],

            // Ukrainian
            '????????????????????: ??????????, ????????????' => ['Ukrainska: ganok, evropa', 'Ukrains\'ka: ganok, evropa'],

            // Serbian
            '????????????: ??, ??, ??!' => ['Srpska: d, n, d!'],

            // Spanish
            '??Espa??ol?' => ['Espanol?', '?Espanol?'],
            // Chinese
            '??????' => ['mei guo'],
        ];

        foreach ($data as $source => $allowed) {
            $this->assertIsOneOf(Inflector::transliterate($source, Inflector::TRANSLITERATE_LOOSE), $allowed);
        }
    }

    public function testSlugPhp()
    {
        $data = [
            'we have ????????????????????????' => 'we-have',
        ];

        foreach ($data as $source => $expected) {
            $this->assertEquals($expected, FallbackInflector::slug($source));
        }
    }

    public function testClassify()
    {
        $this->assertEquals('CustomerTable', Inflector::classify('customer_tables'));
    }

    public function testOrdinalize()
    {
        $this->assertEquals('21st', Inflector::ordinalize('21'));
        $this->assertEquals('22nd', Inflector::ordinalize('22'));
        $this->assertEquals('23rd', Inflector::ordinalize('23'));
        $this->assertEquals('24th', Inflector::ordinalize('24'));
        $this->assertEquals('25th', Inflector::ordinalize('25'));
        $this->assertEquals('111th', Inflector::ordinalize('111'));
        $this->assertEquals('113th', Inflector::ordinalize('113'));
    }

    public function testSentence()
    {
        $array = [];
        $this->assertEquals('', Inflector::sentence($array));

        $array = ['Spain'];
        $this->assertEquals('Spain', Inflector::sentence($array));

        $array = ['Spain', 'France'];
        $this->assertEquals('Spain and France', Inflector::sentence($array));

        $array = ['Spain', 'France', 'Italy'];
        $this->assertEquals('Spain, France and Italy', Inflector::sentence($array));

        $array = ['Spain', 'France', 'Italy', 'Germany'];
        $this->assertEquals('Spain, France, Italy and Germany', Inflector::sentence($array));

        $array = ['Spain', 'France'];
        $this->assertEquals('Spain or France', Inflector::sentence($array, ' or '));

        $array = ['Spain', 'France', 'Italy'];
        $this->assertEquals('Spain, France or Italy', Inflector::sentence($array, ' or '));

        $array = ['Spain', 'France'];
        $this->assertEquals('Spain and France', Inflector::sentence($array, ' and ', ' or ', ' - '));

        $array = ['Spain', 'France', 'Italy'];
        $this->assertEquals('Spain - France or Italy', Inflector::sentence($array, ' and ', ' or ', ' - '));
    }
}
