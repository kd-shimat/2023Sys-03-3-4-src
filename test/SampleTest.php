
<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

class SampleTest extends TestCase
{
    protected $pdo; // PDOオブジェクト用のプロパティ(メンバ変数)の宣言
    protected $driver;

    public function setUp(): void
    {
        // PDOオブジェクトを生成し、データベースに接続
        $dsn = "mysql:host=db;dbname=shop;charset=utf8";
        $user = "shopping";
        $password = "site";
        try {
            $this->pdo = new PDO($dsn, $user, $password);
        } catch (Exception $e) {
            echo 'Error:' . $e->getMessage();
            die();
        }

        #XAMPP環境で実施している場合、$dsn設定を変更する必要がある
        //ファイルパス
        $rdfile = __DIR__ . '/../src/classes/dbdata.php';
        $val = "host=db;";

        //ファイルの内容を全て文字列に読み込む
        $str = file_get_contents($rdfile);
        //検索文字列に一致したすべての文字列を置換する
        $str = str_replace("host=localhost;", $val, $str);
        //文字列をファイルに書き込む
        file_put_contents($rdfile, $str);

        // chrome ドライバーの起動
        $host = 'http://172.17.0.1:4444/wd/hub'; #Github Actions上で実行可能なHost
        // chrome ドライバーの起動
        $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
    }

    public function testSignup()
    {
        // 指定URLへ遷移 (Google)
        $this->driver->get('http://php/src/index.php');

        // トップページ画面のログインリンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[3]->click();

        // ログイン画面の新規登録リンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[4]->click();

        // 新規登録
        $element_input = $this->driver->findElements(WebDriverBy::tagName('input'));
        $element_input[0]->sendKeys('taro@yamada.com');
        $element_input[1]->sendKeys('山田　太郎');
        $element_input[2]->sendKeys('ヤマダ　タロウ');
        $element_input[3]->sendKeys('6508570');
        $element_input[4]->sendKeys('神戸市中央区加納町6‒5‒1');
        $element_input[5]->sendKeys('078‒331‒8181');
        $element_input[6]->sendKeys('taroyamada');
        $element_input[7]->submit();

        // トップページへのリンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[1]->click();

        // トップページ画面のmusicリンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[7]->click();

        // ジャンル別商品一覧画面の詳細リンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[5]->click();

        // 商品詳細画面の注文数を「2」にし、「カートに入れる」をクリック
        $selector = $this->driver->findElement(WebDriverBy::tagName('select'));
        $selector->click();
        $this->driver->getKeyboard()->sendKeys("2");
        $selector->click();
        $selector->submit();

        // ユーザー情報リンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[3]->click();

        // ユーザー情報変更
        $element_input = $this->driver->findElements(WebDriverBy::tagName('input'));
        $element_input[0]->clear();
        $element_input[1]->clear();
        $element_input[2]->clear();
        $element_input[3]->clear();
        $element_input[4]->clear();
        $element_input[5]->clear();
        $element_input[6]->clear();
        $element_input[0]->sendKeys('hanako@yamada.com');
        $element_input[1]->sendKeys('山田　花子');
        $element_input[2]->sendKeys('ヤマダ　ハナコ');
        $element_input[3]->sendKeys('1008111');
        $element_input[4]->sendKeys('東京都千代田区千代田1‒1');
        $element_input[5]->sendKeys('03‒3213‒1111');
        $element_input[6]->sendKeys('hanakoyamada');
        $element_input[7]->submit();

        //データベースの値を取得
        $sql = 'select * from cart where userId = ?';       // SQL文の定義
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['hanako@yamada.com']);
        $cart = $stmt->fetch();

        // assert
        $this->assertEquals(2, $cart['quantity'], 'ユーザー変更後のカート情報が正しく引き継がれておりません');
    }
}
