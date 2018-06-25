# coinbase-commerce-prestashop

# Run Tests

This Prestashop plugin is using the composer dependencies Mockery and PHPUnit to do unit testing. Because of the limitations of Mockery all unit tests need to be run as isolated processes. 

You run the tests with `vendor/phpunit/phpunit/phpunit --process-isolation`.