<?php

/**
 * Created by PhpStorm.
 * User: Pathologic
 * Date: 19.11.2016
 * Time: 0:27
 */
class ReCaptchaWrapper
{
    /**
     * @var array $cfg
     * id, secretKey, siteKey, size, theme, tabIndex, type
     */
    public $cfg = null;
    protected $modx = null;

    /**
     * modxCaptchaWrapper constructor.
     * @param $modx
     * @param $cfg
     */
    public function __construct($modx, $cfg)
    {
        $this->cfg = $cfg;
        $this->modx = $modx;
    }

    /**
     * Устанавливает значение капчи
     * @return mixed
     */
    public function init()
    {
        return;
    }

    /**
     * Плейсхолдер капчи для вывода в шаблон
     * Может быть ссылкой на коннектор (чтобы можно было обновлять c помощью js), может быть сразу картинкой в base64
     * @return string
     */
    public function getPlaceholder()
    {
        $siteKey = \APIhelpers::getkey($this->cfg, 'siteKey');
        $type = \APIhelpers::getkey($this->cfg, 'type', 'image');
        $size = \APIhelpers::getkey($this->cfg, 'tabindex', 0);
        $tabindex = \APIhelpers::getkey($this->cfg, 'tabindex', 0);
        $theme = \APIhelpers::getkey($this->cfg, 'theme', 'light');
        $id = \APIhelpers::getkey($this->cfg, 'id');
        $id = 'id="' . $id . '-recaptcha"';
        $out = '';
        if (!empty($siteKey)) {
            $out = "<div {$id} class=\"g-recaptcha\" data-sitekey=\"{$siteKey}\" data-type=\"{$type}\" data-tabindex=\"{$tabindex}\" data-size=\"{$size}\" data-theme=\"{$theme}\"></div>";
        }

        return $out;
    }

    /**
     * @param \FormLister\Core $FormLister
     * @param $value
     * @param ReCaptchaWrapper $captcha
     * @return bool|string
     */
    public static function validate(\FormLister\Core $FormLister, $value, \ReCaptchaWrapper $captcha)
    {
        $secretKey = \APIhelpers::getkey($captcha->cfg, 'secretKey');
        $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . $secretKey . "&response=" . $value . "&remoteip=" . \APIhelpers::getUserIP();
        $out = false;
        if (!empty($value)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_USERAGENT,
                "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response, true);
            $out = $response['success'];
        }
        if (!$out) {
            $out = \APIhelpers::getkey($captcha->cfg, 'errorCodeFailed', 'Вы не прошли проверку');
        }
        $FormLister->log('reCaptcha validation result: '.$out);

        return $out;
    }
}
