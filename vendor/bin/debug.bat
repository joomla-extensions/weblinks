:: XDEBUB settings as envoirenment variable for PHPStorm on Windwos
:: see https://confluence.jetbrains.com/display/PhpStorm/Debugging+PHP+CLI+scripts+with+PhpStorm
:: 'switch on' Storms debugger and execute this once before run the tests.

SET XDEBUG_CONFIG="remote_enable=1 remote_mode=req remote_port=9000 remote_host=127.0.0.1 remote_connect_back=0"
