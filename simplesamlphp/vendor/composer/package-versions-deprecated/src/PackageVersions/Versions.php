<?php

declare(strict_types=1);

namespace PackageVersions;

use Composer\InstalledVersions;
use OutOfBoundsException;

class_exists(InstalledVersions::class);

/**
 * This class is generated by composer/package-versions-deprecated, specifically by
 * @see \PackageVersions\Installer
 *
 * This file is overwritten at every run of `composer install` or `composer update`.
 *
 * @deprecated in favor of the Composer\InstalledVersions class provided by Composer 2. Require composer-runtime-api:^2 to ensure it is present.
 */
final class Versions
{
    /**
     * @deprecated please use {@see self::rootPackageName()} instead.
     *             This constant will be removed in version 2.0.0.
     */
    const ROOT_PACKAGE_NAME = 'simplesamlphp/simplesamlphp';

    /**
     * Array of all available composer packages.
     * Dont read this array from your calling code, but use the \PackageVersions\Versions::getVersion() method instead.
     *
     * @var array<string, string>
     * @internal
     */
    const VERSIONS          = array (
  'gettext/gettext' => '4.x-dev@3e7460f8d9c90174824e3f39240bd578bb3d376a',
  'gettext/languages' => '2.9.0@ed56dd2c7f4024cc953ed180d25f02f2640e3ffa',
  'phpfastcache/riak-client' => '3.4.3@d771f75d16196006604a30bb15adc1c6a9b0fcc9',
  'phpmailer/phpmailer' => 'v6.6.3@9400f305a898f194caff5521f64e5dfa926626f3',
  'psr/cache' => '1.0.1@d11b50ad223250cf17b86e38383413f5a6764bf8',
  'psr/container' => '1.0.0@b7ce3b176482dbbc1245ebf52b181af44c2cf55f',
  'psr/log' => '1.1.4@d49695b909c3b7628b6289db5479a1c204601f11',
  'robrichards/xmlseclibs' => '3.1.1@f8f19e58f26cdb42c54b214ff8a820760292f8df',
  'simplesamlphp/assert' => 'v0.0.13@5429921b320ca4f9d1844225884ac52f649ea1e3',
  'simplesamlphp/composer-module-installer' => 'v1.1.8@45161b5406f3e9c82459d0f9a5a1dba064953cfa',
  'simplesamlphp/saml2' => 'v4.6.3@bfc9c79dd6b728a41d1de988f545f6e64728a51d',
  'simplesamlphp/simplesamlphp-module-adfs' => 'v1.0.9@c47daabc262b7e14a76879015fd9db85319752ec',
  'simplesamlphp/simplesamlphp-module-authcrypt' => 'v0.9.4@62555123e61b11463be3cd7adb708562023cff28',
  'simplesamlphp/simplesamlphp-module-authfacebook' => 'v0.9.3@9152731e939ad4a49e0f06da5f0009ebde0d2b5c',
  'simplesamlphp/simplesamlphp-module-authorize' => 'v0.9.4@4c7ce4eaa54fc301f131c62e803fc843e4d88056',
  'simplesamlphp/simplesamlphp-module-authtwitter' => 'v0.9.3@6e178e7aae7827a64dc462b5bb2f28d6eddc4381',
  'simplesamlphp/simplesamlphp-module-authwindowslive' => 'v0.9.1@f40aecec6c0adaedb6693309840c98cec783876e',
  'simplesamlphp/simplesamlphp-module-authx509' => 'v0.9.9@b138f41b2bc725371f42abb63b5a39ac11b5432a',
  'simplesamlphp/simplesamlphp-module-authyubikey' => 'v0.9.3@414e2a73da4adfee6d97ba66e852ec7c85369913',
  'simplesamlphp/simplesamlphp-module-cas' => 'v0.9.1@63b72e4600550c507cdfc32fdd208ad59a64321e',
  'simplesamlphp/simplesamlphp-module-cdc' => 'v0.9.2@92498fc3004c02849d96da29ca472d99ed23af73',
  'simplesamlphp/simplesamlphp-module-consent' => 'v0.9.8@8466b0b7c6207b15ca5e265f436299ff2dec85da',
  'simplesamlphp/simplesamlphp-module-consentadmin' => 'v0.9.2@62dc5e9d5b1a12a73549c80140b7224d7f7d1c2e',
  'simplesamlphp/simplesamlphp-module-discopower' => 'v0.10.1@4cb6b7c648b455586903b8932a171397375b50b0',
  'simplesamlphp/simplesamlphp-module-exampleattributeserver' => 'v1.0.0@63e0323e81c32bc3c9eaa01ea45194bb10153708',
  'simplesamlphp/simplesamlphp-module-expirycheck' => 'v0.9.4@02101497281031befba93c48c96ee9133f57241d',
  'simplesamlphp/simplesamlphp-module-ldap' => 'v0.9.17@40f1bfe0c4ac2f91cf8e52d22fa6ec2fe1c03066',
  'simplesamlphp/simplesamlphp-module-memcachemonitor' => 'v0.9.3@8d25463ac56b4e2294f59f622a6658e0c67086f4',
  'simplesamlphp/simplesamlphp-module-memcookie' => 'v1.2.2@39535304e8d464b7baa1e82cb441fa432947ff57',
  'simplesamlphp/simplesamlphp-module-metarefresh' => 'v0.10.0@488d7809857c274befac89facfa03520a05bc1ba',
  'simplesamlphp/simplesamlphp-module-negotiate' => 'v0.9.12@48752cea80e81a60ebb522cc10789589ac16df50',
  'simplesamlphp/simplesamlphp-module-oauth' => 'v0.9.3@2a2433144dca408315e4ee163f9ab73a6110b2b1',
  'simplesamlphp/simplesamlphp-module-preprodwarning' => 'v0.9.3@b3c6d9d41d009e340f4843ce5c24b4118a38e4c3',
  'simplesamlphp/simplesamlphp-module-radius' => 'v0.9.4@dbe2976ba27f5131faeca368a5665f8baeaae8b6',
  'simplesamlphp/simplesamlphp-module-riak' => 'v0.9.1@c1a9d9545cb4e05b9205b34624850bb777aca991',
  'simplesamlphp/simplesamlphp-module-sanitycheck' => 'v0.9.1@15d6664eae73a233c3c4c72fd8a5c2be72b6ed2a',
  'simplesamlphp/simplesamlphp-module-smartattributes' => 'v0.9.2@ba6a32fa287db0f8d767104471176f70fad7f0e1',
  'simplesamlphp/simplesamlphp-module-sqlauth' => 'v0.9.4@8a28f9a9726bab1dbc8fd3734daa08882dd0a25b',
  'simplesamlphp/simplesamlphp-module-statistics' => 'v0.9.6@03fb6bdbbf5ce0a0cb257208db79aacac227ac10',
  'simplesamlphp/twig-configurable-i18n' => 'v2.3.4@e2bffc7eed3112a0b3870ef5b4da0fd74c7c4b8a',
  'symfony/cache' => 'v4.4.43@3b3c6019f3df2fa73d15dfed133e432a9801d7eb',
  'symfony/cache-contracts' => 'v1.1.13@a872a66e0bf7bac179c89bc96c7098bef1949f81',
  'symfony/config' => 'v4.4.42@83cdafd1bd3370de23e3dc2ed01026908863be81',
  'symfony/console' => 'v4.4.43@8a2628d2d5639f35113dc1b833ecd91e1ed1cf46',
  'symfony/debug' => 'v4.4.41@6637e62480b60817b9a6984154a533e8e64c6bd5',
  'symfony/dependency-injection' => 'v4.4.43@8d0ae6d87ceea5f3a352413f9d1f71ed2234dcbd',
  'symfony/error-handler' => 'v4.4.41@529feb0e03133dbd5fd3707200147cc4903206da',
  'symfony/event-dispatcher' => 'v4.4.42@708e761740c16b02c86e3f0c932018a06b895d40',
  'symfony/event-dispatcher-contracts' => 'v1.1.13@1d5cd762abaa6b2a4169d3e77610193a7157129e',
  'symfony/filesystem' => 'v4.4.42@815412ee8971209bd4c1eecd5f4f481eacd44bf5',
  'symfony/finder' => 'v4.4.41@40790bdf293b462798882ef6da72bb49a4a6633a',
  'symfony/framework-bundle' => 'v4.4.43@07a0ce6656e6de53a529018fe7a1421be838d2ad',
  'symfony/http-client-contracts' => 'v1.1.13@59f37624a82635962f04c98f31aed122e539a89e',
  'symfony/http-foundation' => 'v4.4.43@4441dada27f9208e03f449d73cb9253c639e53c5',
  'symfony/http-kernel' => 'v4.4.43@c4c33fb9203e6f166ac0f318ce34e00686702522',
  'symfony/mime' => 'v4.4.43@de46889e8844d8327677582950bd227273d8f2f3',
  'symfony/polyfill-ctype' => 'v1.26.0@6fd1b9a79f6e3cf65f9e679b23af304cd9e010d4',
  'symfony/polyfill-intl-idn' => 'v1.26.0@59a8d271f00dd0e4c2e518104cc7963f655a1aa8',
  'symfony/polyfill-intl-normalizer' => 'v1.26.0@219aa369ceff116e673852dce47c3a41794c14bd',
  'symfony/polyfill-mbstring' => 'v1.26.0@9344f9cb97f3b19424af1a21a3b0e75b0a7d8d7e',
  'symfony/polyfill-php72' => 'v1.26.0@bf44a9fd41feaac72b074de600314a93e2ae78e2',
  'symfony/polyfill-php73' => 'v1.26.0@e440d35fa0286f77fb45b79a03fedbeda9307e85',
  'symfony/polyfill-php80' => 'v1.26.0@cfa0ae98841b9e461207c13ab093d76b0fa7bace',
  'symfony/polyfill-php81' => 'v1.26.0@13f6d1271c663dc5ae9fb843a8f16521db7687a1',
  'symfony/routing' => 'v4.4.41@c25e38d403c00d5ddcfc514f016f1b534abdf052',
  'symfony/service-contracts' => 'v1.1.13@afa00c500c2d6aea6e3b2f4862355f507bc5ebb4',
  'symfony/var-dumper' => 'v4.4.42@742aab50ad097bcb62d91fccb613f66b8047d2ca',
  'symfony/var-exporter' => 'v4.4.43@4a7a3a3d55c471d396e6d28011368b7b83cb518b',
  'symfony/yaml' => 'v4.4.43@07e392f0ef78376d080d5353c081a5e5704835bd',
  'twig/extensions' => 'v1.5.4@57873c8b0c1be51caa47df2cdb824490beb16202',
  'twig/twig' => 'v2.15.1@3b7cedb2f736899a7dbd0ba3d6da335a015f5cc4',
  'webmozart/assert' => '1.9.1@bafc69caeb4d49c39fd0779086c03a3738cbb389',
  'whitehat101/apr1-md5' => 'v1.0.0@8b261c9fc0481b4e9fa9d01c6ca70867b5d5e819',
  'amphp/amp' => 'v2.6.2@9d5100cebffa729aaffecd3ad25dc5aeea4f13bb',
  'amphp/byte-stream' => 'v1.8.1@acbd8002b3536485c997c4e019206b3f10ca15bd',
  'composer/package-versions-deprecated' => '1.11.99.5@b4f54f74ef3453349c24a845d22392cd31e65f1d',
  'composer/semver' => '3.3.2@3953f23262f2bff1919fc82183ad9acb13ff62c9',
  'composer/xdebug-handler' => '1.4.6@f27e06cd9675801df441b3656569b328e04aa37c',
  'dnoegel/php-xdg-base-dir' => 'v0.1.1@8f8a6e48c5ecb0f991c2fdcf5f154a47d85f9ffd',
  'doctrine/instantiator' => '1.4.1@10dcfce151b967d20fde1b34ae6640712c3891bc',
  'felixfbecker/advanced-json-rpc' => 'v3.2.1@b5f37dbff9a8ad360ca341f3240dc1c168b45447',
  'felixfbecker/language-server-protocol' => 'v1.5.2@6e82196ffd7c62f7794d778ca52b69feec9f2842',
  'mikey179/vfsstream' => 'v1.6.10@250c0825537d501e327df879fb3d4cd751933b85',
  'myclabs/deep-copy' => '1.11.0@14daed4296fae74d9e3201d2c4925d1acb7aa614',
  'netresearch/jsonmapper' => 'v3.1.1@ba09f0e456d4f00cef84e012da5715625594407c',
  'nikic/php-parser' => 'v4.14.0@34bea19b6e03d8153165d8f30bba4c3be86184c1',
  'openlss/lib-array2xml' => '1.0.0@a91f18a8dfc69ffabe5f9b068bc39bb202c81d90',
  'phar-io/manifest' => '1.0.3@7761fcacf03b4d4f16e7ccb606d4879ca431fcf4',
  'phar-io/version' => '2.0.1@45a2ec53a73c70ce41d55cedef9063630abaf1b6',
  'phpdocumentor/reflection-common' => '2.1.0@6568f4687e5b41b054365f9ae03fcb1ed5f2069b',
  'phpdocumentor/reflection-docblock' => '4.3.4@da3fd972d6bafd628114f7e7e036f45944b62e9c',
  'phpdocumentor/type-resolver' => '1.0.1@2e32a6d48972b2c1976ed5d8967145b6cec4a4a9',
  'phpspec/prophecy' => 'v1.10.3@451c3cd1418cf640de218914901e51b064abb093',
  'phpunit/php-code-coverage' => '6.1.4@807e6013b00af69b6c5d9ceb4282d0393dbb9d8d',
  'phpunit/php-file-iterator' => '2.0.5@42c5ba5220e6904cbfe8b1a1bda7c0cfdc8c12f5',
  'phpunit/php-text-template' => '1.2.1@31f8b717e51d9a2afca6c9f046f5d69fc27c8686',
  'phpunit/php-timer' => '2.1.3@2454ae1765516d20c4ffe103d85a58a9a3bd5662',
  'phpunit/php-token-stream' => '3.1.3@9c1da83261628cb24b6a6df371b6e312b3954768',
  'phpunit/phpunit' => '7.5.20@9467db479d1b0487c99733bb1e7944d32deded2c',
  'sebastian/code-unit-reverse-lookup' => '1.0.2@1de8cd5c010cb153fcd68b8d0f64606f523f7619',
  'sebastian/comparator' => '3.0.3@1071dfcef776a57013124ff35e1fc41ccd294758',
  'sebastian/diff' => '3.0.3@14f72dd46eaf2f2293cbe79c93cc0bc43161a211',
  'sebastian/environment' => '4.2.4@d47bbbad83711771f167c72d4e3f25f7fcc1f8b0',
  'sebastian/exporter' => '3.1.4@0c32ea2e40dbf59de29f3b49bf375176ce7dd8db',
  'sebastian/global-state' => '2.0.0@e8ba02eed7bbbb9e59e43dedd3dddeff4a56b0c4',
  'sebastian/object-enumerator' => '3.0.4@e67f6d32ebd0c749cf9d1dbd9f226c727043cdf2',
  'sebastian/object-reflector' => '1.1.2@9b8772b9cbd456ab45d4a598d2dd1a1bced6363d',
  'sebastian/recursion-context' => '3.0.1@367dcba38d6e1977be014dc4b22f47a484dac7fb',
  'sebastian/resource-operations' => '2.0.2@31d35ca87926450c44eae7e2611d45a7a65ea8b3',
  'sebastian/version' => '2.0.1@99732be0ddb3361e16ad77b68ba41efc8e979019',
  'sensiolabs/security-checker' => 'v6.0.3@a576c01520d9761901f269c4934ba55448be4a54',
  'simplesamlphp/simplesamlphp-test-framework' => 'v0.1.2@f54a646a95f7b928d06a36d5f7f8303ac07f09b2',
  'squizlabs/php_codesniffer' => '3.7.1@1359e176e9307e906dc3d890bcc9603ff6d90619',
  'symfony/http-client' => 'v4.4.42@0366fe9d67709477e86b45e2e51a34ccf5018d04',
  'theseer/tokenizer' => '1.1.3@11336f6f84e16a720dae9d8e6ed5019efa85a0f9',
  'vimeo/psalm' => '3.18.2@19aa905f7c3c7350569999a93c40ae91ae4e1626',
  'webmozart/glob' => '4.1.0@3cbf63d4973cf9d780b93d2da8eec7e4a9e63bbe',
  'webmozart/path-util' => '2.3.0@d939f7edc24c9a1bb9c0dee5cb05d8e859490725',
  'simplesamlphp/simplesamlphp' => 'v1.19.6@',
);

