## yii2-arubasms

This widget will let you send SMS through the HTTP interface exposed by Aruba (http://guide.hosting.aruba.it/web-marketing/sms-aruba/invio-sms-tramite-api-sms-aruba.aspx).


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

### Install

Either run

```
$ php composer.phar require d4rkstar/yii2-arubasms "dev-master"
```

or add

```
"d4rkstar/yii2-arubasms": "dev-master"
```

to the ```require``` section of your `composer.json` file.

### Sample Usage

In the section ```components``` of your `app/config/web.php`, add:

```
'components' => [
    ...
    'arubasms' => require(__DIR__ . '/arubasms.php'),
]
```

Now, add a configuration file named `app/config/arubasms.php`, and add:

```
<?php
use d4rkstar\kannel\HttpSms;

return [
    'class'=>'d4rkstar\arubasms\HttpSms',

];
?>
```

Now, anywhere in your application, you can send an SMS:

```
<?php
    // sample code here
?>
```

