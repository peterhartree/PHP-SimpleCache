# PHP SimpleCache

The PHP SimpleCache Class is an easy way to cache 3rd party API calls.

Patched by @peterhartree to store the cache files on Amazon S3.

## Install

Install via [composer](https://getcomposer.org):

```javascript
{
    "require": {
        "aws/aws-sdk-php": "~2.6",
        "peterhartree/php-simplecache": "~1.4"
    }
}
```

Run `composer install` then use as normal.

## Usage

A basic usage example:

```php
require 'vendor/autoload.php';

$s3_bucket_id = 'YOUR_BUCKET_ID';
$s3_access_control_list = 'CANNED_ACL'; // http://docs.aws.amazon.com/AmazonS3/latest/dev/acl-overview.html#canned-acl

$s3_credentials = array(
  'credentials' => array(
    'key'    => 'YOUR_KEY',
    'secret' => 'YOUR_SECRET_KEY',
  )
);

use Aws\S3\S3Client;

// Instantiate the client.
$s3_client = S3Client::factory($s3_credentials);

$cache = new Gilbitron\Util\SimpleCache($s3_client, $s3_bucket_id, $s3_access_control_list);

$latest_tweet = $cache->get_data('tweet', 'http://search.twitter.com/search.atom?q=from:gilbitron&rpp=1');
echo $latest_tweet;
```

## Credits

PHP SimpleCache was created by [Gilbert Pellegrom](http://gilbert.pellegrom.me) from [Dev7studios](http://dev7studios.com). Released under the MIT license.
