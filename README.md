# phpsoliscontrol
phpsoliscontrol is a standalone php proof-of-concept script to set charge/discharge settings for Solis Inverters via SolisCloud API


## Usage

The script can be directly called like:
```
php phpsoliscontrol.php
```

Optionally, you can specify the "reset" argument to set a predefined set of charge / discharge from 00:00-00:00 (aka "do nothing")
```
php phpsoliscontrol.php reset
```



## Account Settings

Enter your account details in a separate config.php file or directly edit the script

```

$keyID = '<your API Key provided by SolisAPI>'; //i.e. '13000000000000000'
$keySecret = '<your API SECRET provided by SolisAPI>'; //i.e. 'aabbccddeff001122334455'

$keyAccount = '<SolisCloud Web Account>'; //i.e. 'blahblah@hotmail.com';
$keyPass = '<SolisCloud Web Pass>'; // i.e.'sldkfjslkdfjslkdfjlsk';

```



## Requirements

Just a basic PHP able to run curl calls.