    private function __construct()
    {
    }

    /**
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function rootPackageName() : string
    {
        if (!self::composer2ApiUsable()) {
            return self::ROOT_PACKAGE_NAME;
        }

        return InstalledVersions::getRootPackage()['name'];
    }

    /**
     * @throws OutOfBoundsException If a version cannot be located.
     *
     * @psalm-param key-of<self::VERSIONS> $packageName
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function getVersion(string $packageName): string
    {
        if (self::composer2ApiUsable()) {
            return InstalledVersions::getPrettyVersion($packageName)
                . '@' . InstalledVersions::getReference($packageName);
        }

        if (isset(self::VERSIONS[$packageName])) {
            return self::VERSIONS[$packageName];
        }

        throw new OutOfBoundsException(
            'Required package "' . $packageName . '" is not installed: check your ./vendor/composer/installed.json and/or ./composer.lock files'
        );
    }

    private static function composer2ApiUsable(): bool
    {
        if (!class_exists(InstalledVersions::class, false)) {
            return false;
        }

        if (method_exists(InstalledVersions::class, 'getAllRawData')) {
            $rawData = InstalledVersions::getAllRawData();
            if (count($rawData) === 1 && count($rawData[0]) === 0) {
                return false;
            }
        } else {
            $rawData = InstalledVersions::getRawData();
            if ($rawData === null || $rawData === []) {
                return false;
            }
        }

        return true;
    }
}
